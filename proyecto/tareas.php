<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';

if (!isset($_SESSION['rol'])) { header('Location: login.php'); exit(); }

$rol = $_SESSION['rol'];
$uid = intval($_SESSION['id'] ?? 0);
$eid = intval($_SESSION['empresa_id'] ?? 0);
$volver = agro_volver_panel();
$mensaje = '';
$error = '';

$sinTabla = !agro_tabla_existe($conn,'tareas_agricolas') || !agro_tabla_existe($conn,'parcelas') || !agro_tabla_existe($conn,'usuarios') || !agro_columna_existe($conn,'tareas_agricolas','trabajador_id') || !agro_columna_existe($conn,'tareas_agricolas','prioridad') || !agro_columna_existe($conn,'tareas_agricolas','fecha_limite');
$tieneGrupos = agro_tabla_existe($conn,'grupos_trabajadores') && agro_tabla_existe($conn,'grupo_trabajador_miembros');

function registrar_historico_tarea($conn, $empresa_id, $parcela_id, $usuario_id, $descripcion) {
    if (!agro_tabla_existe($conn, 'historico_actividades')) return;
    $fecha = date('Y-m-d');
    $tipo = 'observacion';
    $st = $conn->prepare('INSERT INTO historico_actividades (empresa_id, parcela_id, usuario_id, tipo, descripcion, fecha, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $st->bind_param('iiisss', $empresa_id, $parcela_id, $usuario_id, $tipo, $descripcion, $fecha);
    $st->execute();
}

function obtener_trabajadores_destino($conn, $empresa_id, $trabajadores_ids, $grupos_ids, $tieneGrupos) {
    $ids = [];
    foreach ($trabajadores_ids as $id) {
        $id = intval($id);
        if ($id > 0) $ids[$id] = true;
    }
    if ($tieneGrupos) {
        foreach ($grupos_ids as $gid) {
            $gid = intval($gid);
            if ($gid <= 0) continue;
            $st = $conn->prepare("SELECT u.id FROM grupo_trabajador_miembros gm INNER JOIN usuarios u ON gm.trabajador_id=u.id WHERE gm.grupo_id=? AND u.empresa_id=? AND u.rol IN ('trabajador','peon')");
            $st->bind_param('ii', $gid, $empresa_id);
            $st->execute();
            $res = $st->get_result();
            while ($r = $res->fetch_assoc()) $ids[intval($r['id'])] = true;
        }
    }
    return array_keys($ids);
}

if (!$sinTabla && isset($_POST['guardar']) && in_array($rol, ['jefe','superadmin','trabajador'])) {
    $empresa_id = ($rol === 'superadmin') ? intval($_POST['empresa_id'] ?? 0) : $eid;
    $parcela_id = intval($_POST['parcela_id'] ?? 0);
    $trabajadores_ids = $_POST['trabajadores_id'] ?? [];
    $grupos_ids = $_POST['grupos_id'] ?? [];
    if (!is_array($trabajadores_ids)) $trabajadores_ids = [$trabajadores_ids];
    if (!is_array($grupos_ids)) $grupos_ids = [$grupos_ids];

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = $_POST['tipo'] ?? 'revision';
    $prioridad = $_POST['prioridad'] ?? 'media';
    $fecha_programada = $_POST['fecha_programada'] ?: null;
    $fecha_limite = $_POST['fecha_limite'] ?: null;

    $destinatarios = obtener_trabajadores_destino($conn, $empresa_id, $trabajadores_ids, $grupos_ids, $tieneGrupos);

    if ($empresa_id <= 0 || $parcela_id <= 0 || empty($destinatarios) || $titulo === '') {
        $error = 'Indica empresa, parcela, al menos un trabajador o grupo, y título de la tarea.';
    } else {
        $chp = $conn->prepare('SELECT id, nombre FROM parcelas WHERE id=? AND empresa_id=?');
        $chp->bind_param('ii', $parcela_id, $empresa_id);
        $chp->execute();
        $parcela = $chp->get_result()->fetch_assoc();

        if (!$parcela) {
            $error = 'La parcela no pertenece a la empresa seleccionada.';
        } else {
            $creadas = 0;
            $nombres = [];
            foreach ($destinatarios as $trabajador_id) {
                $chk = $conn->prepare("SELECT id, nombre FROM usuarios WHERE id=? AND empresa_id=? AND rol IN ('trabajador','peon')");
                $chk->bind_param('ii', $trabajador_id, $empresa_id);
                $chk->execute();
                $trabajador = $chk->get_result()->fetch_assoc();
                if (!$trabajador) continue;

                $responsable = $trabajador['nombre'];
                $st = $conn->prepare("INSERT INTO tareas_agricolas (empresa_id, parcela_id, trabajador_id, creado_por, titulo, descripcion, tipo, prioridad, fecha_programada, fecha_limite, estado, responsable, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, NOW())");
                $st->bind_param('iiiisssssss', $empresa_id, $parcela_id, $trabajador_id, $uid, $titulo, $descripcion, $tipo, $prioridad, $fecha_programada, $fecha_limite, $responsable);
                if ($st->execute()) { $creadas++; $nombres[] = $responsable; }
            }

            if ($creadas > 0) {
                $mensaje = 'Tarea asignada correctamente a '.$creadas.' persona/s.';
                registrar_historico_tarea($conn, $empresa_id, $parcela_id, $uid, 'Tarea creada: '.$titulo.' para '.implode(', ', $nombres));
            } else {
                $error = 'No se pudo asignar la tarea. Revisa que los trabajadores pertenezcan a la empresa.';
            }
        }
    }
}

if (!$sinTabla && isset($_POST['cambiar_estado'])) {
    $tarea_id = intval($_POST['tarea_id'] ?? 0);
    $nuevo_estado = $_POST['nuevo_estado'] ?? 'pendiente';
    $comentario = trim($_POST['comentario'] ?? '');
    $permitidos = ['pendiente','en_proceso','completada','incidencia','cancelada'];

    if (!in_array($nuevo_estado, $permitidos)) {
        $error = 'Estado no válido.';
    } else {
        if ($rol === 'trabajador' || $rol === 'peon') {
            $st = $conn->prepare('SELECT * FROM tareas_agricolas WHERE id=? AND trabajador_id=? AND empresa_id=?');
            $st->bind_param('iii', $tarea_id, $uid, $eid);
        } elseif ($rol === 'jefe') {
            $st = $conn->prepare('SELECT * FROM tareas_agricolas WHERE id=? AND empresa_id=?');
            $st->bind_param('ii', $tarea_id, $eid);
        } else {
            $st = $conn->prepare('SELECT * FROM tareas_agricolas WHERE id=?');
            $st->bind_param('i', $tarea_id);
        }
        $st->execute();
        $tarea = $st->get_result()->fetch_assoc();

        if (!$tarea) {
            $error = 'No tienes permiso para modificar esta tarea.';
        } else {
            $fechaCompletadaSql = ($nuevo_estado === 'completada') ? ', fecha_completada=NOW()' : '';
            $up = $conn->prepare("UPDATE tareas_agricolas SET estado=?, observaciones_trabajador=?, fecha_actualizacion=NOW() $fechaCompletadaSql WHERE id=?");
            $up->bind_param('ssi', $nuevo_estado, $comentario, $tarea_id);
            if ($up->execute()) {
                $mensaje = 'Estado de la tarea actualizado.';
                $texto = 'Tarea actualizada a "'.$nuevo_estado.'": '.$tarea['titulo'];
                if ($comentario !== '') $texto .= '. Comentario: '.$comentario;
                registrar_historico_tarea($conn, intval($tarea['empresa_id']), intval($tarea['parcela_id']), $uid, $texto);
            } else {
                $error = 'No se pudo actualizar la tarea.';
            }
        }
    }
}

$empresas = $parcelas = $trabajadores = $grupos = $tareas = null;
$stats = ['pendiente'=>0,'en_proceso'=>0,'completada'=>0,'incidencia'=>0,'cancelada'=>0,'total'=>0];

if (!$sinTabla) {
    $empresas = agro_empresas_visibles($conn);

    if ($rol === 'superadmin') {
        $parcelas = $conn->query('SELECT id,nombre,empresa_id FROM parcelas ORDER BY nombre ASC');
        $trabajadores = $conn->query("SELECT id,nombre,empresa_id,rol FROM usuarios WHERE rol IN ('trabajador','peon') ORDER BY rol,nombre ASC");
        if ($tieneGrupos) $grupos = $conn->query('SELECT g.id,g.nombre,g.empresa_id,COUNT(gm.trabajador_id) total FROM grupos_trabajadores g LEFT JOIN grupo_trabajador_miembros gm ON g.id=gm.grupo_id GROUP BY g.id ORDER BY g.nombre ASC');
        $tareas = $conn->query("SELECT t.*, p.nombre AS parcela, e.nombre AS empresa, u.nombre AS trabajador, c.nombre AS creador
            FROM tareas_agricolas t
            LEFT JOIN parcelas p ON t.parcela_id=p.id
            LEFT JOIN empresas e ON t.empresa_id=e.id
            LEFT JOIN usuarios u ON t.trabajador_id=u.id
            LEFT JOIN usuarios c ON t.creado_por=c.id
            ORDER BY FIELD(t.estado,'incidencia','pendiente','en_proceso','completada','cancelada'), t.fecha_limite IS NULL, t.fecha_limite ASC, t.id DESC");
        $resStats = $conn->query("SELECT estado, COUNT(*) total FROM tareas_agricolas GROUP BY estado");
    } elseif ($rol === 'jefe') {
        $st = $conn->prepare('SELECT id,nombre,empresa_id FROM parcelas WHERE empresa_id=? ORDER BY nombre ASC'); $st->bind_param('i',$eid); $st->execute(); $parcelas=$st->get_result();
        $tw = $conn->prepare("SELECT id,nombre,empresa_id,rol FROM usuarios WHERE empresa_id=? AND rol IN ('trabajador','peon') ORDER BY rol,nombre ASC"); $tw->bind_param('i',$eid); $tw->execute(); $trabajadores=$tw->get_result();
        if ($tieneGrupos) { $gg=$conn->prepare('SELECT g.id,g.nombre,g.empresa_id,COUNT(gm.trabajador_id) total FROM grupos_trabajadores g LEFT JOIN grupo_trabajador_miembros gm ON g.id=gm.grupo_id WHERE g.empresa_id=? GROUP BY g.id ORDER BY g.nombre ASC'); $gg->bind_param('i',$eid); $gg->execute(); $grupos=$gg->get_result(); }
        $tt = $conn->prepare("SELECT t.*, p.nombre AS parcela, e.nombre AS empresa, u.nombre AS trabajador, c.nombre AS creador
            FROM tareas_agricolas t
            LEFT JOIN parcelas p ON t.parcela_id=p.id
            LEFT JOIN empresas e ON t.empresa_id=e.id
            LEFT JOIN usuarios u ON t.trabajador_id=u.id
            LEFT JOIN usuarios c ON t.creado_por=c.id
            WHERE t.empresa_id=?
            ORDER BY FIELD(t.estado,'incidencia','pendiente','en_proceso','completada','cancelada'), t.fecha_limite IS NULL, t.fecha_limite ASC, t.id DESC");
        $tt->bind_param('i',$eid); $tt->execute(); $tareas=$tt->get_result();
        $rs = $conn->prepare("SELECT estado, COUNT(*) total FROM tareas_agricolas WHERE empresa_id=? GROUP BY estado"); $rs->bind_param('i',$eid); $rs->execute(); $resStats=$rs->get_result();
    } elseif ($rol === 'trabajador' || $rol === 'peon') {
        if ($rol === 'trabajador') {
            $stp = $conn->prepare('SELECT id,nombre,empresa_id FROM parcelas WHERE empresa_id=? ORDER BY nombre ASC');
            $stp->bind_param('i',$eid); $stp->execute(); $parcelas=$stp->get_result();
            $twp = $conn->prepare("SELECT id,nombre,empresa_id,rol FROM usuarios WHERE empresa_id=? AND rol='peon' ORDER BY nombre ASC");
            $twp->bind_param('i',$eid); $twp->execute(); $trabajadores=$twp->get_result();
        }
        $tt = $conn->prepare("SELECT t.*, p.nombre AS parcela, e.nombre AS empresa, u.nombre AS trabajador, c.nombre AS creador
            FROM tareas_agricolas t
            LEFT JOIN parcelas p ON t.parcela_id=p.id
            LEFT JOIN empresas e ON t.empresa_id=e.id
            LEFT JOIN usuarios u ON t.trabajador_id=u.id
            LEFT JOIN usuarios c ON t.creado_por=c.id
            WHERE t.empresa_id=? AND t.trabajador_id=?
            ORDER BY FIELD(t.estado,'incidencia','pendiente','en_proceso','completada','cancelada'), t.fecha_limite IS NULL, t.fecha_limite ASC, t.id DESC");
        $tt->bind_param('ii',$eid,$uid); $tt->execute(); $tareas=$tt->get_result();
        $rs = $conn->prepare("SELECT estado, COUNT(*) total FROM tareas_agricolas WHERE empresa_id=? AND trabajador_id=? GROUP BY estado"); $rs->bind_param('ii',$eid,$uid); $rs->execute(); $resStats=$rs->get_result();
    }

    while ($s = $resStats->fetch_assoc()) { $stats[$s['estado']] = intval($s['total']); $stats['total'] += intval($s['total']); }
}

function badge_estado($estado) {
    $clases = ['pendiente'=>'bg-warning text-dark','en_proceso'=>'bg-primary','completada'=>'bg-success','incidencia'=>'bg-danger','cancelada'=>'bg-secondary'];
    return $clases[$estado] ?? 'bg-secondary';
}
function badge_prioridad($prioridad) {
    $clases = ['baja'=>'bg-success','media'=>'bg-info text-dark','alta'=>'bg-danger'];
    return $clases[$prioridad] ?? 'bg-info text-dark';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tareas - AgroConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero">
        <div><span class="eyebrow">Planificación profesional</span><h1><?php echo in_array($rol, ['trabajador','peon']) ? 'Mis tareas asignadas' : 'Asignación y seguimiento de tareas'; ?></h1><p><?php echo in_array($rol, ['trabajador','peon']) ? 'Consulta las tareas que te han asignado y marca su estado en tiempo real.' : 'Asigna trabajos a empleados, grupos completos, controla estados, incidencias y genera histórico automáticamente.'; ?></p></div>
        <?php if($rol === 'jefe' || $rol === 'superadmin'){ ?><div class="dashboard-actions"><a href="grupos_trabajadores.php" class="btn btn-outline-success">Gestionar grupos</a></div><?php } ?>
    </div>

    <?php if($sinTabla){ ?>
        <div class="alert alert-warning">Importa <strong>actualizacion_superadmin.sql</strong> para activar el gestor profesional de tareas.</div>
    <?php } else { ?>
        <?php if(!$tieneGrupos && in_array($rol,['jefe','superadmin'])){ ?><div class="alert alert-warning">Importa <strong>actualizacion_superadmin.sql</strong> para activar los grupos de trabajadores.</div><?php } ?>
        <?php if($mensaje){ ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php } ?>
        <?php if($error){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <section class="stats-grid mb-4">
            <div class="stat-card"><span>Total tareas</span><strong><?php echo $stats['total']; ?></strong><small>Registradas en el filtro actual</small></div>
            <div class="stat-card"><span>Pendientes</span><strong><?php echo $stats['pendiente']; ?></strong><small>Por comenzar</small></div>
            <div class="stat-card"><span>En proceso</span><strong><?php echo $stats['en_proceso']; ?></strong><small>Trabajo activo</small></div>
            <div class="stat-card"><span>Completadas</span><strong><?php echo $stats['completada']; ?></strong><small>Finalizadas</small></div>
            <div class="stat-card"><span>Incidencias</span><strong><?php echo $stats['incidencia']; ?></strong><small>Requieren revisión</small></div>
        </section>

        <?php if(in_array($rol, ['jefe','superadmin','trabajador'])){ ?>
        <div class="admin-card mb-4">
            <h3>Asignar nueva tarea</h3>
            <p class="text-muted mb-3"><?php echo $rol === 'trabajador' ? 'Puedes asignar tareas a peones de tu empresa.' : 'Puedes elegir trabajadores sueltos, uno o varios grupos, o combinar ambos. Si una persona aparece dos veces, solo se le crea una tarea.'; ?></p>
            <form method="POST" class="row g-3 mt-1">
                <?php if($rol==='superadmin'){ ?>
                <div class="col-md-4"><label class="form-label">Empresa</label><select name="empresa_id" class="form-select" required><option value="">Seleccionar...</option><?php while($e=$empresas->fetch_assoc()){ ?><option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php } ?></select></div>
                <?php } ?>
                <div class="col-md-4"><label class="form-label">Parcela</label><select name="parcela_id" class="form-select" required><option value="">Seleccionar...</option><?php while($p=$parcelas->fetch_assoc()){ ?><option value="<?php echo $p['id']; ?>" data-empresa="<?php echo $p['empresa_id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option><?php } ?></select></div>
                <div class="col-md-4"><label class="form-label">Trabajadores / peones individuales</label><select name="trabajadores_id[]" class="form-select" multiple size="5"><option value="">-- Opcional --</option><?php while($w=$trabajadores->fetch_assoc()){ if($rol==='trabajador' && ($w['rol']??'')!=='peon') continue; ?><option value="<?php echo $w['id']; ?>" data-empresa="<?php echo $w['empresa_id']; ?>"><?php echo htmlspecialchars($w['nombre']); ?><?php echo !empty($w['rol']) ? ' · '.htmlspecialchars($w['rol']) : ''; ?></option><?php } ?></select><small class="text-muted">Mantén Ctrl pulsado para elegir varios.</small></div>
                <div class="col-md-4"><label class="form-label">Grupos de trabajadores</label><select name="grupos_id[]" class="form-select" multiple size="5" <?php if(!$tieneGrupos) echo 'disabled'; ?>><option value="">-- Opcional --</option><?php if($grupos){ while($g=$grupos->fetch_assoc()){ ?><option value="<?php echo $g['id']; ?>" data-empresa="<?php echo $g['empresa_id']; ?>"><?php echo htmlspecialchars($g['nombre']); ?> · <?php echo intval($g['total']); ?> trabajador/es</option><?php }} ?></select><small class="text-muted">Los grupos se gestionan desde “Gestionar grupos”.</small></div>
                <div class="col-md-4"><label class="form-label">Título</label><input name="titulo" class="form-control" placeholder="Ej: Revisar riego sector norte" required></div>
                <div class="col-md-3"><label class="form-label">Tipo</label><select name="tipo" class="form-select"><option value="riego">Riego</option><option value="siembra">Siembra</option><option value="tratamiento">Tratamiento</option><option value="revision">Revisión</option><option value="cosecha">Cosecha</option><option value="otro">Otro</option></select></div>
                <div class="col-md-3"><label class="form-label">Prioridad</label><select name="prioridad" class="form-select"><option value="baja">Baja</option><option value="media" selected>Media</option><option value="alta">Alta</option></select></div>
                <div class="col-md-3"><label class="form-label">Fecha programada</label><input type="date" name="fecha_programada" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Fecha límite</label><input type="date" name="fecha_limite" class="form-control"></div>
                <div class="col-md-12"><label class="form-label">Descripción / instrucciones</label><textarea name="descripcion" class="form-control" rows="3" placeholder="Explica qué debe hacer el trabajador, material necesario, zona exacta, observaciones..."></textarea></div>
                <div class="col-12"><button name="guardar" class="btn btn-success">Asignar tarea</button></div>
            </form>
        </div>
        <?php } ?>

        <div class="task-board">
        <?php if($tareas && $tareas->num_rows > 0){ while($t=$tareas->fetch_assoc()){ ?>
            <article class="task-card" data-task-notify="<?php echo htmlspecialchars($t['estado']); ?>" data-task-priority="<?php echo htmlspecialchars($t['prioridad']); ?>">
                <div class="task-card-header">
                    <div><h3><?php echo htmlspecialchars($t['titulo']); ?></h3><p><?php echo htmlspecialchars($t['descripcion'] ?: 'Sin descripción.'); ?></p></div>
                    <div class="task-badges"><span class="badge <?php echo badge_estado($t['estado']); ?>"><?php echo htmlspecialchars($t['estado']); ?></span><span class="badge <?php echo badge_prioridad($t['prioridad']); ?>">Prioridad <?php echo htmlspecialchars($t['prioridad']); ?></span></div>
                </div>
                <div class="task-meta">
                    <span><strong>Empresa:</strong> <?php echo htmlspecialchars($t['empresa'] ?? ''); ?></span>
                    <span><strong>Parcela:</strong> <?php echo htmlspecialchars($t['parcela'] ?? ''); ?></span>
                    <span><strong>Asignado a:</strong> <?php echo htmlspecialchars($t['trabajador'] ?? $t['responsable'] ?? 'Sin asignar'); ?></span>
                    <span><strong>Tipo:</strong> <?php echo htmlspecialchars($t['tipo']); ?></span>
                    <span><strong>Asignada por:</strong> <?php echo htmlspecialchars($t['creador'] ?? 'Sistema'); ?></span>
                    <span><strong>Programada:</strong> <?php echo htmlspecialchars($t['fecha_programada'] ?: 'Sin fecha'); ?></span>
                    <span><strong>Límite:</strong> <?php echo htmlspecialchars($t['fecha_limite'] ?: 'Sin límite'); ?></span>
                    <?php if(!empty($t['fecha_completada'])){ ?><span><strong>Completada:</strong> <?php echo htmlspecialchars($t['fecha_completada']); ?></span><?php } ?>
                </div>
                <?php if(!empty($t['observaciones_trabajador'])){ ?><div class="task-note"><strong>Observación:</strong> <?php echo htmlspecialchars($t['observaciones_trabajador']); ?></div><?php } ?>
                <form method="POST" class="task-actions">
                    <input type="hidden" name="tarea_id" value="<?php echo $t['id']; ?>">
                    <select name="nuevo_estado" class="form-select">
                        <option value="pendiente" <?php if($t['estado']==='pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="en_proceso" <?php if($t['estado']==='en_proceso') echo 'selected'; ?>>En proceso</option>
                        <option value="completada" <?php if($t['estado']==='completada') echo 'selected'; ?>>Completada</option>
                        <option value="incidencia" <?php if($t['estado']==='incidencia') echo 'selected'; ?>>Incidencia</option>
                        <?php if(!in_array($rol, ['trabajador','peon'])){ ?><option value="cancelada" <?php if($t['estado']==='cancelada') echo 'selected'; ?>>Cancelada</option><?php } ?>
                    </select>
                    <input name="comentario" class="form-control" placeholder="Comentario opcional">
                    <button name="cambiar_estado" class="btn btn-outline-success">Actualizar</button>
                </form>
                <?php if(in_array($rol,['jefe','superadmin'])) echo agro_crud_botones('tareas_agricolas', $t['id'], 'tareas.php'); ?>
            </article>
        <?php }} else { ?>
            <div class="admin-card"><p class="mb-0">No hay tareas para mostrar.</p></div>
        <?php } ?>
        </div>
    <?php } ?>
</div>
<script>
const empresaSelect = document.querySelector('select[name="empresa_id"]');
if (empresaSelect) {
  const filtrar = () => {
    const empresa = empresaSelect.value;
    document.querySelectorAll('select[name="parcela_id"] option[data-empresa], select[name="trabajadores_id[]"] option[data-empresa], select[name="grupos_id[]"] option[data-empresa]').forEach(opt => {
      opt.hidden = empresa && opt.dataset.empresa !== empresa;
    });
  };
  empresaSelect.addEventListener('change', filtrar); filtrar();
}
</script>
<script src="notificaciones.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

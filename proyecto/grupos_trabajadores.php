<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['jefe','superadmin'])) { header('Location: login.php'); exit(); }

$rol = $_SESSION['rol'];
$uid = intval($_SESSION['id'] ?? 0);
$eid = intval($_SESSION['empresa_id'] ?? 0);
$volver = agro_volver_panel();
$mensaje = '';
$error = '';

$sinTabla = !agro_tabla_existe($conn,'grupos_trabajadores') || !agro_tabla_existe($conn,'grupo_trabajador_miembros') || !agro_tabla_existe($conn,'usuarios');

function empresa_permitida_grupo($empresa_id) {
    if (($_SESSION['rol'] ?? '') === 'superadmin') return $empresa_id > 0;
    return $empresa_id === intval($_SESSION['empresa_id'] ?? 0);
}

if (!$sinTabla && isset($_POST['crear_grupo'])) {
    $empresa_id = ($rol === 'superadmin') ? intval($_POST['empresa_id'] ?? 0) : $eid;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $miembros = $_POST['trabajadores'] ?? [];
    if (!is_array($miembros)) $miembros = [];

    if (!empresa_permitida_grupo($empresa_id) || $nombre === '') {
        $error = 'Indica empresa y nombre del grupo.';
    } else {
        $st = $conn->prepare('INSERT INTO grupos_trabajadores (empresa_id, nombre, descripcion, creado_por, fecha_creacion) VALUES (?, ?, ?, ?, NOW())');
        $st->bind_param('issi', $empresa_id, $nombre, $descripcion, $uid);
        if ($st->execute()) {
            $grupo_id = $conn->insert_id;
            $insertados = 0;

            foreach ($miembros as $trabajador_id) {
                $trabajador_id = intval($trabajador_id);
                if ($trabajador_id <= 0) continue;

                // Seguridad: solo permite añadir trabajadores reales de la misma empresa del grupo.
                $chk = $conn->prepare("SELECT id FROM usuarios WHERE id=? AND empresa_id=? AND rol IN ('trabajador','peon')");
                $chk->bind_param('ii', $trabajador_id, $empresa_id);
                $chk->execute();
                if (!$chk->get_result()->fetch_assoc()) continue;

                $ins = $conn->prepare('INSERT IGNORE INTO grupo_trabajador_miembros (grupo_id, trabajador_id, fecha_creacion) VALUES (?, ?, NOW())');
                $ins->bind_param('ii', $grupo_id, $trabajador_id);
                if ($ins->execute() && $ins->affected_rows > 0) $insertados++;
            }

            $mensaje = 'Grupo creado correctamente con ' . $insertados . ' persona/s añadido/s.';
        } else {
            $error = 'No se pudo crear el grupo. Puede que ya exista un grupo con ese nombre en la empresa.';
        }
    }
}

if (!$sinTabla && isset($_POST['editar_grupo'])) {
    $grupo_id = intval($_POST['grupo_id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '') {
        $error = 'El nombre del grupo no puede estar vacío.';
    } else {
        if ($rol === 'jefe') {
            $st = $conn->prepare('UPDATE grupos_trabajadores SET nombre=?, descripcion=? WHERE id=? AND empresa_id=?');
            $st->bind_param('ssii', $nombre, $descripcion, $grupo_id, $eid);
        } else {
            $st = $conn->prepare('UPDATE grupos_trabajadores SET nombre=?, descripcion=? WHERE id=?');
            $st->bind_param('ssi', $nombre, $descripcion, $grupo_id);
        }
        if ($st->execute()) $mensaje = 'Grupo actualizado.'; else $error = 'No se pudo actualizar el grupo.';
    }
}

if (!$sinTabla && isset($_POST['borrar_grupo'])) {
    $grupo_id = intval($_POST['grupo_id'] ?? 0);
    if ($rol === 'jefe') {
        $st = $conn->prepare('DELETE FROM grupos_trabajadores WHERE id=? AND empresa_id=?');
        $st->bind_param('ii', $grupo_id, $eid);
    } else {
        $st = $conn->prepare('DELETE FROM grupos_trabajadores WHERE id=?');
        $st->bind_param('i', $grupo_id);
    }
    if ($st->execute()) $mensaje = 'Grupo eliminado. Las tareas ya creadas no se borran.'; else $error = 'No se pudo eliminar el grupo.';
}

if (!$sinTabla && isset($_POST['guardar_miembros'])) {
    $grupo_id = intval($_POST['grupo_id'] ?? 0);
    $miembros = $_POST['trabajadores'] ?? [];
    if (!is_array($miembros)) $miembros = [];

    if ($rol === 'jefe') {
        $st = $conn->prepare('SELECT id, empresa_id FROM grupos_trabajadores WHERE id=? AND empresa_id=?');
        $st->bind_param('ii', $grupo_id, $eid);
    } else {
        $st = $conn->prepare('SELECT id, empresa_id FROM grupos_trabajadores WHERE id=?');
        $st->bind_param('i', $grupo_id);
    }
    $st->execute();
    $grupo = $st->get_result()->fetch_assoc();

    if (!$grupo) {
        $error = 'No tienes permiso para modificar este grupo.';
    } else {
        $empresa_id = intval($grupo['empresa_id']);
        $del = $conn->prepare('DELETE FROM grupo_trabajador_miembros WHERE grupo_id=?');
        $del->bind_param('i', $grupo_id);
        $del->execute();

        $insertados = 0;
        foreach ($miembros as $trabajador_id) {
            $trabajador_id = intval($trabajador_id);
            if ($trabajador_id <= 0) continue;
            $chk = $conn->prepare("SELECT id FROM usuarios WHERE id=? AND empresa_id=? AND rol IN ('trabajador','peon')");
            $chk->bind_param('ii', $trabajador_id, $empresa_id);
            $chk->execute();
            if (!$chk->get_result()->fetch_assoc()) continue;

            $ins = $conn->prepare('INSERT IGNORE INTO grupo_trabajador_miembros (grupo_id, trabajador_id, fecha_creacion) VALUES (?, ?, NOW())');
            $ins->bind_param('ii', $grupo_id, $trabajador_id);
            if ($ins->execute()) $insertados++;
        }
        $mensaje = 'Miembros del grupo actualizados.';
    }
}

$empresas = $grupos = null;
$trabajadoresPorEmpresa = [];
$miembrosPorGrupo = [];

if (!$sinTabla) {
    $empresas = agro_empresas_visibles($conn);

    if ($rol === 'superadmin') {
        $grupos = $conn->query('SELECT g.*, e.nombre AS empresa, COUNT(gm.trabajador_id) AS total_miembros FROM grupos_trabajadores g LEFT JOIN empresas e ON g.empresa_id=e.id LEFT JOIN grupo_trabajador_miembros gm ON g.id=gm.grupo_id GROUP BY g.id ORDER BY e.nombre ASC, g.nombre ASC');
        $trabajadores = $conn->query("SELECT id,nombre,email,empresa_id FROM usuarios WHERE rol IN ('trabajador','peon') ORDER BY nombre ASC");
    } else {
        $gg = $conn->prepare('SELECT g.*, e.nombre AS empresa, COUNT(gm.trabajador_id) AS total_miembros FROM grupos_trabajadores g LEFT JOIN empresas e ON g.empresa_id=e.id LEFT JOIN grupo_trabajador_miembros gm ON g.id=gm.grupo_id WHERE g.empresa_id=? GROUP BY g.id ORDER BY g.nombre ASC');
        $gg->bind_param('i', $eid); $gg->execute(); $grupos = $gg->get_result();
        $tw = $conn->prepare("SELECT id,nombre,email,empresa_id FROM usuarios WHERE empresa_id=? AND rol IN ('trabajador','peon') ORDER BY nombre ASC");
        $tw->bind_param('i', $eid); $tw->execute(); $trabajadores = $tw->get_result();
    }

    while ($t = $trabajadores->fetch_assoc()) {
        $trabajadoresPorEmpresa[intval($t['empresa_id'])][] = $t;
    }
    $resMiembros = $conn->query('SELECT grupo_id, trabajador_id FROM grupo_trabajador_miembros');
    if ($resMiembros) while ($m = $resMiembros->fetch_assoc()) $miembrosPorGrupo[intval($m['grupo_id'])][] = intval($m['trabajador_id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Grupos de trabajadores - AgroConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero">
        <div><span class="eyebrow">Organización de equipos</span><h1>Grupos de trabajadores</h1><p>Crea cuadrillas, equipos por zona, turnos o especialidades. Los grupos se pueden editar o borrar sin afectar a las tareas ya asignadas.</p></div>
        <div class="dashboard-actions"><a href="tareas.php" class="btn btn-success">Asignar tareas</a></div>
    </div>

    <?php if($sinTabla){ ?>
        <div class="alert alert-warning">Importa <strong>actualizacion_superadmin.sql</strong> para activar los grupos de trabajadores.</div>
    <?php } else { ?>
        <?php if($mensaje){ ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php } ?>
        <?php if($error){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>

        <div class="admin-card mb-4">
            <h3>Crear nuevo grupo</h3>
            <p class="text-muted mb-3">Crea el grupo y añade directamente los trabajadores ya creados que formarán parte de él.</p>
            <form method="POST" class="row g-3 mt-1" id="formCrearGrupo">
                <?php if($rol==='superadmin'){ ?>
                <div class="col-md-4"><label class="form-label">Empresa</label><select name="empresa_id" id="empresaCrearGrupo" class="form-select" required><option value="">Seleccionar...</option><?php mysqli_data_seek($empresas, 0); while($e=$empresas->fetch_assoc()){ ?><option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php } ?></select></div>
                <?php } ?>
                <div class="col-md-4"><label class="form-label">Nombre del grupo</label><input name="nombre" class="form-control" placeholder="Ej: Equipo riego mañana" required></div>
                <div class="col-md-4"><label class="form-label">Descripción</label><input name="descripcion" class="form-control" placeholder="Ej: Trabajadores responsables del riego"></div>

                <div class="col-12">
                    <label class="form-label">Trabajadores/peones a añadir</label>
                    <?php
                    $hayTrabajadores = false;
                    foreach($trabajadoresPorEmpresa as $listaTmp){ if(count($listaTmp)>0){ $hayTrabajadores=true; break; } }
                    ?>
                    <?php if($hayTrabajadores){ ?>
                        <div class="members-grid create-members-grid" id="trabajadoresCrearGrupo">
                            <?php foreach($trabajadoresPorEmpresa as $empresaTmp => $listaTrabajadores){ foreach($listaTrabajadores as $t){ $tid=intval($t['id']); ?>
                                <label class="member-check create-member-option" data-empresa="<?php echo intval($empresaTmp); ?>">
                                    <input type="checkbox" name="trabajadores[]" value="<?php echo $tid; ?>">
                                    <span><strong><?php echo htmlspecialchars($t['nombre']); ?></strong><small><?php echo htmlspecialchars($t['email']); ?></small></span>
                                </label>
                            <?php }} ?>
                        </div>
                        <?php if($rol==='superadmin'){ ?><small class="text-muted d-block mt-2">Selecciona primero una empresa para ver sus trabajadores.</small><?php } ?>
                    <?php } else { ?>
                        <p class="text-muted mb-0">Todavía no hay trabajadores o peones creados para añadir a grupos.</p>
                    <?php } ?>
                </div>

                <div class="col-12"><button name="crear_grupo" class="btn btn-success">Crear grupo con trabajadores seleccionados</button></div>
            </form>
        </div>

        <?php if($grupos && $grupos->num_rows > 0){ while($g=$grupos->fetch_assoc()){ $gid=intval($g['id']); $empresaGrupo=intval($g['empresa_id']); $miembrosActuales=$miembrosPorGrupo[$gid] ?? []; $trabajadoresEmpresa=$trabajadoresPorEmpresa[$empresaGrupo] ?? []; ?>
            <article class="admin-card group-card mb-4">
                <div class="task-card-header">
                    <div>
                        <h3><?php echo htmlspecialchars($g['nombre']); ?> <span class="badge bg-success"><?php echo intval($g['total_miembros']); ?> miembros</span></h3>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($g['descripcion'] ?: 'Sin descripción.'); ?></p>
                        <small><strong>Empresa:</strong> <?php echo htmlspecialchars($g['empresa'] ?? ''); ?></small>
                    </div>
                    <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este grupo? Las tareas ya creadas seguirán existiendo.');">
                        <input type="hidden" name="grupo_id" value="<?php echo $gid; ?>">
                        <button name="borrar_grupo" class="btn btn-outline-danger">Eliminar grupo</button>
                    </form>
                </div>

                <form method="POST" class="row g-3 mt-2">
                    <input type="hidden" name="grupo_id" value="<?php echo $gid; ?>">
                    <div class="col-md-4"><label class="form-label">Nombre</label><input name="nombre" value="<?php echo htmlspecialchars($g['nombre']); ?>" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Descripción</label><input name="descripcion" value="<?php echo htmlspecialchars($g['descripcion'] ?? ''); ?>" class="form-control"></div>
                    <div class="col-md-2 d-flex align-items-end"><button name="editar_grupo" class="btn btn-outline-success w-100">Guardar</button></div>
                </form>

                <form method="POST" class="mt-3">
                    <input type="hidden" name="grupo_id" value="<?php echo $gid; ?>">
                    <label class="form-label">Trabajadores/peones del grupo</label>
                    <?php if(count($trabajadoresEmpresa) > 0){ ?>
                        <div class="members-grid">
                            <?php foreach($trabajadoresEmpresa as $t){ $tid=intval($t['id']); ?>
                                <label class="member-check">
                                    <input type="checkbox" name="trabajadores[]" value="<?php echo $tid; ?>" <?php if(in_array($tid,$miembrosActuales)) echo 'checked'; ?>>
                                    <span><strong><?php echo htmlspecialchars($t['nombre']); ?></strong><small><?php echo htmlspecialchars($t['email']); ?></small></span>
                                </label>
                            <?php } ?>
                        </div>
                        <button name="guardar_miembros" class="btn btn-success mt-3">Guardar miembros</button>
                    <?php } else { ?>
                        <p class="text-muted mb-0">Esta empresa todavía no tiene trabajadores o peones.</p>
                    <?php } ?>
                </form>
            </article>
        <?php }} else { ?>
            <div class="admin-card"><p class="mb-0">Todavía no hay grupos creados.</p></div>
        <?php } ?>
    <?php } ?>
</div>
<script>
(function(){
    const empresaSelect = document.getElementById('empresaCrearGrupo');
    const opciones = document.querySelectorAll('.create-member-option');

    function filtrarTrabajadoresCrearGrupo(){
        if(!opciones.length) return;

        // Si es jefe no hay selector de empresa: se muestran todos sus trabajadores.
        if(!empresaSelect){
            opciones.forEach(op => op.style.display = 'flex');
            return;
        }

        const empresaId = empresaSelect.value;
        opciones.forEach(op => {
            const visible = empresaId && op.dataset.empresa === empresaId;
            op.style.display = visible ? 'flex' : 'none';
            if(!visible){
                const check = op.querySelector('input[type="checkbox"]');
                if(check) check.checked = false;
            }
        });
    }

    if(empresaSelect){
        empresaSelect.addEventListener('change', filtrarTrabajadoresCrearGrupo);
    }
    filtrarTrabajadoresCrearGrupo();
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>


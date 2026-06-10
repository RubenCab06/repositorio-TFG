<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';
if (!isset($_SESSION['rol'])) { header('Location: login.php'); exit(); }

$rol = $_SESSION['rol'];
$volver = agro_volver_panel();
$sinTabla = !agro_tabla_existe($conn, 'cultivos') || !agro_tabla_existe($conn, 'parcelas');
$tieneCoordenadas = !$sinTabla && agro_columna_existe($conn, 'parcelas', 'latitud') && agro_columna_existe($conn, 'parcelas', 'longitud');
$mensaje = '';
$error = '';
$eid = intval($_SESSION['empresa_id'] ?? 0);

if (!$sinTabla && isset($_POST['guardar'])) {
    $parcela_id = intval($_POST['parcela_id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $variedad = trim($_POST['variedad'] ?? '');
    $fecha_siembra = $_POST['fecha_siembra'] ?: null;
    $fecha_cosecha = $_POST['fecha_cosecha'] ?: null;
    $estado = $_POST['estado'] ?? 'crecimiento';
    $empresa_id = ($rol === 'superadmin') ? intval($_POST['empresa_id'] ?? 0) : $eid;

    if ($nombre === '' || $empresa_id <= 0 || $parcela_id <= 0) {
        $error = 'Indica empresa, parcela y cultivo.';
    } else {
        $st = $conn->prepare('INSERT INTO cultivos (empresa_id,parcela_id,nombre,variedad,fecha_siembra,fecha_cosecha_prevista,estado,fecha_creacion) VALUES (?,?,?,?,?,?,?,NOW())');
        $st->bind_param('iisssss', $empresa_id, $parcela_id, $nombre, $variedad, $fecha_siembra, $fecha_cosecha, $estado);
        $mensaje = $st->execute() ? 'Cultivo guardado correctamente.' : 'No se pudo guardar el cultivo.';
    }
}

if (!$sinTabla) {
    $empresas = agro_empresas_visibles($conn);
    $coordSelect = $tieneCoordenadas ? ', p.latitud, p.longitud' : ', NULL AS latitud, NULL AS longitud';
    if ($rol === 'superadmin') {
        $parcelas = $conn->query('SELECT id,nombre,empresa_id FROM parcelas ORDER BY nombre ASC');
        $cultivos = $conn->query("SELECT c.*, p.nombre AS parcela, p.ubicacion AS parcela_ubicacion $coordSelect, e.nombre AS empresa FROM cultivos c LEFT JOIN parcelas p ON c.parcela_id=p.id LEFT JOIN empresas e ON c.empresa_id=e.id ORDER BY c.id DESC");
    } else {
        $st = $conn->prepare('SELECT id,nombre,empresa_id FROM parcelas WHERE empresa_id=? ORDER BY nombre ASC');
        $st->bind_param('i', $eid);
        $st->execute();
        $parcelas = $st->get_result();
        $st2 = $conn->prepare("SELECT c.*, p.nombre AS parcela, p.ubicacion AS parcela_ubicacion $coordSelect, e.nombre AS empresa FROM cultivos c LEFT JOIN parcelas p ON c.parcela_id=p.id LEFT JOIN empresas e ON c.empresa_id=e.id WHERE c.empresa_id=? ORDER BY c.id DESC");
        $st2->bind_param('i', $eid);
        $st2->execute();
        $cultivos = $st2->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cultivos - AgroConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero"><div><span class="eyebrow">Producción</span><h1>Control del estado de los cultivos</h1><p>Asocia cultivos a parcelas y consulta el tiempo real de la zona exacta donde está cada cultivo.</p></div></div>
    <?php if($sinTabla){ ?>
        <div class="alert alert-warning">Importa <strong>actualizacion_superadmin.sql</strong> para activar cultivos y parcelas.</div>
    <?php } else { ?>
        <?php if(!$tieneCoordenadas){ ?><div class="alert alert-warning">Importa de nuevo <strong>actualizacion_superadmin.sql</strong> para añadir coordenadas a las parcelas y activar el tiempo por cultivo.</div><?php } ?>
        <?php if($mensaje){ ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php } ?>
        <?php if($error){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <div class="admin-card mb-4">
            <h3>Nuevo cultivo</h3>
            <form method="POST" class="row g-3 mt-1">
                <?php if($rol==='superadmin'){ ?><div class="col-md-6"><label class="form-label">Empresa</label><select name="empresa_id" class="form-select"><option value="">Seleccionar...</option><?php while($e=$empresas->fetch_assoc()){ ?><option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php } ?></select></div><?php } ?>
                <div class="col-md-6"><label class="form-label">Parcela</label><select name="parcela_id" class="form-select" required><option value="">Seleccionar...</option><?php while($p=$parcelas->fetch_assoc()){ ?><option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option><?php } ?></select></div>
                <div class="col-md-4"><label class="form-label">Cultivo</label><input name="nombre" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Variedad</label><input name="variedad" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="siembra">Siembra</option><option value="crecimiento">Crecimiento</option><option value="cosecha">Cosecha</option><option value="finalizado">Finalizado</option></select></div>
                <div class="col-md-6"><label class="form-label">Fecha de siembra</label><input type="date" name="fecha_siembra" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Cosecha prevista</label><input type="date" name="fecha_cosecha" class="form-control"></div>
                <div class="col-12"><button name="guardar" class="btn btn-success">Guardar cultivo</button></div>
            </form>
        </div>
        <div class="admin-card">
            <h3>Cultivos registrados</h3>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Cultivo</th><th>Empresa</th><th>Parcela</th><th>Variedad</th><th>Siembra</th><th>Cosecha prevista</th><th>Estado</th><th>Tiempo del cultivo</th><?php if(in_array($rol,['superadmin','jefe'])){ ?><th>Acciones</th><?php } ?></tr></thead><tbody>
            <?php while($c=$cultivos->fetch_assoc()){ $lat=$c['latitud'] ?? null; $lon=$c['longitud'] ?? null; ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['empresa'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($c['parcela'] ?? ''); ?><br><small class="text-muted"><?php echo htmlspecialchars($c['parcela_ubicacion'] ?? ''); ?></small></td>
                    <td><?php echo htmlspecialchars($c['variedad']); ?></td>
                    <td><?php echo htmlspecialchars($c['fecha_siembra']); ?></td>
                    <td><?php echo htmlspecialchars($c['fecha_cosecha_prevista']); ?></td>
                    <td><span class="badge bg-success"><?php echo htmlspecialchars($c['estado']); ?></span></td>
                    <td>
                        <?php if($tieneCoordenadas && $lat !== null && $lon !== null){ ?>
                            <div class="crop-weather" data-lat="<?php echo htmlspecialchars($lat); ?>" data-lon="<?php echo htmlspecialchars($lon); ?>" data-place="<?php echo htmlspecialchars($c['parcela'] ?? 'Parcela'); ?>">
                                <span class="crop-weather-icon">🌤️</span>
                                <strong>Cargando...</strong>
                                <small>Tiempo de la parcela</small>
                            </div>
                        <?php } else { ?>
                            <span class="text-muted">Añade coordenadas a la parcela</span>
                        <?php } ?>
                    </td>
                    <?php if(in_array($rol,['superadmin','jefe'])){ ?><td><?php echo agro_crud_botones('cultivos', $c['id'], 'cultivos.php'); ?></td><?php } ?>
                </tr>
            <?php } ?>
            </tbody></table></div>
        </div>
    <?php } ?>
</div>
<script src="tiempo_cultivos.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

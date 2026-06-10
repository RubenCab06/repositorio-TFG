<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';
if (!isset($_SESSION['rol'])) { header('Location: login.php'); exit(); }
$rol = $_SESSION['rol'];
$volver = agro_volver_panel();
$sinTabla = !agro_tabla_existe($conn, 'parcelas');
$tieneCoordenadas = !$sinTabla && agro_columna_existe($conn, 'parcelas', 'latitud') && agro_columna_existe($conn, 'parcelas', 'longitud');
$mensaje=''; $error='';

if (!$sinTabla && isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $hectareas = floatval($_POST['hectareas'] ?? 0);
    $tipo_suelo = trim($_POST['tipo_suelo'] ?? '');
    $estado = $_POST['estado'] ?? 'activa';
    $latitud = ($_POST['latitud'] ?? '') !== '' ? floatval($_POST['latitud']) : null;
    $longitud = ($_POST['longitud'] ?? '') !== '' ? floatval($_POST['longitud']) : null;
    $empresa_id = ($rol === 'superadmin') ? intval($_POST['empresa_id'] ?? 0) : intval($_SESSION['empresa_id'] ?? 0);

    if ($nombre === '' || $empresa_id <= 0) {
        $error = 'Indica nombre y empresa.';
    } elseif ($tieneCoordenadas && (($latitud !== null && ($latitud < -90 || $latitud > 90)) || ($longitud !== null && ($longitud < -180 || $longitud > 180)))) {
        $error = 'Las coordenadas no son válidas.';
    } else {
        if ($tieneCoordenadas) {
            $st = $conn->prepare('INSERT INTO parcelas (empresa_id,nombre,ubicacion,latitud,longitud,hectareas,tipo_suelo,estado,fecha_creacion) VALUES (?,?,?,?,?,?,?,?,NOW())');
            $st->bind_param('issdddss', $empresa_id, $nombre, $ubicacion, $latitud, $longitud, $hectareas, $tipo_suelo, $estado);
        } else {
            $st = $conn->prepare('INSERT INTO parcelas (empresa_id,nombre,ubicacion,hectareas,tipo_suelo,estado,fecha_creacion) VALUES (?,?,?,?,?,?,NOW())');
            $st->bind_param('issdss', $empresa_id, $nombre, $ubicacion, $hectareas, $tipo_suelo, $estado);
        }
        $mensaje = $st->execute() ? 'Parcela guardada correctamente.' : 'No se pudo guardar la parcela.';
    }
}

$empresas = !$sinTabla ? agro_empresas_visibles($conn) : false;
if (!$sinTabla) {
    if ($rol === 'superadmin') {
        $parcelas = $conn->query("SELECT p.*, e.nombre AS empresa FROM parcelas p LEFT JOIN empresas e ON p.empresa_id=e.id ORDER BY p.id DESC");
    } else {
        $eid = intval($_SESSION['empresa_id'] ?? 0);
        $st = $conn->prepare("SELECT p.*, e.nombre AS empresa FROM parcelas p LEFT JOIN empresas e ON p.empresa_id=e.id WHERE p.empresa_id=? ORDER BY p.id DESC");
        $st->bind_param('i', $eid);
        $st->execute();
        $parcelas = $st->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parcelas - AgroConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero"><div><span class="eyebrow">Explotaciones</span><h1>Registro y consulta de parcelas</h1><p>Controla cada parcela, su ubicación, coordenadas para el tiempo, superficie, tipo de suelo y estado.</p></div></div>
    <?php if($sinTabla){ ?>
        <div class="alert alert-warning">Importa <strong>actualizacion_superadmin.sql</strong> para activar parcelas.</div>
    <?php } else { ?>
        <?php if(!$tieneCoordenadas){ ?><div class="alert alert-warning">Importa de nuevo <strong>actualizacion_superadmin.sql</strong> para añadir latitud y longitud a las parcelas.</div><?php } ?>
        <?php if($mensaje){ ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php } ?>
        <?php if($error){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
        <div class="dashboard-grid two-columns">
            <div class="admin-card">
                <h3>Nueva parcela</h3>
                <form method="POST" class="row g-3 mt-1">
                    <?php if($rol==='superadmin'){ ?><div class="col-md-12"><label class="form-label">Empresa</label><select name="empresa_id" class="form-select" required><option value="">Seleccionar...</option><?php while($e=$empresas->fetch_assoc()){ ?><option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php } ?></select></div><?php } ?>
                    <div class="col-md-6"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Ubicación</label><input name="ubicacion" class="form-control" placeholder="Ej: Finca norte, Sevilla"></div>
                    <?php if($tieneCoordenadas){ ?>
                    <div class="col-md-6"><label class="form-label">Latitud</label><input type="number" step="0.00000001" name="latitud" class="form-control" placeholder="Ej: 37.3891"></div>
                    <div class="col-md-6"><label class="form-label">Longitud</label><input type="number" step="0.00000001" name="longitud" class="form-control" placeholder="Ej: -5.9845"></div>
                    <?php } ?>
                    <div class="col-md-4"><label class="form-label">Hectáreas</label><input type="number" step="0.01" name="hectareas" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Tipo de suelo</label><input name="tipo_suelo" class="form-control" placeholder="Arcilloso, arenoso..."></div>
                    <div class="col-md-4"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="activa">Activa</option><option value="en_revision">En revisión</option><option value="inactiva">Inactiva</option></select></div>
                    <div class="col-12"><button name="guardar" class="btn btn-success">Guardar parcela</button></div>
                </form>
            </div>
            <div class="admin-card"><h3>Tiempo por cultivo</h3><ul class="clean-list"><li>Rellena latitud y longitud de la parcela.</li><li>Los cultivos asociados mostrarán el tiempo real de esa zona.</li><li>Si no hay coordenadas, se indicará que falta ubicación.</li></ul></div>
        </div>
        <div class="admin-card mt-4"><h3>Parcelas registradas</h3><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Parcela</th><th>Empresa</th><th>Ubicación</th><?php if($tieneCoordenadas){ ?><th>Coordenadas</th><?php } ?><th>Ha</th><th>Suelo</th><th>Estado</th><?php if(in_array($rol,['superadmin','jefe'])){ ?><th>Acciones</th><?php } ?></tr></thead><tbody><?php while($p=$parcelas->fetch_assoc()){ ?><tr><td><strong><?php echo htmlspecialchars($p['nombre']); ?></strong></td><td><?php echo htmlspecialchars($p['empresa']??''); ?></td><td><?php echo htmlspecialchars($p['ubicacion']); ?></td><?php if($tieneCoordenadas){ ?><td><?php echo ($p['latitud']!==null && $p['longitud']!==null) ? htmlspecialchars($p['latitud'].', '.$p['longitud']) : '<span class="text-muted">Sin coordenadas</span>'; ?></td><?php } ?><td><?php echo htmlspecialchars($p['hectareas']); ?></td><td><?php echo htmlspecialchars($p['tipo_suelo']); ?></td><td><span class="badge bg-success"><?php echo htmlspecialchars($p['estado']); ?></span></td><?php if(in_array($rol,['superadmin','jefe'])){ ?><td><?php echo agro_crud_botones('parcelas', $p['id'], 'parcelas.php'); ?></td><?php } ?></tr><?php } ?></tbody></table></div></div>
    <?php } ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

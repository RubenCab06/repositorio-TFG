<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';
if (!isset($_SESSION['rol'])) { header('Location: login.php'); exit(); }
$rol = $_SESSION['rol'];
$eid = intval($_SESSION['empresa_id'] ?? 0);
$sinTabla = !agro_tabla_existe($conn,'parcelas') || !agro_columna_existe($conn,'parcelas','latitud') || !agro_columna_existe($conn,'parcelas','longitud');
$parcelas = [];
if (!$sinTabla) {
    if ($rol === 'superadmin') {
        $sql = "SELECT p.id,p.nombre,p.ubicacion,p.latitud,p.longitud,p.hectareas,p.estado,e.nombre AS empresa,
                       GROUP_CONCAT(c.nombre SEPARATOR ', ') AS cultivos
                FROM parcelas p
                LEFT JOIN empresas e ON p.empresa_id=e.id
                LEFT JOIN cultivos c ON c.parcela_id=p.id
                WHERE p.latitud IS NOT NULL AND p.longitud IS NOT NULL
                GROUP BY p.id
                ORDER BY p.nombre ASC";
        $res = $conn->query($sql);
    } else {
        $sql = "SELECT p.id,p.nombre,p.ubicacion,p.latitud,p.longitud,p.hectareas,p.estado,e.nombre AS empresa,
                       GROUP_CONCAT(c.nombre SEPARATOR ', ') AS cultivos
                FROM parcelas p
                LEFT JOIN empresas e ON p.empresa_id=e.id
                LEFT JOIN cultivos c ON c.parcela_id=p.id
                WHERE p.empresa_id=? AND p.latitud IS NOT NULL AND p.longitud IS NOT NULL
                GROUP BY p.id
                ORDER BY p.nombre ASC";
        $st = $conn->prepare($sql); $st->bind_param('i',$eid); $st->execute(); $res = $st->get_result();
    }
    while($r=$res->fetch_assoc()) { $parcelas[] = $r; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mapa agrícola - AgroConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero">
        <div><span class="eyebrow">Geolocalización agrícola</span><h1>Mapa de parcelas</h1><p>Visualiza las parcelas con OpenStreetMap, consulta cultivos, tiempo actual y distancia aproximada desde tu ubicación.</p></div>
        <div class="dashboard-actions"><a href="parcelas.php" class="btn btn-outline-success">Gestionar parcelas</a><button type="button" id="notifyButton" class="btn btn-success">Activar notificaciones</button></div>
    </div>
    <?php if($sinTabla){ ?>
        <div class="alert alert-warning">Para usar el mapa necesitas que la tabla <strong>parcelas</strong> tenga las columnas <strong>latitud</strong> y <strong>longitud</strong>.</div>
    <?php } else { ?>
        <div class="admin-card map-card">
            <div class="section-title-row"><div><h3>Parcelas geolocalizadas</h3><p>Haz clic en cada marcador para ver información de la parcela.</p></div><span class="badge bg-success"><?php echo count($parcelas); ?> parcelas con coordenadas</span></div>
            <div id="parcelMap" class="parcel-map"></div>
            <small class="text-muted d-block mt-3" id="mapUserNote">Puedes permitir ubicación para calcular distancias hasta cada parcela.</small>
        </div>
    <?php } ?>
</div>
<script>
window.AGRO_PARCELAS = <?php echo json_encode($parcelas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="mapa_parcelas.js"></script>
<script src="notificaciones.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'trabajador') { header('Location: login.php'); exit(); }

$uid = intval($_SESSION['id'] ?? 0);
$eid = intval($_SESSION['empresa_id'] ?? 0);
$pendientes = $proceso = $completadas = $incidencias = 0;
$proximas = null;

if (agro_tabla_existe($conn,'tareas_agricolas') && agro_columna_existe($conn,'tareas_agricolas','trabajador_id')) {
    $st = $conn->prepare("SELECT estado, COUNT(*) total FROM tareas_agricolas WHERE empresa_id=? AND trabajador_id=? GROUP BY estado");
    $st->bind_param('ii',$eid,$uid); $st->execute(); $rs=$st->get_result();
    while($r=$rs->fetch_assoc()){
        if($r['estado']==='pendiente') $pendientes=intval($r['total']);
        if($r['estado']==='en_proceso') $proceso=intval($r['total']);
        if($r['estado']==='completada') $completadas=intval($r['total']);
        if($r['estado']==='incidencia') $incidencias=intval($r['total']);
    }
    $tp = $conn->prepare("SELECT t.titulo,t.estado,t.fecha_limite,p.nombre parcela FROM tareas_agricolas t LEFT JOIN parcelas p ON t.parcela_id=p.id WHERE t.empresa_id=? AND t.trabajador_id=? AND t.estado IN ('pendiente','en_proceso','incidencia') ORDER BY FIELD(t.estado,'incidencia','pendiente','en_proceso'), t.fecha_limite IS NULL, t.fecha_limite ASC LIMIT 5");
    $tp->bind_param('ii',$eid,$uid); $tp->execute(); $proximas=$tp->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title>Panel Trabajador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero"><div><span class="eyebrow">Panel trabajador</span><h1>Trabajo de campo</h1><p>Bienvenido <?php echo htmlspecialchars($_SESSION['nombre']); ?>. Consulta tus tareas, marca avances, comunica incidencias y revisa cultivos.</p></div><div class="dashboard-actions"><a href="tareas.php" class="btn btn-success">Ver mis tareas</a><a href="incidencias.php" class="btn btn-outline-success">Reportar incidencia</a></div></div>

    <?php include 'tiempo_widget.php'; ?>

    <section class="stats-grid mt-4">
        <div class="stat-card"><span>Pendientes</span><strong><?php echo $pendientes; ?></strong><small>Tareas asignadas</small></div>
        <div class="stat-card"><span>En proceso</span><strong><?php echo $proceso; ?></strong><small>Trabajos iniciados</small></div>
        <div class="stat-card"><span>Completadas</span><strong><?php echo $completadas; ?></strong><small>Histórico automático</small></div>
        <div class="stat-card"><span>Incidencias</span><strong><?php echo $incidencias; ?></strong><small>Necesitan revisión</small></div>
    </section>

    <div class="admin-card mt-4">
        <h3>Próximas tareas</h3>
        <?php if($proximas && $proximas->num_rows>0){ ?>
            <div class="table-responsive"><table class="table"><thead><tr><th>Tarea</th><th>Parcela</th><th>Fecha límite</th><th>Estado</th></tr></thead><tbody>
            <?php while($t=$proximas->fetch_assoc()){ ?><tr><td><strong><?php echo htmlspecialchars($t['titulo']); ?></strong></td><td><?php echo htmlspecialchars($t['parcela'] ?? ''); ?></td><td><?php echo htmlspecialchars($t['fecha_limite'] ?: 'Sin límite'); ?></td><td><?php echo htmlspecialchars($t['estado']); ?></td></tr><?php } ?>
            </tbody></table></div>
        <?php } else { ?><p class="text-muted mb-0">No tienes tareas pendientes.</p><?php } ?>
    </div>

    <section class="quick-grid mt-4">
        <a href="tareas.php" class="quick-card"><strong>Mis tareas</strong><span>Marca como en proceso, completada o incidencia.</span></a>
        <a href="parcelas.php" class="quick-card"><strong>Parcelas</strong><span>Consulta la información de campo.</span></a>
        <a href="cultivos.php" class="quick-card"><strong>Cultivos</strong><span>Revisa cultivos y tiempo de cada parcela.</span></a>
        <a href="mapa_parcelas.php" class="quick-card"><strong>Mapa agrícola</strong><span>Visualiza parcelas geolocalizadas, distancia y tiempo de cada zona.</span></a><a href="historico.php" class="quick-card"><strong>Histórico</strong><span>Consulta trabajos realizados.</span></a>
        <a href="incidencias.php" class="quick-card"><strong>Incidencias</strong><span>Comunica problemas al jefe o soporte.</span></a>
        <a href="foro.php" class="quick-card"><strong>Foro</strong><span>Dudas entre empresas y soporte.</span></a>
    </section>
</div>
<script src="notificaciones.js"></script><?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

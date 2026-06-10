<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'peon') { header('Location: login.php'); exit(); }
$uid = intval($_SESSION['id'] ?? 0);
$eid = intval($_SESSION['empresa_id'] ?? 0);
$nombre = $_SESSION['nombre'] ?? 'Peón';

$stats = ['pendiente'=>0,'en_proceso'=>0,'completada'=>0,'incidencia'=>0,'total'=>0];
$tareas = null; $grupos = null;
if (agro_tabla_existe($conn,'tareas_agricolas')) {
    $st = $conn->prepare("SELECT t.*, p.nombre AS parcela, p.ubicacion, u.nombre AS creador
        FROM tareas_agricolas t
        LEFT JOIN parcelas p ON t.parcela_id=p.id
        LEFT JOIN usuarios u ON t.creado_por=u.id
        WHERE t.trabajador_id=? AND t.empresa_id=?
        ORDER BY FIELD(t.estado,'incidencia','pendiente','en_proceso','completada','cancelada'), t.fecha_limite IS NULL, t.fecha_limite ASC, t.id DESC
        LIMIT 12");
    $st->bind_param('ii',$uid,$eid); $st->execute(); $tareas=$st->get_result();
    $rs=$conn->prepare("SELECT estado, COUNT(*) total FROM tareas_agricolas WHERE trabajador_id=? AND empresa_id=? GROUP BY estado");
    $rs->bind_param('ii',$uid,$eid); $rs->execute(); $rres=$rs->get_result();
    while($r=$rres->fetch_assoc()){ $stats[$r['estado']]=intval($r['total']); $stats['total']+=intval($r['total']); }
}
if (agro_tabla_existe($conn,'grupos_trabajadores') && agro_tabla_existe($conn,'grupo_trabajador_miembros')) {
    $stg=$conn->prepare("SELECT g.nombre, g.descripcion, COUNT(gm2.trabajador_id) AS miembros
        FROM grupo_trabajador_miembros gm
        INNER JOIN grupos_trabajadores g ON gm.grupo_id=g.id
        LEFT JOIN grupo_trabajador_miembros gm2 ON gm2.grupo_id=g.id
        WHERE gm.trabajador_id=? AND g.empresa_id=?
        GROUP BY g.id
        ORDER BY g.nombre ASC");
    $stg->bind_param('ii',$uid,$eid); $stg->execute(); $grupos=$stg->get_result();
}
function badge_estado_peon($estado){ $c=['pendiente'=>'bg-warning text-dark','en_proceso'=>'bg-primary','completada'=>'bg-success','incidencia'=>'bg-danger','cancelada'=>'bg-secondary']; return $c[$estado] ?? 'bg-secondary'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Peón - AgroConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container">
    <div class="dashboard-hero">
        <div>
            <span class="eyebrow">Panel de trabajo</span>
            <h1>Hola, <?php echo htmlspecialchars($nombre); ?></h1>
            <p>Consulta tus tareas asignadas, quién te las ha mandado, tus grupos de trabajo y el tiempo real de tu zona.</p>
        </div>
        <div class="dashboard-actions"><a href="tareas.php" class="btn btn-success">Ver mis tareas</a><button type="button" id="notifyButton" class="btn btn-outline-success">Activar notificaciones</button></div>
    </div>

    <?php include 'tiempo_widget.php'; ?>

    <section class="stats-grid mb-4">
        <div class="stat-card"><span>Total tareas</span><strong><?php echo $stats['total']; ?></strong><small>Asignadas a ti</small></div>
        <div class="stat-card"><span>Pendientes</span><strong><?php echo $stats['pendiente']; ?></strong><small>Por comenzar</small></div>
        <div class="stat-card"><span>En proceso</span><strong><?php echo $stats['en_proceso']; ?></strong><small>Trabajo activo</small></div>
        <div class="stat-card"><span>Completadas</span><strong><?php echo $stats['completada']; ?></strong><small>Finalizadas</small></div>
        <div class="stat-card"><span>Incidencias</span><strong><?php echo $stats['incidencia']; ?></strong><small>Requieren aviso</small></div>
    </section>

    <div class="dashboard-grid two-columns">
        <div class="admin-card">
            <div class="section-title-row"><div><h3>Mis próximas tareas</h3><p>Solo aparecen tareas asignadas directamente a tu usuario.</p></div><a href="tareas.php" class="small-link">Abrir tareas</a></div>
            <?php if($tareas && $tareas->num_rows>0){ ?>
                <div class="activity-list">
                    <?php while($t=$tareas->fetch_assoc()){ ?>
                        <div class="activity-item">
                            <div>
                                <strong><?php echo htmlspecialchars($t['titulo']); ?></strong>
                                <small><?php echo htmlspecialchars($t['parcela'] ?? 'Sin parcela'); ?> · Asignada por <?php echo htmlspecialchars($t['creador'] ?? 'Sistema'); ?> · Límite: <?php echo htmlspecialchars($t['fecha_limite'] ?: 'sin fecha'); ?></small>
                            </div>
                            <span class="badge <?php echo badge_estado_peon($t['estado']); ?>"><?php echo htmlspecialchars($t['estado']); ?></span>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?><p class="text-muted mb-0">No tienes tareas asignadas ahora mismo.</p><?php } ?>
        </div>

        <div class="admin-card">
            <div class="section-title-row"><div><h3>Mis grupos de trabajo</h3><p>Equipos o cuadrillas donde estás incluido.</p></div></div>
            <?php if($grupos && $grupos->num_rows>0){ ?>
                <div class="activity-list">
                    <?php while($g=$grupos->fetch_assoc()){ ?>
                        <div class="activity-item">
                            <div>
                                <strong><?php echo htmlspecialchars($g['nombre']); ?></strong>
                                <small><?php echo htmlspecialchars($g['descripcion'] ?: 'Sin descripción'); ?></small>
                            </div>
                            <span><?php echo intval($g['miembros']); ?> miembros</span>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?><p class="text-muted mb-0">Todavía no estás dentro de ningún grupo de trabajo.</p><?php } ?>
        </div>
    </div>
</div>
<script src="notificaciones.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

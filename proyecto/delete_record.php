<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect'); $conn->set_charset('utf8mb4'); require_once 'agro_helper.php';
if (!isset($_SESSION['rol'])) { header('Location: login.php'); exit(); }
$rol=$_SESSION['rol']; $eid=intval($_SESSION['empresa_id']??0);
if (!in_array($rol,['superadmin','jefe'])) die('No tienes permiso para borrar registros.');
$tabla=$_GET['tabla'] ?? $_POST['tabla'] ?? ''; $id=intval($_GET['id'] ?? $_POST['id'] ?? 0); $volver=$_GET['volver'] ?? $_POST['volver'] ?? agro_volver_panel();
$permitidas=['empresas'=>['superadmin'],'usuarios'=>['superadmin','jefe'],'parcelas'=>['superadmin','jefe'],'cultivos'=>['superadmin','jefe'],'recursos'=>['superadmin','jefe'],'incidencias'=>['superadmin','jefe'],'tareas_agricolas'=>['superadmin','jefe'],'historico_actividades'=>['superadmin','jefe'],'grupos_trabajadores'=>['superadmin','jefe']];
if(!isset($permitidas[$tabla]) || !in_array($rol,$permitidas[$tabla]) || $id<=0) die('Borrado no permitido.');
function puede_borrar($conn,$tabla,$id,$rol,$eid){
 if($rol==='superadmin') return true;
 if($tabla==='empresas') return false;
 if($tabla==='usuarios'){ $st=$conn->prepare('SELECT rol,empresa_id FROM usuarios WHERE id=?'); $st->bind_param('i',$id); $st->execute(); $r=$st->get_result()->fetch_assoc(); return $r && intval($r['empresa_id'])===$eid && in_array($r['rol'],['trabajador','peon']); }
 if(agro_columna_existe($conn,$tabla,'empresa_id')){ $st=$conn->prepare("SELECT empresa_id FROM `$tabla` WHERE id=?"); $st->bind_param('i',$id); $st->execute(); $r=$st->get_result()->fetch_assoc(); return $r && intval($r['empresa_id'])===$eid; }
 return false;
}
if(!puede_borrar($conn,$tabla,$id,$rol,$eid)) die('No tienes permiso sobre este registro.');
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['confirmar'])){ $st=$conn->prepare("DELETE FROM `$tabla` WHERE id=?"); $st->bind_param('i',$id); $st->execute(); header('Location: '.$volver); exit(); }
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Borrar registro</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style.css"></head><body class="admin-body"><?php include __DIR__.'/includes/topbar.php'; ?><div class="admin-container"><div class="admin-card"><h2>Confirmar borrado</h2><p>Vas a borrar un registro de <strong><?php echo htmlspecialchars($tabla); ?></strong>. Esta acción no se puede deshacer.</p><form method="POST" class="d-flex gap-2"><input type="hidden" name="tabla" value="<?php echo htmlspecialchars($tabla); ?>"><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="hidden" name="volver" value="<?php echo htmlspecialchars($volver); ?>"><button name="confirmar" class="btn btn-danger">Sí, borrar</button><a href="<?php echo htmlspecialchars($volver); ?>" class="btn btn-outline-secondary">Cancelar</a></form></div></div><?php include __DIR__.'/includes/footer.php'; ?></body></html>

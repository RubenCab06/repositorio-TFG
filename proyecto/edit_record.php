<?php
session_start();
$conn = new mysqli('localhost','root','','agroconnect');
$conn->set_charset('utf8mb4');
require_once 'agro_helper.php';
if (!isset($_SESSION['rol'])) { header('Location: login.php'); exit(); }
$rol = $_SESSION['rol']; $uid = intval($_SESSION['id'] ?? 0); $eid = intval($_SESSION['empresa_id'] ?? 0);
if (!in_array($rol, ['superadmin','jefe'])) { die('No tienes permiso para editar registros.'); }

$tabla = $_GET['tabla'] ?? $_POST['tabla'] ?? '';
$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
$volver = $_GET['volver'] ?? $_POST['volver'] ?? agro_volver_panel();

$config = [
 'empresas'=>['volver'=>'empresas.php','roles'=>['superadmin'],'campos'=>['nombre','cif','telefono','email','direccion','estado']],
 'usuarios'=>['volver'=>'usuarios.php','roles'=>['superadmin','jefe'],'campos'=>['nombre','apellido1','apellido2','email','rol','empresa_id']],
 'parcelas'=>['volver'=>'parcelas.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','nombre','ubicacion','latitud','longitud','hectareas','tipo_suelo','estado']],
 'cultivos'=>['volver'=>'cultivos.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','parcela_id','nombre','variedad','fecha_siembra','fecha_cosecha_prevista','estado']],
 'recursos'=>['volver'=>'recursos.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','nombre','tipo','cantidad','unidad','estado','notas']],
 'incidencias'=>['volver'=>'incidencias.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','titulo','descripcion','prioridad','estado']],
 'tareas_agricolas'=>['volver'=>'tareas.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','parcela_id','trabajador_id','titulo','descripcion','tipo','prioridad','fecha_programada','fecha_limite','estado','responsable']],
 'historico_actividades'=>['volver'=>'historico.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','parcela_id','usuario_id','tipo','descripcion','fecha']],
 'grupos_trabajadores'=>['volver'=>'grupos_trabajadores.php','roles'=>['superadmin','jefe'],'campos'=>['empresa_id','nombre','descripcion']]
];
if (!isset($config[$tabla]) || $id <= 0 || !in_array($rol, $config[$tabla]['roles'])) die('Registro no permitido.');
if (!agro_tabla_existe($conn, $tabla)) die('La tabla no existe.');

function puede_gestionar_registro($conn, $tabla, $id, $rol, $eid) {
    if ($rol === 'superadmin') return true;
    if ($tabla === 'empresas') return false;
    if ($tabla === 'usuarios') {
        $st=$conn->prepare("SELECT rol, empresa_id FROM usuarios WHERE id=?"); $st->bind_param('i',$id); $st->execute(); $r=$st->get_result()->fetch_assoc();
        return $r && intval($r['empresa_id']) === $eid && in_array($r['rol'], ['trabajador','peon']);
    }
    if (agro_columna_existe($conn, $tabla, 'empresa_id')) {
        $st=$conn->prepare("SELECT empresa_id FROM `$tabla` WHERE id=?"); $st->bind_param('i',$id); $st->execute(); $r=$st->get_result()->fetch_assoc();
        return $r && intval($r['empresa_id']) === $eid;
    }
    return false;
}
if (!puede_gestionar_registro($conn, $tabla, $id, $rol, $eid)) die('No tienes permiso sobre este registro.');

$mensaje=''; $error='';
$campos = array_values(array_filter($config[$tabla]['campos'], fn($c)=>agro_columna_existe($conn,$tabla,$c)));
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['guardar_edicion'])) {
    $sets=[]; $types=''; $vals=[];
    foreach($campos as $c){
        if ($rol !== 'superadmin' && $c === 'empresa_id') continue;
        $v = $_POST[$c] ?? null;
        if ($v === '') $v = null;
        if ($tabla === 'usuarios' && $c === 'rol' && $rol === 'jefe' && !in_array($v, ['trabajador','peon'])) $v='trabajador';
        $sets[]="`$c`=?"; $types.='s'; $vals[]=$v;
    }
    if ($sets) {
        $sql="UPDATE `$tabla` SET ".implode(',',$sets)." WHERE id=?";
        if ($rol !== 'superadmin' && agro_columna_existe($conn,$tabla,'empresa_id') && $tabla !== 'usuarios') $sql .= " AND empresa_id=".$eid;
        $types.='i'; $vals[]=$id;
        $st=$conn->prepare($sql); $st->bind_param($types, ...$vals); $mensaje=$st->execute()?'Registro actualizado correctamente.':'No se pudo actualizar.';
    }
}
$st=$conn->prepare("SELECT * FROM `$tabla` WHERE id=?"); $st->bind_param('i',$id); $st->execute(); $row=$st->get_result()->fetch_assoc();
if(!$row) die('Registro no encontrado.');
function input_tipo($campo){ if(str_contains($campo,'fecha')) return 'date'; if(in_array($campo,['latitud','longitud','hectareas','cantidad'])) return 'number'; if($campo==='email') return 'email'; return 'text'; }
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Editar registro</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style.css"></head><body class="admin-body"><?php include __DIR__.'/includes/topbar.php'; ?><div class="admin-container"><div class="dashboard-hero"><div><span class="eyebrow">Edición segura</span><h1>Editar <?php echo htmlspecialchars($tabla); ?></h1><p>Modifica el registro seleccionado respetando los permisos de cada rol.</p></div><a class="btn btn-outline-success" href="<?php echo htmlspecialchars($config[$tabla]['volver']); ?>">Volver</a></div><?php if($mensaje){?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje);?></div><?php } if($error){?><div class="alert alert-danger"><?php echo htmlspecialchars($error);?></div><?php } ?><div class="admin-card"><form method="POST" class="row g-3"><input type="hidden" name="tabla" value="<?php echo htmlspecialchars($tabla); ?>"><input type="hidden" name="id" value="<?php echo $id; ?>"><?php foreach($campos as $c){ if($rol!=='superadmin' && $c==='empresa_id') continue; $valor=$row[$c] ?? ''; ?><div class="col-md-6"><label class="form-label"><?php echo ucfirst(str_replace('_',' ',$c)); ?></label><?php if(in_array($c,['descripcion','notas'])){ ?><textarea name="<?php echo $c; ?>" class="form-control" rows="3"><?php echo htmlspecialchars($valor); ?></textarea><?php } elseif($c==='estado'){ ?><input name="<?php echo $c; ?>" class="form-control" value="<?php echo htmlspecialchars($valor); ?>"><?php } elseif($c==='rol' && $tabla==='usuarios'){ ?><select name="rol" class="form-select"><?php $roles=($rol==='superadmin')?['superadmin','jefe','trabajador','peon']:['trabajador','peon']; foreach($roles as $rr){ ?><option value="<?php echo $rr; ?>" <?php if($valor===$rr) echo 'selected'; ?>><?php echo ucfirst($rr); ?></option><?php } ?></select><?php } else { ?><input type="<?php echo input_tipo($c); ?>" <?php if(input_tipo($c)==='number') echo 'step="0.00000001"'; ?> name="<?php echo $c; ?>" class="form-control" value="<?php echo htmlspecialchars($valor); ?>"><?php } ?></div><?php } ?><div class="col-12"><button name="guardar_edicion" class="btn btn-success">Guardar cambios</button></div></form></div></div><?php include __DIR__.'/includes/footer.php'; ?></body></html>

<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agroconnect");
$conn->set_charset("utf8mb4");
require_once 'agro_helper.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'jefe') { header("Location: login.php"); exit(); }
$empresa_id = intval($_SESSION['empresa_id'] ?? 0);
$mensaje=''; $error='';
if ($empresa_id <= 0) { $error = 'Tu usuario jefe no tiene empresa asignada. Contacta con el SuperAdmin.'; }
if ($_SERVER['REQUEST_METHOD']==='POST' && $empresa_id > 0) {
    $nombre=trim($_POST['nombre']??''); $apellido1=trim($_POST['apellido1']??''); $apellido2=trim($_POST['apellido2']??''); $email=trim($_POST['email']??''); $passwordPlano=$_POST['password']??''; $rolSolicitado=$_POST['rol']??'trabajador'; if(!in_array($rolSolicitado,['trabajador','peon'])) $rolSolicitado='trabajador';
    if($nombre==='' || $apellido1==='' || $email==='' || $passwordPlano===''){ $error='Rellena todos los campos obligatorios.'; }
    else {
        $check=$conn->prepare("SELECT id FROM usuarios WHERE email=? LIMIT 1"); $check->bind_param("s",$email); $check->execute();
        if($check->get_result()->num_rows>0){ $error='Ya existe un usuario con ese email.'; }
        else { $password=password_hash($passwordPlano, PASSWORD_DEFAULT); $rol=$rolSolicitado; $stmt=$conn->prepare("INSERT INTO usuarios (nombre, apellido1, apellido2, email, password, rol, empresa_id) VALUES (?, ?, ?, ?, ?, ?, ?)"); $stmt->bind_param("ssssssi",$nombre,$apellido1,$apellido2,$email,$password,$rol,$empresa_id); if($stmt->execute()){ $mensaje=ucfirst($rol).' creado correctamente.'; } else { $error='No se pudo crear el trabajador.'; } }
    }
}
$empresa = null; if($empresa_id>0){ $st=$conn->prepare("SELECT nombre FROM empresas WHERE id=?"); $st->bind_param("i",$empresa_id); $st->execute(); $empresa=$st->get_result()->fetch_assoc(); }
$trabajadores = false; if($empresa_id>0){ $st=$conn->prepare("SELECT * FROM usuarios WHERE empresa_id=? AND rol IN ('trabajador','peon') ORDER BY rol, id DESC"); $st->bind_param("i",$empresa_id); $st->execute(); $trabajadores=$st->get_result(); }
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Crear trabajadores - AgroConnect</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style.css"></head><body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container"><div class="dashboard-hero"><div><span class="eyebrow">Panel jefe</span><h1>Trabajadores de <?php echo htmlspecialchars($empresa['nombre'] ?? 'tu empresa'); ?></h1><p>Crea trabajadores o peones únicamente para tu empresa. No pueden registrarse por su cuenta.</p></div></div>
<?php if($mensaje){ ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php } ?><?php if($error){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
<?php if($empresa_id>0){ ?><div class="admin-card mb-4"><h3>Crear trabajador o peón</h3><form method="POST" class="row g-3 mt-1"><div class="col-md-4"><label class="form-label">Nombre *</label><input class="form-control" name="nombre" required></div><div class="col-md-4"><label class="form-label">Primer apellido *</label><input class="form-control" name="apellido1" required></div><div class="col-md-4"><label class="form-label">Segundo apellido</label><input class="form-control" name="apellido2"></div><div class="col-md-6"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" required></div><div class="col-md-3"><label class="form-label">Rol *</label><select name="rol" class="form-select"><option value="trabajador">Trabajador</option><option value="peon">Peón</option></select></div><div class="col-md-3"><label class="form-label">Contraseña inicial *</label><input type="password" class="form-control" name="password" required></div><div class="col-12"><button class="btn btn-success">Crear usuario</button></div></form></div>
<div class="admin-card"><h3>Trabajadores y peones registrados</h3><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead><tbody><?php while($t=$trabajadores->fetch_assoc()){ ?><tr><td><?php echo htmlspecialchars($t['nombre'].' '.$t['apellido1']); ?></td><td><?php echo htmlspecialchars($t['email']); ?></td><td><span class="badge bg-success"><?php echo htmlspecialchars($t['rol']); ?></span></td><td><?php echo agro_crud_botones('usuarios', $t['id'], 'crear_trabajador.php'); ?></td></tr><?php } ?></tbody></table></div></div><?php } ?></div><?php include __DIR__ . '/includes/footer.php'; ?>
</body></html>

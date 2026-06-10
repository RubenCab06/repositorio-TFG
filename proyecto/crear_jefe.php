<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agroconnect");
$conn->set_charset("utf8mb4");
require_once 'agro_helper.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superadmin') { header("Location: login.php"); exit(); }
$mensaje = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido1 = trim($_POST['apellido1'] ?? '');
    $apellido2 = trim($_POST['apellido2'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordPlano = $_POST['password'] ?? '';
    $empresa_id = intval($_POST['empresa_id'] ?? 0);
    $nuevaEmpresa = trim($_POST['nueva_empresa'] ?? '');

    if ($nombre === '' || $apellido1 === '' || $email === '' || $passwordPlano === '') {
        $error = 'Rellena todos los campos obligatorios.';
    } else {
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email=? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Ya existe un usuario con ese email.';
        } else {
            if ($empresa_id <= 0 && $nuevaEmpresa !== '') {
                $stmtEmpresa = $conn->prepare("INSERT INTO empresas (nombre) VALUES (?)");
                $stmtEmpresa->bind_param("s", $nuevaEmpresa);
                $stmtEmpresa->execute();
                $empresa_id = $stmtEmpresa->insert_id;
            }
            if ($empresa_id <= 0) {
                $error = 'Selecciona una empresa o crea una nueva.';
            } else {
                $password = password_hash($passwordPlano, PASSWORD_DEFAULT);
                $rol = 'jefe';
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido1, apellido2, email, password, rol, empresa_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssi", $nombre, $apellido1, $apellido2, $email, $password, $rol, $empresa_id);
                if ($stmt->execute()) { $mensaje = 'Jefe creado correctamente.'; } else { $error = 'No se pudo crear el jefe.'; }
            }
        }
    }
}
$empresas = $conn->query("SELECT id, nombre FROM empresas ORDER BY nombre ASC");
$jefes = $conn->query("SELECT u.*, e.nombre AS empresa FROM usuarios u LEFT JOIN empresas e ON u.empresa_id=e.id WHERE u.rol='jefe' ORDER BY u.id DESC");
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Crear jefes - AgroConnect</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style.css"></head><body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>
<div class="admin-container"><div class="dashboard-hero"><div><span class="eyebrow">SuperAdmin</span><h1>Alta de jefes de empresa</h1><p>Solo el SuperAdmin puede crear jefes. Cada jefe quedará asociado a una empresa y podrá crear sus trabajadores.</p></div></div>
<?php if($mensaje){ ?><div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div><?php } ?><?php if($error){ ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php } ?>
<div class="admin-card mb-4"><h3>Crear nuevo jefe</h3><form method="POST" class="row g-3 mt-1"><div class="col-md-4"><label class="form-label">Nombre *</label><input class="form-control" name="nombre" required></div><div class="col-md-4"><label class="form-label">Primer apellido *</label><input class="form-control" name="apellido1" required></div><div class="col-md-4"><label class="form-label">Segundo apellido</label><input class="form-control" name="apellido2"></div><div class="col-md-6"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" required></div><div class="col-md-6"><label class="form-label">Contraseña inicial *</label><input type="password" class="form-control" name="password" required></div><div class="col-md-6"><label class="form-label">Empresa existente</label><select class="form-select" name="empresa_id"><option value="0">Seleccionar empresa...</option><?php while($e=$empresas->fetch_assoc()){ ?><option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php } ?></select></div><div class="col-md-6"><label class="form-label">O crear nueva empresa</label><input class="form-control" name="nueva_empresa" placeholder="Nombre de empresa nueva"></div><div class="col-12"><button class="btn btn-success">Crear jefe</button></div></form></div>
<div class="admin-card"><h3>Jefes registrados</h3><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nombre</th><th>Email</th><th>Empresa</th><th>Acciones</th></tr></thead><tbody><?php while($j=$jefes->fetch_assoc()){ ?><tr><td><?php echo htmlspecialchars($j['nombre'].' '.$j['apellido1']); ?></td><td><?php echo htmlspecialchars($j['email']); ?></td><td><?php echo htmlspecialchars($j['empresa'] ?? 'Sin empresa'); ?></td><td><?php echo agro_crud_botones('usuarios', $j['id'], 'crear_jefe.php'); ?></td></tr><?php } ?></tbody></table></div></div></div><?php include __DIR__ . '/includes/footer.php'; ?>
</body></html>

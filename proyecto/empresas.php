<?php
session_start();
$conn = new mysqli("localhost","root","","agroconnect");
$conn->set_charset("utf8mb4");
require_once 'agro_helper.php';

/* SEGURIDAD: solo SuperAdmin */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

$mensaje = '';
$error = '';

/* CREAR EMPRESA: el ID siempre lo genera MySQL automáticamente */
if (isset($_POST['crear'])) {
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $error = 'Debes indicar el nombre de la empresa.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM empresas WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();

        if ($existe) {
            $error = 'Esa empresa ya existe. No se pueden crear dos empresas con el mismo nombre.';
        } else {
            $stmt = $conn->prepare("INSERT INTO empresas (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            if ($stmt->execute()) {
                $mensaje = 'Empresa creada correctamente. Código asignado automáticamente: EMP-' . str_pad($conn->insert_id, 3, '0', STR_PAD_LEFT);
            } else {
                $error = 'No se pudo crear la empresa.';
            }
        }
    }
}

/* OBTENER EMPRESAS */
$empresas = $conn->query("SELECT * FROM empresas ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Empresas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body class="admin-body">
<?php include __DIR__ . '/includes/topbar.php'; ?>

<div class="admin-container">

    <div class="section-head">
        <div>
            <span class="eyebrow">SuperAdmin</span>
            <h2 class="admin-title mb-1">Empresas</h2>
            <p class="text-muted mb-0">Crea y gestiona empresas. El código interno se asigna automáticamente.</p>
        </div>
    </div>

    <?php if ($mensaje) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php } ?>

    <?php if ($error) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST" class="admin-card mb-4 empresa-form">
        <label class="form-label fw-bold">Nueva empresa</label>
        <div class="empresa-form__row">
            <input name="nombre" class="form-control" placeholder="Nombre de la empresa" required>
            <button name="crear" class="btn btn-success">Crear empresa</button>
        </div>
        <small class="text-muted">No introduzcas ningún ID. AgroConnect lo genera solo, por ejemplo EMP-001, EMP-002...</small>
    </form>

    <?php if($empresas->num_rows == 0){ ?>
        <div class="admin-card"><p class="mb-0">No hay empresas registradas.</p></div>
    <?php } else { ?>
        <div class="empresa-lista">
        <?php while($e = $empresas->fetch_assoc()){ ?>
            <div class="empresa-item">
                <div class="empresa-item__main">
                    <div class="empresa-code">EMP-<?php echo str_pad($e['id'], 3, '0', STR_PAD_LEFT); ?></div>
                    <div>
                        <h5><?php echo htmlspecialchars($e['nombre']); ?></h5>
                        <p>Empresa registrada en AgroConnect</p>
                    </div>
                </div>

                <div class="empresa-actions">
                    <a href="empresa_detalle.php?id=<?php echo $e['id']; ?>" class="btn btn-success btn-sm">Entrar</a>
                    <?php echo agro_crud_botones('empresas', $e['id'], 'empresas.php'); ?>
                </div>
            </div>
        <?php } ?>
        </div>
    <?php } ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

<?php
session_start();
$conn = new mysqli("localhost","root","","agroconnect");
require_once 'agro_helper.php';

/* SEGURIDAD */
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'superadmin') {
    header("Location: login.php");
    exit();
}

/* OBTENER USUARIOS + EMPRESA */
$sql = "SELECT usuarios.*, empresas.nombre AS empresa 
        FROM usuarios 
        LEFT JOIN empresas ON usuarios.empresa_id = empresas.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body class="admin-body">

<!-- NAVBAR -->
<?php include __DIR__ . '/includes/topbar.php'; ?>

<div class="admin-container">

    <h2 class="admin-title">Lista de usuarios</h2>

    <div class="admin-card">

        <?php if ($result->num_rows == 0) { ?>
            <p>No hay usuarios</p>
        <?php } else { ?>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Empresa</th><th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($u = $result->fetch_assoc()){ ?>
                        <tr>
                            <td><?php echo $u['nombre']; ?></td>
                            <td><?php echo $u['email']; ?></td>
                            <td>
                                <span class="badge bg-success">
                                    <?php echo $u['rol']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $u['empresa'] ? $u['empresa'] : "Sin empresa"; ?>
                            </td><td><?php echo agro_crud_botones('usuarios', $u['id'], 'usuarios.php'); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>

            </table>

        <?php } ?>

    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
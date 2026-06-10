<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agroconnect");

// SOLO SUPERADMIN
if(!isset($_SESSION['rol']) || $_SESSION['rol'] != 'superadmin'){
    header("Location: login.php");
    exit();
}

// ID EMPRESA
if(!isset($_GET['id'])){
    die("Empresa no encontrada");
}

$empresa_id = $_GET['id'];

// CREAR USUARIO (JEFE/TRABAJADOR)
if(isset($_POST['crear_usuario'])){

    $nombre = $_POST['nombre'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    $sql = "INSERT INTO usuarios (nombre, apellido1, apellido2, email, password, rol, empresa_id)
            VALUES ('$nombre', '$apellido1', '$apellido2', '$email', '$password', '$rol', '$empresa_id')";

    mysqli_query($conn, $sql);
}

// OBTENER EMPRESA
$empresa = mysqli_query($conn, "SELECT * FROM empresas WHERE id=$empresa_id")->fetch_assoc();

// OBTENER USUARIOS
$usuarios = mysqli_query($conn, "SELECT * FROM usuarios WHERE empresa_id=$empresa_id");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">



<?php include __DIR__ . '/includes/topbar.php'; ?>


<div class="admin-container">

    <h2 class="admin-title">Empresa: <?php echo $empresa['nombre']; ?></h2>

    <!-- CREAR USUARIO -->
    <div class="admin-card mb-4">
        <h4>Crear trabajador / jefe</h4>

        <form method="POST">

            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Primer apellido</label>
                <input type="text" name="apellido1" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Segundo apellido</label>
                <input type="text" name="apellido2" class="form-control">
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Rol</label>
                <select name="rol" class="form-control">
                    <option value="jefe">Jefe</option>
                    <option value="trabajador">Trabajador</option>
                </select>
            </div>

            <button type="submit" name="crear_usuario" class="btn btn-success w-100">
                Crear usuario
            </button>

        </form>
    </div>

    <!-- LISTADO USUARIOS -->
    <div class="admin-card">
        <h4>Usuarios de la empresa</h4>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Nombre completo</th>
                    <th>Email</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>

                <?php while($u = $usuarios->fetch_assoc()){ ?>
                    <tr>
                        <td>
                            <?php 
                                echo $u['nombre'] . " " . $u['apellido1'] . 
                                ($u['apellido2'] ? " " . $u['apellido2'] : "");
                            ?>
                        </td>
                        <td><?php echo $u['email']; ?></td>
                        <td><?php echo $u['rol']; ?></td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>

    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agroconnect");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();

       if (password_verify($password, $usuario['password'])) {

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['empresa_id'] = $usuario['empresa_id'] ?? null;

            // Registrar actividad para saber quién está conectado
            // Este bloque también arregla la tabla si en el PC nuevo falta alguna columna.
            $conn->query("
                CREATE TABLE IF NOT EXISTS sesiones_activas (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT NOT NULL,
                    session_id VARCHAR(255) NOT NULL,
                    ultima_actividad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    ip VARCHAR(45) NULL,
                    user_agent VARCHAR(255) NULL,
                    UNIQUE KEY unique_session_id (session_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $columnas = [];
            $resColumnas = $conn->query("SHOW COLUMNS FROM sesiones_activas");
            if ($resColumnas) {
                while ($col = $resColumnas->fetch_assoc()) {
                    $columnas[] = $col['Field'];
                }
            }

            if (!in_array('session_id', $columnas)) {
                $conn->query("ALTER TABLE sesiones_activas ADD COLUMN session_id VARCHAR(255) NOT NULL AFTER usuario_id");
            }
            if (!in_array('ultima_actividad', $columnas)) {
                $conn->query("ALTER TABLE sesiones_activas ADD COLUMN ultima_actividad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
            }
            if (!in_array('ip', $columnas)) {
                $conn->query("ALTER TABLE sesiones_activas ADD COLUMN ip VARCHAR(45) NULL");
            }
            if (!in_array('user_agent', $columnas)) {
                $conn->query("ALTER TABLE sesiones_activas ADD COLUMN user_agent VARCHAR(255) NULL");
            }

            $existeIndice = false;
            $indices = $conn->query("SHOW INDEX FROM sesiones_activas WHERE Key_name = 'unique_session_id'");
            if ($indices && $indices->num_rows > 0) {
                $existeIndice = true;
            }
            if (!$existeIndice) {
                @$conn->query("ALTER TABLE sesiones_activas ADD UNIQUE KEY unique_session_id (session_id)");
            }

            $sid = session_id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

            $actividad = $conn->prepare("
                INSERT INTO sesiones_activas (usuario_id, session_id, ultima_actividad, ip, user_agent)
                VALUES (?, ?, NOW(), ?, ?)
                ON DUPLICATE KEY UPDATE
                    usuario_id = VALUES(usuario_id),
                    ultima_actividad = NOW(),
                    ip = VALUES(ip),
                    user_agent = VALUES(user_agent)
            ");
            $actividad->bind_param("isss", $usuario['id'], $sid, $ip, $agent);
            $actividad->execute();

            // REDIRECCIÓN SEGÚN ROL
            if ($usuario['rol'] == 'superadmin') {
                header("Location: superadmin.php");
            } elseif ($usuario['rol'] == 'jefe') {
                header("Location: jefe.php");
            } elseif ($usuario['rol'] == 'trabajador') {
                header("Location: trabajador.php");
            } elseif ($usuario['rol'] == 'peon') {
                header("Location: peon.php");
            } else {
                echo "Rol no válido";
            }

            exit();
        }

        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - AgroConnect</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- HEADER -->
<header class="header-top">
    <div class="header-container">
        <div class="logo-area">
            <img src="img/logo.png" alt="AgroConnect">
        </div>

        <nav class="main-nav">
            <a href="index.php">Inicio</a>
            <a href="sobrenosotros.php">Sobre nosotros</a>
            <a href="servicios.php">Servicios</a>
            <a href="contacto.php">Contacto</a>
        </nav>
    </div>
</header>

<!-- CONTENIDO -->
<section class="contenido auth-layout">
    <div class="card">

        <h2>Iniciar sesión</h2>

        <?php if (isset($error)) { ?>
            <p class="alert-error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" class="formulario">

            <div class="grupo">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="grupo">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn-enviar">Entrar</button>
        </form>

        <p style="margin-top:20px; color:#667085;">
            El acceso es privado. Solicita tu cuenta al responsable de tu empresa.
        </p>

    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
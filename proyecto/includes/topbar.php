<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$agroTopbarOwnConn = false;
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = @new mysqli("localhost", "root", "", "agroconnect");
    if (!$conn->connect_error) { $conn->set_charset("utf8mb4"); $agroTopbarOwnConn = true; }
}

$usuarioNombre = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? '';
$empresaId = intval($_SESSION['empresa_id'] ?? 0);
$empresaNombre = 'Administración general';

if ($empresaId > 0 && isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $stmtTopbar = $conn->prepare("SELECT nombre FROM empresas WHERE id = ? LIMIT 1");
    if ($stmtTopbar) {
        $stmtTopbar->bind_param("i", $empresaId);
        $stmtTopbar->execute();
        $resTopbar = $stmtTopbar->get_result();
        if ($rowTopbar = $resTopbar->fetch_assoc()) {
            $empresaNombre = $rowTopbar['nombre'];
        }
        $stmtTopbar->close();
    }
}

$rolLabel = [
    'superadmin' => 'SuperAdmin',
    'jefe' => 'Jefe de empresa',
    'trabajador' => 'Trabajador',
    'peon' => 'Peón'
][$rol] ?? ucfirst($rol);

$panelUrl = 'login.php';
if ($rol === 'superadmin') { $panelUrl = 'superadmin.php'; }
if ($rol === 'jefe') { $panelUrl = 'jefe.php'; }
if ($rol === 'trabajador') { $panelUrl = 'trabajador.php'; }
if ($rol === 'peon') { $panelUrl = 'peon.php'; }
?>
<header class="app-topbar">
    <div class="app-topbar__inner">
        <a href="<?php echo htmlspecialchars($panelUrl); ?>" class="app-brand" aria-label="Ir al panel de AgroConnect">
            <img src="img/logo.png" alt="AgroConnect" class="app-brand__logo">
        </a>

        <nav class="app-nav" aria-label="Navegación principal">
            <a href="<?php echo htmlspecialchars($panelUrl); ?>">Panel</a>
            <?php if ($rol === 'superadmin'): ?>
                <a href="empresas.php">Empresas</a>
                <a href="usuarios.php">Usuarios</a>
                <a href="foro.php">Foro</a>
            <?php elseif ($rol === 'jefe'): ?>
                <a href="tareas.php">Tareas</a>
                <a href="grupos_trabajadores.php">Grupos</a>
                <a href="crear_trabajador.php">Trabajadores</a>
            <?php elseif ($rol === 'trabajador'): ?>
                <a href="tareas.php">Mis tareas</a>
                <a href="incidencias.php">Incidencias</a>
            <?php elseif ($rol === 'peon'): ?>
                <a href="peon.php">Mi panel</a>
                <a href="tareas.php">Mis tareas</a>
            <?php endif; ?>
            <?php if ($rol !== 'peon'): ?>
                <a href="parcelas.php">Parcelas</a>
                <a href="cultivos.php">Cultivos</a>
                <a href="mapa_parcelas.php">Mapa</a>
            <?php endif; ?>
        </nav>

        <div class="app-userbox">
            <div class="app-userbox__avatar">
                <?php echo strtoupper(mb_substr($usuarioNombre, 0, 1, 'UTF-8')); ?>
            </div>
            <div class="app-userbox__text">
                <strong><?php echo htmlspecialchars($usuarioNombre); ?></strong>
                <span><?php echo htmlspecialchars($rolLabel); ?> · <?php echo htmlspecialchars($empresaNombre); ?></span>
            </div>
            <a href="logout.php" class="app-logout">Salir</a>
        </div>
    </div>
</header>

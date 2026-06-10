<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro cerrado - AgroConnect</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header-top"><div class="header-container"><div class="logo-area"><img src="img/logo.png" alt="AgroConnect"></div><nav class="main-nav"><a href="index.php">Inicio</a><a href="sobrenosotros.php">Sobre nosotros</a><a href="servicios.php">Servicios</a><a href="contacto.php">Contacto</a><a href="login.php">Login</a></nav></div></header>
<section class="contenido auth-layout">
  <div class="card registro-card">
    <h2>Registro privado</h2>
    <p>AgroConnect es una plataforma privada para empresas y trabajadores autorizados.</p>
    <p>Las cuentas no se crean desde un registro público:</p>
    <ul style="text-align:left; line-height:1.8; margin:20px auto; max-width:520px;">
      <li>El <strong>SuperAdmin</strong> crea las empresas y sus jefes.</li>
      <li>Cada <strong>jefe</strong> crea los trabajadores de su propia empresa.</li>
    </ul>
    <a href="login.php" class="btn-enviar" style="display:inline-block;text-decoration:none;">Volver al login</a>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

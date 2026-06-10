<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - AgroConnect</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tu CSS principal -->
    <link rel="stylesheet" href="style.css">

</head>

<body>

<!-- CABECERA -->
<header class="header-top">
    <div class="header-container">
        <div class="logo-area">
            <img src="img/logo.png" alt="AgroConnect">
        </div>

       <nav class="main-nav">
            <a href="index.php">Inicio</a>
            <a href="sobrenosotros.php">Sobre nosotros</a>
            <a href="servicios.php">Servicios</a>
            <a href="contacto.php" class="active">Contacto</a>
            <a href="login.php" class="login-btn">Iniciar sesión</a>
        </nav>
    </div>
</header>

<!-- SECCIÓN CONTACTO -->
<div class="contact-section">
    <div class="row g-5 align-items-stretch">

        <!-- FORMULARIO -->
        <div class="col-lg-7">
            <div class="contact-card">

                <h3 class="mb-4 text-success">Formulario de contacto</h3>
                <p class="text-muted mb-4">
                    ¿Necesitas información sobre la plataforma o asesoramiento técnico?
                    Nuestro equipo te responderá en la mayor brevedad posible.
                </p>

                <form method="POST">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Asunto</label>
                        <input type="text" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Mensaje</label>
                        <textarea class="form-control" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-agro w-100">
                        Enviar mensaje
                    </button>

                </form>
            </div>
        </div>

        <!-- INFORMACIÓN -->
        <div class="col-lg-5">
            <div class="info-box">

                <h4 class="mb-4">Información de contacto</h4>

                <div class="info-item">
                    <strong>Email</strong>
                    soporte@agroconnect.com
                </div>

                <div class="info-item">
                    <strong>Teléfono</strong>
                    +34 900 123 456
                </div>

                <div class="info-item">
                    <strong>Ubicación</strong>
                    La Campana (Sevilla), España
                </div>

                <hr class="border-light my-4">

                <p>
                    AgroConnect es una plataforma especializada en la gestión inteligente
                    del riego agrícola, enfocada en la digitalización y optimización
                    de recursos hídricos.
                </p>

            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
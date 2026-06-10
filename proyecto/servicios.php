<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios - AgroConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header-top">
    <div class="header-container">
        <div class="logo-area">
            <img src="img/logo.png" alt="AgroConnect">
        </div>

        <nav class="main-nav">
            <a href="index.php">Inicio</a>
            <a href="sobrenosotros.php">Sobre nosotros</a>
            <a href="servicios.php" class="active">Servicios</a>
            <a href="contacto.php">Contacto</a>
            <a href="login.php" class="login-btn">Iniciar sesión</a>
        </nav>
    </div>
</header>

<section class="hero">
    <span class="hero-badge">Servicios</span>
    <h1>Soluciones digitales para gestionar mejor el trabajo agrícola</h1>
    <p>
        AgroConnect reúne en una misma plataforma las herramientas necesarias para controlar empresas,
        equipos, parcelas, cultivos, tareas, incidencias, recursos y datos meteorológicos.
    </p>
</section>

<main class="landing-content">

    <section class="landing-grid three-columns">
        <article class="landing-card service-card">
            <div class="icon-bubble">🏢</div>
            <h2>Gestión de empresas</h2>
            <p>
                El SuperAdmin puede crear, consultar, editar y eliminar empresas de forma controlada.
                Cada empresa queda separada del resto para que sus jefes y trabajadores solo vean la información que les corresponde.
            </p>
        </article>

        <article class="landing-card service-card">
            <div class="icon-bubble">👥</div>
            <h2>Usuarios y roles</h2>
            <p>
                La plataforma trabaja con roles diferenciados: SuperAdmin, jefe, trabajador y peón.
                Así se evita que cualquier persona pueda registrarse y se mantiene un acceso privado y seguro.
            </p>
        </article>

        <article class="landing-card service-card">
            <div class="icon-bubble">🧑‍🌾</div>
            <h2>Grupos de trabajo</h2>
            <p>
                El jefe puede crear grupos con trabajadores o peones ya existentes. Estos grupos pueden modificarse o eliminarse
                cuando cambie la organización de la empresa.
            </p>
        </article>
    </section>

    <section class="landing-grid two-columns">
        <article class="landing-card service-detail-card">
            <span class="section-kicker">Producción agrícola</span>
            <h2>Parcelas y cultivos siempre organizados</h2>
            <p>
                AgroConnect permite registrar parcelas con su nombre, ubicación, superficie, tipo de suelo y coordenadas.
                A partir de esas parcelas se pueden crear cultivos y consultar la información de cada uno de forma clara.
            </p>
            <p>
                Esta organización permite saber qué se está cultivando, dónde se encuentra cada cultivo y en qué estado está.
                Además, al usar coordenadas, cada cultivo puede mostrar el tiempo real de la zona donde se encuentra su parcela.
            </p>
            <ul class="check-list columns-list">
                <li>Alta de parcelas.</li>
                <li>Edición y eliminación controlada.</li>
                <li>Registro de cultivos.</li>
                <li>Consulta del tiempo por cultivo.</li>
                <li>Historial de actividades.</li>
                <li>Información accesible desde cualquier dispositivo.</li>
            </ul>
        </article>

        <article class="landing-card weather-service-card">
            <div class="weather-demo">
                <span>☀️</span>
                <strong>Tiempo en cultivo</strong>
                <p>Temperatura, humedad, viento y lluvia de la parcela seleccionada.</p>
            </div>
            <h2>Meteorología integrada</h2>
            <p>
                El sistema incluye tiempo real en el panel de control y también en los cultivos. Esto permite comparar la ubicación
                del usuario con la ubicación real de las parcelas, algo muy útil cuando una empresa trabaja en distintas zonas.
            </p>
        </article>
    </section>

    <section class="landing-card process-card">
        <span class="section-kicker">Trabajo diario</span>
        <h2>Gestión profesional de tareas</h2>
        <p>
            El módulo de tareas está pensado para que el jefe o el trabajador puedan organizar el trabajo del día a día.
            Las tareas pueden asignarse a usuarios concretos o a grupos completos, evitando duplicados si una persona aparece
            seleccionada de varias formas.
        </p>
        <div class="steps-grid compact-steps">
            <div><strong>✓</strong><h4>Asignación flexible</h4><p>A trabajadores sueltos, peones o grupos completos.</p></div>
            <div><strong>✓</strong><h4>Estados de tarea</h4><p>Pendiente, en proceso, completada o incidencia.</p></div>
            <div><strong>✓</strong><h4>Responsable visible</h4><p>El peón ve quién le ha asignado cada tarea.</p></div>
            <div><strong>✓</strong><h4>Histórico automático</h4><p>Las acciones importantes quedan registradas.</p></div>
        </div>
    </section>

    <section class="landing-grid three-columns">
        <article class="landing-card service-card">
            <div class="icon-bubble">🚜</div>
            <h2>Recursos y maquinaria</h2>
            <p>
                Registro de recursos agrícolas para controlar maquinaria, herramientas o materiales necesarios para el trabajo diario.
                Permite tener una visión más ordenada de los medios disponibles en la empresa.
            </p>
        </article>

        <article class="landing-card service-card">
            <div class="icon-bubble">⚠️</div>
            <h2>Incidencias</h2>
            <p>
                Sistema para comunicar problemas, revisar su estado y mantener un seguimiento de las incidencias abiertas,
                en revisión o solucionadas.
            </p>
        </article>

        <article class="landing-card service-card">
            <div class="icon-bubble">💬</div>
            <h2>Foro interno</h2>
            <p>
                Espacio de comunicación entre empresas y responsables para plantear dudas, incidencias o mejoras relacionadas
                con el uso de la plataforma.
            </p>
        </article>
    </section>

    <section class="landing-card cta-wide">
        <h2>Una plataforma pensada para trabajar, no solo para mostrar datos</h2>
        <p>
            Nuestro objetivo es que AgroConnect sea una aplicación completa: segura, clara, mantenible y útil para una empresa agrícola
            que necesite controlar su información desde un único lugar.
        </p>
        <a href="contacto.php" class="btn-landing">Solicitar información</a>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

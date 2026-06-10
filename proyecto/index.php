<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AgroConnect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header-top">
    <div class="header-container">
        <div class="logo-area">
            <img src="img/logo.png" alt="AgroConnect">
        </div>

        <nav class="main-nav">
            <a href="index.php" class="active">Inicio</a>
            <a href="sobrenosotros.php">Sobre nosotros</a>
            <a href="servicios.php">Servicios</a>
            <a href="contacto.php">Contacto</a>
            <a href="login.php" class="login-btn">Iniciar sesión</a>
        </nav>
    </div>
</header>

<section class="hero hero-home">
    <span class="hero-badge">Gestión agrícola inteligente</span>
    <h1>Digitaliza tu explotación agrícola desde una sola plataforma</h1>
    <p>
        AgroConnect ayuda a empresas agrícolas a controlar parcelas, cultivos, tareas, trabajadores,
        incidencias y recursos desde una aplicación web sencilla, segura y accesible desde cualquier dispositivo.
    </p>
    <div class="hero-actions">
        <a href="servicios.php" class="btn-landing">Ver servicios</a>
        <a href="sobrenosotros.php" class="btn-landing btn-landing-light">Conocer el proyecto</a>
    </div>
</section>

<main class="landing-content">

    <section class="landing-grid two-columns">
        <article class="landing-card landing-card-large">
            <span class="section-kicker">Qué es AgroConnect</span>
            <h2>Una herramienta creada para que el campo sea más fácil de gestionar</h2>
            <p>
                AgroConnect nace como una solución web pensada para agricultores, responsables de finca y empresas
                que necesitan tener la información importante bien organizada. La plataforma permite consultar el
                estado de los cultivos, asignar tareas, registrar actividades, controlar incidencias y revisar recursos
                sin depender de papeles, llamadas o mensajes perdidos.
            </p>
            <p>
                La idea es sencilla: que cada persona entre con su usuario y vea únicamente lo que necesita para trabajar.
                El jefe puede organizar la empresa, los trabajadores pueden consultar sus tareas y el peón puede ver las
                tareas que tiene asignadas y los grupos a los que pertenece.
            </p>
        </article>

        <article class="landing-card highlight-card">
            <h3>Diseñada para empresas agrícolas reales</h3>
            <p>
                El sistema no es una página informativa sin más: incluye paneles privados, roles, gestión de empresas,
                parcelas, cultivos, recursos, grupos de trabajo, tareas, incidencias, foro interno y meteorología.
            </p>
            <ul class="check-list">
                <li>Acceso privado por usuario.</li>
                <li>Control por roles.</li>
                <li>Información centralizada.</li>
                <li>Diseño responsive para móvil, tablet y ordenador.</li>
            </ul>
        </article>
    </section>

    <section class="feature-strip">
        <div>
            <strong>Parcelas</strong>
            <span>Control de ubicación, superficie y datos principales.</span>
        </div>
        <div>
            <strong>Cultivos</strong>
            <span>Seguimiento del estado de cada cultivo y su parcela.</span>
        </div>
        <div>
            <strong>Tareas</strong>
            <span>Asignación a trabajadores, peones o grupos completos.</span>
        </div>
        <div>
            <strong>Tiempo real</strong>
            <span>Meteorología del usuario y de cada zona de cultivo.</span>
        </div>
    </section>

    <section class="landing-grid three-columns">
        <article class="landing-card mini-card">
            <div class="icon-bubble">🌱</div>
            <h3>Control de cultivos</h3>
            <p>Consulta cultivos activos, parcelas asociadas, estado del terreno y datos útiles para tomar decisiones.</p>
        </article>
        <article class="landing-card mini-card">
            <div class="icon-bubble">👷</div>
            <h3>Organización del equipo</h3>
            <p>Crea jefes, trabajadores, peones y grupos de trabajo para repartir tareas de forma ordenada.</p>
        </article>
        <article class="landing-card mini-card">
            <div class="icon-bubble">📊</div>
            <h3>Paneles claros</h3>
            <p>Visualiza estadísticas, incidencias, tareas pendientes y actividad reciente desde paneles intuitivos.</p>
        </article>
    </section>

    <section class="landing-card process-card">
        <span class="section-kicker">Cómo funciona</span>
        <h2>Un flujo de trabajo sencillo y profesional</h2>
        <div class="steps-grid">
            <div><strong>1</strong><h4>Se crea la empresa</h4><p>El SuperAdmin da de alta la empresa y sus responsables.</p></div>
            <div><strong>2</strong><h4>Se organiza el equipo</h4><p>El jefe crea trabajadores, peones y grupos según las necesidades.</p></div>
            <div><strong>3</strong><h4>Se registran parcelas y cultivos</h4><p>La empresa controla digitalmente sus explotaciones agrícolas.</p></div>
            <div><strong>4</strong><h4>Se asignan tareas</h4><p>Cada usuario ve sus tareas, quién se las asignó y su estado.</p></div>
        </div>
    </section>

    <section class="landing-grid two-columns">
        <article class="landing-card owners-preview">
            <span class="section-kicker">Quién está detrás</span>
            <h2>Un proyecto creado por jóvenes técnicos de ASIR</h2>
            <p>
                AgroConnect ha sido desarrollado por Rubén Cabello y Álvaro Ballesteros, dos jóvenes de 20 años
                de La Campana, recién graduados en Administración de Sistemas Informáticos en Red.
            </p>
            <p>
                Nuestro objetivo es aplicar lo aprendido en redes, bases de datos, servidores y desarrollo web para crear
                una herramienta útil, visual y cercana al sector agrícola.
            </p>
            <a href="sobrenosotros.php" class="text-link">Leer más sobre nosotros →</a>
        </article>
        <article class="landing-card cta-card">
            <h2>Acceso privado para empresas</h2>
            <p>
                AgroConnect no permite registros públicos. Cada cuenta debe ser creada por el responsable correspondiente
                para mantener la seguridad de la información de cada empresa.
            </p>
            <a href="login.php" class="btn-landing">Entrar a la plataforma</a>
        </article>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

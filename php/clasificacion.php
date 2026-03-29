<?php

declare(strict_types=1);

// Vista de clasificacion: prepara la pagina y el estado de temporada activa.

require_once __DIR__ . '/../includes/app_init.php';

// Se usa para sincronizar cliente con la temporada guardada en sesion.
$pageTitle = 'Clasificacion | FEDERACIÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Contenido central -->
<main class="page page-home" data-temporada-sesion="<?php echo e($temporadaSesion); ?>">
    <!-- Section: Tabla de liga -->
    <section id="clasificacion" class="panel standings-panel">
        <article class="panel-heading">
            <h2>Clasificación</h2>
            <p>Temporada seleccionada: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="set_temporada.php" method="post">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="clasificacion_render" class="table-wrap" aria-label="Tabla de clasificacion">
            <p>Cargando clasificaciÃ³n...</p>
        </article>

        <article id="clasificacion_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar la clasificaciÃ³n con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar la clasificaciÃ³n en esta versiÃ³n.</p>
            </article>
        </noscript>
    </section>

    <section class="home-sections" aria-label="Apartados principales">
        <a class="panel quick-link-panel" href="equipo.php">
            <div class="panel-heading">
                <h2>Equipo</h2>
                <p>Ve todos los clubes participantes y entra a cada ficha completa.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="jugadores.php">
            <div class="panel-heading">
                <h2>Jugadores</h2>
                <p>Consulta la plantilla completa de la temporada activa.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="partidos.php">
            <div class="panel-heading">
                <h2>Partidos</h2>
                <p>Consulta todos los resultados y filtra por equipo.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="normativa.php">
            <div class="panel-heading">
                <h2>Normativa</h2>
                <p>Revisa las reglas basicas y el formato de la competicion.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="noticias.php">
            <div class="panel-heading">
                <h2>Noticias</h2>
                <p>Lee los titulares generados a partir de los resultados recientes.</p>
            </div>
        </a>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


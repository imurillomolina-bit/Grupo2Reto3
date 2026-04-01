<?php

declare(strict_types=1);

// Vista de partidos con filtros por temporada y jornada seleccionadas.

require_once __DIR__ . '/../includes/app_init.php';

// Temporada de sesion para iniciar filtros del lado cliente.
$pageTitle = 'Partidos | FEDERACIÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-matches" data-temporada-sesion="<?php echo e($temporadaSesion); ?>">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Partidos</h2>
            <p>
                Temporada seleccionada: <strong id="temporada_nombre">Cargando...</strong>
                | Jornada: <strong id="jornada_nombre">-</strong>
            </p>

            <!-- Filtros de temporada y jornada para recalcular la tabla de partidos. -->
            <div class="matches-filters">
                <form class="season-form" id="season_form" action="set_temporada.php" method="post">
                    <label for="temporada_id">Cambiar temporada</label>
                    <select id="temporada_id" name="temporada_id" required></select>
                    <button type="submit">Cambiar</button>
                </form>

                <form class="season-form" id="jornada_form" action="#" method="get">
                    <label for="jornada_id">Filtrar por jornada</label>
                    <select id="jornada_id" name="jornada_id" required></select>
                    <button type="submit">Aplicar</button>
                </form>
            </div>
        </article>

        <!-- Contenedor de resultados de la transformacion XSL de partidos. -->
        <article id="partidos_render" class="matches-wrap" aria-label="Listado de partidos">
            <p>Cargando partidos...</p>
        </article>

        <article id="partidos_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar el apartado de partidos con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar Partidos en esta version.</p>
            </article>
        </noscript>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


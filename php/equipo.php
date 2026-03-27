<?php

declare(strict_types=1);

// Vista de detalle de equipo con datos filtrados por temporada seleccionada.

require_once __DIR__ . '/../includes/app_init.php';

// Estado inicial recibido desde sesion y query para sincronizar la vista.
$pageTitle = 'Detalle de equipo | FEDERACIÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));
$equipoIdRaw = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);
$equipoId = is_string($equipoIdRaw) ? trim($equipoIdRaw) : '';

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Informacion del equipo -->
<main class="page page-team" data-temporada-sesion="<?php echo e($temporadaSesion); ?>" data-equipo-id="<?php echo e($equipoId); ?>">
    <!-- Section: Ficha principal -->
    <section class="panel team-panel">
        <article class="panel-heading">
            <h2 id="equipos_titulo">Equipos</h2>
            <p>Temporada activa: <strong id="temporada_nombre">Cargando...</strong></p>

            <?php if ($equipoId === ''): ?>
                <form class="season-form" id="season_form" action="#" method="get">
                    <label for="temporada_id">Cambiar temporada</label>
                    <select id="temporada_id" name="temporada_id" required></select>
                    <button type="submit">Cambiar</button>
                </form>
            <?php endif; ?>
        </article>

        <article id="equipos_render" class="cards-grid team-summary-grid" aria-label="Listado y detalle de equipos">
            <p>Cargando equipos...</p>
        </article>

        <article id="equipos_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar el apartado de equipos con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar Equipos en esta versión.</p>
            </article>
        </noscript>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


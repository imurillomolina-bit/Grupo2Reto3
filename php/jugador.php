<?php

declare(strict_types=1);

// Vista de ficha individual de jugador segun la temporada en sesion.

require_once __DIR__ . '/../includes/app_init.php';

// Estado inicial de temporada y jugador para hidratar el cliente.
$pageTitle = 'Ficha de jugador | FEDERACIÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));
$jugadorIdRaw = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);
$jugadorId = is_string($jugadorIdRaw) ? trim($jugadorIdRaw) : '';

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-player-detail" data-temporada-sesion="<?php echo e($temporadaSesion); ?>" data-jugador-id="<?php echo e($jugadorId); ?>">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Ficha de jugador</h2>
            <p>Temporada activa: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="#" method="get">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="jugador_render" class="player-basic-detail" aria-label="Ficha de jugador">
            <p>Cargando jugador...</p>
        </article>

        <article id="jugador_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar la ficha de jugador con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar la ficha del jugador en esta version.</p>
            </article>
        </noscript>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


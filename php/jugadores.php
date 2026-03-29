<?php

declare(strict_types=1);

// Vista de listado de jugadores con soporte de cambio de temporada.

require_once __DIR__ . '/../includes/app_init.php';

// Temporada de sesion para precargar la seleccion en cliente.
$pageTitle = 'Jugadores | FEDERACIÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-players" data-temporada-sesion="<?php echo e($temporadaSesion); ?>">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Jugadores</h2>
            <p>Temporada activa: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="set_temporada.php" method="post">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="jugadores_render" aria-label="Listado de jugadores">
            <p>Cargando jugadores...</p>
        </article>

        <article id="jugadores_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar el apartado de jugadores con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar Jugadores en esta version.</p>
            </article>
        </noscript>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

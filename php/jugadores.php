<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Jugadores | FEDERACIÓN FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$jugadores = [];
$temporadas = [];

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $temporadas = get_temporadas($xml);
    $jugadores = get_jugadores_temporada($temporada);
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-players">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Jugadores</h2>
            <p>Temporada activa: <strong><?php echo e($temporadaNombre); ?></strong></p>

            <form class="season-form" action="set_temporada.php" method="post">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required>
                    <?php foreach ($temporadas as $temporadaItem): ?>
                        <option
                            value="<?php echo e($temporadaItem['id']); ?>"
                            <?php echo (($temporadaItem['id'] ?? '') === ($_SESSION['temporada_actual'] ?? '')) ? 'selected' : ''; ?>
                        >
                            <?php echo e($temporadaItem['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <?php if ($error !== null): ?>
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
            </article>
        <?php else: ?>
            <article class="cards-grid player-spotlight-grid">
                <?php foreach ($jugadores as $jugador): ?>
                    <figure class="player-card spotlight-card">
                        <a class="player-image-link" href="jugador.php?id=<?php echo (int) $jugador['id']; ?>" aria-label="Ver ficha de <?php echo e($jugador['nombre']); ?>">
                            <img src="<?php echo e($jugador['foto']); ?>" alt="Foto de <?php echo e($jugador['nombre']); ?>">
                        </a>
                        <figcaption>
                            <strong class="player-card-name"><?php echo e($jugador['nombre']); ?></strong>
                            <span class="player-card-position"><?php echo e($jugador['posicion']); ?></span>
                            <small>
                                <a href="equipo.php?id=<?php echo (int) $jugador['equipo_id']; ?>"><?php echo e($jugador['equipo']); ?></a>
                            </small>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
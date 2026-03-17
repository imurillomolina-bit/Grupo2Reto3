<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Detalle de equipo | FEDERACIÓN FUTSAL';

$error = null;
$equipos = [];
$equipo = null;
$partidos = [];
$temporadaNombre = 'No disponible';
$temporadas = [];

$equipoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $temporadas = get_temporadas($xml);
    $equipos = get_equipos_temporada($temporada);

    if ($equipoId !== false && $equipoId !== null && $equipoId > 0) {
        $equipo = find_equipo_by_id($temporada, (int) $equipoId);
        if ($equipo === null) {
            $error = 'No existe el equipo solicitado en la temporada activa.';
        } else {
            $partidos = get_partidos_equipo($temporada, (int) $equipoId);
            $pageTitle = (string) $equipo->nombre . ' | FEDERACIÓN FUTSAL';
        }
    } elseif ($equipoId === false) {
        $error = 'El ID del equipo es invalido.';
    }
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Informacion del equipo -->
<main class="page page-team">
    <!-- Section: Ficha principal -->
    <section class="panel team-panel">
        <article class="panel-heading">
            <h2><?php echo ($equipo === null) ? 'Equipos' : 'Detalle de equipo'; ?></h2>
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
                <h2>Error</h2>
                <p><?php echo e($error); ?></p>
                <p><a href="clasificacion.php">Volver a la clasificacion</a></p>
            </article>
        <?php elseif ($equipo === null): ?>
            <article class="cards-grid team-summary-grid">
                <?php foreach ($equipos as $equipoItem): ?>
                    <a class="info-card team-summary-card" href="equipo.php?id=<?php echo (int) $equipoItem['id']; ?>">
                        <img src="<?php echo e($equipoItem['escudo']); ?>" alt="Escudo de <?php echo e($equipoItem['nombre']); ?>">
                        <div>
                            <h3><?php echo e($equipoItem['nombre']); ?></h3>
                            <p><?php echo e($equipoItem['descripcion']); ?></p>
                            <span><?php echo e($equipoItem['ciudad']); ?> · <?php echo e($equipoItem['estadio']); ?> · <?php echo (int) $equipoItem['jugadores']; ?> jugadores</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </article>
        <?php else: ?>
            <article class="team-headline">
                <img class="team-shield-large" src="<?php echo e((string) $equipo->escudo); ?>" alt="Escudo de <?php echo e((string) $equipo->nombre); ?>">
                <div>
                    <p class="meta">Temporada: <?php echo e($temporadaNombre); ?></p>
                    <h2><?php echo e((string) $equipo->nombre); ?></h2>
                    <p><?php echo e((string) $equipo->descripcion); ?></p>
                    <p><strong>Estadio:</strong> <?php echo e((string) $equipo->estadio); ?></p>
                    <p><strong>Ciudad:</strong> <?php echo e((string) $equipo->ciudad); ?></p>
                </div>
            </article>

            <!-- Article: Galeria de jugadores -->
            <article>
                <h3>Plantilla de jugadores</h3>
                <div class="player-grid">
                    <?php foreach ($equipo->jugadores->jugador as $jugador): ?>
                        <figure class="player-card">
                            <img src="<?php echo e(build_jugador_avatar_url((string) $jugador->nombre)); ?>" alt="Foto de <?php echo e((string) $jugador->nombre); ?>">
                            <figcaption>
                                <strong><?php echo e((string) $jugador->nombre); ?></strong>
                                <span><?php echo e((string) $jugador->posicion); ?></span>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </article>

            <!-- Article: Historial de partidos -->
            <article>
                <h3>Partidos de la temporada</h3>
                <div class="matches-wrap">
                    <table class="matches-table">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Local</th>
                            <th>Visitante</th>
                            <th>Marcador</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($partidos as $partido): ?>
                            <tr>
                                <td><?php echo e($partido['fecha']); ?></td>
                                <td><?php echo e($partido['local']); ?></td>
                                <td><?php echo e($partido['visitante']); ?></td>
                                <td><strong><?php echo e($partido['marcador']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

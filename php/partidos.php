<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Partidos | FEDERACIÓN FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$temporadas = [];
$equipos = [];
$partidos = [];
$equipoSeleccionado = null;

$equipoId = filter_input(INPUT_GET, 'equipo_id', FILTER_VALIDATE_INT);

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $temporadas = get_temporadas($xml);
    $equipos = get_equipos_temporada($temporada);

    if ($equipoId !== false && $equipoId !== null && $equipoId > 0) {
        $equipo = find_equipo_by_id($temporada, (int) $equipoId);
        if ($equipo === null) {
            $error = 'El equipo indicado no existe en la temporada activa.';
        } else {
            $equipoSeleccionado = [
                'id' => (int) $equipo['id'],
                'nombre' => (string) $equipo->nombre,
            ];
            $partidos = get_partidos_equipo($temporada, (int) $equipoId);
        }
    } elseif ($equipoId === false) {
        $error = 'El filtro de equipo es invalido.';
    }

    if ($error === null && $equipoSeleccionado === null) {
        $partidos = get_partidos_recientes($temporada);
    }
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<main class="page">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Partidos</h2>
            <p>
                Temporada seleccionada: <strong><?php echo e($temporadaNombre); ?></strong>
                <?php if ($equipoSeleccionado !== null): ?>
                    | Equipo: <strong><?php echo e($equipoSeleccionado['nombre']); ?></strong>
                <?php endif; ?>
            </p>

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

            <form class="season-form" method="get" action="partidos.php">
                <label for="equipo_id">Filtrar por equipo</label>
                <select id="equipo_id" name="equipo_id">
                    <option value="">Todos</option>
                    <?php foreach ($equipos as $equipoItem): ?>
                        <option
                            value="<?php echo (int) $equipoItem['id']; ?>"
                            <?php echo ($equipoSeleccionado !== null && $equipoSeleccionado['id'] === (int) $equipoItem['id']) ? 'selected' : ''; ?>
                        >
                            <?php echo e($equipoItem['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Aplicar</button>
            </form>
        </article>

        <?php if ($error !== null): ?>
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
                <p><a href="partidos.php">Ver todos los partidos</a></p>
            </article>
        <?php else: ?>
            <article class="matches-wrap" aria-label="Listado de partidos">
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
                    <?php if ($partidos === []): ?>
                        <tr>
                            <td colspan="4">No hay partidos disponibles para este filtro.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($partidos as $partido): ?>
                            <tr>
                                <td><?php echo e($partido['fecha']); ?></td>
                                <td><?php echo e($partido['local']); ?></td>
                                <td><?php echo e($partido['visitante']); ?></td>
                                <td><strong><?php echo e($partido['marcador']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

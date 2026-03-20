<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Partidos | FEDERACIÓN FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$temporadas = [];
$jornadas = [];
$partidos = [];
$jornadaSeleccionada = null;

$jornadaId = filter_input(INPUT_GET, 'jornada_id', FILTER_VALIDATE_INT);

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $temporadas = get_temporadas($xml);
    $jornadas = get_jornadas_temporada($temporada);

    if ($jornadaId !== false && $jornadaId !== null && $jornadaId > 0) {
        $jornadaEncontrada = false;
        foreach ($jornadas as $j) {
            if ($j['numero'] === $jornadaId) {
                $jornadaEncontrada = true;
                $jornadaSeleccionada = [
                    'numero' => $j['numero'],
                    'fecha' => $j['fecha'],
                ];
                break;
            }
        }

        if (!$jornadaEncontrada) {
            $error = 'La jornada indicada no existe en la temporada activa.';
        } else {
            $partidos = get_partidos_jornada($temporada, (int) $jornadaId);
        }
    } elseif ($jornadaId === false) {
        $error = 'El filtro de jornada es invalido.';
    }

    if ($error === null && $jornadaSeleccionada === null && $jornadas !== []) {
        $jornadaSeleccionada = $jornadas[0];
        $partidos = get_partidos_jornada($temporada, $jornadaSeleccionada['numero']);
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
                <?php if ($jornadaSeleccionada !== null): ?>
                    | Jornada: <strong><?php echo e((string) $jornadaSeleccionada['numero']); ?></strong>
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
                <label for="jornada_id">Filtrar por jornada</label>
                <select id="jornada_id" name="jornada_id">
                    <?php foreach ($jornadas as $jornadaItem): ?>
                        <option
                            value="<?php echo (int) $jornadaItem['numero']; ?>"
                            <?php echo ($jornadaSeleccionada !== null && $jornadaSeleccionada['numero'] === (int) $jornadaItem['numero']) ? 'selected' : ''; ?>
                        >
                            Jornada <?php echo e((string) $jornadaItem['numero']); ?>
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

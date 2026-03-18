<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Ficha de jugador | FEDERACION FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$temporadas = [];
$jugador = null;
$caracteristicas = [];

$jugadorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $temporadas = get_temporadas($xml);

    if ($jugadorId === false || $jugadorId === null || $jugadorId <= 0) {
        $error = 'El ID del jugador es invalido.';
    } else {
        $jugador = get_jugador_detalle_by_id($temporada, (int) $jugadorId);
        if ($jugador === null) {
            $error = 'No existe el jugador solicitado en la temporada activa.';
        } else {
            $nombreMostrado = trim($jugador['nombre'] . ' ' . (($jugador['apellidos'] !== 'No disponible') ? $jugador['apellidos'] : ''));
            $jugador['nombre_mostrado'] = $nombreMostrado !== '' ? $nombreMostrado : $jugador['nombre'];
            $pageTitle = $jugador['nombre_mostrado'] . ' | FEDERACION FUTSAL';

            $caracteristicasRaw = [
                ['key' => 'nombre', 'label' => 'Nombre', 'value' => $jugador['nombre']],
                ['key' => 'apellidos', 'label' => 'Apellidos', 'value' => $jugador['apellidos']],
                ['key' => 'fecha_nacimiento', 'label' => 'Nacimiento', 'value' => $jugador['fecha_nacimiento']],
                ['key' => 'peso', 'label' => 'Peso', 'value' => $jugador['peso'] !== 'No disponible' ? ($jugador['peso'] . ' kg') : 'No disponible'],
                ['key' => 'altura', 'label' => 'Altura', 'value' => $jugador['altura'] !== 'No disponible' ? ($jugador['altura'] . ' m') : 'No disponible'],
            ];

            foreach ($caracteristicasRaw as $item) {
                $valor = trim((string) $item['value']);
                if ($valor === '' || mb_strtolower($valor) === 'no disponible') {
                    $valor = '-';
                }

                $caracteristicas[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'value' => $valor,
                ];
            }
        }
    }
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-player-detail">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Ficha de jugador</h2>
            <p>Temporada activa: <strong><?php echo e($temporadaNombre); ?></strong></p>
        </article>

        <?php if ($error !== null): ?>
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
                <p><a href="jugadores.php">Volver al listado de jugadores</a></p>
            </article>
        <?php else: ?>
            <article class="player-basic-detail">
                <div class="player-basic-layout">
                    <aside class="player-basic-media">
                        <img class="player-basic-photo" src="<?php echo e($jugador['foto']); ?>" alt="Foto de <?php echo e($jugador['nombre_mostrado']); ?>">
                    </aside>

                    <div class="player-basic-content">
                        <h3 class="player-basic-title"><?php echo e($jugador['nombre_mostrado']); ?></h3>
                        <div class="player-basic-grid">
                            <?php foreach ($caracteristicas as $item): ?>
                                <article class="player-basic-item">
                                    <h4><?php echo e($item['label']); ?></h4>
                                    <p><?php echo e($item['value']); ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <p class="player-back-link"><a class="btn-outline-back" href="jugadores.php">Volver a jugadores</a></p>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

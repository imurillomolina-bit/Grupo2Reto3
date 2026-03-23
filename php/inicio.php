<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

$pageTitle = 'Inicio | FEDERACIÃ“N FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$resumen = [
    'equipos' => 0,
    'jugadores' => 0,
    'noticias' => 0,
    'lider' => 'Pendiente',
];
$apartados = [];

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];

    $clasificacion = build_clasificacion($temporada);
    $equipos = get_equipos_temporada($temporada);
    $jugadores = get_jugadores_temporada($temporada);
    $noticias = build_noticias_temporada($temporada);

    $resumen['equipos'] = count($equipos);
    $resumen['jugadores'] = count($jugadores);
    $resumen['noticias'] = count($noticias);
    $resumen['lider'] = $clasificacion[0]['nombre'] ?? 'Pendiente';

    $apartados = [
        [
            'titulo' => 'Clasificación',
            'texto' => 'Consulta la tabla completa de la temporada activa y el rendimiento de cada club.',
            'detalle' => 'Líder actual: ' . $resumen['lider'] . '.',
            'enlace' => 'clasificacion.php',
        ],
        [
            'titulo' => 'Equipo',
            'texto' => 'Accede al listado de equipos y entra a la ficha de cada escudo.',
            'detalle' => 'Equipos disponibles: ' . $resumen['equipos'] . '.',
            'enlace' => 'equipo.php',
        ],
        [
            'titulo' => 'Jugadores',
            'texto' => 'Revisa la plantilla vinculada a cada equipo de la temporada.',
            'detalle' => 'Jugadores visibles: ' . $resumen['jugadores'] . '.',
            'enlace' => 'jugadores.php',
        ],
        [
            'titulo' => 'Normativa',
            'texto' => 'Lee las reglas basicas de puntuacion, clasificacion y consulta del portal.',
            'detalle' => 'Resumen rapido del formato competitivo.',
            'enlace' => 'normativa.php',
        ],
        [
            'titulo' => 'Noticias',
            'texto' => 'Mira los titulares generados a partir de resultados y clasificacion.',
            'detalle' => 'Noticias activas: ' . $resumen['noticias'] . '.',
            'enlace' => 'noticias.php',
        ],
    ];
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-start">
    <section class="panel hero-panel">
        <div>
            <h2>Inicio de la liga</h2>
            <p>Desde aqui tienes una vista rapida de todos los apartados disponibles en FEDERACIÓN FUTSAL.</p>
            <p>Temporada activa: <strong><?php echo e($temporadaNombre); ?></strong></p>
        </div>

        <?php if ($error === null): ?>
            <div class="hero-stats" aria-label="Resumen general">
                <div class="hero-stat">
                    <strong>Equipos</strong>
                    <span><?php echo (int) $resumen['equipos']; ?></span>
                </div>
                <div class="hero-stat">
                    <strong>Jugadores</strong>
                    <span><?php echo (int) $resumen['jugadores']; ?></span>
                </div>
                <div class="hero-stat">
                    <strong>Noticias</strong>
                    <span><?php echo (int) $resumen['noticias']; ?></span>
                </div>
                <div class="hero-stat">
                    <strong>Líder</strong>
                    <span><?php echo e($resumen['lider']); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($error !== null): ?>
        <section class="panel">
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
            </article>
        </section>
    <?php else: ?>
        <section class="summary-grid" aria-label="Resumen de apartados">
            <?php foreach ($apartados as $apartado): ?>
                <article class="panel summary-card">
                    <h3><?php echo e($apartado['titulo']); ?></h3>
                    <p><?php echo e($apartado['texto']); ?></p>
                    <p><?php echo e($apartado['detalle']); ?></p>
                    <a class="summary-link" href="<?php echo e($apartado['enlace']); ?>">Ir al apartado</a>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

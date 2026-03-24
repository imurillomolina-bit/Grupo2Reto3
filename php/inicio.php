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
$resumenPosiciones = [];
$microTitulares = [];
$dueloDestacado = null;
$mejorAtaque = null;
$mejorDefensa = null;
$topClasificacion = [];
$ultimosMarcadores = [];
$metricasCompeticion = [];
$ciudadesSede = [];
$promedioJugadoresEquipo = 0.0;

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];

    $clasificacion = build_clasificacion($temporada);
    $equipos = get_equipos_temporada($temporada);
    $jugadores = get_jugadores_temporada($temporada);
    $noticias = build_noticias_temporada($temporada);
    $partidosRecientes = get_partidos_recientes($temporada);

    $resumen['equipos'] = count($equipos);
    $resumen['jugadores'] = count($jugadores);
    $resumen['noticias'] = count($noticias);
    $resumen['lider'] = $clasificacion[0]['nombre'] ?? 'Pendiente';
    $topClasificacion = array_slice($clasificacion, 0, 5);
    $ultimosMarcadores = array_slice($partidosRecientes, 0, 6);
    $promedioJugadoresEquipo = $resumen['equipos'] > 0 ? ($resumen['jugadores'] / $resumen['equipos']) : 0.0;

    $dueloDestacado = $partidosRecientes[0] ?? null;

    $ataques = $clasificacion;
    usort($ataques, static function (array $a, array $b): int {
        return [$b['gf'], $a['nombre']] <=> [$a['gf'], $b['nombre']];
    });
    $mejorAtaque = $ataques[0] ?? null;

    $defensas = $clasificacion;
    usort($defensas, static function (array $a, array $b): int {
        return [$a['gc'], $a['nombre']] <=> [$b['gc'], $b['nombre']];
    });
    $mejorDefensa = $defensas[0] ?? null;

    $conteoPosiciones = [
        'Portero' => 0,
        'Cierre' => 0,
        'Ala' => 0,
        'Pivote' => 0,
        'Otros' => 0,
    ];

    foreach ($jugadores as $jugador) {
        $posicion = trim((string) ($jugador['posicion'] ?? ''));
        if (isset($conteoPosiciones[$posicion])) {
            $conteoPosiciones[$posicion]++;
            continue;
        }

        $conteoPosiciones['Otros']++;
    }

    foreach ($conteoPosiciones as $posicion => $total) {
        if ($total <= 0) {
            continue;
        }

        $resumenPosiciones[] = [
            'nombre' => $posicion,
            'total' => $total,
            'porcentaje' => (int) round(($total / max(1, $resumen['jugadores'])) * 100),
        ];
    }

    usort($resumenPosiciones, static function (array $a, array $b): int {
        return [$b['total'], $a['nombre']] <=> [$a['total'], $b['nombre']];
    });

    if ($dueloDestacado !== null) {
        $microTitulares[] = $dueloDestacado['local'] . ' y ' . $dueloDestacado['visitante'] . ' firmaron el ' . $dueloDestacado['marcador'] . ' mas reciente.';
    }

    if ($mejorAtaque !== null) {
        $microTitulares[] = $mejorAtaque['nombre'] . ' lidera el ataque con ' . $mejorAtaque['gf'] . ' goles a favor.';
    }

    if ($mejorDefensa !== null) {
        $microTitulares[] = $mejorDefensa['nombre'] . ' marca la mejor defensa con solo ' . $mejorDefensa['gc'] . ' goles encajados.';
    }

    $totalPartidos = 0;
    $totalGoles = 0;
    $victoriasLocal = 0;
    $victoriasVisitante = 0;
    $empates = 0;
    $maxGolesPartido = -1;
    $partidoEspectaculo = null;

    foreach ($partidosRecientes as $partidoReciente) {
        $totalPartidos++;
        $golesLocal = (int) $partidoReciente['goles_local'];
        $golesVisitante = (int) $partidoReciente['goles_visitante'];
        $golesPartido = $golesLocal + $golesVisitante;
        $totalGoles += $golesPartido;

        if ($golesLocal > $golesVisitante) {
            $victoriasLocal++;
        } elseif ($golesLocal < $golesVisitante) {
            $victoriasVisitante++;
        } else {
            $empates++;
        }

        if ($golesPartido > $maxGolesPartido) {
            $maxGolesPartido = $golesPartido;
            $partidoEspectaculo = $partidoReciente;
        }
    }

    $mediaGoles = $totalPartidos > 0 ? number_format($totalGoles / $totalPartidos, 2, '.', '') : '0.00';

    if ($partidoEspectaculo !== null) {
        $partidoShow = $partidoEspectaculo['local'] . ' ' . $partidoEspectaculo['marcador'] . ' ' . $partidoEspectaculo['visitante'];
    } else {
        $partidoShow = 'Sin datos';
    }

    $metricasCompeticion = [
        ['etiqueta' => 'Partidos disputados', 'valor' => (string) $totalPartidos],
        ['etiqueta' => 'Goles totales', 'valor' => (string) $totalGoles],
        ['etiqueta' => 'Media de goles', 'valor' => $mediaGoles],
        ['etiqueta' => 'Victorias local', 'valor' => (string) $victoriasLocal],
        ['etiqueta' => 'Victorias visitante', 'valor' => (string) $victoriasVisitante],
        ['etiqueta' => 'Empates', 'valor' => (string) $empates],
        ['etiqueta' => 'Partido mas abierto', 'valor' => $partidoShow],
    ];

    $ciudadesIndexadas = [];
    foreach ($equipos as $equipo) {
        $ciudad = trim((string) ($equipo['ciudad'] ?? ''));
        if ($ciudad === '') {
            continue;
        }

        $ciudadesIndexadas[$ciudad] = true;
    }
    $ciudadesSede = array_keys($ciudadesIndexadas);
    sort($ciudadesSede);

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
        <section class="panel start-insights" aria-label="Radar de temporada">
            <article class="insight-card insight-card-spotlight">
                <p class="insight-kicker">Radar competitivo</p>
                <h3>Panorama express de la jornada</h3>

                <?php if ($dueloDestacado !== null): ?>
                    <p class="insight-highlight">
                        Duelo mas reciente: <strong><?php echo e($dueloDestacado['local']); ?></strong>
                        <span><?php echo e($dueloDestacado['marcador']); ?></span>
                        <strong><?php echo e($dueloDestacado['visitante']); ?></strong>
                    </p>
                <?php endif; ?>

                <?php if ($microTitulares !== []): ?>
                    <ul class="insight-list">
                        <?php foreach ($microTitulares as $titular): ?>
                            <li><?php echo e($titular); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="insight-card insight-card-positions">
                <p class="insight-kicker">Mapa de plantilla</p>
                <h3>Reparto por posicion</h3>

                <?php if ($resumenPosiciones === []): ?>
                    <p>No hay datos de posiciones para esta temporada.</p>
                <?php else: ?>
                    <div class="position-bars">
                        <?php foreach ($resumenPosiciones as $posicion): ?>
                            <div class="position-row">
                                <span class="position-name"><?php echo e($posicion['nombre']); ?></span>
                                <div class="position-track" aria-hidden="true">
                                    <span style="width: <?php echo (int) $posicion['porcentaje']; ?>%;"></span>
                                </div>
                                <span class="position-count"><?php echo (int) $posicion['total']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>

        <section class="panel start-data-grid" aria-label="Datos competitivos ampliados">
            <article class="data-card">
                <p class="insight-kicker">Zona alta</p>
                <h3>Top 5 de clasificacion</h3>

                <?php if ($topClasificacion === []): ?>
                    <p>No hay tabla disponible para esta temporada.</p>
                <?php else: ?>
                    <div class="mini-table" role="table" aria-label="Top 5">
                        <div class="mini-row mini-row-head" role="row">
                            <span>#</span>
                            <span>Equipo</span>
                            <span>PTS</span>
                            <span>DG</span>
                        </div>
                        <?php foreach ($topClasificacion as $index => $fila): ?>
                            <div class="mini-row" role="row">
                                <span><?php echo (int) ($index + 1); ?></span>
                                <span><?php echo e($fila['nombre']); ?></span>
                                <span><?php echo (int) $fila['pts']; ?></span>
                                <span><?php echo (int) $fila['dg']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="data-card">
                <p class="insight-kicker">Ritmo de competicion</p>
                <h3>Metricas de temporada</h3>

                <?php if ($metricasCompeticion === []): ?>
                    <p>No hay metricas disponibles.</p>
                <?php else: ?>
                    <ul class="metric-list">
                        <?php foreach ($metricasCompeticion as $metrica): ?>
                            <li>
                                <span><?php echo e($metrica['etiqueta']); ?></span>
                                <strong><?php echo e($metrica['valor']); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        </section>

        <section class="panel start-data-grid" aria-label="Actividad y contexto territorial">
            <article class="data-card">
                <p class="insight-kicker">Actividad reciente</p>
                <h3>Ultimos marcadores</h3>

                <?php if ($ultimosMarcadores === []): ?>
                    <p>No hay partidos recientes para mostrar.</p>
                <?php else: ?>
                    <ul class="score-list">
                        <?php foreach ($ultimosMarcadores as $marcador): ?>
                            <li>
                                <span><?php echo e($marcador['fecha']); ?></span>
                                <strong><?php echo e($marcador['local']); ?> <?php echo e($marcador['marcador']); ?> <?php echo e($marcador['visitante']); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>

            <article class="data-card">
                <p class="insight-kicker">Mapa de sedes</p>
                <h3>Territorios y densidad de plantilla</h3>
                <p>Promedio de jugadores por equipo: <strong><?php echo number_format($promedioJugadoresEquipo, 1, '.', ''); ?></strong></p>

                <?php if ($ciudadesSede === []): ?>
                    <p>No hay ciudades registradas.</p>
                <?php else: ?>
                    <div class="city-tags" aria-label="Ciudades con equipos">
                        <?php foreach ($ciudadesSede as $ciudad): ?>
                            <span><?php echo e($ciudad); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>

    <?php endif; ?>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

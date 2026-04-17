<?php

declare(strict_types=1);

// Panel operativo: resumen de temporada para los roles Manager y Admin.

require_once __DIR__ . '/../includes/app_init.php';

// Control de acceso: solo Manager o Admin pueden entrar al panel.
$rolSesion = trim((string) ($_SESSION['rol'] ?? ''));
if (strcasecmp($rolSesion, 'Admin') !== 0 && strcasecmp($rolSesion, 'Manager') !== 0) {
    $_SESSION['flash_error'] = 'No tienes permisos para acceder al Panel.';
    header('Location: inicio.php');
    exit;
}

$pageTitle = 'Panel | FEDERACIÓN FUTSAL';

$error = null;
// Variables base que alimentan los indicadores y listados de la vista.
$temporadaNombre = 'No disponible';
$numEquipos = 0;
$numJugadores = 0;
$numPartidos = 0;
$totalGoles = 0;
$mediaGoles = '0.00';
$top3 = [];
$ultimosPartidos = [];

try {
    // Se carga el XML principal y se extrae la temporada activa para construir el panel.
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];

    // Se recopilan los datos necesarios para los KPI y las secciones principales.
    $equipos = get_equipos_temporada($temporada);
    $jugadores = get_jugadores_temporada($temporada);
    $clasificacion = build_clasificacion($temporada);
    $partidos = get_partidos_recientes($temporada);

    // Conteos generales de la temporada activa.
    $numEquipos = count($equipos);
    $numJugadores = count($jugadores);
    $numPartidos = count($partidos);

    // Cálculo de goles totales y media por partido.
    foreach ($partidos as $partido) {
        $totalGoles += (int) $partido['goles_local'] + (int) $partido['goles_visitante'];
    }
    $mediaGoles = $numPartidos > 0
        ? number_format($totalGoles / $numPartidos, 2, '.', '')
        : '0.00';

    $top3 = array_slice($clasificacion, 0, 3);
    $ultimosPartidos = array_slice($partidos, 0, 5);
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

$esAdmin = strcasecmp($rolSesion, 'Admin') === 0;

require __DIR__ . '/../includes/header.php';
?>

<!-- Contenido principal del panel operativo -->
<main class="page page-panel">

    <!-- Cabecera con el contexto general de la temporada -->
    <section class="panel hero-panel">
        <div>
            <h2>Panel operativo</h2>
            <p>Vista consolidada de la temporada activa para la gestión interna.</p>
            <p>Temporada activa: <strong><?php echo e($temporadaNombre); ?></strong></p>
        </div>
    </section>

    <?php if ($error !== null): ?>
        <!-- Error de carga de XML -->
        <section class="panel">
            <article class="panel-error">
                <p>No se pudieron cargar los datos: <?php echo e($error); ?></p>
            </article>
        </section>
    <?php else: ?>

        <!-- Bloque de indicadores clave de la temporada -->
        <section class="panel start-data-grid" aria-label="Indicadores clave de la temporada">
            <article class="data-card">
                <p class="insight-kicker">Resumen operativo</p>
                <h3>KPIs de la temporada</h3>
                <ul class="metric-list">
                    <li>
                        <span>Equipos</span>
                        <strong><?php echo $numEquipos; ?></strong>
                    </li>
                    <li>
                        <span>Jugadores</span>
                        <strong><?php echo $numJugadores; ?></strong>
                    </li>
                    <li>
                        <span>Partidos disputados</span>
                        <strong><?php echo $numPartidos; ?></strong>
                    </li>
                    <li>
                        <span>Goles totales</span>
                        <strong><?php echo $totalGoles; ?></strong>
                    </li>
                    <li>
                        <span>Media de goles por partido</span>
                        <strong><?php echo e($mediaGoles); ?></strong>
                    </li>
                </ul>
            </article>

            <!-- Top 3 de clasificación con el estado de la zona alta -->
            <article class="data-card">
                <p class="insight-kicker">Zona alta</p>
                <h3>Top 3 de clasificación</h3>
                <?php if ($top3 === []): ?>
                    <p>No hay datos de clasificación disponibles.</p>
                <?php else: ?>
                    <div class="mini-table" role="table" aria-label="Top 3 clasificación">
                        <div class="mini-row mini-row-head" role="row">
                            <span>#</span>
                            <span>Equipo</span>
                            <span>PTS</span>
                        </div>
                        <?php foreach ($top3 as $pos => $fila): ?>
                            <div class="mini-row" role="row">
                                <span><?php echo (int) ($pos + 1); ?></span>
                                <span><?php echo e($fila['nombre']); ?></span>
                                <span><?php echo (int) $fila['pts']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>

        <!-- Últimos resultados disponibles para consulta rápida -->
        <section class="panel content-panel" aria-label="Últimos resultados">
            <article class="panel-heading">
                <h3>Últimos resultados</h3>
            </article>
            <article class="matches-wrap">
                <?php if ($ultimosPartidos === []): ?>
                    <p>No hay partidos recientes para mostrar.</p>
                <?php else: ?>
                    <table class="matches-table">
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Local</th>
                            <th>Marcador</th>
                            <th>Visitante</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimosPartidos as $partido): ?>
                            <tr>
                                <td><?php echo e($partido['fecha']); ?></td>
                                <td><?php echo e($partido['local']); ?></td>
                                <td><?php echo e($partido['marcador']); ?></td>
                                <td><?php echo e($partido['visitante']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </article>
        </section>

    <?php endif; ?>

    <!-- Accesos rápidos a las secciones principales del portal -->
    <section class="panel start-data-grid" aria-label="Accesos rápidos">
        <a class="panel quick-link-panel" href="clasificacion.php">
            <article class="panel-heading">
                <h3>Clasificación</h3>
                <p>Tabla de posiciones de la temporada activa.</p>
            </article>
        </a>
        <a class="panel quick-link-panel" href="partidos.php">
            <article class="panel-heading">
                <h3>Partidos</h3>
                <p>Consulta y filtrado de encuentros por jornada.</p>
            </article>
        </a>
        <a class="panel quick-link-panel" href="jugadores.php">
            <article class="panel-heading">
                <h3>Jugadores</h3>
                <p>Listado y fichas individuales de la plantilla.</p>
            </article>
        </a>
        <a class="panel quick-link-panel" href="noticias.php">
            <article class="panel-heading">
                <h3>Noticias</h3>
                <p>Titulares y novedades de la temporada.</p>
            </article>
        </a>
        <a class="panel quick-link-panel" href="equipo.php">
            <article class="panel-heading">
                <h3>Equipos</h3>
                <p>Fichas y detalle de cada club registrado.</p>
            </article>
        </a>
        <?php if ($esAdmin): ?>
            <a class="panel quick-link-panel" href="usuarios.php">
                <article class="panel-heading">
                    <h3>Usuarios</h3>
                    <p>Gestión de cuentas e historial de accesos.</p>
                </article>
            </a>
        <?php endif; ?>
    </section>

</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

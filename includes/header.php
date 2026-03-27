<?php

declare(strict_types=1);

// Plantilla de cabecera: titulo, mensajes flash y selector global de temporada.

$currentPageTitle = $pageTitle ?? 'FEDERACIÓN FUTSAL';
// Se leen mensajes flash de sesion y se consumen para no repetirlos.
$flashError = $_SESSION['flash_error'] ?? null;
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Carga de temporadas para mostrar la activa en cabecera.
$temporadasHeader = [];
try {
    $xmlHeader = load_liga_xml();
    $temporadasHeader = get_temporadas($xmlHeader);
} catch (Throwable $ex) {
    $temporadasHeader = [];
}

$temporadaActualNombre = 'No disponible';
$temporadaActualId = (string) ($_SESSION['temporada_actual'] ?? '');
// Busca el nombre legible de la temporada guardada en sesion.
foreach ($temporadasHeader as $temporadaItem) {
    if (($temporadaItem['id'] ?? '') === $temporadaActualId) {
        $temporadaActualNombre = (string) ($temporadaItem['nombre'] ?? 'No disponible');
        break;
    }
}

// Si no hay coincidencia, usa la primera temporada disponible como respaldo.
if ($temporadaActualNombre === 'No disponible' && $temporadasHeader !== []) {
    $temporadaActualNombre = (string) ($temporadasHeader[0]['nombre'] ?? 'No disponible');
}

// Ajusta rutas relativas segun si estamos en /php o en la raiz del proyecto.
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$isPhpPage = str_ends_with($scriptDir, '/php');
$assetPrefix = $isPhpPage ? '../' : '';
$pagePrefix = $isPhpPage ? '' : 'php/';
$stylesVersion = (string) (@filemtime(__DIR__ . '/../css/styles.css') ?: time());
$clasificacionPath = $isPhpPage ? 'clasificacion.php' : 'php/clasificacion.php';
// Estado de sesion para condicionar chips, rol y enlaces de acceso/salida.
$sessionUser = trim((string) ($_SESSION['user'] ?? ''));
$sessionRole = trim((string) ($_SESSION['rol'] ?? ''));
$showSessionChip = $sessionUser !== '' || $sessionRole !== '';
$showSessionRole = $sessionRole !== '';
$showLogoutLink = $showSessionChip;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($currentPageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo e($assetPrefix . 'css/styles.css?v=' . $stylesVersion); ?>">
</head>
<body>
    <!-- Header: Identidad principal -->
    <header class="site-header">
        <div class="header-top">
            <div class="brand-wrap">
                <p class="league-tag">Liga Oficial</p>
                <h1>FEDERACIÓN FUTSAL</h1>
                <p class="subtitle">Pasión, velocidad y estrategia en cada jornada.</p>
            </div>

            <div class="header-tools">
                <?php if ($showSessionChip): ?>
                    <p class="session-chip">
                        <?php echo e($sessionUser); ?><?php if ($showSessionRole): ?> [<?php echo e($sessionRole); ?>]<?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nav: Navegacion principal -->
        <nav class="main-nav" aria-label="Navegacion principal" id="main-nav">
            <button class="nav-toggle" aria-label="Abrir menÃº" aria-expanded="false" aria-controls="nav-collapse">
                <span></span><span></span><span></span>
            </button>

            <div class="nav-collapse" id="nav-collapse">
                <div class="main-nav-left">
                    <div class="main-nav-links">
                        <a href="<?php echo e($pagePrefix . 'inicio.php'); ?>">Inicio</a>
                        <a href="<?php echo e($clasificacionPath); ?>">Clasificación</a>
                        <a href="<?php echo e($pagePrefix . 'equipo.php'); ?>">Equipos</a>
                        <a href="<?php echo e($pagePrefix . 'partidos.php'); ?>">Partidos</a>
                        <a href="<?php echo e($pagePrefix . 'jugadores.php'); ?>">Jugadores</a>
                        <?php if (strcasecmp($sessionRole, 'Admin') === 0): ?>
                            <a href="<?php echo e($pagePrefix . 'usuarios.php'); ?>">Usuarios</a>
                        <?php endif; ?>
                        <a href="<?php echo e($pagePrefix . 'normativa.php'); ?>">Normativa</a>
                        <a href="<?php echo e($pagePrefix . 'noticias.php'); ?>">Noticias</a>
                    </div>
                </div>
                <div class="main-nav-right">
                    <div class="header-access" aria-label="Acciones de sesion">
                        <?php if (!$showLogoutLink): ?>
                            <a href="<?php echo e($pagePrefix . 'login.php'); ?>">Acceso</a>
                        <?php endif; ?>
                        <?php if ($showLogoutLink): ?>
                            <a href="<?php echo e($pagePrefix . 'logout.php'); ?>">Cerrar sesion</a>
                        <?php endif; ?>
                    </div>
                    <p class="season-indicator">Temporada: <?php echo e($temporadaActualNombre); ?></p>
                </div>
            </div>
        </nav>
    </header>

    <?php if ($flashError): ?>
        <p class="flash flash-error"><?php echo e((string) $flashError); ?></p>
    <?php endif; ?>

    <?php if ($flashSuccess): ?>
        <p class="flash flash-ok"><?php echo e((string) $flashSuccess); ?></p>
    <?php endif; ?>


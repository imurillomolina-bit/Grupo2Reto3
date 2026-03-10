<?php

declare(strict_types=1);

// Carga utilidades comunes: sesion, XML, autenticacion y calculos.
require __DIR__ . '/arranque.php';

// Si el XML falla, respondemos con 500 para evitar render parcial.
try {
    $xml = load_futsal_xml();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error cargando datos XML: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

// Verifica inactividad y cierra sesion automaticamente si supera timeout.
apply_session_timeout($xml);

// Controlador de acciones de formulario (login, logout, etc.).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Login normal con usuario/clave desde XML.
    if ($action === 'login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));

        if ($username === '' || $password === '') {
            set_flash('error', 'Debes completar usuario y contraseña.');
        } else {
            $user = authenticate_user($xml, $username, $password);
            if ($user === null) {
                set_flash('error', 'Usuario o contraseña incorrectos.');
            } else {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name'],
                    'is_guest' => $user['is_guest'],
                ];
                set_flash('success', 'Sesion iniciada correctamente.');
            }
        }
    }

    // Login rapido como invitado (rol restringido).
    if ($action === 'guest_login') {
        $guest = get_guest_user($xml);
        $_SESSION['user'] = [
            'id' => $guest['id'],
            'username' => $guest['username'],
            'role' => $guest['role'],
            'full_name' => $guest['full_name'],
            'is_guest' => true,
        ];
        set_flash('success', 'Has entrado como invitado.');
    }

    // Cierre de sesion manual.
    if ($action === 'logout') {
        unset($_SESSION['user']);
        set_flash('success', 'Sesion cerrada correctamente.');
    }

    // Cambio de temporada seleccionada via formulario.
    if ($action === 'select_season') {
        $seasonId = (int) ($_POST['season_id'] ?? 0);
        if ($seasonId <= 0 || get_season_by_id($xml, $seasonId) === null) {
            set_flash('error', 'Temporada seleccionada no valida.');
        } else {
            $_SESSION['season_id'] = $seasonId;
            set_flash('success', 'Temporada cambiada correctamente.');
        }
    }

    // Patron PRG (Post/Redirect/Get) para evitar reenvio del formulario.
    $redirectPage = $_GET['page'] ?? 'home';
    header('Location: index.php?page=' . urlencode((string) $redirectPage));
    exit;
}

// Carga de contexto base para pintar la pagina.
$allSeasons = get_seasons($xml);
$currentSeason = get_current_season($xml);
$selectedSeason = $currentSeason;

// Si el usuario eligio temporada previamente, se mantiene en sesion.
if (isset($_SESSION['season_id'])) {
    $season = get_season_by_id($xml, (int) $_SESSION['season_id']);
    if ($season !== null) {
        $selectedSeason = $season;
    }
}

// Lista blanca de vistas permitidas para evitar includes arbitrarios.
$page = (string) ($_GET['page'] ?? 'home');
$allowedPages = ['home', 'clasificacion', 'equipos', 'equipo', 'jugadores', 'partidos', 'noticias', 'normativa', 'buscar'];
if (!in_array($page, $allowedPages, true)) {
    $page = 'home';
}

// Reglas de acceso por rol: partidos requiere usuario no invitado.
$user = $_SESSION['user'] ?? null;
if ($page === 'partidos' && $user === null) {
    set_flash('error', 'Debes iniciar sesion para ver partidos.');
    header('Location: index.php?page=home');
    exit;
}

if ($page === 'partidos' && $user !== null && (($user['is_guest'] ?? false) === true)) {
    set_flash('error', 'Con rol Invitado no puedes acceder a partidos.');
    header('Location: index.php?page=home');
    exit;
}

// Flash para feedback visual de la ultima accion del usuario.
$flash = get_flash();

// Estructuras precargadas para que las vistas sean simples y rapidas.
$teamsMap = get_teams_map($xml);
$playersMap = get_players_map($xml);
$seasonShields = $selectedSeason ? get_team_shield_for_season($selectedSeason) : [];
$seasonPhotos = $selectedSeason ? get_player_photo_for_season($selectedSeason) : [];
$teamSeasonRelations = get_team_season_relations($xml);

// Marca enlace de menu activo segun pagina actual.
function is_active_page(string $page, string $current): string
{
    return $page === $current ? 'is-active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Futsal - Fase 3</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/php-portal.css">
</head>
<body>
<header class="portal-header">
    <!-- Cabecera dinamica: temporada activa + estado de sesion -->
    <div class="portal-header__top">
        <h1>Federacion Futsal</h1>
        <div class="portal-header__meta">
            <span>Temporada actual: <strong><?= htmlspecialchars($selectedSeason['name'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></strong></span>
            <?php if ($user !== null): ?>
                <span>Usuario: <strong><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars(role_label($xml, (string) $user['role']), ENT_QUOTES, 'UTF-8') ?>)</span>
            <?php else: ?>
                <span>Usuario: <strong>No autenticado</strong></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navegacion principal semantica del portal -->
    <nav class="portal-nav" aria-label="Navegacion principal">
        <a class="<?= is_active_page('home', $page) ?>" href="index.php?page=home">Inicio</a>
        <a class="<?= is_active_page('clasificacion', $page) ?>" href="index.php?page=clasificacion">Clasificacion</a>
        <a class="<?= is_active_page('equipos', $page) ?>" href="index.php?page=equipos">Equipos</a>
        <a class="<?= is_active_page('jugadores', $page) ?>" href="index.php?page=jugadores">Jugadores</a>
        <a class="<?= is_active_page('partidos', $page) ?>" href="index.php?page=partidos">Partidos</a>
        <a class="<?= is_active_page('noticias', $page) ?>" href="index.php?page=noticias">Noticias</a>
        <a class="<?= is_active_page('normativa', $page) ?>" href="index.php?page=normativa">Normativa</a>
    </nav>
</header>

<main class="portal-main">
    <!-- Zona de feedback visual (exito/error) -->
    <?php if ($flash !== null): ?>
        <section class="flash flash--<?= htmlspecialchars((string) $flash['type'], ENT_QUOTES, 'UTF-8') ?>" role="status">
            <?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </section>
    <?php endif; ?>

    <aside class="portal-sidebar">
        <!-- Bloque de autenticacion y acciones de sesion -->
        <section class="panel">
            <h2>Acceso</h2>
            <?php if ($user === null): ?>
                <form id="login-form" method="post" action="index.php?page=<?= urlencode($page) ?>" novalidate>
                    <input type="hidden" name="action" value="login">
                    <label for="username">Usuario</label>
                    <input id="username" name="username" type="text" required minlength="3">

                    <label for="password">Contrasena</label>
                    <input id="password" name="password" type="password" required minlength="3">

                    <button type="submit">Iniciar sesion</button>
                </form>
                <form method="post" action="index.php?page=<?= urlencode($page) ?>">
                    <input type="hidden" name="action" value="guest_login">
                    <button type="submit" class="btn-secondary">Entrar como invitado</button>
                </form>
            <?php else: ?>
                <p><strong><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <p>Rol: <?= htmlspecialchars(role_label($xml, (string) $user['role']), ENT_QUOTES, 'UTF-8') ?></p>
                <form method="post" action="index.php?page=<?= urlencode($page) ?>">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Cerrar sesion</button>
                </form>
            <?php endif; ?>
        </section>

        <!-- Buscador global de equipos y jugadores -->
        <section class="panel">
            <h2>Buscador</h2>
            <form id="search-form" method="get" action="index.php" novalidate>
                <input type="hidden" name="page" value="buscar">
                <label for="q">Equipo o jugador</label>
                <input id="q" name="q" type="text" minlength="2" required>
                <button type="submit">Buscar</button>
            </form>
        </section>
    </aside>

    <section class="portal-content">
        <?php
        // Carga de vista dinamica en base al parametro page.
        $viewPath = __DIR__ . '/' . $page . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            require __DIR__ . '/home.php';
        }
        ?>
    </section>
</main>

<footer class="portal-footer">
    <p>&copy; 2026 Federacion de Futbol Sala</p>
</footer>

<script src="../js/php-portal.js"></script>
</body>
</html>

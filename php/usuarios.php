<?php

declare(strict_types=1);

// Panel de administracion para listar usuarios y sus roles del sistema.

require_once __DIR__ . '/../includes/app_init.php';

// Control de acceso: solo perfiles Admin pueden abrir este panel.
$rolSesion = trim((string) ($_SESSION['rol'] ?? ''));
if (strcasecmp($rolSesion, 'Admin') !== 0) {
    $_SESSION['flash_error'] = 'No tienes permisos para acceder a Usuarios.';
    header('Location: inicio.php');
    exit;
}

// Datos base de la vista: titulo y listado de usuarios disponibles.
$pageTitle = 'Usuarios | FEDERACIÓN FUTSAL';
$users = get_users();
$loginEvents = read_login_events();
if ($loginEvents !== []) {
    $loginEvents = array_reverse($loginEvents);
}

// Roles disponibles para el filtro visual (derivados del listado de usuarios).
$availableRoles = [];
foreach ($users as $userData) {
    $roleValue = trim((string) ($userData['rol'] ?? ''));
    if ($roleValue !== '') {
        $availableRoles[$roleValue] = true;
    }
}
ksort($availableRoles, SORT_NATURAL | SORT_FLAG_CASE);
$availableRoles = array_keys($availableRoles);

// Filtro de historial para evitar listados excesivamente largos.
$loginRangeRaw = filter_input(INPUT_GET, 'rango', FILTER_UNSAFE_RAW);
$loginRange = is_string($loginRangeRaw) ? trim($loginRangeRaw) : '';
$allowedRanges = ['24h', '7d', '30d'];
if (!in_array($loginRange, $allowedRanges, true)) {
    $loginRange = '24h';
}

$loginRoleRaw = filter_input(INPUT_GET, 'rol', FILTER_UNSAFE_RAW);
$loginRole = is_string($loginRoleRaw) ? trim($loginRoleRaw) : '';
if ($loginRole === '') {
    $loginRole = 'todos';
}

$allowedRoles = array_merge(['todos'], $availableRoles);
if (!in_array($loginRole, $allowedRoles, true)) {
    $loginRole = 'todos';
}

$now = new DateTimeImmutable('now');
$rangeSeconds = match ($loginRange) {
    '24h' => 24 * 60 * 60,
    '7d' => 7 * 24 * 60 * 60,
    '30d' => 30 * 24 * 60 * 60,
    default => 24 * 60 * 60,
};

$filteredLoginEvents = [];
foreach ($loginEvents as $event) {
    $rawDate = (string) ($event['fecha_hora'] ?? '');
    $eventDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $rawDate);
    if ($eventDate === false) {
        continue;
    }

    $diff = $now->getTimestamp() - $eventDate->getTimestamp();
    if ($diff >= 0 && $diff <= $rangeSeconds) {
        $eventRole = trim((string) ($event['rol'] ?? ''));
        if ($loginRole !== 'todos' && strcasecmp($eventRole, $loginRole) !== 0) {
            continue;
        }

        $filteredLoginEvents[] = $event;
    }
}

$rangeLabels = [
    '24h' => 'Ultimas 24 horas',
    '7d' => 'Ultima semana',
    '30d' => 'Ultimo mes',
];

$roleLabel = $loginRole === 'todos' ? 'Todos' : $loginRole;

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Panel administrativo para consultar usuarios del sistema -->
<main class="page page-users">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Gestion de usuarios</h2>
        </article>

        <!-- Tabla simple con identidad y rol de cada cuenta registrada -->
        <article class="matches-wrap" aria-label="Listado de usuarios del sistema">
            <table class="matches-table">
                <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                </tr>
                </thead>
                <tbody>
                <!-- Render de filas dinamicas desde el arreglo de usuarios -->
                <?php foreach ($users as $username => $data): ?>
                    <tr>
                        <td><?php echo e((string) $username); ?></td>
                        <td><?php echo e((string) ($data['nombre'] ?? 'N/D')); ?></td>
                        <td><?php echo e((string) ($data['rol'] ?? 'N/D')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </article>

        <article class="panel-heading">
            <h2>Ultimos inicios de sesión</h2>
            <form class="season-form" method="get" action="usuarios.php" aria-label="Filtro de historial de inicios de sesion">
                <label for="rango">Mostrar</label>
                <select id="rango" name="rango">
                    <option value="24h" <?php echo $loginRange === '24h' ? 'selected' : ''; ?>>Ultimas 24 horas</option>
                    <option value="7d" <?php echo $loginRange === '7d' ? 'selected' : ''; ?>>Ultima semana</option>
                    <option value="30d" <?php echo $loginRange === '30d' ? 'selected' : ''; ?>>Ultimo mes</option>
                </select>
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="todos" <?php echo $loginRole === 'todos' ? 'selected' : ''; ?>>Todos</option>
                    <?php foreach ($availableRoles as $role): ?>
                        <option value="<?php echo e($role); ?>" <?php echo $loginRole === $role ? 'selected' : ''; ?>><?php echo e($role); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Aplicar</button>
            </form>
            <p>Rango activo: <strong><?php echo e($rangeLabels[$loginRange]); ?></strong> | Rol activo: <strong><?php echo e($roleLabel); ?></strong></p>
        </article>

        <article class="matches-wrap" aria-label="Historial de inicios de sesion">
            <?php if ($filteredLoginEvents === []): ?>
                <p>No hay inicios de sesion en el rango seleccionado.</p>
            <?php else: ?>
                <table class="matches-table">
                    <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Fecha y hora</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filteredLoginEvents as $event): ?>
                        <tr>
                            <td><?php echo e((string) ($event['usuario'] ?? 'N/D')); ?></td>
                            <td><?php echo e((string) ($event['rol'] ?? 'N/D')); ?></td>
                            <td><?php echo e((string) ($event['fecha_hora'] ?? 'N/D')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </article>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


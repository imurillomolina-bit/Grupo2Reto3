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
            <h2>Ultimos inicios de sesion</h2>
        </article>

        <article class="matches-wrap" aria-label="Historial de inicios de sesion">
            <?php if ($loginEvents === []): ?>
                <p>No hay inicios de sesion registrados todavia.</p>
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
                    <?php foreach ($loginEvents as $event): ?>
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


<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

$rolSesion = trim((string) ($_SESSION['rol'] ?? ''));
if (strcasecmp($rolSesion, 'Admin') !== 0) {
    $_SESSION['flash_error'] = 'No tienes permisos para acceder a Usuarios.';
    header('Location: inicio.php');
    exit;
}

$pageTitle = 'Usuarios | FEDERACIÃ“N FUTSAL';
$users = get_users();

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-users">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Gestion de usuarios</h2>
        </article>

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
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

$pageTitle = 'Login | FEDERACIÃ“N FUTSAL';

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Zona de acceso -->
<main class="page page-login">
    <!-- Section: Formulario de autenticacion -->
    <section class="panel login-panel">
        <article class="panel-heading">
            <h2>Ingreso de usuarios</h2>
            <p>Prueba con admin/admin123, manager/manager123 o arbitro/arbitro123.</p>
        </article>

        <article>
            <form class="login-form" action="procesar_login.php" method="post" novalidate>
                <label for="username">Usuario</label>
                <input id="username" name="username" type="text" maxlength="30" required>

                <label for="password">Contrasena</label>
                <input id="password" name="password" type="password" maxlength="60" required>

                <button type="submit">Iniciar sesion</button>
            </form>

            <form class="login-form" action="entrar_invitado.php" method="post">
                <button class="login-guest-button" type="submit">Entrar como invitado</button>
            </form>
        </article>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>


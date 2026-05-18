<?php
 
declare(strict_types=1);
 
// Pantalla de acceso: valida login por POST y muestra el formulario de entrada.
 
require_once __DIR__ . '/../includes/app_init.php';
 
// Si llega un POST, esta misma pagina procesa el intento de autenticacion.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // Lectura de credenciales enviadas por formulario.
    $usernameRaw = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $passwordRaw = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
 
    // Normaliza espacios para evitar credenciales con blancos accidentales.
    $username = is_string($usernameRaw) ? trim($usernameRaw) : '';
    $password = is_string($passwordRaw) ? trim($passwordRaw) : '';
 
    // Validacion minima de campos obligatorios.
    if ($username === '' || $password === '') {
        $_SESSION['flash_error'] = 'Debes completar usuario y contrasena.';
        header('Location: login.php');
        exit;
    }
 
    // Control de formato permitido para el identificador de usuario.
    if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $username)) {
        $_SESSION['flash_error'] = 'El usuario contiene caracteres no permitidos.';
        header('Location: login.php');
        exit;
    }
 
    // Delega comprobacion de credenciales en la capa de autenticacion.
    $loginData = validate_login_credentials($username, $password);
    if ($loginData === null) {
        $_SESSION['flash_error'] = 'Credenciales invalidas.';
        header('Location: login.php');
        exit;
    }
 
    // Persistencia de sesion para identificar usuario y rol en toda la app.
    $_SESSION['nombre_usuario'] = $loginData['nombre'];
    $_SESSION['user'] = $loginData['nombre'];
    $_SESSION['rol'] = $loginData['rol'];
    $_SESSION['flash_success'] = 'Sesion iniciada correctamente.';
    register_login_event((string) $loginData['nombre'], (string) $loginData['rol']);
 
    // Destino por defecto tras login correcto.
    $rol = trim((string) $loginData['rol']);
    $redirect = 'inicio.php';
 
    // Redirecciones especificas segun perfil.
    if (strcasecmp($rol, 'Admin') === 0) {
        $redirect = 'usuarios.php';
    } elseif (strcasecmp($rol, 'Manager') === 0) {
        $redirect = 'panel.php';
    } elseif (strcasecmp($rol, 'Arbitro') === 0) {
        $redirect = 'partidos.php';
    }
 
    // Cierre del flujo POST con redireccion final.
    header('Location: ' . $redirect);
    exit;
}
 
$pageTitle = 'Login | FEDERACIÓN FUTSAL';
 
require __DIR__ . '/../includes/header.php';
?>
 
<!-- Main: Zona de acceso -->
<main class="page page-login">
    <!-- Section: Formulario de autenticacion -->
    <section class="panel login-panel">
        <article class="panel-heading">
            <h2>Ingreso de usuarios</h2>
            <!-- <p>Prueba con admin/admin123, manager/manager123 o arbitro/arbitro123.</p> -->
        </article>
 
        <article>
            <form class="login-form" id="form-login" action="login.php" method="post" novalidate>
                <label for="username">Usuario</label>
                <input id="username" name="username" type="text" maxlength="30" required>
 
                <label for="password">Contrasena</label>
                <input id="password" name="password" type="password" maxlength="60" required>
 
                <button type="submit">Iniciar sesion</button>
            </form>
 
            <!-- Botón de acceso rápido para invitados eliminado según petición del usuario -->
        </article>
    </section>
</main>
 
<!-- Validacion en cliente: se ejecuta antes de enviar el formulario al servidor -->
<script>
document.getElementById('form-login').addEventListener('submit', function (e) {
    var usuario  = document.getElementById('username').value.trim();
    var password = document.getElementById('password').value.trim();
 
    // Comprueba que ambos campos esten rellenos.
    if (usuario === '' || password === '') {
        e.preventDefault();
        alert('Debes completar usuario y contrasena.');
        return;
    }
 
    // Longitud minima del usuario (igual que el servidor).
    if (usuario.length < 3) {
        e.preventDefault();
        alert('El usuario debe tener al menos 3 caracteres.');
        return;
    }
 
    // Formato permitido: misma expresion regular que usa el PHP del servidor.
    if (!/^[a-zA-Z0-9._-]+$/.test(usuario)) {
        e.preventDefault();
        alert('El usuario contiene caracteres no permitidos.');
        return;
    }
});
</script>
 
<?php require __DIR__ . '/../includes/footer.php'; ?>
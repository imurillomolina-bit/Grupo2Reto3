<?php

declare(strict_types=1);

// Cierra la sesion activa, restablece visitante y redirige al inicio.

require_once __DIR__ . '/../includes/app_init.php';

// Limpia todas las variables de sesion en memoria.
$_SESSION = [];

// Si la sesion usa cookie, la invalida en el navegador.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        (string) $params['path'],
        (string) $params['domain'],
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}

// Destruye sesion anterior y crea una nueva para dejar estado consistente.
session_destroy();
session_start();

// Inicializa estructura de visitante anonimo y mensaje de salida.
set_guest_session();
$_SESSION['flash_success'] = 'Sesion cerrada correctamente.';

// Redirige al inicio para cerrar el flujo de logout.
header('Location: inicio.php');
exit;


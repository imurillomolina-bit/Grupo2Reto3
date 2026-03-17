<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: login.php');
    exit;
}

$usernameRaw = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
$passwordRaw = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

$username = is_string($usernameRaw) ? trim($usernameRaw) : '';
$password = is_string($passwordRaw) ? trim($passwordRaw) : '';

if ($username === '' || $password === '') {
    $_SESSION['flash_error'] = 'Debes completar usuario y contrasena.';
    header('Location: login.php');
    exit;
}

if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $username)) {
    $_SESSION['flash_error'] = 'El usuario contiene caracteres no permitidos.';
    header('Location: login.php');
    exit;
}

$loginData = validate_login_credentials($username, $password);
if ($loginData === null) {
    $_SESSION['flash_error'] = 'Credenciales invalidas.';
    header('Location: login.php');
    exit;
}

$_SESSION['nombre_usuario'] = $loginData['nombre'];
$_SESSION['user'] = $loginData['nombre'];
$_SESSION['rol'] = $loginData['rol'];
$_SESSION['flash_success'] = 'Sesion iniciada correctamente.';

header('Location: inicio.php');
exit;

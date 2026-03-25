<?php

declare(strict_types=1);

// Gestion de usuarios y validacion de credenciales para el acceso al sistema.

function get_users(): array
{
    // Mapa simple de usuarios permitidos en este entorno academico.
    return [
        'admin' => ['password' => 'admin123', 'nombre' => 'Admin', 'rol' => 'Admin'],
        'manager' => ['password' => 'manager123', 'nombre' => 'Manager', 'rol' => 'Manager'],
        'arbitro' => ['password' => 'arbitro123', 'nombre' => 'Arbitro', 'rol' => 'Arbitro'],
    ];
}

function validate_login_credentials(string $username, string $password): ?array
{
    // Se normalizan entradas para evitar espacios al inicio o final.
    $username = trim($username);
    $password = trim($password);

    // Credenciales vacias no son validas.
    if ($username === '' || $password === '') {
        return null;
    }

    // Se limita el usuario a caracteres seguros y longitud controlada.
    if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $username)) {
        return null;
    }

    $users = get_users();
    // Solo se autentican usuarios registrados en el mapa local.
    if (!isset($users[$username])) {
        return null;
    }

    $knownPassword = (string) $users[$username]['password'];
    // Comparacion resistente a ataques por temporizacion.
    if (!hash_equals($knownPassword, $password)) {
        return null;
    }

    // Devuelve datos minimos de sesion para el usuario autenticado.
    return [
        'username' => $username,
        'nombre' => (string) $users[$username]['nombre'],
        'rol' => (string) $users[$username]['rol'],
    ];
}

function set_guest_session(): void
{
    // Garantiza claves de sesion existentes para simplificar vistas.
    if (!isset($_SESSION['nombre_usuario'])) {
        $_SESSION['nombre_usuario'] = '';
    }

    if (!isset($_SESSION['user'])) {
        $_SESSION['user'] = '';
    }

    if (!isset($_SESSION['rol'])) {
        $_SESSION['rol'] = '';
    }
}


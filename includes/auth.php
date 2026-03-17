<?php

declare(strict_types=1);

function get_users(): array
{
    return [
        'admin' => ['password' => 'admin123', 'nombre' => 'Admin', 'rol' => 'Admin'],
        'manager' => ['password' => 'manager123', 'nombre' => 'Manager', 'rol' => 'Manager'],
        'arbitro' => ['password' => 'arbitro123', 'nombre' => 'Arbitro', 'rol' => 'Arbitro'],
    ];
}

function validate_login_credentials(string $username, string $password): ?array
{
    $username = trim($username);
    $password = trim($password);

    if ($username === '' || $password === '') {
        return null;
    }

    if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $username)) {
        return null;
    }

    $users = get_users();
    if (!isset($users[$username])) {
        return null;
    }

    $knownPassword = (string) $users[$username]['password'];
    if (!hash_equals($knownPassword, $password)) {
        return null;
    }

    return [
        'username' => $username,
        'nombre' => (string) $users[$username]['nombre'],
        'rol' => (string) $users[$username]['rol'],
    ];
}

function set_guest_session(): void
{
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

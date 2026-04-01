<?php

declare(strict_types=1);

// Procesa el acceso rapido como invitado y fija la sesion correspondiente.

require_once __DIR__ . '/../includes/app_init.php';

// Solo se permite acceso por POST para evitar invocaciones accidentales por URL.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: inicio.php');
    exit;
}

// Identidad de invitado: perfil sin privilegios de administracion.
$_SESSION['nombre_usuario'] = 'Visitante';
$_SESSION['user'] = 'Visitante';
$_SESSION['rol'] = 'Invitado';
$_SESSION['flash_success'] = 'Sesion iniciada como invitado.';
// Registra el acceso para trazabilidad en la pantalla de usuarios.
register_login_event('Visitante', 'Invitado');

header('Location: inicio.php');
exit;


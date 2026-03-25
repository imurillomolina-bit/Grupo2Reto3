<?php

declare(strict_types=1);

// Procesa el acceso rapido como invitado y fija la sesion correspondiente.

require_once __DIR__ . '/../includes/app_init.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: inicio.php');
    exit;
}

$_SESSION['nombre_usuario'] = 'Visitante';
$_SESSION['user'] = 'Visitante';
$_SESSION['rol'] = 'Invitado';
$_SESSION['flash_success'] = 'Sesion iniciada como invitado.';

header('Location: inicio.php');
exit;


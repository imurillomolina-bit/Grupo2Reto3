<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: inicio.php');
    exit;
}

$_SESSION['nombre_usuario'] = 'Visitante';
$_SESSION['user'] = 'Visitante';
$_SESSION['rol'] = 'Invitado';
$_SESSION['flash_success'] = 'Sesion iniciada como invitado.';

header('Location: clasificacion.php');
exit;

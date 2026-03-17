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

$redirect = 'inicio.php';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (is_string($referer) && $referer !== '') {
    $refererPath = (string) parse_url($referer, PHP_URL_PATH);
    $refererQuery = (string) parse_url($referer, PHP_URL_QUERY);
    if ($refererPath !== '') {
        $candidate = basename($refererPath);
        if ($candidate !== '' && str_ends_with($candidate, '.php')) {
            $redirect = $candidate;
            if ($refererQuery !== '') {
                $redirect .= '?' . $refererQuery;
            }
        }
    }
}

header('Location: ' . $redirect);
exit;

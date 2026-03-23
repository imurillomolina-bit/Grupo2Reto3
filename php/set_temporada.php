<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: inicio.php');
    exit;
}

$temporadaRaw = filter_input(INPUT_POST, 'temporada_id', FILTER_UNSAFE_RAW);
$temporadaId = is_string($temporadaRaw) ? trim($temporadaRaw) : '';

if ($temporadaId === '' || !preg_match('/^[0-9]{4}-[0-9]{4}$/', $temporadaId)) {
    $_SESSION['flash_error'] = 'Temporada invalida.';
    header('Location: inicio.php');
    exit;
}

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_by_id($xml, $temporadaId);
    if ($temporada === null) {
        $_SESSION['flash_error'] = 'La temporada seleccionada no existe.';
    } else {
        $_SESSION['temporada_actual'] = $temporadaId;
        $_SESSION['flash_success'] = 'Temporada actualizada a ' . (string) $temporada['nombre'] . '.';
    }
} catch (Throwable $ex) {
    $_SESSION['flash_error'] = 'No se pudo cambiar la temporada.';
}

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


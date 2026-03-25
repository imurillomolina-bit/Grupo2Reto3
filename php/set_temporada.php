<?php

declare(strict_types=1);

// Controlador de cambio de temporada: valida la solicitud y el formato recibido,
// comprueba que la temporada exista en el XML, actualiza la sesion y redirige
// de vuelta a la vista de origen (o a inicio) mostrando mensajes flash.

require_once __DIR__ . '/../includes/app_init.php';

// Solo se permite cambiar temporada mediante envio de formulario.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: inicio.php');
    exit;
}

// Se valida que el identificador de temporada tenga formato YYYY-YYYY.
$temporadaRaw = filter_input(INPUT_POST, 'temporada_id', FILTER_UNSAFE_RAW);
$temporadaId = is_string($temporadaRaw) ? trim($temporadaRaw) : '';

if ($temporadaId === '' || !preg_match('/^[0-9]{4}-[0-9]{4}$/', $temporadaId)) {
    $_SESSION['flash_error'] = 'Temporada invalida.';
    header('Location: inicio.php');
    exit;
}

try {
    // Se comprueba que la temporada exista en el XML antes de guardarla en sesion.
    $xml = load_liga_xml();
    $temporada = get_temporada_by_id($xml, $temporadaId);
    if ($temporada === null) {
        $_SESSION['flash_error'] = 'La temporada seleccionada no existe.';
    } else {
        // Se actualiza la temporada activa para el resto de vistas.
        $_SESSION['temporada_actual'] = $temporadaId;
        $_SESSION['flash_success'] = 'Temporada actualizada a ' . (string) $temporada['nombre'] . '.';
    }
} catch (Throwable $ex) {
    $_SESSION['flash_error'] = 'No se pudo cambiar la temporada.';
}

// Por defecto vuelve a inicio, pero intenta regresar a la pagina de origen.
$redirect = 'inicio.php';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (is_string($referer) && $referer !== '') {
    $refererPath = (string) parse_url($referer, PHP_URL_PATH);
    $refererQuery = (string) parse_url($referer, PHP_URL_QUERY);
    if ($refererPath !== '') {
        $candidate = basename($refererPath);
        if ($candidate !== '' && str_ends_with($candidate, '.php')) {
            // Se restringe a rutas internas *.php para evitar redirecciones externas.
            $redirect = $candidate;
            if ($refererQuery !== '') {
                $redirect .= '?' . $refererQuery;
            }
        }
    }
}

header('Location: ' . $redirect);
exit;


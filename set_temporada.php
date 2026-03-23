<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/app_init.php';

// Solo permite cambios de temporada mediante formulario POST.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: php/inicio.php');
    exit;
}

// Lee y normaliza el id de temporada recibido.
$temporadaRaw = filter_input(INPUT_POST, 'temporada_id', FILTER_UNSAFE_RAW);
$temporadaId = is_string($temporadaRaw) ? trim($temporadaRaw) : '';

// Valida formato esperado: YYYY-YYYY.
if ($temporadaId === '' || !preg_match('/^[0-9]{4}-[0-9]{4}$/', $temporadaId)) {
    $_SESSION['flash_error'] = 'Temporada invalida.';
    header('Location: php/inicio.php');
    exit;
}

try {
    // Verifica que la temporada exista en el XML y la guarda en sesion.
    $xml = load_liga_xml();
    $temporada = get_temporada_by_id($xml, $temporadaId);
    if ($temporada === null) {
        $_SESSION['flash_error'] = 'La temporada seleccionada no existe.';
    } else {
        $_SESSION['temporada_actual'] = $temporadaId;
        $_SESSION['flash_success'] = 'Temporada actualizada a ' . (string) $temporada['nombre'] . '.';
    }
} catch (Throwable $ex) {
    // Maneja errores de lectura/procesamiento sin romper la navegacion.
    $_SESSION['flash_error'] = 'No se pudo cambiar la temporada.';
}

// Vuelve a la pagina origen (si es valida) para conservar flujo del usuario.
$redirect = 'php/inicio.php';
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

// Aplica redireccion final tras procesar la seleccion.
header('Location: ' . $redirect);
exit;


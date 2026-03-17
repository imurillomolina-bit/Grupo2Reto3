<?php

declare(strict_types=1);

// Inicia la sesion solo si aun no esta activa.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Utilidades de autenticacion y acceso a datos XML.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/xml.php';

// Escapa texto para salida segura en HTML.
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Garantiza un contexto minimo de visitante anonimo en sesion.
set_guest_session();

try {
    // Carga el XML principal de la liga para configurar contexto global.
    $bootstrapXml = load_liga_xml();

    // Si no hay temporada seleccionada en sesion, establece la predeterminada.
    if (!isset($_SESSION['temporada_actual'])) {
        $defaultTemporada = get_default_temporada_id($bootstrapXml);
        if ($defaultTemporada !== null) {
            $_SESSION['temporada_actual'] = $defaultTemporada;
        }
    }
} catch (Throwable $e) {
    // Conserva el error para mostrarlo o depurarlo en otras vistas.
    $_SESSION['xml_error'] = $e->getMessage();
}

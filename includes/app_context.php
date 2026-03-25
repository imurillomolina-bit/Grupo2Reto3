<?php

declare(strict_types=1);

// Configura el contexto global de sesion y temporada activa para toda la app.

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


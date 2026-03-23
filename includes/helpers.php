<?php

declare(strict_types=1);

// Escapa texto para salida segura en HTML.
if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
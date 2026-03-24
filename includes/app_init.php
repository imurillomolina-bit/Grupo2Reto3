<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/xml.php';
require_once __DIR__ . '/app_context.php';

// Inicia la sesion solo si aun no esta activa.
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Escapa texto para salida segura en HTML.
if (!function_exists('e')) {
	function e(string $value): string
	{
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}
}


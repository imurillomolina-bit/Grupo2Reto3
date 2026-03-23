<?php

declare(strict_types=1);

// Inicia la sesion solo si aun no esta activa.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


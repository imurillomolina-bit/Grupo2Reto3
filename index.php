<?php

declare(strict_types=1);

// Punto de entrada raiz del proyecto; redirige a la portada de la aplicacion.

// Redireccion explicita para centralizar todas las rutas en /php.
header('Location: php/inicio.php');
// Se corta ejecucion para evitar salida accidental despues de headers.
exit;


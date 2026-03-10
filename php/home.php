<?php

// Texto de apoyo para cabecera de inicio segun temporada seleccionada.
$seasonName = $selectedSeason['name'] ?? 'No disponible';
?>
<!-- Presentacion general del portal y stack tecnico -->
<section class="panel">
    <h2>Bienvenido al Portal Dinamico</h2>
    <p>Esta version usa <strong>PHP + XML</strong> para mostrar datos en tiempo real y gestionar sesiones por rol.</p>
    <p>Temporada seleccionada: <strong><?= htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8') ?></strong></p>
</section>

<!-- Resumen funcional y flujo recomendado para el usuario -->
<section class="panel grid-2">
    <article>
        <h3>Que incluye esta fase</h3>
        <ul>
            <li>Login y sesiones con roles en PHP.</li>
            <li>Clasificacion calculada en servidor desde XML.</li>
            <li>Ficha de equipo con escudo y jugadores.</li>
            <li>Buscador de equipos y jugadores.</li>
        </ul>
    </article>
    <article>
        <h3>Flujo recomendado</h3>
        <ol>
            <li>Inicia sesion o entra como invitado.</li>
            <li>Selecciona temporada en Clasificacion.</li>
            <li>Pulsa un equipo para ver su ficha detallada.</li>
        </ol>
    </article>
</section>

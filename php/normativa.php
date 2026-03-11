<?php

$config = get_config($xml);
$overview = $selectedSeason !== null ? get_season_overview($xml, $selectedSeason) : null;
?>
<section class="panel">
    <h2>Normativa</h2>
    <p>Reglas de competicion y funcionamiento del portal generadas desde la configuracion activa.</p>
</section>

<section class="grid-2">
    <article class="panel">
        <h3>Competicion</h3>
        <ul class="compact-list">
            <li>Las victorias suman 3 puntos.</li>
            <li>Los empates suman 1 punto.</li>
            <li>La clasificacion se ordena por puntos, diferencia de goles y goles a favor.</li>
            <?php if ($overview !== null): ?>
                <li>La temporada activa contempla <?= (int) $overview['rounds'] ?> jornadas y <?= (int) $overview['matches'] ?> partidos.</li>
            <?php endif; ?>
        </ul>
    </article>

    <article class="panel">
        <h3>Acceso y sesion</h3>
        <ul class="compact-list">
            <li>La sesion caduca tras <?= (int) $config['timeout_minutes'] ?> minutos de inactividad.</li>
            <li>Los usuarios invitados no pueden acceder a la pagina de partidos.</li>
            <li>El portal permite autenticacion completa o acceso de invitado.</li>
        </ul>
    </article>
</section>

<section class="panel">
    <h3>Roles disponibles</h3>
    <ul class="compact-list">
        <?php foreach ($config['roles'] as $roleName => $label): ?>
            <li><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $roleName, ENT_QUOTES, 'UTF-8') ?>)</li>
        <?php endforeach; ?>
    </ul>
</section>

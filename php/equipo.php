<?php

// Validaciones iniciales de contexto minimo.
if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

// team_id llega por query string desde clasificacion/listado equipos.
$teamId = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;
if ($teamId <= 0 || !isset($teamsMap[$teamId])) {
    echo '<section class="panel"><p>Equipo no valido.</p></section>';
    return;
}

// Datos base del equipo y sus jugadores para la temporada activa.
$team = $teamsMap[$teamId];
$shield = $seasonShields[$teamId] ?? $team['shield'];
$playerIds = $teamSeasonRelations[$selectedSeason['id']][$teamId] ?? [];
?>
<!-- Bloque superior: datos institucionales del equipo -->
<section class="panel team-detail">
    <h2><?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="team-detail__meta">
        <?php if ($shield !== ''): ?>
            <img class="team-shield-large" src="<?= htmlspecialchars($shield, ENT_QUOTES, 'UTF-8') ?>" alt="Escudo <?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <div>
            <p><strong>Pabellon:</strong> <?= htmlspecialchars($team['pabellon'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Pais:</strong> <?= htmlspecialchars($team['country'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Temporada:</strong> <?= htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>

<!-- Bloque inferior: plantilla del equipo para la temporada -->
<section class="panel">
    <h3>Jugadores</h3>
    <?php if ($playerIds === []): ?>
        <p>No hay jugadores asociados a este equipo para la temporada seleccionada.</p>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($playerIds as $playerId): ?>
                <?php if (!isset($playersMap[$playerId])) { continue; } ?>
                <?php
                // Foto de temporada tiene prioridad frente a la foto base del jugador.
                $player = $playersMap[$playerId];
                $photo = $seasonPhotos[$playerId] ?? $player['image'];
                ?>
                <article class="card player-card">
                    <?php if ($photo !== ''): ?>
                        <img class="player-photo" src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                    <h4><?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <p><strong>Dorsal:</strong> <?= (int) $player['number'] ?></p>
                    <p><strong>Posicion:</strong> <?= htmlspecialchars($player['position'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

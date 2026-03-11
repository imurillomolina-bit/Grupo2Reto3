<?php

// Validar que exista una temporada seleccionada
if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

// Obtener ID del equipo desde query string y validar
$teamId = (int) ($_GET['team_id'] ?? 0);
if ($teamId <= 0 || !isset($teamsMap[$teamId])) {
    echo '<section class="panel"><p>Equipo no valido.</p></section>';
    return;
}

// Cargar datos del equipo
$team = $teamsMap[$teamId];
$shield = $seasonShields[$teamId] ?? $team['shield'];
$playerIds = $teamSeasonRelations[$selectedSeason['id']][$teamId] ?? [];

// Escapar variables para seguridad en HTML
$teamName = htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8');
$seasonName = htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8');
?>
<!-- Mostrar información del equipo -->
<section class="panel team-detail">
    <h2><?= $teamName ?></h2>
    <div class="team-detail__meta">
        <!-- Mostrar escudo si existe -->
        <?php if ($shield !== ''): ?>
            <img class="team-shield-large" 
                 src="<?= htmlspecialchars($shield, ENT_QUOTES, 'UTF-8') ?>" 
                 alt="Escudo <?= $teamName ?>">
        <?php endif; ?>
        <!-- Datos institucionales -->
        <div>
            <p><strong>Pabellon:</strong> <?= htmlspecialchars($team['pabellon'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Pais:</strong> <?= htmlspecialchars($team['country'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Temporada:</strong> <?= $seasonName ?></p>
        </div>
    </div>
</section>

<!-- Mostrar plantilla de jugadores -->
<section class="panel">
    <h3>Jugadores</h3>
    <!-- Si no hay jugadores, mostrar mensaje -->
    <?php if (empty($playerIds)): ?>
        <p>No hay jugadores asociados a este equipo para la temporada seleccionada.</p>
    <?php else: ?>
        <!-- Grid de tarjetas de jugadores -->
        <div class="cards-grid">
            <?php foreach ($playerIds as $playerId): ?>
                <!-- Saltar si el jugador no existe en el mapa -->
                <?php 
                if (!isset($playersMap[$playerId])) continue;
                
                // Cargar datos del jugador
                $player = $playersMap[$playerId];
                $photo = $seasonPhotos[$playerId] ?? $player['image'];
                $playerName = htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8');
                ?>
                
                <!-- Tarjeta individual del jugador -->
                <article class="card player-card">
                    <!-- Foto del jugador -->
                    <?php if ($photo !== ''): ?>
                        <img class="player-photo" 
                             src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= $playerName ?>">
                    <?php endif; ?>
                    
                    <!-- Información del jugador -->
                    <h4><?= $playerName ?></h4>
                    <p><strong>Dorsal:</strong> <?= (int) $player['number'] ?></p>
                    <p><strong>Posicion:</strong> <?= htmlspecialchars($player['position'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

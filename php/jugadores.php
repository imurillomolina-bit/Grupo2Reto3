<?php

if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

$teamFilter = isset($_GET['team_id']) ? (int) $_GET['team_id'] : 0;
?>
<section class="panel">
    <h2>Jugadores - <?= htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8') ?></h2>
    <form method="get" action="index.php" class="filters-inline">
        <input type="hidden" name="page" value="jugadores">
        <label for="team_id">Filtrar por equipo</label>
        <select id="team_id" name="team_id">
            <option value="0">Todos</option>
            <?php foreach ($selectedSeason['team_ids'] as $teamId): ?>
                <?php if (!isset($teamsMap[$teamId])) { continue; } ?>
                <option value="<?= (int) $teamId ?>" <?= $teamFilter === (int) $teamId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($teamsMap[$teamId]['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Aplicar</button>
    </form>
</section>

<section class="cards-grid">
    <?php foreach ($selectedSeason['team_ids'] as $teamId): ?>
        <?php if (!isset($teamsMap[$teamId])) { continue; } ?>
        <?php if ($teamFilter > 0 && $teamFilter !== (int) $teamId) { continue; } ?>
        <?php $playerIds = $teamSeasonRelations[$selectedSeason['id']][$teamId] ?? []; ?>
        <?php foreach ($playerIds as $playerId): ?>
            <?php if (!isset($playersMap[$playerId])) { continue; } ?>
            <?php
            $player = $playersMap[$playerId];
            $photo = $seasonPhotos[$playerId] ?? $player['image'];
            ?>
            <article class="card player-card">
                <?php if ($photo !== ''): ?>
                    <img class="player-photo" src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <h3><?= htmlspecialchars($player['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p><strong>Equipo:</strong> <?= htmlspecialchars($teamsMap[$teamId]['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Dorsal:</strong> <?= (int) $player['number'] ?></p>
                <p><strong>Posicion:</strong> <?= htmlspecialchars($player['position'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        <?php endforeach; ?>
    <?php endforeach; ?>
</section>

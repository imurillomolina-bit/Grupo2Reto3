<?php

if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}
?>
<section class="panel">
    <h2>Equipos - <?= htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8') ?></h2>
</section>

<section class="cards-grid">
    <?php foreach ($selectedSeason['team_ids'] as $teamId): ?>
        <?php if (!isset($teamsMap[$teamId])) { continue; } ?>
        <?php
        $team = $teamsMap[$teamId];
        $shield = $seasonShields[$teamId] ?? $team['shield'];
        ?>
        <article class="card">
            <?php if ($shield !== ''): ?>
                <img class="team-shield" src="<?= htmlspecialchars($shield, ENT_QUOTES, 'UTF-8') ?>" alt="Escudo <?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <h3>
                <a href="index.php?page=equipo&team_id=<?= (int) $teamId ?>">
                    <?= htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </h3>
            <p><strong>Pabellon:</strong> <?= htmlspecialchars($team['pabellon'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Pais:</strong> <?= htmlspecialchars($team['country'], ENT_QUOTES, 'UTF-8') ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php

if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

$classification = compute_classification($xml, $selectedSeason);
$minPoints = isset($_GET['min_points']) && is_numeric($_GET['min_points']) ? (int) $_GET['min_points'] : 0;
$teamFilter = trim((string) ($_GET['team_filter'] ?? ''));
?>
<section class="panel">
    <h2>Clasificacion - <?= htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8') ?></h2>

    <form id="season-form" method="post" action="index.php?page=clasificacion" novalidate>
        <input type="hidden" name="action" value="select_season">
        <label for="season_id">Temporada</label>
        <select id="season_id" name="season_id" required>
            <option value="">Selecciona temporada</option>
            <?php foreach ($allSeasons as $season): ?>
                <option value="<?= (int) $season['id'] ?>" <?= $season['id'] === $selectedSeason['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($season['name'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $season['year'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Aplicar temporada</button>
    </form>

    <form method="get" action="index.php" class="filters-inline">
        <input type="hidden" name="page" value="clasificacion">
        <label for="min_points">Puntos minimos</label>
        <input id="min_points" name="min_points" type="number" min="0" value="<?= (int) $minPoints ?>">

        <label for="team_filter">Nombre equipo</label>
        <input id="team_filter" name="team_filter" type="text" value="<?= htmlspecialchars($teamFilter, ENT_QUOTES, 'UTF-8') ?>">

        <button type="submit">Filtrar</button>
    </form>
</section>

<section class="panel">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Equipo</th>
                <th>Pts</th>
                <th>J</th>
                <th>G</th>
                <th>E</th>
                <th>P</th>
                <th>GF</th>
                <th>GC</th>
                <th>DG</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $position = 0;
            foreach ($classification as $row):
                if ($row['points'] < $minPoints) {
                    continue;
                }
                if ($teamFilter !== '' && stripos($row['team_name'], $teamFilter) === false) {
                    continue;
                }
                $position++;
                $teamId = (int) $row['team_id'];
                $shield = $seasonShields[$teamId] ?? ($teamsMap[$teamId]['shield'] ?? '');
            ?>
                <tr>
                    <td><?= $position ?></td>
                    <td>
                        <a href="index.php?page=equipo&team_id=<?= $teamId ?>">
                            <?php if ($shield !== ''): ?>
                                <img class="shield-inline" src="<?= htmlspecialchars($shield, ENT_QUOTES, 'UTF-8') ?>" alt="Escudo <?= htmlspecialchars($row['team_name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                            <?= htmlspecialchars($row['team_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td><?= (int) $row['points'] ?></td>
                    <td><?= (int) $row['played'] ?></td>
                    <td><?= (int) $row['wins'] ?></td>
                    <td><?= (int) $row['draws'] ?></td>
                    <td><?= (int) $row['losses'] ?></td>
                    <td><?= (int) $row['goals_for'] ?></td>
                    <td><?= (int) $row['goals_against'] ?></td>
                    <td><?= (int) $row['goal_diff'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

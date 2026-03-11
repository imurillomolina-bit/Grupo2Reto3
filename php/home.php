<?php

if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

$seasonName = $selectedSeason['name'] ?? 'No disponible';
$matches = get_matches_for_season($xml, (int) $selectedSeason['id']);
$overview = get_season_overview($xml, $selectedSeason);
$classification = compute_classification($xml, $selectedSeason);
$upcomingMatches = get_upcoming_matches($matches, 4);
$completedMatches = get_completed_matches($matches, 3);
$playersInSeason = 0;

foreach ($selectedSeason['team_ids'] as $teamId) {
    $playersInSeason += count($teamSeasonRelations[$selectedSeason['id']][$teamId] ?? []);
}
?>
<section class="panel portal-hero">
    <p class="eyebrow">Portal dinamico</p>
    <h2>Federacion Futsal en tiempo real</h2>
    <p>
        La informacion de esta portada se genera desde el XML central y refleja la temporada
        <strong><?= htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8') ?></strong>.
    </p>
    <div class="hero-links">
        <a class="hero-link" href="index.php?page=clasificacion">Ver clasificacion</a>
        <a class="hero-link hero-link--secondary" href="index.php?page=equipos">Explorar equipos</a>
    </div>
</section>

<section class="cards-grid portal-stats">
    <article class="card stat-card">
        <p class="stat-card__label">Equipos</p>
        <p class="stat-card__value"><?= (int) $overview['teams'] ?></p>
    </article>
    <article class="card stat-card">
        <p class="stat-card__label">Jugadores registrados</p>
        <p class="stat-card__value"><?= $playersInSeason ?></p>
    </article>
    <article class="card stat-card">
        <p class="stat-card__label">Partidos programados</p>
        <p class="stat-card__value"><?= (int) $overview['matches'] ?></p>
    </article>
    <article class="card stat-card">
        <p class="stat-card__label">Estado</p>
        <p class="stat-card__value"><?= htmlspecialchars((string) $overview['status'], ENT_QUOTES, 'UTF-8') ?></p>
    </article>
</section>

<section class="panel grid-2">
    <article>
        <h3>Resumen de competicion</h3>
        <ul class="compact-list">
            <li>Jornadas previstas: <?= (int) $overview['rounds'] ?></li>
            <li>Partidos jugados: <?= (int) $overview['played_matches'] ?></li>
            <li>Partidos pendientes: <?= (int) $overview['pending_matches'] ?></li>
            <li>Temporada activa: <?= htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8') ?></li>
        </ul>
    </article>
    <article>
        <h3>Accesos rapidos</h3>
        <ul class="compact-list">
            <li><a href="index.php?page=buscar">Buscar equipos y jugadores</a></li>
            <li><a href="index.php?page=jugadores">Filtrar jugadores por equipo</a></li>
            <li><a href="index.php?page=partidos">Consultar calendario completo</a></li>
            <li><a href="index.php?page=noticias">Leer resumen de actualidad</a></li>
        </ul>
    </article>
</section>

<section class="panel">
    <h3>Clasificacion provisional</h3>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Equipo</th>
                <th>Pts</th>
                <th>J</th>
                <th>DG</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($classification, 0, 5) as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <a href="index.php?page=equipo&team_id=<?= (int) $row['team_id'] ?>">
                            <?= htmlspecialchars($row['team_name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td><?= (int) $row['points'] ?></td>
                    <td><?= (int) $row['played'] ?></td>
                    <td><?= (int) $row['goal_diff'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="grid-2">
    <article class="panel">
        <h3>Proximos partidos</h3>
        <?php if ($upcomingMatches === []): ?>
            <p>No hay partidos pendientes para la temporada seleccionada.</p>
        <?php else: ?>
            <ul class="match-list">
                <?php foreach ($upcomingMatches as $match): ?>
                    <li>
                        <strong><?= htmlspecialchars($teamsMap[$match['team1']]['name'] ?? ('Equipo ' . $match['team1']), ENT_QUOTES, 'UTF-8') ?></strong>
                        vs
                        <strong><?= htmlspecialchars($teamsMap[$match['team2']]['name'] ?? ('Equipo ' . $match['team2']), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars(format_match_date((string) $match['date']), ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>

    <article class="panel">
        <h3>Ultima actividad</h3>
        <?php if ($completedMatches === []): ?>
            <p>Todavia no hay resultados cerrados en esta temporada.</p>
        <?php else: ?>
            <ul class="match-list">
                <?php foreach ($completedMatches as $match): ?>
                    <li>
                        <strong><?= htmlspecialchars($teamsMap[$match['team1']]['name'] ?? ('Equipo ' . $match['team1']), ENT_QUOTES, 'UTF-8') ?></strong>
                        <?= (int) $match['goals1'] ?> - <?= (int) $match['goals2'] ?>
                        <strong><?= htmlspecialchars($teamsMap[$match['team2']]['name'] ?? ('Equipo ' . $match['team2']), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars(format_match_date((string) $match['date']), ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
</section>

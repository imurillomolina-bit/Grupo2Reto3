<?php

if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

$overview = get_season_overview($xml, $selectedSeason);
$classification = compute_classification($xml, $selectedSeason);
$matches = get_matches_for_season($xml, (int) $selectedSeason['id']);
$upcomingMatches = get_upcoming_matches($matches, 3);
$leader = $classification[0] ?? null;
?>
<section class="panel">
    <h2>Noticias</h2>
    <p>Resumen generado en servidor a partir de la temporada activa y del calendario cargado en XML.</p>
</section>

<section class="grid-2">
    <article class="panel news-card">
        <h3>Apertura de la temporada</h3>
        <p>
            La temporada <strong><?= htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8') ?></strong>
            cuenta con <?= (int) $overview['teams'] ?> equipos, <?= (int) $overview['rounds'] ?> jornadas y
            <?= (int) $overview['matches'] ?> partidos programados.
        </p>
    </article>

    <article class="panel news-card">
        <h3>Liderato provisional</h3>
        <?php if ($leader === null): ?>
            <p>Todavia no hay datos suficientes para construir una clasificacion.</p>
        <?php else: ?>
            <p>
                <?= htmlspecialchars($leader['team_name'], ENT_QUOTES, 'UTF-8') ?> encabeza la clasificacion provisional
                con <?= (int) $leader['points'] ?> puntos y una diferencia de goles de <?= (int) $leader['goal_diff'] ?>.
            </p>
        <?php endif; ?>
    </article>
</section>

<section class="panel">
    <h3>Proxima jornada destacada</h3>
    <?php if ($upcomingMatches === []): ?>
        <p>No hay encuentros pendientes para destacar.</p>
    <?php else: ?>
        <ul class="match-list">
            <?php foreach ($upcomingMatches as $match): ?>
                <li>
                    <strong><?= htmlspecialchars($teamsMap[$match['team1']]['name'] ?? ('Equipo ' . $match['team1']), ENT_QUOTES, 'UTF-8') ?></strong>
                    vs
                    <strong><?= htmlspecialchars($teamsMap[$match['team2']]['name'] ?? ('Equipo ' . $match['team2']), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Jornada <?= (int) $match['round_id'] ?> · <?= htmlspecialchars(format_match_date((string) $match['date']), ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

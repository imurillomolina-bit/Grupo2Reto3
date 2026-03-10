<?php

if ($selectedSeason === null) {
    echo '<section class="panel"><p>No hay temporadas disponibles.</p></section>';
    return;
}

$matches = get_matches_for_season($xml, $selectedSeason['id']);
?>
<section class="panel">
    <h2>Partidos - <?= htmlspecialchars($selectedSeason['name'], ENT_QUOTES, 'UTF-8') ?></h2>
    <p>Vista restringida: solo usuarios autenticados no invitados.</p>
</section>

<section class="panel">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Jornada</th>
                <th>Local</th>
                <th>Resultado</th>
                <th>Visitante</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $match['date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $match['round_id'] ?></td>
                    <td><?= htmlspecialchars($teamsMap[$match['team1']]['name'] ?? ('Equipo ' . $match['team1']), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $match['goals1'] ?> - <?= (int) $match['goals2'] ?></td>
                    <td><?= htmlspecialchars($teamsMap[$match['team2']]['name'] ?? ('Equipo ' . $match['team2']), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $match['status'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

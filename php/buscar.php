<?php

$query = trim((string) ($_GET['q'] ?? ''));
$results = [];

if (mb_strlen($query) >= 2) {
    $needle = mb_strtolower($query);

    foreach ($teamsMap as $team) {
        if (mb_strpos(mb_strtolower($team['name']), $needle) !== false) {
            $results[] = [
                'type' => 'Equipo',
                'label' => $team['name'],
                'url' => 'index.php?page=equipo&team_id=' . (int) $team['id'],
            ];
        }
    }

    foreach ($playersMap as $player) {
        if (mb_strpos(mb_strtolower($player['name']), $needle) !== false) {
            $results[] = [
                'type' => 'Jugador',
                'label' => $player['name'],
                'url' => 'index.php?page=jugadores',
            ];
        }
    }
}
?>
<section class="panel">
    <h2>Buscador</h2>
    <?php if (mb_strlen($query) < 2): ?>
        <p>Escribe al menos 2 caracteres para buscar.</p>
    <?php elseif ($results === []): ?>
        <p>No se encontraron resultados para <strong><?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    <?php else: ?>
        <p>Resultados para <strong><?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?></strong>:</p>
        <ul class="search-results-list">
            <?php foreach ($results as $result): ?>
                <li>
                    <a href="<?= htmlspecialchars($result['url'], ENT_QUOTES, 'UTF-8') ?>">
                        [<?= htmlspecialchars($result['type'], ENT_QUOTES, 'UTF-8') ?>] <?= htmlspecialchars($result['label'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

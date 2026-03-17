<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Normativa | FEDERACIÓN FUTSAL';

$normas = [
    [
        'titulo' => 'Sistema de puntos',
        'texto' => 'Cada victoria suma 3 puntos, el empate reparte 1 punto por equipo y la derrota no puntua.',
    ],
    [
        'titulo' => 'Orden de la tabla',
        'texto' => 'La clasificacion se decide por puntos, diferencia de goles y goles a favor en ese orden.',
    ],
    [
        'titulo' => 'Temporada activa',
        'texto' => 'Desde la cabecera puedes cambiar la temporada para revisar otra edicion de la liga.',
    ],
    [
        'titulo' => 'Consulta de equipos',
        'texto' => 'Cada equipo tiene una ficha con su estadio, ciudad, plantilla y partidos disputados.',
    ],
];

require __DIR__ . '/../includes/header.php';
?>

<main class="page">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Normativa</h2>
            <p>Reglas basicas y formato general de FEDERACIÓN FUTSAL.</p>
        </article>

        <article class="cards-grid news-grid">
            <?php foreach ($normas as $norma): ?>
                <div class="info-card rule-card">
                    <h3><?php echo e($norma['titulo']); ?></h3>
                    <p><?php echo e($norma['texto']); ?></p>
                </div>
            <?php endforeach; ?>
        </article>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
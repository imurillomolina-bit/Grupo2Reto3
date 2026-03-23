<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

$pageTitle = 'Normativa | FEDERACIÃ“N FUTSAL';
$fechaEdicion = date('d/m/Y');

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

<main class="page news-page">
    <section class="panel content-panel newsprint-panel">
        <article class="panel-heading newsprint-header">
            <p class="newsprint-masthead">Marca Futsal</p>
            <h2>Normativa</h2>
            <p class="newsprint-strap">Reglas basicas y formato general de FEDERACIÓN FUTSAL.</p>
            <p class="newsprint-edition">Edicion digital | <?php echo e($fechaEdicion); ?></p>
        </article>

        <article class="newsprint-layout" aria-label="Normativa de la liga">
            <section class="news-lead" aria-label="Resumen general de normativa">
                <p class="news-kicker">Reglamento oficial</p>
                <h3>Todo lo que necesitas para seguir la competiciòn</h3>
                <p class="news-lead-text">Consulta aqui los criterios clave de puntuación, orden de clasificación y uso del portal para esta temporada.</p>
            </section>

            <div class="news-columns" aria-label="Normas principales">
                <?php foreach ($normas as $norma): ?>
                    <article class="news-column-piece">
                        <h4><?php echo e($norma['titulo']); ?></h4>
                        <p><?php echo e($norma['texto']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="news-briefs" aria-label="Recordatorios rapidos">
                <h4>Recordatorios</h4>
                <ul>
                    <li><strong>Victoria:</strong> 3 puntos por partido ganado.</li>
                    <li><strong>Empate:</strong> 1 punto para cada equipo.</li>
                    <li><strong>Desempate:</strong> diferencia de goles y goles a favor.</li>
                </ul>
            </aside>
        </article>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

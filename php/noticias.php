<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

$pageTitle = 'Noticias | FEDERACIÃ“N FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$noticias = [];

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $noticias = build_noticias_temporada($temporada);
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<main class="page news-page">
    <section class="panel content-panel newsprint-panel">
        <article class="panel-heading newsprint-header">
            <p class="newsprint-masthead">Marca Futsal</p>
            <h2>Noticias</h2>
            <p class="newsprint-strap">Actualidad generada con la temporada activa: <strong><?php echo e($temporadaNombre); ?></strong></p>
            <p class="newsprint-edition">Edicion digital | <?php echo e(date('d/m/Y')); ?></p>
        </article>

        <?php if ($error !== null): ?>
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
            </article>
        <?php else: ?>
            <?php if ($noticias === []): ?>
                <article class="cards-grid news-grid">
                    <div class="info-card news-card">
                        <h3>Sin novedades</h3>
                        <p>No se pudieron generar noticias para la temporada actual.</p>
                    </div>
                </article>
            <?php else: ?>
                <?php
                $titularPrincipal = $noticias[0];
                $columnas = array_slice($noticias, 1, 4);
                $breves = array_slice($noticias, 5);
                ?>
                <article class="newsprint-layout" aria-label="Portada de noticias">
                    <section class="news-lead" aria-label="Titular principal">
                        <p class="news-kicker">Portada</p>
                        <h3><?php echo e($titularPrincipal['titulo']); ?></h3>
                        <p class="news-lead-text"><?php echo e($titularPrincipal['texto']); ?></p>
                    </section>

                    <div class="news-columns" aria-label="Noticias destacadas">
                        <?php foreach ($columnas as $noticia): ?>
                            <article class="news-column-piece">
                                <h4><?php echo e($noticia['titulo']); ?></h4>
                                <p><?php echo e($noticia['texto']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($breves !== []): ?>
                        <aside class="news-briefs" aria-label="Breves">
                            <h4>Ultima hora</h4>
                            <ul>
                                <?php foreach ($breves as $breve): ?>
                                    <li>
                                        <strong><?php echo e($breve['titulo']); ?>:</strong>
                                        <?php echo e($breve['texto']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </aside>
                    <?php endif; ?>
                </article>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

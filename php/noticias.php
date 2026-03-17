<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Noticias | FEDERACIÓN FUTSAL';

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

<main class="page">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Noticias</h2>
            <p>Actualidad generada con la temporada activa: <strong><?php echo e($temporadaNombre); ?></strong></p>
        </article>

        <?php if ($error !== null): ?>
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
            </article>
        <?php else: ?>
            <article class="cards-grid news-grid">
                <?php if ($noticias === []): ?>
                    <div class="info-card news-card">
                        <h3>Sin novedades</h3>
                        <p>No se pudieron generar noticias para la temporada actual.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($noticias as $noticia): ?>
                        <div class="info-card news-card">
                            <h3><?php echo e($noticia['titulo']); ?></h3>
                            <p><?php echo e($noticia['texto']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
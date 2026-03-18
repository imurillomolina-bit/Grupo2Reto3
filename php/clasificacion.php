<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Clasificacion | FEDERACIÓN FUTSAL';

$error = null;
$temporadaNombre = 'No disponible';
$clasificacion = [];
$temporadas = [];

try {
    $xml = load_liga_xml();
    $temporada = get_temporada_actual($xml);
    $temporadaNombre = (string) $temporada['nombre'];
    $temporadas = get_temporadas($xml);
    $clasificacion = build_clasificacion($temporada);
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Contenido central -->
<main class="page page-home">
    <!-- Section: Tabla de liga -->
    <section id="clasificacion" class="panel standings-panel">
        <article class="panel-heading">
            <h2>Clasificación</h2>
            <p>Temporada seleccionada: <strong><?php echo e($temporadaNombre); ?></strong></p>

            <form class="season-form" action="set_temporada.php" method="post">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required>
                    <?php foreach ($temporadas as $temporadaItem): ?>
                        <option
                            value="<?php echo e($temporadaItem['id']); ?>"
                            <?php echo (($temporadaItem['id'] ?? '') === ($_SESSION['temporada_actual'] ?? '')) ? 'selected' : ''; ?>
                        >
                            <?php echo e($temporadaItem['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <?php if ($error !== null): ?>
            <article class="panel-error">
                <p><?php echo e($error); ?></p>
            </article>
        <?php else: ?>
            <article class="table-wrap" aria-label="Tabla de clasificacion">
                <table class="standings-table">
                    <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Equipo</th>
                        <th>PJ</th>
                        <th>PG</th>
                        <th class="hide-mobile">PE</th>
                        <th class="hide-mobile">PP</th>
                        <th class="hide-mobile">GF</th>
                        <th class="hide-mobile">GC</th>
                        <th>DG</th>
                        <th>PTS</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($clasificacion as $idx => $fila): ?>
                        <tr>
                            <td><?php echo (int) ($idx + 1); ?></td>
                            <td>
                                <a class="team-link" href="equipo.php?id=<?php echo (int) $fila['id']; ?>">
                                    <img src="<?php echo e($fila['escudo']); ?>" alt="Escudo de <?php echo e($fila['nombre']); ?>">
                                    <span><?php echo e($fila['nombre']); ?></span>
                                </a>
                            </td>
                            <td><?php echo (int) $fila['pj']; ?></td>
                            <td><?php echo (int) $fila['pg']; ?></td>
                            <td class="hide-mobile"><?php echo (int) $fila['pe']; ?></td>
                            <td class="hide-mobile"><?php echo (int) $fila['pp']; ?></td>
                            <td class="hide-mobile"><?php echo (int) $fila['gf']; ?></td>
                            <td class="hide-mobile"><?php echo (int) $fila['gc']; ?></td>
                            <td><?php echo (int) $fila['dg']; ?></td>
                            <td><strong><?php echo (int) $fila['pts']; ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        <?php endif; ?>
    </section>

    <section class="home-sections" aria-label="Apartados principales">
        <a class="panel quick-link-panel" href="equipo.php">
            <div class="panel-heading">
                <h2>Equipo</h2>
                <p>Ve todos los clubes participantes y entra a cada ficha completa.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="jugadores.php">
            <div class="panel-heading">
                <h2>Jugadores</h2>
                <p>Consulta la plantilla completa de la temporada activa.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="partidos.php">
            <div class="panel-heading">
                <h2>Partidos</h2>
                <p>Consulta todos los resultados y filtra por equipo.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="normativa.php">
            <div class="panel-heading">
                <h2>Normativa</h2>
                <p>Revisa las reglas basicas y el formato de la competicion.</p>
            </div>
        </a>

        <a class="panel quick-link-panel" href="noticias.php">
            <div class="panel-heading">
                <h2>Noticias</h2>
                <p>Lee los titulares generados a partir de los resultados recientes.</p>
            </div>
        </a>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>

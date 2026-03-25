<?php

declare(strict_types=1);

// Vista de clasificacion: prepara la pagina y el estado de temporada activa.

require_once __DIR__ . '/../includes/app_init.php';

// Se usa para sincronizar cliente con la temporada guardada en sesion.
$pageTitle = 'Clasificacion | FEDERACIÃ“N FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Contenido central -->
<main class="page page-home">
    <!-- Section: Tabla de liga -->
    <section id="clasificacion" class="panel standings-panel">
        <article class="panel-heading">
            <h2>Clasificación</h2>
            <p>Temporada seleccionada: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="#" method="get">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="clasificacion_render" class="table-wrap" aria-label="Tabla de clasificacion">
            <p>Cargando clasificaciÃ³n...</p>
        </article>

        <article id="clasificacion_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar la clasificaciÃ³n con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar la clasificaciÃ³n en esta versiÃ³n.</p>
            </article>
        </noscript>
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

<script>
(function () {
    // Rutas de datos base para renderizar la clasificacion en cliente.
    var xmlUrl = '../data/datos.xml';
    var xslUrl = '../data/xsl/clasificacion.xsl';
    var temporadaSesion = '<?php echo e($temporadaSesion); ?>';

    // Referencias a nodos del DOM que se actualizan durante el flujo.
    var renderTarget = document.getElementById('clasificacion_render');
    var errorTarget = document.getElementById('clasificacion_error');
    var seasonName = document.getElementById('temporada_nombre');
    var seasonSelect = document.getElementById('temporada_id');
    var seasonForm = document.getElementById('season_form');

    // Parseador XML para documentos de datos y de estilo.
    function parseXml(text) {
        return new window.DOMParser().parseFromString(text, 'text/xml');
    }

    // Detecta errores de parseo generados por el navegador.
    function hasXmlError(doc) {
        return doc.getElementsByTagName('parsererror').length > 0;
    }

    // Extrae temporadas disponibles desde el XML principal.
    function getTemporadas(xmlDoc) {
        return Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).map(function (n) {
            return {
                id: n.getAttribute('id') || '',
                nombre: n.getAttribute('nombre') || '',
                actual: (n.getAttribute('actual') || '') === 'si'
            };
        });
    }

    // Prioridad de seleccion: query string, sesion y luego temporada actual del XML.
    function getSelectedSeasonId(temporadas) {
        var params = new URLSearchParams(window.location.search);
        var byQuery = params.get('temporada_id');
        if (byQuery && temporadas.some(function (t) { return t.id === byQuery; })) {
            return byQuery;
        }

        if (temporadaSesion && temporadas.some(function (t) { return t.id === temporadaSesion; })) {
            return temporadaSesion;
        }

        var actual = temporadas.find(function (t) { return t.actual; });
        if (actual) {
            return actual.id;
        }

        return temporadas.length > 0 ? temporadas[0].id : '';
    }

    // Rellena el selector visual de temporadas en cabecera de panel.
    function fillSeasonSelect(temporadas, selectedId) {
        seasonSelect.innerHTML = '';
        temporadas.forEach(function (temp) {
            var option = document.createElement('option');
            option.value = temp.id;
            option.textContent = temp.nombre;
            if (temp.id === selectedId) {
                option.selected = true;
            }
            seasonSelect.appendChild(option);
        });
    }

    // Actualiza texto de temporada actualmente mostrada.
    function updateHeaderSeasonName(temporadas, selectedId) {
        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    // Aplica XSLT con parametro de temporada y reemplaza el contenido renderizado.
    function renderWithXsl(xmlDoc, xslDoc, temporadaId) {
        var processor = new window.XSLTProcessor();
        processor.importStylesheet(xslDoc);
        processor.setParameter(null, 'temporadaId', temporadaId);

        var fragment = processor.transformToFragment(xmlDoc, document);
        renderTarget.innerHTML = '';
        renderTarget.appendChild(fragment);
    }

    // Carga XML y XSL en paralelo para reducir tiempo de espera.
    Promise.all([
        fetch(xmlUrl).then(function (r) { return r.text(); }),
        fetch(xslUrl).then(function (r) { return r.text(); })
    ]).then(function (payload) {
        var xmlDoc = parseXml(payload[0]);
        var xslDoc = parseXml(payload[1]);

        if (hasXmlError(xmlDoc) || hasXmlError(xslDoc)) {
            throw new Error('Error de parseo XML/XSL');
        }

        var temporadas = getTemporadas(xmlDoc);
        var selectedSeasonId = getSelectedSeasonId(temporadas);

        if (!selectedSeasonId) {
            throw new Error('No hay temporadas disponibles');
        }

        fillSeasonSelect(temporadas, selectedSeasonId);
        updateHeaderSeasonName(temporadas, selectedSeasonId);
        renderWithXsl(xmlDoc, xslDoc, selectedSeasonId);

        // Cambio de temporada sin recargar pagina; solo rerender y URL.
        seasonForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var nextSeasonId = seasonSelect.value;
            renderWithXsl(xmlDoc, xslDoc, nextSeasonId);
            updateHeaderSeasonName(temporadas, nextSeasonId);

            var nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('temporada_id', nextSeasonId);
            window.history.replaceState({}, '', nextUrl.toString());
        });
    }).catch(function (err) {
        // Fallback de error: oculta tabla y muestra mensaje detallado.
        renderTarget.style.display = 'none';
        errorTarget.style.display = 'block';

        var message = 'No se pudo cargar la clasificaciÃ³n con XML/XSL.';
        if (err && err.message) {
            message += ' ' + err.message;
        }

        var p = errorTarget.querySelector('p');
        if (p) {
            p.textContent = message;
        }
    });
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>


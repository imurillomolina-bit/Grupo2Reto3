<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/app_init.php';

$pageTitle = 'Detalle de equipo | FEDERACIÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));
$equipoIdRaw = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);
$equipoId = is_string($equipoIdRaw) ? trim($equipoIdRaw) : '';

require __DIR__ . '/../includes/header.php';
?>

<!-- Main: Informacion del equipo -->
<main class="page page-team">
    <!-- Section: Ficha principal -->
    <section class="panel team-panel">
        <article class="panel-heading">
            <h2 id="equipos_titulo">Equipos</h2>
            <p>Temporada activa: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="#" method="get">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="equipos_render" class="cards-grid team-summary-grid" aria-label="Listado y detalle de equipos">
            <p>Cargando equipos...</p>
        </article>

        <article id="equipos_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar el apartado de equipos con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar Equipos en esta versión.</p>
            </article>
        </noscript>
    </section>
</main>

<script>
(function () {
    var xmlUrl = '../data/datos.xml';
    var xslUrl = '../data/xsl/equipos.xsl';
    var temporadaSesion = '<?php echo e($temporadaSesion); ?>';
    var equipoIdInicial = '<?php echo e($equipoId); ?>';

    var renderTarget = document.getElementById('equipos_render');
    var errorTarget = document.getElementById('equipos_error');
    var titleTarget = document.getElementById('equipos_titulo');
    var seasonName = document.getElementById('temporada_nombre');
    var seasonSelect = document.getElementById('temporada_id');
    var seasonForm = document.getElementById('season_form');

    function parseXml(text) {
        return new window.DOMParser().parseFromString(text, 'text/xml');
    }

    function hasXmlError(doc) {
        return doc.getElementsByTagName('parsererror').length > 0;
    }

    function getTemporadas(xmlDoc) {
        return Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).map(function (n) {
            return {
                id: n.getAttribute('id') || '',
                nombre: n.getAttribute('nombre') || '',
                actual: (n.getAttribute('actual') || '') === 'si'
            };
        });
    }

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

    function getSelectedTeamId() {
        var params = new URLSearchParams(window.location.search);
        var byQuery = params.get('id');
        if (byQuery) {
            return byQuery;
        }
        return equipoIdInicial;
    }

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

    function updateHeaderSeasonName(temporadas, selectedId) {
        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    function updateTitle(teamId) {
        titleTarget.textContent = teamId ? 'Detalle de equipo' : 'Equipos';
    }

    function renderWithXsl(xmlDoc, xslDoc, temporadaId, equipoId) {
        var processor = new window.XSLTProcessor();
        processor.importStylesheet(xslDoc);
        processor.setParameter(null, 'temporadaId', temporadaId);
        processor.setParameter(null, 'equipoId', equipoId || '');

        var fragment = processor.transformToFragment(xmlDoc, document);
        renderTarget.innerHTML = '';
        renderTarget.appendChild(fragment);
    }

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
        var selectedTeamId = getSelectedTeamId();

        if (!selectedSeasonId) {
            throw new Error('No hay temporadas disponibles');
        }

        fillSeasonSelect(temporadas, selectedSeasonId);
        updateHeaderSeasonName(temporadas, selectedSeasonId);
        updateTitle(selectedTeamId);
        renderWithXsl(xmlDoc, xslDoc, selectedSeasonId, selectedTeamId);

        seasonForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var nextSeasonId = seasonSelect.value;
            var currentTeamId = getSelectedTeamId();

            renderWithXsl(xmlDoc, xslDoc, nextSeasonId, currentTeamId);
            updateHeaderSeasonName(temporadas, nextSeasonId);
            updateTitle(currentTeamId);

            var nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('temporada_id', nextSeasonId);
            if (currentTeamId) {
                nextUrl.searchParams.set('id', currentTeamId);
            }
            window.history.replaceState({}, '', nextUrl.toString());
        });
    }).catch(function (err) {
        renderTarget.style.display = 'none';
        errorTarget.style.display = 'block';

        var message = 'No se pudo cargar el apartado de equipos con XML/XSL.';
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


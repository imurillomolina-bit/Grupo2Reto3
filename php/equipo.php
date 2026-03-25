<?php

declare(strict_types=1);

// Vista de detalle de equipo con datos filtrados por temporada seleccionada.

require_once __DIR__ . '/../includes/app_init.php';

// Estado inicial recibido desde sesion y query para sincronizar la vista.
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
    // Recursos fuente para renderizar equipos mediante XSLT en cliente.
    var xmlUrl = '../data/datos.xml';
    var xslUrl = '../data/xsl/equipos.xsl';
    var temporadaSesion = '<?php echo e($temporadaSesion); ?>';
    var equipoIdInicial = '<?php echo e($equipoId); ?>';

    // Nodos del DOM que se actualizan durante la carga y filtrado.
    var renderTarget = document.getElementById('equipos_render');
    var errorTarget = document.getElementById('equipos_error');
    var titleTarget = document.getElementById('equipos_titulo');
    var seasonName = document.getElementById('temporada_nombre');
    var seasonSelect = document.getElementById('temporada_id');
    var seasonForm = document.getElementById('season_form');

    // Parsea texto XML/XSL a documento DOM.
    function parseXml(text) {
        return new window.DOMParser().parseFromString(text, 'text/xml');
    }

    // Detecta errores de parseo generados por el navegador.
    function hasXmlError(doc) {
        return doc.getElementsByTagName('parsererror').length > 0;
    }

    // Lee temporadas desde el XML para selector y contexto visible.
    function getTemporadas(xmlDoc) {
        return Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).map(function (n) {
            return {
                id: n.getAttribute('id') || '',
                nombre: n.getAttribute('nombre') || '',
                actual: (n.getAttribute('actual') || '') === 'si'
            };
        });
    }

    // Prioriza temporada por query, luego sesion y finalmente marcada como actual.
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

    // Determina equipo objetivo por query y, si no existe, usa el inicial del servidor.
    function getSelectedTeamId() {
        var params = new URLSearchParams(window.location.search);
        var byQuery = params.get('id');
        if (byQuery) {
            return byQuery;
        }
        return equipoIdInicial;
    }

    // Rellena el selector de temporada con la opcion activa seleccionada.
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

    // Refresca el nombre de temporada visible en cabecera del panel.
    function updateHeaderSeasonName(temporadas, selectedId) {
        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    // Ajusta el titulo segun se muestre listado completo o ficha de un equipo.
    function updateTitle(teamId) {
        titleTarget.textContent = teamId ? 'Detalle de equipo' : 'Equipos';
    }

    // Aplica la plantilla XSL con parametros de temporada y equipo.
    function renderWithXsl(xmlDoc, xslDoc, temporadaId, equipoId) {
        var processor = new window.XSLTProcessor();
        processor.importStylesheet(xslDoc);
        processor.setParameter(null, 'temporadaId', temporadaId);
        processor.setParameter(null, 'equipoId', equipoId || '');

        var fragment = processor.transformToFragment(xmlDoc, document);
        renderTarget.innerHTML = '';
        renderTarget.appendChild(fragment);
    }

    // Carga de archivos en paralelo para minimizar espera inicial.
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

        // Cambio de temporada sin recarga, conservando equipo si aplica.
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
        // Modo degradado cuando falla XML/XSL o su transformacion.
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


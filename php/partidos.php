<?php

declare(strict_types=1);

// Vista de partidos con filtros por temporada y jornada seleccionadas.

require_once __DIR__ . '/../includes/app_init.php';

// Temporada de sesion para iniciar filtros del lado cliente.
$pageTitle = 'Partidos | FEDERACIAÓN FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-matches">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Partidos</h2>
            <p>
                Temporada seleccionada: <strong id="temporada_nombre">Cargando...</strong>
                | Jornada: <strong id="jornada_nombre">-</strong>
            </p>

            <div class="matches-filters">
                <form class="season-form" id="season_form" action="#" method="get">
                    <label for="temporada_id">Cambiar temporada</label>
                    <select id="temporada_id" name="temporada_id" required></select>
                    <button type="submit">Cambiar</button>
                </form>

                <form class="season-form" id="jornada_form" action="#" method="get">
                    <label for="jornada_id">Filtrar por jornada</label>
                    <select id="jornada_id" name="jornada_id" required></select>
                    <button type="submit">Aplicar</button>
                </form>
            </div>
        </article>

        <article id="partidos_render" class="matches-wrap" aria-label="Listado de partidos">
            <p>Cargando partidos...</p>
        </article>

        <article id="partidos_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar el apartado de partidos con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar Partidos en esta version.</p>
            </article>
        </noscript>
    </section>
</main>

<script>
(function () {
    // Rutas de origen para transformar XML con plantilla XSL de partidos.
    var xmlUrl = '../data/datos.xml';
    var xslUrl = '../data/xsl/partidos.xsl';
    var temporadaSesion = '<?php echo e($temporadaSesion); ?>';

    // Referencias a elementos de interfaz y formularios de filtros.
    var renderTarget = document.getElementById('partidos_render');
    var errorTarget = document.getElementById('partidos_error');
    var seasonName = document.getElementById('temporada_nombre');
    var jornadaName = document.getElementById('jornada_nombre');
    var seasonSelect = document.getElementById('temporada_id');
    var jornadaSelect = document.getElementById('jornada_id');
    var seasonForm = document.getElementById('season_form');
    var jornadaForm = document.getElementById('jornada_form');

    // Convierte texto XML/XSL en documento navegable.
    function parseXml(text) {
        return new window.DOMParser().parseFromString(text, 'text/xml');
    }

    // Comprueba si el parser del navegador devolvio error.
    function hasXmlError(doc) {
        return doc.getElementsByTagName('parsererror').length > 0;
    }

    // Extrae catalogo de temporadas para filtros y cabecera.
    function getTemporadas(xmlDoc) {
        return Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).map(function (n) {
            return {
                id: n.getAttribute('id') || '',
                nombre: n.getAttribute('nombre') || '',
                actual: (n.getAttribute('actual') || '') === 'si'
            };
        });
    }

    // Obtiene nodo de temporada por id, con fallback de busqueda manual.
    function getTemporadaNode(xmlDoc, temporadaId) {
        return xmlDoc.querySelector('liga > temporadas > temporada[id="' + temporadaId + '"]') ||
            Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).find(function (n) {
                return (n.getAttribute('id') || '') === temporadaId;
            }) ||
            null;
    }

    // Construye jornadas a partir de fechas unicas de partidos.
    function getJornadas(temporadaNode) {
        if (!temporadaNode) {
            return [];
        }

        var fechasUnicas = [];
        Array.from(temporadaNode.querySelectorAll('partidos > partido')).forEach(function (partido) {
            var fecha = partido.getAttribute('fecha') || '';
            if (fecha && fechasUnicas.indexOf(fecha) === -1) {
                fechasUnicas.push(fecha);
            }
        });

        fechasUnicas.sort();

        return fechasUnicas.map(function (fecha, index) {
            return {
                numero: index + 1,
                fecha: fecha
            };
        });
    }

    // Prioridad de temporada: query string, sesion, actual y primera disponible.
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

    // Resuelve jornada por query y valida que exista en la temporada actual.
    function getSelectedJornadaNumero(jornadas) {
        var params = new URLSearchParams(window.location.search);
        var byQueryRaw = params.get('jornada_id');
        var byQuery = byQueryRaw ? parseInt(byQueryRaw, 10) : NaN;

        if (!Number.isNaN(byQuery) && jornadas.some(function (j) { return j.numero === byQuery; })) {
            return byQuery;
        }

        return jornadas.length > 0 ? jornadas[0].numero : 0;
    }

    // Rellena selector de temporadas con la opcion activa marcada.
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

    // Rellena selector de jornadas segun la temporada seleccionada.
    function fillJornadaSelect(jornadas, selectedNumero) {
        jornadaSelect.innerHTML = '';
        jornadas.forEach(function (jornada) {
            var option = document.createElement('option');
            option.value = String(jornada.numero);
            option.textContent = 'Jornada ' + String(jornada.numero);
            if (jornada.numero === selectedNumero) {
                option.selected = true;
            }
            jornadaSelect.appendChild(option);
        });
    }

    // Refresca nombre de temporada mostrado en cabecera.
    function updateHeaderSeasonName(temporadas, selectedId) {
        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    // Refresca etiqueta de jornada activa.
    function updateHeaderJornadaName(selectedNumero) {
        jornadaName.textContent = selectedNumero > 0 ? String(selectedNumero) : '-';
    }

    // Renderiza partidos filtrados por temporada y fecha de jornada.
    function renderWithXsl(xmlDoc, xslDoc, temporadaId, fechaSeleccionada) {
        var processor = new window.XSLTProcessor();
        processor.importStylesheet(xslDoc);
        processor.setParameter(null, 'temporadaId', temporadaId);
        processor.setParameter(null, 'fechaSeleccionada', fechaSeleccionada || '');

        var fragment = processor.transformToFragment(xmlDoc, document);
        renderTarget.innerHTML = '';
        renderTarget.appendChild(fragment);
    }

    // Carga XML y XSL en paralelo para acelerar el primer render.
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

        var temporadaNode = getTemporadaNode(xmlDoc, selectedSeasonId);

        var jornadas = getJornadas(temporadaNode);
        var selectedJornadaNumero = getSelectedJornadaNumero(jornadas);
        var selectedJornada = jornadas.find(function (j) { return j.numero === selectedJornadaNumero; }) || null;

        fillSeasonSelect(temporadas, selectedSeasonId);
        fillJornadaSelect(jornadas, selectedJornadaNumero);
        updateHeaderSeasonName(temporadas, selectedSeasonId);
        updateHeaderJornadaName(selectedJornadaNumero);
        renderWithXsl(xmlDoc, xslDoc, selectedSeasonId, selectedJornada ? selectedJornada.fecha : '');

        // Cambio de temporada: recalcula jornadas y actualiza URL sin recargar.
        seasonForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var nextSeasonId = seasonSelect.value;

            var nextTemporadaNode = getTemporadaNode(xmlDoc, nextSeasonId);

            var nextJornadas = getJornadas(nextTemporadaNode);
            var nextSelectedJornadaNumero = nextJornadas.length > 0 ? nextJornadas[0].numero : 0;
            var nextSelectedJornada = nextJornadas.find(function (j) {
                return j.numero === nextSelectedJornadaNumero;
            }) || null;

            fillJornadaSelect(nextJornadas, nextSelectedJornadaNumero);
            updateHeaderSeasonName(temporadas, nextSeasonId);
            updateHeaderJornadaName(nextSelectedJornadaNumero);
            renderWithXsl(xmlDoc, xslDoc, nextSeasonId, nextSelectedJornada ? nextSelectedJornada.fecha : '');

            var nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('temporada_id', nextSeasonId);
            if (nextSelectedJornadaNumero > 0) {
                nextUrl.searchParams.set('jornada_id', String(nextSelectedJornadaNumero));
            } else {
                nextUrl.searchParams.delete('jornada_id');
            }
            window.history.replaceState({}, '', nextUrl.toString());
        });

        // Cambio de jornada dentro de la temporada actualmente seleccionada.
        jornadaForm.addEventListener('submit', function (ev) {
            ev.preventDefault();

            var currentSeasonId = seasonSelect.value;
            var currentTemporadaNode = getTemporadaNode(xmlDoc, currentSeasonId);
            var currentJornadas = getJornadas(currentTemporadaNode);

            var nextJornadaNumero = parseInt(jornadaSelect.value, 10);
            if (Number.isNaN(nextJornadaNumero)) {
                nextJornadaNumero = currentJornadas.length > 0 ? currentJornadas[0].numero : 0;
            }

            var nextJornada = currentJornadas.find(function (j) {
                return j.numero === nextJornadaNumero;
            }) || null;

            updateHeaderSeasonName(temporadas, currentSeasonId);
            updateHeaderJornadaName(nextJornadaNumero);
            renderWithXsl(xmlDoc, xslDoc, currentSeasonId, nextJornada ? nextJornada.fecha : '');

            var nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('temporada_id', currentSeasonId);
            if (nextJornadaNumero > 0) {
                nextUrl.searchParams.set('jornada_id', String(nextJornadaNumero));
            } else {
                nextUrl.searchParams.delete('jornada_id');
            }
            window.history.replaceState({}, '', nextUrl.toString());
        });
    }).catch(function (err) {
        // Estado de error: oculta contenido y muestra mensaje detallado.
        renderTarget.style.display = 'none';
        errorTarget.style.display = 'block';

        var message = 'No se pudo cargar el apartado de partidos con XML/XSL.';
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


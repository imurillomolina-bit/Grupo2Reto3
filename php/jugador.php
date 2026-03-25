<?php

declare(strict_types=1);

// Vista de ficha individual de jugador segun la temporada en sesion.

require_once __DIR__ . '/../includes/app_init.php';

// Estado inicial de temporada y jugador para hidratar el cliente.
$pageTitle = 'Ficha de jugador | FEDERACION FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));
$jugadorIdRaw = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);
$jugadorId = is_string($jugadorIdRaw) ? trim($jugadorIdRaw) : '';

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-player-detail">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Ficha de jugador</h2>
            <p>Temporada activa: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="#" method="get">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="jugador_render" class="player-basic-detail" aria-label="Ficha de jugador">
            <p>Cargando jugador...</p>
        </article>

        <article id="jugador_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar la ficha de jugador con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar la ficha del jugador en esta version.</p>
            </article>
        </noscript>
    </section>
</main>

<script>
(function () {
    // Fuentes XML/XSL para generar la ficha de jugador en navegador.
    var xmlUrl = '../data/datos.xml';
    var xslUrl = '../data/xsl/jugadores.xsl';
    var temporadaSesion = '<?php echo e($temporadaSesion); ?>';
    var jugadorIdInicial = '<?php echo e($jugadorId); ?>';

    // Elementos de interfaz que se actualizan durante el proceso.
    var renderTarget = document.getElementById('jugador_render');
    var errorTarget = document.getElementById('jugador_error');
    var seasonName = document.getElementById('temporada_nombre');
    var seasonSelect = document.getElementById('temporada_id');
    var seasonForm = document.getElementById('season_form');

    // Conversor de texto a documento XML/XSL.
    function parseXml(text) {
        return new window.DOMParser().parseFromString(text, 'text/xml');
    }

    // Comprobacion basica de errores de parseo del navegador.
    function hasXmlError(doc) {
        return doc.getElementsByTagName('parsererror').length > 0;
    }

    // Lee temporadas para selector y sincronizacion de cabecera.
    function getTemporadas(xmlDoc) {
        return Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).map(function (n) {
            return {
                id: n.getAttribute('id') || '',
                nombre: n.getAttribute('nombre') || '',
                actual: (n.getAttribute('actual') || '') === 'si'
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

    // Prioriza id de jugador en query para soportar navegacion directa.
    function getSelectedPlayerId() {
        var params = new URLSearchParams(window.location.search);
        return params.get('id') || jugadorIdInicial;
    }

    // Carga opciones de temporada en el selector visual.
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

    // Refleja temporada actual en el texto de cabecera.
    function updateHeaderSeasonName(temporadas, selectedId) {
        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    // Ejecuta transformacion XSLT con temporada y jugador como parametros.
    function renderWithXsl(xmlDoc, xslDoc, temporadaId, jugadorId) {
        var processor = new window.XSLTProcessor();
        processor.importStylesheet(xslDoc);
        processor.setParameter(null, 'temporadaId', temporadaId);
        processor.setParameter(null, 'jugadorId', jugadorId || '');

        var fragment = processor.transformToFragment(xmlDoc, document);
        renderTarget.innerHTML = '';
        renderTarget.appendChild(fragment);
    }

    // Si una foto falla, prueba variantes y finalmente avatar generado.
    function applyPlayerImageFallback(root) {
        var images = root.querySelectorAll('img');

        // Normaliza el orden de jugador a 2 digitos para construir nombres de archivo.
        function pad2(value) {
            var n = String(value || '').trim();
            if (n.length === 1) {
                return '0' + n;
            }
            return n;
        }

        // Elimina rutas duplicadas para no repetir intentos de carga.
        function unique(values) {
            var out = [];
            values.forEach(function (v) {
                if (v && out.indexOf(v) === -1) {
                    out.push(v);
                }
            });
            return out;
        }

        // Recorre cada imagen renderizada y encadena estrategias de carga.
        images.forEach(function (img) {
            var alt = (img.getAttribute('alt') || '').trim();
            var nombre = alt.replace(/^Foto de\s+/i, '').trim();
            var equipoId = (img.getAttribute('data-equipo-id') || '').trim();
            var ordenJugador = pad2(img.getAttribute('data-orden-jugador') || '');

            if (!nombre) {
                nombre = img.getAttribute('data-nombre') || 'Jugador';
            }

            var currentSrc = (img.getAttribute('src') || '').trim();
            var candidates = [];

            if (currentSrc) {
                candidates.push(currentSrc);
            }

            if (equipoId && ordenJugador) {
                candidates.push('../img/Jugadores2024_2025/J' + equipoId + '00000' + ordenJugador + '.png');
                candidates.push('../img/Jugadores2024_2025/J' + equipoId + '0000' + ordenJugador + '.png');
            }

            candidates = unique(candidates);

            if (candidates.length === 0) {
                img.src = 'avatar.php?nombre=' + encodeURIComponent(nombre);
                return;
            }

            var idx = 0;
            img.onerror = function () {
                // Intenta siguiente candidato y, si no hay mas, usa avatar.
                idx += 1;
                if (idx < candidates.length) {
                    this.src = candidates[idx];
                    return;
                }

                this.onerror = null;
                this.src = 'avatar.php?nombre=' + encodeURIComponent(nombre);
            };

            img.src = candidates[0];
        });
    }

    // Carga XML y XSL en paralelo para mejorar tiempo de respuesta.
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
        var selectedPlayerId = getSelectedPlayerId();

        if (!selectedSeasonId) {
            throw new Error('No hay temporadas disponibles');
        }

        fillSeasonSelect(temporadas, selectedSeasonId);
        updateHeaderSeasonName(temporadas, selectedSeasonId);
        renderWithXsl(xmlDoc, xslDoc, selectedSeasonId, selectedPlayerId);
        applyPlayerImageFallback(renderTarget);

        // Cambio de temporada sin recargar, manteniendo jugador si existe.
        seasonForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var nextSeasonId = seasonSelect.value;
            var currentPlayerId = getSelectedPlayerId();

            renderWithXsl(xmlDoc, xslDoc, nextSeasonId, currentPlayerId);
            updateHeaderSeasonName(temporadas, nextSeasonId);
            applyPlayerImageFallback(renderTarget);

            var nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('temporada_id', nextSeasonId);
            if (currentPlayerId) {
                nextUrl.searchParams.set('id', currentPlayerId);
            }
            window.history.replaceState({}, '', nextUrl.toString());
        });
    }).catch(function (err) {
        // Muestra estado de error cuando falla carga o transformacion.
        renderTarget.style.display = 'none';
        errorTarget.style.display = 'block';

        var message = 'No se pudo cargar la ficha de jugador con XML/XSL.';
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


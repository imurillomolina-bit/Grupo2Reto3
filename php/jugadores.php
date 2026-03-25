<?php

declare(strict_types=1);

// Vista de listado de jugadores con soporte de cambio de temporada.

require_once __DIR__ . '/../includes/app_init.php';

// Temporada de sesion para precargar la seleccion en cliente.
$pageTitle = 'Jugadores | FEDERACION FUTSAL';
$temporadaSesion = trim((string) ($_SESSION['temporada_actual'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<main class="page page-players">
    <section class="panel content-panel">
        <article class="panel-heading">
            <h2>Jugadores</h2>
            <p>Temporada activa: <strong id="temporada_nombre">Cargando...</strong></p>

            <form class="season-form" id="season_form" action="#" method="get">
                <label for="temporada_id">Cambiar temporada</label>
                <select id="temporada_id" name="temporada_id" required></select>
                <button type="submit">Cambiar</button>
            </form>
        </article>

        <article id="jugadores_render" aria-label="Listado de jugadores">
            <p>Cargando jugadores...</p>
        </article>

        <article id="jugadores_error" class="panel-error" style="display:none;">
            <p>No se pudo cargar el apartado de jugadores con XML/XSL.</p>
        </article>

        <noscript>
            <article class="panel-error">
                <p>Necesitas JavaScript activado para visualizar Jugadores en esta version.</p>
            </article>
        </noscript>
    </section>
</main>

<script>
(function () {
    // Fuentes de datos para transformar XML a HTML mediante XSLT.
    var xmlUrl = '../data/datos.xml';
    var xslUrl = '../data/xsl/jugadores.xsl';
    var temporadaSesion = '<?php echo e($temporadaSesion); ?>';

    // Referencias a nodos que se actualizan durante la carga/render.
    var renderTarget = document.getElementById('jugadores_render');
    var errorTarget = document.getElementById('jugadores_error');
    var seasonName = document.getElementById('temporada_nombre');
    var seasonSelect = document.getElementById('temporada_id');
    var seasonForm = document.getElementById('season_form');

    // Convierte texto a documento XML/XSL.
    function parseXml(text) {
        return new window.DOMParser().parseFromString(text, 'text/xml');
    }

    // Detecta parseos fallidos del navegador.
    function hasXmlError(doc) {
        return doc.getElementsByTagName('parsererror').length > 0;
    }

    // Obtiene lista de temporadas para selector y cabecera.
    function getTemporadas(xmlDoc) {
        return Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).map(function (n) {
            return {
                id: n.getAttribute('id') || '',
                nombre: n.getAttribute('nombre') || '',
                actual: (n.getAttribute('actual') || '') === 'si'
            };
        });
    }

    // Prioridad de temporada: query, sesion, marca actual y primera disponible.
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

    // Construye opciones del select dejando marcada la temporada activa.
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

    // Actualiza etiqueta visible con el nombre de temporada seleccionada.
    function updateHeaderSeasonName(temporadas, selectedId) {
        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    // Renderiza listado de jugadores aplicando plantilla XSL y parametros.
    function renderWithXsl(xmlDoc, xslDoc, temporadaId) {
        var processor = new window.XSLTProcessor();
        processor.importStylesheet(xslDoc);
        processor.setParameter(null, 'temporadaId', temporadaId);
        processor.setParameter(null, 'jugadorId', '');

        var fragment = processor.transformToFragment(xmlDoc, document);
        renderTarget.innerHTML = '';
        renderTarget.appendChild(fragment);
    }

    // Estrategia de imagen: probar rutas candidatas y caer a avatar dinamico.
    function applyPlayerImageFallback(root) {
        var images = root.querySelectorAll('img');

        // Normaliza orden a dos digitos para componer nombre de archivo.
        function pad2(value) {
            var n = String(value || '').trim();
            if (n.length === 1) {
                return '0' + n;
            }
            return n;
        }

        // Elimina candidatos repetidos para evitar peticiones redundantes.
        function unique(values) {
            var out = [];
            values.forEach(function (v) {
                if (v && out.indexOf(v) === -1) {
                    out.push(v);
                }
            });
            return out;
        }

        // Intenta cada ruta en cadena y termina en avatar si fallan todas.
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
                // Pasa al siguiente candidato o aplica fallback final.
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

    // Carga XML y XSL en paralelo para reducir latencia inicial.
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
        applyPlayerImageFallback(renderTarget);

        // Cambio de temporada en caliente, limpiando id de jugador en URL.
        seasonForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var nextSeasonId = seasonSelect.value;

            renderWithXsl(xmlDoc, xslDoc, nextSeasonId);
            updateHeaderSeasonName(temporadas, nextSeasonId);
            applyPlayerImageFallback(renderTarget);

            var nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('temporada_id', nextSeasonId);
            nextUrl.searchParams.delete('id');
            window.history.replaceState({}, '', nextUrl.toString());
        });
    }).catch(function (err) {
        // Muestra mensaje de error cuando falla carga o transformacion.
        renderTarget.style.display = 'none';
        errorTarget.style.display = 'block';

        var message = 'No se pudo cargar el apartado de jugadores con XML/XSL.';
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

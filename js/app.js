(function () {
    'use strict';

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

    function getSelectedSeasonId(temporadas, temporadaSesion) {
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

    function fillSeasonSelect(seasonSelect, temporadas, selectedId) {
        if (!seasonSelect) {
            return;
        }

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

    function updateHeaderSeasonName(seasonName, temporadas, selectedId) {
        if (!seasonName) {
            return;
        }

        var found = temporadas.find(function (t) { return t.id === selectedId; });
        seasonName.textContent = found ? found.nombre : 'No disponible';
    }

    function unique(values) {
        var out = [];
        values.forEach(function (v) {
            if (v && out.indexOf(v) === -1) {
                out.push(v);
            }
        });
        return out;
    }

    function pad2(value) {
        var n = String(value || '').trim();
        if (n.length === 1) {
            return '0' + n;
        }
        return n;
    }

    function applyPlayerImageFallback(root) {
        if (!root) {
            return;
        }

        var images = root.querySelectorAll('img');
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

    function initNavToggle() {
        var btn = document.querySelector('.nav-toggle');
        var nav = document.getElementById('main-nav');
        if (!btn || !nav) {
            return;
        }

        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('nav-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    function initClasificacion() {
        var page = document.querySelector('main.page-home');
        if (!page) {
            return;
        }

        var xmlUrl = '../data/datos.xml';
        var xslUrl = '../data/xsl/clasificacion.xsl';
        var temporadaSesion = page.getAttribute('data-temporada-sesion') || '';

        var renderTarget = document.getElementById('clasificacion_render');
        var errorTarget = document.getElementById('clasificacion_error');
        var seasonName = document.getElementById('temporada_nombre');
        var seasonSelect = document.getElementById('temporada_id');
        var seasonForm = document.getElementById('season_form');

        function renderWithXsl(xmlDoc, xslDoc, temporadaId) {
            var processor = new window.XSLTProcessor();
            processor.importStylesheet(xslDoc);
            processor.setParameter(null, 'temporadaId', temporadaId);

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
            var selectedSeasonId = getSelectedSeasonId(temporadas, temporadaSesion);

            if (!selectedSeasonId) {
                throw new Error('No hay temporadas disponibles');
            }

            fillSeasonSelect(seasonSelect, temporadas, selectedSeasonId);
            updateHeaderSeasonName(seasonName, temporadas, selectedSeasonId);
            renderWithXsl(xmlDoc, xslDoc, selectedSeasonId);

            seasonForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var nextSeasonId = seasonSelect.value;
                renderWithXsl(xmlDoc, xslDoc, nextSeasonId);
                updateHeaderSeasonName(seasonName, temporadas, nextSeasonId);

                var nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('temporada_id', nextSeasonId);
                window.history.replaceState({}, '', nextUrl.toString());
            });
        }).catch(function (err) {
            renderTarget.style.display = 'none';
            errorTarget.style.display = 'block';

            var message = 'No se pudo cargar la clasificacion con XML/XSL.';
            if (err && err.message) {
                message += ' ' + err.message;
            }

            var p = errorTarget.querySelector('p');
            if (p) {
                p.textContent = message;
            }
        });
    }

    function initEquipo() {
        var page = document.querySelector('main.page-team');
        if (!page) {
            return;
        }

        var xmlUrl = '../data/datos.xml';
        var xslUrl = '../data/xsl/equipos.xsl';
        var temporadaSesion = page.getAttribute('data-temporada-sesion') || '';
        var equipoIdInicial = page.getAttribute('data-equipo-id') || '';

        var renderTarget = document.getElementById('equipos_render');
        var errorTarget = document.getElementById('equipos_error');
        var titleTarget = document.getElementById('equipos_titulo');
        var seasonName = document.getElementById('temporada_nombre');
        var seasonSelect = document.getElementById('temporada_id');
        var seasonForm = document.getElementById('season_form');

        function getSelectedTeamId() {
            var params = new URLSearchParams(window.location.search);
            var byQuery = params.get('id');
            if (byQuery) {
                return byQuery;
            }
            return equipoIdInicial;
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
            var selectedSeasonId = getSelectedSeasonId(temporadas, temporadaSesion);
            var selectedTeamId = getSelectedTeamId();

            if (!selectedSeasonId) {
                throw new Error('No hay temporadas disponibles');
            }

            fillSeasonSelect(seasonSelect, temporadas, selectedSeasonId);
            updateHeaderSeasonName(seasonName, temporadas, selectedSeasonId);
            updateTitle(selectedTeamId);
            renderWithXsl(xmlDoc, xslDoc, selectedSeasonId, selectedTeamId);

            seasonForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var nextSeasonId = seasonSelect.value;
                var currentTeamId = getSelectedTeamId();

                renderWithXsl(xmlDoc, xslDoc, nextSeasonId, currentTeamId);
                updateHeaderSeasonName(seasonName, temporadas, nextSeasonId);
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
    }

    function initJugador() {
        var page = document.querySelector('main.page-player-detail');
        if (!page) {
            return;
        }

        var xmlUrl = '../data/datos.xml';
        var xslUrl = '../data/xsl/jugadores.xsl';
        var temporadaSesion = page.getAttribute('data-temporada-sesion') || '';
        var jugadorIdInicial = page.getAttribute('data-jugador-id') || '';

        var renderTarget = document.getElementById('jugador_render');
        var errorTarget = document.getElementById('jugador_error');
        var seasonName = document.getElementById('temporada_nombre');
        var seasonSelect = document.getElementById('temporada_id');
        var seasonForm = document.getElementById('season_form');

        function getSelectedPlayerId() {
            var params = new URLSearchParams(window.location.search);
            return params.get('id') || jugadorIdInicial;
        }

        function renderWithXsl(xmlDoc, xslDoc, temporadaId, jugadorId) {
            var processor = new window.XSLTProcessor();
            processor.importStylesheet(xslDoc);
            processor.setParameter(null, 'temporadaId', temporadaId);
            processor.setParameter(null, 'jugadorId', jugadorId || '');

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
            var selectedSeasonId = getSelectedSeasonId(temporadas, temporadaSesion);
            var selectedPlayerId = getSelectedPlayerId();

            if (!selectedSeasonId) {
                throw new Error('No hay temporadas disponibles');
            }

            fillSeasonSelect(seasonSelect, temporadas, selectedSeasonId);
            updateHeaderSeasonName(seasonName, temporadas, selectedSeasonId);
            renderWithXsl(xmlDoc, xslDoc, selectedSeasonId, selectedPlayerId);
            applyPlayerImageFallback(renderTarget);

            seasonForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var nextSeasonId = seasonSelect.value;
                var currentPlayerId = getSelectedPlayerId();

                renderWithXsl(xmlDoc, xslDoc, nextSeasonId, currentPlayerId);
                updateHeaderSeasonName(seasonName, temporadas, nextSeasonId);
                applyPlayerImageFallback(renderTarget);

                var nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('temporada_id', nextSeasonId);
                if (currentPlayerId) {
                    nextUrl.searchParams.set('id', currentPlayerId);
                }
                window.history.replaceState({}, '', nextUrl.toString());
            });
        }).catch(function (err) {
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
    }

    function initJugadores() {
        var page = document.querySelector('main.page-players');
        if (!page) {
            return;
        }

        var xmlUrl = '../data/datos.xml';
        var xslUrl = '../data/xsl/jugadores.xsl';
        var temporadaSesion = page.getAttribute('data-temporada-sesion') || '';

        var renderTarget = document.getElementById('jugadores_render');
        var errorTarget = document.getElementById('jugadores_error');
        var seasonName = document.getElementById('temporada_nombre');
        var seasonSelect = document.getElementById('temporada_id');
        var seasonForm = document.getElementById('season_form');

        function renderWithXsl(xmlDoc, xslDoc, temporadaId) {
            var processor = new window.XSLTProcessor();
            processor.importStylesheet(xslDoc);
            processor.setParameter(null, 'temporadaId', temporadaId);
            processor.setParameter(null, 'jugadorId', '');

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
            var selectedSeasonId = getSelectedSeasonId(temporadas, temporadaSesion);

            if (!selectedSeasonId) {
                throw new Error('No hay temporadas disponibles');
            }

            fillSeasonSelect(seasonSelect, temporadas, selectedSeasonId);
            updateHeaderSeasonName(seasonName, temporadas, selectedSeasonId);
            renderWithXsl(xmlDoc, xslDoc, selectedSeasonId);
            applyPlayerImageFallback(renderTarget);

            seasonForm.addEventListener('submit', function (ev) {
                ev.preventDefault();
                var nextSeasonId = seasonSelect.value;

                renderWithXsl(xmlDoc, xslDoc, nextSeasonId);
                updateHeaderSeasonName(seasonName, temporadas, nextSeasonId);
                applyPlayerImageFallback(renderTarget);

                var nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('temporada_id', nextSeasonId);
                nextUrl.searchParams.delete('id');
                window.history.replaceState({}, '', nextUrl.toString());
            });
        }).catch(function (err) {
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
    }

    function initPartidos() {
        var page = document.querySelector('main.page-matches');
        if (!page) {
            return;
        }

        var xmlUrl = '../data/datos.xml';
        var xslUrl = '../data/xsl/partidos.xsl';
        var temporadaSesion = page.getAttribute('data-temporada-sesion') || '';

        var renderTarget = document.getElementById('partidos_render');
        var errorTarget = document.getElementById('partidos_error');
        var seasonName = document.getElementById('temporada_nombre');
        var jornadaName = document.getElementById('jornada_nombre');
        var seasonSelect = document.getElementById('temporada_id');
        var jornadaSelect = document.getElementById('jornada_id');
        var seasonForm = document.getElementById('season_form');
        var jornadaForm = document.getElementById('jornada_form');

        function getTemporadaNode(xmlDoc, temporadaId) {
            return xmlDoc.querySelector('liga > temporadas > temporada[id="' + temporadaId + '"]') ||
                Array.from(xmlDoc.querySelectorAll('liga > temporadas > temporada')).find(function (n) {
                    return (n.getAttribute('id') || '') === temporadaId;
                }) ||
                null;
        }

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

        function getSelectedJornadaNumero(jornadas) {
            var params = new URLSearchParams(window.location.search);
            var byQueryRaw = params.get('jornada_id');
            var byQuery = byQueryRaw ? parseInt(byQueryRaw, 10) : NaN;

            if (!Number.isNaN(byQuery) && jornadas.some(function (j) { return j.numero === byQuery; })) {
                return byQuery;
            }

            return jornadas.length > 0 ? jornadas[0].numero : 0;
        }

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

        function updateHeaderJornadaName(selectedNumero) {
            jornadaName.textContent = selectedNumero > 0 ? String(selectedNumero) : '-';
        }

        function renderWithXsl(xmlDoc, xslDoc, temporadaId, fechaSeleccionada) {
            var processor = new window.XSLTProcessor();
            processor.importStylesheet(xslDoc);
            processor.setParameter(null, 'temporadaId', temporadaId);
            processor.setParameter(null, 'fechaSeleccionada', fechaSeleccionada || '');

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
            var selectedSeasonId = getSelectedSeasonId(temporadas, temporadaSesion);

            if (!selectedSeasonId) {
                throw new Error('No hay temporadas disponibles');
            }

            var temporadaNode = getTemporadaNode(xmlDoc, selectedSeasonId);
            var jornadas = getJornadas(temporadaNode);
            var selectedJornadaNumero = getSelectedJornadaNumero(jornadas);
            var selectedJornada = jornadas.find(function (j) { return j.numero === selectedJornadaNumero; }) || null;

            fillSeasonSelect(seasonSelect, temporadas, selectedSeasonId);
            fillJornadaSelect(jornadas, selectedJornadaNumero);
            updateHeaderSeasonName(seasonName, temporadas, selectedSeasonId);
            updateHeaderJornadaName(selectedJornadaNumero);
            renderWithXsl(xmlDoc, xslDoc, selectedSeasonId, selectedJornada ? selectedJornada.fecha : '');

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
                updateHeaderSeasonName(seasonName, temporadas, nextSeasonId);
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

                updateHeaderSeasonName(seasonName, temporadas, currentSeasonId);
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
    }

    initNavToggle();
    initClasificacion();
    initEquipo();
    initJugador();
    initJugadores();
    initPartidos();
})();

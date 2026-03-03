// ==================== CARRUSEL DE NOTICIAS ====================
// Controla el slider de noticias de la pagina home
function initCarousel() {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');
    if (slides.length && prevBtn && nextBtn) {
        function showSlide(n) {
            slides.forEach((slide, i) => {
                slide.style.display = (i === n) ? 'block' : 'none';
            });
        }
        // Navegacion circular: al llegar al final vuelve al principio
        function nextSlide() {
            slideIndex = (slideIndex + 1) % slides.length;
            showSlide(slideIndex);
        }
        // Lo mismo pero hacia atras (el + slides.length evita numeros negativos)
        function prevSlide() {
            slideIndex = (slideIndex - 1 + slides.length) % slides.length;
            showSlide(slideIndex);
        }
        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);
        showSlide(slideIndex);
    }
}

// Reinicializar el carrusel cuando se navega a home (el contenido se carga dinamicamente)
$(document).on('click', '.nav-link[data-page="home"]', function() {
    setTimeout(function() {
        initCarousel();
    }, 100); // Esperamos un poco porque el HTML aun no esta en el DOM
});

// Inicializar carrusel en la carga inicial
$(document).ready(function() {
    setTimeout(function() {
        initCarousel();
    }, 100);
});

// ==================== INICIALIZACION Y NAVEGACION ====================
$(document).ready(function() {
    
    // ==================== INICIALIZACIÓN DE AUTENTICACIÓN ====================
    // Verificar si hay usuario autenticado
    if (authSystem.isAuthenticated()) {
        updateUserSessionDisplay();
        loadPage('home');
    } else {
        // Mostrar modal de login si no hay sesión
        $('#login-modal').css('display', 'block');
    }
    
    /* 
       Delegacion de eventos con $(document).on()
       Esto es importante porque los elementos .nav-link pueden no existir todavia
       cuando se ejecuta este codigo (se cargan dinamicamente)
    */
    $(document).on('click', '.nav-link', function(e) {
        e.preventDefault();
        
        var pageName = $(this).data('page');
        
        // Actualizar estado visual del menu
        $('#main-nav .nav-link').removeClass('active');
        
        if ($(this).closest('#main-nav').length) {
            $(this).addClass('active');
        }
        
        // Cerrar menu movil si esta abierto
        $('#main-nav').removeClass('active');
        $('#mobile-menu-toggle').removeClass('active');
        
        loadPage(pageName);
    });
    
    // Enlaces del footer - misma logica pero hacemos scroll arriba
    $(document).on('click', '.footer-link', function(e) {
        e.preventDefault();
        
        var pageName = $(this).data('page');
        
        $('#main-nav .nav-link').removeClass('active');
        $('#main-nav .nav-link[data-page="' + pageName + '"]').addClass('active');
        
        loadPage(pageName);
        $('html, body').animate({ scrollTop: 0 }, 500);
    });
    
    // Menu hamburguesa - toggle de clases para mostrar/ocultar
    $('#mobile-menu-toggle').on('click', function() {
        $('#main-nav').toggleClass('active');
        $(this).toggleClass('active');
    });
    
    // Cerrar menu movil al hacer clic fuera del header
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#main-header').length) {
            $('#main-nav').removeClass('active');
            $('#mobile-menu-toggle').removeClass('active');
        }
    });

    // ==================== MANEJADORES DE LOGIN ====================
    
    // Manejar envío del formulario de login
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        
        const username = $('#username').val();
        const password = $('#password').val();
        
        const result = authSystem.login(username, password);
        
        if (result.success) {
            $('#login-modal').fadeOut(300, function() {
                updateUserSessionDisplay();
                loadPage('home');
            });
            $('#login-form')[0].reset();
        } else {
            alert('Error: ' + result.message);
            $('#password').val('').focus();
        }
    });
    
    // Manejar login como invitado
    $('#btn-guest').on('click', function() {
        const result = authSystem.loginAsGuest();
        
        if (result.success) {
            $('#login-modal').fadeOut(300, function() {
                updateUserSessionDisplay();
                loadPage('home');
            });
        }
    });
    
    // Manejar cierre del modal de login (X button)
    $('#close-login').on('click', function() {
        // Si el usuario no está autenticado, no permitir cerrar el modal
        if (!authSystem.isAuthenticated()) {
            alert('Debes iniciar sesión o continuar como invitado para acceder');
        }
    });
    
    // Manejar botón de logout
    $('#logout-btn').on('click', function() {
        if (confirm('¿Seguro que deseas cerrar sesión?')) {
            authSystem.logout();
            $('#login-modal').fadeIn(300);
            updateUserSessionDisplay();
        }
    });
    
    // Manejar botón de login desde la cabecera
    $('#login-btn-header').on('click', function() {
        $('#login-modal').fadeIn(300);
        $('#username').focus();
    });
});

// ==================== FUNCIONES DE INICIALIZACION PARA NUEVAS PAGINAS ====================

// Inicializar filtros de noticias
function initNewsFilters() {
    // Los filtros ya est�n manejados por delegaci�n de eventos
    console.log('P�gina de noticias cargada');
}

// Inicializar tabs y acordeones de normativa
function initNormativaTabs() {
    // Los tabs y acordeones ya est�n manejados por delegaci�n de eventos
    console.log('P�gina de normativa cargada');
}

// ==================== CARGA DINAMICA DE PAGINAS ====================
/*
   Esta es la funcion principal del sitio. Carga el contenido HTML de cada seccion
   sin recargar toda la pagina (comportamiento SPA).
   Despues de cargar el HTML, ejecuta la funcion correspondiente para cargar
   los datos desde XML y transformarlos con XSLT.
*/
function loadPage(pageName) {
    $('html, body').animate({ scrollTop: 0 }, 300);
    
    // Feedback visual mientras carga
    $('#content-area').html('<div class="loading">Cargando contenido...</div>');
    
    var pageUrl = 'pages/' + pageName + '.html';
    
    $.ajax({
        url: pageUrl,
        method: 'GET',
        dataType: 'html',
        success: function(data) {
            $('#content-area').html(data);
            $('html, body').animate({ scrollTop: 0 }, 300);
            
            // Segun la pagina cargada, llamamos a la funcion que carga los datos XML
            switch(pageName) {
                case 'equipos':
                    loadEquipos();
                    break;
                case 'jugadores':
                    loadJugadores();
                    break;
                case 'partidos':
                    loadPartidos();
                    break;
                case 'clasificacion':
                    initClasificacionFilter();
                    loadClasificacion();
                    break;
                case 'temporadas':
                    loadTemporadas();
                    break;
                case 'noticias':
                    // Inicializar filtros de noticias
                    initNewsFilters();
                    break;
                case 'normativa':
                    // Inicializar tabs y acordeones de normativa
                    initNormativaTabs();
                    break;
                // home no necesita cargar datos XML
            }
        },
        error: function() {
            $('#content-area').html(
                '<div class="error-message">' +
                '<h2>Error al cargar el contenido</h2>' +
                '<p>No se pudo cargar la p�gina solicitada. Por favor, intenta de nuevo.</p>' +
                '</div>'
            );
        }
    });
}

// ==================== FUNCIONES DE CARGA DE DATOS ====================
/*
   Todas estas funciones siguen el mismo patron:
   1. Cargar el XML con los datos (futsal.xml)
   2. Cargar el XSLT correspondiente (equipos.xsl, jugadores.xsl, etc.)
   3. Transformar XML a HTML usando la funcion transformXML()
   4. Inyectar el HTML resultante en el contenedor de la pagina
*/

function loadEquipos() {
    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            $.ajax({
                url: 'xslt/equipos.xsl?v=' + new Date().getTime(),
                method: 'GET',
                dataType: 'xml',
                cache: false,
                success: function(xslData) {
                    var resultDocument = transformXML(xmlData, xslData);
                    $('#equipos-container').html(resultDocument);
                    // Despues de cargar, a�adimos los eventos a los botones
                    attachTeamEvents();
                }
            });
        },
        error: function() {
            $('#equipos-container').html('<p>Error al cargar los equipos.</p>');
        }
    });
}

// equipoId es opcional - si se pasa, filtra jugadores de ese equipo
function loadJugadores(equipoId) {
    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            $.ajax({
                url: 'xslt/jugadores.xsl?v=' + new Date().getTime(),
                method: 'GET',
                dataType: 'xml',
                cache: false,
                success: function(xslData) {
                    // El tercer parametro pasa el equipoId al XSLT
                    var resultDocument = transformXML(xmlData, xslData, equipoId);
                    $('#jugadores-container').html(resultDocument);
                }
            });
        },
        error: function() {
            $('#jugadores-container').html('<p>Error al cargar los jugadores.</p>');
        }
    });
}

function loadPartidos() {
    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            $.ajax({
                url: 'xslt/partidos.xsl?v=' + new Date().getTime(),
                method: 'GET',
                dataType: 'xml',
                cache: false,
                success: function(xslData) {
                    var resultDocument = transformXML(xmlData, xslData);
                    $('#partidos-container').html(resultDocument);
                }
            });
        },
        error: function() {
            $('#partidos-container').html('<p>Error al cargar los partidos.</p>');
        }
    });
}

function loadClasificacion(temporadaId) {
    var selectedTemporada = temporadaId || $('#temporada-select').val() || '1';
    $('#clasificacion-container').html('<div class="loading">Cargando clasificaci�n...</div>');

    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            $.ajax({
                url: 'xslt/clasificacion.xsl?v=' + new Date().getTime(),
                method: 'GET',
                dataType: 'xml',
                cache: false,
                success: function(xslData) {
                    var resultDocument = transformXML(xmlData, xslData, { temporadaId: selectedTemporada });
                    $('#clasificacion-container').html(resultDocument);
                    $('#temporada-select').val(selectedTemporada);
                }
            });
        },
        error: function() {
            $('#clasificacion-container').html('<p>Error al cargar la clasificacion.</p>');
        }
    });
}

function loadTemporadas() {
    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            $.ajax({
                url: 'xslt/temporadas.xsl?v=' + new Date().getTime(),
                method: 'GET',
                dataType: 'xml',
                cache: false,
                success: function(xslData) {
                    var resultDocument = transformXML(xmlData, xslData);
                    $('#temporadas-container').html(resultDocument);
                    attachSeasonEvents();
                }
            });
        },
        error: function() {
            $('#temporadas-container').html('<p>Error al cargar las temporadas.</p>');
        }
    });
}
   


// ==================== TRANSFORMACION XSLT ====================
/*
   Esta es la funcion mas importante del archivo.
   Se encarga de convertir el XML en HTML usando una hoja XSLT.
   Tiene dos formas de hacerlo:
   - XSLTProcessor: para navegadores modernos (Chrome, Firefox, Edge)
   - transformNode: fallback para Internet Explorer (por si acaso)
*/
function transformXML(xml, xsl, params) {
    // Navegadores modernos - la forma estandar
    if (window.XSLTProcessor) {
        var xsltProcessor = new XSLTProcessor();
        xsltProcessor.importStylesheet(xsl);
        
        // Admitimos un solo valor (equipoId) o un objeto de parametros
        if (params) {
            if (typeof params === 'object') {
                Object.keys(params).forEach(function(key) {
                    var value = params[key];
                    if (value !== undefined && value !== null && value !== '') {
                        xsltProcessor.setParameter(null, key, value);
                    }
                });
            } else {
                xsltProcessor.setParameter(null, 'equipoId', params);
            }
        }
        
        var resultDocument = xsltProcessor.transformToFragment(xml, document);
        
        // Necesitamos extraer el HTML como string para poder insertarlo
        var tempDiv = document.createElement('div');
        tempDiv.appendChild(resultDocument);
        return tempDiv.innerHTML;
    }
    // Internet Explorer usa su propio metodo
    else if (typeof xml.transformNode !== 'undefined') {
        return xml.transformNode(xsl);
    }
    
    return '<p>Tu navegador no soporta transformaciones XSLT.</p>';
}

// ==================== EVENTOS DE CLASIFICACION ====================

function initClasificacionFilter() {
    $(document).off('change', '#temporada-select').on('change', function() {
        var temporadaId = $(this).val();
        loadClasificacion(temporadaId);
    });
}

// ==================== EVENTOS DE EQUIPOS ====================

// Vincula el boton "Ver Jugadores" de cada tarjeta de equipo
function attachTeamEvents() {
    $('.ver-jugadores-btn').on('click', function() {
        var equipoId = $(this).data('equipo-id');
        mostrarJugadoresEquipo(equipoId);
    });
}

/*
   Cuando el usuario pulsa "Ver Jugadores" en un equipo,
   esta funcion carga la pagina de jugadores y filtra
   solo los jugadores de ese equipo.
   
   Es un poco complejo porque tiene que:
   1. Cargar el XML con todos los datos
   2. Cargar la pagina HTML de jugadores
   3. Aplicar el XSLT con el filtro del equipo
*/
function mostrarJugadoresEquipo(equipoId) {
    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            $.ajax({
                url: 'pages/jugadores.html',
                method: 'GET',
                dataType: 'html',
                success: function(data) {
                    $('#content-area').html(data);
                    
                    // Ahora cargamos los jugadores filtrados por equipo
                    $.ajax({
                        url: 'xslt/jugadores.xsl?v=' + new Date().getTime(),
                        method: 'GET',
                        dataType: 'xml',
                        cache: false,
                        success: function(xslData) {
                            var resultDocument = transformXML(xmlData, xslData, equipoId);
                            $('#jugadores-container').html(resultDocument);
                        }
                    });
                }
            });
            
            // Actualizamos la navegacion para marcar la seccion correcta
            $('.nav-link').removeClass('active');
            $('.nav-link[data-page="jugadores"]').addClass('active');
        }
    });
}

// ==================== EVENTOS DE TEMPORADAS ====================

// Vincula los botones "Ver Detalles" de cada temporada
// Usa off('click') para evitar duplicar eventos si se recarga
function attachSeasonEvents() {
    $('.btn-detalles').off('click').on('click', function() {
        var temporadaId = $(this).data('temporada-id');
        var $card = $(this).closest('.season-card');

        // Buscamos si ya existe el contenedor de detalles
        var $details = $card.find('.season-details');
        if ($details.length === 0) {
            $details = $('<div class="season-details"></div>');
            $card.append($details);
        }

        // Si ya esta visible, lo ocultamos (comportamiento toggle)
        if ($details.is(':visible')) {
            $details.slideUp();
            return;
        }

        $details.html('<div class="loading">Cargando detalles...</div>');
        mostrarDetallesTemporada(temporadaId, $details);
    });
}

/*
   Carga los detalles completos de una temporada y los muestra
   en el contenedor que le pasemos.
   
   Busca en el XML por el ID de la temporada y extrae:
   - Nombre, ano, estado
   - Lista de equipos participantes (traduciendo IDs a nombres)
   - Numero de jornadas
*/
function mostrarDetallesTemporada(temporadaId, $container) {
    $.ajax({
        url: 'futsal_data.xml?v=' + new Date().getTime(),
        method: 'GET',
        dataType: 'xml',
        cache: false,
        success: function(xmlData) {
            // Buscamos la temporada que coincida con el ID
            var $temporada = $(xmlData).find('Temporada').filter(function() {
                return $(this).find('ID').text() === String(temporadaId);
            }).first();

            if ($temporada.length === 0) {
                $container.html('<p>No se encontro la temporada.</p>');
                return;
            }

            // Extraemos toda la info de la temporada
            var nombre = $temporada.find('Nombre').text();
            var ano = $temporada.find('Ano').text();
            var estado = $temporada.find('Estado').text();
            var equipoIds = $temporada.find('EquipoIds').text();
            var jornadaIds = $temporada.find('JornadaIds').text();

            // Construimos el HTML de los detalles
            var html = '<div class="season-details-content">';
            html += '<p><strong>Nombre:</strong> ' + nombre + '</p>';
            html += '<p><strong>Ano:</strong> ' + ano + '</p>';
            html += '<p><strong>Estado:</strong> ' + estado + '</p>';

            // Si hay equipos, los listamos buscando sus nombres
            if (equipoIds && equipoIds.trim() !== '') {
                var ids = equipoIds.split(',');
                html += '<p><strong>Equipos:</strong></p><ul>';
                ids.forEach(function(id) {
                    // Traducimos el ID del equipo a su nombre
                    var equipo = $(xmlData).find('Equipo').filter(function() {
                        return $(this).find('ID').text() === id.trim();
                    }).first();
                    var nombreEquipo = equipo.length ? equipo.find('Nombre').text() : ('ID ' + id.trim());
                    html += '<li>' + nombreEquipo + '</li>';
                });
                html += '</ul>';
            } else {
                html += '<p><strong>Equipos:</strong> Ninguno.</p>';
            }

            // Contamos las jornadas
            if (jornadaIds && jornadaIds.trim() !== '') {
                var jids = jornadaIds.split(',');
                html += '<p><strong>Jornadas:</strong> ' + jids.length + '</p>';
            } else {
                // Si no hay lista de IDs, contamos las jornadas que pertenecen a esta temporada
                var jornadasCount = $(xmlData).find('Jornada').filter(function() {
                    return $(this).find('TemporadaID').text() === String(temporadaId);
                }).length;
                html += '<p><strong>Jornadas:</strong> ' + jornadasCount + '</p>';
            }

            html += '</div>';

            // Mostramos con animacion
            $container.hide().html(html).slideDown();
        },
        error: function() {
            $container.html('<p>Error al cargar los detalles de la temporada.</p>');
        }
    });
}

// ==================== BOTON "VOLVER ARRIBA" ====================
// Boton flotante que aparece cuando el usuario hace scroll
// y permite volver al inicio de la pagina con un click

$(document).ready(function() {
    var backToTopBtn = $("#back-to-top");

    // El boton aparece cuando bajas mas de 300px
    $(window).on("scroll", function() {
        if ($(window).scrollTop() > 300) {
            backToTopBtn.addClass("show");
        } else {
            backToTopBtn.removeClass("show");
        }
    });

    // Al hacer click, subimos suavemente (500ms de animacion)
    backToTopBtn.on("click", function(e) {
        e.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, 500);
    });
});

// ==================== SISTEMA DE B�SQUEDA ====================
$(document).ready(function() {
    var searchModal = $('#search-modal');
    var searchResults = $('#search-results');
    
    // Base de b�squeda: secciones est�ticas
    var searchableContent = [
        { title: 'Clasificaci�n', type: 'Secci�n', page: 'clasificacion' },
        { title: 'Partidos', type: 'Secci�n', page: 'partidos' },
        { title: 'Jugadores', type: 'Secci�n', page: 'jugadores' },
        { title: 'Equipos', type: 'Secci�n', page: 'equipos' },
        { title: 'Noticias', type: 'Secci�n', page: 'noticias' },
        { title: 'Normativa', type: 'Secci�n', page: 'normativa' }
    ];

    // Cargar jugadores y equipos desde el XML para buscarlos
    function loadSearchData() {
        $.ajax({
            url: 'futsal_data.xml?v=' + new Date().getTime(),
            method: 'GET',
            dataType: 'xml',
            cache: false,
            success: function(xml) {
                var $xml = $(xml);
                var equipos = $xml.find('Futsal > Equipos > Equipo');
                var jugadores = $xml.find('Futsal > Jugadores > Jugador');

                equipos.each(function() {
                    var nombre = $(this).find('Nombre').first().text();
                    if (nombre) {
                        searchableContent.push({ title: nombre, type: 'Equipo', page: 'equipos' });
                    }
                });

                jugadores.each(function() {
                    var nombre = $(this).find('Nombre').first().text();
                    if (nombre) {
                        searchableContent.push({ title: nombre, type: 'Jugador', page: 'jugadores' });
                    }
                });
            }
        });
    }

    loadSearchData();
    
    function performSearch(query) {
        if (!query || query.length < 2) {
            return [];
        }
        
        query = query.toLowerCase();
        return searchableContent.filter(function(item) {
            return item.title.toLowerCase().includes(query);
        });
    }
    
    function displayResults(results, query) {
        if (results.length === 0) {
            searchResults.html('<p class="no-results">No se encontraron resultados para "' + query + '"</p>');
        } else {
            var html = '';
            results.forEach(function(result) {
                html += '<div class="search-result-item" data-page="' + result.page + '">';
                html += '<div class="search-result-title">' + result.title + '</div>';
                html += '<div class="search-result-type">' + result.type + '</div>';
                html += '</div>';
            });
            searchResults.html(html);
        }
        searchModal.addClass('active');
    }
    
    // Buscar al hacer clic en el bot�n
    $('#search-btn').on('click', function() {
        var query = $('#search-input').val().trim();
        if (query.length >= 2) {
            var results = performSearch(query);
            displayResults(results, query);
        }
    });
    
    // Buscar al presionar Enter
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) {
            var query = $(this).val().trim();
            if (query.length >= 2) {
                var results = performSearch(query);
                displayResults(results, query);
            }
        }
    });
    
    // Navegar al resultado al hacer clic
    $(document).on('click', '.search-result-item', function() {
        var page = $(this).data('page');
        searchModal.removeClass('active');
        $('#search-input').val('');
        loadPage(page);
    });

    // Cerrar modal con el bot�n X
    $(document).on('click', '.close-modal', function() {
        searchModal.removeClass('active');
        $('#search-input').val('');
    });

    // Cerrar modal al hacer clic fuera del contenido
    $(document).on('click', '#search-modal', function(e) {
        if (e.target.id === 'search-modal') {
            searchModal.removeClass('active');
            $('#search-input').val('');
        }
    });

    // Cerrar modal con la tecla ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && searchModal.hasClass('active')) {
            searchModal.removeClass('active');
            $('#search-input').val('');
        }
    });
});

// ==================== P�GINA DE NOTICIAS - FILTROS ====================
$(document).on('click', '.filter-btn', function() {
    var filter = $(this).data('filter');
    
    // Actualizar bot�n activo
    $('.filter-btn').removeClass('active');
    $(this).addClass('active');
    
    // Filtrar noticias
    if (filter === 'all') {
        $('.news-card').show();
    } else {
        $('.news-card').hide();
        $('.news-card[data-category="' + filter + '"]').show();
    }
});

// ==================== P�GINA DE NORMATIVA - TABS Y ACORDEONES ====================
// Tabs de normativa
$(document).on('click', '.tab-btn', function() {
    var tabId = $(this).data('tab');
    
    // Actualizar bot�n activo
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    
    // Mostrar contenido del tab
    $('.tab-content').removeClass('active');
    $('#tab-' + tabId).addClass('active');
});

// Acordeones
$(document).on('click', '.accordion-header', function() {
    var item = $(this).closest('.accordion-item');
    item.toggleClass('active');
});

// FAQ
$(document).on('click', '.faq-question', function() {
    var item = $(this).closest('.faq-item');
    item.toggleClass('active');
});

// Newsletter (simulado)
$(document).on('submit', '#newsletter-form', function(e) {
    e.preventDefault();
    alert('�Gracias por suscribirte! Pronto recibir�s nuestras noticias.');
    $(this).find('input').val('');
});
// ==================== ACTUALIZAR INFORMACIÓN DE SESIÓN EN LA CABECERA ====================
/**
 * Actualiza la información del usuario en la cabecera
 * Muestra desplegable con nombre, rol y temporada actual si hay sesión
 * Si no hay sesión, muestra el botón de login
 */
function updateUserSessionDisplay() {
    const userDropdown = $('#user-dropdown');
    const loginBtnHeader = $('#login-btn-header');
    
    if (authSystem.isAuthenticated()) {
        const info = authSystem.getSessionInfo();
        
        if (info) {
            $('#user-name-header').text(info.fullName);
            $('#user-name-dropdown').text(info.fullName);
            $('#user-role-dropdown').text(info.role);
            $('#current-season-dropdown').text('📅 ' + info.season);
            
            userDropdown.show();
            loginBtnHeader.hide();
        }
    } else {
        userDropdown.hide();
        loginBtnHeader.show();
    }
}

// Manejar apertura/cierre del dropdown de usuario
$(document).on('click', '#user-dropdown-toggle', function(e) {
    e.stopPropagation();
    $(this).toggleClass('active');
});

// Cerrar dropdown al hacer clic en cualquier parte de la página
$(document).on('click', function(e) {
    if (!$(e.target).closest('#user-dropdown').length) {
        $('#user-dropdown-toggle').removeClass('active');
    }
});

// Manejar opción de cambiar usuario
$(document).on('click', '#change-user-btn', function() {
    authSystem.logout();
    $('#user-dropdown-toggle').removeClass('active');
    $('#login-modal').fadeIn(300);
    updateUserSessionDisplay();
    $('#username').focus();
});

// Manejar botón de logout en el dropdown
$(document).on('click', '#logout-btn', function() {
    if (confirm('¿Seguro que deseas cerrar sesión?')) {
        authSystem.logout();
        $('#user-dropdown-toggle').removeClass('active');
        $('#login-modal').fadeIn(300);
        updateUserSessionDisplay();
    }
});

// Manejar botón de login desde la cabecera
$(document).on('click', '#login-btn-header', function() {
    $('#login-modal').fadeIn(300);
    $('#username').focus();
});

// Actualizar información de sesión cada vez que se cargue una página
$(document).ready(function() {
    setInterval(function() {
        updateUserSessionDisplay();
    }, 1000);
});
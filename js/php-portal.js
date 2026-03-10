(function () {
    // Referencias a los formularios que validamos antes de enviar al servidor.
    var loginForm = document.getElementById('login-form');
    var seasonForm = document.getElementById('season-form');
    var searchForm = document.getElementById('search-form');

    // Validacion del login: evita enviar usuario/clave demasiado cortos.
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            var username = document.getElementById('username');
            var password = document.getElementById('password');

            // Si no existen los campos, no forzamos validacion adicional.
            if (!username || !password) {
                return;
            }

            var userOk = username.value.trim().length >= 3;
            var passOk = password.value.trim().length >= 3;

            // Bloquea envio y muestra feedback inmediato al usuario.
            if (!userOk || !passOk) {
                event.preventDefault();
                alert('Usuario y contraseña deben tener al menos 3 caracteres.');
            }
        });
    }

    // Validacion del selector de temporada (debe ser un ID numerico).
    if (seasonForm) {
        seasonForm.addEventListener('submit', function (event) {
            var season = document.getElementById('season_id');
            if (!season || season.value === '' || !/^\d+$/.test(season.value)) {
                event.preventDefault();
                alert('Selecciona una temporada valida.');
            }
        });
    }

    // Validacion del buscador: se exige longitud minima para mejorar resultados.
    if (searchForm) {
        searchForm.addEventListener('submit', function (event) {
            var input = document.getElementById('q');
            if (!input || input.value.trim().length < 2) {
                event.preventDefault();
                alert('Introduce al menos 2 caracteres para buscar.');
            }
        });
    }
})();

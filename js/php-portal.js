(function () {
    var loginForm = document.getElementById('login-form');
    var seasonForm = document.getElementById('season-form');
    var searchForm = document.getElementById('search-form');

    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            var username = document.getElementById('username');
            var password = document.getElementById('password');

            if (!username || !password) {
                return;
            }

            var userOk = username.value.trim().length >= 3;
            var passOk = password.value.trim().length >= 3;

            if (!userOk || !passOk) {
                event.preventDefault();
                alert('Usuario y contraseña deben tener al menos 3 caracteres.');
            }
        });
    }

    if (seasonForm) {
        seasonForm.addEventListener('submit', function (event) {
            var season = document.getElementById('season_id');
            if (!season || season.value === '' || !/^\d+$/.test(season.value)) {
                event.preventDefault();
                alert('Selecciona una temporada valida.');
            }
        });
    }

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

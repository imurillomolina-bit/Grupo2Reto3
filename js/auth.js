// ==================== SISTEMA DE AUTENTICACIÓN Y SESIONES ====================
// Gestiona Login, Sesiones, Roles de Usuario y Temporadas

class AuthSystem {
    constructor() {
        this.users = [
            { id: 1, username: 'admin', password: 'admin123', role: 'Administrador', fullName: 'Admin Usuario' },
            { id: 2, username: 'editor', password: 'editor123', role: 'Editor', fullName: 'Editor Usuario' },
            { id: 3, username: 'viewer', password: 'viewer123', role: 'Observador', fullName: 'Observador Usuario' }
        ];
        this.currentUser = null;
        this.currentSeason = null;
        this.sessionTimeout = 30 * 60 * 1000; // 30 minutos
        this.loadSession();
        this.loadSeasons();
    }

    /**
     * Autentica un usuario con usuario y contraseña
     */
    login(username, password) {
        const user = this.users.find(u => u.username === username && u.password === password);
        if (user) {
            this.currentUser = {
                id: user.id,
                username: user.username,
                role: user.role,
                fullName: user.fullName,
                loginTime: new Date()
            };
            this.saveSession();
            return { success: true, user: this.currentUser };
        }
        return { success: false, message: 'Usuario o contraseña incorrectos' };
    }

    /**
     * Inicia sesión como invitado
     */
    loginAsGuest() {
        this.currentUser = {
            id: 0,
            username: 'invitado',
            role: 'Invitado',
            fullName: 'Usuario Invitado',
            isGuest: true,
            loginTime: new Date()
        };
        this.saveSession();
        return { success: true, user: this.currentUser };
    }

    /**
     * Cierra la sesión actual
     */
    logout() {
        this.currentUser = null;
        sessionStorage.removeItem('user_session');
        sessionStorage.removeItem('current_season');
        return { success: true, message: 'Sesión cerrada correctamente' };
    }

    /**
     * Obtiene el usuario actual
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Verifica si hay un usuario autenticado
     */
    isAuthenticated() {
        return this.currentUser !== null;
    }

    /**
     * Verifica si el usuario es administrador
     */
    isAdmin() {
        return this.currentUser && this.currentUser.role === 'Administrador';
    }

    /**
     * Verifica si el usuario es invitado
     */
    isGuest() {
        return this.currentUser && this.currentUser.isGuest === true;
    }

    /**
     * Guarda la sesión en sessionStorage
     */
    saveSession() {
        if (this.currentUser) {
            sessionStorage.setItem('user_session', JSON.stringify(this.currentUser));
        }
    }

    /**
     * Carga la sesión desde sessionStorage
     */
    loadSession() {
        const session = sessionStorage.getItem('user_session');
        if (session) {
            try {
                this.currentUser = JSON.parse(session);
            } catch (e) {
                console.error('Error al cargar sesión:', e);
                this.currentUser = null;
            }
        }
    }

    /**
     * Carga las temporadas desde el XML
     */
    loadSeasons() {
        fetch('./data/futsal.xml')
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(data, 'text/xml');
                const temporadas = xmlDoc.getElementsByTagName('Temporada');
                
                const seasons = [];
                for (let i = 0; i < temporadas.length; i++) {
                    const temp = temporadas[i];
                    const nombre = temp.getElementsByTagName('Nombre')[0]?.textContent || '';
                    const ano = parseInt(temp.getElementsByTagName('Ano')[0]?.textContent || '0');
                    const estado = temp.getElementsByTagName('Estado')[0]?.textContent || '';
                    const id = parseInt(temp.getElementsByTagName('ID')[0]?.textContent || '0');

                    seasons.push({ id, nombre, ano, estado });
                }

                // Ordena por año descendente y selecciona la más reciente
                seasons.sort((a, b) => b.ano - a.ano);
                this.seasons = seasons;
                
                // Establece la temporada actual como la más reciente con estado 'abierta'
                const openSeason = seasons.find(s => s.estado === 'abierta');
                if (openSeason) {
                    this.setCurrentSeason(openSeason);
                } else if (seasons.length > 0) {
                    this.setCurrentSeason(seasons[0]);
                }
            })
            .catch(error => console.error('Error cargando temporadas:', error));
    }

    /**
     * Establece la temporada actual
     */
    setCurrentSeason(season) {
        this.currentSeason = season;
        sessionStorage.setItem('current_season', JSON.stringify(season));
    }

    /**
     * Obtiene la temporada actual
     */
    getCurrentSeason() {
        return this.currentSeason;
    }

    /**
     * Obtiene todas las temporadas
     */
    getAllSeasons() {
        return this.seasons || [];
    }

    /**
     * Obtiene información de la sesión formateada para mostrar
     */
    getSessionInfo() {
        if (!this.currentUser) {
            return null;
        }

        let userRole = this.currentUser.role;
        // Traducción de roles
        const roleTranslations = {
            'Administrador': 'Administrador',
            'Editor': 'Editor',
            'Observador': 'Observador',
            'Invitado': 'Invitado'
        };

        return {
            fullName: this.currentUser.fullName,
            role: roleTranslations[userRole] || userRole,
            season: this.currentSeason ? this.currentSeason.nombre : 'No disponible',
            isGuest: this.currentUser.isGuest || false
        };
    }
}

// Crear instancia global
const authSystem = new AuthSystem();

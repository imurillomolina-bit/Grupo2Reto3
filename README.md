# Documentación

## Objetivo

Es una aplicación web desarrollada en PHP orientada a la consulta de información deportiva (temporadas, clasificación, partidos, equipos, jugadores y noticias).
La aplicación se apoya en un fichero XML (data/datos.xml) como fuente principal de datos, validado mediante un XSD (data/datos.xsd). Algunas secciones se renderizan mediante transformaciones XSLT ubicadas en data/xsl/.

## Usuarios y Roles

La web contempla tres perfiles:
- **Invitado**: acceso de lectura a la web pública.
- **Usuarios (Manager, Árbitro)**: acceso de lectura con sesión iniciada, dependiendo el usuario le llevará a una página distinta al iniciar sesión (mismas páginas públicas, pero con sesión y datos de usuario).
- **Admin**: además, puede acceder a Usuarios (listado y control básico) mediante restricción por rol.

La sesión se gestiona con `$_SESSION` y se muestra en el encabezado (usuario actual, rol y temporada activa).

## Estructura de carpetas

```
index.php
```
Punto de entrada en raíz (redirige o actúa como acceso inicial del proyecto).

```
php/
```
Páginas y endpoints principales:
- `inicio.php`, `clasificacion.php`, `partidos.php`, `jugadores.php`, `jugador.php`, `equipo.php`, `noticias.php`, `normativa.php`.
- autenticación: `login.php`, `logout.php`, `entrar_invitado.php`.
- administración: `usuarios.php` (solo Admin)
- panel operativo: `panel.php` (Manager y Admin)
- controladores/servicios: `set_temporada.php`, `cargar_contenido.php`.
- recurso dinámico: `avatar.php` (SVG)

```
includes/
```
Código compartido:
- `app_init.php` (inicialización)
- `header.php` / `footer.php` (layout común)
- `auth.php` (autenticación/roles)
- `xml.php` (carga/validación y funciones de negocio sobre XML)
- `app_context.php` (contexto/estado de aplicación)

```
data/
```
- `datos.xml` (datos)
- `datos.xsd` (esquema)
- `login_events.json` (registro de eventos relacionados con autenticación)
- `xsl/` (transformaciones XSLT)

```
css/, js/, img/
```
Recursos estáticos (estilos, scripts e imágenes).

## Funcionalidad por secciones

### Páginas principales (php/)

- **Inicio** (`php/inicio.php`): página principal con contenido de temporada (resúmenes, accesos y contenido destacado).
- **Clasificación** (`php/clasificacion.php`): muestra la tabla de clasificación filtrada por temporada (transformación XSLT en cliente).
- **Partidos** (`php/partidos.php`): filtrado por temporada y jornada/fecha (XSLT y parámetros).
- **Equipo** (`equipo.php`): información de un equipo (XSLT y parámetro equipoId).
- **Jugadores** (`jugadores.php`): listado de jugadores filtrable (XSLT).
- **Jugador** (`jugador.php`): ficha individual del jugador (XSLT y parámetro jugadorId).
- **Noticias** (`noticias.php`): noticias por cada temporada (renderizado desde PHP).
- **Normativa** (`normativa.php`): contenido informativo estructurado en secciones.
- **Login/Logout** (`login.php`, `logout.php`): inicio/cierre de sesión.
- **Invitado** (`entrar_invitado.php`): crea sesión invitado.
- **Usuarios** (`usuarios.php`): solo el Admin tiene este apartado, control de permisos por rol.
- **Panel** (`panel.php`): panel operativo accesible únicamente para los roles Manager y Admin. Muestra una vista consolidada de la temporada activa con KPIs (número de equipos, jugadores, partidos disputados, goles totales y media de goles por partido), el top 3 de la clasificación, los últimos resultados y accesos rápidos a las secciones principales del portal. El Admin dispone además de un acceso directo a la gestión de usuarios.
- **Set temporada** (`set_temporada.php`): controlador POST que valida temporada y la persiste en sesión.
- **Avatar** (`avatar.php`): si no se encuentra la imagen, crea un SVG por defecto.

### Módulos compartidos (includes/)

#### Inicialización (`includes/app_init.php`)
Arranque común de la aplicación: prepara configuración, sesión y dependencias necesarias para que cualquier página funcione de forma consistente.

#### Contexto de aplicación (`includes/app_context.php`)
Centraliza información de "estado" usada por distintas páginas (por ejemplo, datos de sesión, temporada activa o utilidades de contexto).

#### Autenticación y roles (`includes/auth.php`)
Contiene la lógica relacionada con el inicio de sesión, validación de credenciales, gestión de roles y comprobaciones de permisos.

#### Gestión de XML (`includes/xml.php`)
Es el núcleo del acceso a datos: carga `data/datos.xml`, valida contra `data/datos.xsd` y proporciona funciones para obtener datos estructurados (temporadas, equipos, jugadores, partidos, noticias, clasificación…).

#### Plantilla común (`includes/header.php` y `includes/footer.php`)
Definen la estructura común de la interfaz (cabecera/menú/pie) y garantizan el mismo diseño y navegación en todo el sitio.

## Datos y transformaciones (data/)

### `data/datos.xml`
Fuente principal de datos: temporadas, equipos, jugadores, partidos, noticias, etc.

### `data/datos.xsd`
Esquema de validación del XML. Permite detectar datos incompletos o con estructura incorrecta antes de mostrarlos en la web.

### `data/login_events.json`
Registro de eventos relacionados con autenticación (trazabilidad básica de inicios de sesión / eventos asociados, según implementación).

### `data/xsl/` (transformaciones XSLT)
Hojas XSL que convierten el XML en HTML para secciones basadas en listados:
- `clasificacion.xsl`
- `partidos.xsl`
- `jugadores.xsl`
- `jugador.xsl`
- `equipos.xsl`
- `equipo.xsl`

## Estilos (css/)

Su función principal es garantizar una presentación homogénea en todas las páginas y adaptar la interfaz a distintos tamaños de pantalla.

- Diseño uniforme en todas las vistas gracias a las clases compartidas.
- Implementación responsive: la interfaz se adapta correctamente a móvil, tablet y escritorio.
- Cualquier cambio visual global (colores, tipografía, espaciados) se gestiona desde aquí, sin tocar las páginas PHP.

## Scripts (js/)

Incluye los archivos JavaScript responsables del comportamiento dinámico de la interfaz:
- Actualización de secciones al cambiar filtros activos (temporada, jornada, jugador...).
- Interacción con los elementos del menú de navegación.
- Peticiones de carga parcial de contenido a través de `cargar_contenido.php`, evitando recargas completas de página.

## Recursos gráficos (img/)

Carpeta destinada a las imágenes estáticas del sitio: logos, banners e imágenes de apoyo visual.
Las imágenes de jugadores y equipos se referencian desde aquí cuando están disponibles.
Si no existe un recurso gráfico para un jugador o equipo concreto, la aplicación usa automáticamente `php/avatar.php`, que genera un SVG con las iniciales del elemento.

## Tecnologías utilizadas

- **PHP**: creación de páginas, control de sesiones, control de acceso por roles y validación de formularios.
- **XML y XSD**: almacenamiento de datos y validación de estructura.
- **XSLT** (`data/xsl/*.xsl`): transformaciones de XML a HTML en cliente, con parámetros para filtrar.
- **HTML5 semántico**: `header`/`nav`/`main`/`section`/`article`/`aside`.
- **CSS**: diseño homogéneo + responsive.
- **JavaScript**: selección de temporada/jornada y recarga parcial por transformaciones XSLT.

## Datos y validación XML

- Los datos se almacenan en `data/datos.xml`.
- Antes de trabajar con ellos, el sistema valida el XML con `data/datos.xsd`.
- La lógica de lectura y construcción de datos (clasificación, listados, etc.) se centraliza en `includes/xml.php`.
- La parte en PHP del proyecto incluye funciones de apoyo para:
  - Obtener temporada actual.
  - Construir clasificación.
  - Recuperar equipos/jugadores.
  - Construir noticias por temporada, etc.

## Formulario y validación

Formularios:
- **Login** (POST): valida que el usuario cumpla patrón y que la contraseña exista.
- **Entrada invitado** (POST): crea sesión invitado.
- **Cambiar temporada** (POST): valida formato YYYY-YYYY y comprueba existencia real en el XML.

## Accesibilidad y usabilidad

Navegación con etiqueta accesible en `<nav>`.

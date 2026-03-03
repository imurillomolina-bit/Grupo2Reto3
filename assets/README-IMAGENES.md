# Instrucciones para las Imágenes

## Estructura requerida:

### Fotos de jugadores:
Las fotos de jugadores deben colocarse en: `assets/players/`

Según el XML, las rutas son:
- `assets/players/jugador_1_temp_1.png`
- `assets/players/jugador_2_temp_1.png`
- `assets/players/jugador_3_temp_1.jpg`
- etc.

### Escudos de equipos:
Los escudos de equipos deben colocarse en: `assets/teams/`

Según el XML, las rutas son:
- `assets/teams/equipo_1_temp_1.png`
- `assets/teams/equipo_4_temp_1.png`
- etc.

### Imágenes por defecto:
Se necesitan dos imágenes por defecto en la carpeta `assets/`:
- `default-player.png` - Imagen que se muestra cuando no hay foto de jugador
- `default-team.png` - Imagen que se muestra cuando no hay escudo de equipo

## Cómo agregar las imágenes:

1. **Para jugadores**: Coloca las fotos en `assets/players/` con los nombres exactos que aparecen en el XML dentro de `<FotosJugadores>`

2. **Para equipos**: Coloca los escudos en `assets/teams/` con los nombres exactos que aparecen en el XML dentro de `<EscudosEquipos>`

3. **Formatos soportados**: PNG, JPG, SVG

## Ejemplo de estructura:
```
assets/
├── players/
│   ├── jugador_1_temp_1.png
│   ├── jugador_2_temp_1.png
│   ├── jugador_3_temp_1.jpg
│   └── ...
├── teams/
│   ├── equipo_1_temp_1.png
│   ├── equipo_4_temp_1.png
│   └── ...
├── default-player.png
└── default-team.png
```

## Nota importante:
El sistema XSL ha sido configurado para buscar automáticamente estas imágenes. Si no encuentra una imagen específica, usará la imagen por defecto correspondiente.

<?php

declare(strict_types=1);

const DATA_XML_PATH = __DIR__ . '/../data/datos.xml';
const DATA_XSD_PATH = __DIR__ . '/../data/datos.xsd';

function normalize_public_path(string $path): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $isPhpPage = str_ends_with($scriptDir, '/php');

    if ($isPhpPage) {
        return $path;
    }

    if (str_starts_with($path, '../')) {
        return substr($path, 3);
    }

    return $path;
}

function build_jugador_avatar_url(string $nombre): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $isPhpPage = str_ends_with($scriptDir, '/php');
    $basePath = $isPhpPage ? 'avatar.php' : 'php/avatar.php';

    return $basePath . '?nombre=' . rawurlencode($nombre);
}

function load_liga_xml(): SimpleXMLElement
{
    if (!is_file(DATA_XML_PATH)) {
        throw new RuntimeException('No existe el archivo XML de datos.');
    }

    if (!is_file(DATA_XSD_PATH)) {
        throw new RuntimeException('No existe el archivo XSD de validacion.');
    }

    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;
    $dom->load(DATA_XML_PATH);

    libxml_use_internal_errors(true);
    $isValid = $dom->schemaValidate(DATA_XSD_PATH);
    if (!$isValid) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $message = isset($errors[0]) ? trim((string) $errors[0]->message) : 'XML invalido.';
        throw new RuntimeException('El XML no cumple el esquema XSD: ' . $message);
    }

    $xml = simplexml_load_file(DATA_XML_PATH);
    if ($xml === false) {
        throw new RuntimeException('No se pudo cargar el XML.');
    }

    return $xml;
}

function get_temporadas(SimpleXMLElement $xml): array
{
    $temporadas = [];
    foreach ($xml->temporadas->temporada as $temporada) {
        $temporadas[] = [
            'id' => (string) $temporada['id'],
            'nombre' => (string) $temporada['nombre'],
            'actual' => ((string) $temporada['actual']) === 'si',
        ];
    }

    return $temporadas;
}

function get_default_temporada_id(SimpleXMLElement $xml): ?string
{
    foreach ($xml->temporadas->temporada as $temporada) {
        if ((string) $temporada['actual'] === 'si') {
            return (string) $temporada['id'];
        }
    }

    $first = $xml->temporadas->temporada[0] ?? null;
    return $first ? (string) $first['id'] : null;
}

function get_temporada_by_id(SimpleXMLElement $xml, string $temporadaId): ?SimpleXMLElement
{
    foreach ($xml->temporadas->temporada as $temporada) {
        if ((string) $temporada['id'] === $temporadaId) {
            return $temporada;
        }
    }

    return null;
}

function get_temporada_actual(SimpleXMLElement $xml): SimpleXMLElement
{
    $temporadaId = (string) ($_SESSION['temporada_actual'] ?? '');
    $temporada = get_temporada_by_id($xml, $temporadaId);

    if ($temporada !== null) {
        return $temporada;
    }

    $defaultTemporadaId = get_default_temporada_id($xml);
    if ($defaultTemporadaId === null) {
        throw new RuntimeException('No hay temporadas disponibles en el XML.');
    }

    $_SESSION['temporada_actual'] = $defaultTemporadaId;
    $temporada = get_temporada_by_id($xml, $defaultTemporadaId);

    if ($temporada === null) {
        throw new RuntimeException('No se pudo cargar la temporada actual.');
    }

    return $temporada;
}

function build_clasificacion(SimpleXMLElement $temporada): array
{
    $tabla = [];
    foreach ($temporada->equipos->equipo as $equipo) {
        $id = (int) $equipo['id'];
        $tabla[$id] = [
            'id' => $id,
            'nombre' => (string) $equipo->nombre,
            'escudo' => normalize_public_path((string) $equipo->escudo),
            'pj' => 0,
            'pg' => 0,
            'pe' => 0,
            'pp' => 0,
            'gf' => 0,
            'gc' => 0,
            'dg' => 0,
            'pts' => 0,
        ];
    }

    foreach ($temporada->partidos->partido as $partido) {
        $localId = (int) $partido['local'];
        $visitanteId = (int) $partido['visitante'];
        $golesLocal = (int) $partido['goles_local'];
        $golesVisitante = (int) $partido['goles_visitante'];

        if (!isset($tabla[$localId], $tabla[$visitanteId])) {
            continue;
        }

        $tabla[$localId]['pj']++;
        $tabla[$visitanteId]['pj']++;

        $tabla[$localId]['gf'] += $golesLocal;
        $tabla[$localId]['gc'] += $golesVisitante;
        $tabla[$visitanteId]['gf'] += $golesVisitante;
        $tabla[$visitanteId]['gc'] += $golesLocal;

        if ($golesLocal > $golesVisitante) {
            $tabla[$localId]['pg']++;
            $tabla[$visitanteId]['pp']++;
            $tabla[$localId]['pts'] += 3;
        } elseif ($golesLocal < $golesVisitante) {
            $tabla[$visitanteId]['pg']++;
            $tabla[$localId]['pp']++;
            $tabla[$visitanteId]['pts'] += 3;
        } else {
            $tabla[$localId]['pe']++;
            $tabla[$visitanteId]['pe']++;
            $tabla[$localId]['pts']++;
            $tabla[$visitanteId]['pts']++;
        }
    }

    foreach ($tabla as &$fila) {
        $fila['dg'] = $fila['gf'] - $fila['gc'];
    }
    unset($fila);

    usort($tabla, static function (array $a, array $b): int {
        return [$b['pts'], $b['dg'], $b['gf'], $a['nombre']] <=> [$a['pts'], $a['dg'], $a['gf'], $b['nombre']];
    });

    return $tabla;
}

function get_equipos_temporada(SimpleXMLElement $temporada): array
{
    $equipos = [];

    foreach ($temporada->equipos->equipo as $equipo) {
        $equipos[] = [
            'id' => (int) $equipo['id'],
            'nombre' => (string) $equipo->nombre,
            'ciudad' => (string) $equipo->ciudad,
            'estadio' => (string) $equipo->estadio,
            'descripcion' => (string) $equipo->descripcion,
            'escudo' => normalize_public_path((string) $equipo->escudo),
            'jugadores' => isset($equipo->jugadores->jugador) ? count($equipo->jugadores->jugador) : 0,
        ];
    }

    return $equipos;
}

function get_jugadores_temporada(SimpleXMLElement $temporada): array
{
    $jugadores = [];

    foreach ($temporada->equipos->equipo as $equipo) {
        $nombreEquipo = (string) $equipo->nombre;

        foreach ($equipo->jugadores->jugador as $jugador) {
            $nombreCompleto = trim((string) $jugador->nombre);
            $nombrePartido = split_nombre_jugador($nombreCompleto);
            $nombreXml = trim((string) ($jugador->nombre ?? ''));
            $apellidosXml = trim((string) ($jugador->apellidos ?? ''));

            $nombre = $nombrePartido['nombre'];
            $apellidos = $nombrePartido['apellidos'];

            if ($apellidosXml !== '') {
                $nombre = $nombreXml !== '' ? $nombreXml : $nombrePartido['nombre'];
                $apellidos = $apellidosXml;
            }

            $jugadores[] = [
                'id' => (int) $jugador['id'],
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'fecha_nacimiento' => trim((string) ($jugador->fecha_nacimiento ?? 'No disponible')),
                'nacionalidad' => trim((string) ($jugador->nacionalidad ?? 'No disponible')),
                'peso' => trim((string) ($jugador->peso ?? 'No disponible')),
                'altura' => trim((string) ($jugador->altura ?? 'No disponible')),
                'posicion' => (string) $jugador->posicion,
                'foto' => build_jugador_avatar_url($nombreCompleto),
                'equipo' => $nombreEquipo,
                'equipo_id' => (int) $equipo['id'],
            ];
        }
    }

    return $jugadores;
}

function split_nombre_jugador(string $nombreCompleto): array
{
    $partes = preg_split('/\s+/', trim($nombreCompleto)) ?: [];

    if ($partes === []) {
        return ['nombre' => 'No disponible', 'apellidos' => 'No disponible'];
    }

    if (count($partes) === 1) {
        return ['nombre' => $partes[0], 'apellidos' => 'No disponible'];
    }

    return [
        'nombre' => (string) array_shift($partes),
        'apellidos' => implode(' ', $partes),
    ];
}

function get_jugador_detalle_by_id(SimpleXMLElement $temporada, int $jugadorId): ?array
{
    foreach ($temporada->equipos->equipo as $equipo) {
        foreach ($equipo->jugadores->jugador as $jugador) {
            if ((int) $jugador['id'] !== $jugadorId) {
                continue;
            }

            $nombreCompleto = trim((string) $jugador->nombre);
            $nombrePartido = split_nombre_jugador($nombreCompleto);
            $nombreXml = trim((string) ($jugador->nombre ?? ''));
            $apellidosXml = trim((string) ($jugador->apellidos ?? ''));

            $nombre = $nombrePartido['nombre'];
            $apellidos = $nombrePartido['apellidos'];

            if ($apellidosXml !== '') {
                $nombre = $nombreXml !== '' ? $nombreXml : $nombrePartido['nombre'];
                $apellidos = $apellidosXml;
            }

            return [
                'id' => $jugadorId,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'fecha_nacimiento' => trim((string) ($jugador->fecha_nacimiento ?? 'No disponible')),
                'nacionalidad' => trim((string) ($jugador->nacionalidad ?? 'No disponible')),
                'peso' => trim((string) ($jugador->peso ?? 'No disponible')),
                'altura' => trim((string) ($jugador->altura ?? 'No disponible')),
                'posicion' => trim((string) $jugador->posicion),
                'foto' => build_jugador_avatar_url($nombreCompleto),
                'equipo' => trim((string) $equipo->nombre),
                'equipo_id' => (int) $equipo['id'],
                'equipo_escudo' => normalize_public_path((string) $equipo->escudo),
            ];
        }
    }

    return null;
}

function get_partidos_recientes(SimpleXMLElement $temporada): array
{
    $equiposPorId = [];
    foreach ($temporada->equipos->equipo as $equipo) {
        $equiposPorId[(int) $equipo['id']] = (string) $equipo->nombre;
    }

    $partidos = [];
    foreach ($temporada->partidos->partido as $partido) {
        $partidos[] = [
            'fecha' => (string) $partido['fecha'],
            'local' => $equiposPorId[(int) $partido['local']] ?? 'N/D',
            'visitante' => $equiposPorId[(int) $partido['visitante']] ?? 'N/D',
            'goles_local' => (int) $partido['goles_local'],
            'goles_visitante' => (int) $partido['goles_visitante'],
            'marcador' => (string) $partido['goles_local'] . ' - ' . (string) $partido['goles_visitante'],
        ];
    }

    usort($partidos, static function (array $a, array $b): int {
        return strcmp($b['fecha'], $a['fecha']);
    });

    return $partidos;
}

function build_noticias_temporada(SimpleXMLElement $temporada): array
{
    $clasificacion = build_clasificacion($temporada);
    $partidosRecientes = get_partidos_recientes($temporada);
    $temporadaNombre = (string) $temporada['nombre'];
    $noticias = [];

    $liderActual = $clasificacion[0] ?? null;
    if ($liderActual !== null) {
        $noticias[] = [
            'titulo' => 'Liderato en juego',
            'texto' => $liderActual['nombre'] . ' manda en la tabla de ' . $temporadaNombre . ' con ' . $liderActual['pts'] . ' puntos y una diferencia de ' . $liderActual['dg'] . '.',
        ];
    }

    foreach (array_slice($partidosRecientes, 0, 3) as $partidoReciente) {
        $noticias[] = [
            'titulo' => 'Marcador reciente',
            'texto' => $partidoReciente['local'] . ' y ' . $partidoReciente['visitante'] . ' cerraron su cruce con un ' . $partidoReciente['marcador'] . ' el ' . $partidoReciente['fecha'] . '.',
        ];
    }

    return $noticias;
}

function find_equipo_by_id(SimpleXMLElement $temporada, int $equipoId): ?SimpleXMLElement
{
    foreach ($temporada->equipos->equipo as $equipo) {
        if ((int) $equipo['id'] === $equipoId) {
            return $equipo;
        }
    }

    return null;
}

function get_partidos_equipo(SimpleXMLElement $temporada, int $equipoId): array
{
    $equiposPorId = [];
    foreach ($temporada->equipos->equipo as $equipo) {
        $equiposPorId[(int) $equipo['id']] = (string) $equipo->nombre;
    }

    $partidos = [];
    foreach ($temporada->partidos->partido as $partido) {
        $localId = (int) $partido['local'];
        $visitanteId = (int) $partido['visitante'];
        if ($localId !== $equipoId && $visitanteId !== $equipoId) {
            continue;
        }

        $partidos[] = [
            'fecha' => (string) $partido['fecha'],
            'local' => $equiposPorId[$localId] ?? 'N/D',
            'visitante' => $equiposPorId[$visitanteId] ?? 'N/D',
            'marcador' => (string) $partido['goles_local'] . ' - ' . (string) $partido['goles_visitante'],
        ];
    }

    return $partidos;
}

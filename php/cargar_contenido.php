<?php

declare(strict_types=1);

// Endpoint unico para validar parametros, validar XML/XSD y transformar XML+XSL en HTML.

require_once __DIR__ . '/../includes/app_init.php';

/** @var array<string, string> */
const PAGE_XSL_MAP = [
    'clasificacion' => __DIR__ . '/../data/xsl/clasificacion.xsl',
    'equipos' => __DIR__ . '/../data/xsl/equipos.xsl',
    'equipo_detalle' => __DIR__ . '/../data/xsl/equipos.xsl',
    'jugadores' => __DIR__ . '/../data/xsl/jugadores.xsl',
    'jugador_detalle' => __DIR__ . '/../data/xsl/jugadores.xsl',
    'partidos' => __DIR__ . '/../data/xsl/partidos.xsl',
    'jornadas' => __DIR__ . '/../data/xsl/partidos.xsl',
];

function html_error(int $statusCode, string $message): never
{
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<article class="panel-error"><p>' . e($message) . '</p></article>';
    exit;
}

function collect_match_dates(SimpleXMLElement $temporada): array
{
    $dates = [];

    foreach ($temporada->partidos->partido as $partido) {
        $fecha = trim((string) ($partido['fecha'] ?? ''));
        if ($fecha !== '' && !in_array($fecha, $dates, true)) {
            $dates[] = $fecha;
        }
    }

    sort($dates);
    return $dates;
}

function exists_team_in_season(SimpleXMLElement $temporada, string $teamId): bool
{
    foreach ($temporada->equipos->equipo as $equipo) {
        if ((string) ($equipo['id'] ?? '') === $teamId) {
            return true;
        }
    }

    return false;
}

function exists_player_in_season(SimpleXMLElement $temporada, string $playerId): bool
{
    foreach ($temporada->equipos->equipo as $equipo) {
        foreach ($equipo->jugadores->jugador as $jugador) {
            if ((string) ($jugador['id'] ?? '') === $playerId) {
                return true;
            }
        }
    }

    return false;
}

$pageRaw = filter_input(INPUT_GET, 'page', FILTER_UNSAFE_RAW);
$page = is_string($pageRaw) ? trim($pageRaw) : '';

if ($page === '' || !array_key_exists($page, PAGE_XSL_MAP)) {
    html_error(400, 'Pagina no encontrada.');
}

try {
    // Valida XML contra XSD en cada request.
    $xml = load_liga_xml();
} catch (Throwable $ex) {
    html_error(500, 'Error: los datos XML no son validos.');
}

$temporadaRaw = filter_input(INPUT_GET, 'temporada_id', FILTER_UNSAFE_RAW);
$temporadaId = is_string($temporadaRaw) ? trim($temporadaRaw) : '';

if ($temporadaId === '') {
    $temporadaId = trim((string) ($_SESSION['temporada_actual'] ?? ''));
}

if ($temporadaId === '') {
    $temporadaId = (string) (get_default_temporada_id($xml) ?? '');
}

if ($temporadaId === '' || !preg_match('/^[0-9]{4}-[0-9]{4}$/', $temporadaId)) {
    html_error(400, 'Temporada invalida.');
}

$temporada = get_temporada_by_id($xml, $temporadaId);
if ($temporada === null) {
    html_error(404, 'No existe la temporada solicitada.');
}

$idRaw = filter_input(INPUT_GET, 'id', FILTER_UNSAFE_RAW);
$id = is_string($idRaw) ? trim($idRaw) : '';

if ($id !== '' && !preg_match('/^[0-9]+$/', $id)) {
    html_error(400, 'Identificador invalido.');
}

if ($page === 'equipo_detalle') {
    if ($id === '') {
        html_error(400, 'Debes indicar el equipo solicitado.');
    }

    if (!exists_team_in_season($temporada, $id)) {
        html_error(404, 'No existe el equipo solicitado en la temporada activa.');
    }
}

if ($page === 'jugador_detalle') {
    if ($id === '') {
        html_error(400, 'Debes indicar el jugador solicitado.');
    }

    if (!exists_player_in_season($temporada, $id)) {
        html_error(404, 'No existe el jugador solicitado en la temporada activa.');
    }
}

$fechaRaw = filter_input(INPUT_GET, 'fecha_seleccionada', FILTER_UNSAFE_RAW);
$fechaSeleccionada = is_string($fechaRaw) ? trim($fechaRaw) : '';

if ($fechaSeleccionada !== '' && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $fechaSeleccionada)) {
    html_error(400, 'Fecha de jornada invalida.');
}

if (($page === 'partidos' || $page === 'jornadas') && $fechaSeleccionada === '') {
    $dates = collect_match_dates($temporada);
    $fechaSeleccionada = $dates[0] ?? '';
}

if (($page === 'partidos' || $page === 'jornadas') && $fechaSeleccionada !== '') {
    $knownDates = collect_match_dates($temporada);
    if (!in_array($fechaSeleccionada, $knownDates, true)) {
        html_error(404, 'La jornada solicitada no existe en la temporada activa.');
    }
}

$xslPath = PAGE_XSL_MAP[$page];
if (!is_file($xslPath)) {
    html_error(500, 'No se encontro la plantilla XSL solicitada.');
}

$xmlDom = new DOMDocument();
$xmlDom->preserveWhiteSpace = false;
$xmlDom->formatOutput = false;
$xmlDom->load(DATA_XML_PATH);

$xslDom = new DOMDocument();
$xslDom->preserveWhiteSpace = false;
$xslDom->formatOutput = false;
$xslDom->load($xslPath);

$processor = new XSLTProcessor();
$processor->importStylesheet($xslDom);
$processor->setParameter('', 'temporadaId', $temporadaId);

if ($page === 'equipo_detalle' || ($page === 'equipos' && $id !== '')) {
    $processor->setParameter('', 'equipoId', $id);
} elseif ($page === 'equipos') {
    $processor->setParameter('', 'equipoId', '');
}

if ($page === 'jugador_detalle' || ($page === 'jugadores' && $id !== '')) {
    $processor->setParameter('', 'jugadorId', $id);
} elseif ($page === 'jugadores') {
    $processor->setParameter('', 'jugadorId', '');
}

if ($page === 'partidos' || $page === 'jornadas') {
    $processor->setParameter('', 'fechaSeleccionada', $fechaSeleccionada);
}

$output = $processor->transformToXML($xmlDom);
if (!is_string($output)) {
    html_error(500, 'No se pudo transformar el contenido XML/XSL.');
}

header('Content-Type: text/html; charset=UTF-8');
echo $output;

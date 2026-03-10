<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const FUTSAL_XML_PATH = __DIR__ . '/../data/futsal.xml';

function load_futsal_xml(): SimpleXMLElement
{
    static $xml = null;

    if ($xml instanceof SimpleXMLElement) {
        return $xml;
    }

    if (!file_exists(FUTSAL_XML_PATH)) {
        throw new RuntimeException('No se encontro data/futsal.xml');
    }

    $loaded = simplexml_load_file(FUTSAL_XML_PATH);
    if ($loaded === false) {
        throw new RuntimeException('No se pudo parsear data/futsal.xml');
    }

    $xml = $loaded;
    return $xml;
}

function normalize_path(string $path): string
{
    $normalized = str_replace('\\', '/', trim($path));
    return $normalized !== '' ? $normalized : '';
}

function csv_ids(string $csv): array
{
    $csv = trim($csv);
    if ($csv === '') {
        return [];
    }

    $parts = array_map('trim', explode(',', $csv));
    $ids = [];

    foreach ($parts as $part) {
        if ($part !== '' && ctype_digit($part)) {
            $ids[] = (int) $part;
        }
    }

    return $ids;
}

function get_config(SimpleXMLElement $xml): array
{
    $timeoutMin = isset($xml->Configuracion->SesionTimeoutMinutos)
        ? (int) $xml->Configuracion->SesionTimeoutMinutos
        : 30;

    $roles = [];
    if (isset($xml->Configuracion->Roles->Rol)) {
        foreach ($xml->Configuracion->Roles->Rol as $rol) {
            $name = (string) ($rol['nombre'] ?? '');
            $label = (string) ($rol['etiqueta'] ?? '');
            if ($name !== '') {
                $roles[$name] = $label !== '' ? $label : $name;
            }
        }
    }

    return [
        'timeout_minutes' => max(1, $timeoutMin),
        'roles' => $roles,
    ];
}

function apply_session_timeout(SimpleXMLElement $xml): void
{
    $config = get_config($xml);
    $timeoutSec = $config['timeout_minutes'] * 60;

    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return;
    }

    if ((time() - (int) $_SESSION['last_activity']) > $timeoutSec) {
        unset($_SESSION['user']);
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sesion caducada por inactividad.'];
    }

    $_SESSION['last_activity'] = time();
}

function get_users(SimpleXMLElement $xml): array
{
    $users = [];

    if (!isset($xml->Usuarios->Usuario)) {
        return $users;
    }

    foreach ($xml->Usuarios->Usuario as $user) {
        $users[] = [
            'id' => (int) ($user->ID ?? 0),
            'username' => (string) ($user->Username ?? ''),
            'password' => (string) ($user->Password ?? ''),
            'role' => (string) ($user->Rol ?? 'Invitado'),
            'full_name' => (string) ($user->NombreCompleto ?? 'Usuario'),
            'is_guest' => ((string) ($user->EsInvitado ?? 'false')) === 'true',
        ];
    }

    return $users;
}

function authenticate_user(SimpleXMLElement $xml, string $username, string $password): ?array
{
    foreach (get_users($xml) as $user) {
        if ($user['is_guest']) {
            continue;
        }

        if ($user['username'] === $username && $user['password'] === $password) {
            return $user;
        }
    }

    return null;
}

function get_guest_user(SimpleXMLElement $xml): array
{
    foreach (get_users($xml) as $user) {
        if ($user['is_guest']) {
            return $user;
        }
    }

    return [
        'id' => 0,
        'username' => 'invitado',
        'password' => '',
        'role' => 'Invitado',
        'full_name' => 'Usuario Invitado',
        'is_guest' => true,
    ];
}

function get_seasons(SimpleXMLElement $xml): array
{
    $seasons = [];

    if (!isset($xml->Temporadas->Temporada)) {
        return $seasons;
    }

    foreach ($xml->Temporadas->Temporada as $season) {
        $seasons[] = [
            'id' => (int) ($season->ID ?? 0),
            'name' => (string) ($season->Nombre ?? ''),
            'year' => (int) ($season->Ano ?? 0),
            'status' => (string) ($season->Estado ?? ''),
            'team_ids' => csv_ids((string) ($season->EquipoIds ?? '')),
            'round_ids' => csv_ids((string) ($season->JornadaIds ?? '')),
            'xml' => $season,
        ];
    }

    usort($seasons, static function (array $a, array $b): int {
        return $b['year'] <=> $a['year'];
    });

    return $seasons;
}

function get_current_season(SimpleXMLElement $xml): ?array
{
    $seasons = get_seasons($xml);
    if ($seasons === []) {
        return null;
    }

    foreach ($seasons as $season) {
        if ($season['status'] === 'abierta') {
            return $season;
        }
    }

    return $seasons[0];
}

function get_season_by_id(SimpleXMLElement $xml, int $seasonId): ?array
{
    foreach (get_seasons($xml) as $season) {
        if ($season['id'] === $seasonId) {
            return $season;
        }
    }

    return null;
}

function get_teams_map(SimpleXMLElement $xml): array
{
    $teams = [];

    if (!isset($xml->Equipos->Equipo)) {
        return $teams;
    }

    foreach ($xml->Equipos->Equipo as $team) {
        $id = (int) ($team->ID ?? 0);
        if ($id <= 0) {
            continue;
        }

        $teams[$id] = [
            'id' => $id,
            'name' => (string) ($team->Nombre ?? ''),
            'pabellon' => (string) ($team->Pabellon ?? ''),
            'country' => (string) ($team->Pais ?? ''),
            'shield' => normalize_path((string) ($team->Escudo ?? '')),
        ];
    }

    return $teams;
}

function get_players_map(SimpleXMLElement $xml): array
{
    $players = [];

    if (!isset($xml->Jugadores->Jugador)) {
        return $players;
    }

    foreach ($xml->Jugadores->Jugador as $player) {
        $id = (int) ($player->ID ?? 0);
        if ($id <= 0) {
            continue;
        }

        $players[$id] = [
            'id' => $id,
            'name' => (string) ($player->Nombre ?? ''),
            'position' => (string) ($player->Posicion ?? ''),
            'number' => (int) ($player->Numero ?? 0),
            'nationality' => (string) ($player->Nacionalidad ?? ''),
            'image' => normalize_path((string) ($player->Imagen ?? '')),
        ];
    }

    return $players;
}

function get_team_season_relations(SimpleXMLElement $xml): array
{
    $relations = [];

    if (!isset($xml->EquipoTemporada)) {
        return $relations;
    }

    foreach ($xml->EquipoTemporada as $relation) {
        $seasonId = (int) ($relation->TemporadaID ?? 0);
        $teamId = (int) ($relation->EquipoID ?? 0);
        if ($seasonId <= 0 || $teamId <= 0) {
            continue;
        }

        $relations[$seasonId][$teamId] = csv_ids((string) ($relation->JugadorIds ?? ''));
    }

    return $relations;
}

function get_team_shield_for_season(array $season): array
{
    $map = [];
    $node = $season['xml']->EscudosEquipos->Equipo ?? null;

    if ($node === null) {
        return $map;
    }

    foreach ($season['xml']->EscudosEquipos->Equipo as $teamShield) {
        $teamId = (int) ($teamShield['id'] ?? 0);
        $path = normalize_path((string) $teamShield);
        if ($teamId > 0 && $path !== '') {
            $map[$teamId] = $path;
        }
    }

    return $map;
}

function get_player_photo_for_season(array $season): array
{
    $map = [];
    $node = $season['xml']->FotosJugadores->Jugador ?? null;

    if ($node === null) {
        return $map;
    }

    foreach ($season['xml']->FotosJugadores->Jugador as $playerPhoto) {
        $playerId = (int) ($playerPhoto['id'] ?? 0);
        $path = normalize_path((string) $playerPhoto);
        if ($playerId > 0 && $path !== '') {
            $map[$playerId] = $path;
        }
    }

    return $map;
}

function get_matches_for_season(SimpleXMLElement $xml, int $seasonId): array
{
    $matches = [];

    if (!isset($xml->Partidos->Partido)) {
        return $matches;
    }

    foreach ($xml->Partidos->Partido as $match) {
        if ((int) ($match->TemporadaID ?? 0) !== $seasonId) {
            continue;
        }

        $matches[] = [
            'id' => (int) ($match->ID ?? 0),
            'team1' => (int) ($match->EquipoID1 ?? 0),
            'team2' => (int) ($match->EquipoID2 ?? 0),
            'goals1' => (int) ($match->GolesEquipo1 ?? 0),
            'goals2' => (int) ($match->GolesEquipo2 ?? 0),
            'date' => (string) ($match->Fecha ?? ''),
            'status' => (string) ($match->Estado ?? ''),
            'round_id' => (int) ($match->JornadaID ?? 0),
        ];
    }

    return $matches;
}

function compute_classification(SimpleXMLElement $xml, array $season): array
{
    $teams = get_teams_map($xml);
    $table = [];

    foreach ($season['team_ids'] as $teamId) {
        if (!isset($teams[$teamId])) {
            continue;
        }

        $table[$teamId] = [
            'team_id' => $teamId,
            'team_name' => $teams[$teamId]['name'],
            'points' => 0,
            'played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_diff' => 0,
        ];
    }

    $matches = get_matches_for_season($xml, $season['id']);
    foreach ($matches as $match) {
        if ($match['status'] === 'pendiente') {
            continue;
        }

        if (!isset($table[$match['team1']], $table[$match['team2']])) {
            continue;
        }

        $table[$match['team1']]['played']++;
        $table[$match['team2']]['played']++;
        $table[$match['team1']]['goals_for'] += $match['goals1'];
        $table[$match['team1']]['goals_against'] += $match['goals2'];
        $table[$match['team2']]['goals_for'] += $match['goals2'];
        $table[$match['team2']]['goals_against'] += $match['goals1'];

        if ($match['goals1'] > $match['goals2']) {
            $table[$match['team1']]['wins']++;
            $table[$match['team1']]['points'] += 3;
            $table[$match['team2']]['losses']++;
        } elseif ($match['goals1'] < $match['goals2']) {
            $table[$match['team2']]['wins']++;
            $table[$match['team2']]['points'] += 3;
            $table[$match['team1']]['losses']++;
        } else {
            $table[$match['team1']]['draws']++;
            $table[$match['team2']]['draws']++;
            $table[$match['team1']]['points']++;
            $table[$match['team2']]['points']++;
        }
    }

    foreach ($table as &$row) {
        $row['goal_diff'] = $row['goals_for'] - $row['goals_against'];
    }
    unset($row);

    $rows = array_values($table);
    usort($rows, static function (array $a, array $b): int {
        $byPoints = $b['points'] <=> $a['points'];
        if ($byPoints !== 0) {
            return $byPoints;
        }

        $byDiff = $b['goal_diff'] <=> $a['goal_diff'];
        if ($byDiff !== 0) {
            return $byDiff;
        }

        $byGoals = $b['goals_for'] <=> $a['goals_for'];
        if ($byGoals !== 0) {
            return $byGoals;
        }

        return strcmp($a['team_name'], $b['team_name']);
    });

    return $rows;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function role_label(SimpleXMLElement $xml, string $role): string
{
    $roles = get_config($xml)['roles'];
    return $roles[$role] ?? $role;
}

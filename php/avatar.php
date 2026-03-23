<?php

declare(strict_types=1);

header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$nombreRaw = filter_input(INPUT_GET, 'nombre', FILTER_UNSAFE_RAW);
$nombre = is_string($nombreRaw) ? trim($nombreRaw) : '';

if ($nombre === '') {
    $nombre = 'Jugador';
}

$nombre = preg_replace('/\s+/', ' ', $nombre) ?? 'Jugador';
$nombre = mb_substr($nombre, 0, 24);

$paletas = [
    ['#0a3d7a', '#1261c9', '#ffd232'],
    ['#2b2d42', '#3f5aa9', '#f7b801'],
    ['#1f4b3f', '#2d7d66', '#f3d34a'],
    ['#4a2f1d', '#8f5b3e', '#ffd166'],
    ['#3b1f5c', '#6d3bb8', '#ffd166'],
    ['#7a1026', '#c1264b', '#ffd166'],
];

$hash = abs(crc32(mb_strtolower($nombre, 'UTF-8')));
$palette = $paletas[$hash % count($paletas)];
[$bgFrom, $bgTo, $shirt] = $palette;

echo "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'>";
echo "<defs><linearGradient id='bg' x1='0' x2='1' y1='0' y2='1'>";
echo "<stop offset='0%' stop-color='{$bgFrom}'/>";
echo "<stop offset='100%' stop-color='{$bgTo}'/>";
echo "</linearGradient></defs>";
echo "<rect width='400' height='300' fill='url(#bg)'/>";
echo "<circle cx='200' cy='120' r='54' fill='{$shirt}'/>";
echo "<rect x='130' y='178' width='140' height='95' rx='18' fill='{$shirt}'/>";
echo '</svg>';


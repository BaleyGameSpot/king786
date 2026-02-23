<?php

function curlGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'OSM-Test-Agent'); // obrigatório para Nominatim
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

echo "<h2>✅ Teste Geocoding (endereço → coordenadas):</h2>";

$address = "Lisboa, Portugal";
$geocodeUrl = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);
$geocodeResponse = curlGet($geocodeUrl);
$geocodeData = json_decode($geocodeResponse, true);

if (!empty($geocodeData[0])) {
    echo "Endereço encontrado: <strong>{$address}</strong><br>";
    echo "Latitude: " . $geocodeData[0]['lat'] . "<br>";
    echo "Longitude: " . $geocodeData[0]['lon'] . "<br>";

    $startLat = $geocodeData[0]['lat'];
    $startLon = $geocodeData[0]['lon'];

    // Exemplo: rota entre dois pontos próximos
    $endLat = $startLat + 0.01;
    $endLon = $startLon + 0.01;

    echo "<h2>✅ Teste Routing (direção OSM):</h2>";

    // OSRM demo server (atenção: uso limitado)
    $routeUrl = "http://router.project-osrm.org/route/v1/driving/{$startLon},{$startLat};{$endLon},{$endLat}?overview=false&geometries=polyline";
    $routeResponse = curlGet($routeUrl);
    $routeData = json_decode($routeResponse, true);

    if (!empty($routeData['routes'][0])) {
        $distance = $routeData['routes'][0]['distance'];
        $duration = $routeData['routes'][0]['duration'];

        echo "Distância: " . round($distance / 1000, 2) . " km<br>";
        echo "Duração estimada: " . round($duration / 60, 1) . " min<br>";
    } else {
        echo "<span style='color:red;'>❌ Falha ao obter direção OSRM.</span>";
    }
} else {
    echo "<span style='color:red;'>❌ Falha ao geocodificar endereço.</span>";
}

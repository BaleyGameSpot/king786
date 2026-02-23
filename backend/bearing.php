<?php

function bearing($lat1_d, $lon1_d, $lat2_d, $lon2_d) {
    // Converte de graus para radianos
    $lat1 = deg2rad($lat1_d);
    $lon1 = deg2rad($lon1_d);
    $lat2 = deg2rad($lat2_d);
    $lon2 = deg2rad($lon2_d);

    // Calcula diferença de longitude
    $dLon = $lon2 - $lon1;

    // Calcula bearing
    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
    $bearingRad = atan2($y, $x);

    // Converte para graus
    $bearingDeg = rad2deg($bearingRad);

    // Normaliza para 0°–360°
    return fmod(($bearingDeg + 360), 360);
}

// Exemplo de uso
$lat1 = 23.013826;
$lon1 = 72.503887;
$lat2 = 23.026741;
$lon2 = 72.507664;

$bearing = bearing($lat1, $lon1, $lat2, $lon2);

echo "Bearing de ($lat1, $lon1) para ($lat2, $lon2): " . round($bearing, 2) . "°";

?>

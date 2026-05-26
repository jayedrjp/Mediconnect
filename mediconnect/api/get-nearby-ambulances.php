<?php
// api/get-nearby-ambulances.php
header('Content-Type: application/json');
require_once '../includes/functions.php';

$lat  = (float)($_GET['lat'] ?? 0);
$lng  = (float)($_GET['lng'] ?? 0);
$radius = (float)($_GET['radius'] ?? 10); // km

if (!$lat || !$lng) {
    echo json_encode(['error' => 'Latitude and longitude required.']);
    exit;
}

// Haversine formula in SQL
$ambulances = mysqli_query($conn,
    "SELECT *, (6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude)))) AS distance
     FROM ambulances
     WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND status = 'Available'
     HAVING distance <= $radius
     ORDER BY distance ASC LIMIT 10");

$result = [];
while ($a = mysqli_fetch_assoc($ambulances)) {
    $a['facilities'] = json_decode($a['facilities'] ?? '[]', true);
    $a['distance_km'] = round($a['distance'], 2);
    $result[] = $a;
}

echo json_encode(['success' => true, 'ambulances' => $result, 'count' => count($result)]);

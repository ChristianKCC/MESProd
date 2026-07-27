
<?php
$file = '../data/faces.json';
$data = json_decode(file_get_contents('php://input'), true);
$input_descriptor = $data['descriptor'];

$faces = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

function euclidean_distance($a, $b) {
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}
$min_distance = INF;
$matched_name = "No encontrado";
foreach ($faces as $face) {
    $distance = euclidean_distance($input_descriptor, $face['descriptor']);
    if ($distance < $min_distance && $distance < 0.4) { // Umbral de similitud
        $min_distance = $distance;
        $matched_name = $face['name'];
    }
}

echo $matched_name;
?>

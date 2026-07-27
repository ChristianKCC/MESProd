<?php
require __DIR__ . '/../catalogos.php';

// Zona horaria a Ciudad de México
date_default_timezone_set('America/Mexico_City');

$hoy = date('Y-m-d');

$guardado = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // TODO: validar y guardar en SQL Server (sqlsrv) repartiendo por bitácora/turno.
    $guardado = $_POST;
}

/* ---------- Helpers de presentación ---------- */
// Estructura para tablas y pasos indicados
// $ancho refiere a que tanto se le asigna a la tabla para ocupar mayor o menor espacio
function tileOpen(int $paso, string $titulo, bool $ancho = false, string $attrs = ''): string {
    $cls = $ancho ? 'tile span2' : 'tile';
    return "<section class=\"$cls\" $attrs><header class=\"tile-head\">"
         . "<span class=\"step\">$paso</span><h2>" . htmlspecialchars($titulo) . "</h2>"
         . "</header><div class=\"tile-body\">";
}
function tileClose(): string { return "</div></section>"; }

/** 3 inputs por turno (sección Producción de pacas en Tiempos). */
function turnoInputs(string $base): string {
    $h = '<div class="row g-2">';
    foreach (['1ero','2do','3ero'] as $t) {
        $h .= '<div class="col-4"><label class="form-label tlbl">'.strtoupper($t).'</label>'
            . inp("{$base}[{$t}]", 'data-sum="'.htmlspecialchars($base, ENT_QUOTES).'"')
            . '</div>';
    }
    return $h . '</div>';
}

// Valores ideales de presión de compactadores en Tiempos de Operacion y Tiempos Perdidos
$PRESION_DEFAULT = ['desechos' => '800', 'merma' => '800', 'recorte_panal' => '800', 'recorte_toalla' => '1200'];
?>
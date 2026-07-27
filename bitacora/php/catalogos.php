<?php
/**
 * _catalogos.php
 * Catálogos y helpers compartidos por los dos formatos
 * (Tiempos de Operación y Tiempos Pérdidos / Orden y Limpieza).
 *
 * Aquí defines una sola vez los equipos, plantas, áreas, turnos, etc.
 * Más adelante estos arreglos pueden venir de la base (SELECT ... FROM CATALOGO).
 */

/* ---- Catálogos ---- */

// Equipos para la sección DISPONIBLE (la fila vacía del formato físico se respeta con '')
$EQUIPOS_DISPONIBLE = ['BCM-1', 'BCM-3', 'BCM-4', 'PAO-01', 'PA-02', 'PA-04', 'MP25', ''];

// Equipos para PORCENTAJE / KILOGRAMOS DE RECUPERADO
$EQUIPOS_RECUPERADO = ['BCM-1', 'BCM-3', 'BCM-4', 'PAO-01', 'PA-02', 'PA-04', 'MP25'];

// Plantas (pacas recibidas / alimentadas)
$PLANTAS = ['TLAXCALA', 'RAMOS ARIZPE', 'PROSEDE', 'OGDEN', 'LAO'];

// Áreas de orden y limpieza
$AREAS_LIMPIEZA = [
    'MXA MEZANINE',
    'MXA CASETA',
    'MXB MEZANINE',
    'MXB CASETA',
    'SEPARACIÓN CCM',
    'PURIFICADOR SAM',
    'ESTACION DE SAM',
    'MEZANINE SEPARADORES',
    'SEPARACIÓN PISO',
    'AREA BOLSAS CONTENEDORES DE POLVO',
];

// Columnas de presión de los compactadores
$COLS_PRESION = [
    'desechos'       => 'DESECHOS',
    'merma'          => 'MERMA',
    'recorte_panal'  => 'RECORTE PAÑAL',
    'recorte_toalla' => 'RECORTE TOALLA',
];

// Turnos
$TURNOS = ['1ero', '2do', '3ero'];          // tres turnos
$TURNOS_RECIBIDAS = ['1er', '2do'];          // pacas recibidas usa solo 2 turnos

/* ---- Helpers ---- */

/**
 * Convierte un texto a una llave segura para usar en name="" de inputs.
 * Ej: "RAMOS ARIZPE" -> "ramos_arizpe", "SEPARACIÓN CCM" -> "separacion_ccm"
 */
function slug(string $s): string {
    // Quita acentos (mayúsculas y minúsculas) sin depender de mbstring.
    $acentos = [
        'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n',
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','%'=>'pct',
    ];
    $s = strtr(trim($s), $acentos);
    $s = strtolower($s);                       // ya es ASCII en este punto
    $s = preg_replace('/[^a-z0-9]+/', '_', $s);
    return trim($s, '_');
}

/** Input de texto compacto, centrado. */
function inp(string $name, string $extra = ''): string {
    $name = htmlspecialchars($name, ENT_QUOTES);
    return "<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm text-center\" autocomplete=\"off\" $extra>";
}

/** Checkbox simple. */
function chk(string $name): string {
    $name = htmlspecialchars($name, ENT_QUOTES);
    return "<input type=\"checkbox\" name=\"$name\" value=\"1\" class=\"form-check-input\">";
}

/* =================================================================
   Catálogos adicionales para Producciones W.R. y Peso de Bolsas
================================================================= */

// Máquinas (Producciones W.R. / Notas)
$WR_MAQUINAS = ['MXA-2/BCM-1', 'MXA-3/BCM-3', 'MXA-1/BCM-4', 'MXB-7/PAO-01', 'MXB-5/PA-02', 'MXB-8/PA-04'];

// Plantas como aparecen en Producciones W.R. (difieren un poco de la bitácora 1)
$WR_PLANTAS = ['TLAXCALA', 'RAMOS ARIZPE', 'PROSEDE', 'P.OGDEN', 'LATINOAMERICA (LAO)'];

// Tolvas para SAM recuperado
$WR_TOLVAS = [1, 2];

// Tipos de peso de bolsas
$PESO_TIPOS = ['TOALLA' => 'Peso de bolsas Toalla', 'PANAL' => 'Peso de bolsas Pañal'];

// Turnos con clave corta (para las bitácoras nuevas)
$TURNOS3 = ['t1' => '1er turno', 't2' => '2do turno', 't3' => '3er turno'];

/** Input numérico compacto. */
function inpNum(string $name, string $extra = '', string $val = ''): string {
    $name = htmlspecialchars($name, ENT_QUOTES);
    $val  = htmlspecialchars($val, ENT_QUOTES);
    return "<input type=\"text\" inputmode=\"decimal\" name=\"$name\" value=\"$val\" "
         . "class=\"form-control form-control-sm text-center\" autocomplete=\"off\" $extra>";
}
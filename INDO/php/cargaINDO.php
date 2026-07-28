<?php
$errorCarga = '';
try {
    $ss = cargar_spreadsheet($CFG['ruta_excel']);
    $ws = $ss->getSheetByName($CFG['hoja']);
    $wl = $ss->getSheetByName($CFG['hoja_listas']);
    $dropdowns = construir_dropdowns();

    // Cache de listas leídas
    $cacheListas = [];
    $puesto = limpiar($ws->getCell('D5')->getValue()) ?: limpiar($ws->getCell('B5')->getValue());
    $fEnc   = $CFG['fila_encabezado'];
} catch (\Throwable $e){
    $errorCarga = $e->getMessage();
    log_debug('ERROR carga index: ' . $e->getMessage());
}

// Render de un campo (columna)
function render_campo($col){
    global $CFG, $ws, $wl, $dropdowns, $cacheListas, $fEnc;
    if(in_array($col, $CFG['cols_auto'], true))    return '';
    if(in_array($col, $CFG['cols_formula'], true)) return '';

    $label = limpiar($ws->getCell($col . $fEnc)->getValue());
    if($label === '') return '';   // sin encabezado = no es campo

    $name = "campos[$col]";
    $id   = "c_$col";
    $html = '<div class="col-md-6 col-lg-4 mb-3">';
    $html .= '<label for="'.$id.'" class="form-label small fw-semibold">'.htmlspecialchars($label).' <span class="text-muted">('.$col.')</span></label>';

    if(isset($dropdowns[$col])){
        $rango = $dropdowns[$col];
        if(!isset($cacheListas[$rango])) $cacheListas[$rango] = leer_lista($wl, $rango);
        $html .= '<select class="form-select form-select-sm" id="'.$id.'" name="'.$name.'"><option value=""> Selecciona uno </option>';
        foreach($cacheListas[$rango] as $op)
            $html .= '<option value="'.htmlspecialchars($op).'">'.htmlspecialchars($op).'</option>';
        $html .= '</select>';
    } elseif(in_array($col, $CFG['cols_fecha'], true)){
        $html .= '<input type="date" class="form-control form-control-sm" id="'.$id.'" name="'.$name.'">';
    } elseif(in_array($col, $CFG['cols_numero'], true)){
        $html .= '<input type="number" step="1" class="form-control form-control-sm" id="'.$id.'" name="'.$name.'">';
    } elseif(in_array($col, $CFG['cols_textarea'], true)){
        $html .= '<textarea class="form-control form-control-sm" rows="2" id="'.$id.'" name="'.$name.'"></textarea>';
    } else {
        $html .= '<input type="text" class="form-control form-control-sm" id="'.$id.'" name="'.$name.'">';
    }
    $html .= '</div>';
    return $html;
}
<?php
// Configuración general para el manejo del excel
$CFG = [
    'ruta_excel'      => __DIR__ . '/../data/InventarioDeOperaciones.xlsx',
    'hoja'            => 'IO Puesto',
    'hoja_listas'    => 'Listas Desplegables',
    'fila_aspecto'    => 10,
    'fila_subgrupo'   => 11,
    'fila_encabezado' => 12,
    'fila_datos_ini'  => 13,
    'fila_plantilla'  => 13,   // fila modelo de la que se copian fórmulas/estilo
    'col_num'         => 'B',
    'col_ini'         => 'B',
    'col_fin'         => 'CW',
    'dir_logs'        => __DIR__ . '/logs',

    // Columnas que NO se piden en el formulario
    'cols_auto'       => ['B'],                                    // # de forma autoincremental
    'cols_formula'    => ['BM','BO','BQ','BS','BT','BU','BV','CU'],

    // Tipos especiales de input
    'cols_fecha'      => ['CS','CT'],
    'cols_numero'     => ['CV'],
    'cols_textarea'   => ['D','O','P','Q','R','CQ','CR'],

    // Layout de aspectos -> [titulo, colIni, colFin, subgrupos|null]
    'grupos' => [
        ['IDENTIFICACIÓN',                  'B','E', null],
        ['UBICACIÓN',                       'F','H', null],
        ['PUESTOS APLICABLES',              'I','N', null],
        ['ESCENARIOS DE RIESGO',            'O','R', null],
        ['ANÁLISIS DE RIESGOS', null, null, [
            ['ENERGÍAS INVOLUCRADAS','S','Y'],
            ['ENERGÍAS PELIGROSAS','Z','AA'],
            ['MAQUINARIA','AB','AG'],
            ['HERRAMIENTAS','AH','AK'],
            ['ERGONOMÍA','AL','AP'],
            ['FÍSICOS','AQ','AX'],
            ['QUÍMICOS','AY','BK'],
        ]],
        ['EVALUACIÓN DEL RIESGO',           'BL','BW', null],
        ['PERMISOS PARA TRABAJOS PELIGROSOS','BX','CF', null],
        ['ZONA ANATÓMICA / EPP',            'CG','CP', null],
        ['CONTROLES EXISTENTES',            'CQ','CQ', null],
        ['CONTROLES POR IMPLEMENTAR',       'CR','CR', null],
        ['CONTROL DOCUMENTAL',              'CS','CW', null],
    ],
];
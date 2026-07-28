<?php
/* ============================================================================
   Catálogos configurables desde el modal Personalizar.
   Lista blanca: solo estas tablas y columnas se pueden tocar.
   ============================================================================ */
function catalogosConfigurables()
{
    return [
        'categorias' => [
            'etiqueta' => 'Categorías de Peligro',
            'tabla' => 'Seg_CatCategoriaPeligro',
            'pk' => 'IdCategoria',
        ],
        'consecuencias' => [
            'etiqueta' => 'Consecuencias',
            'tabla' => 'Seg_CatConsecuencia',
            'pk' => 'IdConsecuencia',
        ],
        'mecanismos' => [
            'etiqueta' => 'Mecanismos',
            'tabla' => 'Seg_CatMecanismo',
            'pk' => 'IdMecanismo',
        ],
        'fuentes' => [
            'etiqueta' => 'Fuentes',
            'tabla' => 'Seg_CatFuente',
            'pk' => 'IdFuente',
        ],
    ];
}

function configOMorir($tipo)
{
    $mapa = catalogosConfigurables();
    if (!isset($mapa[$tipo])) {
        responderError("Tipo de configuración no válido");
    }
    return $mapa[$tipo];
}
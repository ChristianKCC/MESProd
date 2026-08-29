<?php
/**
 * transformarDatos()
 *
 * Convierte el array plano de la BD en estructura agrupada.
 * El SP debe regresar todos los registros del mes actual.
 * - Columnas D1-D7: USTD solo de los últimos 7 días
 * - AcumuladoUSTD: último registro del mes (fecha + turno más alto)
 *
 * @param array  $datos  Array plano de registros del SP
 * @param string $fecha  Fecha fin del período ('2026-05-10')
 * @return array
 */
function transformarDatos(array $datos, string $fecha, int $noDepto = 0): array
{
    // ── Configuración de máquinas por idCategoria ───────────────────────────
    // Define el orden y nombre a mostrar por categoría
    // Si la máquina no tiene datos ese día aparece igual con celdas vacías
    $configuracionMaquinas = [
        1 => [ // Pañal Infantil
            62 => 'PE10',
            63 => 'MP21',
            61 => 'MP23',
            60 => 'MP24',
            65 => 'MP25',
        ],
        2 => [ // Calzón Entrenador
            64 => 'MP22',
        ],
        3 => [], // SOAR
        4 => $noDepto === 24
            ? [ // Protección Femenina: solo N1
                138 => 'N1',
            ]
            : [ // Cuidado Infantil (y otros): PX y N2
                101 => 'PX',
                139 => 'N2',
            ],
        5 => [
            136 => 'W. Reclaim',
        ], // Waste Reclaim
        6 => [ // Pañal Abierto
            81  => 'PA01',
        ],
        7 => [ // Predoblado
            81  => 'PA01',
            82  => 'PA02',
            83  => 'PA03',
        ],
        8 => [ // Ropa Interior
            84  => 'PA04',
            97  => 'PA05',
        ],
        9 => [ // Toalla
            69  => 'MP03',
            70  => 'MP09',
            76  => 'MP12',
            73  => 'MP13',
            74  => 'MP14',
            137 => 'MP16',
        ],
        10 => [ // Panty
            75  => 'MP08',
            72  => 'MP11',
            77  => 'MP15',
        ],
        11 => [ // Lactancia
            68  => 'MP01',
        ],
        12 => [],
        13 => [],
        14 => [ // TABBI
            85  => 'TABBI',
        ],
        15 => [
            87 => 'SPOOLER1',
            90 => 'SPOOLER3',
        ], // Spooler
    ];
    // Rango visible en columnas (últimos 7 días)
    $fechaInicioRango = date('Y-m-d', strtotime($fecha . ' -6 days'));
    // Inicio del mes para AcumuladoUSTD
    $inicioMes        = date('Y-m-01', strtotime($fecha));

    // Fechas del encabezado — solo las del período de 7 días
    $fechas      = obtenerFechasPeriodo($datos, $fechaInicioRango);
    $ultimaFecha = !empty($fechas) ? max($fechas) : $fecha;

    $agrupado = [];

    // ── Acumulador global de turnos por NoMaquina (ignora categoría) ──
    // Sirve para calcular %TP y %Merma correctamente cuando una máquina
    // aparece en múltiples categorías (ej: PA01 en Predoblado y Pañal Abierto)
    $turnosPorMaquina = [];

    foreach ($datos as $row) {
        $cat      = $row['Categoria'] ?: 'Sin categoría';
        $maq      = $row['NombreMaquina'] ?? 'Sin máquina';
        $noMaq    = $row['NoMaquina'];
        $prod     = $row['Producto']      ?? 'Sin producto';
        $clave    = $row['Clave'];
        $etapa    = $row['Etapa']         ?? 'Sin etapa';
        $descripcion = $row['Descripcion'] ?? 'Sin descripción';
        $rowFecha = $row['Fecha'];
        $turno    = $row['Turno'];

        if (!isset($agrupado[$cat])) {
            $agrupado[$cat] = [];
            $agrupado[$cat]['_idCategoria'] = $row['idCategoria'] ?? null;
        }
        if (!isset($agrupado[$cat][$maq])) {
            $agrupado[$cat][$maq] = [
                'NoMaquina' => $noMaq,
                'productos' => [],
                '_turnos'   => [],
            ];
        }
        if (!isset($agrupado[$cat][$maq]['productos'][$prod]))
            $agrupado[$cat][$maq]['productos'][$prod] = ['claves' => []];

        if (!isset($agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave])) {
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave] = [
                'etapa'        => $clave . ' - ' . $descripcion,
                'dias'         => array_fill_keys($fechas, null),
                'acum'         => 0,
                '_ultimaFecha' => '',
                '_ultimoTurno' => 0,
            ];
        }

        // ── USTD por día: solo si está dentro de los últimos 7 días ──
        if ($rowFecha >= $fechaInicioRango) {
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] =
                ($agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] ?? 0)
                + $row['USTD'];
        }

        // ── AcumuladoUSTD: último registro del mes ──
        $reg = $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave];
        if ($rowFecha > $reg['_ultimaFecha'] ||
           ($rowFecha === $reg['_ultimaFecha'] && $turno > $reg['_ultimoTurno'])) {
            $acum = $rowFecha >= $inicioMes ? $row['AcumuladoUSTD'] : 0;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['acum']         = $acum;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['_ultimaFecha'] = $rowFecha;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['_ultimoTurno'] = $turno;
        }

        // ── Un registro por turno/día para %TP y %Merma (por categoría, para compatibilidad) ──
        $keyTurno = $rowFecha . '_' . $turno;
        if (!isset($agrupado[$cat][$maq]['_turnos'][$keyTurno])) {
            $agrupado[$cat][$maq]['_turnos'][$keyTurno] = [
                'Fecha'           => $rowFecha,
                'Turno'           => $turno,
                'TiempoAbajo'     => $row['TiempoAbajo'],
                'HorasTrabajadas' => (float)$row['HorasTrabajadas'],
                'Cortes'          => $row['Cortes'],
                'Rechazos'        => $row['Rechazos'],
            ];
        }

        // ── Acumular en el acumulador global por NoMaquina ──
        $noMaq = $row['NoMaquina'];
        if (!isset($turnosPorMaquina[$noMaq])) {
            $turnosPorMaquina[$noMaq] = [];
        }

        $keyTurnoGlobal = $rowFecha . '_' . $turno;

        if (!isset($turnosPorMaquina[$noMaq][$keyTurnoGlobal])) {
            $turnosPorMaquina[$noMaq][$keyTurnoGlobal] = [
                'Fecha'           => $rowFecha,
                'Turno'           => $turno,
                'TiempoAbajo'     => $row['TiempoAbajo'],
                'HorasTrabajadas' => (float)$row['HorasTrabajadas'],
                'Cortes'          => $row['Cortes'],
                'Rechazos'        => $row['Rechazos'],
                'TotalPiezas'     => $row['TotalPiezas'],
            ];
        }

        // ignorar registros sin categoría (basura) para el agrupado de producción
        if ($row['Categoria'] === null || $row['Categoria'] === '') {
            continue;
        }
    }

    // ── Reordenar máquinas y agregar vacías según configuración ─────────────
    foreach ($agrupado as $cat => &$maquinas) {
        $idCategoria = $maquinas['_idCategoria'] ?? null;
        unset($maquinas['_idCategoria']);

        if ($idCategoria !== null && isset($configuracionMaquinas[$idCategoria])) {
            $orden = $configuracionMaquinas[$idCategoria];
            $maquinasOrdenadas = [];

            foreach ($orden as $noMaq => $nombreMostrar) {
                // Buscar si esta máquina tiene datos
                $encontrada = null;
                foreach ($maquinas as $nombreMaq => $data) {
                    if (isset($data['NoMaquina']) && $data['NoMaquina'] == $noMaq) {
                        $encontrada = $nombreMaq;
                        break;
                    }
                }

                if ($encontrada !== null) {
                    // Tiene datos — usar sus datos pero con el nombre correcto
                    $maquinasOrdenadas[$nombreMostrar] = $maquinas[$encontrada];
                    $maquinasOrdenadas[$nombreMostrar]['NoMaquina'] = $noMaq;
                }
                // v2: si la máquina no tiene datos, ya NO se inserta como fila
                // vacía — se omite por completo para ahorrar espacio en el PDF.
            }
            $maquinas = $maquinasOrdenadas;
        }
    }
    unset($maquinas);

    // ── Calcular %TP, %Merma, Prom y Totales ──
    foreach ($agrupado as $cat => &$maquinas) {
        $totalOp = inicializarTotal($fechas);

        foreach ($maquinas as $maq => &$data) {
            $turnos = $data['_turnos'];

            // Usar turnos globales de la máquina para %TP y %Merma
            // Esto asegura que máquinas en múltiples categorías tengan el mismo valor
            $noMaqActual   = $data['NoMaquina'] ?? null;
            $turnosGlobales = ($noMaqActual !== null && isset($turnosPorMaquina[$noMaqActual]))
                ? $turnosPorMaquina[$noMaqActual]
                : $turnos; // fallback a turnos locales si no hay globales

            // %TP y %Merma Día (última fecha del período) — sobre 24 horas fijas
            $turnosDia         = array_filter($turnosGlobales, fn($t) => $t['Fecha'] === $ultimaFecha);
            $data['hrsT']      = calcularHrsT($turnosDia);
            $data['tp_dia']    = calcularTPDia($turnosDia);
            $data['merma_dia'] = calcularMerma($turnosDia);

            // %TP y %Merma Acum (mes completo)
            $turnosAcum         = array_filter($turnosGlobales, fn($t) => $t['Fecha'] >= $inicioMes);
            $data['tp_acum']    = calcularTPAcum($turnosAcum);
            $data['merma_acum'] = calcularMerma($turnosAcum);

            // Días trabajados = suma HorasTrabajadas del mes / 24
            $turnosAcumLocal = array_filter($turnos, fn($t) => $t['Fecha'] >= $inicioMes);
            $totalHorasMes  = array_sum(array_column(array_values($turnosAcumLocal), 'HorasTrabajadas'));
            $diasTrabajados = $totalHorasMes > 0 ? round($totalHorasMes / 24, 2) : 0;

            $totalMaq = inicializarTotal($fechas);

            // ── v2: colapsar las claves de cada producto en un solo total ──
            // El producto ya no expone el desglose por clave: sus valores por
            // día y su acumulado son la suma de todas las claves que lo
            // integran (pueden ser 1 o varias).
            foreach ($data['productos'] as &$producto) {
                $prodDias = array_fill_keys($fechas, null);
                $prodAcum = 0;

                foreach ($producto['claves'] as $info) {
                    foreach ($fechas as $f) {
                        $v = $info['dias'][$f] ?? null;
                        if ($v !== null) {
                            $prodDias[$f] = ($prodDias[$f] ?? 0) + $v;
                        }
                    }
                    $prodAcum += $info['acum'] ?? 0;
                }

                $producto['dias'] = $prodDias;
                $producto['acum'] = $prodAcum;
                // Prom = AcumuladoUSTD del producto / días trabajados en el mes
                $producto['prom'] = $diasTrabajados > 0 ? round($prodAcum / $diasTrabajados, 1) : 0;
                unset($producto['claves']);

                foreach ($fechas as $f) {
                    $totalMaq['dias'][$f] += $prodDias[$f] ?? 0;
                }
                $totalMaq['acum'] += $prodAcum;
            }
            unset($producto);

            // ── Eliminar productos sin ningún dato: ni en los 7 días ni acumulado ──
            // Si tiene acumulado del mes (aunque esta semana no produjo), se conserva.
            $data['productos'] = array_filter(
                $data['productos'],
                fn($p) => array_sum(array_map(fn($v) => (float)($v ?? 0), $p['dias'])) > 0
                    || ($p['acum'] ?? 0) > 0
            );

            // Prom total máquina = AcumuladoUSTD total / días trabajados
            $totalMaq['prom'] = $diasTrabajados > 0
                ? round($totalMaq['acum'] / $diasTrabajados, 1)
                : 0;

            $data['total_maquina']    = $totalMaq;
            $data['_diasTrabajados']  = $diasTrabajados;
            unset($data['_turnos']);

            foreach ($fechas as $f) {
                $totalOp['dias'][$f] += $totalMaq['dias'][$f];
            }
            $totalOp['acum']         += $totalMaq['acum'];
            $totalOp['_diasTotales'] += $diasTrabajados;
            // Acumular HRS T sumando las horas de cada máquina individualmente
            $hrsNum = is_numeric($data['hrsT']) ? (float)$data['hrsT'] : 0;
            $totalOp['_hrsTTotal']   += $hrsNum;
        }
        unset($data);

        // Prom total operación = AcumuladoUSTD total / suma días trabajados de todas las máquinas
        $totalOp['prom'] = $totalOp['_diasTotales'] > 0
            ? round($totalOp['acum'] / $totalOp['_diasTotales'], 1)
            : 0;

        // HRS T total = suma de hrsT de cada máquina
        $totalOp['hrsT'] = $totalOp['_hrsTTotal'] > 0
            ? number_format($totalOp['_hrsTTotal'], 1)
            : '—';

        // %TP y %Merma acumulados de toda la categoría
        $turnosCatDia  = [];
        $turnosCatAcum = [];
        foreach ($maquinas as $maq => $data) {
            if ($maq === '_total_operacion' || !isset($data['NoMaquina'])) continue;
            $noMaqCat   = $data['NoMaquina'];
            $turnosGlob = isset($turnosPorMaquina[$noMaqCat]) ? $turnosPorMaquina[$noMaqCat] : [];
            foreach ($turnosGlob as $key => $t) {
                if ($t['Fecha'] === $ultimaFecha) $turnosCatDia[$noMaqCat . '_' . $key]  = $t;
                if ($t['Fecha'] >= $inicioMes)    $turnosCatAcum[$noMaqCat . '_' . $key] = $t;
            }
        }
        $totalOp['tp_dia']    = calcularTPDia($turnosCatDia);
        $totalOp['tp_acum']   = calcularTPAcum($turnosCatAcum);
        $totalOp['merma_dia'] = calcularMerma($turnosCatDia);
        $totalOp['merma_acum']= calcularMerma($turnosCatAcum);

        $maquinas['_total_operacion'] = $totalOp;
    }
    unset($maquinas);

    // ── Filtrar máquinas sin productos y categorías vacías ──
    foreach ($agrupado as $cat => &$maquinas) {
        foreach ($maquinas as $maq => &$data) {
            if ($maq === '_total_operacion') continue;
            // v2: eliminar cualquier máquina sin productos (ya no existen
            // máquinas "forzadas" — se omiten desde el paso anterior).
            if (empty($data['productos'])) {
                unset($maquinas[$maq]);
            }
        }
        unset($data);

        // Eliminar categoría "Sin categoría" si todas sus máquinas quedaron vacías
        // o si la categoría es literalmente "Sin categoría"
        $maquinasSinTotal = array_filter(
            $maquinas,
            fn($k) => $k !== '_total_operacion',
            ARRAY_FILTER_USE_KEY
        );
        if ($cat === 'Sin categoría' && empty($maquinasSinTotal)) {
            unset($agrupado[$cat]);
        }
    }
    unset($maquinas);

    // ── Eliminar completamente la categoría "Sin categoría" ──
    // (máquinas con Categoria=NULL del SP no deben aparecer en el reporte)
    unset($agrupado['Sin categoría']);

    return [
        'fechas' => $fechas,
        'tablas' => $agrupado,
    ];
}

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Extrae solo las fechas dentro del período de 7 días
 */
/**
 * Obtiene el rango de 7 días previos (incluyendo la fecha dada)
 * SIEMPRE devuelve 7 fechas, aunque algún día no tenga registros con datos
 * 
 * Esto garantiza que todas las columnas de días se dibujen en la tabla,
 * incluso si algún día no tuvo producción.
 */
function obtenerFechasPeriodo(array $datos, string $fechaInicio): array
{
    // Generar siempre los 7 días del rango, sin depender de los datos
    // Este patrón es el mismo que en obtenerFechasPeriodoTabbi() y obtenerFechasPeriodoSpooler()
    $fechas = [];
    $cursor = strtotime($fechaInicio);
    for ($i = 0; $i < 7; $i++) {
        $fechas[] = date('Y-m-d', $cursor);
        $cursor = strtotime('+1 day', $cursor);
    }
    return $fechas;
}

function inicializarTotal(array $fechas): array
{
    return ['dias' => array_fill_keys($fechas, 0), 'acum' => 0, 'prom' => 0, '_diasTotales' => 0, '_hrsTTotal' => 0];
}

/**
 * HRS T = suma de HorasTrabajadas del día seleccionado (turnos únicos)
 */
function calcularHrsT(array $turnos): string
{
    if (empty($turnos)) return '—';

    $unicos = [];
    foreach ($turnos as $t) {
        $key = $t['Fecha'] . '_' . $t['Turno'];
        if (!isset($unicos[$key])) {
            $unicos[$key] = (float)($t['HorasTrabajadas'] ?? 0);
        }
    }

    $total = array_sum($unicos);
    return $total > 0 ? number_format($total, 1) : '0.0';
}

/**
 * %TP Día = TiempoAbajo total / 60 / 24 (día siempre es 24 horas)
 */

function calcularTPDia(array $turnos): string
{
    if (empty($turnos)) return '—'; // sin producción/turnos ese día

    // Nota: NO se vuelve a deduplicar por Fecha+Turno aquí, porque $turnos
    // puede venir de varias máquinas combinadas (total de categoría) y una
    // llave Fecha+Turno sin la máquina pisaría los datos de una máquina con
    // los de otra. turnosPorMaquina ya viene deduplicado por máquina+turno
    // desde su construcción, así que sumar directamente es seguro.
    $totalTP  = array_sum(array_column($turnos, 'TiempoAbajo'));
    $totalHrs = array_sum(array_column($turnos, 'HorasTrabajadas'));

    if ($totalHrs <= 0) return '0.00%'; // hubo turno/producción, pero sin horas registradas

    return number_format(($totalTP / ($totalHrs * 60)) * 100, 2) . '%';
    // return number_format($totalTP, 2);
    
}


/**
 * %TP Acum = TiempoAbajo total / 60 / (días trabajados * 24)
 */

function calcularTPAcum(array $turnos): string
{
    if (empty($turnos)) return '—'; // sin producción/turnos en el mes

    // Ver nota de calcularTPDia: no se re-deduplica por Fecha+Turno aquí.
    $totalTP  = array_sum(array_column($turnos, 'TiempoAbajo'));
    $totalHrs = array_sum(array_column($turnos, 'HorasTrabajadas'));

    if ($totalHrs <= 0) return '0.00%'; // hubo turno/producción, pero sin horas registradas

    $diasTrabajados = $totalHrs / 24;

    return number_format((($totalTP / 60) / ($diasTrabajados * 24)) * 100, 2) . '%';
    // return number_format($totalTP, 2);
}


/**
 * @deprecated Usar calcularTPDia o calcularTPAcum
 */
function calcularTP(array $turnos): string
{
    return calcularTPAcum($turnos);
}

function calcularMerma(array $turnos): string
{
    if (empty($turnos)) return '—'; // sin producción/turnos en el periodo

    $totalPiezas = array_sum(array_column($turnos, 'TotalPiezas'));
    $totalCortes   = array_sum(array_column($turnos, 'Cortes'));
    if ($totalCortes <= 0) return '0.00%'; // hubo producción, pero sin cortes registrados
    return number_format((1 - ($totalPiezas / $totalCortes)) * 100, 2) . '%';
    // return number_format($totalPiezas, 2);
}
<?php
/**
 * transformarTabbi()
 *
 * Convierte el array plano del SP de TABBI en estructura agrupada.
 * Similar a transformarDatos() pero con métricas propias:
 * - USTD    → MetrosCuadrados (MM²)
 * - Acum    → TotalMC (acumulado MM² del mes)
 * - Cortes  → TotalML (Metros Lineales, para %TP)
 * - Rechazos→ KGSRechazados (para %Merma)
 * - Reales  → PesoTotal (KG, denominador %Merma)
 *
 * @param array  $datos  Array plano del SP
 * @param string $fecha  Fecha fin del período ('2026-05-21')
 * @return array
 */
function transformarTabbi(array $datos, string $fecha): array
{
    $fechaInicioRango = date('Y-m-d', strtotime($fecha . ' -6 days'));
    $inicioMes        = date('Y-m-01', strtotime($fecha));

    // Fechas del encabezado — solo las del período de 7 días
    $fechas      = obtenerFechasPeriodoTabbi($datos, $fechaInicioRango);
    $ultimaFecha = !empty($fechas) ? max($fechas) : $fecha;

    $agrupado = [];
    $turnosPorMaquina = [];

    foreach ($datos as $row) {
        $cat         = $row['Categoria']     ?? 'TABBI';
        $maq         = $row['NombreMaquina'] ?? 'TABBI';
        $noMaq       = $row['NoMaquina'];
        $prod        = $row['Producto']      ?? 'Sin producto';
        $clave       = $row['Clave'];
        $descripcion = $row['Descripcion']   ?? 'Sin descripción';
        $rowFecha    = $row['Fecha'];
        $turno       = $row['Turno'];

        if (!isset($agrupado[$cat])) {
            $agrupado[$cat] = [];
        }
        if (!isset($agrupado[$cat][$maq])) {
            $agrupado[$cat][$maq] = [
                'NoMaquina' => $noMaq,
                'productos' => [],
                '_turnos'   => [],
            ];
        }
        if (!isset($agrupado[$cat][$maq]['productos'][$prod])) {
            $agrupado[$cat][$maq]['productos'][$prod] = ['claves' => []];
        }
        if (!isset($agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave])) {
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave] = [
                'etapa'        => $clave . ' - ' . $descripcion,
                'dias'         => array_fill_keys($fechas, null),
                'acum'         => 0,
                '_ultimaFecha' => '',
                '_ultimoTurno' => 0,
            ];
        }

        // MM² por día
        if ($rowFecha >= $fechaInicioRango) {
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] =
                round(($agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] ?? 0)
                + (float)$row['MetrosCuadrados'], 3);
        }

        // Acumulado MM² del mes — último registro
        $reg = $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave];
        if ($rowFecha > $reg['_ultimaFecha'] ||
           ($rowFecha === $reg['_ultimaFecha'] && $turno > $reg['_ultimoTurno'])) {
            $acum = $rowFecha >= $inicioMes ? (float)($row['MC'] ?? 0) : 0;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['acum']         = $acum;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['_ultimaFecha'] = $rowFecha;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['_ultimoTurno'] = $turno;
        }

        // Turnos para %TP y %Merma
        //
        // IMPORTANTE: un mismo turno (Fecha+Turno) puede tener varias filas,
        // una por cada clave/producto trabajado en ese turno. TiempoAbajo y
        // HorasTrabajadas son valores a NIVEL TURNO (el SP los repite igual,
        // o los pone en 0 en filas duplicadas), así que se fijan una sola vez
        // con la primera fila del turno. Pero MetrosCuadrados, KGSRechazados,
        // PesoTotal y ML SÍ cambian por fila porque son la producción de esa
        // clave específica dentro del turno — si se toman solo de la primera
        // fila (como antes), se pierde la producción de las claves adicionales
        // que comparten turno, y el acumulado de la máquina queda por debajo
        // del real.
        $keyTurno = $rowFecha . '_' . $turno;
        if (!isset($agrupado[$cat][$maq]['_turnos'][$keyTurno])) {
            $agrupado[$cat][$maq]['_turnos'][$keyTurno] = [
                'Fecha'            => $rowFecha,
                'Turno'            => $turno,
                'TiempoAbajo'      => (int)($row['TiempoAbajo'] ?? 0),
                'HorasTrabajadas'  => (float)($row['HorasTrabajadas'] ?? 0),
                'ML'               => 0.0,
                'MetrosCuadrados'  => 0.0,
                'KGSRechazados'    => 0.0,
                'PesoTotal'        => 0.0,
            ];
        }
        $agrupado[$cat][$maq]['_turnos'][$keyTurno]['ML']              += (float)($row['MetrosLineales'] ?? 0);
        $agrupado[$cat][$maq]['_turnos'][$keyTurno]['MetrosCuadrados'] += (float)($row['MetrosCuadrados'] ?? 0);
        $agrupado[$cat][$maq]['_turnos'][$keyTurno]['KGSRechazados']   += (float)($row['KGSRechazados'] ?? 0);
        $agrupado[$cat][$maq]['_turnos'][$keyTurno]['PesoTotal']       += (float)($row['Kilogramos'] ?? 0);
    }

    // ── Construir turnosPorMaquina a partir de los _turnos ya agregados ──────
    // Se hace en un segundo paso (en vez de fila por fila) para que no dependa
    // de si la fila que llega primero trae HorasTrabajadas en 0 o distinto de
    // 0 — así ninguna clave se pierde por el orden en que vienen las filas del SP.
    foreach ($agrupado as $catTmp => $maquinasTmp) {
        foreach ($maquinasTmp as $maqTmp => $dataTmp) {
            $noMaqTmp = $dataTmp['NoMaquina'] ?? null;
            if ($noMaqTmp === null || empty($dataTmp['_turnos'])) {
                continue;
            }
            if (!isset($turnosPorMaquina[$noMaqTmp])) {
                $turnosPorMaquina[$noMaqTmp] = [];
            }
            foreach ($dataTmp['_turnos'] as $keyTurnoTmp => $t) {
                if (!isset($turnosPorMaquina[$noMaqTmp][$keyTurnoTmp])) {
                    $turnosPorMaquina[$noMaqTmp][$keyTurnoTmp] = $t;
                } else {
                    // El mismo turno aparece agrupado bajo más de una
                    // categoría/producto para esta máquina: sumar las
                    // métricas por-clave, no duplicar TiempoAbajo/HorasTrabajadas.
                    $turnosPorMaquina[$noMaqTmp][$keyTurnoTmp]['MetrosCuadrados'] += $t['MetrosCuadrados'];
                    $turnosPorMaquina[$noMaqTmp][$keyTurnoTmp]['KGSRechazados']   += $t['KGSRechazados'];
                    $turnosPorMaquina[$noMaqTmp][$keyTurnoTmp]['PesoTotal']       += $t['PesoTotal'];
                    $turnosPorMaquina[$noMaqTmp][$keyTurnoTmp]['ML']             += $t['ML'];
                }
            }
        }
    }

    // ── Asegurar que TABBI siempre aparezca, aunque no tenga producción ──────
    $maquinasEsperadas = [85 => 'TABBI'];

    if (empty($agrupado)) {
        $agrupado['TABBI'] = [];
    }
    $primeraCat = array_key_first($agrupado);

    foreach ($maquinasEsperadas as $noMaq => $nombre) {
        $existe = false;
        foreach ($agrupado as $cat => $maquinas) {
            foreach ($maquinas as $data) {
                if (isset($data['NoMaquina']) && $data['NoMaquina'] == $noMaq) {
                    $existe = true;
                    break 2;
                }
            }
        }
        if (!$existe) {
            $agrupado[$primeraCat][$nombre] = [
                'NoMaquina' => $noMaq,
                'productos' => [],
                '_turnos'   => [],
            ];
        }
    }

    // Calcular %TP, %Merma, Prom y Totales
    foreach ($agrupado as $cat => &$maquinas) {
        $totalOp = inicializarTotalTabbi($fechas);

        foreach ($maquinas as $maq => &$data) {
            $turnos = $data['_turnos'];
            $noMaqActual = $data['NoMaquina'] ?? null;
            $turnosGlobales = ($noMaqActual !== null && isset($turnosPorMaquina[$noMaqActual]))
                ? $turnosPorMaquina[$noMaqActual]
                : $turnos;

            // %TP Día y Acum
            $turnosDia  = array_filter($turnosGlobales, fn($t) => $t['Fecha'] === $ultimaFecha);
            $turnosAcum = array_filter($turnosGlobales, fn($t) => $t['Fecha'] >= $inicioMes);

            $data['tp_day_hrs'] = calcularHrsTTabbi($turnosDia);
            $data['tp_dia']    = calcularTPDiaTabbi($turnosDia);
            $data['tp_acum']   = calcularTPAcumTabbi($turnosAcum);
            $data['merma_dia'] = calcularMermaTabbi($turnosDia);
            $data['merma_acum']= calcularMermaTabbi($turnosAcum);

            // Días trabajados
            $turnosAcumLocal = array_filter($turnos, fn($t) => $t['Fecha'] >= $inicioMes);
            $totalHrs        = array_sum(array_column(array_values($turnosAcumLocal), 'HorasTrabajadas'));
            $diasTrabajados  = $totalHrs > 0 ? round($totalHrs / 24, 2) : 0;

            $totalMaq = inicializarTotalTabbi($fechas);

            foreach ($data['productos'] as &$producto) {
                foreach ($producto['claves'] as &$info) {
                    $info['prom'] = $diasTrabajados > 0
                        ? round($info['acum'] / $diasTrabajados, 3)
                        : 0;

                    foreach ($fechas as $f) {
                        $totalMaq['dias'][$f] = round(($totalMaq['dias'][$f] ?? 0) + ($info['dias'][$f] ?? 0), 3);
                    }
                    $totalMaq['acum'] += $info['acum'] ?? 0;

                    unset($info['_ultimaFecha'], $info['_ultimoTurno']);
                }
                unset($info);

                // Filtrar claves sin datos en los 7 días
                $producto['claves'] = array_filter(
                    $producto['claves'],
                    fn($info) => array_sum(array_map(fn($v) => (float)($v ?? 0), $info['dias'])) > 0
                );
            }
            unset($producto);

            // Eliminar productos sin claves
            $data['productos'] = array_filter(
                $data['productos'],
                fn($p) => !empty($p['claves'])
            );

            // Acum total de la máquina = suma de MetrosCuadrados del mes desde turnos
            $turnosAcumMC = array_filter($turnosGlobales, fn($t) => $t['Fecha'] >= $inicioMes);
            $totalMaq['acum'] = round(array_sum(array_column(array_values($turnosAcumMC), 'MetrosCuadrados')), 3);

            $totalMaq['prom'] = $diasTrabajados > 0
                ? round($totalMaq['acum'] / $diasTrabajados, 3)
                : 0;

            $data['total_maquina']   = $totalMaq;
            $data['_diasTrabajados'] = $diasTrabajados;
            unset($data['_turnos']);

            foreach ($fechas as $f) {
                $totalOp['dias'][$f] = round(($totalOp['dias'][$f] ?? 0) + ($totalMaq['dias'][$f] ?? 0), 3);
            }
            $totalOp['acum']         += $totalMaq['acum'];
            $totalOp['_diasTotales'] += $diasTrabajados;
            $hrsNum = is_numeric($data['tp_day_hrs']) ? (float)$data['tp_day_hrs'] : 0;
            $totalOp['_hrsTTotal']   += $hrsNum;
        }
        unset($data);

        $totalOp['prom'] = $totalOp['_diasTotales'] > 0
            ? round($totalOp['acum'] / $totalOp['_diasTotales'], 3)
            : 0;

        $totalOp['tp_day_hrs'] = $totalOp['_hrsTTotal'] > 0
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
        $totalOp['tp_dia']    = calcularTPDiaTabbi($turnosCatDia);
        $totalOp['tp_acum']   = calcularTPAcumTabbi($turnosCatAcum);
        $totalOp['merma_dia'] = calcularMermaTabbi($turnosCatDia);
        $totalOp['merma_acum']= calcularMermaTabbi($turnosCatAcum);

        $maquinas['_total_operacion'] = $totalOp;
    }
    unset($maquinas);

    return [
        'fechas' => $fechas,
        'tablas' => $agrupado,
    ];
}

// ── Helpers ──────────────────────────────────────────────────────────────────

function obtenerFechasPeriodoTabbi(array $datos, string $fechaInicio): array
{
    // Generar siempre los 7 días del rango, aunque algún día no tenga registros
    $fechas = [];
    $cursor = strtotime($fechaInicio);
    for ($i = 0; $i < 7; $i++) {
        $fechas[] = date('Y-m-d', $cursor);
        $cursor = strtotime('+1 day', $cursor);
    }
    return $fechas;
}

function inicializarTotalTabbi(array $fechas): array
{
    return ['dias' => array_fill_keys($fechas, 0), 'acum' => 0, 'prom' => 0, '_diasTotales' => 0, '_hrsTTotal' => 0];
}


function calcularHrsTTabbi(array $turnos): string
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

function calcularTPDiaTabbi(array $turnos): string
{
    if (empty($turnos)) return '-'; // sin producción/turnos ese día

    // No se re-deduplica por Fecha+Turno aquí: $turnos puede venir de varias
    // máquinas combinadas (total de la operación) y una llave sin la máquina
    // pisaría los datos de una con los de otra. turnosPorMaquina ya viene
    // deduplicado por máquina+turno desde su construcción.
    $totalTA  = array_sum(array_column($turnos, 'TiempoAbajo'));
    $totalHrs = array_sum(array_column($turnos, 'HorasTrabajadas'));

    if ($totalHrs <= 0) return '0.00%'; // hubo turno/producción, pero sin horas registradas

    return number_format(($totalTA / ($totalHrs * 60)) * 100, 2) . '%';
}



function calcularTPAcumTabbi(array $turnos): string
{
    if (empty($turnos)) return '-'; // sin producción/turnos en el mes

    // Ver nota de calcularTPDiaTabbi: no se re-deduplica por Fecha+Turno aquí.
    $totalTA  = array_sum(array_column($turnos, 'TiempoAbajo'));
    $totalHrs = array_sum(array_column($turnos, 'HorasTrabajadas'));

    if ($totalHrs <= 0) return '0.00%'; // hubo turno/producción, pero sin horas registradas

    $dias = $totalHrs / 24;

    return number_format(($totalTA / 60 / ($dias * 24)) * 100, 2) . '%';
}


function calcularMermaTabbi(array $turnos): string
{
    if (empty($turnos)) return '-'; // sin producción/turnos en el periodo

    $kgsRechazados = array_sum(array_column($turnos, 'KGSRechazados'));
    $pesoTotal     = array_sum(array_column($turnos, 'PesoTotal'));
    if ($pesoTotal <= 0) return '0.00%'; // hubo producción, pero sin peso registrado
    return number_format(($kgsRechazados / $pesoTotal) * 100, 2) . '%';
}
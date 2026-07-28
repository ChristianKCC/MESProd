<?php
/**
 * transformarHook()
 *
 * Convierte el array plano del SP sp_PRSD_ObtenerProduccionHook_ConTiempos
 * en la misma estructura agrupada que usan TABBI y Spooler, para que
 * PdfGenerator::agregarTabbi() pueda dibujarla sin cambios.
 *
 * Notas de negocio (confirmadas con Christian):
 * - La máquina Hook (MS01, NoMaquina 67) pertenece administrativamente al
 *   NoDepto 1 (Cuidado Infantil), pero se reporta dentro del bloque
 *   "TELAS NO TEJIDAS", después de Spooler. Por eso $cat se fija en 'Hook'
 *   en vez de usar el campo Categoria que trae el SP (ese campo trae la
 *   categoría real de producto, ej. "SOAR").
 * - No existe una columna de acumulado de mes tipo AcMMC (como en Tabbi/
 *   Spooler). El acumulado se calcula sumando TotalMC (que ya es el total
 *   de MM² de cada turno) de todas las filas del mes.
 * - %Merma queda PENDIENTE (no hay columna de peso total en el SP todavía)
 *   → siempre se muestra '-'. Cuando Christian agregue esa columna, solo
 *   hay que reemplazar calcularMermaHook() por la misma lógica de Spooler.
 * - %TP: si la máquina tuvo turnos registrados ese día/mes pero el
 *   resultado da 0 (ej. HorasTrabajadas viene en 0), se muestra "0.00%".
 *   Solo se muestra "-" cuando NO hay ningún turno registrado en el
 *   periodo (es decir, la máquina no tuvo producción).
 *
 * @param array  $datos  Array plano mapeado desde mapearFilaHook()
 * @param string $fecha  Fecha fin del período ('2026-07-05')
 * @return array
 */
function transformarHook(array $datos, string $fecha): array
{
    $fechaInicioRango = date('Y-m-d', strtotime($fecha . ' -6 days'));
    $inicioMes        = date('Y-m-01', strtotime($fecha));

    $fechas      = obtenerFechasPeriodoHook($fechaInicioRango);
    $ultimaFecha = !empty($fechas) ? max($fechas) : $fecha;

    $agrupado         = [];
    $turnosPorMaquina = [];

    foreach ($datos as $row) {
        // ── Sección fija: siempre "Hook", sin importar la Categoria real del producto ──
        $cat         = 'SOAR';
        $maq         = $row['NombreMaquina'] ?? 'MS01';
        $noMaq       = $row['NoMaquina'];
        $prod        = $row['Producto']      ?? 'Sin producto';
        $clave       = $row['Clave'];
        $descripcion = $row['Descripcion']   ?? 'Sin descripción';
        $rowFecha    = $row['Fecha'];
        $turno       = $row['Turno'];

        $tieneMC = $row['MetrosCuadrados'] !== null;

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
                'etapa' => $clave . ' - ' . $descripcion,
                'dias'  => array_fill_keys($fechas, null),
                'acum'  => 0,
            ];
        }

        // ── MM² por día (solo dentro de la ventana de 7 días) ──
        if ($tieneMC && $rowFecha >= $fechaInicioRango) {
            $prev = $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] ?? 0;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] =
                round((float)$prev + (float)$row['MetrosCuadrados'], 3);
        }

        // ── Acumulado del mes: suma de TotalMC (ya viene como total del turno) ──
        if ($tieneMC && $rowFecha >= $inicioMes) {
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['acum'] += (float)$row['MetrosCuadrados'];
        }

        // ── Turnos para %TP y %Merma ──
        // El SP ya resuelve el duplicado por folio dentro del mismo turno
        // (TiempoAbajo/HorasTrabajadas vienen en 0 salvo en la primera fila
        // del turno), así que sumar directamente es seguro.
        $keyTurno = $rowFecha . '_' . $turno;
        if (!isset($turnosPorMaquina[$noMaq])) {
            $turnosPorMaquina[$noMaq] = [];
        }
        if (!isset($turnosPorMaquina[$noMaq][$keyTurno])) {
            $turnosPorMaquina[$noMaq][$keyTurno] = [
                'Fecha'           => $rowFecha,
                'Turno'           => $turno,
                'TiempoAbajo'     => (int)($row['TiempoAbajo'] ?? 0),
                'HorasTrabajadas' => (float)($row['HorasTrabajadas'] ?? 0),
                'MetrosLineales'  => (float)($row['MetrosLineales'] ?? 0),
                'KGSRechazados'   => (float)($row['KGSRechazados'] ?? 0),
            ];
        } else {
            // Salvaguarda: si por alguna razón llega otra fila del mismo turno
            // con horas/tiempo abajo (no debería pasar por el RN_Turno del SP).
            $turnosPorMaquina[$noMaq][$keyTurno]['TiempoAbajo']     += (int)($row['TiempoAbajo'] ?? 0);
            $turnosPorMaquina[$noMaq][$keyTurno]['HorasTrabajadas'] += (float)($row['HorasTrabajadas'] ?? 0);
            $turnosPorMaquina[$noMaq][$keyTurno]['MetrosLineales']  += (float)($row['MetrosLineales'] ?? 0);
            $turnosPorMaquina[$noMaq][$keyTurno]['KGSRechazados']   += (float)($row['KGSRechazados'] ?? 0);
        }
    }

    // ── Asegurar que MS01 siempre aparezca, aunque no tenga producción ──
    $maquinasEsperadas = [67 => 'MS01'];

    if (empty($agrupado)) {
        $agrupado['Hook'] = [];
    }
    $primeraCat = array_key_first($agrupado) ?? 'Hook';
    if (!isset($agrupado[$primeraCat])) {
        $agrupado[$primeraCat] = [];
    }

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

    // ── Calcular %TP, %Merma (pendiente), Prom y Totales ──
    foreach ($agrupado as $cat => &$maquinas) {
        $totalOp = inicializarTotalHook($fechas);

        foreach ($maquinas as $maq => &$data) {
            $noMaqActual    = $data['NoMaquina'] ?? null;
            $turnosGlobales = ($noMaqActual !== null && isset($turnosPorMaquina[$noMaqActual]))
                ? $turnosPorMaquina[$noMaqActual]
                : [];

            $turnosDia  = array_filter($turnosGlobales, fn($t) => $t['Fecha'] === $ultimaFecha);
            $turnosAcum = array_filter($turnosGlobales, fn($t) => $t['Fecha'] >= $inicioMes);

            $data['tp_day_hrs'] = calcularHrsTHook($turnosDia);
            $data['tp_dia']     = calcularTPDiaHook($turnosDia);
            $data['tp_acum']    = calcularTPAcumHook($turnosAcum);
            $data['merma_dia']  = calcularMermaHook($turnosDia);   // pendiente → '-'
            $data['merma_acum'] = calcularMermaHook($turnosAcum);  // pendiente → '-'

            // Días trabajados del mes (para Prom)
            $totalHrs       = array_sum(array_column(array_values($turnosAcum), 'HorasTrabajadas'));
            $diasTrabajados = $totalHrs > 0 ? round($totalHrs / 24, 2) : 0;

            $totalMaq = inicializarTotalHook($fechas);

            // ── v2: colapsar las claves de cada producto en un solo total ──
            foreach ($data['productos'] as &$producto) {
                $prodDias = array_fill_keys($fechas, null);
                $prodAcum = 0;

                foreach ($producto['claves'] as $info) {
                    foreach ($fechas as $f) {
                        $v = $info['dias'][$f] ?? null;
                        if ($v !== null) {
                            $prodDias[$f] = round(($prodDias[$f] ?? 0) + $v, 3);
                        }
                    }
                    $prodAcum += $info['acum'] ?? 0;
                }

                $producto['dias'] = $prodDias;
                $producto['acum'] = round($prodAcum, 3);
                $producto['prom'] = $diasTrabajados > 0
                    ? round($prodAcum / $diasTrabajados, 3)
                    : 0;
                unset($producto['claves']);

                foreach ($fechas as $f) {
                    $totalMaq['dias'][$f] = round(
                        ($totalMaq['dias'][$f] ?? 0) + ($prodDias[$f] ?? 0),
                        3
                    );
                }
                $totalMaq['acum'] += $prodAcum;
            }
            unset($producto);

            // ── Eliminar productos sin ningún dato en los 7 días ──
            $data['productos'] = array_filter(
                $data['productos'],
                fn($p) => array_sum(array_map(fn($v) => (float)($v ?? 0), $p['dias'])) > 0
                    || ($p['acum'] ?? 0) > 0
            );

            $totalMaq['prom'] = $diasTrabajados > 0
                ? round($totalMaq['acum'] / $diasTrabajados, 3)
                : 0;

            $data['total_maquina']   = $totalMaq;
            $data['_diasTrabajados'] = $diasTrabajados;
            unset($data['_turnos']);

            foreach ($fechas as $f) {
                $totalOp['dias'][$f] = round(
                    ($totalOp['dias'][$f] ?? 0) + ($totalMaq['dias'][$f] ?? 0),
                    3
                );
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

        $turnosCatDia  = [];
        $turnosCatAcum = [];
        foreach ($maquinas as $maq => $data) {
            if (!isset($data['NoMaquina'])) continue;
            $noMaqCat   = $data['NoMaquina'];
            $turnosGlob = $turnosPorMaquina[$noMaqCat] ?? [];
            foreach ($turnosGlob as $key => $t) {
                if ($t['Fecha'] === $ultimaFecha) $turnosCatDia[$noMaqCat . '_' . $key]  = $t;
                if ($t['Fecha'] >= $inicioMes)    $turnosCatAcum[$noMaqCat . '_' . $key] = $t;
            }
        }
        $totalOp['tp_dia']     = calcularTPDiaHook($turnosCatDia);
        $totalOp['tp_acum']    = calcularTPAcumHook($turnosCatAcum);
        $totalOp['merma_dia']  = calcularMermaHook($turnosCatDia);
        $totalOp['merma_acum'] = calcularMermaHook($turnosCatAcum);

        $maquinas['_total_operacion'] = $totalOp;
    }
    unset($maquinas);

    return [
        'fechas' => $fechas,
        'tablas' => $agrupado,
    ];
}

// ── Helpers ──────────────────────────────────────────────────────────────────

function obtenerFechasPeriodoHook(string $fechaInicio): array
{
    $fechas = [];
    $cursor = strtotime($fechaInicio);
    for ($i = 0; $i < 7; $i++) {
        $fechas[] = date('Y-m-d', $cursor);
        $cursor = strtotime('+1 day', $cursor);
    }
    return $fechas;
}

function inicializarTotalHook(array $fechas): array
{
    return [
        'dias'         => array_fill_keys($fechas, 0),
        'acum'         => 0,
        'prom'         => 0,
        '_diasTotales' => 0,
        '_hrsTTotal'   => 0,
    ];
}

function calcularHrsTHook(array $turnos): string
{
    if (empty($turnos)) return '—';
    $total = array_sum(array_column($turnos, 'HorasTrabajadas'));
    return $total > 0 ? number_format($total, 1) : '0.0';
}

/**
 * %TP del día.
 * - Sin turnos registrados ese día → '-' (no hubo producción).
 * - Con turnos registrados pero resultado 0 → '0.00%' (sí hubo producción).
 */
function calcularTPDiaHook(array $turnos): string
{
    if (empty($turnos)) return '-';

    $totalTA = array_sum(array_column($turnos, 'TiempoAbajo'));
    return number_format(($totalTA / 60 / 24) * 100, 2) . '%';
}

/**
 * %TP acumulado del mes.
 * - Sin turnos registrados → '-'.
 * - Con turnos pero sin HorasTrabajadas (o en 0) → '0.00%', no '-'.
 */
function calcularTPAcumHook(array $turnos): string
{
    if (empty($turnos)) return '-';

    $totalTA  = array_sum(array_column($turnos, 'TiempoAbajo'));
    $totalHrs = array_sum(array_column($turnos, 'HorasTrabajadas'));

    if ($totalHrs <= 0) return '0.00%'; // hubo turno/producción, pero sin horas registradas

    return number_format(($totalTA / 60 / $totalHrs) * 100, 2) . '%';
}

/**
 * %Merma de Hook
 * 
 * Fórmula: %Merma = ((KGSRechazados - MetrosLineales) / MetrosLineales) * 100
 * 
 * Lógica:
 * - Sin turnos registrados → '-' (no hubo producción)
 * - Con turnos pero MetrosLineales <= 0 → '-' (no hay base para calcular)
 * - Con datos válidos → porcentaje
 */
function calcularMermaHook(array $turnos): string
{
    if (empty($turnos)) return '-';

    $totalMetrosLineales = 0;
    $totalKGSRechazados  = 0;

    foreach ($turnos as $t) {
        $totalMetrosLineales += (float)($t['MetrosLineales'] ?? 0);
        $totalKGSRechazados  += (float)($t['KGSRechazados'] ?? 0);
    }

    if ($totalMetrosLineales <= 0) return '-';

    $merma = (($totalKGSRechazados - $totalMetrosLineales) / $totalMetrosLineales) * 100;
    return number_format($merma, 2) . '%';
}
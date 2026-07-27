<?php
/**
 * transformarSpooler()
 *
 * Convierte el array plano del SP de Spooler en estructura agrupada.
 * Idéntica lógica a transformarTabbi() pero con manejo especial de NULLs:
 * - Registros con métricas NULL = máquina trabajando sin producción registrada
 *   → se incluyen en turnos (para %TP) pero NO en días de producción
 *
 * Métricas:
 * - Producción diaria → TotalMC  (MM²)
 * - Acumulado mes     → AcMMC    (MM² acumulado)
 * - %TP               → TiempoAbajo / HorasTrabajadas
 * - %Merma            → KGSRechazados / TotalPeso
 *
 * @param array  $datos  Array plano mapeado desde mapearFilaSpooler()
 * @param string $fecha  Fecha fin del período ('2026-06-08')
 * @return array
 */
function transformarSpooler(array $datos, string $fecha): array
{
    $fechaInicioRango = date('Y-m-d', strtotime($fecha . ' -6 days'));
    $inicioMes        = date('Y-m-01', strtotime($fecha));

    // Fechas del encabezado — solo las del período de 7 días con producción real
    $fechas      = obtenerFechasPeriodoSpooler($datos, $fechaInicioRango);
    $ultimaFecha = !empty($fechas) ? max($fechas) : $fecha;

    $agrupado         = [];
    $turnosPorMaquina = [];

    foreach ($datos as $row) {
        $cat         = $row['Categoria']     ?? 'Spooler';
        $maq         = $row['NombreMaquina'] ?? 'Spooler';
        $noMaq       = $row['NoMaquina'];
        $prod        = $row['Producto']      ?? 'Sin producto';
        $clave       = $row['Clave'];
        $descripcion = $row['Descripcion']   ?? 'Sin descripción';
        $rowFecha    = $row['Fecha'];
        $turno       = $row['Turno'];

        // ── ¿Tiene producción real? (NULLs = sin registro) ──────────────────
        $tieneMC  = $row['MetrosCuadrados'] !== null;
        $tieneAcum = $row['MC'] !== null;

        // Inicializar estructura agrupada
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

        // ── MM² por día — solo si hay producción real ────────────────────────
        if ($tieneMC && $rowFecha >= $fechaInicioRango) {
            $prev = $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] ?? 0;
            $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['dias'][$rowFecha] =
                round((float)$prev + (float)$row['MetrosCuadrados'], 3);
        }

        // ── Acumulado MM² del mes — último registro con valor real ───────────
        if ($tieneAcum) {
            $reg = $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave];
            if ($rowFecha > $reg['_ultimaFecha'] ||
               ($rowFecha === $reg['_ultimaFecha'] && $turno > $reg['_ultimoTurno'])) {
                $acum = $rowFecha >= $inicioMes ? (float)$row['MC'] : 0;
                $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['acum']         = $acum;
                $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['_ultimaFecha'] = $rowFecha;
                $agrupado[$cat][$maq]['productos'][$prod]['claves'][$clave]['_ultimoTurno'] = $turno;
            }
        }

        // ── Turnos para %TP y %Merma — se registran SIEMPRE (aunque sean NULL)
        // porque la máquina sí estuvo trabajando.
        // Se usa clave como parte del key para que dos productos distintos
        // en el mismo turno no se sobreescriban entre sí. ──────────────────────
        $keyTurno    = $rowFecha . '_' . $turno;
        $keyTurnoClave = $rowFecha . '_' . $turno . '_' . $clave;
        if (!isset($agrupado[$cat][$maq]['_turnos'][$keyTurnoClave])) {
            $agrupado[$cat][$maq]['_turnos'][$keyTurnoClave] = [
                'Fecha'           => $rowFecha,
                'Turno'           => $turno,
                'TiempoAbajo'     => (int)($row['TiempoAbajo']    ?? 0),
                'HorasTrabajadas' => (float)($row['HorasTrabajadas'] ?? 0),
                'MetrosCuadrados' => $tieneMC  ? (float)$row['MetrosCuadrados'] : 0,
                'KGSRechazados'   => $row['KGSRechazados'] !== null ? (float)$row['KGSRechazados'] : 0,
                'PesoTotal'       => $row['Kilogramos']    !== null ? (float)$row['Kilogramos']    : 0,
            ];
        }

        // ── Acumulador global por máquina (para %TP/%Merma de totales) ────────
        // TiempoAbajo y HorasTrabajadas son del turno — solo se toman de la
        // primera fila que los reporte con valor > 0.
        // KGSRechazados y PesoTotal son por producto — se acumulan SIEMPRE,
        // incluso si esa fila tiene HorasTrabajadas = 0 (ej: Verde en SPOOLER 1).
        if (!isset($turnosPorMaquina[$noMaq])) {
            $turnosPorMaquina[$noMaq] = [];
        }
        if (!isset($turnosPorMaquina[$noMaq][$keyTurno])) {
            // Primera fila vista para este turno: inicializar con lo que traiga
            $turnosPorMaquina[$noMaq][$keyTurno] = [
                'Fecha'           => $rowFecha,
                'Turno'           => $turno,
                'TiempoAbajo'     => (int)($row['TiempoAbajo']      ?? 0),
                'HorasTrabajadas' => (float)($row['HorasTrabajadas'] ?? 0),
                'MetrosCuadrados' => $tieneMC ? (float)$row['MetrosCuadrados'] : 0,
                'KGSRechazados'   => $row['KGSRechazados'] !== null ? (float)$row['KGSRechazados'] : 0,
                'PesoTotal'       => $row['Kilogramos']    !== null ? (float)$row['Kilogramos']    : 0,
            ];
        } else {
            // Fila adicional del mismo turno (otro producto):
            // Horas/TiempoAbajo: actualizar solo si el registro actual aún no los tiene
            if ($turnosPorMaquina[$noMaq][$keyTurno]['HorasTrabajadas'] <= 0
                && !empty($row['HorasTrabajadas'])) {
                $turnosPorMaquina[$noMaq][$keyTurno]['HorasTrabajadas'] = (float)$row['HorasTrabajadas'];
                $turnosPorMaquina[$noMaq][$keyTurno]['TiempoAbajo']     = (int)($row['TiempoAbajo'] ?? 0);
            }
            // KGS y Peso: acumular siempre
            $turnosPorMaquina[$noMaq][$keyTurno]['KGSRechazados'] +=
                $row['KGSRechazados'] !== null ? (float)$row['KGSRechazados'] : 0;
            $turnosPorMaquina[$noMaq][$keyTurno]['PesoTotal'] +=
                $row['Kilogramos'] !== null ? (float)$row['Kilogramos'] : 0;
            if ($tieneMC) {
                $turnosPorMaquina[$noMaq][$keyTurno]['MetrosCuadrados'] += (float)$row['MetrosCuadrados'];
            }
        }
    }

    // ── Asegurar que SPOOLER1 y SPOOLER3 siempre aparezcan, aunque no tengan producción ──
    $maquinasEsperadas = [87 => 'SPOOLER1', 90 => 'SPOOLER3'];

    if (empty($agrupado)) {
        $agrupado['Spooler'] = [];
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

    // ── Calcular %TP, %Merma, Prom y Totales ─────────────────────────────────
    foreach ($agrupado as $cat => &$maquinas) {
        $totalOp = inicializarTotalSpooler($fechas);

        foreach ($maquinas as $maq => &$data) {
            $turnos         = $data['_turnos'];
            $noMaqActual    = $data['NoMaquina'] ?? null;
            $turnosGlobales = ($noMaqActual !== null && isset($turnosPorMaquina[$noMaqActual]))
                ? $turnosPorMaquina[$noMaqActual]
                : $turnos;

            // %TP y %Merma del día y acumulado
            $turnosDia  = array_filter($turnosGlobales, fn($t) => $t['Fecha'] === $ultimaFecha);
            $turnosAcum = array_filter($turnosGlobales, fn($t) => $t['Fecha'] >= $inicioMes);

            $data['tp_day_hrs'] = calcularHrsTSpooler($turnosDia);
            $data['tp_dia']     = calcularTPDiaSpooler($turnosDia);
            $data['tp_acum']    = calcularTPAcumSpooler($turnosAcum);
            $data['merma_dia']  = calcularMermaSpooler($turnosDia);
            $data['merma_acum'] = calcularMermaSpooler($turnosAcum);

            // Días trabajados del mes (para Prom)
            $turnosAcumLocal = array_filter($turnos, fn($t) => $t['Fecha'] >= $inicioMes);
            $totalHrs        = array_sum(array_column(array_values($turnosAcumLocal), 'HorasTrabajadas'));
            $diasTrabajados  = $totalHrs > 0 ? round($totalHrs / 24, 2) : 0;

            $totalMaq = inicializarTotalSpooler($fechas);

            foreach ($data['productos'] as &$producto) {
                foreach ($producto['claves'] as &$info) {
                    // Prom: protegido contra división por cero
                    $info['prom'] = $diasTrabajados > 0
                        ? round($info['acum'] / $diasTrabajados, 3)
                        : 0;

                    foreach ($fechas as $f) {
                        $totalMaq['dias'][$f] = round(
                            ($totalMaq['dias'][$f] ?? 0) + ($info['dias'][$f] ?? 0),
                            3
                        );
                    }
                    $totalMaq['acum'] += $info['acum'] ?? 0;

                    unset($info['_ultimaFecha'], $info['_ultimoTurno']);
                }
                unset($info);

                // Filtrar claves sin ningún dato real en los 7 días
                $producto['claves'] = array_filter(
                    $producto['claves'],
                    fn($info) => array_sum(
                        array_map(fn($v) => (float)($v ?? 0), $info['dias'])
                    ) > 0
                );
            }
            unset($producto);

            // Eliminar productos sin claves visibles
            $data['productos'] = array_filter(
                $data['productos'],
                fn($p) => !empty($p['claves'])
            );

            // Acum total máquina = suma de MM² del mes desde turnos globales
            // CORREGIDO (20/07/2026): sin ROUND aquí. Este valor se vuelve a sumar
            // después en $totalOp['acum'] (87+89) — redondear por máquina antes de
            // sumar introducía un error de arrastre (ej. 3636.947 en vez de 3636.948).
            // El redondeo a 3 decimales ocurre una sola vez, al mostrar con
            // number_format() en PdfGenerator.php.
            $turnosAcumMC     = array_filter($turnosGlobales, fn($t) => $t['Fecha'] >= $inicioMes);
            $totalMaq['acum'] = array_sum(array_column(array_values($turnosAcumMC), 'MetrosCuadrados'));

            // Prom total máquina: protegido contra división por cero
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

        // Prom total operación: protegido contra división por cero
        $totalOp['prom'] = $totalOp['_diasTotales'] > 0
            ? round($totalOp['acum'] / $totalOp['_diasTotales'], 3)
            : 0;

        $totalOp['tp_day_hrs'] = $totalOp['_hrsTTotal'] > 0
            ? number_format($totalOp['_hrsTTotal'], 1)
            : '—';

        // %TP y %Merma consolidados de la categoría completa
        $turnosCatDia  = [];
        $turnosCatAcum = [];
        foreach ($maquinas as $maq => $data) {
            if ($maq === '_total_operacion' || !isset($data['NoMaquina'])) continue;
            $noMaqCat   = $data['NoMaquina'];
            $turnosGlob = $turnosPorMaquina[$noMaqCat] ?? [];
            foreach ($turnosGlob as $key => $t) {
                if ($t['Fecha'] === $ultimaFecha) $turnosCatDia[$noMaqCat . '_' . $key]  = $t;
                if ($t['Fecha'] >= $inicioMes)    $turnosCatAcum[$noMaqCat . '_' . $key] = $t;
            }
        }
        $totalOp['tp_dia']     = calcularTPDiaSpooler($turnosCatDia);
        $totalOp['tp_acum']    = calcularTPAcumSpooler($turnosCatAcum);
        $totalOp['merma_dia']  = calcularMermaSpooler($turnosCatDia);
        $totalOp['merma_acum'] = calcularMermaSpooler($turnosCatAcum);

        $maquinas['_total_operacion'] = $totalOp;
    }
    unset($maquinas);

    return [
        'fechas' => $fechas,
        'tablas' => $agrupado,
    ];
}

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Obtiene las fechas del período de 7 días que tengan al menos
 * un registro con producción real (no NULL).
 */
function obtenerFechasPeriodoSpooler(array $datos, string $fechaInicio): array
{
    // Generar siempre los 7 días del rango, aunque algún día no tenga registros
    // con producción real (MetrosCuadrados no NULL)
    $fechas = [];
    $cursor = strtotime($fechaInicio);
    for ($i = 0; $i < 7; $i++) {
        $fechas[] = date('Y-m-d', $cursor);
        $cursor = strtotime('+1 day', $cursor);
    }
    return $fechas;
}

function inicializarTotalSpooler(array $fechas): array
{
    return [
        'dias'         => array_fill_keys($fechas, 0),
        'acum'         => 0,
        'prom'         => 0,
        '_diasTotales' => 0,
        '_hrsTTotal'   => 0,
    ];
}

function calcularHrsTSpooler(array $turnos): string
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
 * %TP del día: (TiempoAbajo en min / 1440 min) * 100
 * Protegido: si no hay turnos devuelve '-'
 */
function calcularTPDiaSpooler(array $turnos): string
{
    if (empty($turnos)) return '-';

    // No se re-deduplica por Fecha+Turno aquí: $turnos puede venir de varias
    // máquinas combinadas (ej. SPOOLER 1 + SPOOLER 3 en el total) y una llave
    // sin la máquina pisaría los datos de una con los de otra.
    // turnosPorMaquina ya viene deduplicado por máquina+turno desde su
    // construcción, así que sumar directamente es seguro.
    $totalTA = array_sum(array_column($turnos, 'TiempoAbajo'));
    // 1440 = minutos en un día; protegido implícitamente (constante > 0)
    return number_format(($totalTA / 60 / 24) * 100, 2) . '%';
}

/**
 * %TP acumulado: (TiempoAbajo en min / horas trabajadas en min) * 100
 * Protegido contra división por cero cuando HorasTrabajadas = 0
 */
function calcularTPAcumSpooler(array $turnos): string
{
    if (empty($turnos)) return '-';

    // No se re-deduplica por Fecha+Turno aquí (ver nota en calcularTPDiaSpooler).
    $totalTA  = array_sum(array_column($turnos, 'TiempoAbajo'));
    $totalHrs = array_sum(array_column($turnos, 'HorasTrabajadas'));

    if ($totalHrs <= 0) return '0.00%'; // hubo turno/producción, pero sin horas registradas (no confundir con sin turnos)

    $dias = $totalHrs / 24;
    // $dias * 24 = totalHrs, ya protegido arriba
    return number_format(($totalTA / 60 / ($dias * 24)) * 100, 2) . '%';
}

/**
 * %Merma: (KGS rechazados / Peso total) * 100
 * Protegido contra división por cero y NULLs
 */
function calcularMermaSpooler(array $turnos): string
{
    if (empty($turnos)) return '-';

    $kgsRechazados = array_sum(array_column($turnos, 'KGSRechazados'));
    $pesoTotal     = array_sum(array_column($turnos, 'PesoTotal'));

    if ($pesoTotal <= 0) return '0.00%'; // ← división por cero protegida

    return number_format(($kgsRechazados / $pesoTotal) * 100, 2) . '%';
    // return number_format($kgsRechazados);
}
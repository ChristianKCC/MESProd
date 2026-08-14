<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
require_once("consultaDepartamentos.php");
set_time_limit(0);  // PHP no mata el proceso por tiempo

$departamentosObj = new ReporteDepartamentos();

$departamento = $_GET['departamento'];
$fechai = $_GET['fechai'];
$fechaf = $_GET['fechaf'];

// para los USTD y Reales
$dataPeriodo = $departamentosObj->getReporteDepartamentos($departamento, $fechai, $fechaf);
$dataDia = filtrarPorFecha($dataPeriodo, $fechaf);
$dataDia = ordenarPorTurno($dataDia);
$dataPeriodo = ordenarPorTurno($dataPeriodo);

// Para Cortes y rechazos
$dataCortesRechazos = $departamentosObj->getInfoCortesRechazos($departamento, $fechai, $fechaf);
$dataCRD = filtrarPorFecha($dataCortesRechazos, $fechaf);
$dataCRD = ordenarPorTurno($dataCRD);
$dataPeriodoCR = ordenarPorTurno($dataCortesRechazos);

// Para los tiempos
$dataTiemposDeptos = $departamentosObj->getInfoTiemposDeptos($departamento, $fechai, $fechaf);
$dataTiempos = filtrarPorFecha($dataTiemposDeptos, $fechaf);
$dataTiempos = ordenarPorTurno($dataTiempos);
$dataPeriodoTiempos = ordenarPorTurno($dataTiemposDeptos);

// Para claves de produccion
$dataClavesProduccion = $departamentosObj->getInfoClavesProduccion($departamento, $fechai, $fechaf);
$dataClaves = filtrarPorFecha($dataClavesProduccion, $fechaf);
$dataClaves = ordenarPorTurno($dataClaves);
$dataClavesP = ordenarPorTurno($dataClavesProduccion);

// Variables Globales
$diasAcc = 0;

// Mapa de nombres -> NoMaquina 
$mapaNombres = construirMapaNombres($dataPeriodo);

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 5, 25);
    }
}

function formatFecha($fecha)
{
    $ts = strtotime($fecha);
    if ($ts === false)
        return $fecha;
    $meses = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
    ];
    return sprintf('%02d-%s-%04d', date('d', $ts), $meses[(int) date('n', $ts) - 1], date('Y', $ts));
}


function filtrarPorFecha(array $data, string $fechaf): array
{
    $out = [];
    foreach ($data as $noMaq => $registros) {
        $filtrados = [];
        foreach ($registros as $r) {
            // En tu getReporteDepartamentos, 'Fecha' ya viene como string "Y-m-d"
            if (isset($r['Fecha']) && $r['Fecha'] === $fechaf) {
                $filtrados[] = $r;
            }
        }
        if (!empty($filtrados)) {
            // Reindexa para tener índices 0,1,2... (Turno 1,2,3)
            $out[$noMaq] = array_values($filtrados);
        }
    }
    return $out;
}

/**
 * (Opcional) Ordena las filas de cada máquina por el campo 'Turno' (1,2,3) si existe.
 */
function ordenarPorTurno(array $data): array
{
    foreach ($data as $noMaq => &$regs) {
        usort($regs, function ($a, $b) {
            $ta = isset($a['Turno']) ? (int) $a['Turno'] : 0;
            $tb = isset($b['Turno']) ? (int) $b['Turno'] : 0;
            return $ta <=> $tb;
        });
    }
    unset($regs);
    return $data;
}


/**
 * Construye mapa 'NombreMaquina' => 'NoMaquina' a partir del arreglo de datos.
 */
function construirMapaNombres(array $data): array
{
    $map = [];
    foreach ($data as $noMaquina => $registros) {
        if (!empty($registros) && !empty($registros[0]['NombreMaquina'])) {
            $nombre = $registros[0]['NombreMaquina']; // 'BCM4', 'BCM3', 'MP25', etc.
            $map[$nombre] = (string) $noMaquina;
        }
    }
    return $map;
}

/**
 * Dibuja el encabezado de la tabla de Turnos en la coordenada (x, y).
 * Mantiene los mismos anchos/offsets que ya usas.
 */
function pintarEncabezadoTurnos(FPDF $pdf, float $x, float $y): void
{

    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(205, 221, 250);
    $pdf->SetTextColor(0, 0, 0);

    // Encabezados
    $pdf->SetXY($x - 3, $y - 2);
    $pdf->Cell(13, 3.5, 'TURNO', 1, 0, 'C', true);

    $labels = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25', 'Total Turno'];
    $offsets = [10, 22, 34, 46, 58, 70, 82];
    $widths = [12, 12, 12, 12, 12, 12, 13];

    for ($i = 0; $i < count($labels); $i++) {
        $pdf->SetXY($x + $offsets[$i], $y - 2);
        $pdf->Cell($widths[$i], 3.5, $labels[$i], 1, 1, 'C', true);
    }

    $pdf->SetTextColor(0, 0, 0); // restablecer
}

function pintarEncabezadoClaves(FPDF $pdf, float $x, float $y): void
{
    $pdf->SetDrawColor(30, 144, 227);

    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(205, 221, 250);
    $pdf->SetTextColor(0, 0, 0);

    // Encabezados
    $pdf->SetXY($x, $y - 2);
    $pdf->Cell(15, 3.5, 'CLAVE', 1, 0, 'C', true);

    $labels = ['DESCRIPCION', 'PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25', 'Total Turno'];
    $offsets = [-8, 42, 57, 72, 87, 102, 117, 132];
    $widths = [50, 15, 15, 15, 15, 15, 15, 15];

    for ($i = 0; $i < count($labels); $i++) {
        $pdf->SetXY($x + $offsets[$i] + 23, $y - 2);
        $pdf->Cell($widths[$i], 3.5, $labels[$i], 1, 0, 'C', true);
    }

    $pdf->SetTextColor(0, 0, 0); // restablecer
}

function sumarCampoPorMaquina(array $data, array $mapaNombres, string $campo = 'TotalUSTD'): array
{
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $porMaquina = [];
    $totalGeneral = 0.0;

    foreach ($colMachines as $nombreMaquina) {
        $suma = 0.0;

        if (isset($mapaNombres[$nombreMaquina])) {
            $noMaq = $mapaNombres[$nombreMaquina];

            if (isset($data[$noMaq]) && is_array($data[$noMaq])) {
                foreach ($data[$noMaq] as $r) {
                    if (isset($r[$campo]) && is_numeric($r[$campo])) {
                        $suma += (float) $r[$campo];
                    }
                }
            }
        }

        $porMaquina[$nombreMaquina] = $suma;
        $totalGeneral += $suma;
    }

    return ['porMaquina' => $porMaquina, 'totalGeneral' => $totalGeneral];
}

function sumarCampoCortesRechazos(array $data, array $mapaNombres, string $campo = 'Cortes'): array
{
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $porMaquina = [];
    $totalGeneral = 0.0;

    foreach ($colMachines as $nombreMaquina) {
        $suma = 0.0;

        if (isset($mapaNombres[$nombreMaquina])) {
            $noMaq = $mapaNombres[$nombreMaquina];

            if (isset($data[$noMaq]) && is_array($data[$noMaq])) {
                foreach ($data[$noMaq] as $r) {
                    if (isset($r[$campo]) && is_numeric($r[$campo])) {
                        $suma += (float) $r[$campo];
                    }
                }
            }
        }

        $porMaquina[$nombreMaquina] = $suma;
        $totalGeneral += $suma;
    }

    return ['porMaquina' => $porMaquina, 'totalGeneral' => $totalGeneral];
}

function sumarCampoCortesPiezas(array $data, array $mapaNombres, string $campo = 'Cortes'): array
{
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $porMaquina = [];
    $totalGeneral = 0.0;

    foreach ($colMachines as $nombreMaquina) {
        $suma = 0.0;

        if (isset($mapaNombres[$nombreMaquina])) {
            $noMaq = $mapaNombres[$nombreMaquina];

            if (isset($data[$noMaq]) && is_array($data[$noMaq])) {
                foreach ($data[$noMaq] as $r) {
                    if (isset($r[$campo]) && is_numeric($r[$campo])) {
                        $suma += (float) $r[$campo];
                    }
                }
            }
        }

        $porMaquina[$nombreMaquina] = $suma;
        $totalGeneral += $suma;
    }

    return ['porMaquina' => $porMaquina, 'totalGeneral' => $totalGeneral];
}

function sumarTiempoArribaDiaPorMaquina(array $dataCRD): array
{
    $resultado = [
        'porMaquina' => [],
        'totalGeneral' => 0
    ];

    foreach ($dataCRD as $noMaquina => $turnos) {
        // Si no hay turnos, seguimos
        if (empty($turnos) || !is_array($turnos)) {
            continue;
        }

        // Tomamos el nombre desde el primer turno (está estable).
        $nombre = isset($turnos[0]['NombreMaquina']) ? $turnos[0]['NombreMaquina'] : (string) $noMaquina;

        $suma = 0;
        foreach ($turnos as $t) {
            // Aseguramos la llave y tipo
            $suma += isset($t['HorasTrabajadas']) ? (float) $t['HorasTrabajadas'] : 0;
        }

        $resultado['porMaquina'][$nombre] = ($resultado['porMaquina'][$nombre] ?? 0) + $suma;
        $resultado['totalGeneral'] += $suma;
    }
    // echo json_encode($resultado, JSON_PRETTY_PRINT);

    return $resultado;
}

function sumarTiempoAbajoDiaPorMaquina(array $dataCRD): array
{
    $resultado = [
        'porMaquina' => [],
        'totalGeneral' => 0
    ];

    foreach ($dataCRD as $noMaquina => $turnos) {
        // Si no hay turnos, seguimos
        if (empty($turnos) || !is_array($turnos)) {
            continue;
        }

        // Tomamos el nombre desde el primer turno (está estable).
        $nombre = isset($turnos[0]['NombreMaquina']) ? $turnos[0]['NombreMaquina'] : (string) $noMaquina;

        $suma = 0;
        foreach ($turnos as $t) {
            // Aseguramos la llave y tipo
            $suma += isset($t['TiempoAbajo']) ? (int) $t['TiempoAbajo'] : 0;
        }

        $resultado['porMaquina'][$nombre] = ($resultado['porMaquina'][$nombre] ?? 0) + $suma;
        $resultado['totalGeneral'] += $suma;
    }

    return $resultado;
}

/**
 * Pinta 3 filas (Turno 1..3) en la tabla, con la métrica $campo.
 * $campo puede ser: 'USTD', 'Reales', 'Piezas', 'TotalPiezas', 'TotalUSTD', 'TotalReal'
 *
 * @param FPDF  $pdf
 * @param float $x           X base de la tabla (coincidir con el encabezado)
 * @param float $y           Y de inicio del encabezado (las filas inician 5mm debajo)
 * @param array $data        Arreglo de datos original (agrupado por NoMaquina)
 * @param array $mapaNombres ['BCM4' => '60', ...]
 * @param string $campo
 */


function truncarDecimales($numero, $precision = 2)
{
    $factor = pow(10, $precision);
    if ($numero >= 0) {
        return floor($numero * $factor) / $factor;
    } else {
        return ceil($numero * $factor) / $factor; // hacia 0 para negativos
    }
}


function pintarCuerpoTurnos(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataDia,                 // solo Fecha == $fechaf
    array $mapaNombres,
    string $campo,                  // lo que muestra por turno: 'USTD' o 'Reales'
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],        // todo el rango: $fechai..$fechaf
    string $campoAcumulado = '',    // 'TotalUSTD' o 'TotalReal'
    string $labelAcumulado = 'Acumulado',
    int $decimalesAcumulado = 2,
    array $dataTiempos,
    string $HorasTrabajadas

): void {

    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    // Formato para el campo "por turno" (USTD -> 2 dec, Reales -> 0 dec)
    $decimalesTurno = in_array($campo, ['USTD', 'TotalUSTD', 'TotalUSTDTurno']) ? 2 : 0;

    // Totales de la tabla por turno (del día)
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = '0.0';

    $pdf->SetFont('Arial', '', 4.5);

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY - 2);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        $totalTurno = 0.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];
            $valorNum = 0.0;
            $valorStr = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];


                if (isset($dataDia[$noMaq][$i]) && isset($dataDia[$noMaq][$i][$campo]) && is_numeric($dataDia[$noMaq][$i][$campo])) {
                    $valorNum = (float) $dataDia[$noMaq][$i][$campo];

                    if ($valorNum == 0.0) {
                        $valorStr = '';
                    } else {
                        // Truncado a 2 decimales Hacia CERO (positivos ↓, negativos ↑)
                        $factor = 100;
                        $truncado = $valorNum >= 0
                            ? floor($valorNum * $factor) / $factor
                            : ceil($valorNum * $factor) / $factor;

                        // Formatear con 2 decimales fijos, punto como separador
                        $valorStr = number_format($truncado, $decimalesAcumulado, '.', '');
                    }
                }

            }

            $totalesPorMaquina[$idx] += $valorNum;
            $totalTurno += $valorNum;

            $pdf->SetXY($cellX, $currY - 2);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorStr, 1, 0, 'C');
        }

        $pdf->SetXY($x + 82, $currY - 2);

        $trunc2 = $totalTurno >= 0
            ? floor($totalTurno * 100) / 100
            : ceil($totalTurno * 100) / 100;

        $pdf->Cell($colW[7], $rowHeight, number_format($trunc2, 2, '.', ''), 1, 0, 'C');


        $totalGeneral += $totalTurno;
    }

    // ---------------- Fila TOTAL (del día, del campo $campo) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);

    $pdf->SetFillColor(235, 242, 250);
    $pdf->SetXY($x - 3, $totalY - 2);
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY - 2);
        $v = $totalesPorMaquina[$idx] ?? 0.0;
        $valorDisplay = ((float) $v === 0.0) ? '' : number_format($v, $decimalesTurno);
        $pdf->SetFont('Arial', 'B', 4);
        $pdf->Cell(
            $colW[$idx + 1],
            $rowHeight,
            $valorDisplay,
            1,
            0,
            'C',
            true
        );
    }

    $pdf->SetXY($x + 82, $totalY - 2);
    $pdf->SetFont('Arial', 'B', 4.5);
    $valorTruncado = truncarDecimales($totalGeneral, 2);
    $pdf->Cell($colW[7], $rowHeight, number_format($valorTruncado, 2), 1, 0, 'C', true);

    // ---------------- Fila ACUMULADO (del periodo, del campo $campoAcumulado) ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acum = sumarCampoPorMaquina($dataPeriodo, $mapaNombres, $campoAcumulado);
        $acumY = $totalY + $rowHeight;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetXY($x - 3, $acumY - 2);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelAcumulado, 1, 'C', 'C', true);

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY - 2);
            $v = $acum['porMaquina'][$nombreMaquina] ?? 0.0;
            $valorDisplay = ((float) $v === 0.0) ? '' : number_format((float) $v, $decimalesAcumulado);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorDisplay, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY - 2);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->Cell($colW[7], $rowHeight, number_format($acum['totalGeneral'], 2), 1, 0, 'C', true);
    }

    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight + 2;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + 10);
        // MultiCell estándar de FPDF: (w, h, txt, border, align, fill)
        $pdf->MultiCell($colW[0], $rowHeight - 1.75, 'PROMEDIO USTD DIA', 1, 'C', true);
        $acum = sumarCampoPorMaquina($dataPeriodo, $mapaNombres, $campoAcumulado);             // USTD Acumuladas
        $acumNum = sumarCampoCortesRechazos($dataTiempos, $mapaNombres, $HorasTrabajadas);     // Dias Acumulados por máquina en el periodo

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 10);
            $ustdProm = ($acum['porMaquina'][$nombreMaquina]) ?? 0.0;
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
            $dias = (float) number_format(($num / 24), 2); // 1440 minutos en un día

            $texto = ($dias != 0.0) ? number_format(($ustdProm) / $dias, 2) : '';
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + 10);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $textoG = ($numG != 0.0) ? number_format($acum['totalGeneral'] / ($numG / 24), 2) : '';
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }


}

function pintarCuerpoReales(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataDia,                 // solo Fecha == $fechaf
    array $mapaNombres,
    string $campo,                  // lo que muestra por turno: 'USTD' o 'Reales'
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],        // todo el rango: $fechai..$fechaf
    string $campoAcumulado = '',    // 'TotalUSTD' o 'TotalReal'
    string $labelAcumulado = 'Acumulado',
    int $decimalesAcumulado = 2,

): void {

    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    // Formato para el campo "por turno" (USTD -> 2 dec, Reales -> 0 dec)
    $decimalesTurno = in_array($campo, ['USTD', 'TotalUSTD', 'TotalUSTDTurno']) ? 2 : 0;

    // Totales de la tabla por turno (del día)
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = '0.0';

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY - 2);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        $totalTurno = 0.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];
            $valorNum = 0.0;
            $valorStr = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];


                if (isset($dataDia[$noMaq][$i]) && isset($dataDia[$noMaq][$i][$campo]) && is_numeric($dataDia[$noMaq][$i][$campo])) {
                    $valorNum = (float) $dataDia[$noMaq][$i][$campo];

                    if ($valorNum == 0.0) {
                        $valorStr = '';
                    } else {
                        // Truncado a 2 decimales Hacia CERO (positivos ↓, negativos ↑)
                        $factor = 100;
                        $truncado = $valorNum >= 0
                            ? floor($valorNum * $factor) / $factor
                            : ceil($valorNum * $factor) / $factor;

                        // Formatear con 2 decimales fijos, punto como separador
                        $valorStr = number_format($truncado, $decimalesAcumulado, '.', '');
                    }
                }

            }

            $totalesPorMaquina[$idx] += $valorNum;
            $totalTurno += $valorNum;

            $pdf->SetXY($cellX, $currY - 2);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorStr, 1, 0, 'C');
        }

        $pdf->SetXY($x + 82, $currY - 2);

        $trunc2 = $totalTurno >= 0
            ? floor($totalTurno * 100) / 100
            : ceil($totalTurno * 100) / 100;

        $pdf->Cell($colW[7], $rowHeight, number_format($trunc2, 2, '.', ''), 1, 0, 'C');


        $totalGeneral += $totalTurno;
    }

    // ---------------- Fila TOTAL (del día, del campo $campo) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);

    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(235, 242, 250);

    $pdf->SetXY($x - 3, $totalY - 2);
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY - 2);
        $v = $totalesPorMaquina[$idx] ?? 0.0;
        $valorDisplay = ((float) $v === 0.0) ? '' : number_format($v, $decimalesTurno);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell(
            $colW[$idx + 1],
            $rowHeight,
            $valorDisplay,
            1,
            0,
            'C',
            true
        );
    }

    $pdf->SetXY($x + 82, $totalY - 2);
    $pdf->SetFont('Arial', 'B', 4.5);
    $valorTruncado = truncarDecimales($totalGeneral, 2);
    $pdf->Cell($colW[7], $rowHeight, number_format($valorTruncado, 2), 1, 0, 'C', true);

    // ---------------- Fila ACUMULADO (del periodo, del campo $campoAcumulado) ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acum = sumarCampoPorMaquina($dataPeriodo, $mapaNombres, $campoAcumulado);
        $acumY = $totalY + $rowHeight;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetXY($x - 3, $acumY - 2);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelAcumulado, 1, 'C', 'C', true);

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY - 2);
            $v = $acum['porMaquina'][$nombreMaquina] ?? 0.0;
            $valorDisplay = ((float) $v === 0.0) ? '' : number_format((float) $v, $decimalesAcumulado);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorDisplay, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY - 2);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->Cell($colW[7], $rowHeight, number_format($acum['totalGeneral'], 2), 1, 0, 'C', true);
    }

}

function pintarCuerpoCortesRechazos(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campo, // 'Cortes' o 'Rechazos'
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',
    string $labelAcumulado,
    int $decimalesAcumulado
): void {
    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    // Formato para el campo "por turno" (USTD -> 2 dec, Reales -> 0 dec)
    $decimalesTurno = in_array($campo, ['Cortes', 'TotalCortes', 'TotalCortesTurno']) ? 2 : 0;

    // Totales de la tabla por turno (del día)
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = 0.0;

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        $totalTurno = 0.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];
            $valorNum = 0.0;
            $valorStr = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campo]) && is_numeric($dataCRD[$noMaq][$i][$campo])) {
                    $valorNum = (float) $dataCRD[$noMaq][$i][$campo];
                    $valorStr = ($valorNum == 0.0) ? '' : number_format($valorNum);

                }
            }

            $totalesPorMaquina[$idx] += $valorNum;
            $totalTurno += $valorNum;

            $pdf->SetXY($cellX, $currY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorStr, 1, 0, 'C');
        }

        $pdf->SetXY($x + 82, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, number_format($totalTurno), 1, 0, 'C');

        $totalGeneral += $totalTurno;
    }

    // ---------------- Fila TOTAL (del día, del campo $campo) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);
    $pdf->SetFillColor(235, 242, 250);
    $pdf->SetXY($x - 3, $totalY);
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY);
        $v = $totalesPorMaquina[$idx] ?? 0.0;
        $valorDisplay = ((float) $v === 0.0) ? '' : number_format($v);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell(
            $colW[$idx + 1],
            $rowHeight,
            $valorDisplay,
            1,
            0,
            'C',
            true
        );
    }

    $pdf->SetXY($x + 82, $totalY);
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->Cell($colW[7], $rowHeight, number_format($totalGeneral), 1, 0, 'C', true);

    // ---------------- Fila ACUMULADO (del periodo, del campo $campoAcumulado) ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);
        $acumY = $totalY + $rowHeight;

        $pdf->SetFillColor(235, 242, 250);

        $pdf->SetXY($x - 3, $acumY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelAcumulado, 1, 0, 'C', true);

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);
            $v = $acum['porMaquina'][$nombreMaquina] ?? 0.0;
            $valorDisplay = ((float) $v === 0.0) ? '' : number_format((float) $v, $decimalesAcumulado);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorDisplay, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, number_format($acum['totalGeneral']), 1, 0, 'C', true);
    }
}

function pintarCuerpoMermaMaquinas(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campoDenominador,     // Ej: 'Cortes' (o 'Rechazos' si solo quieres pintar ese campo)
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',  // Para acumulado del denominador (ej 'Cortes')
    string $labelAcumulado = '',
    int $decimalesAcumulado = 0,  // decimales para acumulado en modo NO porcentaje (o si quieres controlar el formato)
    ?string $campoNumerador = null, // NUEVO: Ej 'Rechazos' para % Merma
    int $decimalesPorcentaje = 1    // NUEVO: decimales para el % (ej 1)
): void {
    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    $modoPorcentaje = ($campoNumerador !== null && $campoNumerador !== '');

    // Acumuladores por día (sumas para calcular % correctos)
    $sumDenPorMaquina = array_fill(0, count($colMachines), 0.0); // Cortes
    $sumNumPorMaquina = array_fill(0, count($colMachines), 0.0); // Rechazos

    $sumDenGeneral = 0.0;
    $sumNumGeneral = 0.0;

    // Si NO es porcentaje, mantenemos acumuladores tipo "antes" para pintar totals como sumas simples
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = 0.0;

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        // Para la columna TOTAL por turno
        $sumDenTurno = 0.0;
        $sumNumTurno = 0.0;
        $totalTurnoSimple = 0.0; // solo si NO es porcentaje (suma simple)

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];

            $den = 0.0; // Cortes (denominador)
            $num = 0.0; // Rechazos (numerador)
            $textoCelda = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                // Denominador
                if (isset($dataCRD[$noMaq][$i][$campoDenominador]) && is_numeric($dataCRD[$noMaq][$i][$campoDenominador])) {
                    $den = (float) $dataCRD[$noMaq][$i][$campoDenominador];
                }

                // Numerador (si modo porcentaje)
                if ($modoPorcentaje && isset($dataCRD[$noMaq][$i][$campoNumerador]) && is_numeric($dataCRD[$noMaq][$i][$campoNumerador])) {
                    $num = (float) $dataCRD[$noMaq][$i][$campoNumerador];
                }
            }

            if ($modoPorcentaje) {
                // Acumular sumas para % correctos
                $sumDenPorMaquina[$idx] += $den;
                $sumNumPorMaquina[$idx] += $num;

                $sumDenTurno += $den;
                $sumNumTurno += $num;

                $sumDenGeneral += $den;
                $sumNumGeneral += $num;

                // Texto de celda: % por máquina
                if ($den > 0) {
                    $pct = ($num / $den) * 100;
                    $textoCelda = number_format($pct, 2) . '%';
                } else {
                    $textoCelda = '';
                }
            } else {
                // Modo original: pinta el campoDenominador como número
                $valorNum = $den;
                $textoCelda = ($valorNum != 0.0) ? number_format($valorNum) : '';

                $totalesPorMaquina[$idx] += $valorNum;
                $totalTurnoSimple += $valorNum;
                $totalGeneral += $valorNum;
            }

            $pdf->SetXY($cellX, $currY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $textoCelda, 1, 0, 'C');
        }

        // Columna TOTAL por turno
        $pdf->SetXY($x + 82, $currY);

        if ($modoPorcentaje) {
            $totalTurnoPct = ($sumDenTurno > 0) ? (($sumNumTurno / $sumDenTurno) * 100) : 0.0;
            $textoTotal = ($sumDenTurno > 0) ? number_format($totalTurnoPct, $decimalesPorcentaje) . '%' : '';
            $pdf->Cell($colW[7], $rowHeight, $textoTotal, 1, 0, 'C');
        } else {
            $pdf->Cell($colW[7], $rowHeight, number_format($totalTurnoSimple), 1, 0, 'R');
        }
    }

    // ---------------- Fila TOTAL (del día) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);

    $pdf->SetFillColor(235, 242, 250);

    $pdf->SetXY($x - 3, $totalY);
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY);

        if ($modoPorcentaje) {
            $den = $sumDenPorMaquina[$idx] ?? 0.0;
            $num = $sumNumPorMaquina[$idx] ?? 0.0;

            if ($den > 0) {
                $pct = ($num / $den) * 100;
                $texto = number_format($pct, 2, '.', '') . '%';
            } else {
                $texto = '';
            }

            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        } else {
            $v = $totalesPorMaquina[$idx] ?? 0.0;
            $texto = ((float) $v === 0.0) ? '' : number_format((float) $v);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }
    }

    // TOTAL General (columna final)
    $pdf->SetXY($x + 82, $totalY);

    if ($modoPorcentaje) {
        $pctG = ($sumDenGeneral > 0) ? (($sumNumGeneral / $sumDenGeneral) * 100) : 0.0;
        $pdf->Cell($colW[7], $rowHeight, number_format($pctG, 2) . '%', 1, 0, 'C', true);
    } else {
        $pdf->Cell($colW[7], $rowHeight, number_format($totalGeneral), 1, 0, 'C', true);
    }

    // ---------------- Fila ACUMULADO (del periodo) ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $totalY + $rowHeight;

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY);
        // MultiCell estándar de FPDF: (w, h, txt, border, align, fill)
        $pdf->SetFillColor(235, 242, 250);
        $pdf->MultiCell($colW[0], $rowHeight, $labelAcumulado, 1, 'C', true);

        // OJO: MultiCell mueve el cursor; reubicamos para seguir pintando fila
        // Acumulados del periodo:
        // - Si es porcentaje: necesitamos acumulado de Cortes (den) y de Rechazos (num)
        // - Si no: usamos el campoAcumulado tal cual

        if ($modoPorcentaje) {
            $acumDen = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);     // Cortes del periodo
            $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoNumerador);     // Rechazos del periodo

            foreach ($colMachines as $idx => $nombreMaquina) {
                $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

                $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? 0.0);
                $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? 0.0);

                if ($den > 0) {
                    $pct = ($num / $den) * 100;
                    $texto = number_format($pct, 2, '.', '') . '%';
                } else {
                    $texto = '';
                }

                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
            }

            $pdf->SetXY($x + 82, $acumY);

            $denG = (float) ($acumDen['totalGeneral'] ?? 0.0);
            $numG = (float) ($acumNum['totalGeneral'] ?? 0.0);

            $textoG = ($denG > 0)
                ? number_format((($numG / $denG) * 100), 2) . '%'
                : '';

            $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);

        } else {
            // Modo normal: acumulado de un solo campo
            $acum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);

            foreach ($colMachines as $idx => $nombreMaquina) {
                $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

                $v = $acum['porMaquina'][$nombreMaquina] ?? 0.0;
                $texto = ((float) $v === 0.0) ? '' : number_format((float) $v, $decimalesAcumulado);

                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'R', true);
            }

            $pdf->SetXY($x + 82, $acumY);
            $pdf->Cell($colW[7], $rowHeight, number_format((float) $acum['totalGeneral']), 1, 0, 'R', true);
        }
    }

    $pdf->SetFont('Arial', '', 6);
}

function pintarMermaMaquinasTotal(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campoDenominador,     // Ej: 'Cortes' (o 'Rechazos' si solo quieres pintar ese campo)
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',  // Para acumulado del denominador (ej 'Cortes')
    string $labelAcumulado = '',
    int $decimalesAcumulado = 0,  // decimales para acumulado en modo NO porcentaje (o si quieres controlar el formato)
    ?string $campoNumerador = null, // NUEVO: Ej 'Rechazos' para % Merma
    int $decimalesPorcentaje = 1,    // NUEVO: decimales para el % (ej 1)
    array $dataCortesRechazos = [],
    string $campoUSTD,
    string $campoRechazos
) {
    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    $modoPorcentaje = ($campoNumerador !== null && $campoNumerador !== '');

    // Acumuladores por día (sumas para calcular % correctos)
    $sumDenPorMaquina = array_fill(0, count($colMachines), 0.0); // Cortes
    $sumNumPorMaquina = array_fill(0, count($colMachines), 0.0); // Rechazos

    $sumDenGeneral = 0.0;
    $sumNumGeneral = 0.0;

    // Si NO es porcentaje, mantenemos acumuladores tipo "antes" para pintar totals como sumas simples
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = 0.0;

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        // Para la columna TOTAL por turno
        $sumDenTurno = 0.0;
        $sumNumTurno = 0.0;
        $totalTurnoSimple = 0.0; // solo si NO es porcentaje (suma simple)

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];

            $den = 0.0; // Cortes (denominador)
            $num = 0.0; // Rechazos (numerador)
            $textoCelda = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                // Denominador
                if (isset($dataCRD[$noMaq][$i][$campoDenominador]) && is_numeric($dataCRD[$noMaq][$i][$campoDenominador])) {
                    $den = (float) $dataCRD[$noMaq][$i][$campoDenominador];
                }

                // Numerador (si modo porcentaje)
                if ($modoPorcentaje && isset($dataCRD[$noMaq][$i][$campoNumerador]) && is_numeric($dataCRD[$noMaq][$i][$campoNumerador])) {
                    $num = (float) $dataCRD[$noMaq][$i][$campoNumerador];
                }
            }

            if ($modoPorcentaje) {
                // Acumular sumas para % correctos
                $sumDenPorMaquina[$idx] += $den;
                $sumNumPorMaquina[$idx] += $num;

                $sumDenTurno += $den;
                $sumNumTurno += $num;

                $sumDenGeneral += $den;
                $sumNumGeneral += $num;

                // Texto de celda: % por máquina
                if ($den > 0) {
                    $pct = (1 - ($num / $den)) * 100;
                    $textoCelda = number_format($pct, 2) . '%';
                } else {
                    $textoCelda = '';
                }
            } else {
                // Modo original: pinta el campoDenominador como número
                $valorNum = $den;
                $textoCelda = ($valorNum != 0.0) ? number_format($valorNum) : '';

                $totalesPorMaquina[$idx] += $valorNum;
                $totalTurnoSimple += $valorNum;
                $totalGeneral += $valorNum;
            }

            $pdf->SetXY($cellX, $currY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $textoCelda, 1, 0, 'C');
        }

        // Columna TOTAL por turno
        $pdf->SetXY($x + 82, $currY);

        if ($modoPorcentaje) {
            $totalTurnoPct = ($sumDenTurno > 0) ? ((1 - ($sumNumTurno / $sumDenTurno)) * 100) : 0.0;
            $textoTotal = ($sumDenTurno > 0) ? number_format($totalTurnoPct, 2) . '%' : '';
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[7], $rowHeight, $textoTotal, 1, 0, 'C');
        } else {
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[7], $rowHeight, number_format($totalTurnoSimple), 1, 0, 'C');
        }
    }

    // ---------------- Fila TOTAL (del día) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);

    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(235, 242, 250);

    $pdf->SetXY($x - 3, $totalY);

    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY);

        if ($modoPorcentaje) {
            $den = $sumDenPorMaquina[$idx] ?? 0.0;
            $num = $sumNumPorMaquina[$idx] ?? 0.0;

            if ($den > 0) {
                // % MD =  
                $pct = (1 - ($num / $den)) * 100;
                $texto = number_format($pct, 2, '.', '') . '%';
            } else {
                $texto = '';
            }
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        } else {
            $v = $totalesPorMaquina[$idx] ?? 0.0;
            $texto = ((float) $v === 0.0) ? '' : number_format((float) $v);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }
    }

    // TOTAL General (columna final)
    $pdf->SetXY($x + 82, $totalY);

    if ($modoPorcentaje) {
        $pctG = ($sumDenGeneral > 0) ? ((1 - ($sumNumGeneral / $sumDenGeneral)) * 100) : 0.0;
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, number_format($pctG, 2) . '%', 1, 0, 'C', true);
    } else {
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, number_format($totalGeneral), 1, 0, 'C', true);
    }


    // ---------------- Fila Total ACC % MM ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $totalY + $rowHeight;

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY);
        $pdf->SetFillColor(235, 242, 250);
        $pdf->MultiCell($colW[0], $rowHeight, 'TOTAL ACC', 1, 'C', true);

        $acumUSTD = sumarCampoCortesRechazos($dataCortesRechazos, $mapaNombres, $campoUSTD);     // USTD 
        $acumDen = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);     // Cortes del periodo
        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoNumerador);     // TotalPiezas

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

            if ($modoPorcentaje) {
                $ustd = (float) ($acumUSTD['porMaquina'][$nombreMaquina] ?? '');
                $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
                $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? '');
                $totalPanal = abs((($ustd * 150) - $den));
                if ($den > 0) {
                    // (1 - (Pañales / Cortes) ) *100
                    // $num = totalPanal
                    //  $totalPanal = ($ustd * 150) - $cortes;
                    // (1 - (Pañales / Cortes) ) *100
                    $pct = (1 - ($num / $den)) * 100; // CALCULO A REVISAR  
                    $texto = number_format($pct, 2, '.', '') . '%';
                } else {
                    $texto = '';
                }
                $pdf->SetFont('Arial', 'B', 4.5);
                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
            } else {
                $v = $totalesPorMaquina[$idx] ?? 0.0;
                $texto = ((float) $v === 0.0) ? '' : number_format((float) $v);
                $pdf->SetFont('Arial', 'B', 4.5);
                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
            }
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $num, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $denG = (float) ($acumDen['totalGeneral'] ?? '');
        $numGTexto = '';
        if ($modoPorcentaje) {
            if ($denG > 0) {
                $pctG = (1 - ($numG / $denG)) * 100;
                $numGTexto = number_format($pctG, 2) . '%';
            }
        } else {
            $numGTexto = ($numG != 0.0) ? number_format($numG, 2) : '';
        }
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $numGTexto, 1, 0, 'C', true);


    }

    // ---------------- Fila YIELD ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $totalY + $rowHeight + 3.5;

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY);
        $pdf->SetFillColor(235, 242, 250);
        $pdf->MultiCell($colW[0], $rowHeight, 'YIELD', 1, 'C', true);

        if ($modoPorcentaje) {
            $acumDen = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);     // Cortes del periodo
            $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoNumerador);     // Rechazos del periodo 

            foreach ($colMachines as $idx => $nombreMaquina) {
                $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

                $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? 0.0);
                $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? 0.0);

                if ($den > 0) {
                    $pct = 100 - ((1 - ($num / $den)) * 100);
                    $texto = number_format($pct, 2, '.', '') . '%';
                } else {
                    $texto = '';
                }
                $pdf->SetFont('Arial', 'B', 4.5);
                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
            }

            $pdf->SetXY($x + 82, $acumY);

            $denG = (float) ($acumDen['totalGeneral'] ?? 0.0);
            $numG = (float) ($acumNum['totalGeneral'] ?? 0.0);

            $textoG = ($denG > 0)
                ? number_format((100 - ((1 - ($numG / $denG)) * 100)), 2) . '%'
                : '';
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);

        } else {
            // Modo normal: acumulado de un solo campo
            $acum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);

            foreach ($colMachines as $idx => $nombreMaquina) {
                $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

                $v = $acum['porMaquina'][$nombreMaquina] ?? 0.0;
                $texto = ((float) $v === 0.0) ? '' : number_format((float) $v, $decimalesAcumulado);
                $pdf->SetFont('Arial', 'B', 4.5);
                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'R', true);
            }

            $pdf->SetXY($x + 82, $acumY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[7], $rowHeight, $acum, 1, 0, 'R', true);
        }
    }


}

function pintarTiemposMaquinas(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campo,
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',
    string $labelAcumulado,
    int $decimalesAcumulado
) {
    $rowHeight = 3.5;

    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];
    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y - 2.5;
    // Totales de la tabla por turno (del día)
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = '0.0';

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY + 6);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        $totalTurno = 0.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];
            $valorNum = 0.0;
            $valorStr = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campo]) && is_numeric($dataCRD[$noMaq][$i][$campo])) {
                    $valorNum = (float) $dataCRD[$noMaq][$i][$campo];
                    $valorStr = ($valorNum == 0.0) ? '' : number_format($valorNum);
                }
            }

            $totalesPorMaquina[$idx] += $valorNum;
            $totalTurno += $valorNum;
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->SetXY($cellX, $currY + 6);
            $valorMin = $valorNum == 0.0 ? '' : number_format($valorNum * 60);
            $valoHr = $valorNum == 0.0 ? '' : number_format($valorNum, 2);
            $valorSTR = ($valorNum == 0.0) ? '' : "$valorMin / $valoHr";
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorSTR, 1, 0, 'C');
        }
        $totalMinTurno = $totalTurno * 60;
        $valorSTRTotal = ($totalTurno == 0.0) ? '' : " $totalMinTurno  /  $totalTurno";
        $pdf->SetXY($x + 82, $currY + 6);
        $pdf->Cell($colW[7], $rowHeight, $valorSTRTotal, 1, 0, 'C');

        $totalGeneral += $totalTurno;
    }




    // ---------------- Fila acumulado de TiempoArriba (en minutos) ----------------
    {
        $acumY = $bodyStartY + 16;

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Arial', 'B', 4.5);

        // Etiqueta de la fila
        $pdf->SetXY($x - 3, $acumY);
        $pdf->MultiCell($colW[0], $rowHeight - 1.75, 'TIEMPO ARRIBA DIA', 1, 'C', true);

        // Calcula acumulados por máquina (minutos del día seleccionado)
        $acumArriba = sumarTiempoArribaDiaPorMaquina($dataCRD);

        // Imprime por máquina en el orden de $colMachines
        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);
            $hrs = (float) ($acumArriba['porMaquina'][$nombreMaquina] ?? 0);
            $min = (float) ($hrs * 60);
            $texto = ($hrs != 0.0) ? number_format($min, 2) . ' / ' . number_format($hrs, 2) : '';
            $pdf->SetFont('Arial', 'B', 4);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);

        }


        $pdf->SetXY($x + 82, $acumY);
        $horasG = (float) ($acumArriba['totalGeneral'] ?? 0);
        $minG = (float) ($horasG * 60);
        $textoG = ($horasG != 0.0) ? number_format($minG) . ' / ' . number_format(($horasG), 2) : '';
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);

    }



    // ---------------- Fila acumulado de hras trabajadas ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight + 12.5;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + $rowHeight);
        // MultiCell estándar de FPDF: (w, h, txt, border, align, fill)
        $pdf->MultiCell($colW[0], $rowHeight, 'HRS ACC', 1, 'C', true);

        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo);     // Rechazos del periodo 

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + $rowHeight);
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');

            $texto = ($num != 0.0) ? number_format(($num), 2) : '';

            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + $rowHeight);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $textoG = ($numG != 0.0) ? number_format(($numG), 2) : '';
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }

    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight + 9.5;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + 10);
        // MultiCell estándar de FPDF: (w, h, txt, border, align, fill)
        $pdf->MultiCell($colW[0], $rowHeight, 'DIAS ACC', 1, 'C', true);

        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo);     // Rechazos del periodo 

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 10);
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
            $dias = (float) ($num / 24); // 1440 minutos en un día

            $texto = ($dias != 0.0) ? number_format($dias, 2) : '';
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + 10);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $textoG = ($numG != 0.0) ? number_format(($numG / 24), 2) : '';
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }

}

function pintarTiempoAbajoMaquinas(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campo,
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',
    string $campo2,
    int $decimalesAcumulado
) {
    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];


    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y - 2.5;
    // Totales de la tabla por turno (del día)
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = '0.0';


    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    $minutosTurno = [480, 450, 510];
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY + 6);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        $totalTurno = 0.0;

        // 1) Calcular suma de HorasTrabajadas del turno i para las máquinas visibles
        $horasTrabajadasTurno = 0.0;

        foreach ($colMachines as $idxHM => $nombreMaquinaHM) {
            if (isset($mapaNombres[$nombreMaquinaHM])) {
                $noMaqHM = $mapaNombres[$nombreMaquinaHM];
                if (isset($dataCRD[$noMaqHM][$i]['HorasTrabajadas']) && is_numeric($dataCRD[$noMaqHM][$i]['HorasTrabajadas'])) {
                    $horasTrabajadasTurno += (float) $dataCRD[$noMaqHM][$i]['HorasTrabajadas'];
                }
            }
        }

        // 2) Convertir horas a minutos para el denominador dinámico
        $minutosTrabajadosTurno = $horasTrabajadasTurno * 60.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];
            $valorNum = 0.0;
            $valorStr = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campo]) && is_numeric($dataCRD[$noMaq][$i][$campo])) {
                    $valorNum = (float) $dataCRD[$noMaq][$i][$campo];
                    $valorStr = ($valorNum == 0.0) ? '' : number_format($valorNum);
                }
            }

            $totalesPorMaquina[$idx] += $valorNum;
            $totalTurno += $valorNum;

            // Minutos fijos del turno (si sigues usando esto para ptp individual)
            $minTurno = $minutosTurno[$i] ?? 0;

            // Porcentaje por máquina respecto a minutos fijos del turno (como lo tenías)
            $ptp = ($minTurno > 0) ? (($valorNum / $minTurno) * 100) : 0.0;

            // 3) Porcentaje acumulado del turno usando minutos trabajados reales (horas * 60)
            $ptpt = ($minutosTrabajadosTurno > 0) ? (($totalTurno / $minutosTrabajadosTurno) * 100) : 0.0;

            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->SetXY($cellX, $currY + 6);

            // Si quieres mostrar también el ptpt, se podría agregar en otra celda/columna
            $valorStr = ($valorNum == 0.0) ? '' : number_format($valorNum) . ' / ' . number_format($ptp, 2) . '%';
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorStr, 1, 0, 'C');
        }


        $pdf->SetXY($x + 82, $currY + 6);
        $valortTotal = ($minutosTrabajadosTurno > 0)
            ? number_format($ptpt, 2) . '%'
            : '';
        $pdf->Cell($colW[7], $rowHeight, number_format($totalTurno,0) . ' / ' . $valortTotal, 1, 0, 'C');

        $totalGeneral += $totalTurno;
    }


    // ---------------- Fila TOTAL (del día, del campo $campo) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);

    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(235, 242, 250);

    $pdf->SetXY($x - 3, $totalY + 6);
    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);
    // Calcula acumulados por máquina (minutos del día seleccionado)
    $acumArriba = sumarTiempoArribaDiaPorMaquina($dataCRD);
    $acumNum = sumarTiempoAbajoDiaPorMaquina($dataCRD);  // Tiempo abajo
    $acumDen = sumarTiempoArribaDiaPorMaquina($dataCRD); // Horas trabajadas 
    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY + 6);
        $v = $totalesPorMaquina[$idx] ?? 0.0;
        $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
        $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? '');
        $tpp = ($den > 0) ? (($num / ($den * 60)) * 100) : 0.0;
        $valorDisplay = ((float) $v === 0.0) ? '' : number_format($v) . ' / ' . number_format($tpp, 2) . '%';
        $pdf->Cell(
            $colW[$idx + 1],
            $rowHeight,
            $valorDisplay,
            1,
            0,
            'C',
            true
        );
    }

    $pdf->SetXY($x + 82, $totalY + 6);
    $minG = (float) (number_format($acumArriba['totalGeneral'], 2) ?? 0);
    $pdf->Cell($colW[7], $rowHeight, number_format($minG, 2) . ' / ' . number_format(($totalGeneral / ($minG * 60)) * 100, 2) . '%', 1, 0, 'C', true);

    // ---------------- Tiempo perdido acumulado ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + 16.5);
        // MultiCell estándar de FPDF: (w, h, txt, border, align, fill)
        $pdf->MultiCell($colW[0], $rowHeight - 1.8, 'TP ACUMULADO', 1, 'C', true);

        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo);     // Rechazos del periodo 

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 16.5);
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');  // Esta en minutos

            $texto = ($num != 0.0) ? number_format($num, 2) : '';

            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + 16.5);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $textoG = ($numG != 0.0) ? number_format($numG, 2) : '';

        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }

    // % De tiempo perdido diario (% TP DIARIO)
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + 20);
        // MultiCell estándar de FPDF: (w, h, txt, border, align, fill)
        $pdf->MultiCell($colW[0], $rowHeight, '% TP DIARIO', 1, 'C', true);

        $acumNum = sumarTiempoAbajoDiaPorMaquina($dataCRD);  // Tiempo abajo
        $acumDen = sumarTiempoArribaDiaPorMaquina($dataCRD); // Horas trabajadas 

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 20);
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
            $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? '');
            // (tiempoAbajo / HorasTrabajadas*60) * 100  
            $tpp = ($den > 0) ? (($num / ($den * 60)) * 100) : 0.0;
            $texto = ($tpp != 0.0) ? number_format($tpp, 2) . '%' : '';

            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + 20);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $denG = (float) ($acumDen['totalGeneral'] ?? 0.0);
        $tppG = ($denG > 0) ? (($numG / ($denG * 60)) * 100) : 0.0;
        $textoG = ($tppG != 0.0) ? number_format($tppG, 2) . '%' : '';

        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }

    //% Tiempo Perdido Acumulado (% TP ACC)
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + 23.5);
        $pdf->MultiCell($colW[0], $rowHeight - 1.8, '% TP ACUMULADO', 1, 'C', true);

        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo);
        $acumDen = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo2);

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 23.5);
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
            $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? '');

            // (Tiempo Perdido Acumulado / (Horas Trabajadas * 60)) * 100
            $tpp = ($den > 0) ? (($num / ($den * 60)) * 100) : 0.0;

            $texto = ($tpp != 0.0) ? number_format($tpp, 2) . '%' : '';
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + 23.5);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $denG = (float) ($acumDen['totalGeneral'] ?? 0.0);

        $tppG = ($denG > 0) ? (($numG / ($denG * 60)) * 100) : 0.0;
        $textoG = ($tppG != 0.0) ? number_format($tppG, 2) . '%' : '';

        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }

    // //UPTIME
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $bodyStartY + $rowHeight + 6;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY + 21);
        $pdf->MultiCell($colW[0], $rowHeight, 'UPTIME', 1, 'C', true);

        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo);
        $acumDen = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campo2);

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 21);
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
            $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? '');

            $tpp = ($den > 0) ? (100 - (($num / ($den * 60)) * 100)) : 0.0;

            $texto = ($tpp != 0.0) ? number_format($tpp, 2) . '%' : '';

            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
        }

        $pdf->SetXY($x + 82, $acumY + 21);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $denG = (float) ($acumDen['totalGeneral'] ?? 0.0);

        $tppG = ($denG > 0) ? (100 - ($numG / ($denG * 60) * 100)) : 0.0;

        $textoG = ($tppG != 0.0) ? number_format($tppG, 2) . '%' : '';

        $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);


    }

}

function pintarCuerpoClavesMaquinas(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campoDenominador,
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',
    string $labelAcumulado = '',
    int $decimalesAcumulado = 0,
    ?string $campoNumerador = null,
    int $decimalesPorcentaje = 1
) {
    // ---------- Config de tabla (alineada a pintarEncabezadoClaves) ----------
    $rowH = 3.5;

    // Columnas (posiciones como en tu encabezado)
    // Clave está en x+20 (width 15)
    // Descripcion en x+35 (width 30)
    // Máquinas y total con offsets de tu encabezado:
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $xClave = $x + 20;
    $wClave = 15;

    $xDesc = $x + 35;
    $wDesc = 50;

    // Offsets usados en pintarEncabezadoClaves:
    // labels = ['DESCRIPCION','BCM4','BCM3','PE10','BCM1','MP22','MP25','Total Turno'];
    // offsets = [12, 42, 57, 72, 87, 102, 117, 132];
    // y SetXY($x + $offsets[$i] + 23)
    $machineXs = [
        'PE10' => $x + 42 + 23,
        'MP21' => $x + 57 + 23,
        'MP22' => $x + 72 + 23,
        'MP23' => $x + 87 + 23,
        'MP24' => $x + 102 + 23,
        'MP25' => $x + 117 + 23,
    ];
    $wMachine = 15;

    $xTotal = $x + 132 + 23;
    $wTotal = 15;

    $bodyY = $y + 3.5; // debajo del encabezado

    // ---------- Validación mínima ----------
    if ($campoNumerador === null || $campoNumerador === '') {
        // Si no mandas numerador, no hay "num/den". Puedes decidir qué hacer.
        // Por ahora, pintamos solo el denominador como número.
        $campoNumerador = null;
    }

    // ---------- 1) Agrupar por clave ----------
    // $rows[clave] = [
    //   'Descripcion' => ...,
    //   'porMaquina' => ['BCM4' => ['num'=>..,'den'=>..], ...]
    // ]
    $rows = [];

    foreach ($dataCRD as $noMaq => $registros) {
        foreach ($registros as $r) {
            if (!isset($r['Clave']))
                continue;
            $clave = (string) $r['Clave'];

            if (!isset($rows[$clave])) {
                $rows[$clave] = [
                    'Descripcion' => $r['Descripcion'] ?? '',
                    'porMaquina' => []
                ];
            }

            $nombreMaquina = $r['NombreMaquina'] ?? null;
            if ($nombreMaquina === null || !in_array($nombreMaquina, $colMachines, true)) {
                continue;
            }

            $num = 0.0;
            $den = 0.0;

            if ($campoNumerador !== null && isset($r[$campoNumerador]) && is_numeric($r[$campoNumerador])) {
                $num = (float) $r[$campoNumerador];
            }
            if (isset($r[$campoDenominador]) && is_numeric($r[$campoDenominador])) {
                $den = (float) $r[$campoDenominador];
            }

            if (!isset($rows[$clave]['porMaquina'][$nombreMaquina])) {
                $rows[$clave]['porMaquina'][$nombreMaquina] = ['num' => 0.0, 'den' => 0.0];
            }

            $rows[$clave]['porMaquina'][$nombreMaquina]['num'] += $num;
            $rows[$clave]['porMaquina'][$nombreMaquina]['den'] += $den;
        }
    }

    // Ordenar claves (numérico si aplica)
    uksort($rows, function ($a, $b) {
        $na = is_numeric($a) ? (float) $a : $a;
        $nb = is_numeric($b) ? (float) $b : $b;
        return $na <=> $nb;
    });

    // ---------- 2) Imprimir filas ----------
    $pdf->SetFont('Arial', '', 4.5);
    $currY = $bodyY;

    // Totales globales por máquina
    $totMachine = [];
    foreach ($colMachines as $m)
        $totMachine[$m] = ['num' => 0.0, 'den' => 0.0];
    $totGeneral = ['num' => 0.0, 'den' => 0.0];

    foreach ($rows as $clave => $info) {

        // Clave
        $pdf->SetXY($xClave - 20, $currY - 2);
        $pdf->Cell($wClave, $rowH, $clave, 1, 0, 'C');

        // Descripción (recorta si es muy larga)
        $desc = mb_convert_encoding(mb_strtoupper($info['Descripcion'], 'UTF-8'), 'ISO-8859-1', 'UTF-8');
        $descCorta = mb_substr($desc, 0, 34);
        $pdf->SetXY($xDesc - 20, $currY - 2);
        $pdf->Cell($wDesc, $rowH, $descCorta, 1, 0, 'L');

        // Total por fila (suma de máquinas)
        $rowTot = ['num' => 0.0, 'den' => 0.0];

        // Máquinas
        foreach ($colMachines as $m) {
            $num = (float) ($info['porMaquina'][$m]['num'] ?? 0.0);
            $den = (float) ($info['porMaquina'][$m]['den'] ?? 0.0);

            // Acumular totales
            $totMachine[$m]['num'] += $num;
            $totMachine[$m]['den'] += $den;

            $rowTot['num'] += $num;
            $rowTot['den'] += $den;

            // Texto "num / den"
            $txt = '';
            if ($num != 0.0 || $den != 0.0) {
                $txt = number_format($num, 0) . ' / ' . number_format($den, $decimalesAcumulado);
            }

            $pdf->SetXY($machineXs[$m], $currY - 2);
            $pdf->Cell($wMachine, $rowH, $txt, 1, 0, 'C');
        }

        // TotalTurno (por clave)
        $totGeneral['num'] += $rowTot['num'];
        $totGeneral['den'] += $rowTot['den'];

        $txtTot = '';
        if ($rowTot['num'] != 0.0 || $rowTot['den'] != 0.0) {
            $txtTot = number_format($rowTot['num'], 0) . ' / ' . number_format($rowTot['den'], $decimalesAcumulado);
        }

        $pdf->SetXY($xTotal, $currY - 2);
        $pdf->Cell($wTotal, $rowH, $txtTot, 1, 0, 'C');

        $currY += $rowH;
    }

    // ---------- 3) Fila TOTAL ----------
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(235, 242, 250);

    // Clave vacío, Descripción = TOTAL
    $pdf->SetXY($xClave - 20, $currY - 2);
    $pdf->Cell($wClave, $rowH, '', 1, 0, 'C', true);

    $pdf->SetXY($xDesc - 20, $currY - 2);
    $pdf->Cell($wDesc, $rowH, 'TOTAL', 1, 0, 'L', true);

    foreach ($colMachines as $m) {
        $num = (float) $totMachine[$m]['num'];
        $den = (float) $totMachine[$m]['den'];
        $txt = ($num != 0.0 || $den != 0.0)
            ? number_format($num, 0) . ' / ' . number_format($den, $decimalesAcumulado)
            : '';

        $pdf->SetXY($machineXs[$m], $currY - 2);
        $pdf->Cell($wMachine, $rowH, $txt, 1, 0, 'C', true);
    }

    $txtG = ($totGeneral['num'] != 0.0 || $totGeneral['den'] != 0.0)
        ? number_format($totGeneral['num'], 0) . ' / ' . number_format($totGeneral['den'], $decimalesAcumulado)
        : '';

    $pdf->SetXY($xTotal, $currY - 2);
    $pdf->Cell($wTotal, $rowH, $txtG, 1, 0, 'C', true);

    $currY += $rowH;

}

function pintarMermaEmpaque(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campoDenominador,     // Ej: 'Cortes' (o 'Rechazos' si solo quieres pintar ese campo)
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',  // Para acumulado del denominador (ej 'Cortes')
    string $labelAcumulado = '',
    int $decimalesAcumulado = 0,  // decimales para acumulado en modo NO porcentaje (o si quieres controlar el formato)
    ?string $campoNumerador = null, // NUEVO: Ej 'Rechazos' para % Merma
    int $decimalesPorcentaje = 1,    // NUEVO: decimales para el % (ej 1)
    array $dataCortesRechazos = [],
    string $campoUSTD,
    string $campoRechazos
) {
    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    $modoPorcentaje = ($campoNumerador !== null && $campoNumerador !== '');

    $sumDenGeneral = 0.0;
    $sumNumGeneral = 0.0;

    //Acumulados por día
    $totalPiezasMaquina = array_fill(0, count($colMachines), 0.0);
    $totalCortesMaquina = array_fill(0, count($colMachines), 0.0);
    $totalRechazosMaquina = array_fill(0, count($colMachines), 0.0);

    $totalGeneral = 0.0;
    $totalCortes = 0.0;
    $totalRechazos = 0.0;

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------
    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        $totalTurno = 0.0;
        $totalCortesTurno = 0.0;
        $totalRechazosTurno = 0.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];
            $valorPiezas = 0.0;
            $valorCortes = 0.0;
            $valorRechazos = 0.0;
            $mermaEmpaque = 0.0;
            $ustdEmpaque = 0.0;
            $mermaEmpaqueTurno = 0.0;
            $ustdEmpaqueTurno = 0.0;
            $valorStr = '';
            $valorSTRTotalTurno = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campoNumerador]) && is_numeric($dataCRD[$noMaq][$i][$campoNumerador])) {
                    $valorPiezas = (float) $dataCRD[$noMaq][$i][$campoNumerador];

                }

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campoAcumulado]) && is_numeric($dataCRD[$noMaq][$i][$campoAcumulado])) {
                    $valorCortes = (float) $dataCRD[$noMaq][$i][$campoAcumulado];

                }

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campoRechazos]) && is_numeric($dataCRD[$noMaq][$i][$campoRechazos])) {
                    $valorRechazos = (float) $dataCRD[$noMaq][$i][$campoRechazos];

                }
            }


            // Valores por turno
            $mermaEmpaque = $valorCortes - $valorPiezas - $valorRechazos;
            $ustdEmpaque = number_format($mermaEmpaque / 150.0, 2);
            $valorStr = ($mermaEmpaque != 0.0) ? "$mermaEmpaque / $ustdEmpaque" : '';

            $pdf->SetXY($cellX, $currY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorStr, 1, 0, 'C');

            // Totales turno maquinas
            $totalTurno += $valorPiezas;
            $totalCortesTurno += $valorCortes;
            $totalRechazosTurno += $valorRechazos;

            $mermaEmpaqueTurno = $totalCortesTurno - $totalTurno - $totalRechazosTurno;
            $ustdEmpaqueTurno = number_format($mermaEmpaqueTurno / 150.0, 2);

            $valor = $mermaEmpaqueTurno / 150.0;
            $truncado2 = $valor >= 0 ? floor($valor * 100) / 100 : ceil($valor * 100) / 100;
            $ustdEmpaqueTurno = number_format($truncado2, 2, '.', '');

            $valorSTRTotalTurno = ($mermaEmpaqueTurno != 0.0) ? "$mermaEmpaqueTurno / $ustdEmpaqueTurno" : '';

            $totalPiezasMaquina[$idx] += $valorPiezas;
            $totalCortesMaquina[$idx] += $valorCortes;
            $totalRechazosMaquina[$idx] += $valorRechazos;

        }

        $pdf->SetXY($x + 82, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $valorSTRTotalTurno, 1, 0, 'C');

        $totalGeneral += $totalTurno;
        $totalCortes += $totalCortesTurno;
        $totalRechazos += $totalRechazosTurno;
    }


    // ---------------- Fila TOTAL (del día) ----------------
    $totalY = $bodyStartY + ($rowHeight);
    $acumY = $totalY + $rowHeight + 3.5;
    $pdf->SetFont('Arial', 'B', 4);
    $pdf->SetFillColor(235, 242, 250);

    $pdf->SetXY($x - 3, $acumY);
    $pdf->MultiCell($colW[0], $rowHeight - 1.75, 'TOTAL PIEZAS DIA', 1, 'C', true);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);
        $mermaEmpaqueDia = $totalCortesMaquina[$idx] - $totalPiezasMaquina[$idx] - $totalRechazosMaquina[$idx] ?? 0.0;

        $valor = $mermaEmpaqueDia / 150.0;
        $truncado2 = $valor >= 0 ? floor($valor * 100) / 100 : ceil($valor * 100) / 100;
        $ustdDia = number_format($truncado2, 2, '.', '');

        $valorDisplay = ((float) $mermaEmpaqueDia === 0.0) ? '' : "$mermaEmpaqueDia / $ustdDia";
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell(
            $colW[$idx + 1],
            $rowHeight,
            $valorDisplay,
            1,
            0,
            'C',
            true
        );
    }
    $pdf->SetXY($x + 82, $acumY);
    $pdf->SetFont('Arial', 'B', 4.5);
    $mermaEmpaqueGeneral = $totalCortes - $totalGeneral - $totalRechazos;
    $valorG = $mermaEmpaqueGeneral / 150.0;
    $texto = ($mermaEmpaqueGeneral != 0.0) ? "$mermaEmpaqueGeneral / " . number_format($valorG, 2) : '';
    $pdf->Cell($colW[7], $rowHeight, $texto, 1, 0, 'C', true);

    // ---------------- Fila Acumulado ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $totalY + $rowHeight + 2;

        $pdf->SetFillColor(235, 242, 250);
        $pdf->SetFont('Arial', 'B', 4);

        $pdf->SetXY($x - 3, $acumY + 5);
        $pdf->SetFillColor(235, 242, 250);
        $pdf->MultiCell($colW[0], $rowHeight - 1.75, 'TOTAL PIEZAS ACUMULADO', 1, 'C', true);

        if ($modoPorcentaje) {
            $acumPiezas = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoNumerador);     // PIEZAS 
            $acumCortes = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);    // CORTES
            $acumRechazos = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoRechazos);   // RECHAZOS


            foreach ($colMachines as $idx => $nombreMaquina) {
                $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY + 5);
                $piezasAcumulado = (float) ($acumPiezas['porMaquina'][$nombreMaquina] ?? 0.0);
                $cortesAcumulado = (float) ($acumCortes['porMaquina'][$nombreMaquina] ?? 0.0);
                $rechazosAcumulado = (float) ($acumRechazos['porMaquina'][$nombreMaquina] ?? 0.0);

                $mermaEmpaqueAcumulado = $cortesAcumulado - $piezasAcumulado - $rechazosAcumulado;
                $ustdAcumulado = number_format($mermaEmpaqueAcumulado / 150.0, 2);
                $texto = ($mermaEmpaqueAcumulado != 0.0) ? "$mermaEmpaqueAcumulado / $ustdAcumulado" : '';
                $pdf->SetFont('Arial', 'B', 4.5);
                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);
            }

            $pdf->SetXY($x + 82, $acumY + 5);

            $piezasG = (float) ($acumPiezas['totalGeneral'] ?? '');
            $cortesG = (float) ($acumCortes['totalGeneral'] ?? '');
            $rechazosG = (float) ($acumRechazos['totalGeneral'] ?? 0.0);
            $mermaEmpaqueG = $cortesG - $piezasG - $rechazosG;
            $ustdG = number_format($mermaEmpaqueG / 150.0, 2);
            $textoG = ($mermaEmpaqueG != 0.0) ? "$mermaEmpaqueG / $ustdG" : '';
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[7], $rowHeight, $textoG, 1, 0, 'C', true);

        } else {
            // Modo normal: acumulado de un solo campo
            $acum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);

            foreach ($colMachines as $idx => $nombreMaquina) {
                $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

                $v = $acum['porMaquina'][$nombreMaquina] ?? 0.0;
                $texto = ((float) $v === 0.0) ? '' : number_format((float) $v, $decimalesAcumulado);

                $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'R', true);
            }

            $pdf->SetXY($x + 82, $acumY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[7], $rowHeight, $acum, 1, 0, 'R', true);
        }
    }

}

function pintarOEEMaquinas(
    FPDF $pdf,
    float $x,
    float $y,
    array $dataCRD,
    array $mapaNombres,
    string $campoDenominador,     // Ej: 'Cortes' (o 'Rechazos' si solo quieres pintar ese campo)
    bool $agregarAcumulado = false,
    array $dataPeriodo = [],
    string $campoAcumulado = '',  // Para acumulado del denominador (ej 'Cortes')
    string $labelAcumulado = '',
    int $decimalesAcumulado = 0,  // decimales para acumulado en modo NO porcentaje (o si quieres controlar el formato)
    ?string $campoNumerador = null, // NUEVO: Ej 'Rechazos' para % Merma
    int $decimalesPorcentaje = 1,    // NUEVO: decimales para el % (ej 1)
    array $dataCortesRechazos = [],
    string $campoUSTD,
    string $campoRechazos,
    string $campoTiempoAbajo,
    string $campoHorasTrabajadas,
    array $dataTiempos
) {
    $rowHeight = 3.5;
    $labelsTurno = ['TURNO 1', 'TURNO 2', 'TURNO 3'];

    $colOffsets = [-3, 10, 22, 34, 46, 58, 70, 82];
    $colW = [13, 12, 12, 12, 12, 12, 12, 13];
    $colMachines = ['PE10', 'MP21', 'MP22', 'MP23', 'MP24', 'MP25'];

    $bodyStartY = $y + 3.5;

    $modoPorcentaje = ($campoNumerador !== null && $campoNumerador !== '');

    // Acumuladores por día (sumas para calcular % correctos)
    $sumDenPorMaquina = array_fill(0, count($colMachines), 0.0); // Cortes
    $sumNumPorMaquina = array_fill(0, count($colMachines), 0.0); // Rechazos

    $sumDenGeneral = 0.0;
    $sumNumGeneral = 0.0;

    // Si NO es porcentaje, mantenemos acumuladores tipo "antes" para pintar totals como sumas simples
    $totalesPorMaquina = array_fill(0, count($colMachines), 0.0);
    $totalGeneral = 0.0;

    //Variables de calculos
    $ptp = 0.0;
    $ptpt = 0.0;
    $mmDia = 0.0;
    $mTT = 0.0;

    // Valores de minutos de cada turno
    $minutosTurno = [480, 450, 510];

    // ---------------- Turnos 1..3 (del día final seleccionado) ----------------

    for ($i = 0; $i < 3; $i++) {
        $currY = $bodyStartY + ($i * $rowHeight);

        $pdf->SetXY($x - 3, $currY);
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[0], $rowHeight, $labelsTurno[$i], 1, 0, 'C');

        // Para la columna TOTAL por turno
        $sumDenTurno = 0.0;
        $sumNumTurno = 0.0;
        $totalTurnoSimple = 0.0; // solo si NO es porcentaje (suma simple)
        $totalTurno = 0.0;

        // 1) Calcular suma de HorasTrabajadas del turno i para las máquinas visibles
        $horasTrabajadasTurno = 0.0;
        foreach ($colMachines as $idxHM => $nombreMaquinaHM) {
            if (isset($mapaNombres[$nombreMaquinaHM])) {
                $noMaqHM = $mapaNombres[$nombreMaquinaHM];
                if (isset($dataCRD[$noMaqHM][$i][$campoHorasTrabajadas]) && is_numeric($dataCRD[$noMaqHM][$i][$campoHorasTrabajadas])) {
                    $horasTrabajadasTurno += (float) $dataCRD[$noMaqHM][$i][$campoHorasTrabajadas];
                }
            }
        }

        // 2) Convertir horas a minutos para el denominador dinámico
        $minutosTrabajadosTurno = $horasTrabajadasTurno * 60.0;

        foreach ($colMachines as $idx => $nombreMaquina) {
            $cellX = $x + $colOffsets[$idx + 1];

            $den = 0.0; // Cortes (denominador)
            $num = 0.0; // Rechazos (numerador)
            $textoCelda = '';
            $valorNum = 0.0;
            $valorStr = '';

            if (isset($mapaNombres[$nombreMaquina])) {
                $noMaq = $mapaNombres[$nombreMaquina];

                // Denominador
                if (isset($dataCRD[$noMaq][$i][$campoDenominador]) && is_numeric($dataCRD[$noMaq][$i][$campoDenominador])) {
                    $den = (float) $dataCRD[$noMaq][$i][$campoDenominador];
                }

                // Numerador (si modo porcentaje)
                if ($modoPorcentaje && isset($dataCRD[$noMaq][$i][$campoNumerador]) && is_numeric($dataCRD[$noMaq][$i][$campoNumerador])) {
                    $num = (float) $dataCRD[$noMaq][$i][$campoNumerador];
                }

                if (isset($dataCRD[$noMaq][$i]) && isset($dataCRD[$noMaq][$i][$campoTiempoAbajo]) && is_numeric($dataCRD[$noMaq][$i][$campoTiempoAbajo])) {
                    $valorNum = (float) $dataCRD[$noMaq][$i][$campoTiempoAbajo];
                    $valorStr = ($valorNum == 0.0) ? '' : number_format($valorNum);
                }

            }

            $totalesPorMaquina[$idx] += $valorNum;
            $totalTurno += $valorNum;
            $sumDenPorMaquina[$idx] += $den;
            $sumNumPorMaquina[$idx] += $num;

            $sumDenTurno += $den;
            $sumNumTurno += $num;

            $sumDenGeneral += $den;
            $sumNumGeneral += $num;

            // Minutos fijos del turno (si sigues usando esto para ptp individual)
            $minTurno = $minutosTurno[$i] ?? 0;

            // Porcentaje por máquina respecto a minutos fijos del turno (como lo tenías)
            $ptp = ($minTurno > 0) ? (($valorNum / $minTurno) * 100) : 0.0;

            // 3) Porcentaje acumulado del turno usando minutos trabajados reales (horas * 60)
            $ptpt = ($minutosTrabajadosTurno > 0) ? (($totalTurno / $minutosTrabajadosTurno) * 100) : 0.0;

            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->SetXY($cellX, $currY + 6);

            // Texto de celda: % por máquina
            if ($den > 0) {
                $mmDia = (1 - ($num / $den)) * 100;
            } else {
                $textoCelda = '';
            }

            $oeeDia = ((100 - $ptp) * (100 - $mmDia)) / 100;
            $valorStr = ($den > 0) ? number_format($oeeDia, 2) . '%' : '';

            $pdf->SetXY($cellX, $currY);
            $pdf->SetFont('Arial', 'B', 4.5);
            $pdf->Cell($colW[$idx + 1], $rowHeight, $valorStr, 1, 0, 'C');
        }

        // Columna TOTAL por turno
        $pdf->SetXY($x + 82, $currY);

        $mTT = ($sumDenTurno > 0) ? ((1 - ($sumNumTurno / $sumDenTurno)) * 100) : 0.0;

        $oEETT = ((100 - $ptpt) * (100 - $mTT)) / 100;
        $textoTotal = ($sumDenTurno > 0) ? number_format($oEETT, 2) . '%' : '';
        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $textoTotal, 1, 0, 'C');
        $totalGeneral += $totalTurno;

    }

    // ---------------- Fila TOTAL (del día) ----------------
    $totalY = $bodyStartY + (3 * $rowHeight);

    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->SetFillColor(235, 242, 250);

    $pdf->SetXY($x - 3, $totalY);

    $pdf->Cell($colW[0], $rowHeight, 'TOTAL DIA', 1, 0, 'C', true);
    $acumArriba = sumarTiempoArribaDiaPorMaquina($dataTiempos);

    foreach ($colMachines as $idx => $nombreMaquina) {
        $pdf->SetXY($x + $colOffsets[$idx + 1], $totalY);
        $dena = (float) ($acumArriba['porMaquina'][$nombreMaquina] ?? '');

        $den = $sumDenPorMaquina[$idx] ?? 0.0;
        $num = $sumNumPorMaquina[$idx] ?? 0.0;
        $tpd = $totalesPorMaquina[$idx] ?? 0.0;


        if ($den > 0) {
            // % MD =  
            $md = (1 - ($num / $den)) * 100;
            $ptpd = ($tpd / ($dena * 60)) * 100;

            $oeeD = ((100 - $md) * (100 - $ptpd)) / 100;

            $texto = number_format($oeeD, 2) . '%';
        } else {
            $texto = '';
        }
        $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);

    }

    // TOTAL General Dia (columna final)
    $pdf->SetXY($x + 82, $totalY);
    $minG = (float) (number_format($acumArriba['totalGeneral'], 2) ?? 0);
    $pctG = ($sumDenGeneral > 0) ? ((1 - ($sumNumGeneral / $sumDenGeneral)) * 100) : 0.0;
    $tptD = ($totalGeneral / ($minG * 60)) * 100;
    $oeeTD = ((100 - $pctG) * (100 - $tptD)) / 100;
    $pdf->SetFont('Arial', 'B', 4.5);
    $pdf->Cell($colW[7], $rowHeight, number_format($oeeTD, 2) . '%', 1, 0, 'C', true);


    // ---------------- Fila Total ACC % MM ----------------
    if ($agregarAcumulado && $campoAcumulado !== '' && !empty($dataPeriodo)) {
        $acumY = $totalY + $rowHeight;

        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Arial', 'B', 4.5);

        $pdf->SetXY($x - 3, $acumY);
        $pdf->SetFillColor(235, 242, 250);
        $pdf->MultiCell($colW[0], $rowHeight, 'TOTAL ACC', 1, 'C', true);

        $acumUSTD = sumarCampoCortesRechazos($dataCortesRechazos, $mapaNombres, $campoUSTD);     // USTD 
        $acumDen = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoAcumulado);     // Cortes del periodo
        $acumNum = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoNumerador);     // TotalPiezas
        $acumHTrabajadas = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoHorasTrabajadas); // Horas trabajadas 
        $acumTAbajo = sumarCampoCortesRechazos($dataPeriodo, $mapaNombres, $campoTiempoAbajo);  // Tiempo abajo

        foreach ($colMachines as $idx => $nombreMaquina) {
            $pdf->SetXY($x + $colOffsets[$idx + 1], $acumY);

            $ustd = (float) ($acumUSTD['porMaquina'][$nombreMaquina] ?? '');
            $num = (float) ($acumNum['porMaquina'][$nombreMaquina] ?? '');
            $den = (float) ($acumDen['porMaquina'][$nombreMaquina] ?? '');
            $totalPanal = abs((($ustd * 150) - $den));

            $tiempoP = (float) ($acumTAbajo['porMaquina'][$nombreMaquina] ?? '');
            $horasT = (float) ($acumHTrabajadas['porMaquina'][$nombreMaquina] ?? '');

            if ($den > 0) {
                // (1 - (Pañales / Cortes) ) *100
                // $num = totalPanal
                //  $totalPanal = ($ustd * 150) - $cortes;
                // (1 - (Pañales / Cortes) ) *100
                $pct = (1 - ($num / $den)) * 100; // CALCULO A REVISAR  
                $tpAcc = ($horasT > 0) ? (($tiempoP / ($horasT * 60)) * 100) : 0.0;
                $oeeAcc = ((100 - $pct) * (100 - $tpAcc)) / 100;
                $texto = number_format($oeeAcc, 2, '.', '') . '%';
            } else {
                $texto = '';
            }
            $pdf->Cell($colW[$idx + 1], $rowHeight, $texto, 1, 0, 'C', true);

        }

        $pdf->SetXY($x + 82, $acumY);
        $numG = (float) ($acumNum['totalGeneral'] ?? '');
        $denG = (float) ($acumDen['totalGeneral'] ?? '');
        $horasACCG = (float) ($acumHTrabajadas['totalGeneral'] ?? 0.0);
        $tiempoAbajoG = (float) ($acumTAbajo['totalGeneral'] ?? 0.0);
        $numGTexto = '';
        if ($denG > 0) {
            $pctG = (1 - ($numG / $denG)) * 100;
            $tpAccG = ($horasACCG > 0) ? (($tiempoAbajoG / ($horasACCG * 60)) * 100) : 0.0;
            $oeeAccG = ((100 - $pctG) * (100 - $tpAccG)) / 100;
            $numGTexto = number_format($oeeAccG, 2) . '%';
        }

        $pdf->SetFont('Arial', 'B', 4.5);
        $pdf->Cell($colW[7], $rowHeight, $numGTexto, 1, 0, 'C', true);


    }

}

// ========================= COMIENZO REPORTE =========================

$pdf = new PDF('P', 'mm', array(220, 280));
$pdf->AddPage();

//------------------------------------------- Coordenadas --------------------------------------------------------
$x = $pdf->GetX();
$y = $pdf->GetY();
//------------------------------------------ Encabezado del reporte ----------------------------------------------
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(65, 147, 199);
$pdf->SetDrawColor(65, 147, 199);
$texto = mb_convert_encoding("REPORTE DE PRODUCCIÓN PAÑAL", 'ISO-8859-1', 'UTF-8');

$pdf->SetXY($x + 75, $y - 8);
$pdf->Write(10, $texto);
$pdf->SetXY($x + 165, $y - 8);

$fechaInicioFormateada = formatFecha($fechai);
$fechaFinFormateada = formatFecha($fechaf);
$pdf->SetFont('Arial', 'B', 5);
$pdf->Write(10, "DE: ");
$pdf->SetTextColor(143, 139, 139);
$pdf->Write(10, $fechaInicioFormateada);
$pdf->SetX($x + 183);
$pdf->SetTextColor(65, 147, 199);
$pdf->Write(10, "A: ");
$pdf->SetTextColor(143, 139, 139);
$pdf->Write(10, $fechaFinFormateada);
$pdf->SetXY($x - 5, $y - 2);
$pdf->Cell(20, 5, '___________________________________________________________________________________________________________________________________________________________________________________________________________________');
$endY = $pdf->GetY();

// Construye mapa de nombres una vez
$mapaNombres = construirMapaNombres($dataPeriodo);

//------------------------------------------ TABLA 1: STD (USTD) ------------------------------------------------
$tabla1_x = $x;
$tabla1_y = $endY + 13;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla1_x + 41, $tabla1_y - 10);
$pdf->Write(10, mb_convert_encoding("USTD", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla1_x, $tabla1_y);
pintarCuerpoTurnos(
    $pdf,
    $tabla1_x,
    $tabla1_y,
    $dataDia,
    $mapaNombres,
    'TotalUSTD',
    true,
    $dataPeriodo,
    'TotalUSTD',
    'ACUMULADO',
    2,
    $dataPeriodoTiempos,
    'HorasTrabajadas',
);



//------------------------------------------ TABLA 2: REALES ----------------------------------------------------
$tabla2_x = $x + 106;
$tabla2_y = $endY + 13;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla2_x + 39, $tabla2_y - 10);
$pdf->Write(10, mb_convert_encoding("REALES", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla2_x, $tabla2_y);
pintarCuerpoReales(
    $pdf,
    $tabla2_x,
    $tabla2_y,
    $dataDia,
    $mapaNombres,
    'TotalReal',
    true,
    $dataPeriodo,
    'TotalReal',
    'ACUMULADO',
    0,
);

//------------------------------------------ TABLA 3: CORTES ----------------------------------------------------
$tabla3_x = $x;
$tabla3_y = $tabla1_y + 32;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla3_x + 39, $tabla3_y - 12);
$pdf->Write(10, mb_convert_encoding("CORTES", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla3_x, $tabla3_y - 2);
pintarCuerpoCortesRechazos(
    $pdf,
    $tabla3_x,
    $tabla3_y - 4,
    $dataCRD,
    $mapaNombres,
    'Cortes',
    true,
    $dataPeriodoCR,
    'Cortes',
    'ACUMULADO',
    0
);

//------------------------------------------ TABLA 4: RECHAZOS ----------------------------------------------------
$tabla4_x = $x + 106;
$tabla4_y = $tabla1_y + 30;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla4_x + 37, $tabla4_y - 12);
$pdf->Write(10, mb_convert_encoding("RECHAZOS", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla4_x, $tabla4_y - 2);
pintarCuerpoCortesRechazos(
    $pdf,
    $tabla4_x,
    $tabla4_y - 4,
    $dataCRD,
    $mapaNombres,
    'Rechazos',
    true,
    $dataPeriodoCR,
    'Rechazos',
    'ACUMULADO',
    0
);

//------------------------------------------ TABLA 5: MERMA MAQUINA ----------------------------------------------------
$tabla5_x = $x;
$tabla5_y = $tabla1_y + 60;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla5_x + 31, $tabla5_y - 12);
$pdf->Write(10, mb_convert_encoding("% MERMA MÁQUINA", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla5_x, $tabla5_y - 2);
pintarCuerpoMermaMaquinas(
    $pdf,
    $tabla5_x,
    $tabla5_y - 4,
    $dataCRD,
    $mapaNombres,
    'Cortes',             // denominador
    true,
    $dataPeriodoCR,
    'Cortes',             // acumulado denominador
    'TOTAL ACC',
    0,
    'Rechazos',           // numerador (NUEVO)
    1                     // decimales del porcentaje
);

//------------------------------------------ TABLA 6: MERMA TOTAL ----------------------------------------------------
$tabla6_x = $x + 106;
$tabla6_y = $tabla1_y + 60;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla6_x + 32, $tabla6_y - 12);
$pdf->Write(10, mb_convert_encoding("% MERMA TOTAL", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla6_x, $tabla6_y - 2);
pintarMermaMaquinasTotal(
    $pdf,
    $tabla6_x,
    $tabla6_y - 4,
    $dataCRD,
    $mapaNombres,
    'Cortes',             // denominador
    true,
    $dataPeriodoCR,
    'Cortes',            // acumulado denominador
    'Cortes Acumulados',
    0,
    'TotalPiezas',           // numerador 
    1,                  // decimales del porcentaje
    $dataPeriodo,
    'TotalUSTD',
    'Rechazos'

);

//------------------------------------------ TABLA 7: TIEMPO PERDIDO ----------------------------------------------------
$tabla8_x = $x;
$tabla8_y = $tabla1_y + 88;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla8_x + 34, $tabla8_y - 12);
$pdf->Write(10, mb_convert_encoding("% TPT (min / %)", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla8_x, $tabla8_y - 2);
pintarTiempoAbajoMaquinas(
    $pdf,
    $tabla8_x,
    $tabla8_y - 4,
    $dataTiempos,
    $mapaNombres,
    'TiempoAbajo',
    true,
    $dataPeriodoTiempos,
    'TiempoAbajo',
    'HorasTrabajadas',
    0
);
//------------------------------------------ TABLA 8: HORAS TRABAJADAS ----------------------------------------------------
$tabla7_x = $x;
$tabla7_y = $tabla1_y + 127;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla7_x + 20, $tabla7_y - 12);
$pdf->Write(10, mb_convert_encoding("HORAS TRABAJADAS (MIN / HORAS)", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla7_x, $tabla7_y - 2);
pintarTiemposMaquinas(
    $pdf,
    $tabla7_x,
    $tabla7_y - 4,
    $dataTiempos,
    $mapaNombres,
    'HorasTrabajadas',
    true,
    $dataPeriodoTiempos,
    'TiempoArriba',
    'TiempoArriba Acumulados',
    0
);


$tabla9_x = $x + 106;
$tabla9_y = $tabla1_y + 92;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla9_x + 22, $tabla9_y - 12);
$pdf->Write(10, mb_convert_encoding("MERMA EMPAQUE (PIEZAS / USTD)", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla9_x, $tabla9_y - 2);
pintarMermaEmpaque(
    $pdf,
    $tabla9_x,
    $tabla9_y - 4,
    $dataCRD,
    $mapaNombres,
    'Cortes',             // denominador
    true,
    $dataPeriodoCR,
    'Cortes',            // acumulado denominador
    'Cortes Acumulados',
    0,
    'TotalPiezas',           // numerador 
    1,                  // decimales del porcentaje
    $dataPeriodo,
    'TotalUSTD',
    'Rechazos'

);
// TABLA OEE
$tabla10_x = $x + 106;
$tabla10_y = $tabla1_y + 122;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla10_x + 42, $tabla10_y - 12);
$pdf->Write(10, mb_convert_encoding("OEE", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoTurnos($pdf, $tabla10_x, $tabla10_y - 2);
pintarOEEMaquinas(
    $pdf,
    $tabla10_x,
    $tabla10_y - 4,
    $dataCRD,
    $mapaNombres,
    'Cortes',             // denominador
    true,
    $dataPeriodoCR,
    'Cortes',            // acumulado denominador
    'Cortes Acumulados',
    0,
    'TotalPiezas',           // numerador 
    1,                  // decimales del porcentaje
    $dataPeriodo,
    'TotalUSTD',
    'Rechazos',
    'TiempoAbajo',
    'HorasTrabajadas',
    $dataTiempos
);


//------------------------------------------ TABLA 9: CLAVES DE PRODUCCIÓN ----------------------------------------------------
$tabla9_x = $x + 15;
$tabla9_y = $tabla1_y + 165;
$pdf->SetTextColor(30, 144, 227);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY($tabla9_x + 55, $tabla9_y - 14);
$pdf->Write(10, mb_convert_encoding("CLAVES DE PRODUCCIÓN (REALES / USTD)", 'ISO-8859-1', 'UTF-8'));
pintarEncabezadoClaves($pdf, $tabla9_x, $tabla9_y - 4);
pintarCuerpoClavesMaquinas(
    $pdf,
    $tabla9_x,
    $tabla9_y - 4,
    $dataClaves,
    $mapaNombres,
    'USTD',             // denominador
    true,
    $dataClavesP,
    'Cortes',             // acumulado denominador
    'Cortes Acumulados',
    0,
    'Reales',           // numerador (NUEVO)
    1                     // decimales del porcentaje

);

$pdf->AddPage();
//------------------------------------------------- NUEVA PAGINA --------------------------------------------------------------
//------------------------------------------ PLAN PRODUCCION VS PRODUCCION ----------------------------------------------------
// Para claves con plan de produccion
$dataPlanProduccion = $departamentosObj->getInfoPlanProduccion($departamento, $fechai, $fechaf);
function planStyles(): array
{
    return [
        'margins' => ['l' => 10, 't' => 12, 'r' => 10, 'b' => 12],
        'gutter' => 6,          // separación entre columnas
        'rowH' => 6,          // alto de fila
        'font' => ['family' => 'Arial'], // fuente base
        'colors' => [
            'headerBlue' => [205, 221, 250],   // barra de título/encabezado
            'subHeader' => [205, 221, 250], // subencabezado gris claro
            'band' => [245, 245, 245], // banda total
            'textLight' => [255, 255, 255], // texto sobre azul
            'textDark' => [0, 0, 0],
            'line' => [150, 150, 150],
        ],
        // Anchos relativos de columnas dentro de la tabla:
        // CLAVE, DESCRIPCION, REALES, STD, PROGRAMA, DIF
        'colsPct' => [8, 49, 10, 10, 13, 10],
        // Texto de títulos
        'labels' => [
            'producto' => 'TIPO PAÑAL',
            'tamano' => 'TAMAÑO PAÑAL',
            'totalTamano' => 'TOTAL TAMAÑO',
            'totalTipo' => 'TOTAL TIPO',
            'colHeaders' => ['CLAVE', 'DESCRIPCIÓN', 'REALES', 'STD', 'PROGRAMA', 'DIF'],
        ]
    ];
}


function ordenEtapaIndex(string $etapa): int
{
    // Mapeo con normalización básica (lowercase y sin espacios extra).
    // Agregamos alias comunes por si el SP cambia mayúsculas o tildes.
    $map = [
        'recién nacido' => 1,
        'chico' => 2,
        'mediano' => 3,
        'grande' => 4,
        'jumbo' => 5,
        'extra jumbo' => 6,
    ];

    // Normalizar: lower, trim y colapsar espacios
    $e = mb_strtolower(trim($etapa), 'UTF-8');
    $e = preg_replace('/\s+/', ' ', $e ?? '');

    return $map[$e] ?? 1000; // no mapeado => al final
}


function agregarFilasPorClave(array $list): array
{
    $byClave = [];

    foreach ($list as $r) {
        $clave = (string) ($r['Clave'] ?? '');
        if ($clave === '')
            continue;

        if (!isset($byClave[$clave])) {
            $byClave[$clave] = [
                'Clave' => $clave,
                'Descripcion' => (string) ($r['Descripcion'] ?? ''),
                'Reales' => 0.0,
                'USTD' => 0.0,
                'PlanProduccion' => 0.0,
                'DiferenciaUSTD' => 0.0,
            ];
        }

        // Mantener la descripción "más informativa" (la más larga/no vacía)
        $descNueva = (string) ($r['Descripcion'] ?? '');
        if ($descNueva !== '' && mb_strlen($descNueva, 'UTF-8') > mb_strlen($byClave[$clave]['Descripcion'], 'UTF-8')) {
            $byClave[$clave]['Descripcion'] = $descNueva;
        }

        // Sumar métricas
        $byClave[$clave]['Reales'] += (float) ($r['Reales'] ?? 0);
        $byClave[$clave]['USTD'] += (float) ($r['USTD'] ?? 0);
        $byClave[$clave]['PlanProduccion'] += (float) ($r['PlanProduccion'] ?? 0);
        $byClave[$clave]['DiferenciaUSTD'] += (float) ($r['DiferenciaUSTD'] ?? 0);
    }

    // Orden por Clave (natural, útil si son numéricas)
    ksort($byClave, SORT_NATURAL);

    // Si prefieres ordenar por Descripción:
    // usort($byClave, fn($a, $b) => strcasecmp($a['Descripcion'], $b['Descripcion']));

    return array_values($byClave);
}



function agruparPlanPorProductoEtapa(array $dataPlanProduccion): array
{
    $bloques = [];
    foreach ($dataPlanProduccion as $idProducto => $rows) {
        if (empty($rows))
            continue;

        $productoNombre = $rows[0]['Producto'] ?? ('Producto ' . $idProducto);

        // 1) AGRUPAMOS POR ETAPA (tamaño)
        $porEtapa = [];
        foreach ($rows as $r) {
            $etapa = trim((string) ($r['Etapa'] ?? 'SIN ETAPA'));
            if (!isset($porEtapa[$etapa]))
                $porEtapa[$etapa] = [];
            $porEtapa[$etapa][] = [
                'Clave' => (string) ($r['Clave'] ?? ''),
                'Descripcion' => (string) ($r['Descripcion'] ?? ''),
                'Reales' => (float) ($r['Reales'] ?? 0),
                'USTD' => (float) ($r['USTD'] ?? 0),
                'PlanProduccion' => (float) ($r['PlanProduccion'] ?? 0),
                'DiferenciaUSTD' => (float) ($r['DiferenciaUSTD'] ?? 0),
            ];
        }

        // 2) DENTRO DE CADA ETAPA, TOTALIZAMOS POR CLAVE
        $totalesProducto = ['Reales' => 0, 'USTD' => 0, 'PlanProduccion' => 0, 'DiferenciaUSTD' => 0];
        $etapasList = [];
        foreach ($porEtapa as $etapa => $listRaw) {

            // ← totalizador por clave
            $rowsAgg = agregarFilasPorClave($listRaw);

            // Totales por etapa (de las filas ya agregadas)
            $t = ['Reales' => 0, 'USTD' => 0, 'PlanProduccion' => 0, 'DiferenciaUSTD' => 0];
            foreach ($rowsAgg as $r) {
                $t['Reales'] += $r['Reales'];
                $t['USTD'] += $r['USTD'];
                $t['PlanProduccion'] += $r['PlanProduccion'];
                $t['DiferenciaUSTD'] += $r['DiferenciaUSTD'];
            }

            // Acumular al total de producto
            $totalesProducto['Reales'] += $t['Reales'];
            $totalesProducto['USTD'] += $t['USTD'];
            $totalesProducto['PlanProduccion'] += $t['PlanProduccion'];
            $totalesProducto['DiferenciaUSTD'] += $t['DiferenciaUSTD'];

            $etapasList[] = [
                'etapa' => $etapa,
                'rows' => $rowsAgg, // ← filas agregadas por clave
                'totales' => $t
            ];
        }

        // 3) ORDENAR ETAPAS SEGÚN TU JERARQUÍA
        usort($etapasList, function ($a, $b) {
            $ia = ordenEtapaIndex($a['etapa']);
            $ib = ordenEtapaIndex($b['etapa']);
            if ($ia === $ib)
                return strcasecmp($a['etapa'], $b['etapa']);
            return $ia <=> $ib;
        });

        $bloques[] = [
            'producto' => $productoNombre,
            'etapas' => $etapasList,
            'totales' => $totalesProducto
        ];
    }

    // (Opcional) ordenar productos por nombre
    usort($bloques, fn($a, $b) => strcasecmp($a['producto'], $b['producto']));

    return $bloques;
}

function agruparPorCategoria(array $dataPlanProduccion): array
{
    $categorias = [];

    foreach ($dataPlanProduccion as $idProducto => $rows) {
        foreach ($rows as $row) {

            $categoria = $row['Categoria'] ?? 'SIN CATEGORIA';

            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [];
            }

            // Mantener producto dentro de la categoría
            if (!isset($categorias[$categoria][$idProducto])) {
                $categorias[$categoria][$idProducto] = [];
            }

            // Aquí sí agregas la fila correcta
            $categorias[$categoria][$idProducto][] = $row;
        }
    }

    ksort($categorias, SORT_NATURAL);

    return $categorias;
}

function dibujarCategoria(FPDF $pdf, float $x, float $y, float $w, string $categoria, array $st): float
{
    $rowH = $st['rowH'];
    // Color diferente para categoría (más fuerte que producto)
    $pdf->SetFillColor(132, 165, 227);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont($st['font']['family'], 'B', 8);
    $pdf->SetXY($x, $y);
    $pdf->Cell($w, $rowH + 2, mb_convert_encoding($categoria, 'ISO-8859-1', 'UTF-8'), 1, 1, 'L', true);
    return $rowH + 2;
}

function calcularTotalCategoria(array $bloques): array
{
    $total = [
        'Reales' => 0,
        'USTD' => 0,
        'PlanProduccion' => 0,
        'DiferenciaUSTD' => 0
    ];
    foreach ($bloques as $bloque) {
        $total['Reales'] += $bloque['totales']['Reales'];
        $total['USTD'] += $bloque['totales']['USTD'];
        $total['PlanProduccion'] += $bloque['totales']['PlanProduccion'];
        $total['DiferenciaUSTD'] += $bloque['totales']['DiferenciaUSTD'];
    }
    return $total;
}

function calcularTotalGeneral(array $categorias): array
{
    $total = [
        'Reales' => 0,
        'USTD' => 0,
        'PlanProduccion' => 0,
        'DiferenciaUSTD' => 0
    ];
    foreach ($categorias as $productosData) {
        // reutilizamos tu lógica actual
        $bloques = agruparPlanPorProductoEtapa($productosData);
        foreach ($bloques as $bloque) {
            $total['Reales'] += $bloque['totales']['Reales'];
            $total['USTD'] += $bloque['totales']['USTD'];
            $total['PlanProduccion'] += $bloque['totales']['PlanProduccion'];
            $total['DiferenciaUSTD'] += $bloque['totales']['DiferenciaUSTD'];
        }
    }
    return $total;
}
/* ==============================================
 *   CÁLCULO DE ALTO APROXIMADO DE CADA BLOQUE
 * ============================================== */
function altoBloquePlan(array $bloque, float $rowH): float
{
    // Estructura: barra título producto (rowH), por cada etapa:
    //   barra etapa (rowH), encabezados (rowH), N filas * rowH, total tamaño (rowH)
    // Al final, total tipo (rowH)
    $h = $rowH - 3;
    $alto = 0;
    $alto += $h; // título producto
    foreach ($bloque['etapas'] as $etapa) {
        $alto += $h; // barra etapa
        $alto += $h; // encabezados
        $alto += count($etapa['rows']) * $h; // filas
        $alto += $h; // total tamaño
        // separador sutil entre etapas (opcional)
        $alto += 1.5;
    }
    $alto += $h; // total tipo
    // margen inferior del bloque
    $alto += 2;
    return $alto;
}

/* ==============================================
 *   DIBUJA UN BLOQUE (PRODUCTO y sus ETAPAS)
 *   Devuelve altura usada efectiva.
 * ============================================== */

function dibujarBloquePlan(FPDF $pdf, float $x, float $y, float $w, array $bloque, array $st): float
{
    $rowH = $st['rowH'] - 3;
    $cBlue = $st['colors']['headerBlue'];
    $cSub = $st['colors']['subHeader'];
    $cBand = $st['colors']['band'];
    $cWhite = $st['colors']['textLight'];
    $cBlack = $st['colors']['textDark'];
    $labels = $st['labels'];
    $colsPct = $st['colsPct'];

    // Cálculo de anchos reales por porcentajes
    $colsW = [];
    foreach ($colsPct as $pct)
        $colsW[] = round(($w * $pct / 100), 2);

    // --- Título de producto (barra azul) ---
    $pdf->SetFillColor(205, 221, 250);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont($st['font']['family'], 'B', 7);
    $pdf->SetXY($x, $y);
    $pdf->Cell($w, $rowH, mb_convert_encoding($bloque['producto'], 'ISO-8859-1', 'UTF-8'), 1, 1, '0', true);

    $yActual = $y + $rowH;

    // Por cada etapa (tamaño)
    foreach ($bloque['etapas'] as $etapa) {

        // Subtítulo de etapa (gris)
        $pdf->SetFillColor($cSub[0], $cSub[1], $cSub[2]);
        $pdf->SetTextColor($cBlack[0], $cBlack[1], $cBlack[2]);
        $pdf->SetFont($st['font']['family'], 'B', 6);
        $pdf->SetXY($x, $yActual);
        $pdf->Cell($w, $rowH, trim(mb_convert_encoding(mb_strtoupper($etapa['etapa'], 'UTF-8'), 'ISO-8859-1', 'UTF-8')), 1, 1, 'L', true);
        $yActual += $rowH;

        // Encabezados de columnas (azul)
        $pdf->SetFillColor($cBlue[0], $cBlue[1], $cBlue[2]);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont($st['font']['family'], 'B', 5);
        $pdf->SetXY($x, $yActual);
        foreach ($labels['colHeaders'] as $i => $txt) {
            $pdf->Cell($colsW[$i], $rowH, mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $yActual += $rowH;

        // Filas

        $pdf->SetTextColor($cBlack[0], $cBlack[1], $cBlack[2]);
        foreach ($etapa['rows'] as $r) {
            $pdf->SetXY($x, $yActual);
            $pdf->SetFont($st['font']['family'], 'B', 4);
            $pdf->Cell($colsW[0], $rowH, preg_replace('/\s+/', '', (string) $r['Clave']), 1, 0, 'C');
            // Descripción (alineación izquierda, trunc simple si excede)
            $pdf->SetFont($st['font']['family'], '', 4);
            $desc = mb_convert_encoding(mb_strtoupper($r['Descripcion'], 'UTF-8'), 'ISO-8859-1', 'UTF-8');
            $pdf->Cell($colsW[1], $rowH, $desc, 1, 0, 'L');
            $pdf->Cell($colsW[2], $rowH, number_format((float) $r['Reales']), 1, 0, 'C');
            $pdf->Cell($colsW[3], $rowH, number_format((float) $r['USTD']), 1, 0, 'C');
            $pdf->Cell($colsW[4], $rowH, number_format((float) $r['PlanProduccion']), 1, 0, 'C');
            $pdf->Cell($colsW[5], $rowH, number_format((float) $r['DiferenciaUSTD']), 1, 0, 'C');
            $pdf->Ln();
            $yActual += $rowH;
        }

        // Total tamaño (banda gris clara)
        $t = $etapa['totales'];
        $pdf->SetFillColor(132, 165, 227);
        $pdf->SetFont($st['font']['family'], 'B', 4);
        $pdf->SetXY($x, $yActual);
        // Colspan primeras dos columnas
        $pdf->Cell($colsW[0] + $colsW[1], $rowH, mb_convert_encoding($labels['totalTamano'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'R', true);
        $pdf->SetFont($st['font']['family'], 'B', 4);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell($colsW[2], $rowH, number_format($t['Reales']), 1, 0, 'C', true);
        $pdf->Cell($colsW[3], $rowH, number_format($t['USTD']), 1, 0, 'C', true);
        $pdf->Cell($colsW[4], $rowH, number_format($t['PlanProduccion']), 1, 0, 'C', true);
        $pdf->Cell($colsW[5], $rowH, number_format($t['DiferenciaUSTD']), 1, 0, 'C', true);
        $pdf->Ln();
        $yActual += $rowH;

        // separador entre etapas
        $yActual += 1.5;
    }

    // Total tipo (producto) - barra azul
    $T = $bloque['totales'];
    $pdf->SetFillColor(132, 165, 227);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont($st['font']['family'], 'B', 4);
    $pdf->SetXY($x, $yActual);
    $pdf->Cell($colsW[0] + $colsW[1], $rowH, $labels['totalTipo'], 1, 0, 'R', true);
    $pdf->SetFillColor($cBand[0], $cBand[1], $cBand[2]);
    $pdf->SetTextColor($cBlack[0], $cBlack[1], $cBlack[2]);
    $pdf->SetFont($st['font']['family'], 'B', 4);
    $pdf->Cell($colsW[2], $rowH, number_format($T['Reales']), 1, 0, 'C', true);
    $pdf->Cell($colsW[3], $rowH, number_format($T['USTD']), 1, 0, 'C', true);
    $pdf->Cell($colsW[4], $rowH, number_format($T['PlanProduccion']), 1, 0, 'C', true);
    $pdf->Cell($colsW[5], $rowH, number_format($T['DiferenciaUSTD']), 1, 0, 'C', true);
    $pdf->Ln();
    $yActual += $rowH;

    // margen inferior del bloque
    $yActual += 2;

    return ($yActual - $y);
}

/* =======================================================
 *   LAYOUT EN 2 COLUMNAS (arriba → abajo, luego a la otra)
 * ======================================================= */
function imprimirPlanProduccionTablas(FPDF $pdf, array $dataPlanProduccion): void
{
    $st = planStyles();
    $m = $st['margins'];
    $gutter = $st['gutter'];
    $rowH = $st['rowH'];

    $pageW = $pdf->GetPageWidth();
    $pageH = $pdf->GetPageHeight();
    $contentW = $pageW - $m['l'] - $m['r'];
    $colW = ($contentW - $gutter) / 2;

    $colX = [$m['l'], $m['l'] + $colW + $gutter];
    $topY = $m['t'];
    $bottomY = $pageH - $m['b'];

    if ($pdf->PageNo() === 0)
        $pdf->AddPage();

    $pdf->SetAutoPageBreak(false);

    // 🔥 NUEVO: agrupar por categoría
    $categorias = agruparPorCategoria($dataPlanProduccion);

    $col = 0;
    $y = $topY + 5;

    foreach ($categorias as $categoria => $productosData) {

        // 🔹 Dibujar categoría
        $hCat = $st['rowH'] + 2;
        $primerBloque = agruparPlanPorProductoEtapa($productosData)[0] ?? null;
        $hPrimerBloque = $primerBloque ? altoBloquePlan($primerBloque, $rowH) : 0;

        if ($y + $hCat + $hPrimerBloque > $bottomY) {
            if ($col === 0) {
                $col = 1;
                $y = $topY + 5;
            } else {
                $pdf->AddPage();
                $col = 0;
                $y = $topY + 5;
            }
        }

        $y += dibujarCategoria($pdf, $colX[$col], $y, $colW, $categoria, $st);

        // 🔥 REUTILIZAS TU LÓGICA ACTUAL
        $bloques = agruparPlanPorProductoEtapa($productosData);

        foreach ($bloques as $bloque) {
            $hEstimado = altoBloquePlan($bloque, $rowH);

            if ($y + $hEstimado > $bottomY) {
                if ($col === 0) {
                    $col = 1;
                    $y = $topY + 5;
                } else {
                    $pdf->AddPage();
                    $col = 0;
                    $y = $topY + 5;
                }
            }

            $altoUsado = dibujarBloquePlan($pdf, $colX[$col], $y, $colW, $bloque, $st);
            $y += $altoUsado;
        }

        // 🔥 calcular total categoría
        $totalCategoria = calcularTotalCategoria($bloques);
        // 🔥 validar espacio antes de dibujar
        $hTotalCat = $st['rowH'] + 2;
        if ($y + $hTotalCat > $bottomY) {
            if ($col === 0) {
                $col = 1;
                $y = $topY + 5;
            } else {
                $pdf->AddPage();
                $col = 0;
                $y = $topY + 5;
            }
        }
        // 🔥 dibujar
        $y += dibujarTotalCategoria($pdf, $colX[$col], $y, $colW, $totalCategoria, $st, $categoria);
        // espacio extra entre categorías
        $y += 3;
    }

    // 🔥 TOTAL GENERAL
    $totalGeneral = calcularTotalGeneral($categorias);
    // Validar espacio (como ocupa más ancho, usamos toda la página)
    $hTotalGeneral = $st['rowH'] + 3;
    if ($y + $hTotalGeneral > $bottomY) {
        if ($col === 0) {
            $col = 1;
            $y = $topY + 5;
        } else {
            $pdf->AddPage();
            $col = 0;
            $y = $topY + 5;
        }
    }
    // 🔥 IMPORTANTE: usar todo el ancho
    dibujarTotalGeneral($pdf, $colX[$col], $y, $colW, $totalGeneral, $st);

}

function dibujarTotalCategoria(FPDF $pdf, float $x, float $y, float $w, array $total, array $st, string $categoria): float
{
    $rowH = $st['rowH'];
    $colsPct = $st['colsPct'];
    // calcular anchos
    $colsW = [];
    foreach ($colsPct as $pct) {
        $colsW[] = round(($w * $pct / 100), 2);
    }
    // estilo
    $pdf->SetFillColor(125, 166, 245);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont($st['font']['family'], 'B', 4);
    $pdf->SetXY($x, $y);
    $pdf->Cell($colsW[0] + $colsW[1], $rowH - 2, "TOTAL " . mb_convert_encoding(mb_strtoupper($categoria, 'UTF-8'), 'ISO-8859-1', 'UTF-8'), 1, 0, 'R', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell($colsW[2], $rowH - 2, number_format($total['Reales']), 1, 0, 'C', true);
    $pdf->Cell($colsW[3], $rowH - 2, number_format($total['USTD']), 1, 0, 'C', true);
    $pdf->Cell($colsW[4], $rowH - 2, number_format($total['PlanProduccion']), 1, 0, 'C', true);
    $pdf->Cell($colsW[5], $rowH - 2, number_format($total['DiferenciaUSTD']), 1, 0, 'C', true);
    $pdf->Ln();
    return $rowH + 2;
}

function dibujarTotalGeneral(FPDF $pdf, float $x, float $y, float $w, array $total, array $st): float
{
    $rowH = $st['rowH'];
    $colsPct = $st['colsPct'];

    $colsW = [];
    foreach ($colsPct as $pct) {
        $colsW[] = round(($w * $pct / 100), 2);
    }

    // estilo más fuerte 🔥
    $pdf->SetFillColor(125, 166, 245);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont($st['font']['family'], 'B', 4);

    $pdf->SetXY($x + 39.8, $y);

    $pdf->Cell($colsW[2], $rowH - 2, 'REALES', 1, 0, 'C', true);
    $pdf->Cell($colsW[3], $rowH - 2, 'USTD', 1, 0, 'C', true);
    $pdf->Cell($colsW[4], $rowH - 2, 'PROGRAMA', 1, 0, 'C', true);
    $pdf->Cell($colsW[5], $rowH - 2, 'DIFERENCIA', 1, 0, 'C', true);
    $pdf->SetXY($x + 25, $y + 4);
    $pdf->Cell($colsW[0] + 7, $rowH - 2, 'TOTAL GENERAL', 1, 0, 'L', true);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($colsW[2], $rowH - 2, number_format($total['Reales']), 1, 0, 'C', true);
    $pdf->Cell($colsW[3], $rowH - 2, number_format($total['USTD']), 1, 0, 'C', true);
    $pdf->Cell($colsW[4], $rowH - 2, number_format($total['PlanProduccion']), 1, 0, 'C', true);
    $pdf->Cell($colsW[5], $rowH - 2, number_format($total['DiferenciaUSTD']), 1, 0, 'C', true);

    $pdf->Ln();

    return $rowH + 3;
}

imprimirPlanProduccionTablas($pdf, $dataPlanProduccion);

$pdf->Output();
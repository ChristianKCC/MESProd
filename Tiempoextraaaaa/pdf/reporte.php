<?php
//Creacion de PDF para Autorizaciones de tiempos extra
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX003MXDB");

// Toma de datos segun los pasados por el form
$folio = base64_decode($_GET["folio"]);

// QUERY para obtener:
// El supervisor /
// Nombre de empleado /
// Nombre de departamento /
// Fecha de solicitud de tiempo extra
// Fecha final se toma de la incial mas 6 dias /
// La semana como numsem
// Numero de empleado de quien autoriza
// Y el estado de autorizado de la tabla de TiempoExtraEnc en la 3

// Ligamientos con INNER JOIN:
// De empleados con la 32 se obtiene el numero de empleado con la tabla de TiempoextraEnc segun el numero de supervisor
// De departamentos se obtiene el numero de departamento en donde el departamento de la TiempoextraEnc sea igual al que se pase por POST segun el folio
$query = "SELECT
    TiempoextraEnc.supervisor,
    sup.Nombre AS NombreSupervisor,
    dep.NombreDepto,
    TiempoextraEnc.fecha,
    DATEADD(DAY,6,TiempoextraEnc.fecha) AS fechaf,
    DATEPART(wk, TiempoextraEnc.fecha) AS numsem,
    TiempoextraEnc.noempautoriza,
    aut.Nombre AS NombreAutoriza,
    TiempoextraEnc.autorizado            
FROM [TLX003MXDB].[dbo].TiempoextraEnc
INNER JOIN TLX032MXDB.dbo.tblEmpleados AS sup ON sup.NoEmp = TiempoextraEnc.supervisor
INNER JOIN TLX032MXDB.dbo.tblEmpleados AS aut ON aut.NoEmp = TiempoextraEnc.noempautoriza
INNER JOIN TLX009MXDB.dbo.tblDepartamentos AS dep ON dep.NoDepto = TiempoextraEnc.departamento
WHERE TiempoextraEnc.id = $folio";

$result = sqlsrv_query($conn, $query);

// Clase para PDF
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $folio = $_GET["folio"];
        $this->Image('../../img/imglogoprosede.png', 10, 5, 50);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80, 10, "");
        $this->Cell(10, 10, utf8_decode('RELACION DE TIEMPO EXTRA Y ADICIONAL'));
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(160, 10, "");
        $this->Cell(40, 5, "Folio:" . base64_decode($folio));
        if (!isset($_GET["true"])) {
            $this->SetTextColor(255, 0, 0);
            $this->SetFont('Arial', 'B', 16);
            $this->MultiCell(160, 10, "");
            $this->Cell(40, 5, utf8_decode("Previsualización"));
        }
        $this->Ln(10);
    }
}

// Glosario de conceptos a usar en los Tiempos Extra
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(255, 45);
$pdf->MultiCell(50, 5, "CONCEPTOS:");
$pdf->SetXY(255, 50);
$pdf->MultiCell(50, 5, "TIEMPO EXTRA");
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(255, 60);
$pdf->MultiCell(50, 5, "1. VACACIONES");
$pdf->SetXY(255, 65);
$pdf->MultiCell(50, 5, "2. INCAPACIDADES");
$pdf->SetXY(255, 70);
$pdf->MultiCell(50, 5, "3. ARRANQUE DE MAQUINA");
$pdf->SetXY(255, 75);
$pdf->MultiCell(50, 5, utf8_decode("4 .CAPACITACIÓN"));
$pdf->SetXY(255, 80);
$pdf->MultiCell(50, 5, "5. PERMISO");
$pdf->SetXY(255, 85);
$pdf->MultiCell(50, 5, "6. JUNTAS");
$pdf->SetXY(255, 90);
$pdf->MultiCell(50, 5, "7. OTRO");
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(255, 100);
$pdf->MultiCell(50, 5, "CONCEPTOS:");
$pdf->SetXY(255, 105);
$pdf->MultiCell(50, 5, "TIEMPO ADICIONAL");
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(255, 115);
$pdf->MultiCell(50, 5, utf8_decode("8. REDUCCIÓN DE HORARIO"));
$pdf->SetXY(255, 120);
$pdf->MultiCell(50, 5, "9. TIEMPO DE COMIDA");
$pdf->SetXY(255, 125);
$pdf->MultiCell(50, 5, "10. DESCANSO TRABAJADO");
$pdf->SetXY(255, 130);
$pdf->MultiCell(50, 5, "11. EXTEMPORANEO");
$pdf->SetXY(255, 135);
$pdf->MultiCell(50, 5, "12. DIA FESTIVO");
$pdf->SetXY(5, 20);

$autorizo = 0;
$autorizoemp = '';
$elaboronoemp = '';
$nombreSup = '';
$nombreAut = '';

// Recorrido de datos segun si estan autorizados
while ($row = sqlsrv_fetch_array($result)) {
    $autorizo = $row['autorizado'];
    $autorizoemp = $row['noempautoriza'];
    $elaboronoemp = $row['supervisor'];
    $nombreSup = $row['NombreSupervisor'];
    $nombreAut = $row['NombreAutoriza'];
    $pdf->Cell(60, 5, "");
    $pdf->Cell(15, 5, "DEPTO:");
    $pdf->Cell(40, 5, utf8_decode($row[2]));
    $pdf->Cell(15, 5, "SEMANA:");
    $pdf->Cell(20, 5, $row[5]);
    $pdf->Cell(10, 5, "DEL:");
    $pdf->Cell(30, 5, $row[3]->format("Y-m-d"));
    $pdf->Cell(10, 5, "AL:");
    $pdf->Cell(30, 5, $row[4]->format("Y-m-d"));
}
$pdf->Ln(10);
$pdf->Cell(10, 10, utf8_decode("NoEmp"), 1, 0, 'L', 0);
$pdf->Cell(40, 10, utf8_decode("Nombre"), 1, 0, 'L', 0);
$pdf->Cell(40, 10, utf8_decode("Puesto"), 1, 0, 'L', 0);
$pdf->Cell(20, 10, utf8_decode("Maquina"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("LUN"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("MAR"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("MIE"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("JUE"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("VIE"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("SÁB"), 1, 0, 'L', 0);
$pdf->Cell(15, 5, utf8_decode("DOM"), 1, 0, 'L', 0);
$pdf->Cell(20, 5, utf8_decode("TOTAL"), 1, 0, 'L', 0);
$pdf->SetXY(225, 35);
$pdf->Cell(20, 5, utf8_decode("HORAS"), 1, 0, 'L', 0);

$total2 = 0;
$total2Horas = 0;
$total2Minutos = 0;

$pdf->SetXY(120, 35);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->Cell(5, 5, utf8_decode("C"), 1, 0, 'L', 0);
$pdf->Cell(10, 5, utf8_decode("HRS"), 1, 0, 'L', 0);
$pdf->MultiCell(5, 5, utf8_decode("C"), 1);

// Llenado de datos generales sin datos de horas
// Segunda query para obtener valores unicos en cuanto a datos en el PDF como
// noemp, nombre de empleado, nombre de departamento como depto, nombre del puesto como puesto, nombre de maquina y su id
$queryal = "SELECT
    TiempoextraSubEnc.noemp,
    MAX(tblEmpleados.Nombre) as Nombre,
    MAX(tblDepartamentos.NombreDepto) as depto,
    MAX(tblPuestos.nombre) as puesto,
    MAX(tblMaquinas.NombreMaquina) as NombreMaquina,
    TiempoextraSubEnc.maquina,
    (SELECT TOP 1 t2.turnoAsignado
       FROM TiempoextraSubEnc t2
      WHERE t2.noemp   = TiempoextraSubEnc.noemp
        AND t2.folio   = $folio
        AND t2.maquina = TiempoextraSubEnc.maquina
      GROUP BY t2.turnoAsignado
      ORDER BY COUNT(*) DESC) as turnoAsignado
FROM TiempoextraSubEnc
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id = tblEmpleados.Puesto
INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = TiempoextraSubEnc.maquina
WHERE TiempoextraSubEnc.folio=$folio
GROUP BY TiempoextraSubEnc.noemp, TiempoextraSubEnc.maquina";
$resultal = sqlsrv_query($conn, $queryal);

$horasx = 120;
$empleadosProcessados = [];

while ($rowal = sqlsrv_fetch_array($resultal)) {
    $pdf->SetFont('Arial', '', 6);

    $noemp = $rowal['noemp'];
    $maquina = $rowal['maquina'];
    $key = $noemp . '_' . $maquina;

    // Fila principal con datos del empleado
    $pdf->Cell(10, 5, utf8_decode($rowal['noemp']), 1, 0, 'L', 0);
    $pdf->Cell(40, 5, ucwords(strtolower(utf8_decode($rowal['Nombre']))), 1, 0, 'L', 0);
    $pdf->Cell(40, 5, utf8_decode($rowal['puesto']), 1, 0, 'L', 0);
    $pdf->Cell(20, 5, utf8_decode($rowal['NombreMaquina']), 1, 0, 'L', 0);

    // Celdas vacías para los días
    for ($i = 0; $i < 7; $i++) {
        $pdf->Cell(10, 5, " ", 1, 0, 'L', 0);
        $pdf->Cell(5, 5, " ", 1, 0, 'L', 0);
    }

    $horasy = $pdf->GetY();

    // Tercera query para obtener los datos de horas, motivo y dia de la semana
    // segun el numero de empleado, maquina y folio para cada empleado
    $query = "SELECT DATEPART(WEEKDAY,TiempoextraSubEnc.fecha) as dia,
                     DATEDIFF(MINUTE, TiempoextraSubEnc.horai,TiempoextraSubEnc.horaf) as dif,
                     TiempoextraSubEnc.motivo as motivo
              FROM TiempoextraSubEnc
              WHERE TiempoextraSubEnc.noemp={$rowal['noemp']}
                AND TiempoextraSubEnc.folio=$folio
                AND TiempoextraSubEnc.maquina={$rowal['maquina']}
              ORDER BY TiempoextraSubEnc.fecha";
    $result = sqlsrv_query($conn, $query);

    // Horas reglamentarias según turno
    switch ($rowal['turnoAsignado']) {
        case 'turno1':
            $horasReglamentarias = 48;
            $NombreTurno = "Turno 1";
            break;
        case 'turno2':
            $horasReglamentarias = 45;
            $NombreTurno = "Turno 2";
            break;
        case 'turno3':
            $horasReglamentarias = 51;
            $NombreTurno = "Turno 3";
            break;
        case 'mixto1':
            $horasReglamentarias = 48;
            $NombreTurno = "Mixto 1";
            break;
        case 'mixto2':
            $horasReglamentarias = 48;
            $NombreTurno = "Mixto 2";
            break;
        case 'mixto3':
            $horasReglamentarias = 48;
            $NombreTurno = "Mixto 3";
            break;
        case 'mixto4':
            $horasReglamentarias = 48;
            $NombreTurno = "Mixto 4";
            break;
        case 'turno3_12hrs':
            $horasReglamentarias = 51;
            $NombreTurno = "Turno 3 (12hrs)";
            break;
        case 'turno2_12hrs':
            $horasReglamentarias = 45;
            $NombreTurno = "Turno 2 (12hrs)";
            break;
        default:
            $horasReglamentarias = 0;
            $NombreTurno = "No hay";
    }

    // ===== Procesamiento de registros de detalle =====
    // Recolectar registros agrupados por día
    $detalle = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];
    $totalHorasEmpleado = 0;
    $totalMinutosEmpleado = 0;
    $registroActual = 0;

    while ($row = sqlsrv_fetch_array($result)) {
        $minutos = $row['dif'];
        if ($minutos < 0) {
            $minutos += 24 * 60;
        }

        $horasInt = floor($minutos / 60);
        $minsInt = $minutos % 60;

        // Reglas de redondeo individuales
        if ($minsInt >= 50) {
            $horasInt += 1;
            $minsInt = 0;
        } elseif ($minsInt >= 20) {
            $minsInt = 30;
        } else {
            $minsInt = 0;
        }

        // Acumular horas y minutos ya redondeados
        $totalHorasEmpleado += $horasInt;
        $totalMinutosEmpleado += $minsInt;

        $detalle[$row['dia']][] = [
            'horas' => sprintf("%02d:%02d", $horasInt, $minsInt),
            'motivo' => $row['motivo']
        ];
        $registroActual++;
    }

    // Número de filas = máximo de registros en un solo día
    $maxFilas = 1;
    foreach ($detalle as $regs) {
        if (count($regs) > $maxFilas) {
            $maxFilas = count($regs);
        }
    }

    // Columna X de cada día (DATEPART WEEKDAY: Dom=1, Lun=2, ... Sab=7)
    $colX = [
        2 => $horasx,
        3 => $horasx + 15,
        4 => $horasx + 30,
        5 => $horasx + 45,
        6 => $horasx + 60,
        7 => $horasx + 75,
        1 => $horasx + 90
    ];

    // Filas de continuación 
    $filasY = [0 => $horasy];
    for ($f = 1; $f < $maxFilas; $f++) {
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 6);
        $pdf->Cell(10, 5, utf8_decode($rowal['noemp']), 1, 0, 'L', 0);                        // NoEmp
        $pdf->Cell(40, 5, ucwords(strtolower(utf8_decode($rowal['Nombre']))), 1, 0, 'L', 0);  // Nombre
        $pdf->Cell(40, 5, "", 1, 0, 'L', 0);  // Puesto
        $pdf->Cell(20, 5, "", 1, 0, 'L', 0);  // Maquina
        for ($i = 0; $i < 7; $i++) {
            $pdf->Cell(10, 5, "", 1, 0, 'L', 0);
            $pdf->Cell(5, 5, "", 1, 0, 'L', 0);
        }
        $filasY[$f] = $pdf->GetY();
    }
    $ultimaFila = $filasY[$maxFilas - 1];

    // Colocar cada registro en su columna (día) y fila (orden de aparición)
    $pdf->SetFont('Arial', '', 7);
    foreach ($detalle as $dia => $regs) {
        foreach ($regs as $idx => $reg) {
            $pdf->SetXY($colX[$dia], $filasY[$idx]);
            $pdf->Cell(10, 5, $reg['horas'], 0, 0, 'C', 0);
            $pdf->Cell(5, 5, $reg['motivo'], 0, 0, 'C', 0);
        }
    }

    // Normalizar minutos acumulados
    $totalHorasEmpleado += intdiv($totalMinutosEmpleado, 60);
    $totalMinutosEmpleado = $totalMinutosEmpleado % 60;

    // Reglas de redondeo al total del empleado
    if ($totalMinutosEmpleado >= 50) {
        $totalHorasEmpleado += 1;
        $totalMinutosEmpleado = 0;
    } elseif ($totalMinutosEmpleado >= 26) {
        $totalMinutosEmpleado = 30;
    } else {
        $totalMinutosEmpleado = 0;
    }

    $totalFormateado = sprintf("%02d:%02d", $totalHorasEmpleado, $totalMinutosEmpleado);

    // Columna TOTAL: celdas vacías arriba y el total en la última fila (TODAS ancho 20)
    for ($f = 0; $f < $maxFilas; $f++) {
        $pdf->SetXY(225, $filasY[$f]);
        if ($f === $maxFilas - 1) {
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(20, 5, $totalFormateado, 1, 0, 'C', 0);
        } else {
            $pdf->Cell(20, 5, "", 1, 0, 'C', 0);
        }
    }

    // Total final del empleado en horas decimales (validación 60.5)
    $totalFinal = $totalHorasEmpleado + ($totalMinutosEmpleado / 60);
    $horast = $horasReglamentarias + $totalFinal;
    $tipo60 = ($horast >= 60.5);

    // Acumular total general en horas y minutos
    $total2Horas += $totalHorasEmpleado;
    $total2Minutos += $totalMinutosEmpleado;

    // Guardar historial para mostrarlo en el resumen
    $empleadosProcessados[$key] = [
        'noemp' => $noemp,
        'nombre' => ucwords(strtolower(utf8_decode($rowal['Nombre']))),
        'maquina' => $rowal['NombreMaquina'],
        'total_horas' => $totalFormateado,
        'registros' => $registroActual,
        'turno' => $rowal["turnoAsignado"],
        'nombreTurno' => $NombreTurno,
        'hrsReglamentarias' => $horasReglamentarias,
        'HorasPara60' => $horast
    ];

    // Cursor debajo de la última fila para el siguiente empleado
    $pdf->Ln();
}

// Normalizar minutos acumulados del total general
$total2Horas += intdiv($total2Minutos, 60);
$total2Minutos = $total2Minutos % 60;

// Reglas de redondeo al total general
if ($total2Minutos >= 50) {
    $total2Horas += 1;
    $total2Minutos = 0;
} elseif ($total2Minutos >= 26) {
    $total2Minutos = 30;
} else {
    $total2Minutos = 0;
}

$totalGeneralFormateado = sprintf("%02d:%02d", $total2Horas, $total2Minutos);

// Posicion para total de horas acumuladas en el reporte
$oby = $pdf->GetY();
$pdf->SetXY(225, $oby);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, $totalGeneralFormateado, 1, 0, 'C', 0);
$pdf->Ln(20);

// Formato para firmas
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(50, 5, "");
$pdf->Cell(60, 5, "_____________________________________");
$pdf->Cell(60, 5, "");
$x = $pdf->GetX();
$y = $pdf->GetY();

// Se toma el valor de la persona que autoriza y se agrega su firma
if ($autorizo == 1) {
    $extensiones = ['png', 'jpg', 'jpeg'];
    $ruta_firma = null;

    // Buscar primero en ../firmas/
    foreach ($extensiones as $ext) {
        $ruta = "../firmas/" . $autorizoemp . "." . $ext;
        if (file_exists($ruta)) {
            $ruta_firma = $ruta;
            $size = 13;
            break;
        }
    }

    // Si no se encontró, buscar en ../../FirmaDigital/firmas/
    if (!$ruta_firma) {
        foreach ($extensiones as $ext) {
            $ruta = "../../FirmaDigital/firmas/" . $autorizoemp . "." . $ext;
            if (file_exists($ruta)) {
                $ruta_firma = $ruta;
                $size = 40;
                break;
            }
        }
    }

    // Si se encontró alguna ruta válida, insertar la imagen en el PDF
    if ($ruta_firma) {
        $pdf->Image($ruta_firma, $x + 12, $y - 10, $size);
    }
}

if ($elaboronoemp) {
    error_log("Supervisor que elaboro el reporte: " . $elaboronoemp);
    $extensiones = ['png', 'jpg', 'jpeg'];
    $ruta_firma_sup = null;

    // Buscar primero en ../firmas/
    foreach ($extensiones as $ext) {
        $ruta = "../firmas/" . $elaboronoemp . "." . $ext;
        if (file_exists($ruta)) {
            $ruta_firma_sup = $ruta;
            $size = 13;
            break;
        }
    }

    // Si no se encontró, buscar en ../../FirmaDigital/firmas/
    if (!$ruta_firma_sup) {
        foreach ($extensiones as $ext) {
            $ruta = "../../FirmaDigital/firmas/" . $elaboronoemp . "." . $ext;
            if (file_exists($ruta)) {
                $ruta_firma_sup = $ruta;
                $size = 40;
                break;
            }
        }
    }

    // Si se encontró alguna ruta válida, insertar la imagen en el PDF
    if ($ruta_firma_sup) {
        // Firma
        $pdf->Image($ruta_firma_sup, $x - 112, $y - 12, $size);

        // Nombre flotando debajo de la firma
        $pdf->SetFont('Arial', '', 8);
        $pdf->Text($x - 112, $y + 3, ucwords(strtolower(utf8_decode($nombreSup))));
        $pdf->Text($x + 7, $y + 3, ucwords(strtolower(utf8_decode($nombreAut))));
    }
}

$pdf->MultiCell(60, 5, "__________________________________");
$pdf->Cell(70, 5, "");
$pdf->Cell(40, 5, "Elaboro");
$pdf->Cell(82, 5, "");
$pdf->Cell(40, 5, "Autorizo");
$pdf->SetFont('Arial', 'B', 5);
$pdf->Cell(20, 5, "");

// Resumen de procesamiento segun los empleados
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 7);
$pdf->Cell(280, 10, "Resumen final:", 0, 1);
$pdf->SetFont('Arial', '', 6);

// Resumen final de empleados procesados
foreach ($empleadosProcessados as $hist) {
    $pdf->Cell(
        240,
        4,
        "No. de empleado: {$hist['noemp']}   |  Nombre de empleado: {$hist['nombre']}    |    Maquina: {$hist['maquina']}    |    Cant. de T. extra: {$hist['registros']}   |   Total de horas extra: {$hist['total_horas']}   |   Turno Identificado: {$hist['nombreTurno']}   |   Horas Regl. x turno: {$hist['hrsReglamentarias']}",
        1,
        1
    );
}

$pdf->Output();
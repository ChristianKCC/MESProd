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
// De departamentos se obtiene el numero de departamento en donde el departam,ento de la TiempoextraEnc sea igual al que se pase por POST segun el folio
$query = "SELECT TiempoextraEnc.supervisor,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,TiempoextraEnc.fecha,
DATEADD(DAY,6,TiempoextraEnc.fecha) as fechaf,DATEPART(wk, TiempoextraEnc.fecha) as numsem, noempautoriza, autorizado
FROM TiempoextraEnc 
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=TiempoextraEnc.supervisor
INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=TiempoextraEnc.departamento WHERE TiempoextraEnc.id=$folio";
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
        $this->Cell(40, 5, "Folio:" .base64_decode($folio));
        if(!isset($_GET["true"])){
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
$pdf->SetXY(255,45);
$pdf->MultiCell(50, 5, "CONCEPTOS:");
$pdf->SetXY(255,50);
$pdf->MultiCell(50, 5, "TIEMPO EXTRA");
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(255,60);
$pdf->MultiCell(50, 5, "1. VACACIONES");
$pdf->SetXY(255,65);
$pdf->MultiCell(50, 5, "2. INCAPACIDADES");
$pdf->SetXY(255,70);
$pdf->MultiCell(50, 5, "3. ARRANQUE DE MAQUINA");
$pdf->SetXY(255,75);
$pdf->MultiCell(50, 5,  utf8_decode("4 .CAPACITACIÓN"));
$pdf->SetXY(255,80);
$pdf->MultiCell(50, 5, "5. PERMISO");
$pdf->SetXY(255,85);
$pdf->MultiCell(50, 5,"6. JUNTAS");
$pdf->SetXY(255,90);
$pdf->MultiCell(50, 5, "7. OTRO");
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(255,100);
$pdf->MultiCell(50, 5, "CONCEPTOS:");
$pdf->SetXY(255,105);
$pdf->MultiCell(50, 5, "TIEMPO ADICIONAL");
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY(255,115);
$pdf->MultiCell(50, 5, "8. T. CAMBIO DE HORARIO");
$pdf->SetXY(255,120);
$pdf->MultiCell(50, 5, "9. TIEMPO DE COMIDA");
$pdf->SetXY(255,125);
$pdf->MultiCell(50, 5, "10. DESCANSO / DIA FESTIVO");
$pdf->SetXY(255,130);
$pdf->MultiCell(50, 5, "11. EXTEMPORANEO");
$pdf->SetXY(5,20);
$autorizo = 0;
$autorizoemp = '';

// Recorrido de datos segun si estan autorizados
while ($row = sqlsrv_fetch_array($result)) {
    $autorizo = $row['autorizado'];
    $autorizoemp = $row['noempautoriza'];
    $pdf->Cell(60, 5, "");
    $pdf->Cell(15, 5, "DEPTO:");
    $pdf->Cell(40, 5, utf8_decode($row[2]));
    $pdf->Cell(15, 5, "SEMANA:");
    $pdf->Cell(20, 5, $row[5]+1);
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
$pdf->Cell(20, 5, utf8_decode("TOTAL"), 1,0,'L',0);
// Datos para 60.5 hrs
// $pdf->Cell(10, 5, utf8_decode("+60.5"), 1, 0, 'L', 0);
$pdf->SetXY(225,35);
$pdf->Cell(20, 5, utf8_decode("HORAS"), 1,0,'L',0);
// Datos para 60.5 hrs
// $pdf->Cell(10, 5, utf8_decode("¿ES?"), 1,0,'L',0);
$total2 = 0;
$total2Horas = 0;
$total2Minutos = 0;
$pdf->SetXY(120,35);
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
// noemp, nombre de empleado, nombre de departamento como depto, nombre del puesto como pusto nombre de maquina y su id
// TiempoextraSubEnc.noemp -> Valores totales
// DISTINCT(TiempoextraSubEnc.noemp) -> Valor unico
// Obtencion de valor para 6.5 hrs
$queryal = "SELECT 
    TiempoextraSubEnc.noemp,
    MAX(tblEmpleados.Nombre) as Nombre,
    MAX(tblDepartamentos.NombreDepto) as depto,
    MAX(tblPuestos.nombre) as puesto,
    MAX(tblMaquinas.NombreMaquina) as NombreMaquina,
    TiempoextraSubEnc.maquina,
    TiempoextraSubEnc.turnoAsignado
FROM TiempoextraSubEnc
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id= tblEmpleados.Puesto
INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= TiempoextraSubEnc.maquina
WHERE TiempoextraSubEnc.folio=$folio
GROUP BY TiempoextraSubEnc.noemp, TiempoextraSubEnc.maquina, TiempoextraSubEnc.turnoAsignado";
$resultal = sqlsrv_query($conn, $queryal);

$horasx=120;
$empleadosProcessados = [];

while ($rowal = sqlsrv_fetch_array($resultal)) {
    $pdf->SetFont('Arial', '', 6);
    
    $noemp = $rowal['noemp'];
    $maquina = $rowal['maquina'];
    $key = $noemp . '_' . $maquina;
    
    // fila principal con datos del empleado
    $pdf->Cell(10, 5, utf8_decode($rowal['noemp']), 1, 0, 'L', 0);
    $pdf->Cell(40, 5, ucwords(strtolower(utf8_decode($rowal['Nombre']))), 1, 0, 'L', 0);
    $pdf->Cell(40, 5, utf8_decode($rowal['puesto']), 1, 0, 'L', 0);
    $pdf->Cell(20, 5, utf8_decode($rowal['NombreMaquina']), 1, 0, 'L', 0);

    // celdas vacías para los días
    for ($i=0; $i<7; $i++) {
        $pdf->Cell(10, 5, " ", 1, 0, 'L', 0);
        $pdf->Cell(5, 5, " ", 1, 0, 'L', 0);
    }

    $horasy=$pdf->GetY();

    // Tercera query para obtener los datos de horas, motivo y dia de la semana segun el numero de empleado, maquina y folio para cada empleado
    $query = "SELECT DATEPART(WEEKDAY,TiempoextraSubEnc.fecha) as dia,
                     DATEDIFF(MINUTE, TiempoextraSubEnc.horai,TiempoextraSubEnc.horaf) as dif,
                     TiempoextraSubEnc.motivo as motivo
              FROM TiempoextraSubEnc
              WHERE TiempoextraSubEnc.noemp={$rowal['noemp']}
                AND TiempoextraSubEnc.folio=$folio
                AND TiempoextraSubEnc.maquina={$rowal['maquina']}
              ORDER BY TiempoextraSubEnc.fecha";
    $result = sqlsrv_query($conn, $query);

    $pdf->SetFont('Arial', '', 7);
    // Para cada registro de detalle, se calcula la posición en el PDF según el día de la semana 
    // y se dibujan las horas y el motivo. 
    // Si hay más de un registro para el mismo día, se van acumulando offsets para dibujar en filas diferentes pero en la misma columna del día.
    $offsets = [1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0];
    $totalEmpleado = 0;
    $registroActual = 0;
    $ultimaFila = $horasy;

    // Variables acumuladoras por empleado
    $totalHorasEmpleado = 0;
    $totalMinutosEmpleado = 0;
    

    // Horas reglamentarias según turno
    switch($rowal['turnoAsignado']) {
        case 'turno1': $horasReglamentarias = 48; $NombreTurno = "Turno 1"; break;
        case 'turno2': $horasReglamentarias = 45; $NombreTurno = "Turno 2"; break;
        case 'turno3': $horasReglamentarias = 51; $NombreTurno = "Turno 3"; break;
        case 'mixto1': $horasReglamentarias = 48; $NombreTurno = "Mixto 1"; break;
        case 'mixto2': $horasReglamentarias = 48; $NombreTurno = "Mixto 2"; break;
        case 'mixto3': $horasReglamentarias = 48; $NombreTurno = "Mixto 3"; break;
        case 'mixto4': $horasReglamentarias = 48; $NombreTurno = "Mixto 4"; break;
        case 'turno3_12hrs': $horasReglamentarias = 51; $NombreTurno = "Turno 3 (12hrs)"; break;
        case 'turno2_12hrs': $horasReglamentarias = 45; $NombreTurno = "Turno 2 (12hrs)"; break;
        default: $horasReglamentarias = 0; $NombreTurno = "No hay";
    }      

    while ($row = sqlsrv_fetch_array($result)) {
        $horas = $row['dif']/60;
        $horas = number_format($horas,1);
        if($horas < 0){ $horas = ($horas+24); }
        
        $minutos = $row['dif'];
        if ($minutos < 0) { $minutos += 24*60; }

        // Calcular horas y minutos
        $horasInt = floor($minutos/60);
        $minsInt = $minutos % 60;

        // Aplicar reglas de redondeo individuales
        // + hora:55 -> Pasa a la siguiente hora
        // - hora:55 -> Pasa a media hora
        // - hora:26 -> minutos baja a hora exacta
        if ($minsInt >= 55) {
            $horasInt += 1;
            $minsInt = 0;
        } elseif ($minsInt >= 26) {
            $minsInt = 30;
        } else {
            $minsInt = 0;
        }

        // Formatear resultado redondeado
        $horasFormateadas = sprintf("%02d:%02d", $horasInt, $minsInt);

        // Acumular horas y minutos reales
        $horasInt = floor($minutos/60);
        $minsInt = $minutos % 60;
        $totalHorasEmpleado += $horasInt;
        $totalMinutosEmpleado += $minsInt;

        // Posicionamiento de dias
        $dia = $row['dia'];
        switch ($dia) {
            case 2: $x = $horasx;       break;
            case 3: $x = $horasx+15;    break;
            case 4: $x = $horasx+30;    break;
            case 5: $x = $horasx+45;    break;
            case 6: $x = $horasx+60;    break;
            case 7: $x = $horasx+75;    break;
            case 1: $x = $horasx+90;    break;
        }

        // Cabeceras originales
        if ($offsets[$dia] > 0) {
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 6);
            $pdf->Cell(10, 5, utf8_decode($rowal['noemp']), 1, 0, 'L', 0);
            $pdf->Cell(0, 5, utf8_decode($rowal['Nombre']), 1, 0, 'L', 0);
            $pdf->Cell(40, 5, utf8_decode($rowal['puesto']), 1, 0, 'L', 0);
            $pdf->Cell(20, 5, utf8_decode($rowal['NombreMaquina']), 1, 0, 'L', 0);
            for ($i=0; $i<7; $i++) {
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell(10, 5, " ", 1, 0, 'L', 0);
                $pdf->Cell(5, 5, " ", 1, 0, 'L', 0);
            }
            $ultimaFila = $pdf->GetY();
        }

        $y = $ultimaFila;
        $pdf->SetXY($x, $y);
        $pdf->Cell(10, 5, $horasFormateadas, 0, 0, 'C', 0);
        $pdf->Cell(5, 5, $row['motivo'], 0, 0, 'C', 0);

        $offsets[$dia] += 1;
        $registroActual++;
    }

    // Dibuja el total de horas para el empleado en la primera fila, y si hay más filas, deja celdas vacías con borde
    $yActual = $horasy;
    while ($yActual <= $ultimaFila + 0.1) {
        $pdf->SetXY(225, $yActual);
        // Si es la primera fila del empleado, dibuja el total definitivo
        if (abs($yActual - $horasy) < 0.1) {
            //$pdf->Cell(15, 5, number_format($totalEmpleado,1), 1, 0, 'C', 0);
            //$pdf->Cell(15, 5, number_format($totalEmpleado,2), 1, 0, 'C', 0);

            $totalMinutos = round($totalEmpleado * 60); // convertir horas decimales a minutos exactos
            $totalFormateado = sprintf("%02d:%02d", floor($totalMinutos/60), $totalMinutos%60);
            

        } else {
            // Si hay más filas, dibuja celda vacía con borde
            $pdf->Cell(15, 5, "", 1, 0, 'C', 0);
        }
        $yActual += 5;
    }
    
    // Normalizar minutos acumulados
    $totalHorasEmpleado += intdiv($totalMinutosEmpleado, 60);
    $totalMinutosEmpleado = $totalMinutosEmpleado % 60;

    // Aplicar reglas de redondeo
    if ($totalMinutosEmpleado >= 55) {
        $totalHorasEmpleado += 1;
        $totalMinutosEmpleado = 0;
    } elseif ($totalMinutosEmpleado >= 28) {
        $totalMinutosEmpleado = 30;
    } else {
        $totalMinutosEmpleado = 0;
    }

    // Formatear resultado final
    $totalFormateado = sprintf("%02d:%02d", $totalHorasEmpleado, $totalMinutosEmpleado);

    // Mostrar total en la celda
    $pdf->Cell(20, 5, $totalFormateado, 1, 0, 'C', 0);

    // Calcular total final en horas decimales
    $totalFinal = $totalHorasEmpleado + ($totalMinutosEmpleado/60);

    // Validación de tipo 60.5 hrs
    $horast = $horasReglamentarias + $totalFinal;
    $tipo60 = ($horast >= 60.5);
    // $tipo60 = ($horast >= 60) ? 'SI' : 'NO';    

    // Datos para 60.5 hrs
    // Mostrar columna 60.5 hrs (0 o 1)    
    // $pdf->Cell(10, 5, utf8_decode($tipo60 == 1 ? 'SI' : 'NO'), 1, 0, 'L', 0);

    // Acumular total general en horas y minutos
    $total2Horas += $totalHorasEmpleado;
    $total2Minutos += $totalMinutosEmpleado;

    // Guardar historial para mostrarlo en resumen
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
        
    $pdf->Ln();
}

// Normalizar minutos acumulados del total general
$total2Horas += intdiv($total2Minutos, 60);
$total2Minutos = $total2Minutos % 60;

// Aplicar reglas de redondeo al total general
if ($total2Minutos >= 55) {
    $total2Horas += 1;
    $total2Minutos = 0;
} elseif ($total2Minutos >= 28) {
    $total2Minutos = 30;
} else {
    $total2Minutos = 0;
}

// Formatear resultado final del total general
$totalGeneralFormateado = sprintf("%02d:%02d", $total2Horas, $total2Minutos);

// Posicion para total de horas acumuladas en el reporte
$oby=$pdf->GetY();
$pdf->SetXY(225, $oby );
$pdf->SetFont('Arial', 'B', 8);
//$pdf->Cell(15, 5, number_format($total2,2), 1, 0, 'C', 0);
//$pdf->Cell(15,5, $totalFormateado, 1, 0, 'C', 0);

// Mostrar en el PDF
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
$autorizo == 1 && $pdf->Image('../firmas/'.$autorizoemp.'.JPG', $x+15, $y-15, 20);
$pdf->MultiCell(60, 5, "_____________________________________");
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

// Resumen final de empleados procesados, con su numero de empleado, nombre, maquina, cantidad de registros y total de horas
foreach ($empleadosProcessados as $hist) {
    $pdf->Cell(240, 4, 
        "No. de empleado: {$hist['noemp']}   |  Nombre de empleado: {$hist['nombre']}    |    Maquina: {$hist['maquina']}    |    Cant. de T. extra: {$hist['registros']}   |   Total de horas extra: {$hist['total_horas']}   |   Turno Identificado: {$hist['nombreTurno']}   |   Horas Regl. x turno: {$hist['hrsReglamentarias']}", 1, 1);
}

$pdf->Output();
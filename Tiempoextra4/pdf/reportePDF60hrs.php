<?php
require('../../fpdf/fpdf.php');
require_once(__DIR__ . "/../../conexion.php");

function formatDate($value, $format="Y-m-d") {
    if ($value instanceof DateTime) return $value->format($format);
    return $value ?: "";
}
function formatTime($value, $format="H:i") {
    if ($value instanceof DateTime) return $value->format($format);
    return $value ?: "";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fechaI = $_POST["fechai"] ?? null;
    $fechaF = $_POST["fechaf"] ?? null;
    $turno = $_POST["turno"] ?? null;
    $departamento = $_POST["departamento"] ?? null;

    $ClassConexion = new ClassConexion;
    $conn = $ClassConexion->conexion("TLX003MXDB");

    function formatTurno($turno) {
        switch($turno) {
            case 'turno1': return 'Turno 1';
            case 'turno2': return 'Turno 2';
            case 'turno3': return 'Turno 3';
            case 'turno3_12hrs': return 'Turno 3 (12 hrs)';
            case 'turno2_12hrs': return 'Turno 2 (12 hrs)';
            case 'mixto1': return 'Mixto 1';
            case 'mixto2': return 'Mixto 2';
            case 'mixto3': return 'Mixto 3';
            case 'mixto4': return 'Mixto 4';
            default: return 'No hay turno registrado';
        }
    }        

    // Llamada a sp
    $sql = "EXEC dbo.pa_MXPRReporteHorasExtras ?, ?, ?, ?";
    $params = [$fechaI, $fechaF, $turno ?: null, $departamento ?: null];

    $result = sqlsrv_query($conn, $sql, $params);        

    $pdf = new FPDF('P','mm','Letter');
    $pdf->AddPage();
    $pdf->SetMargins(15,15,15);

    // Logo
    $logoWidth = 80;
    $x = ($pdf->GetPageWidth() - $logoWidth) / 2;
    $pdf->Image('../../img/logo.jpg', $x, 10, $logoWidth);
    $pdf->Ln(10);

    // Título
    $pdf->SetFont('Helvetica','B',12);
    $pdf->SetTextColor(0,51,102);
    $pdf->Cell(0,10,'Reporte de 60.5 hrs en Tiempos Extras y Dobletes',0,1,'C');
    $pdf->Ln(5);

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Helvetica','',9);

    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $pdf->SetFillColor(220,235,255);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(180,7,'Empleado: '.utf8_decode($row['NombreSolicitante']),1,1,'L',true);

        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(40,7,'Departamento:',1,0,'L',true);
        $pdf->Cell(60,7,utf8_decode($row['departamento']),1,0);
        $pdf->Cell(40,7,'Folio de Tiempo Extra:',1,0,'L',true);
        $pdf->Cell(40,7,$row['FolioTurnoExtra'],1,1);        

        $pdf->Cell(40,7,'Fecha:',1,0,'L',true);
        $pdf->Cell(60,7,formatDate($row['fecha']),1,0);
        $pdf->Cell(40,7,'Hrs. Ext. Semanal en folio:',1,0,'L',true);
        $pdf->Cell(40,7,number_format($row['totalHorasExtrasSolicitadas'],2) .' hrs',1,1);        

        $pdf->Cell(40,7,'Inicio Extra:',1,0,'L',true);
        $pdf->Cell(60,7,formatTime($row['horaInicioTurnoExtra']) .' hrs',1,0);
        $pdf->Cell(40,7,'Fin Extra:',1,0,'L',true);
        $pdf->Cell(40,7,formatTime($row['horaFinTurnoExtra']) .' hrs',1,1);

        $pdf->Cell(40,7,'Horas Reglamentarias:',1,0,'L',true);
        $pdf->Cell(60,7,$row['horasReglamentarias'] .' hrs',1,0);
        $pdf->Cell(40,7,'Turno:',1,0,'L',true);
        $pdf->Cell(40,7,formatTurno($row['turnoAsignado']),1,1);

        $pdf->Cell(40,7,'Horas Totales:',1,0,'L',true);
        $pdf->Cell(60,7,$row['horasReglamentarias']+($row['horasExtrasRegistro']??0) .' hrs',1,0);
        
        $pdf->Cell(40,7,'Hrs. Ext. Individuales:',1,0,'L',true);   
        $pdf->Cell(40,7,number_format($row['horasExtrasRegistro'],2) .' hrs',1,1);
        
        $TipoRegistro = $row["esDoblete"];
        $horast = $row['horasReglamentarias']+$row['horasExtrasRegistro']??0;

        // Validacion de horas segun registros
        if ($TipoRegistro === 1 && $horast >= 60.5) {
            $estadoTexto = 'Doblete y >= 60.5 hrs';
        } elseif ($TipoRegistro === 1) {
            $estadoTexto = 'Doblete de turno';
        } elseif ($horast >= 60.5) {
            $estadoTexto = '>= 60.5 hrs';
        } else {
            $estadoTexto = 'No corresponde';
        }

        $pdf->Cell(40,7,'Tipo de registro:',1,0,'L',true);
        $pdf->Cell(140,7,utf8_decode($estadoTexto),1,1);

        $pdf->Ln(8);
    }
    $pdf->Output('I','Reporte60hrs.pdf');
}
?>

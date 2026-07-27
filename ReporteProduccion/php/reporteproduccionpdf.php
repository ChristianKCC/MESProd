<?php
require('../../fpdf/fpdf.php');
require_once "../../conexion.php";
$conection = new ClassConexion();
$conn = $conection->conexion("TLX002MXDB");
$fechai = $_POST["fechai"];
$turno = $_POST["turno"];
$departamento = $_POST["departamento"];
$maquinas = $_POST["maquinas"];
empty($turno) ? $turno = "" : $turno = " AND turno=" . $turno;
empty($maquinas) ? $maquinas = "" : $maquinas = " AND NoMaquina=" . $_POST["maquinas"];
$query = "SELECT idProduccion,fecha,tblDepartamentos.NombreDepto as departamento,tblMaquinas.NombreMaquina as maquina,tblValeEClaves.Descripcion_Articulo as clave,
            tblEmpleados.Nombre as conductor, turno,hrs,golpestotales,cajasreales,tblValeEClaves.factor,tblValeEClaves.panalxcaja,tblValeEClaves.NoClave,
             tblProduccionesClaveTipo.DescripcionTipo,tblProduccionesClaveClase.DescripcionClase,std,merma,foliobitacora,
			 (SELECT  SUM(tblBitCtrltiempos.operacion + tblBitCtrltiempos.electrico + tblBitCtrltiempos.mecanico + tblBitCtrltiempos.materias + tblBitCtrltiempos.grado + tblBitCtrltiempos.prev + tblBitCtrltiempos.servicios) AS TotalSuma
            FROM  tblBitCtrltiempos
            INNER JOIN tblBitSecciones ON tblBitSecciones.NoSeccion = tblBitCtrltiempos.seccion
            INNER JOIN tblBitModulos ON tblBitModulos.NoModulo = tblBitCtrltiempos.modulo
            INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora as Ebit2 ON Ebit2.IdEncabezadoBItacora = tblBitCtrltiempos.folio
            WHERE Ebit2.IdEncabezadoBItacora = foliobitacora
            GROUP BY Ebit2.turno) as tpt FROM tblProduccionesEnc
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblProduccionesEnc.departamento
            INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionesEnc.maquina
            INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblProduccionesEnc.clave
            LEFT JOIN tblProduccionesClaveTipo ON tblProduccionesClaveTipo.idTipo = tblValeEClaves.tipo
            LEFT JOIN tblProduccionesClaveClase ON tblProduccionesClaveClase.idClase = tblValeEClaves.clase
            INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblProduccionesEnc.noemp WHERE fecha = '$fechai' $turno $maquinas ";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('../../img/imglogoprosede.png', 10, 5, 80);
$pdf->Ln(20);
$Y = $pdf->GetY();
$conteo = 0;
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(125, 5);
$pdf->Cell(10, 10, utf8_decode('Reporte de producciones'));
$pdf->Ln();
$Y = $pdf->GetY();
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(160, 10);
$pdf->MultiCell(50, 10, utf8_decode("Fecha: " . $fechai));
$result = sqlsrv_query($conn, $query);
$senddata = $query;
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 8, utf8_decode("FECHA"));
$pdf->Cell(17, 8, utf8_decode("TURNO "));
$pdf->Cell(28, 8, utf8_decode("MAQUINA "));
$pdf->Cell(20, 8, utf8_decode("CLAVE "));
$pdf->Cell(20, 8, utf8_decode("STD "));
$pdf->Cell(20, 8, utf8_decode("CjsReales "));
$pdf->Cell(20, 8, utf8_decode("GolpesT "));
$pdf->Cell(20, 8, utf8_decode("Merma"));
$pdf->MultiCell(120, 8, utf8_decode("TPT"));
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(18, 5, utf8_decode($fila['fecha']->format('Y-m-d')));
    $pdf->Cell(13, 5, utf8_decode($fila['turno']));
    $pdf->Cell(30, 5, utf8_decode($fila['maquina']));
    $pdf->Cell(20, 5, utf8_decode($fila['NoClave']));
    $pdf->Cell(20, 5, utf8_decode($fila['std']));
    $pdf->Cell(20, 5, utf8_decode($fila['cajasreales']));
    $pdf->Cell(20, 5, utf8_decode($fila['golpestotales']));
    $pdf->Cell(20, 5, utf8_decode(number_format($fila['merma'], 2)));
    $pdf->MultiCell(120, 5, utf8_decode($fila['tpt']));
}
$pdf->Ln(10);
$turno == "" ? $turno = '' : $turno = " AND tblEncabezadoBitacora.turno=" . $_POST["turno"];
$query = "WITH CTE AS ( SELECT tblBitPresentacionEnc.presentacion, tblEncabezadoBitacora.turno, tblBitTurnohoras.horastr, tblBitPresentacionSub.real, 
tblBitPresentacionGolpes.golpes, tblBitPresentacionSub.acumulado, tblBitPresentacionSub.std, tblBitPresentacionGolpes.merma, tblValeEClaves.panalxcaja,
    ROW_NUMBER() OVER (PARTITION BY tblBitPresentacionEnc.presentacion, tblEncabezadoBitacora.turno ORDER BY tblBitTurnohoras.id DESC) AS rn FROM tblBitPresentacionEnc 
    INNER JOIN tblBitPresentacionSub ON tblBitPresentacionSub.idpresentacionenc = tblBitPresentacionEnc.idpresentacionenc 
    INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitPresentacionEnc.folio 
    INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id = tblBitPresentacionSub.hora 
	INNER JOIN tblBitPresentacionGolpes ON tblBitPresentacionGolpes.idbitacora = tblBitPresentacionEnc.folio 
	INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblBitPresentacionEnc.presentacion
    WHERE tblBitPresentacionGolpes.hora = tblBitPresentacionSub.hora AND tblBitPresentacionSub.real <> 0 AND tblEncabezadoBitacora.fecha = '$fechai' $turno $maquinas)
    SELECT presentacion, turno, horastr, real, golpes, acumulado, std, merma,panalxcaja FROM CTE WHERE rn = 1 ORDER BY 
    turno, presentacion, std DESC;";
$result = sqlsrv_query($conn, $query);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(60, 0, utf8_decode('Producción Resumen'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 8, utf8_decode("Hora"));
$pdf->Cell(45, 8, utf8_decode("Clave "));
$pdf->Cell(20, 8, utf8_decode("Golpes"));
$pdf->Cell(20, 8, utf8_decode("Acum. Real"));
$pdf->Cell(22, 8, utf8_decode("STD"));
$pdf->Cell(18, 8, utf8_decode("Merma"));
$pdf->MultiCell(20, 8, utf8_decode("Turno"));
$GolpesA = 0;
$gps = 0;
$turnoAnterior = null;
$primerTurno = true;
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    if (!$primerTurno && $turnoAnterior !== $fila['turno']) {
        $GolpesA == 0 ? $merma = 0 : $merma = (($GolpesA - $gps) / $GolpesA) * 100;
        $pdf->Cell(136, 5, "");
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->MultiCell(20, 5, number_format($merma, 2));
        $gps = 0;
        $GolpesA = 0;
    }
    $pdf->SetFont('Arial', '', 7);
    $GolpesA = max($GolpesA, $fila['golpes']);
    $gp = $fila['acumulado'] * $fila['panalxcaja'];
    $pdf->Cell(30, 5, utf8_decode($fila['horastr']));
    $pdf->Cell(46, 5, utf8_decode($fila['presentacion']));
    $pdf->Cell(20, 5, utf8_decode($fila['golpes']));
    $pdf->Cell(20, 5, utf8_decode($fila['acumulado']));
    $pdf->Cell(20, 5, utf8_decode($fila['std']));
    $pdf->Cell(20, 5, utf8_decode($fila['merma']));
    $pdf->MultiCell(20, 5, utf8_decode($fila['turno']));
    $gps += $gp;
    $turnoAnterior = $fila['turno'];
    $primerTurno = false;
}
if (!$primerTurno) {
    $GolpesA == 0 ? $merma = 0 : $merma = (($GolpesA - $gps) / $GolpesA) * 100;
    $pdf->Cell(136, 5, "");
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->MultiCell(20, 5, number_format($merma, 2));
}

$pdf->Ln(10);
$query = "SELECT tblProduccionSecciones.Seccion, tblProduccionModulos.Modulos, 
    (tblBitCtrltiempos.operacion + tblBitCtrltiempos.electrico + tblBitCtrltiempos.mecanico + tblBitCtrltiempos.materias + tblBitCtrltiempos.grado + 
    tblBitCtrltiempos.prev + tblBitCtrltiempos.servicios) AS TotalSuma
    FROM tblBitCtrltiempos INNER JOIN tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblBitCtrltiempos.seccion
    INNER JOIN tblProduccionModulos ON tblProduccionModulos.idModulos = tblBitCtrltiempos.modulo
    INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCtrltiempos.folio
    WHERE tblEncabezadoBitacora.fecha = '$fechai' $turno $maquinas
    GROUP BY 
        tblProduccionSecciones.Seccion,
        tblProduccionModulos.Modulos,
        tblBitCtrltiempos.operacion,
        tblBitCtrltiempos.electrico,
        tblBitCtrltiempos.mecanico,
        tblBitCtrltiempos.materias,
        tblBitCtrltiempos.grado,
        tblBitCtrltiempos.prev,
        tblBitCtrltiempos.servicios;";
$result = sqlsrv_query($conn, $query);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(60, 0, utf8_decode('Paros de maquina total'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 8, utf8_decode("Seccion"));
$pdf->Cell(50, 8, utf8_decode("Modulo "));
$pdf->MultiCell(20, 8, utf8_decode("Total "));
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(30, 5, utf8_decode($fila['Seccion']));
    $pdf->Cell(52, 5, utf8_decode($fila['Modulos']));
    $pdf->MultiCell(20, 5, utf8_decode($fila['TotalSuma']));
}


$pdf->Ln(10);
$turno == "" ? $turno = '' : $turno = " AND tblEncabezadoBitacora.turno=" . $_POST["turno"];
$query = "SELECT  tblBitPresentacionSub.idpresentacionenc,tblBitPresentacionEnc.presentacion,tblBitTurnohoras.horastr,
		tblBitPresentacionSub.real,tblBitPresentacionSub.acumulado,tblBitPresentacionSub.std,tblBitPresentacionGolpes.golpes,
		tblBitPresentacionGolpes.merma,tblEncabezadoBitacora.Turno FROM tblBitPresentacionSub 
		INNER JOIN tblBitPresentacionEnc ON tblBitPresentacionSub.idpresentacionenc = tblBitPresentacionEnc.idpresentacionenc
		INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave= tblBitPresentacionEnc.presentacion
		INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id = tblBitPresentacionSub.hora
		INNER JOIN tblBitPresentacionGolpes ON tblBitPresentacionGolpes.idbitacora = tblBitPresentacionEnc.folio
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitPresentacionEnc.folio
		WHERE tblBitPresentacionGolpes.hora = tblBitPresentacionSub.hora AND tblEncabezadoBitacora.fecha = '$fechai' $turno $maquinas
        ORDER BY tblEncabezadoBitacora.Fecha, tblEncabezadoBitacora.turno, tblValeEClaves.NoClave, tblBitTurnohoras.id ASC ";
// echo $query;
$result = sqlsrv_query($conn, $query);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(60, 0, utf8_decode('Producción por hora'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 8, utf8_decode("Hora"));
$pdf->Cell(45, 8, utf8_decode("Clave "));
$pdf->Cell(22, 8, utf8_decode("Real"));
$pdf->Cell(20, 8, utf8_decode("Golpes"));
$pdf->Cell(20, 8, utf8_decode("Acum."));
$pdf->Cell(22, 8, utf8_decode("STD"));
$pdf->Cell(18, 8, utf8_decode("Merma"));
$pdf->MultiCell(20, 8, utf8_decode("Turno"));
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(30, 5, utf8_decode($fila['horastr']));
    $pdf->Cell(46, 5, utf8_decode($fila['presentacion']));
    $pdf->Cell(22, 5, utf8_decode($fila['real']));
    $pdf->Cell(20, 5, utf8_decode($fila['golpes']));
    $pdf->Cell(20, 5, utf8_decode($fila['acumulado']));
    $pdf->Cell(20, 5, utf8_decode($fila['std']));
    $pdf->Cell(20, 5, utf8_decode($fila['merma']));
    $pdf->MultiCell(20, 5, utf8_decode($fila['Turno']));
}


$pdf->Ln(10);

$query = "SELECT 
    tblProduccionSecciones.Seccion, 
    tblProduccionModulos.Modulos, 
    tblBitCtrltiempos.operacion, 
    tblBitCtrltiempos.electrico, 
    tblBitCtrltiempos.mecanico, 
    tblBitCtrltiempos.materias, 
    tblBitCtrltiempos.grado, 
    tblBitCtrltiempos.prev, 
    tblBitCtrltiempos.servicios
FROM tblBitCtrltiempos INNER JOIN tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblBitCtrltiempos.seccion
INNER JOIN tblProduccionModulos ON tblProduccionModulos.idModulos = tblBitCtrltiempos.modulo
INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCtrltiempos.folio
WHERE tblEncabezadoBitacora.fecha = '$fechai' $turno $maquinas;";
$result = sqlsrv_query($conn, $query);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(60, 0, utf8_decode('Paros de maquina general'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 8, utf8_decode("Seccion"));
$pdf->Cell(45, 8, utf8_decode("Modulo "));
$pdf->Cell(22, 8, utf8_decode("Operacion"));
$pdf->Cell(20, 8, utf8_decode("Electrico"));
$pdf->Cell(20, 8, utf8_decode("Mecanico"));
$pdf->Cell(22, 8, utf8_decode("Materias"));
$pdf->Cell(20, 8, utf8_decode("Grado"));
$pdf->MultiCell(20, 8, utf8_decode("Prev"));
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(30, 5, utf8_decode($fila['Seccion']));
    $pdf->Cell(52, 5, utf8_decode($fila['Modulos']));
    $pdf->Cell(20, 5, utf8_decode($fila['operacion']));
    $pdf->Cell(20, 5, utf8_decode($fila['electrico']));
    $pdf->Cell(20, 5, utf8_decode($fila['mecanico']));
    $pdf->Cell(20, 5, utf8_decode($fila['materias']));
    $pdf->Cell(20, 5, utf8_decode($fila['grado']));
    $pdf->MultiCell(20, 5, utf8_decode($fila['prev']));
}




$pdf->Ln(10);
$query = "SELECT tblBitAsistencias.folio,tblBitAsistencias.noemp,tblempleados.nombre,tblPuestos.nombre as puesto,tblBitAsistencias.id FROM tblBitAsistencias 
		    INNER JOIN TLX032MXDB.dbo.tblempleados ON tblempleados.noemp=tblBitAsistencias.noemp
	    	INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblempleados.puesto
            INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitAsistencias.folio
            WHERE tblEncabezadoBitacora.fecha = '$fechai' $turno $maquinas";
// echo $query;
$result = sqlsrv_query($conn, $query);
$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(60, 0, utf8_decode('Asistencias'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 8, utf8_decode("No. Emp"));
$pdf->Cell(60, 8, utf8_decode("Nombre "));
$pdf->MultiCell(60, 8, utf8_decode("Puesto"));
$pdf->SetFont('Arial', '', 7);
while ($fila = sqlsrv_fetch_array($result)) {
    $pdf->Cell(30, 5, utf8_decode($fila['noemp']));
    $pdf->Cell(60, 5, utf8_decode($fila['nombre']));
    $pdf->MultiCell(60, 5, utf8_decode($fila['puesto']));
}
$pdf->Cell(50, 10, "");
$Y = $pdf->GetY();
$pdf->Output();

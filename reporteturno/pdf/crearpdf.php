<?php
require('../../fpdf/fpdf.php');
require_once "../../../csql.php";
$id=$_GET['id'];
$query="SELECT tblRepmtto.*, tblEmpleados.Nombre FROM tblRepmtto INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmtto.ibm WHERE tblRepmtto.id=$id";
	$result=sqlsrv_query($conn,$query);
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image('logo.jpg',26,5,160);
$pdf->Ln(10);
$pdf->SetFont('Arial','B',18);
$pdf->Cell(140,10,utf8_decode('REPORTE DE TURNO MANTENIMIENTO'));
$pdf->SetFont('Arial','B',14);
$pdf->Cell(40,10,utf8_decode('Numero de folio:'));
$pdf->SetFont('Arial','B',10);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,10,utf8_decode($row[0]));
$pdf->SetFont('Arial','B',10);
$pdf->Ln(10);
$pdf->MultiCell(190,5,utf8_decode('Creada con la fecha '.$row[2]->format('Y-m-d').' en el turno '.$row[3].', fue generada por el usuario con número de empleado '.$row[1].' ('.$row[6].')'));
}
$pdf->SetFont('Arial','B',14);
$pdf->MultiCell(140,10,utf8_decode('RELACIONES INDUSTRIALES'));
$pdf->SetFont('Arial','B',10);
 $query="SELECT tblRepmttori.*, tblEmpleados.Nombre FROM tblRepmttori INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblRepmttori.noemp WHERE tblRepmttori.folioenc=$id";
	$result=sqlsrv_query($conn,$query);
$pdf->Cell(20,5,utf8_decode("TURNO"));
$pdf->MultiCell(100,5,utf8_decode("AUSENTISMO"));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 120 , $Y  );
$pdf->SetFont('Arial','',8);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->Cell(20,5,utf8_decode($row[1]));
$pdf->MultiCell(100,5,utf8_decode($row[3]));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 120 , $Y  );
}
$pdf->SetFont('Arial','B',14);
$pdf->MultiCell(140,10,utf8_decode('Pendientes Mecanicos'));
$pdf->SetFont('Arial','B',10);
$query="SELECT tblRepmttopmecanicos.*,tblMaquinas.NombreMaquina,tblSecciones.NombreSeccion,tblRepmttotipopendiente.nombre FROM tblRepmttopmecanicos INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttopmecanicos.maquina INNER JOIN tblSecciones ON tblSecciones.NoSeccion=tblRepmttopmecanicos.seccion left JOIN tblRepmttotipopendiente ON tblRepmttotipopendiente.id=tblRepmttopmecanicos.tipopendiente WHERE tblRepmttopmecanicos.folioenc=$id";
		$result=sqlsrv_query($conn,$query);
$pdf->Cell(35,5,utf8_decode("MAQUINA"));
$pdf->Cell(35,5,utf8_decode("SECCIÓN"));
$pdf->Cell(35,5,utf8_decode("TIPO"));
$pdf->MultiCell(100,5,utf8_decode("PENDIENTES"));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
$pdf->SetFont('Arial','',8);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->Cell(35,5,utf8_decode($row[6]));
$pdf->Cell(35,5,utf8_decode($row[7]));
$pdf->Cell(35,5,utf8_decode($row[8]));
$pdf->MultiCell(70,5,utf8_decode($row[4]));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
}
$pdf->SetFont('Arial','B',14);
$pdf->MultiCell(140,10,utf8_decode('Paros de maquina'));
$pdf->SetFont('Arial','B',10);
$query="SELECT tblRepmttoparosmaquina.*,tblMaquinas.NombreMaquina,tblSecciones.NombreSeccion FROM tblRepmttoparosmaquina INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttoparosmaquina.maquina INNER JOIN tblSecciones ON tblSecciones.NoSeccion=tblRepmttoparosmaquina.seccion WHERE tblRepmttoparosmaquina.folioenc=$id";
$result=sqlsrv_query($conn,$query);
$pdf->Cell(35,5,utf8_decode("MAQUINA"));
$pdf->Cell(35,5,utf8_decode("SECCIÓN"));
$pdf->Cell(35,5,utf8_decode("T_Paro"));
$pdf->Cell(35,5,utf8_decode("Hora"));
$pdf->MultiCell(35,5,utf8_decode("Comentarios"));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
$pdf->SetFont('Arial','',8);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->Cell(35,5,utf8_decode($row[7]));
$pdf->Cell(35,5,utf8_decode($row[8]));
$pdf->Cell(35,5,utf8_decode($row[4]));
$pdf->Cell(35,5,utf8_decode($row[3]->format("H:i:s")));
$pdf->MultiCell(35,5,utf8_decode($row[5]));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
}
$pdf->SetFont('Arial','B',14);
$pdf->MultiCell(140,10,utf8_decode('Comentarios'));
$pdf->SetFont('Arial','B',10);
$query="SELECT tblRepmttocomentarios.*,tblMaquinas.NombreMaquina FROM tblRepmttocomentarios INNER JOIN tblMaquinas ON tblMaquinas.NoMaquina= tblRepmttocomentarios.maquina WHERE tblRepmttocomentarios.folioenc=$id";
		$result=sqlsrv_query($conn,$query);
$pdf->Cell(30,5,utf8_decode("MAQUINA"));
$pdf->MultiCell(180,5,utf8_decode("COMENTARIOS"));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
$pdf->SetFont('Arial','',8);
while ($row = sqlsrv_fetch_array($result)) {
$pdf->Cell(30,5,utf8_decode($row[4]));
$pdf->MultiCell(160,5,utf8_decode($row[2]));
$Y = $pdf->GetY();
$pdf->Line(10, $Y , 200 , $Y  );
}
$pdf->Output();
?>
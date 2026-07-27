<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
$folio = $_GET["folio"];
$query = "SELECT tblIMCEnc.id as imc,tblIMCEnc.fecha as creado,tblEmpleados.Nombre as emisor,tblEmpleados.NoEmp as noemp,tblDepartamentos.NombreDepto as departamento,
tblProactAreas.NombreArea as area,tblIMCDetecRisgo.detectasteelrisgo as deteccion, tblIMCTipRisgo.tiporiesgo as riesgo,tblIMCTipo.opciones as tipo,
tblEmpleados2.NoEmp as noempres,tblEmpleados2.Nombre as responsable,tblIMCEnc.fechacompromiso,tblIMCEstatus.estatus,tblIMCEnc.descripcion,tblIMCEnc.sugerencias FROM tblIMCEnc
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIMCEnc.emisor
INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblIMCEnc.departamento
INNER JOIN TLX003MXDB.dbo.tblProactAreas ON tblProactAreas.Id = tblIMCEnc.area
INNER JOIN tblIMCDetecRisgo ON tblIMCDetecRisgo.id = tblIMCEnc.detriesgo
INNER JOIN tblIMCTipRisgo ON tblIMCTipRisgo.id = tblIMCEnc.tiporiesgo
INNER JOIN tblIMCTipo ON tblIMCTipo.id = tblIMCEnc.tipo
INNER JOIN tblIMCEstatus ON tblIMCEstatus.id = tblIMCEnc.estado
INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmpleados2 ON tblEmpleados2.NoEmp = tblIMCEnc.responsable WHERE tblIMCEnc.id=$folio";
$result = sqlsrv_query($conn, $query);
$array = array();
while ($row = sqlsrv_fetch_array($result)) {
    array_push($array, [
        "imc" => $row["imc"], "creado" => $row["creado"]->format("Y-m-d"), "emisor" => $row["emisor"], "noemp" => $row["noemp"], "departamento" => $row["departamento"],
        "area" => $row["area"], "deteccion" => $row["deteccion"], "riesgo" => $row["riesgo"], "tipo" => $row["tipo"],
        "responsable" => $row["responsable"],"noempres" => $row["noempres"], "fechacompromiso" => $row["fechacompromiso"]->format("Y-m-d"), "estatus" => $row["estatus"],
        "descripcion" => $row["descripcion"], "sugerencias" => $row["sugerencias"]
    ]);
}
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 10, 60);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80, 10, "");
        $this->Cell(10, 10, 'IDEAS DE MEJORAMIENTO CONTINUO ');
        $this->Ln();
    }
}
$array = $array[0];
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(10, 40);
$pdf->Cell(40, 5, "Numero IMC: " . $folio);
$pdf->Ln();
$pdf->Cell(40, 5, "Fecha: " .  utf8_decode($array['creado']));
$pdf->Ln();
$pdf->Cell(40, 5, "Departamento: " .  utf8_decode($array['departamento']));
$pdf->Ln();
$pdf->Cell(40, 5, "Area: " .  utf8_decode($array['area']));
$pdf->Ln();
$pdf->Cell(40, 5, "Tipo de riesgo: " . utf8_decode($array['tipo']));
$pdf->Ln();
$pdf->Cell(40, 5, "Riesgo: " . utf8_decode($array['riesgo']));
$pdf->Ln();
$pdf->Rect(10, 90, 90, 80);
$pdf->SetXY(20, 87);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(8, 92, 102);
$pdf->Cell(20, 5, "Emisor ", 1, 1, 'C', 1);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(90, 5, "NoEmp: " . $array['noemp']);
$pdf->MultiCell(90, 5, "Nombre: " .  utf8_decode($array['emisor']));
$pdf->MultiCell(90, 5, "Puesto: ");
$pdf->MultiCell(90, 5, utf8_decode("Descripción:".$array['descripcion'] ));
$pdf->Rect(110, 40, 90, 80);
$pdf->SetXY(120, 37);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(8, 92, 102);
$pdf->Cell(25, 5, "Responsable ", 1, 1, 'C', 1);
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(100, 5, "");
$pdf->MultiCell(90, 5, "NoEmp: " .  utf8_decode($array['noempres']));
$pdf->Cell(100, 5, "");
$pdf->MultiCell(90, 5, "Nombre: " .  utf8_decode($array['responsable']));
$pdf->Cell(100, 5, "");
$pdf->MultiCell(90, 5, "Puesto: ");
$pdf->Cell(100, 5, "");
$pdf->MultiCell(90, 5, "Acciones correctivas a tomar: ". utf8_decode($array['sugerencias']));
$pdf->Output();

<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");
function getdatavale($folio)
{
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX002MXDB");
    $query = "SELECT tblValeEEnc.id,tblMaquinas.NombreMaquina as maquina,tblEmpleados.noemp,tblEmpleados.Nombre,Trazabilidadturno.nombre as turno,
    tblValeEEnc.clave1,tblValeEEnc.clave2,tblValeEEnc.clave3,tblValeEEnc.clave4,tblValeEEstados.estado,tblValeEEnc.fecha, tblValeEEnc.fechaenviado,
	tblPuestos.nombre as puesto,tblValeEEnc.foliocons,tbl2emp.NoEmp as noempsup,tbl2emp.Nombre as nombresup,tblpuestossup.nombre as puestosup
    FROM tblValeEEnc INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= tblValeEEnc.maquina
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblValeEEnc.noemp
    INNER JOIN TLX036MXDB.dbo.Trazabilidadturno ON Trazabilidadturno.id= tblValeEEnc.turno
    INNER JOIN tblValeEEstados ON tblValeEEstados.id= tblValeEEnc.estado
	INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto
    INNER JOIN TLX032MXDB.dbo.tblEmpleados as tbl2emp ON tbl2emp.NoEmp= tblValeEEnc.supervisor
	INNER JOIN TLX009MXDB.dbo.tblPuestos as tblpuestossup ON tblpuestossup.id = tbl2emp.Puesto WHERE tblValeEEnc.id=" . $folio;
    $result = sqlsrv_query($conn, $query);
    $array = array();
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($array, [
            'id' => $row['id'],
            'maquina' => $row['maquina'],
            'noemp' => $row['noemp'],
            'nombre' => $row['Nombre'],
            'turno' => $row['turno'],
            'clave1' => $row['clave1'],
            'clave2' => $row['clave2'],
            'clave3' => $row['clave3'],
            'clave4' => $row['clave4'],
            'estado' => $row['estado'],
            'fechac' => $row['fecha']->format('Y-m-d H:i:s'),
            'fechae' => $row['fechaenviado']->format('Y-m-d H:i:s'),
            'Puesto' => $row['puesto'],
            'foliocons' => $row['foliocons'],
            'noempsup' => $row['noempsup'],
            'nombresup' => $row['nombresup'],
            'puestosup' => $row['puestosup']
        ]);
    }
    return $array;
    sqlsrv_close($conn);
}
function getmateriales($folio)
{
    $conexion = new ClassConexion();
    $conn = $conexion->conexion('TLX002MXDB');

    $maquinasKgs = [
        140,
        141,
        142,
        143,
        144,
        145,
        146,
        147,
        148,
        149,
        150,
        151,
        152,
        153,
        154,
        155,
        156,
        157,
        158
    ];

    $query = "SELECT tblValeEMateriales.*, tblValeEMaterialesAdd.id as folio, tblValeEMaterialesAdd.Cantidad, tblValeEMaterialesAdd.CantidadKgs, tblValeEMaterialesAdd.estado as estadomat, tblValeEEnc.maquina
       FROM tblValeEMaterialesAdd 
    INNER JOIN tblValeEMateriales ON tblValeEMateriales.NoMaterial = tblValeEMaterialesAdd.idMaterial 
    INNER JOIN tblValeEEnc ON tblValeEEnc.id = tblValeEMaterialesAdd.idValeEnc
    WHERE tblValeEMaterialesAdd.idValeEnc=? 
    ORDER BY tblValeEMateriales.TipoMontacargas";
    
    $params = [$folio];
    $result = sqlsrv_query($conn, $query, $params);

    $array = [];
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {        
        $usaKgs = in_array($row['maquina'], $maquinasKgs);

        $array[] = [
            'folio' => $row['folio'],
            'NoMaterial' => $row['NoMaterial'],
            'NombreMaterial'=> $row['NombreMaterial'],
            'CentroCosto' => $row['CentroCosto'],
            'TiempoMaterial'=> $row['TiempoMaterial'],
            'TipoMontacargas'=> $row['TipoMontacargas'],
            'Cantidad' => $usaKgs ? $row['CantidadKgs'] : $row['Cantidad'],
            'UM' => $usaKgs ? $row['UMMaterial'] : $row['UM'],
            'estadomat' => $row['estadomat']
        ];
    }

    sqlsrv_close($conn);
    return $array;
}


class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 10, 50);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(60, 10, "");
        $this->Cell(10, 10, 'REQUERIMIENTO DE MATERIALES AL ALMACEN DE MATERIA PRIMA');
        $this->SetFont('Arial', 'B', 7);
        $this->SetXY(270, 5);
        $this->Cell(10, 5, 'REF: 8-383-A-04');
        $this->SetXY(270, 8);
        $this->Cell(10, 5, 'FORM-60524');
        // $this->SetXY(270, 11);
        // $this->Cell(10, 5, 'REV. 07');
    }
    function Footer()
    {
        global $conteohojas;
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página') . $this->PageNo() . ' de ' . $conteohojas, 0, 0, 'C');
    }
}
$conteohojas = 0;
$folio = $_GET["folio"];
$pdf = new PDF();
$array = getdatavale($folio);
$array = $array[0];
$arraymat = getmateriales($folio);
$tipomonta = '';
foreach ($arraymat as $element) {
    if ($tipomonta != $element['TipoMontacargas']) {
        $conteohojas++;
    }
    $tipomonta = $element['TipoMontacargas'];
}
$tipomonta = '';
foreach ($arraymat as $element) {
    if ($tipomonta === '' || $tipomonta !== $element['TipoMontacargas']) {
        $pdf->AddPage('L', 'A4', 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetXY(10, 30);
        $pdf->Cell(15, 5, "Folio: ", 1, 0, 'C');
        $pdf->Cell(30, 5, $array['maquina'] . ' - ' . $array['foliocons'], 1, 0, 'C');
        $pdf->Cell(2, 5, "");
        $pdf->Cell(20, 5, "Maquina: ", 1, 0, 'C');
        $pdf->Cell(40, 5, utf8_decode($array['maquina']), 1, 0, 'C');
        $pdf->Cell(2, 5, "");
        $pdf->Cell(15, 5, "Fecha: ", 1, 0, 'C');
        $pdf->Cell(50, 5, utf8_decode($array['fechac']), 1, 0, 'C');
        $pdf->Cell(2, 5, "");
        $pdf->Cell(15, 5, "Turno: ", 1, 0, 'C');
        $pdf->Cell(30, 5, utf8_decode($array['turno']), 1, 0, 'C');
        $pdf->Cell(2, 5, "");
        $pdf->Cell(15, 5, "Monta: ", 1, 0, 'C');
        $pdf->Cell(40, 5, utf8_decode($element['TipoMontacargas']), 1, 0, 'C');
        $pdf->Ln(7);
        $pdf->Cell(15, 5, "Claves:", 1, 0, 'C');
        $pdf->Cell(15, 5, utf8_decode($array['clave1']), 1, 0, 'C');
        $pdf->Cell(15, 5, utf8_decode($array['clave2']), 1, 0, 'C');
        $pdf->Cell(15, 5, utf8_decode($array['clave3']), 1, 0, 'C');
        $pdf->Cell(15, 5, utf8_decode($array['clave4']), 1, 0, 'C');
        $pdf->Cell(2, 5, "");
        $pdf->Cell(20, 5, "Estado:", 1, 0, 'C');
        $pdf->Cell(40, 5, utf8_decode($array['estado']), 1, 0, 'C');
        $pdf->Cell(2, 5, "");
        $pdf->Cell(25, 5, "Confirmado: ", 1, 0, 'C');
        $pdf->Cell(50, 5, utf8_decode($array['fechae']), 1, 0, 'C');
        $pdf->Rect(10, 150, 130, 20);
        $pdf->SetXY(10, 145);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(8, 92, 102);
        $pdf->Cell(100, 5, utf8_decode("Solicitó "), 1, 1, 'C', 1);
        $pdf->SetXY(100, 145);
        $pdf->Cell(40, 5, utf8_decode("Firma "), 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 5, "NoEmp: " . utf8_decode($array['noemp']));
        $pdf->Ln();
        $pdf->Cell(90, 5, "Nombre: " . utf8_decode($array['nombre']));
        $pdf->Ln();
        $pdf->Cell(90, 5, "Puesto: " . utf8_decode($array['Puesto']));
        $pdf->SetXY(150, 145);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(8, 92, 102);
        $pdf->Rect(150, 150, 130, 20);
        $pdf->Cell(100, 5, utf8_decode("Supervisor "), 1, 1, 'C', 1);
        $pdf->SetXY(240, 145);
        $pdf->Cell(40, 5, utf8_decode("Firma "), 1, 1, 'C', 1);
        $pdf->Ln(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(150);
        $pdf->Cell(90, 5, "NoEmp: " . utf8_decode($array['noempsup']));
        $pdf->Ln();
        $pdf->SetX(150);
        $pdf->Cell(90, 5, "Nombre: " . utf8_decode($array['nombresup']));
        $pdf->Ln();
        $pdf->SetX(150);
        $pdf->Cell(90, 5, "Puesto: " . utf8_decode($array['puestosup']));

        $pdf->Ln(10);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(8, 92, 102);
        $pdf->Rect(10, 177, 130, 20);
        $pdf->Cell(100, 5, utf8_decode("Recibe"), 1, 1, 'C', 1);
        $pdf->SetXY(100, 172);
        $pdf->Cell(40, 5, utf8_decode("Firma "), 1, 1, 'C', 1);
        $pdf->Ln(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 5, "NoEmp: ");
        $pdf->Ln();
        $pdf->SetX(10);
        $pdf->Cell(90, 5, "Nombre: ");

        $pdf->SetXY(150, 172);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(8, 92, 102);
        $pdf->Rect(150, 177, 130, 20);
        $pdf->Cell(100, 5, utf8_decode("Surtió "), 1, 1, 'C', 1);
        $pdf->SetXY(240, 172);
        $pdf->Cell(40, 5, utf8_decode("Firma "), 1, 1, 'C', 1);
        $pdf->Ln(2);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetX(150);
        $pdf->Cell(90, 5, "NoEmp: ");
        $pdf->Ln();
        $pdf->SetX(150);
        $pdf->Cell(90, 5, "Nombre: ");
        $pdf->Ln();


        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(215, 45);
        $pdf->Cell(40, 5, "MP ", 1, 0, 'C');
        $pdf->Cell(40, 5, "Produccion", 1, 0, 'C');

        $pdf->SetXY(10, 50);
        $pdf->Cell(10, 5, "NoMaterial: ");
        $pdf->SetX(40);
        $pdf->Cell(10, 5, "NombreMaterial: ");
        $pdf->SetX(150);
        $pdf->Cell(10, 5, "Cantidad: ");
        $pdf->SetX(170);
        $pdf->Cell(10, 5, "U Env: ");
        $pdf->SetX(190);
        $pdf->Cell(10, 5, "Estado: ");
        $pdf->SetX(215);
        $pdf->Cell(10, 5, "Envases: ");
        $pdf->SetX(236);
        $pdf->Cell(10, 5, "Unidades: ");
        $pdf->SetX(256);
        $pdf->Cell(10, 5, "Envases: ");
        $pdf->SetX(276);
        $pdf->Cell(10, 5, "Unidades: ");
        $pdf->SetXY(10, 55);
    }
    $tipomonta = $element['TipoMontacargas'];
    $pdf->Cell(30, 5, $element['NoMaterial'], 1);
    $pdf->SetX(40);
    $pdf->Cell(110, 5, utf8_decode($element['NombreMaterial']), 1);
    $pdf->SetX(150);
    $pdf->Cell(20, 5, $element['Cantidad'], 1);
    $pdf->SetX(170);
    $pdf->Cell(20, 5, $element['UM'], 1);
    $pdf->SetX(190);
    $elemento = '';
    if ($element['estadomat'] == 1)
        $elemento = 'Solicitado';
    else if ($element['estadomat'] == 2)
        $elemento = 'No disponible';
    else if ($element['estadomat'] == 3)
        $elemento = 'Envio MP';
    $pdf->Cell(25, 5, $elemento, 1);
    $pdf->Cell(20, 5, '', 1);
    $pdf->Cell(20, 5, '', 1);
    $pdf->Cell(20, 5, '', 1);
    $pdf->MultiCell(20, 5, '', 1);
}
$pdf->Output();

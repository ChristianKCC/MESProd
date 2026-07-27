<?php
require('../../fpdf/fpdf.php');
require_once("../../conexion.php");

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");
$id = $_GET["id"];
$query = "SELECT 
tblEnfermeriaExamenM.id, 
tblEnfermeriaExamenM.noemp, 
tblEnfermeriaExamenM.departamento,
tblEnfermeriaExamenM.puesto,
tblEnfermeriaExamenM.maquina,
tblEnfermeriaExamenM.fechanaimiento,
tblEnfermeriaExamenM.lugarnac,
tblEnfermeriaExamenM.domicilio,
tblEnfermeriaExamenM.escolaridad,
tblEnfermeriaExamenM.religion,
tblEnfermeriaExamenM.tiposangre,
tblEnfermeriaExamenM.fechaingreso,
tblEnfermeriaExamenM.problemasdesalud,
tblEnfermeriaExamenM.tomamedicamento,
tblEnfermeriaExamenM.tratamientomedico, 
tblEnfermeriaExamenM.enfermedadcronica,
tblEnfermeriaExamenM.tabaquismo,
tblEnfermeriaExamenM.alcoholismo,
tblEnfermeriaExamenM.altfisica, 
tblEnfermeriaExamenM.quirurgicos,
tblEnfermeriaExamenM.traumaticos,
tblEnfermeriaExamenM.transfuciones,
tblEnfermeriaExamenM.antivioticos,
tblEnfermeriaExamenM.analgesitos,
tblEnfermeriaExamenM.antiinflamatorios,
tblEnfermeriaExamenM.otrosalergias,
tblEnfermeriaExamenM.habhigienicodietetico, 
tblEnfermeriaExamenM.alimentacion,
tblEnfermeriaExamenM.aseogeneral,
tblEnfermeriaExamenM.hobbies, 
tblEnfermeriaExamenM.otrasactlaborales, 
tblEnfermeriaExamenM.incapacidades,
tblEnfermeriaExamenM.diagnostico, 
tblEnfermeriaExamenM.diasIncapacidad, 
tblEnfermeriaExamenM.secuela, 
tblEnfermeriaExamenM.rehabilitacion, 
tblEnfermeriaExamenM.trayecto, 
tblEnfermeriaExamenM.enfgeneral, 
tblEnfermeriaExamenM.accidentetrabajo, 
tblEnfermeriaExamenM.enfermedadtrabajo, 
tblEnfermeriaExamenM.Tos, 
tblEnfermeriaExamenM.expectoracion, 
tblEnfermeriaExamenM.dolortoracico, 
tblEnfermeriaExamenM.taquicardia, 
tblEnfermeriaExamenM.disnea, 
tblEnfermeriaExamenM.cianosis, 
tblEnfermeriaExamenM.edema, 
tblEnfermeriaExamenM.obscardio, 
tblEnfermeriaExamenM.dolorabdominal, 
tblEnfermeriaExamenM.transintestinal, 
tblEnfermeriaExamenM.excretaxdia, 
tblEnfermeriaExamenM.orofaringeo, 
tblEnfermeriaExamenM.abdomen, 
tblEnfermeriaExamenM.hernia, 
tblEnfermeriaExamenM.obsdigestivo, 
tblEnfermeriaExamenM.Observaciongeneral, 
tblEnfermeriaExamenM.peso, 
tblEnfermeriaExamenM.talla, 
tblEnfermeriaExamenM.imc, 
tblEnfermeriaExamenM.fc, 
tblEnfermeriaExamenM.fr, 
tblEnfermeriaExamenM.ta, 
tblEnfermeriaExamenM.ojoder, 
tblEnfermeriaExamenM.ojoizq, 
tblEnfermeriaExamenM.bilateral, 
tblEnfermeriaExamenM.pupilas,
tblEnfermeriaExamenM.conciencia, 
tblEnfermeriaExamenM.sensible, 
tblEnfermeriaExamenM.sueno, 
tblEnfermeriaExamenM.reflejo, 
tblEnfermeriaExamenM.observacionnervios, 
tblEnfermeriaExamenM.audicion,
tblEnfermeriaExamenM.agilidadvisual, 
tblEnfermeriaExamenM.reflejos, 
tblEnfermeriaExamenM.campimetria, 
tblEnfermeriaExamenM.olfato, 
tblEnfermeriaExamenM.tacto, 
tblEnfermeriaExamenM.cardiopulmonar, 
tblEnfermeriaExamenM.tecnicarte, 
tblEnfermeriaExamenM.octocerosis, 
tblEnfermeriaExamenM.timpano, 
tblEnfermeriaExamenM.cardiopulmonar2, 
tblEnfermeriaExamenM.tecnicarte2, 
tblEnfermeriaExamenM.freccardiaca, 
tblEnfermeriaExamenM.viasrespi, 
tblEnfermeriaExamenM.camppulmonar, 
tblEnfermeriaExamenM.obsgencardio, 
tblEnfermeriaExamenM.digestivo, 
tblEnfermeriaExamenM.peristalsis, 
tblEnfermeriaExamenM.dolor, 
tblEnfermeriaExamenM.organomegalias, 
tblEnfermeriaExamenM.herniaumbilical, 
tblEnfermeriaExamenM.cuello, 
tblEnfermeriaExamenM.columnavertebral, 
tblEnfermeriaExamenM.movilidad, 
tblEnfermeriaExamenM.marcha, 
tblEnfermeriaExamenM.rots, 
tblEnfermeriaExamenM.puntorlumbar, 
tblEnfermeriaExamenM.lasage, 
tblEnfermeriaExamenM.bragard, 
tblEnfermeriaExamenM.tinel, 
tblEnfermeriaExamenM.phanel, 
tblEnfermeriaExamenM.trendelemburg, 
tblEnfermeriaExamenM.obsmusculo, 
tblEnfermeriaExamenM.espnormal,
tblEnfermeriaExamenM.espobstructivo, 
tblEnfermeriaExamenM.esprestrictivo, 
tblEnfermeriaExamenM.espmixto, 
tblEnfermeriaExamenM.d1, 
tblEnfermeriaExamenM.d2, 
tblEnfermeriaExamenM.d3, 
tblEnfermeriaExamenM.d4,
tblEnfermeriaExamenM.d5, 
tblEnfermeriaExamenM.d6, 
tblEnfermeriaExamenM.d7, 
tblEnfermeriaExamenM.d8, 
tblEnfermeriaExamenM.i1, 
tblEnfermeriaExamenM.i2, 
tblEnfermeriaExamenM.i3, 
tblEnfermeriaExamenM.i4, 
tblEnfermeriaExamenM.i5, 
tblEnfermeriaExamenM.i6, 
tblEnfermeriaExamenM.i7, 
tblEnfermeriaExamenM.i8, 
tblEnfermeriaExamenM.diagnostivosano, 
tblEnfermeriaExamenM.conductiva, 
tblEnfermeriaExamenM.sensorial, 
tblEnfermeriaExamenM.mixma, 
tblEnfermeriaExamenM.unilateral, 
tblEnfermeriaExamenM.bilateralstp, 
tblEnfermeriaExamenM.superficial, 
tblEnfermeriaExamenM.moderada, profunda, 
tblEnfermeriaExamenM.traumadegenerativo, 
tblEnfermeriaExamenM.traumamixto, traumaotros, 
tblEnfermeriaExamenM.otocerosis, infeccionfaringea, 
tblEnfermeriaExamenM.perforanciatimpanica, 
tblEnfermeriaExamenM.fecharevision, 
tblEnfermeriaExamenM.session,
tblEnfermeriaExamenM.menarco,
tblEnfermeriaExamenM.diurisis,
tblEmpleados.Nombre as nomemp,
tblDepartamentos.NombreDepto,
tblPuestos.nombre as puesto,
tblMaquinas.NombreMaquina as Maquina,
tblEnfermeriaReligion.religiondesc,
tblEnfermeriaTipoSangre.tiposangredesc,
tblEnfermeriaExamenM.firma,
tblEnfermeriaExamenM.puestoAnterior,
tblEnfermeriaExamenM.horarioAnterior,
tblEnfermeriaExamenM.tiempoTrabajoAnterior,
tblEnfermeriaExamenM.seguridadIndustrial,
tblEnfermeriaExamenM.expoRuidos,
tblEnfermeriaExamenM.expoQuimicos,
tblEnfermeriaExamenM.equipoproteccion,
tblE2.Nombre as medico
FROM tblEnfermeriaExamenM
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEnfermeriaExamenM.noemp
INNER JOIN TLX009MXDB.DBO.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
INNER JOIN TLX009MXDB.DBO.tblPuestos ON tblPuestos.id= tblEmpleados.Puesto
INNER JOIN TLX009MXDB.DBO.tblMaquinas ON tblMaquinas.NoMaquina= tblEnfermeriaExamenM.maquina
INNER JOIN tblEnfermeriaReligion ON tblEnfermeriaReligion.id = tblEnfermeriaExamenM.religion
INNER JOIN TLX032MXDB.dbo.tblEmpleados tblE2 ON tblE2.NoEmp = tblEnfermeriaExamenM.medico
INNER JOIN tblEnfermeriaTipoSangre ON tblEnfermeriaTipoSangre.id = tblEnfermeriaExamenM.tiposangre WHERE tblEnfermeriaExamenM.id = $id";


$result = sqlsrv_query($conn, $query);
$array = array();
while ($row = sqlsrv_fetch_array($result)) {
    array_push($array, [
        "id" => $row["id"],
        "noemp" => $row["noemp"],
        "departamento" => $row["departamento"],
        "puesto" => $row["puesto"],
        "maquina" => $row["maquina"],
        "fechanaimiento" => $row["fechanaimiento"]->format("Y-m-d"),
        "lugarnac" => $row["lugarnac"],
        "domicilio" => $row["domicilio"],
        "escolaridad" => $row["escolaridad"],
        "religion" => $row["religion"],
        "tiposangre" => $row["tiposangre"],
        "fechaingreso" => $row["fechaingreso"]->format("Y-m-d"),
        "problemasdesalud" => $row["problemasdesalud"],
        "tomamedicamento" => $row["tomamedicamento"],
        "tratamientomedico" => $row["tratamientomedico"],
        "enfermedadcronica" => $row["enfermedadcronica"],
        "tabaquismo" => $row["tabaquismo"],
        "alcoholismo" => $row["alcoholismo"],
        "altfisica" => $row["altfisica"],
        "quirurgicos" => $row["quirurgicos"],
        "traumaticos" => $row["traumaticos"],
        "transfuciones" => $row["transfuciones"],
        "antivioticos" => $row["antivioticos"],
        "analgesitos" => $row["analgesitos"],
        "antiinflamatorios" => $row["antiinflamatorios"],
        "otrosalergias" => $row["otrosalergias"],
        "habhigienicodietetico" => $row["habhigienicodietetico"],
        "alimentacion" => $row["alimentacion"],
        "aseogeneral" => $row["aseogeneral"],
        "hobbies" => $row["hobbies"],
        "otrasactlaborales" => $row["otrasactlaborales"],
        "incapacidades" => $row["incapacidades"],
        "diagnostico" => $row["diagnostico"],
        "diasIncapacidad" => $row["diasIncapacidad"],
        "secuela" => $row["secuela"],
        "rehabilitacion" => $row["rehabilitacion"],
        "trayecto" => $row["trayecto"],
        "enfgeneral" => $row["enfgeneral"],
        "accidentetrabajo" => $row["accidentetrabajo"],
        "enfermedadtrabajo" => $row["enfermedadtrabajo"],
        "Tos" => $row["Tos"],
        "expectoracion" => $row["expectoracion"],
        "dolortoracico" => $row["dolortoracico"],
        "taquicardia" => $row["taquicardia"],
        "disnea" => $row["disnea"],
        "cianosis" => $row["cianosis"],
        "edema" => $row["edema"],
        "obscardio" => $row["obscardio"],
        "dolorabdominal" => $row["dolorabdominal"],
        "transintestinal" => $row["transintestinal"],
        "excretaxdia" => $row["excretaxdia"],
        "orofaringeo" => $row["orofaringeo"],
        "abdomen" => $row["abdomen"],
        "hernia" => $row["hernia"],
        "obsdigestivo" => $row["obsdigestivo"],
        "Observaciongeneral" => $row["Observaciongeneral"],
        "peso" => $row["peso"],
        "talla" => $row["talla"],
        "imc" => $row["imc"],
        "fc" => $row["fc"],
        "fr" => $row["fr"],
        "ta" => $row["ta"],
        "ojoder" => $row["ojoder"],
        "ojoizq" => $row["ojoizq"],
        "bilateral" => $row["bilateral"],
        "pupilas" => $row["pupilas"],
        "conciencia" => $row["conciencia"],
        "sensible" => $row["sensible"],
        "sueno" => $row["sueno"],
        "reflejo" => $row["reflejo"],
        "observacionnervios" => $row["observacionnervios"],
        "audicion" => $row["audicion"],
        "agilidadvisual" => $row["agilidadvisual"],
        "reflejos" => $row["reflejos"],
        "campimetria" => $row["campimetria"],
        "olfato" => $row["olfato"],
        "tacto" => $row["tacto"],
        "cardiopulmonar" => $row["cardiopulmonar"],
        "tecnicarte" => $row["tecnicarte"],
        "octocerosis" => $row["octocerosis"],
        "timpano" => $row["timpano"],
        "cardiopulmonar2" => $row["cardiopulmonar2"],
        "tecnicarte2" => $row["tecnicarte2"],
        "freccardiaca" => $row["freccardiaca"],
        "viasrespi" => $row["viasrespi"],
        "camppulmonar" => $row["camppulmonar"],
        "obsgencardio" => $row["obsgencardio"],
        "digestivo" => $row["digestivo"],
        "peristalsis" => $row["peristalsis"],
        "dolor" => $row["dolor"],
        "organomegalias" => $row["organomegalias"],
        "herniaumbilical" => $row["herniaumbilical"],
        "cuello" => $row["cuello"],
        "columnavertebral" => $row["columnavertebral"],
        "movilidad" => $row["movilidad"],
        "marcha" => $row["marcha"],
        "rots" => $row["rots"],
        "puntorlumbar" => $row["puntorlumbar"],
        "lasage" => $row["lasage"],
        "bragard" => $row["bragard"],
        "tinel" => $row["tinel"],
        "phanel" => $row["phanel"],
        "trendelemburg" => $row["trendelemburg"],
        "obsmusculo" => $row["obsmusculo"],
        "espnormal" => $row["espnormal"],
        "espobstructivo" => $row["espobstructivo"],
        "esprestrictivo" => $row["esprestrictivo"],
        "espmixto" => $row["espmixto"],
        "d1" => $row["d1"],
        "d2" => $row["d2"],
        "d3" => $row["d3"],
        "d4" => $row["d4"],
        "d5" => $row["d5"],
        "d6" => $row["d6"],
        "d7" => $row["d7"],
        "d8" => $row["d8"],
        "i1" => $row["i1"],
        "i2" => $row["i2"],
        "i3" => $row["i3"],
        "i4" => $row["i4"],
        "i5" => $row["i5"],
        "i6" => $row["i6"],
        "i7" => $row["i7"],
        "i8" => $row["i8"],
        "diagnostivosano" => $row["diagnostivosano"],
        "conductiva" => $row["conductiva"],
        "sensorial" => $row["sensorial"],
        "mixma" => $row["mixma"],
        "unilateral" => $row["unilateral"],
        "bilateralstp" => $row["bilateralstp"],
        "superficial" => $row["superficial"],
        "moderada" => $row["moderada"],
        "profunda" => $row["profunda"],
        "traumadegenerativo" => $row["traumadegenerativo"],
        "traumamixto" => $row["traumamixto"],
        "traumaotros" => $row["traumaotros"],
        "otocerosis" => $row["otocerosis"],
        "infeccionfaringea" => $row["infeccionfaringea"],
        "perforanciatimpanica" => $row["perforanciatimpanica"],
        "fecharevision" => $row["fecharevision"]->format("d-m-Y"),
        "session" => $row["session"],
        "menarco" => $row["menarco"],
        "diurisis" => $row["diurisis"],
        "Nombreemp" => $row["nomemp"],
        "NombreDepto" => $row["NombreDepto"],
        "Maquina" => $row["Maquina"],
        "religiondesc" => $row["religiondesc"],
        "tiposangredesc" => $row["tiposangredesc"],
        "firma" => $row["firma"],
        "puestoAnterior" => $row["puestoAnterior"],
        "horarioAnterior" => $row["horarioAnterior"],
        "tiempoTrabajoAnterior" => $row["tiempoTrabajoAnterior"],
        "seguridadIndustrial" => $row["seguridadIndustrial"],
        "expoRuidos" => $row["expoRuidos"],
        "expoQuimicos" => $row["expoQuimicos"],
        "equipoproteccion" => $row["equipoproteccion"],
        "medico" => $row["medico"]
    ]);
}

$firmaBase64 = $array[0]['firma'];


if ($firmaBase64) {
    // Si la cadena contiene el encabezado "data:image/png;base64,", lo eliminamos
    if (strpos($firmaBase64, ',') !== false) {
        $base64Data = explode(',', $firmaBase64)[1];
    } else {
        $base64Data = $firmaBase64;
    }

    // Crear archivo temporal
    $imagenPath = tempnam(sys_get_temp_dir(), 'firma_') . '.png';
    file_put_contents($imagenPath, base64_decode($base64Data));
} else {
    echo "No se encontró firma en la base de datos.";
}


class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->Image('../../img/imglogoprosede.png', 10, 10, 60);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(80, 10, "");
        $this->Ln();
        $this->SetLeftMargin(27);
        $this->SetRightMargin(25);
        $this->Cell(160, 10, mb_convert_encoding("Exámen Médico de Ingreso", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln();
    }
}

$array = $array[0];
$fecha = $array['fecharevision'];
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8); //Tamaño y fuente de la letra
$pdf->SetX(170); //define la pocision de X y Y
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 5, "Fecha:");
$pdf->SetFont('Arial', '', 8);
$pdf->SetX(185);
$pdf->Cell(45, 5, "$fecha");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(22, 5, "No. Empleado:");
$pdf->SetFont('Arial', '', 8);
$noemp = (isset($array['noemp']) && $array['noemp'] !== null && trim((string) $array['noemp']) !== '') ? $array['noemp'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding((string) $noemp, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(12, 5, "Puesto:");
$pdf->SetFont('Arial', '', 8);
$puesto = (isset($array['puesto']) && $array['puesto'] !== null && trim((string) $array['puesto']) !== '') ? $array['puesto'] : 'N/A';
$pdf->Cell(60, 5, mb_convert_encoding((string) $puesto, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(12, 5, mb_convert_encoding("Área:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$NombreDepto = (isset($array['NombreDepto']) && $array['NombreDepto'] !== null && trim((string) $array['NombreDepto']) !== '') ? $array['NombreDepto'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $NombreDepto, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(14, 5, "Maquina:");
$pdf->SetFont('Arial', '', 8);
$maquina = (isset($array['Maquina']) && $array['Maquina'] !== null && trim((string) $array['Maquina']) !== '') ? $array['Maquina'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $maquina, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, "Nombre Completo:");
$pdf->SetFont('Arial', '', 8);
$nombreemp = (isset($array['Nombreemp']) && $array['Nombreemp'] !== null && trim((string) $array['Nombreemp']) !== '') ? $array['Nombreemp'] : 'N/A';
$pdf->Cell(50, 5, mb_convert_encoding((string) $nombreemp, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(50);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(60, 5, "Apellido Paterno ");
$pdf->Cell(60, 5, "Apellido Materno ");
$pdf->Cell(60, 5, "Nombres");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Domicilio: ");
$pdf->SetFont('Arial', '', 8);
$domicilio = (isset($array['domicilio']) && $array['domicilio'] !== null && trim((string) $array['domicilio']) !== '') ? $array['domicilio'] : 'N/A';
$pdf->Cell(40, 5, mb_convert_encoding((string) $domicilio, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(35);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Calle");
$pdf->Cell(25, 5, "Numero");
$pdf->Cell(25, 5, "Colonia");
$pdf->Cell(25, 5, "Municipio");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, "Fecha de Nacimiento:");
$pdf->SetFont('Arial', '', 8);
$fechanaimiento = (isset($array['fechanaimiento']) && $array['fechanaimiento'] !== null && trim((string) $array['fechanaimiento']) !== '') ? $array['fechanaimiento'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $fechanaimiento, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, "Lugar:");
$pdf->SetFont('Arial', '', 8);
$lugarnac = (isset($array['lugarnac']) && $array['lugarnac'] !== null && trim((string) $array['lugarnac']) !== '') ? $array['lugarnac'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $lugarnac, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, "Escolaridad:");
$pdf->SetFont('Arial', '', 8);
$escolaridad = (isset($array['escolaridad']) && $array['escolaridad'] !== null && trim((string) $array['escolaridad']) !== '') ? $array['escolaridad'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $escolaridad, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();
$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Religion:");
$pdf->SetFont('Arial', '', 8);
$religion = (isset($array['religiondesc']) && $array['religiondesc'] !== null && trim((string) $array['religiondesc']) !== '') ? $array['religiondesc'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $religion, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, "Grupo Sanguineo:");
$pdf->SetFont('Arial', '', 8);
$tiposangre = (isset($array['tiposangredesc']) && $array['tiposangredesc'] !== null && trim((string) $array['tiposangredesc']) !== '') ? $array['tiposangredesc'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $tiposangre, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();


$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 5, mb_convert_encoding("¿Qué puesto desempeñaba en la última empresa que laboró?", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$puestoAnterior = (isset($array['puestoAnterior']) && $array['puestoAnterior'] !== null && trim((string) $array['puestoAnterior']) !== '') ? $array['puestoAnterior'] : 'N/A';
$pdf->SetX(95);
$pdf->Cell(50, 5, mb_convert_encoding((string) $puestoAnterior, 'ISO-8859-1', 'UTF-8'));
$pdf->SetX(93);
$pdf->Cell(50, 5, "____________________");
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, "Horario Laboral:");
$horarioAnterior = (isset($array['horarioAnterior']) && $array['horarioAnterior'] !== null && trim((string) $array['horarioAnterior']) !== '') ? $array['horarioAnterior'] : 'N/A';
$pdf->SetX(170);
$pdf->Cell(30, 5, mb_convert_encoding((string) $horarioAnterior, 'ISO-8859-1', 'UTF-8'));
$pdf->SetX(168);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(30, 5, "___________");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(60, 5, mb_convert_encoding("¿Cuánto tiempo trabajó ahí?", 'ISO-8859-1', 'UTF-8'));
$tiempoAnterior = (isset($array['tiempoTrabajoAnterior']) && $array['tiempoTrabajoAnterior'] !== null && trim((string) $array['tiempoTrabajoAnterior']) !== '') ? $array['tiempoTrabajoAnterior'] : 'N/A';
$pdf->SetX(55);
$pdf->Cell(50, 5, mb_convert_encoding((string) $tiempoAnterior, 'ISO-8859-1', 'UTF-8'));
$pdf->SetX(53);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(50, 5, "_____________");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(90, 5, mb_convert_encoding("Indique qué tipo de protección personal utilizó:", 'ISO-8859-1', 'UTF-8'));
$equipoproteccion = (isset($array['equipoproteccion']) && $array['equipoproteccion'] !== null && trim((string) $array['equipoproteccion']) !== '') ? $array['equipoproteccion'] : 'N/A';
$pdf->SetX(77);
$pdf->Cell(50, 5, mb_convert_encoding((string) $equipoproteccion, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->SetX(75);
$pdf->Cell(100, 5, "_______________________________________________________________________________");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(60, 5, mb_convert_encoding("¿Qué es seguridad industrial?", 'ISO-8859-1', 'UTF-8'));
$seguridadIndustrial = (isset($array['seguridadIndustrial']) && $array['seguridadIndustrial'] !== null && trim((string) $array['seguridadIndustrial']) !== '') ? $array['seguridadIndustrial'] : 'N/A';
$pdf->SetX(55);
$pdf->Cell(50, 5, mb_convert_encoding((string) $seguridadIndustrial, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->SetX(53);
$pdf->Cell(130, 5, "_____________________________________________________________________________________________");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->MultiCell(190, 5, mb_convert_encoding("Durante el tiempo que ha estado laborando ha estado expuesto a: ruido, polvos, calor, humedad, vibraciones, radiaciones o a otros? Indique a cuál de estos ha estado expuesto por favor:", 'ISO-8859-1', 'UTF-8'));
$expoRuids = (isset($array['expoRuidos']) && $array['expoRuidos'] !== null && trim((string) $array['expoRuidos']) !== '') ? $array['expoRuidos'] : 'N/A';
$pdf->SetX(10);
$pdf->Cell(190, 5, mb_convert_encoding((string) $expoRuids, 'ISO-8859-1', 'UTF-8'));
$pdf->SetX(10);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(190, 5, "________________________________________________________________________________________________________________________");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->MultiCell(190, 5, mb_convert_encoding("En caso de ototóxicos indicar si ha estado expuesto a monóxido de piridina, dimetilanina de carbono, dinitrobenceno, plomo, mercurio anhídrido, benceno, piridina, hidrocarburos, halógenos, anhídrido carbónico piridina, dimetilanina o por alguna infección se le ha aplicado gentamicina, amikacina o si utiliza audífonos en exposición prolongada:", 'ISO-8859-1', 'UTF-8'));
$expoQuimicos = (isset($array['expoQuimicos']) && $array['expoQuimicos'] !== null && trim((string) $array['expoQuimicos']) !== '') ? $array['expoQuimicos'] : 'N/A';
$pdf->SetX(10);
$pdf->Cell(190, 5, mb_convert_encoding((string) $expoQuimicos, 'ISO-8859-1', 'UTF-8'));
$pdf->SetX(10);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(190, 5, "________________________________________________________________________________________________________________________");
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Problemas de Salud Actual: ");
$pdf->SetFont('Arial', '', 8);
$problemasdesalud = (isset($array['problemasdesalud']) && $array['problemasdesalud'] !== null && trim((string) $array['problemasdesalud']) !== '') ? $array['problemasdesalud'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $problemasdesalud, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(40, 5, "Toma Algun Medicamento:");
$pdf->SetFont('Arial', '', 8);
$tomaMedicamento = (isset($array['tomamedicamento']) && $array['tomamedicamento'] !== null && trim((string) $array['tomamedicamento']) !== '') ? $array['tomamedicamento'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $tomaMedicamento, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(40, 5, "Tratamiento Medico Actual: ");
$pdf->SetFont('Arial', '', 8);
$tratamientoMedico = (isset($array['tratamientomedico']) && $array['tratamientomedico'] !== null && trim((string) $array['tratamientomedico']) !== '') ? $array['tratamientomedico'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $tratamientoMedico, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(50, 5, "Enfermedad Cronica Degenerativa:");
$pdf->SetFont('Arial', '', 8);
$enfermedadCronica = (isset($array['enfermedadcronica']) && $array['enfermedadcronica'] !== null && trim((string) $array['enfermedadcronica']) !== '') ? $array['enfermedadcronica'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding((string) $enfermedadCronica, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();


$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 155);
$pdf->Cell(25, 5, "Tabaquismo");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
$pdf->SetFont('Arial', 'B', 14);
if ($array['tabaquismo'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 20, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 5, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(25, 5, "Alcoholismo");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
$pdf->SetFont('Arial', 'B', 14);
if ($array['alcoholismo'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 20, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 5, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(185, 5, "Antecedentes Personales Patologicos y no Patologicos", 0, 1, 'C');


$pdf->SetX(20,);
$pdf->Cell(145, 5, mb_convert_encoding("Alergias", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

$pdf->SetX(40);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(70, 5, mb_convert_encoding("Ultimos 2 años", 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Alt. Fisicas", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$altfisica = (isset($array['altfisica']) && $array['altfisica'] !== null && trim((string) $array['altfisica']) !== '') ? $array['altfisica'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($altfisica, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Antibioticos", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$antibioticos = (isset($array['antibioticos']) && $array['antibioticos'] !== null && trim((string) $array['antibioticos']) !== '') ? $array['antibioticos'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($antibioticos, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, mb_convert_encoding("Habitos Higienico-Dieteticos", 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', '', 8);
$habhigienicodietetico = (isset($array['habhigienicodietetico']) && $array['habhigienicodietetico'] !== null && trim((string) $array['habhigienicodietetico']) !== '') ? $array['habhigienicodietetico'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($habhigienicodietetico, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Quirurgicos", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$quirurgicos = (isset($array['quirurgicos']) && $array['quirurgicos'] !== null && trim((string) $array['quirurgicos']) !== '') ? $array['quirurgicos'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($quirurgicos, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Analgesicos", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$analgesicos = (isset($array['analgesicos']) && $array['analgesicos'] !== null && trim((string) $array['analgesicos']) !== '') ? $array['analgesicos'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($analgesicos, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Alimentacion", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$alimentacion = (isset($array['alimentacion']) && $array['alimentacion'] !== null && trim((string) $array['alimentacion']) !== '') ? $array['alimentacion'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($alimentacion, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Traumaticos", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$traumaticos = (isset($array['traumaticos']) && $array['traumaticos'] !== null && trim((string) $array['traumaticos']) !== '') ? $array['traumaticos'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($traumaticos, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Antiinflamatorios", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$antiinflamatorios = (isset($array['antiinflamatorios']) && $array['antiinflamatorios'] !== null && trim((string) $array['antiinflamatorios']) !== '') ? $array['antiinflamatorios'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($antiinflamatorios, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Aseo General", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$aseoGeneral = (isset($array['aseogeneral']) && $array['aseogeneral'] !== null && trim((string) $array['aseogeneral']) !== '') ? $array['aseogeneral'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($aseoGeneral, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Transfusiones", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$transfusiones = (isset($array['transfusiones']) && $array['transfusiones'] !== null && trim((string) $array['transfusiones']) !== '') ? $array['transfusiones'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($transfusiones, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Otros", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$otrosAlergias = (isset($array['otrosalergias']) && $array['otrosalergias'] !== null && trim((string) $array['otrosalergias']) !== '') ? $array['otrosalergias'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($otrosAlergias, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("Hobbies", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$hobbies = (isset($array['hobbies']) && $array['hobbies'] !== null && trim((string) $array['hobbies']) !== '') ? $array['hobbies'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($hobbies, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(125);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, mb_convert_encoding("Otras Actividades Laborales", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$otrasActLaborales = (isset($array['otrasactlaborales']) && $array['otrasactlaborales'] !== null && trim((string) $array['otrasactlaborales']) !== '') ? $array['otrasactlaborales'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($otrasActLaborales, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Incapacidades:");
$pdf->SetFont('Arial', '', 8);
$incapacidades = (isset($array['incapacidades']) && $array['incapacidades'] !== null && trim((string) $array['incapacidades']) !== '') ? $array['incapacidades'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($incapacidades, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Diagnostico:");
$pdf->SetFont('Arial', '', 8);
$diagnostico = (isset($array['diagnostico']) && $array['diagnostico'] !== null && trim((string) $array['diagnostico']) !== '') ? $array['diagnostico'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($diagnostico, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, "Dias de Incapacidad:");
$pdf->SetFont('Arial', '', 8);
$diasIncapacidad = (isset($array['diasIncapacidad']) && $array['diasIncapacidad'] !== null && trim((string) $array['diasIncapacidad']) !== '') ? $array['diasIncapacidad'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($diasIncapacidad, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Secuelas:");
$pdf->SetFont('Arial', '', 8);
$secuela = (isset($array['secuela']) && $array['secuela'] !== null && trim((string) $array['secuela']) !== '') ? $array['secuela'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($secuela, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, mb_convert_encoding("Rehabilitación:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$rehabilitacion = (isset($array['rehabilitacion']) && $array['rehabilitacion'] !== null && trim((string) $array['rehabilitacion']) !== '') ? $array['rehabilitacion'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($rehabilitacion, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, "Trayecto                                  (    )", 1);
if ($array['trayecto'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4.6, $pdf->GetY() + 1, 3);
} else {
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, "Enfermedad General              (    )", 1);
$pdf->SetFont('Arial', 'B', 10);
if ($array['enfgeneral'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
} else {
}


$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, "Accidente de Trabajo             (    )", 1);
$pdf->SetFont('Arial', 'B', 10);
if ($array['accidentetrabajo'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4.4, $pdf->GetY() + 1, 3);
} else {
}


$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, "Enfermedad de Trabajo         (    )", 1);
$pdf->SetFont('Arial', 'B', 10);
if ($array['enfermedadtrabajo'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4.7, $pdf->GetY() + 1, 3);
} else {
}
$pdf->Ln();


$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(150, 10, "Interrogatorio por Aparatos y Sistemas", 0, 1, 'C');
$pdf->Ln(1);

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Cardio -Repiratorio");

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(85);
$pdf->Cell(30, 5, "Disnea");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
$pdf->SetFont('Arial', 'B', 14);
if ($array['disnea'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(40, 5, "Tos");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
$pdf->SetFont('Arial', 'B', 14);
if ($array['Tos'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(85);
$pdf->Cell(35, 5, "Cianosis");
$pdf->SetFont('Arial', '', 8);
$pdf->SetX(115);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
$pdf->SetFont('Arial', 'B', 14);
if ($array['cianosis'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(40, 5, "Expectoracion");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['expectoracion'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->SetX(85);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Edema/Insuf. Venoso");
$pdf->SetFont('Arial', '', 8);
$pdf->SetX(115);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['edema'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(40, 5, "Dolor Toracico");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['dolortoracico'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Comentarios:");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(40, 5, "Taquicardia");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['taquicardia'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();


$pdf->SetXY(20, 265);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 5, "FORM-56674");
$pdf->Cell(30, 5, "Revision: 0");
$pdf->Cell(55, 5, mb_convert_encoding("Exámen Medico Ingreso", 'ISO-8859-1', 'UTF-8'));
$pdf->Cell(30, 5, "Fecha Efectiva:");
$pdf->Cell(45, 5, "$fecha");
$pdf->Ln();

$pdf->Cell(150, 5, "ESTE DOCUMENTO CONTIENE INFORMACION CONFIDENCIAL DE KIMBERLY-CLARK", 0, 1, 'C');
$pdf->SetX(80);
$pdf->Cell(30, 1, "Page 1 of 3", 0, 1, 'C');
$pdf->Ln();

// Pagina 2
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(50, 30);
$pdf->Cell(40, 5, "Digestivo");
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(35, 5, "Dolor Abdominal");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['dolorabdominal'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Abdomen");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['abdomen'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(35, 5, "Transito Intestinal");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['transintestinal'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Hernias");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['hernia'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(35, 5, mb_convert_encoding("Excretas por Día", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$excretaxdia = (isset($array['excretaxdia']) && $array['excretaxdia'] !== null && trim((string) $array['excretaxdia']) !== '') ? $array['excretaxdia'] : 'N/A';
$pdf->Cell(30, 5, mb_convert_encoding($excretaxdia, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Comentarios:");
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(10);
$pdf->Cell(35, 5, mb_convert_encoding("Orofaringeo", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(15, 5, "Si", 1);
$pdf->Cell(15, 5, "No", 1);
if ($array['orofaringeo'] == 0) {
    $pdf->Image("imagen.png", $pdf->GetX() - 19, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(45, 5, "Observaciones:");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(20);
$pdf->SetFont('Arial', 'B', 8);
$pdf->MultiCell(90, 5, mb_convert_encoding("Exploracion Física", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->Ln(1);

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "Peso:");
$pdf->SetFont('Arial', '', 8);
$peso = (isset($array['peso']) && $array['peso'] !== null && trim((string) $array['peso']) !== '') ? $array['peso'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($peso, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "Talla:");
$pdf->SetFont('Arial', '', 8);
$talla = (isset($array['talla']) && $array['talla'] !== null && trim((string) $array['talla']) !== '') ? $array['talla'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($talla, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "IMC:");
$pdf->SetFont('Arial', '', 8);
$imc = (isset($array['imc']) && $array['imc'] !== null && trim((string) $array['imc']) !== '') ? $array['imc'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($imc, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "FC:");
$pdf->SetFont('Arial', '', 8);
$fc = (isset($array['fc']) && $array['fc'] !== null && trim((string) $array['fc']) !== '') ? $array['fc'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($fc, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "FR:");
$pdf->SetFont('Arial', '', 8);
$fr = (isset($array['fr']) && $array['fr'] !== null && trim((string) $array['fr']) !== '') ? $array['fr'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($fr, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "T.A.:");
$pdf->SetFont('Arial', '', 8);
$ta = (isset($array['ta']) && $array['ta'] !== null && trim((string) $array['ta']) !== '') ? $array['ta'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($ta, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(35);
$pdf->MultiCell(90, 5, mb_convert_encoding("Agudeza Visual", 'ISO-8859-1', 'UTF-8'));
$pdf->Ln(1);

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Ojo Der.:");
$pdf->SetFont('Arial', '', 8);
$ojoDer = (isset($array['ojoder']) && $array['ojoder'] !== null && trim((string) $array['ojoder']) !== '') ? $array['ojoder'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($ojoDer, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Ojo Izq.:");
$pdf->SetFont('Arial', '', 8);
$ojoIzq = (isset($array['ojoizq']) && $array['ojoizq'] !== null && trim((string) $array['ojoizq']) !== '') ? $array['ojoizq'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($ojoIzq, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Bilateral:");
$pdf->SetFont('Arial', '', 8);
$bilateral = (isset($array['bilateral']) && $array['bilateral'] !== null && trim((string) $array['bilateral']) !== '') ? $array['bilateral'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($bilateral, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Pupila:");
$pdf->SetFont('Arial', '', 8);
$ta = (isset($array['ta']) && $array['ta'] !== null && trim((string) $array['ta']) !== '') ? $array['ta'] : 'N/A';
$pdf->Cell(15, 5, mb_convert_encoding($ta, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(100);
$pdf->SetX(35);
$pdf->Cell(80, 5, "Nervioso");
$pdf->Cell(55, 5, "Organos de Los Sentidos");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Estado de Conciencia:");
$pdf->SetFont('Arial', '', 8);
$conciencia = (isset($array['conciencia']) && $array['conciencia'] !== null && trim((string) $array['conciencia']) !== '') ? $array['conciencia'] : 'N/A';
$pdf->Cell(35, 5, mb_convert_encoding($conciencia, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Audicion");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "Normal", 1);
$pdf->Cell(25, 5, "Anormal", 1);
if ($array['edema'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 29, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Sensibilidad:");
$pdf->SetFont('Arial', '', 8);
$sensible = (isset($array['sensible']) && $array['sensible'] !== null && trim((string) $array['sensible']) !== '') ? $array['sensible'] : 'N/A';
$pdf->Cell(35, 5, mb_convert_encoding($sensible, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Agilidad Visual");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "Normal", 1);
$pdf->Cell(25, 5, "Anormal", 1);
if ($array['agilidadvisual'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 29, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, mb_convert_encoding("Sueño:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$sueno = (isset($array['sueno']) && $array['sueno'] !== null && trim((string) $array['sueno']) !== '') ? $array['sueno'] : 'N/A';
$pdf->Cell(35, 5, mb_convert_encoding($sueno, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Reflejos");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "Normal", 1);
$pdf->Cell(25, 5, "Anormal", 1);
if ($array['reflejos'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 29, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Reflejos:");
$pdf->SetFont('Arial', '', 8);
$reflejo = (isset($array['reflejo']) && $array['reflejo'] !== null && trim((string) $array['reflejo']) !== '') ? $array['reflejo'] : 'N/A';
$pdf->Cell(35, 5, mb_convert_encoding($reflejo, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Campimetria");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "Normal", 1);
$pdf->Cell(25, 5, "Anormal", 1);
if ($array['campimetria'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 29, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(70, 5, "Observaciones:");
$pdf->Cell(25, 5, "Olfato");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "Normal", 1);
$pdf->Cell(25, 5, "Anormal", 1);
if ($array['campimetria'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 29, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(80);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Tacto");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "Normal", 1);
$pdf->Cell(25, 5, "Anormal", 1);
if ($array['tacto'] == 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 4, $pdf->GetY() + 1, 3);
} else {
    $pdf->Image("imagen.png", $pdf->GetX() - 29, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(35);
$pdf->MultiCell(100, 7, mb_convert_encoding("Pruebas Especiales Equilibrio y Propiocepción NOM-036-1-STPS-2018", 'ISO-8859-1', 'UTF-8'));
$pdf->Ln(1);

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Romberg:");
$pdf->SetFont('Arial', '', 8);
$cardiopulmonar = (isset($array['cardiopulmonar']) && $array['cardiopulmonar'] !== null && trim((string) $array['cardiopulmonar']) !== '') ? $array['cardiopulmonar'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($cardiopulmonar, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Babinski:");
$pdf->SetFont('Arial', '', 8);
$tecnicarte = (isset($array['tecnicarte']) && $array['tecnicarte'] !== null && trim((string) $array['tecnicarte']) !== '') ? $array['tecnicarte'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($tecnicarte, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Otocerosis:");
$pdf->SetFont('Arial', '', 8);
$octocerosis = (isset($array['octocerosis']) && $array['octocerosis'] !== null && trim((string) $array['octocerosis']) !== '') ? $array['octocerosis'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($octocerosis, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Timpano:");
$pdf->SetFont('Arial', '', 8);
$timpano = (isset($array['timpano']) && $array['timpano'] !== null && trim((string) $array['timpano']) !== '') ? $array['timpano'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($timpano, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(100);
$pdf->SetX(35);
$pdf->Cell(80, 5, "Cardio Pulmonar");
$pdf->Cell(55, 5, "Digestivos");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Tencion Arterial:");
$pdf->SetFont('Arial', '', 8);
$tecnicarte2 = (isset($array['tecnicarte2']) && $array['tecnicarte2'] !== null && trim((string) $array['tecnicarte2']) !== '') ? $array['tecnicarte2'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($tecnicarte2, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Peristalsis:");
$pdf->SetFont('Arial', '', 8);
$peristalsis = (isset($array['peristalsis']) && $array['peristalsis'] !== null && trim((string) $array['peristalsis']) !== '') ? $array['peristalsis'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($peristalsis, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Frec. Cardiaca:");
$pdf->SetFont('Arial', '', 8);
$frecardiaca = (isset($array['freccardiaca']) && $array['freccardiaca'] !== null && trim((string) $array['freccardiaca']) !== '') ? $array['freccardiaca'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($frecardiaca, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Dolor:");
$pdf->SetFont('Arial', '', 8);
$dolor = (isset($array['dolor']) && $array['dolor'] !== null && trim((string) $array['dolor']) !== '') ? $array['dolor'] : 'N/A';
$pdf->Cell(20, 5, mb_convert_encoding($dolor, 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Vias Resp. Sip.:");
$pdf->SetFont('Arial', '', 8);
$viasrespi = (isset($array['viasrespi']) && $array['viasrespi'] !== null && trim((string) $array['viasrespi']) !== '') ? $array['viasrespi'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($viasrespi, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Organomegalias:");
$pdf->SetFont('Arial', '', 8);
$organomegalias = (isset($array['organomegalias']) && $array['organomegalias'] !== null && trim((string) $array['organomegalias']) !== '') ? $array['organomegalias'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($organomegalias, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Campos Pulmonares:");
$pdf->SetFont('Arial', '', 8);
$camppulmonar = (isset($array['camppulmonar']) && $array['camppulmonar'] !== null && trim((string) $array['camppulmonar']) !== '') ? $array['camppulmonar'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($camppulmonar, 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Hernia Umbilical:");
$pdf->SetFont('Arial', '', 8);
$herniaumbilical = (isset($array['herniaumbilical']) && $array['herniaumbilical'] !== null && trim((string) $array['herniaumbilical']) !== '') ? $array['herniaumbilical'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($herniaumbilical, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(90, 5, "Observaciones:");
$pdf->Ln();


$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(100);
$pdf->SetX(35);
$pdf->Cell(65, 5, "Musculo Esqueletico");
$pdf->Cell(55, 5, "Pruebas Especiales Osteomuscularar NOM-006-STPS-2014");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Cuello:");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Normal", 1);
$pdf->Cell(20, 5, "Anormal", 1);
if ($array['cuello'] != 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 24, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
}
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Lasage:");
$pdf->SetFont('Arial', '', 8);
$lasage = (isset($array['lasage']) && $array['lasage'] !== null && trim((string) $array['lasage']) !== '') ? $array['lasage'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($lasage, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Columna Vertebral:");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Normal", 1);
$pdf->Cell(20, 5, "Anormal", 1);
if ($array['columnavertebral'] !== 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 24, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Bragard:");
$pdf->SetFont('Arial', '', 8);
$bragard = (isset($array['bragard']) && $array['bragard'] !== null && trim((string) $array['bragard']) !== '') ? $array['bragard'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($bragard, 'ISO-8859-1', 'UTF-8'));

$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Movilidad M.T M.P:");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Normal", 1);
$pdf->Cell(20, 5, "Anormal", 1);
if ($array['movilidad'] !== 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 24, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Tinel:");
$pdf->SetFont('Arial', '', 8);
$tinel = (isset($array['tinel']) && $array['tinel'] !== null && trim((string) $array['tinel']) !== '') ? $array['tinel'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($tinel, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Marcha:");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Normal", 1);
$pdf->Cell(20, 5, "Anormal", 1);
if ($array['marcha'] !== 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 24, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Phalen:");
$pdf->SetFont('Arial', '', 8);
$phalen = (isset($array['phalen']) && $array['phalen'] !== null && trim((string) $array['phalen']) !== '') ? $array['phalen'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($phalen, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "R.O.T.S:");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Normal", 1);
$pdf->Cell(20, 5, "Anormal", 1);
if ($array['rots'] !== 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 24, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "Trendelemburg:");
$pdf->SetFont('Arial', '', 8);
$trendelemburg = (isset($array['trendelemburg']) && $array['trendelemburg'] !== null && trim((string) $array['trendelemburg']) !== '') ? $array['trendelemburg'] : 'N/A';
$pdf->Cell(45, 5, mb_convert_encoding($trendelemburg, 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, "Punto de Riesgo Lumbar:");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Normal", 1);
$pdf->Cell(20, 5, "Anormal", 1);
if ($array['puntorlumbar'] !== 1) {
    $pdf->Image("imagen.png", $pdf->GetX() - 24, $pdf->GetY() + 1, 3);
} else {

    $pdf->Image("imagen.png", $pdf->GetX() - 4.5, $pdf->GetY() + 1, 3);
}
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(90, 5, "Observaciones:");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(100);
$pdf->Cell(55, 5, "Espirometria");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "Normal.:");
$pdf->SetFont('Arial', '', 8);
$espnormal = (isset($array['espnormal']) && $array['espnormal'] !== null && trim((string) $array['espnormal']) !== '') ? $array['espnormal'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($espnormal, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Obstructivo.:");
$pdf->SetFont('Arial', '', 8);
$espobstructivo = (isset($array['espobstructivo']) && $array['espobstructivo'] !== null && trim((string) $array['espobstructivo']) !== '') ? $array['espobstructivo'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($espobstructivo, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Restrictivo:");
$pdf->SetFont('Arial', '', 8);
$esprestrictivo = (isset($array['esprestrictivo']) && $array['esprestrictivo'] !== null && trim((string) $array['esprestrictivo']) !== '') ? $array['esprestrictivo'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($esprestrictivo, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 5, "Mixto:");
$pdf->SetFont('Arial', '', 8);
$espmixto = (isset($array['espmixto']) && $array['espmixto'] !== null && trim((string) $array['espmixto']) !== '') ? $array['espmixto'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($espmixto, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetX(85);
$pdf->Cell(40, 10, "Audiometria NOM-011-STPS-2001");
$pdf->Ln();

$pdf->SetFont('Arial', '', 8);
$pdf->SetX(10);
$pdf->SetX(30);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 5, "500", 1, 0, 'R');
$pdf->Cell(25, 5, "1000", 1, 0, 'R');
$pdf->Cell(25, 5, "2000", 1, 0, 'R');
$pdf->Cell(25, 5, "3000", 1, 0, 'R');
$pdf->Cell(25, 5, "4000", 1, 0, 'R');
$pdf->Cell(25, 5, "6000", 1, 0, 'R');
$pdf->Cell(25, 5, "8000", 1, 0, 'R');
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Derecho:", 1);
$pdf->SetX(30);
$pdf->SetFont('Arial', '', 8);
$d1 = (isset($array['d1']) && $array['d1'] !== null && trim((string) $array['d1']) !== '') ? $array['d1'] : 'N/A';
$d2 = (isset($array['d2']) && $array['d2'] !== null && trim((string) $array['d2']) !== '') ? $array['d2'] : 'N/A';
$d3 = (isset($array['d3']) && $array['d3'] !== null && trim((string) $array['d3']) !== '') ? $array['d3'] : 'N/A';
$d4 = (isset($array['d4']) && $array['d4'] !== null && trim((string) $array['d4']) !== '') ? $array['d4'] : 'N/A';
$d5 = (isset($array['d5']) && $array['d5'] !== null && trim((string) $array['d5']) !== '') ? $array['d5'] : 'N/A';
$d6 = (isset($array['d6']) && $array['d6'] !== null && trim((string) $array['d6']) !== '') ? $array['d6'] : 'N/A';
$d7 = (isset($array['d7']) && $array['d7'] !== null && trim((string) $array['d7']) !== '') ? $array['d7'] : 'N/A';

$pdf->Cell(25, 5, mb_convert_encoding($d1, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($d2, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($d3, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($d4, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($d5, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($d6, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($d7, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 5, "Izquierdo:", 1);
$pdf->SetX(30);
$pdf->SetFont('Arial', '', 8);
$i1 = (isset($array['i1']) && $array['i1'] !== null && trim((string) $array['i1']) !== '') ? $array['i1'] : 'N/A';
$i2 = (isset($array['i1']) && $array['i1'] !== null && trim((string) $array['i1']) !== '') ? $array['i1'] : 'N/A';
$i3 = (isset($array['i3']) && $array['i3'] !== null && trim((string) $array['i3']) !== '') ? $array['i3'] : 'N/A';
$i4 = (isset($array['i4']) && $array['i4'] !== null && trim((string) $array['i4']) !== '') ? $array['i4'] : 'N/A';
$i5 = (isset($array['i5']) && $array['i5'] !== null && trim((string) $array['i5']) !== '') ? $array['i5'] : 'N/A';
$i6 = (isset($array['i6']) && $array['i6'] !== null && trim((string) $array['i6']) !== '') ? $array['i6'] : 'N/A';
$i7 = (isset($array['i7']) && $array['i7'] !== null && trim((string) $array['i7']) !== '') ? $array['i7'] : 'N/A';

$pdf->Cell(25, 5, mb_convert_encoding($i1, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($i2, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($i3, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($i4, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($i5, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($i6, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Cell(25, 5, mb_convert_encoding($i7, 'ISO-8859-1', 'UTF-8'), 1, 0, 'R');
$pdf->Ln();

$pdf->SetY(265);
$pdf->Cell(30, 5, "FORM-56674");
$pdf->Cell(30, 5, "Revision: 0");
$pdf->Cell(55, 5, mb_convert_encoding("Exámen Medico Ingreso", 'ISO-8859-1', 'UTF-8'));
$pdf->Cell(30, 5, "Fecha Efectiva:");
$pdf->Cell(45, 5, "$fecha");
$pdf->Ln();

$pdf->Cell(150, 5, "ESTE DOCUMENTO CONTIENE INFORMACION CONFIDENCIAL DE KIMBERLY-CLARK", 0, 1, 'C');
$pdf->SetX(80);
$pdf->Cell(30, 1, "Page 1 of 3", 0, 1, 'C');
$pdf->Ln();

// Pagina 3
$pdf->AddPage();

$pdf->SetXY(100, 30);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(65, 7, "Diagnostico");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln(3);


$pdf->SetXY(10, 40);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Sano:");
$pdf->SetFont('Arial', '', 8);
$diagnistivosano = (isset($array['diagnostivosano']) && $array['diagnostivosano'] !== null && trim((string) $array['diagnostivosano']) !== '') ? $array['diagnostivosano'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($diagnistivosano, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Conductiva:");
$pdf->SetFont('Arial', '', 8);
$conductiva = (isset($array['conductiva']) && $array['conductiva'] !== null && trim((string) $array['conductiva']) !== '') ? $array['conductiva'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($conductiva, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, mb_convert_encoding("Trauma Acústico Crónico:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$traumadegenerativo = (isset($array['traumadegenerativo']) && $array['traumadegenerativo'] !== null && trim((string) $array['traumadegenerativo']) !== '') ? $array['traumadegenerativo'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($traumadegenerativo, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, mb_convert_encoding("Calculo de Hipoacusia:", 'ISO-8859-1', 'UTF-8'));
$pdf->Ln();

$pdf->SetX(10);
$pdf->Cell(35, 5, "Sensorial:");
$pdf->SetFont('Arial', '', 8);
$sensorial = (isset($array['sensorial']) && $array['sensorial'] !== null && trim((string) $array['sensorial']) !== '') ? $array['sensorial'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($sensorial, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, mb_convert_encoding("Degenerativo:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$degenerativo = (isset($array['degenerativo']) && $array['degenerativo'] !== null && trim((string) $array['degenerativo']) !== '') ? $array['degenerativo'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($degenerativo, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetX(10);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 5, mb_convert_encoding("Mixto:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->SetX(45);
$mixma = (isset($array['mixma']) && $array['mixma'] !== null && trim((string) $array['mixma']) !== '') ? $array['mixma'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($mixma, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, "HBC:");
$pdf->SetFont('Arial', '', 8);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, mb_convert_encoding("Otros:", 'ISO-8859-1', 'UTF-8'));
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, "", 1);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Bilateral:");
$pdf->SetFont('Arial', '', 8);
$bilateralstp = (isset($array['bilateralstp']) && $array['bilateralstp'] !== null && trim((string) $array['bilateralstp']) !== '') ? $array['bilateralstp'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($bilateralstp, 'ISO-8859-1', 'UTF-8'), 1);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(30, 5, mb_convert_encoding("No Valorable por:", 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, "Otocerosis (Cerumen Impactado)");
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, mb_convert_encoding($array['otocerosis'], 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Superficial:");

$pdf->SetFont('Arial', '', 8);
$superficial = (isset($array['superficial']) && $array['superficial'] !== null && trim((string) $array['superficial']) !== '') ? $array['superficial'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($superficial, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Cell(30, 5, "");
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, "Otoesclerosis");
$pdf->SetFont('Arial', '', 8);
$otocerosis = (isset($array['otocerosis']) && $array['otocerosis'] !== null && trim((string) $array['otocerosis']) !== '') ? $array['otocerosis'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($otocerosis, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Moderada:");
$pdf->SetFont('Arial', '', 8);
$moderada = (isset($array['moderada']) && $array['moderada'] !== null && trim((string) $array['moderada']) !== '') ? $array['moderada'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($moderada, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Cell(30, 5, "");

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, "Infeccion Faringea");
$pdf->SetFont('Arial', '', 8);
$infeccionfaringea = (isset($array['infeccionfaringea']) && $array['infeccionfaringea'] !== null && trim((string) $array['infeccionfaringea']) !== '') ? $array['infeccionfaringea'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($infeccionfaringea, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln();

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, "Profunda:");
$pdf->SetFont('Arial', '', 8);
$profunda = (isset($array['profunda']) && $array['profunda'] !== null && trim((string) $array['profunda']) !== '') ? $array['profunda'] : 'N/A';
$pdf->Cell(25, 5, mb_convert_encoding($profunda, 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Cell(30, 5, "");

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(50, 5, mb_convert_encoding("Perforacion Timpánica", 'ISO-8859-1', 'UTF-8'));

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(25, 5, mb_convert_encoding($array['perforanciatimpanica'], 'ISO-8859-1', 'UTF-8'), 1);
$pdf->Ln(6);

$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->MultiCell(100, 5, "Resultado del Examen, Diagnostico y Recomendaciones:");
$pdf->Ln(6);

$pdf->SetX(105);
$pdf->Cell(40, 5, "Declaro que los datos son veridicos");
$pdf->Ln();

$pdf->SetX(105);
$pdf->Cell(40, 5, "Se le Informa el Resultado de Su Examen");
$pdf->Ln();

$pdf->SetX(35);
$pdf->Cell(50, 5, "Nombre del Medico");
$medico = (isset($array['medico']) && $array['medico'] !== null && trim((string) $array['medico']) !== '') ? $array['medico'] : 'N/A';
$pdf->SetXY(22, 130);
$pdf->Cell(50, 5, mb_convert_encoding((string) $medico, 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY(18, 132);
$pdf->Cell(60, 5, '__________________________________________');
$pdf->SetXY(105, 107);
$pdf->Cell(40, 5, "Firma y Nombre de Enterado Del Trabajador");
$pdf->Image($imagenPath, 115, 115, 35, 20, 'PNG');
$pdf->SetXY(107, 130);
$pdf->Cell(50, 5, mb_convert_encoding((string) $nombreemp, 'ISO-8859-1', 'UTF-8'));
$pdf->SetXY(104, 132);
$pdf->Cell(60, 5, '__________________________________________');
$pdf->Ln(7);

$pdf->SetXY(35, 265);
$pdf->Cell(30, 5, "FORM-56674");
$pdf->Cell(30, 5, "Revision: 0");
$pdf->Cell(55, 5, mb_convert_encoding("Exámen Medico Ingreso", 'ISO-8859-1', 'UTF-8'));
$pdf->Cell(30, 5, "Fecha Efectiva:");
$pdf->Cell(45, 5, "$fecha");
$pdf->Ln();

$pdf->Cell(150, 5, "ESTE DOCUMENTO CONTIENE INFORMACION CONFIDENCIAL DE KIMBERLY-CLARK", 0, 1, 'C');
$pdf->SetX(80);
$pdf->Cell(30, 1, "Page 1 of 3", 0, 1, 'C');
$pdf->Ln();

$pdf->Output();
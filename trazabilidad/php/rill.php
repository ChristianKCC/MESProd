<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class Rill
{
    function tblRillReporte()
    {

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $idmaquina = $_POST['idmaquina'];
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $query = "SELECT tblRillEnc.*,tblValeEMateriales.NombreMaterial,tblempleados.Nombre as empleadonombre,tblValeEEnc.foliocons as foliocons,
        tblMaquinas.NombreMaquina FROM tblRillEnc
            LEFT JOIN tblValeEEnc ON tblValeEEnc.id = tblRillEnc.foliovalesril
            LEFT JOIN tblValeEMateriales On tblValeEMateriales.NoMaterial = tblRillEnc.material
            INNER JOIN TLX032MXDB.dbo.tblempleados On tblEmpleados.NoEmp = tblRillEnc.noemp 
			INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblRillEnc.sessionmaquina
            WHERE (tblValeEEnc.maquina = $idmaquina OR tblRillEnc.sessionmaquina =  $idmaquina) AND tblRillEnc.fechasave >= '$fechai' AND tblRillEnc.fechasave < DATEADD(day,1,'$fechaf') ORDER BY tblRillEnc.id DESC";
        $array = array();
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row[0], "clave" => $row[1], "clase" => $row[2], "material" => $row[3], "noemp" => $row[4], "lote" => $row[5], "foliovalesril" => $row[6],
                "horaril" => $row[7]->format('H:i:s'), "fecha" => $row[8]->format('Y-m-d H:i:s'), "materialnombre" => $row['NombreMaterial'], "empleadonombre" => $row['empleadonombre'], "materialprueba" => $row['materialprueba'], "foliovalemanual" => $row['foliomanual'], "foliovalecons" => $row['foliocons'], "foliovaleconsmaq" => $row['NombreMaquina'] . ' - ' . $row['foliocons']
            ]);
        }
        echo json_encode($array);
    }
}

if (isset($_GET["tblRillReporte"])) {
    $Rill = new Rill();
    $Rill->tblRillReporte();
}

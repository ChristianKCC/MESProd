<?php
require_once "../../conexion.php";
require_once "../../Session/seguridad.php";
class Inspeccion
{
    function saveInspeccion()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $data = json_decode(file_get_contents("php://input"), true);
        $params = [
            $data['noempinsp'],
            $data['inspecciontipo'],
            $data['seccionpreusos'],
            $data['inpeccionfecha'],
            $data['inpeccioncomentarios'],
            $data['folio']
        ];
        $query = "INSERT INTO tblBitInspecciones (noemp,tipoinsp,seccion,fecha,comentarios,folio) OUTPUT INSERTED.id VALUES (?,?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, $params);
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        $lastId = $row['id'] ?? null;

        foreach ($data['inpecciondesc'] as $item) {
            $idItem = $item['id'];
            $valorSeleccionado = $item['valor'];
            $sqlCheck = "INSERT INTO tblBitInspeccionesChecklist (idenc ,itemcheck, idcheck) VALUES (?, ?, ?)";
            sqlsrv_query($conn, $sqlCheck, [$lastId, $valorSeleccionado, $idItem]);
        }
        $result == false ? http_response_code(500) : http_response_code(200);
    }
    function tblInspeccion()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_GET['folio'];
        $query = "SELECT tblBitInspecciones.id,tblBitInspecciones.fechasave,tblEmpleados.NoEmp,tblEmpleados.Nombre,tblEncabezadoBitacora.Turno,
        tblInspeccionTipo.tipoinspeccion,tblBitPreusosSecciones.seccionpre,tblBitInspecciones.comentarios FROM tblBitInspecciones 
        INNER JOIN tblInspeccionTipo On tblInspeccionTipo.id = tblBitInspecciones.tipoinsp
        INNER JOIN tblBitPreusosSecciones ON tblBitPreusosSecciones.id = tblBitInspecciones.seccion
        INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora=tblBitInspecciones.folio
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblBitInspecciones.noemp";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row["id"],
                "fechasave" => $row["fechasave"]->format('Y-m-d H:i:s'),
                "NoEmp" => $row["NoEmp"],
                "Nombre" => $row["Nombre"],
                "Turno" => $row["Turno"],
                "tipoinspeccion" => $row["tipoinspeccion"],
                "seccionpre" => $row["seccionpre"],
                "comentarios" => $row["comentarios"]
            ]);
        }
        echo json_encode($array);
    }
}

if (isset($_GET['saveInspeccion'])) {
    $inspeccionobj = new Inspeccion();
    $inspeccionobj->saveInspeccion();
} else if (isset($_GET['tblInspeccion'])) {
    $inspeccionobj = new Inspeccion();
    $inspeccionobj->tblInspeccion();
}

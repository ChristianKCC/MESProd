<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class BitacoraElectronica
{
    function ConsultaHorasxturno($turno)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT * FROM tblBitTurnohoras WHERE turno = ?";
        $result = sqlsrv_query($conn, $query, array($turno));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['id' => $row['id'], 'turno' => $row['turno'], 'hora' => $row['hora'], 'horastr' => $row['horastr']]);
        }
        return $array;
    }
    function InsertHoraxturno($idpresentacionenc, $hora, $foliobit)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "INSERT INTO tblBitPresentacionSub(idpresentacionenc,hora,real,golpes,mermaempaque,mermacalidad) VALUES (?, ?, ?, ?, ?, ?) ";
        sqlsrv_query($conn, $query, array($idpresentacionenc, $hora, 0, 0, 0, 0));
        $sql = "SELECT COUNT(*) as count FROM tblBitPresentacionGolpes WHERE idbitacora = ? AND hora = ?";
        $params = array($foliobit, $hora);
        $stmt = sqlsrv_query($conn, $sql, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row['count'] == 0) {
            $query = "INSERT INTO tblBitPresentacionGolpes(idbitacora,hora,golpes,merma) VALUES (?, ?, ?, ?) ";
            sqlsrv_query($conn, $query, array($foliobit, $hora, 0, 0));
        }
    }
    function InsertHoraxturnoTelas($idpresentacionenc, $hora, $foliobit)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "INSERT INTO tblBitPresentacionSubTelas(idpresentacionenc,hora,rollos,mml,acum) VALUES (?, ?, ?, ?, ?) ";
        sqlsrv_query($conn, $query, array($idpresentacionenc, $hora, 0, 0, 0));
    }
    function InsertarDataTelas2()
    {
        if (!isset($_POST["id"])) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "msg" => "No se recibió el parámetro id",
                "post" => $_POST
            ]);
            return;
        }

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $idPT = $_POST["id"];

        $query = "INSERT INTO tblMXPRProduccionTNTSub
      (idPTEncabezado, MetrosLineales, MMCuadrados, PesoTotal, ACCMMCuadrados, ACCKG)
      VALUES (?, ?, ?, ?, ?, ?)";

        $params = array(
            $idPT,
            0,
            0,
            0,
            0,
            0
        );

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "errors" => sqlsrv_errors()
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode(["status" => "ok"]);



    }

    function saveGolpes()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_POST["id"];
        $golpes = $_POST["golpes"];
        $merma = $_POST["merma"];
        $query = "UPDATE tblBitPresentacionGolpes SET golpes = ?, merma = ? WHERE id = ?";
        $result = sqlsrv_query($conn, $query, array($golpes, $merma, $id));
        if ($result === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
                    echo "code: " . $error['code'] . "<br />";
                    echo "message: " . $error['message'] . "<br />";
                }
            }
            http_response_code(500);
        } else {
            http_response_code(200);
        }
    }
    function savePresentacion()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST["folio"];
        $presentacion = $_POST["presentacion"];
        $turno = $_POST["turnoen"];
        $notbl = $_POST["notbl"];
        $array = array();
        $query = "INSERT INTO tblBitPresentacionEnc (folio,presentacion, notbl) VALUES (?,?,?)";
        $result = sqlsrv_query($conn, $query, array($folio, $presentacion, $notbl));
        if ($result === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
                    echo "code: " . $error['code'] . "<br />";
                    echo "message: " . $error['message'] . "<br />";
                }
            }
            http_response_code(500);
        } else {
            $sql = "SELECT @@IDENTITY AS last_id";
            $stmt = sqlsrv_query($conn, $sql);
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $last_id = $row['last_id'];
            $array = $this->ConsultaHorasxturno($turno);
            foreach ($array as $horas) {
                $this->InsertHoraxturno($last_id, $horas['id'], $folio);
            }
            http_response_code(200);
        }
    }
    function savePresentaciontelas()
{
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX004MXDB");
    $folio = $_POST["folio"];
    $presentacion = $_POST["presentacion"];
    $notbl = $_POST["notbl"];

    $query = "INSERT INTO tblMXPRProduccionTNTEnc (folio, Clave, NoTabla) 
              OUTPUT INSERTED.idPT
              VALUES (?, ?, ?)";

    $result = sqlsrv_query($conn, $query, array($folio, $presentacion, $notbl));

    if ($result === false) {
        http_response_code(500);
        echo json_encode(sqlsrv_errors());
    } else {
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        
        // Verifica qué trae $row
        error_log("ROW completo: " . print_r($row, true));
        error_log("last_id: " . $row['idPT']);
        
        $last_id = $row['idPT'];
        $this->InsertarDataTelas($last_id);
        http_response_code(200);
    }
}

function InsertarDataTelas($idpresentacionenc)
{
    // Verifica que llegue el ID
    error_log("InsertarDataTelas recibió id: " . $idpresentacionenc);

    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX004MXDB");

    $query = "INSERT INTO tblMXPRProduccionTNTSub(idPTEncabezado, MetrosLineales, MMCuadrados, PesoTotal, ACCMMCuadrados, ACCKG, ACCMMLineales) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $result = sqlsrv_query($conn, $query, array($idpresentacionenc, 0, 0, 0, 0, 0, 0));

    if ($result === false) {
        error_log("Error InsertarDataTelas: " . print_r(sqlsrv_errors(), true));
    } else {
        error_log("InsertarDataTelas insertó correctamente");
    }
}

    function DeletePresentacionSub($id)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "DELETE FROM tblBitPresentacionSub WHERE idpresentacionenc = ?";
        $result = sqlsrv_query($conn, $query, array($id));
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function DeletePresentacionSubTelas($id)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $query = "DELETE FROM tblMXPRProduccionTNTSub WHERE idPTEncabezado = ?";
        $result = sqlsrv_query($conn, $query, array($id));
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function DeletePresentacion()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST["folio"];
        $notbl = $_POST["notbl"];
        $idpresentacion = 0;
        $query = "DELETE FROM tblBitPresentacionEnc OUTPUT DELETED.* WHERE folio = ? AND notbl = ?";
        $result = sqlsrv_query($conn, $query, array($folio, $notbl));
        while ($row = sqlsrv_fetch_array($result)) {
            $idpresentacion = $row['idpresentacionenc'];
        }
        $result === false ? http_response_code(500) : $this->DeletePresentacionSub($idpresentacion);
    }
    function DeletePresentacionTelas()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $folio = $_POST["folio"];
        $notbl = $_POST["notbl"];
        $idpresentacion = 0;
        $query = "DELETE FROM tblMXPRProduccionTNTEnc OUTPUT DELETED.* WHERE folio = ? AND NoTabla = ?";
        $result = sqlsrv_query($conn, $query, array($folio, $notbl));
        while ($row = sqlsrv_fetch_array($result)) {
            $idpresentacion = $row['idPT'];
        }
        $result === false ? http_response_code(500) : $this->DeletePresentacionSubTelas($idpresentacion);
    }
    function tblPresentacionGolpes()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST['folio'];
        $query = "SELECT * FROM tblBitPresentacionGolpes WHERE idbitacora = ? ";
        $result = sqlsrv_query($conn, $query, array($folio));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'idbitacora' => $row['idbitacora'],
                'hora' => $row['hora'],
                'golpes' => $row['golpes'],
                'merma' => $row['merma']
            ]);
        }
        echo json_encode($array);
    }
    function tblPresentacionSub()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST['folio'];
        $notbl = $_POST['notbl'];
        $query = "SELECT tblBitPresentacionSub.*,tblBitPresentacionEnc.folio, tblEB.NoMaquina, tblBitTurnohoras.hora as horareal,tblBitPresentacionEnc.presentacion,
        tblValeEClaves.Descripcion_Articulo,tblValeEClaves.panalxcaja,tblValeEClaves.factor FROM tblBitPresentacionSub
        INNER JOIN tblBitPresentacionEnc ON tblBitPresentacionEnc.idpresentacionenc=tblBitPresentacionSub.idpresentacionenc
        INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id=tblBitPresentacionSub.hora
		INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblBitPresentacionEnc.presentacion
        INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora tblEB ON tblEB.IdEncabezadoBItacora = tblBitPresentacionEnc.folio
        WHERE tblBitPresentacionEnc.folio = ? AND tblBitPresentacionEnc.notbl = ?";
        $result = sqlsrv_query($conn, $query, array($folio, $notbl));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'idpresentacionenc' => $row['idpresentacionenc'],
                'hora' => $row['horareal']->format('H:i:s'),
                'real' => $row['real'],
                'golpes' => $row['golpes'],
                'presentacion' => $row['presentacion'],
                'descripcion' => $row['Descripcion_Articulo'],
                'panalxcaja' => $row['panalxcaja'],
                'factor' => $row['factor'],
                'mermaempaque' => $row['mermaempaque'],
                'mermacalidad' => $row['mermacalidad'],
                "NoMaquina" => $row['NoMaquina']
            ]);
        }
        echo json_encode($array);
    }
    function tblPresentacionSubTelas()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $folio = $_POST['folio'];
        $notbl = $_POST['notbl'];
        $query = "SELECT tblPTNTS.*, tblPTNTE.folio, tblPTNTE.Clave, tblPTNTE.NoTabla, tblVEC.Descripcion_Articulo AS Descripcion,
                    tblVEC.panalxCaja, tblVEC.factor 
                    FROM [TLX004MXDB].[dbo].[tblMXPRProduccionTNTSub] tblPTNTS
                    INNER JOIN TLX004MXDB.dbo.tblMXPRProduccionTNTEnc tblPTNTE ON tblPTNTE.idPT = tblPTNTS.idPTEncabezado
                    INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC ON tblVEC.NoClave = tblPTNTE.Clave
                    WHERE tblPTNTE.folio = ? AND tblPTNTE.NoTabla = ?
                    ORDER BY tblPTNTS.NoBajada ASC";
        $result = sqlsrv_query($conn, $query, array($folio, $notbl));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'NoBajada' => $row['NoBajada'],
                'idpresentacionenc' => $row['idPTEncabezado'],
                'ML' => $row['MetrosLineales'],
                'MMC' => $row['MMCuadrados'],
                'PesoTotal' => $row['PesoTotal'],
                'ACCMC' => $row['ACCMMCuadrados'],
                'ACCKG' => $row['ACCKG'],
                'Clave' => $row['Clave'],
                'Descripcion' => $row['Descripcion'],
                'panalxcaja' => $row['panalxCaja'],
                'factor' => $row['factor']
            ]);
        }
        echo json_encode($array);
    }

    function buscarRollo() {

    header('Content-Type: application/json; charset=utf-8');

    // Validar POST
    if (!isset($_POST['rollo']) || empty($_POST['rollo'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Rollo no recibido'
        ]);
        return;
    }

    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX004MXDB");

    $NoRollo = $_POST['rollo'];

    $query = "
        SELECT NoBajada, PesoTotal 
        FROM [TLX004MXDB].[dbo].[tblMXPRProduccionTNTSub]
        WHERE NoBajada = ?
    ";

    $params = [$NoRollo];
    $result = sqlsrv_query($conn, $query, $params);

    if ($result === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en la consulta',
            'error' => sqlsrv_errors()
        ]);
        return;
    }

    $data = [];

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $data[] = [
            'NoBajada'   => $row['NoBajada'],
            'PesoTotal' => $row['PesoTotal']
        ];
    }

    echo json_encode([
        'success' => !empty($data),
        'data' => $data
    ]);
    }


    function saveDatatblPresentacion()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $field = $data['field'];
        $value = $data['value'];
        $sql = "UPDATE tblBitPresentacionSub SET $field = ? WHERE id = ?";
        $params = array($value, $id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
            http_response_code(500);
        } else {
            http_response_code(200);
        }
    }
    function updatedatatbl()
    {
        $id = $_POST["id"];
        $real = $_POST["real"];
        $cajaxp = $_POST["cajaxp"];
        $aum = $_POST["aum"];
        $std = $_POST["std"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "UPDATE tblBitPresentacionSub SET cajasxp = ?,real = ?, acumulado = ?,std = ? WHERE id = ?";
        $result = sqlsrv_query($conn, $query, array($cajaxp, $real, $aum, $std, $id));
        if ($result === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
                    echo "code: " . $error['code'] . "<br />";
                    echo "message: " . $error['message'] . "<br />";
                }
            }
        } else {
            echo json_encode('done');
        }
    }
    function updatedatatblTelas()
    {
        $id = $_POST["id"];
        $ml = $_POST["ml"];
        $accmml = $_POST["accmml"];
        $mm2 = $_POST["mm2"];
        $pesoTotal = $_POST["pesoTotal"];
        $acumm2 = $_POST["acumm2"];
        $acumpt = $_POST["acumpt"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $query = "UPDATE tblMXPRProduccionTNTSub SET MetrosLineales = ?, MMCuadrados = ?, PesoTotal = ?, ACCMMCuadrados = ?, ACCKG = ?, ACCMMLineales = ? WHERE NoBajada = ?";
        $result = sqlsrv_query($conn, $query, array($ml, $mm2, $pesoTotal, $acumm2, $acumpt, $accmml, $id));
        if ($result === false) {
            if (($errors = sqlsrv_errors()) != null) {
                foreach ($errors as $error) {
                    echo "SQLSTATE: " . $error['SQLSTATE'] . "<br />";
                    echo "code: " . $error['code'] . "<br />";
                    echo "message: " . $error['message'] . "<br />";
                }
            }
        } else {
            echo json_encode('done');
        }
    }


    // Contenido para Spooler

    function getClaves(){

        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $idMaquina = $_SESSION["idmaquina"];
        
        $query = "SELECT NoClave, 
                    CONCAT([NoClave], ' - ', [Descripcion_Articulo]) AS ClaveDescripcion,
                    pesoBase AS PesoBase,
                    ancho AS Ancho
                    FROM [TLX004MXDB].[dbo].[vwMXPRClaveMaquina]
                    WHERE maquina = ?
                    ORDER BY NoClave DESC
                    ";
        $params = array($idMaquina);
        $stmt = sqlsrv_query($conn, $query, $params);
        $claves = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $claves[] = [
                "clave" => $row["NoClave"],
                "ClaveDescripcion" => $row["ClaveDescripcion"],
                "PesoBase" => $row["PesoBase"],
                "Ancho" => $row["Ancho"]
            ];
        }
        echo json_encode($claves);
    }

    function savePresentacionSpooler(){

        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $folio = $_POST["folio"];
        $clave = $_POST["clave"];
        $noTabla = $_POST["noTabla"];

        $query = "INSERT INTO tblMXPRProduccionTNTEnc (folio, Clave, NoTabla)
                  OUTPUT INSERTED.idPT
                  VALUES (?, ?, ?)";

        $params = array($folio, $clave, $noTabla);
        $stmt = sqlsrv_query($conn, $query, $params);

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $idPT = $row["idPT"];

        echo json_encode([
            "status" => "ok",
            "idPT" => $idPT
        ]);

    }

    function getRolloPorNumero(){
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $NoRollo = $_GET["noRollo"];

        $query = "SELECT PesoTotal FROM tblMXPRProduccionTNTSub WHERE NoBajada = ?";

        $params = array($NoRollo);
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            echo json_encode([
                "status" => "error",
                "message" => "Error en la consulta",
                "error" => sqlsrv_errors()
            ]);
            return;
        }

        $data = array();

        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data [] = [
                "noRollo" => $NoRollo,
                "kg" => $row["PesoTotal"]
            ];
        }
        echo json_encode($data);
    }

    function saveRollos($input){
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");
        
        $idPT = $input["idPT"];
        $rollos = $input["rollo"];


        foreach($rollos as $rollo){
            $noRollo = $rollo["NoRollo"];
            $accKG = $rollo["accKG"];
            $accMl = $rollo["accMl"];

            $query = "INSERT INTO tblMXPRProduccionTNTSpoolerUno (idPT, NoRollo, AccKG, AccML) VALUES (?, ?, ?, ?)";

            $params = array($idPT, $noRollo, $accKG, $accMl);
            $stmt = sqlsrv_query($conn, $query, $params);
        }

        echo json_encode([
            "status" => "ok"
        ]);
    }

    function saveBajada(){
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $idPT = $_POST["idPT"];
        $noBajada = $_POST["noBajada"];
        $bobinas = $_POST["bobinas"];
        $kgBajada = $_POST["kgBajada"];
        $mlBajada = $_POST["mlBajada"];
        $mm2Bajada = $_POST["mm2Bajada"];
        $kgMerma = $_POST["kgMerma"];
        $comentarios = $_POST["comentarios"];

        $query = "INSERT INTO tblMXPRProduccionTNTSpoolerDos 
                    (idPT, NoBajada, bobinas, KgTotalesBajada, MLBajada, MMCBajada, KgMBajada, Comentarios) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array($idPT, $noBajada, $bobinas, $kgBajada, $mlBajada, $mm2Bajada, $kgMerma, $comentarios);
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            echo json_encode([
                "status" => "error",
                "message" => "Error en la consulta",
                "error" => sqlsrv_errors()
            ]);
            return;
        }

        echo json_encode([
            "status" => "ok"
        ]);
    }

    function getSesionPorFolio(){
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $folio = $_GET["folio"];

        // Query 1: Obtener presentaciones del folio
        $queryEnc = "SELECT idPT, Clave, NoTabla
                     FROM tblMXPRProduccionTNTEnc
                     WHERE folio = ?";
        $paramsEnc = array($folio);
        $stmtEnc = sqlsrv_query($conn, $queryEnc, $paramsEnc);

        $presentaciones = [];

        while($row = sqlsrv_fetch_array($stmtEnc, SQLSRV_FETCH_ASSOC)){
            $idPT    =  $row['idPT'];
            $clave   = $row['Clave'];
            $noTabla = $row['NoTabla'];

            // Query 2: Historial de bajadas por idPT
            $querybajadas = "SELECT NoBajada, bobinas, KgTotalesBajada, MLBajada,
                            MMCBajada, KgMBajada
                            FROM tblMXPRProduccionTNTSpoolerDos
                            WHERE idPT = ?
                            ORDER BY id DESC";
            $paramsBajadas = array($idPT);
            $stmtBajadas = sqlsrv_query($conn, $querybajadas, $paramsBajadas);

            $historial = [];
            while($bajada = sqlsrv_fetch_array($stmtBajadas, SQLSRV_FETCH_ASSOC)){
                $historial[] = [
                    "NoBajada"  => $bajada["NoBajada"],
                    "bobinas"   => $bajada["bobinas"],
                    "KgTotales" => $bajada["KgTotalesBajada"],
                    "MLBajada"  => $bajada["MLBajada"],
                    "MMCBajada" => $bajada["MMCBajada"],
                    "KgMBajada" => $bajada["KgMBajada"]
                ];
            }
            $presentaciones[] = [
                "idPT" => $idPT,
                "Clave" => $clave,
                "NoTabla" => $noTabla,
                "historial" => $historial
            ];
        }

        echo json_encode($presentaciones);

    }


}

// instantiate once and reuse       
$BitacoraElectronica = new BitacoraElectronica();
$input = json_decode(file_get_contents('php://input'), true);
if (isset($_GET["savePresentacion"])) {
    $BitacoraElectronica->savePresentacion();
} else if (isset($_GET["tblPresentacionSub"])) {
    $BitacoraElectronica->tblPresentacionSub();
} else if (isset($_GET["tblPresentacionSubTelas"])) {
    $BitacoraElectronica->tblPresentacionSubTelas();
} else if (isset($_GET["saveDatatblPresentacion"])) {
    $BitacoraElectronica->saveDatatblPresentacion();
} else if (isset($_GET["updatedatatbl"])) {
    $BitacoraElectronica->updatedatatbl();
} else if (isset($_GET["updatedatatblTelas"])) {
    $BitacoraElectronica->updatedatatblTelas();
} else if (isset($_GET["DeletePresentacion"])) {
    $BitacoraElectronica->DeletePresentacion();
} else if (isset($_GET["tblPresentacionGolpes"])) {
    $BitacoraElectronica->tblPresentacionGolpes();
} else if (isset($_GET["saveGolpes"])) {
    $BitacoraElectronica->saveGolpes();
} else if (isset($_GET["savePresentaciontelas"])) {
    $BitacoraElectronica->savePresentaciontelas();
} else if (isset($_GET["DeletePresentacionTelas"])) {
    $BitacoraElectronica->DeletePresentacionTelas();
} else if (isset($_GET["InsertarDataTelas2"])) {
    $BitacoraElectronica->InsertarDataTelas2();
} else if (isset($_GET["getClaves"])){
    $BitacoraElectronica->getClaves();
} else if (isset($_POST["savePresentacionSpooler"])){
    $BitacoraElectronica->savePresentacionSpooler();
} else if (isset($_GET["getRolloPorNumero"])){
    $BitacoraElectronica->getRolloPorNumero();
} else if (isset($input["saveRollos"])){
    $BitacoraElectronica->saveRollos($input);
} else if (isset($_POST["saveBajada"])){
    $BitacoraElectronica->saveBajada();
} else if (isset($_GET["getSesionPorFolio"])){
    $BitacoraElectronica->getSesionPorFolio();
}
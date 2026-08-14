<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

    function buscarRollo()
    {

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
                'NoBajada' => $row['NoBajada'],
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

    function getClaves()
    {

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

    function savePresentacionSpooler()
    {

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

    function getRolloPorNumero()
    {
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
            $data[] = [
                "noRollo" => $NoRollo,
                "kg" => $row["PesoTotal"]
            ];
        }
        echo json_encode($data);
    }

    function saveRollos($input)
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $idPT = $input["idPT"];
        $rollos = $input["rollo"];


        foreach ($rollos as $rollo) {
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

    function saveBajada()
    {
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

    function getSesionPorFolio()
    {
        $Conexion = new ClassConexion();
        $conn = $Conexion->conexion("TLX004MXDB");

        $folio = $_GET["folio"];
        error_log("getSesionPorFolio folio: " . $folio);

        $queryEnc = "SELECT idPT, Clave, NoTabla
                 FROM tblMXPRProduccionTNTEnc
                 WHERE folio = ?";
        $stmtEnc = sqlsrv_query($conn, $queryEnc, array($folio));

        if ($stmtEnc === false) {
            error_log("Error query Enc: " . print_r(sqlsrv_errors(), true));
            echo json_encode([]);
            return;
        }

        $presentaciones = [];

        while ($row = sqlsrv_fetch_array($stmtEnc, SQLSRV_FETCH_ASSOC)) {
            $idPT = $row['idPT'];
            $clave = $row['Clave'];
            $noTabla = $row['NoTabla'];

            error_log("Presentacion encontrada: idPT=$idPT, Clave=$clave, NoTabla=$noTabla");

            $querybajadas = "SELECT NoBajada, bobinas, KgTotalesBajada, MLBajada,
                         MMCBajada, KgMBajada
                         FROM tblMXPRProduccionTNTSpoolerDos
                         WHERE idPT = ?
                         ORDER BY id DESC";
            $stmtBajadas = sqlsrv_query($conn, $querybajadas, array($idPT));

            if ($stmtBajadas === false) {
                error_log("Error query bajadas: " . print_r(sqlsrv_errors(), true));
            }

            $historial = [];
            while ($bajada = sqlsrv_fetch_array($stmtBajadas, SQLSRV_FETCH_ASSOC)) {
                $historial[] = [
                    "NoBajada" => $bajada["NoBajada"],
                    "bobinas" => $bajada["bobinas"],
                    "KgTotales" => $bajada["KgTotalesBajada"],
                    "MLBajada" => $bajada["MLBajada"],
                    "MMCBajada" => $bajada["MMCBajada"],
                    "KgMBajada" => $bajada["KgMBajada"]
                ];
            }

            error_log("Historial count: " . count($historial));

            $presentaciones[] = [
                "idPT" => $idPT,
                "Clave" => $clave,
                "NoTabla" => $noTabla,
                "historial" => $historial  // sin salto de línea
            ];
        }

        error_log("Total presentaciones: " . count($presentaciones));
        echo json_encode($presentaciones);
    }


    // Contenido para HookMesh

    function saveHook()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $folio = $_POST["folio"];
        $clave = $_POST["presentacion"];
        $notbl = $_POST["notbl"];
        $turno = $_POST["turnoen"];

        $query = "INSERT INTO tblMXPR_Produccion_Hook_Enc (folio, clave, NoTabla)
                  OUTPUT INSERTED.idHE
                  VALUES (?, ?, ?)";

        $result = sqlsrv_query($conn, $query, array($folio, $clave, $notbl));

        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors());
        } else {
            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            $idHE = $row['idHE'];
            $array = $this->ConsultaHorasxturno($turno);
            foreach ($array as $horas) {
                $this->insertarSubHook($idHE, $horas['id'], $folio);
            }
            http_response_code(200);
        }
    }

    function insertarSubHook($idHE, $idHoras, $foliobit)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");

        $query = "INSERT INTO tblMXPR_Produccion_Hook_Sub (idPresentacionEncHook, hora, rollos, ml, mmc, accml, accmc)
              VALUES (?, ?, 0, 0, 0, 0, 0)";
        sqlsrv_query($conn, $query, array($idHE, $idHoras));

        $Conecta2 = new ClassConexion();
        $conn2 = $Conecta2->conexion("TLX002MXDB");

        $sql = "SELECT COUNT(*) as count FROM tblBitPresentacionGolpes WHERE idbitacora = ? AND hora = ?";
        $stmt = sqlsrv_query($conn2, $sql, array($foliobit, $idHoras));
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($row['count'] == 0) {
            $queryGolpes = "INSERT INTO tblBitPresentacionGolpes(idbitacora, hora, golpes, merma) VALUES (?, ?, ?, ?)";
            sqlsrv_query($conn2, $queryGolpes, array($foliobit, $idHoras, 0, 0));
        }
    }

    function tblHookSub()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $folio = $_POST['folio'];
        $notbl = $_POST['notbl'];

        $query = "SELECT s.id, s.idPresentacionEncHook, s.hora, s.rollos, s.ml, s.mmc,
                     s.accml, s.accmc, tblBitTurnohoras.hora as horareal,
                     e.folio, e.clave, e.NoTabla, tblVEC.Descripcion_Articulo AS Descripcion,
                     tblVEC.panalxCaja, tblVEC.factor
              FROM tblMXPR_Produccion_Hook_Sub s
              INNER JOIN tblMXPR_Produccion_Hook_Enc e ON e.idHE = s.idPresentacionEncHook
              INNER JOIN TLX002MXDB.dbo.tblBitTurnohoras ON tblBitTurnohoras.id= s.hora
              INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC ON tblVEC.NoClave = e.clave
              WHERE e.folio = ? AND e.NoTabla = ?
              ORDER BY s.id ASC";

        $result = sqlsrv_query($conn, $query, array($folio, $notbl));

        if ($result === false) {
            error_log("Error tblHookSub: " . print_r(sqlsrv_errors(), true));
            echo json_encode([]);
            return;
        }

        $array = array();

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            array_push($array, [
                'id' => $row['id'],
                'idHE' => $row['idPresentacionEncHook'],
                'hora' => $row['horareal']->format('H:i:s'),
                'rollos' => $row['rollos'],
                'ML' => $row['ml'],
                'MC' => $row['mmc'],
                'AccML' => $row['accml'],
                'AccMC' => $row['accmc'],
                'clave' => $row['clave'],
                'NoTabla' => $row['NoTabla'],
                'Descripcion' => $row['Descripcion'],
                'panalxcaja' => $row['panalxCaja'],
                'factor' => $row['factor'],
            ]);
        }

        echo json_encode($array);
    }

    function insertarFilaHook()
    {
        if (!isset($_POST["idHE"])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "msg" => "No se recibió idHE"]);
            return;
        }

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $idHE = $_POST["idHE"];

        $query = "INSERT INTO tblMXPR_Produccion_Hook_Sub 
              (idHE, rollos, MetrosLineales, MetrosCuadrados, AccMetrosLineales, AccMetrosCuadrados)
              VALUES (?, 0, 0, 0, 0, 0)";

        $stmt = sqlsrv_query($conn, $query, array($idHE));

        if ($stmt === false) {
            http_response_code(500);
            echo json_encode(["status" => "error", "errors" => sqlsrv_errors()]);
            return;
        }

        http_response_code(200);
        echo json_encode(["status" => "ok"]);
    }

    function updateDataHook()
    {
        $id = $_POST["id"];
        $rollos = $_POST["rollos"];
        $ml = $_POST["ml"];
        $mc = $_POST["mc"];
        $accml = $_POST["accml"];
        $accmc = $_POST["accmc"];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");

        $query = "UPDATE tblMXPR_Produccion_Hook_Sub 
          SET rollos = ?, ml = ?, mmc = ?, accml = ?, accmc = ?
          WHERE id = ?";

        $result = sqlsrv_query($conn, $query, array($rollos, $ml, $mc, $accml, $accmc, $id));

        if ($result === false) {
            http_response_code(500);
            echo json_encode(["status" => "error", "errors" => sqlsrv_errors()]);
        } else {
            echo json_encode("done");
        }
    }

    function DeletePresentacionHook()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $folio = $_POST["folio"];
        $notbl = $_POST["notbl"];

        // Primero obtener el idHE
        $queryId = "SELECT idHE FROM tblMXPR_Produccion_Hook_Enc WHERE folio = ? AND NoTabla = ?";
        $resultId = sqlsrv_query($conn, $queryId, array($folio, $notbl));
        $row = sqlsrv_fetch_array($resultId, SQLSRV_FETCH_ASSOC);

        if (!$row) {
            http_response_code(200);
            return;
        }

        $idHE = $row['idHE'];

        // Borrar Sub
        $querySub = "DELETE FROM tblMXPR_Produccion_Hook_Sub WHERE idPresentacionEncHook = ?";
        sqlsrv_query($conn, $querySub, array($idHE));

        // Borrar Enc
        $queryEnc = "DELETE FROM tblMXPR_Produccion_Hook_Enc WHERE idHE = ?";
        $result = sqlsrv_query($conn, $queryEnc, array($idHE));

        $result === false ? http_response_code(500) : http_response_code(200);
    }

    function obtenerEtiquetasHook()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");

        $folio = $_POST['folio'] ?? null;
        $notbl = $_POST['notbl'] ?? null;
        $claveOpcional = $_POST['clave'] ?? null;

        if (!$folio || !$notbl) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio y notbl son requeridos']);
            return;
        }

        $clave = null;

        // OPCIÓN A: Si viene clave en POST, usarla directamente
        if ($claveOpcional) {
            $clave = $claveOpcional;
        }
        // OPCIÓN B: Si NO viene clave, obtenerla de Hook_Enc
        else {
            $queryClaveHook = "SELECT clave FROM tblMXPR_Produccion_Hook_Enc 
                           WHERE folio = ? AND NoTabla = ?";
            $resultClaveHook = sqlsrv_query($conn, $queryClaveHook, array($folio, $notbl));

            if ($resultClaveHook === false) {
                error_log("Error obtenerEtiquetasHook (obtener clave): " . print_r(sqlsrv_errors(), true));
                http_response_code(500);
                echo json_encode(['error' => 'Error al obtener clave de Hook_Enc']);
                return;
            }

            $rowClave = sqlsrv_fetch_array($resultClaveHook, SQLSRV_FETCH_ASSOC);
            if (!$rowClave) {
                error_log("No se encontró Hook_Enc para folio=$folio, notbl=$notbl");
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró registro en Hook_Enc']);
                return;
            }

            $clave = $rowClave['clave'];
        }

        // Leer directamente de tblMXPR_Produccion_Hook_Etiquetas
        // AccML y AccMC ya están calculados correctamente por guardarEtiquetasHook
        $query = "SELECT e.NumeroRollo,
                         e.MetrosLineales,
                         e.Clave,
                         e.Factor,
                         e.AccML,
                         e.AccMC
                  FROM tblMXPR_Produccion_Hook_Etiquetas e
                  LEFT JOIN tblMXPR_Hook_Merma m
                      ON m.folio = e.folio
                     AND m.NumeroRollo = e.NumeroRollo
                     AND m.esMerma = 1
                  WHERE e.folio = ?
                    AND e.Clave = ?
                    AND m.id IS NULL
                  ORDER BY e.NumeroRollo ASC";

        $result = sqlsrv_query($conn, $query, array($folio, $clave));

        if ($result === false) {
            error_log("Error obtenerEtiquetasHook (consulta etiquetas): " . print_r(sqlsrv_errors(), true));
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar etiquetas']);
            return;
        }

        $array = array();

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            array_push($array, [
                'NumeroRollo'   => intval($row['NumeroRollo']),
                'MetrosLineales'=> floatval($row['MetrosLineales']),
                'Clave'         => $row['Clave'],
                'factor'        => floatval($row['Factor']),
                'AccML'         => floatval($row['AccML']),
                'AccMC'         => floatval($row['AccMC']),
            ]);
        }

        http_response_code(200);
        echo json_encode($array);
    }
    function cargarPresentacionesAutomatico()
    {
        $folio = $_POST['folio'] ?? null;

        if (!$folio) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro folio es requerido']);
            return;
        }

        // PASO 1: Obtener turno de tblEncabezadoBitacora
        $Conecta = new ClassConexion();
        $connTLX004 = $Conecta->conexion("TLX004MXDB");

        $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora 
                   WHERE IdEncabezadoBItacora = ?";
        $resultTurno = sqlsrv_query($connTLX004, $queryTurno, array($folio));

        if ($resultTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }

        $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }

        $turno = $rowTurno['Turno'];

        // PASO 2: Obtener etiquetas agrupadas por Clave
        $queryEtiquetas = "SELECT DISTINCT Clave 
                       FROM tblMXPRBitacoraEtiquetasImpresion
                       WHERE IdEncabezadoBitacora = ? AND Turno = ?
                       ORDER BY Clave ASC";
        $resultEtiquetas = sqlsrv_query($connTLX004, $queryEtiquetas, array($folio, $turno));

        if ($resultEtiquetas === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener etiquetas']);
            return;
        }

        $clavesEnEtiquetas = array();
        while ($row = sqlsrv_fetch_array($resultEtiquetas, SQLSRV_FETCH_ASSOC)) {
            $clavesEnEtiquetas[] = $row['Clave'];
        }

        if (empty($clavesEnEtiquetas)) {
            http_response_code(200);
            echo json_encode(['mensaje' => 'No hay etiquetas para este folio']);
            return;
        }

        // PASO 3: Conectar a TLX008MXDB (donde está Hook_Enc en pruebas)
        $connTLX008 = $Conecta->conexion("TLX008MXDB");

        // Obtener claves ya existentes en Hook_Enc para este folio
        $queryExistentes = "SELECT DISTINCT clave, NoTabla 
                        FROM tblMXPR_Produccion_Hook_Enc
                        WHERE folio = ?";
        $resultExistentes = sqlsrv_query($connTLX008, $queryExistentes, array($folio));

        if ($resultExistentes === false) {
            error_log("Error en cargarPresentacionesAutomatico (existentes): " . print_r(sqlsrv_errors(), true));
        }

        $clavesExistentes = array();
        $noTablaUsados = array();
        while ($row = sqlsrv_fetch_array($resultExistentes, SQLSRV_FETCH_ASSOC)) {
            $clavesExistentes[$row['clave']] = $row['NoTabla'];
            $noTablaUsados[] = $row['NoTabla'];
        }

        // PASO 4: Crear Hook_Enc para claves nuevas
        $resultado = array();
        $proxNoTabla = 1;

        foreach ($clavesEnEtiquetas as $clave) {
            if (isset($clavesExistentes[$clave])) {
                // La clave ya existe, obtener idHE
                $queryIdHE = "SELECT idHE FROM tblMXPR_Produccion_Hook_Enc 
                        WHERE folio = ? AND clave = ?";
                $resultIdHE = sqlsrv_query($connTLX008, $queryIdHE, array($folio, $clave));
                $rowIdHE = sqlsrv_fetch_array($resultIdHE, SQLSRV_FETCH_ASSOC);

                $resultado[$clave] = [
                    'NoTabla' => $clavesExistentes[$clave],
                    'idHE' => $rowIdHE['idHE'],  // ← AGREGAR
                    'accion' => 'ya_existe'
                ];
            } else {
                // Encontrar el próximo NoTabla disponible
                while (in_array($proxNoTabla, $noTablaUsados) && $proxNoTabla <= 3) {
                    $proxNoTabla++;
                }

                if ($proxNoTabla > 3) {
                    // No hay más espacios
                    $resultado[$clave] = [
                        'idHE' => null,
                        'NoTabla' => null,
                        'accion' => 'no_hay_espacio'
                    ];
                    continue;
                }

                // Crear registro en Hook_Enc
                $queryInsert = "INSERT INTO tblMXPR_Produccion_Hook_Enc (folio, clave, NoTabla)
                            OUTPUT INSERTED.idHE
                            VALUES (?, ?, ?)";
                $resultInsert = sqlsrv_query($connTLX008, $queryInsert, array($folio, $clave, $proxNoTabla));

                if ($resultInsert === false) {
                    error_log("Error insertando Hook_Enc: " . print_r(sqlsrv_errors(), true));
                    $resultado[$clave] = [
                        'NoTabla' => $proxNoTabla,
                        'accion' => 'error_al_crear'
                    ];
                    continue;
                }

                $rowInsert = sqlsrv_fetch_array($resultInsert, SQLSRV_FETCH_ASSOC);
                $idHE = $rowInsert['idHE'];

                // PASO 5: Insertar filas en Hook_Sub para todas las horas del turno
                $arrayHoras = $this->ConsultaHorasxturno($turno);
                foreach ($arrayHoras as $hora) {
                    $this->insertarSubHook($idHE, $hora['id'], $folio);
                }

                $noTablaUsados[] = $proxNoTabla;
                $resultado[$clave] = [
                    'NoTabla' => $proxNoTabla,
                    'idHE' => $idHE,
                    'accion' => 'creado'
                ];

                $proxNoTabla++;
            }
        }

        http_response_code(200);
        echo json_encode([
            'folio' => $folio,
            'turno' => $turno,
            'presentaciones' => $resultado
        ]);
    }
    function guardarEtiquetasHook()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");

        $folio = $_POST['folio'] ?? null;           // IdEncabezadoBitacora
        $clave = $_POST['clave'] ?? null;           // Clave
        $idEncabezadoHook = $_POST['idEncabezadoHook'] ?? null;  // idHE de Hook_Enc

        if (!$folio || !$clave || !$idEncabezadoHook) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio, clave e idEncabezadoHook son requeridos']);
            return;
        }

        // PASO 1: Obtener turno
        $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora 
                   WHERE IdEncabezadoBItacora = ?";
        $resultTurno = sqlsrv_query($conn, $queryTurno, array($folio));

        if ($resultTurno === false) {
            error_log("Error guardarEtiquetasHook (turno): " . print_r(sqlsrv_errors(), true));
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }

        $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }

        $turno = $rowTurno['Turno'];

        // PASO 2: Obtener etiquetas de tblMXPRBitacoraEtiquetasImpresion
        $queryEtiquetas = "SELECT e.NumeroEtiqueta, 
                              e.NumeroRollo, 
                              e.MetrosLineales, 
                              e.Clave, 
                              e.Turno,
                              e.Supervisor,
                              e.DiametroSEWEmpalmeHMUFlechaA,
                              e.DiametroSEWEmpalmeNBLFlechaA,
                              e.DiametroSEWEmpalmeHBUFlechaA,
                              e.Flecha,
                              e.DiametroRolloMetros,
                              e.DiametroSEWEmpalmeHMUFlechaB,
                              e.DiametroSEWEmpalmeNBLFlechaB,
                              e.DiametroSEWEmpalmeHBUFlechaB,
                              e.FechaCaptura,
                              tblVEC.factor
                       FROM tblMXPRBitacoraEtiquetasImpresion e
                       INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC 
                           ON tblVEC.NoClave = CAST(e.Clave AS VARCHAR(10))
                       WHERE e.IdEncabezadoBitacora = ? 
                         AND e.Clave = ?
                         AND e.Turno = ?
                       ORDER BY e.FechaCaptura ASC";

        $resultEtiquetas = sqlsrv_query($conn, $queryEtiquetas, array($folio, $clave, $turno));

        if ($resultEtiquetas === false) {
            error_log("Error guardarEtiquetasHook (etiquetas): " . print_r(sqlsrv_errors(), true));
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener etiquetas']);
            return;
        }

        // PASO 3: Verificar cuáles ya existen y guardar solo las nuevas
        $cantidadGuardadas = 0;
        $cantidadDuplicadas = 0;
        $accML = 0;      
        $accMC = 0;      

        while ($row = sqlsrv_fetch_array($resultEtiquetas, SQLSRV_FETCH_ASSOC)) {
            // Verificar si ya existe esta etiqueta guardada
            $queryExiste = "SELECT TOP 1 id FROM tblMXPR_Produccion_Hook_Etiquetas
                        WHERE folio = ? AND Clave = ? AND NumeroEtiqueta = ?";
            $resultExiste = sqlsrv_query($conn, $queryExiste, array($folio, $clave, $row['NumeroEtiqueta']));

            if ($resultExiste === false) {
                error_log("Error verificando duplicados: " . print_r(sqlsrv_errors(), true));
                continue;
            }

            $existe = sqlsrv_fetch_array($resultExiste, SQLSRV_FETCH_ASSOC);

            if ($existe) {
                // Ya existe, no guardar
                $cantidadDuplicadas++;
                continue;
            }

            // Calcular MM2 = (MetrosLineales * Factor) / 1000
            $mm2 = (floatval($row['MetrosLineales']) * floatval($row['factor'])) / 1000;
            // Acumular
            $accML = $accML + (floatval($row['MetrosLineales']) / 1000);
            $accMC = $accMC + $mm2;

            // Guardar etiqueta nueva
            $queryInsert = "INSERT INTO tblMXPR_Produccion_Hook_Etiquetas 
                (idEncabezadoHook, folio, Clave, NumeroEtiqueta, NumeroRollo, 
                MetrosLineales, MM2, Factor, Supervisor,
                DiametroSEWEmpalmeHMUFlechaA, DiametroSEWEmpalmeNBLFlechaA,
                DiametroSEWEmpalmeHBUFlechaA, Flecha, DiametroRolloMetros,
                DiametroSEWEmpalmeHMUFlechaB, DiametroSEWEmpalmeNBLFlechaB,
                DiametroSEWEmpalmeHBUFlechaB, FechaCaptura, AccML, AccMC)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $resultInsert = sqlsrv_query($conn, $queryInsert, array(
                                $idEncabezadoHook,
                                $folio,
                                $clave,
                                $row['NumeroEtiqueta'],
                                $row['NumeroRollo'],
                                floatval($row['MetrosLineales']),
                                $mm2,
                                floatval($row['factor']),
                                $row['Supervisor'],
                                floatval($row['DiametroSEWEmpalmeHMUFlechaA']),
                                floatval($row['DiametroSEWEmpalmeNBLFlechaA']),
                                floatval($row['DiametroSEWEmpalmeHBUFlechaA']),
                                $row['Flecha'],
                                floatval($row['DiametroRolloMetros']),
                                floatval($row['DiametroSEWEmpalmeHMUFlechaB']),
                                floatval($row['DiametroSEWEmpalmeNBLFlechaB']),
                                floatval($row['DiametroSEWEmpalmeHBUFlechaB']),
                                $row['FechaCaptura'],
                                $accML,  // ← AGREGAR
                                $accMC   // ← AGREGAR
                            ));

            if ($resultInsert === false) {
                error_log("Error insertando etiqueta: " . print_r(sqlsrv_errors(), true));
                continue;
            }

            $cantidadGuardadas++;
        }

        http_response_code(200);
        echo json_encode([
            'folio' => $folio,
            'clave' => $clave,
            'guardadas' => $cantidadGuardadas,
            'duplicadas' => $cantidadDuplicadas
        ]);
    }
    // -------------------------------------------------------
    // MERMA HOOK
    // Obtiene todos los rollos con ML < 1900 del folio/turno,
    // de las 3 claves activas, excluyendo los ya en merma.
    // -------------------------------------------------------
    function obtenerRollosMermaHook()
    {
        $folio  = $_POST['folio']  ?? null;

        if (!$folio) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro folio requerido']);
            return;
        }

        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        // Obtener turno del folio
        $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
        $resTurno   = sqlsrv_query($conn, $queryTurno, array($folio));
        if ($resTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }
        $rowTurno = sqlsrv_fetch_array($resTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }
        $turno = $rowTurno['Turno'];

        // Obtener las claves activas del folio (hasta 3 tablas Hook)
        $queryClaves = "SELECT clave FROM tblMXPR_Produccion_Hook_Enc WHERE folio = ?";
        $resClaves   = sqlsrv_query($conn, $queryClaves, array($folio));
        if ($resClaves === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener claves Hook']);
            return;
        }

        $claves = [];
        while ($r = sqlsrv_fetch_array($resClaves, SQLSRV_FETCH_ASSOC)) {
            $claves[] = $r['clave'];
        }

        if (empty($claves)) {
            // No hay presentaciones activas, retornar arreglo vacío
            echo json_encode([]);
            return;
        }

        // Armar placeholders dinámicos para IN (?)
        $placeholders = implode(',', array_fill(0, count($claves), '?'));

        // Consultar rollos < 1900 ML de esas claves,
        // que NO estén ya guardados como merma (esMerma = 1)
        $query = "SELECT 
                    e.NumeroRollo,
                    e.MetrosLineales,
                    e.Clave,
                    e.Turno,
                    e.IdEncabezadoBitacora,
                    tblVEC.factor,
                    ISNULL(m.esMerma, 0) AS esMerma
                  FROM tblMXPRBitacoraEtiquetasImpresion e
                  INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC
                      ON tblVEC.NoClave = CAST(e.Clave AS VARCHAR(10))
                  LEFT JOIN tblMXPR_Hook_Merma m
                      ON m.folio = e.IdEncabezadoBitacora
                     AND m.turno = e.Turno
                     AND m.NumeroRollo = e.NumeroRollo
                  WHERE e.IdEncabezadoBitacora = ?
                    AND e.Turno = ?
                    AND e.Clave IN ($placeholders)
                    AND e.MetrosLineales < 1900
                  ORDER BY e.Clave ASC, e.FechaCaptura ASC";

        $params = array_merge([$folio, $turno], $claves);
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar rollos', 'details' => sqlsrv_errors()]);
            return;
        }

        $array = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $mmc = (floatval($row['MetrosLineales']) * floatval($row['factor'])) / 1000;
            $array[] = [
                'NumeroRollo'   => intval($row['NumeroRollo']),
                'MetrosLineales'=> floatval($row['MetrosLineales']),
                'Clave'         => $row['Clave'],
                'mmc'           => round($mmc, 3),
                'esMerma'       => intval($row['esMerma']),
            ];
        }

        http_response_code(200);
        echo json_encode($array);
    }

    // -------------------------------------------------------
    // Guarda la selección de merma:
    //   - rollos con esMerma=1 → INSERT/UPDATE en tblMXPR_Hook_Merma
    //   - rollos con esMerma=0 → los elimina de la tabla de merma
    //     (regresan a su presentación normal automáticamente porque
    //      obtenerEtiquetasHook no filtra por merma)
    // Body esperado (JSON):
    //   { folio, rollos: [{NumeroRollo, Clave, MetrosLineales, esMerma}] }
    // -------------------------------------------------------
    function guardarMermaHook()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $folio  = $input['folio']  ?? null;
        $rollos = $input['rollos'] ?? [];

        if (!$folio || empty($rollos)) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio y rollos requeridos']);
            return;
        }

        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        // Obtener turno
        $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
        $resTurno   = sqlsrv_query($conn, $queryTurno, array($folio));
        if ($resTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }
        $rowTurno = sqlsrv_fetch_array($resTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }
        $turno = $rowTurno['Turno'];

        $guardados  = 0;
        $regresados = 0;
        $errores    = [];

        foreach ($rollos as $r) {
            $noRollo = intval($r['NumeroRollo']);
            $clave   = $r['Clave'];
            $ml      = floatval($r['MetrosLineales']);
            $esMerma = intval($r['esMerma']);

            if ($esMerma === 1) {
                // MERGE: insertar o actualizar como merma
                $qMerge = "IF EXISTS (
                                SELECT 1 FROM tblMXPR_Hook_Merma
                                WHERE folio = ? AND turno = ? AND NumeroRollo = ?
                           )
                           UPDATE tblMXPR_Hook_Merma
                              SET esMerma = 1, fechaGuardado = GETDATE()
                            WHERE folio = ? AND turno = ? AND NumeroRollo = ?
                           ELSE
                           INSERT INTO tblMXPR_Hook_Merma
                               (folio, turno, NumeroRollo, MetrosLineales, Clave, esMerma, fechaGuardado)
                           VALUES (?, ?, ?, ?, ?, 1, GETDATE())";

                $params = [$folio, $turno, $noRollo,
                           $folio, $turno, $noRollo,
                           $folio, $turno, $noRollo, $ml, $clave];

                $stmt = sqlsrv_query($conn, $qMerge, $params);
                if ($stmt === false) {
                    $errores[] = "Error rollo $noRollo: " . print_r(sqlsrv_errors(), true);
                } else {
                    $guardados++;
                }
            } else {
                // REGRESAR: eliminar de merma si existe
                $qDel = "DELETE FROM tblMXPR_Hook_Merma
                          WHERE folio = ? AND turno = ? AND NumeroRollo = ?";
                $stmt = sqlsrv_query($conn, $qDel, [$folio, $turno, $noRollo]);
                if ($stmt === false) {
                    $errores[] = "Error al regresar rollo $noRollo: " . print_r(sqlsrv_errors(), true);
                } else {
                    $regresados++;
                }
            }
        }

        if (!empty($errores)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'errores' => $errores]);
        } else {
            http_response_code(200);
            echo json_encode([
                'status'     => 'ok',
                'guardados'  => $guardados,
                'regresados' => $regresados,
            ]);
        }
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
} else if (isset($_GET["getClaves"])) {
    $BitacoraElectronica->getClaves();
} else if (isset($_POST["savePresentacionSpooler"])) {
    $BitacoraElectronica->savePresentacionSpooler();
} else if (isset($_GET["getRolloPorNumero"])) {
    $BitacoraElectronica->getRolloPorNumero();
} else if (isset($input["saveRollos"])) {
    $BitacoraElectronica->saveRollos($input);
} else if (isset($_POST["saveBajada"])) {
    $BitacoraElectronica->saveBajada();
} else if (isset($_GET["getSesionPorFolio"])) {
    $BitacoraElectronica->getSesionPorFolio();
} else if (isset($_GET["saveHook"])) {
    $BitacoraElectronica->saveHook();
} else if (isset($_GET["obtenerEtiquetasHook"])) {
    $BitacoraElectronica->obtenerEtiquetasHook();
} else if (isset($_GET["cargarPresentacionesAutomatico"])) {
    $BitacoraElectronica->cargarPresentacionesAutomatico();
} else if (isset($_GET["tblHookSub"])) {
    $BitacoraElectronica->tblHookSub();
} else if (isset($_GET["insertarFilaHook"])) {
    $BitacoraElectronica->insertarFilaHook();
} else if (isset($_GET["updateDataHook"])) {
    $BitacoraElectronica->updateDataHook();
} else if (isset($_GET["DeletePresentacionHook"])) {
    $BitacoraElectronica->DeletePresentacionHook();
} else if (isset($_GET["guardarEtiquetasHook"])) {
    $BitacoraElectronica->guardarEtiquetasHook();
} else if (isset($_GET["obtenerRollosMermaHook"])) {
    $BitacoraElectronica->obtenerRollosMermaHook();
} else if (isset($_GET["guardarMermaHook"])) {
    $BitacoraElectronica->guardarMermaHook();
}
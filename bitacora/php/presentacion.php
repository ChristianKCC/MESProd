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
        $claveOpcional = $_POST['clave'] ?? null;  // NUEVO: clave opcional

        if (!$folio || !$notbl) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio y notbl son requeridos']);
            return;
        }

        $clave = null;
        $turno = null;

        // OPCIÓN A: Si viene clave en POST, usarla directamente
        if ($claveOpcional) {
            $clave = $claveOpcional;

            // Obtener turno de tblEncabezadoBitacora
            $Conecta2 = new ClassConexion();
            $conn2 = $Conecta2->conexion("TLX004MXDB");

            $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora 
                       WHERE IdEncabezadoBItacora = ?";
            $resultTurno = sqlsrv_query($conn2, $queryTurno, array($folio));

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

            // Obtener el turno desde tblEncabezadoBitacora
            $Conecta2 = new ClassConexion();
            $conn2 = $Conecta2->conexion("TLX004MXDB");

            $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora 
                       WHERE IdEncabezadoBItacora = ?";
            $resultTurno = sqlsrv_query($conn2, $queryTurno, array($folio));

            if ($resultTurno === false) {
                error_log("Error obtenerEtiquetasHook (obtener turno): " . print_r(sqlsrv_errors(), true));
                http_response_code(500);
                echo json_encode(['error' => 'Error al obtener turno de Encabezado']);
                return;
            }

            $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
            if (!$rowTurno) {
                error_log("No se encontró turno en tblEncabezadoBitacora para IdEncabezadoBItacora=$folio");
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró turno en Encabezado']);
                return;
            }

            $turno = $rowTurno['Turno'];
        }

        // PASO 3: Buscar etiquetas en tblMXPRBitacoraEtiquetasImpresion
        $query = "SELECT e.NumeroRollo, 
                     e.MetrosLineales, 
                     e.Clave, 
                     e.Turno, 
                     e.IdEncabezadoBitacora,
                     e.FechaCaptura,
                     tblVEC.factor
              FROM tblMXPRBitacoraEtiquetasImpresion e
              INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC 
                  ON tblVEC.NoClave = CAST(e.Clave AS VARCHAR(10))
              WHERE e.IdEncabezadoBitacora = ? 
                AND e.Clave = ?
                AND e.Turno = ?
              ORDER BY e.FechaCaptura ASC";

        $result = sqlsrv_query($conn, $query, array($folio, $clave, $turno));

        if ($result === false) {
            error_log("Error obtenerEtiquetasHook (consulta etiquetas): " . print_r(sqlsrv_errors(), true));
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar etiquetas']);
            return;
        }

        // PASO 4: Construir array de respuesta
        $array = array();

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            array_push($array, [
                'NumeroRollo' => intval($row['NumeroRollo']),
                'MetrosLineales' => floatval($row['MetrosLineales']),
                'Clave' => $row['Clave'],
                'factor' => floatval($row['factor']),
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
            // La clave ya existe, solo registrar
            $resultado[$clave] = [
                'NoTabla' => $clavesExistentes[$clave],
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
}
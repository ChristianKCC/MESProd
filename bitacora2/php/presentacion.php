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
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST["folio"];
        $presentacion = $_POST["presentacion"];
        $turno = $_POST["turnoen"];
        $notbl = $_POST["notbl"];
        $array = array();
        $query = "INSERT INTO tblBitPresentacionEncTelas (folio,presentacion, notbl) VALUES (?,?,?)";
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
                $this->InsertHoraxturnoTelas($last_id, $horas['id'], $folio);
            }
            http_response_code(200);
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
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "DELETE FROM tblBitPresentacionSubTelas WHERE idpresentacionenc = ?";
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
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST["folio"];
        $notbl = $_POST["notbl"];
        $idpresentacion = 0;
        $query = "DELETE FROM tblBitPresentacionEncTelas OUTPUT DELETED.* WHERE folio = ? AND notbl = ?";
        $result = sqlsrv_query($conn, $query, array($folio, $notbl));
        while ($row = sqlsrv_fetch_array($result)) {
            $idpresentacion = $row['idpresentacionenc'];
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
        $query = "SELECT tblBitPresentacionSub.*,tblBitPresentacionEnc.folio,tblBitTurnohoras.hora as horareal,tblBitPresentacionEnc.presentacion,
        tblValeEClaves.Descripcion_Articulo,tblValeEClaves.panalxcaja,tblValeEClaves.factor FROM tblBitPresentacionSub
        INNER JOIN tblBitPresentacionEnc ON tblBitPresentacionEnc.idpresentacionenc=tblBitPresentacionSub.idpresentacionenc
        INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id=tblBitPresentacionSub.hora
		INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblBitPresentacionEnc.presentacion
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
                'mermacalidad' => $row['mermacalidad']
            ]);
        }
        echo json_encode($array);
    }
    function tblPresentacionSubTelas()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_POST['folio'];
        $notbl = $_POST['notbl'];
        $query = "SELECT tblBitPresentacionSubTelas.*,tblBitPresentacionEncTelas.folio,tblBitTurnohoras.hora as horareal,tblBitPresentacionEncTelas.presentacion,
        tblValeEClaves.Descripcion_Articulo,tblValeEClaves.panalxcaja,tblValeEClaves.factor FROM tblBitPresentacionSubTelas
        INNER JOIN tblBitPresentacionEncTelas ON tblBitPresentacionEncTelas.idpresentacionenc=tblBitPresentacionSubTelas.idpresentacionenc
        INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id=tblBitPresentacionSubTelas.hora
		INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblBitPresentacionEncTelas.presentacion
        WHERE tblBitPresentacionEncTelas.folio = ? AND tblBitPresentacionEncTelas.notbl = ?";
        $result = sqlsrv_query($conn, $query, array($folio, $notbl));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'idpresentacionenc' => $row['idpresentacionenc'],
                'hora' => $row['horareal']->format('H:i:s'),
                'presentacion' => $row['presentacion'],
                'rollos' => $row['rollos'],
                'mml' => $row['mml'],
                'acum' => $row['acum'],
                'descripcion' => $row['Descripcion_Articulo'],
                'panalxcaja' => $row['panalxcaja'],
                'factor' => $row['factor']
            ]);
        }
        echo json_encode($array);
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
        $rollos = $_POST["rollos"];
        $mml = $_POST["mml"];
        $acum = $_POST["acum"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "UPDATE tblBitPresentacionSubTelas SET rollos = ?,mml = ?, acum = ? WHERE id = ?";
        $result = sqlsrv_query($conn, $query, array($rollos, $mml, $acum, $id));
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
}
if (isset($_GET["savePresentacion"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->savePresentacion();
} else if (isset($_GET["tblPresentacionSub"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->tblPresentacionSub();
}  else if (isset($_GET["tblPresentacionSubTelas"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->tblPresentacionSubTelas();
} else if (isset($_GET["saveDatatblPresentacion"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->saveDatatblPresentacion();
} else if (isset($_GET["updatedatatbl"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->updatedatatbl();
} else if (isset($_GET["updatedatatblTelas"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->updatedatatblTelas();
} else if (isset($_GET["DeletePresentacion"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->DeletePresentacion();
} else if (isset($_GET["tblPresentacionGolpes"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->tblPresentacionGolpes();
} else if (isset($_GET["saveGolpes"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->saveGolpes();
}else if (isset($_GET["savePresentaciontelas"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->savePresentaciontelas();
}else if (isset($_GET["DeletePresentacionTelas"])) {
    $BitacoraElectronica = new BitacoraElectronica();
    $BitacoraElectronica->DeletePresentacionTelas();
}

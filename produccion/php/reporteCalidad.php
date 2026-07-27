<?php
require_once "../../Session/seguridad.php";
require_once "../../conexion.php";
class ReporteCalidad
{
    function getDataMaquinas()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX009MXDB');
        $sql = "
        SELECT DISTINCT
            tblMC.NoDepto,
            tblMC.NoMaquina,
            tblM.NombreMaquina
        FROM TLX009MXDB.dbo.tblMaquinasCombo AS tblMC
        INNER JOIN TLX009MXDB.dbo.tblMaquinas AS tblM
            ON tblM.NoMaquina = tblMC.NoMaquina
        WHERE tblMC.NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 73, 76, 137, 138)
        ORDER BY tblMC.NoDepto ASC;
    ";

        $array = [];

        $result = sqlsrv_query($conn, $sql);

        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
            return;
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = $row;
        }

        http_response_code(200);
        echo json_encode($array);
    }
    function hora_actual()
    {
        date_default_timezone_set('America/Mexico_City');
        $hora_actual = date('H:i');
        if ($hora_actual >= '07:00' && $hora_actual < '15:00') {
            $turno = 1;
        } else if ($hora_actual >= '15:00' && $hora_actual < '22:30') {
            $turno = 2;
        } else {
            $turno = 3;
        }
        return $turno;
    }

    function guardarReporteCalidad()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $inspeccionados = $_POST['inspeccionados'] ?? 0;
        $sd = $_POST['sd'] ?? 0;
        $ql = $_POST['ql'] ?? 0;
        $observaciones = $_POST['sdobservaciones'] ?? '';
        $noMaquina = $_POST['maquina'] ?? 0;

        $turno = $this->hora_actual();
        $query = "INSERT INTO tblMXPRCalidadMaquinas (Inspeccionadas, SD, QL, Observaciones, NoMaquina, Turno) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$inspeccionados, $sd, $ql, $observaciones, $noMaquina, $turno];
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            http_response_code(500);

            echo json_encode([
                "success" => false,
                "error" => sqlsrv_errors()
            ]);

            return;
        } else {
            http_response_code(200);

            echo json_encode([
                "success" => true
            ]);

        }

    }

    function obtenerReporteCalidad()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $query = "  SELECT idReporteCalidad, Inspeccionadas, SD
                        ,QL, Observaciones, tblMCalidadM.NoMaquina, tblM.NombreMaquina
                        ,fechaSave, Turno 
                    FROM tblMXPRCalidadMaquinas tblMCalidadM
                    INNER JOIN TLX009MXDB.dbo.tblMaquinas tblM ON tblM.NoMaquina = tblMCalidadM.NoMaquina
                    ORDER BY fechaSave DESC ";
        $stmt = sqlsrv_query($conn, $query);

        if ($stmt === false) {
            http_response_code(500);
            echo json_encode('error');
            return;
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = [
                "idReporteCalidad" => $row['idReporteCalidad'],
                "Inspeccionadas" => $row['Inspeccionadas'],
                "SD" => $row['SD'],
                "QL" => $row['QL'],
                "Observaciones" => $row['Observaciones'],
                "NoMaquina" => $row['NoMaquina'],
                "NombreMaquina" => $row['NombreMaquina'],
                "Fecha" => $row['fechaSave']->format('Y-m-d'),
                "Turno" => $row['Turno']
            ];
        }

        http_response_code(200);
        echo json_encode($data);
    }
}


if (isset($_GET['getDataMaquinas'])) {
    $infoMaquinas = new ReporteCalidad();
    $infoMaquinas->getDataMaquinas();
} else if (isset($_GET['guardarReporteCalidad'])) {
    $infoMaquinas = new ReporteCalidad();
    $infoMaquinas->guardarReporteCalidad();
} else if (isset($_GET['obtenerReporteCalidad'])) {
    $infoMaquinas = new ReporteCalidad();
    $infoMaquinas->obtenerReporteCalidad();
}
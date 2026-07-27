<?php
require_once "../../Session/seguridad.php";
require_once "../../conexion.php";

class ReporteDepartamentos
{
    function getDataDepartamentos()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX009MXDB');

        $query = "SELECT NoDepto, NombreDepto FROM tblDepartamentos WHERE NoDepto IN (1,2,24,25) ORDER BY NombreDepto ASC";
        $result = sqlsrv_query($conn, $query);
        $data = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        echo json_encode($data);

    }

    function infoReporteDepartamentos()
    {
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $departamento = $_POST['departamento'];

        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sql = "SET NOCOUNT ON;

                IF OBJECT_ID('tempdb..#tmpProduccion') IS NOT NULL DROP TABLE #tmpProduccion;

                CREATE TABLE #tmpProduccion (
                    NoMaquina            int,
                    NombreMaquina        varchar(100),
                    Fecha                datetime,
                    Turno                int,
                    idBitacora           int,
                    idpresentacionenc    int,
                    NoDepto              int,
                    NombreDepto          varchar(100),
                    Clave                int,
                    notbl                int,
                    Reales               int,
                    Piezas               int,
                    AcumuladoReal        int,
                    USTD                 decimal(18,2),
                    TotalUSTDTurno       decimal(18,2),
                    TotalRealTurno       decimal(18,2),
                    TotalPiezas          int
                );

                INSERT INTO #tmpProduccion
                EXEC dbo.pa_ObtenerProduccionPorTurno ?, ?, ?;

                SELECT
                    NoMaquina,
                    MAX(NombreMaquina) AS NombreMaquina,
                    MAX(NombreDepto) AS Departamento,
                    SUM(TotalPiezas) AS TotalPiezas,
                    SUM(TotalUSTDTurno) AS TotalUSTD,
                    SUM(TotalRealTurno) AS TotalReal
                FROM #tmpProduccion
                GROUP BY NoMaquina
                ORDER BY NoMaquina;
            ";

        $params = array($departamento, $fechai, $fechaf);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        // Avanza hasta el resultset que tenga columnas (el SELECT final)
        // Esto evita quedarse en DROP/CREATE/INSERT que no regresan filas
        do {
            $meta = sqlsrv_field_metadata($stmt);
            if ($meta !== false && count($meta) > 0) {
                break;
            }
        } while (sqlsrv_next_result($stmt));

        $array = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"],
                "Departamento" => $row["Departamento"],
                "TotalPiezas" => (int) $row["TotalPiezas"],
                "TotalUSTD" => (float) $row["TotalUSTD"],
                "TotalReal" => (float) $row["TotalReal"],
            ];
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($array);
    }
}


if (isset($_GET['getDataDepartamentos'])) {
    $infoDepartamentos = new ReporteDepartamentos();
    $infoDepartamentos->getDataDepartamentos();
} else if (isset($_GET['infoReporteDepartamentos'])) {
    $infoDepartamentos = new ReporteDepartamentos();
    $infoDepartamentos->infoReporteDepartamentos();
}
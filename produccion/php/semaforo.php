<?php
require_once "..\..\conexion.php";

// Función para obtener el turno basado en fecha/hora
function obtenerTurno($fecha)
{
    $hora = (int) $fecha->format('H');
    $minutos = (int) $fecha->format('i');
    $horaMin = $hora + $minutos / 60;

    if ($horaMin >= 7 && $horaMin < 15)
        return 1;
    if ($horaMin >= 15 && $horaMin < 22.5)
        return 2;
    return 3; // Turno 3: 22:30 a 07:00
}


class Semaforo
{
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

    function obtenerRegistrosTurno()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $turno = $this->hora_actual();
        $maquina = isset($_GET['maquina']) ? $_GET['maquina'] : null;

        $sql = "SELECT COUNT(*) AS Total FROM tblMXPRCalidadMaquinas Where Turno = ? AND NoMaquina = ?";
        $params = [$turno, $maquina];
        $stmt = sqlsrv_query($conn, $sql, $params);

        $total = 0;
        if ($stmt) {
            if (sqlsrv_fetch($stmt)) {
                $total = sqlsrv_get_field($stmt, 0);
            }
            sqlsrv_free_stmt($stmt);
        }

        $sqlUltimos = "SELECT TOP 10 idReporteCalidad, Inspeccionadas, SD, QL, Observaciones, NoMaquina, fechaSave, Turno 
                        FROM tblMXPRCalidadMaquinas 
                        WHERE NoMaquina = ? AND Turno = ?
                        ORDER BY fechaSave DESC";
        $paramsUltimos = [$maquina, $turno];
        $stmtUltimos = sqlsrv_query($conn, $sqlUltimos, $paramsUltimos);
        $ultimos = [];

        if ($stmtUltimos) {
            while ($row = sqlsrv_fetch_array($stmtUltimos, SQLSRV_FETCH_ASSOC)) {
                if ($row['fechaSave'] instanceof DateTime) {
                    $row['fechaSave'] = $row['fechaSave']->format('Y-m-d H:i:s');
                }
                $ultimos[] = $row;
            }
            sqlsrv_free_stmt($stmtUltimos);
        }

        $sqlStats = "SELECT
                        SUM(CASE WHEN Turno = 1 THEN 1 ELSE 0 END) AS Turno1,
                        SUM(CASE WHEN Turno = 2 THEN 1 ELSE 0 END) AS Turno2,
                        SUM(CASE WHEN Turno = 3 THEN 1 ELSE 0 END) AS Turno3
                    FROM tblMXPRCalidadMaquinas WHERE CAST(fechaSave AS DATE) = CAST(GETDATE() AS DATE)";
        $stmtStats = sqlsrv_query($conn, $sqlStats);
        $statsHoy = ["Turno1" => 0, "Turno2" => 0, "Turno3" => 0];

        if ($stmtStats) {
            $statsHoy = sqlsrv_fetch_array($stmtStats, SQLSRV_FETCH_ASSOC);
            sqlsrv_free_stmt($stmtStats);
        }
        echo json_encode([
            'success' => true,
            'turno_actual' => $turno,
            'total_registros_turno' => (int) $total,
            'estadisticas_hoy' => $statsHoy,
            'ultimos_registros' => $ultimos
        ]);
    }

}

if (isset($_GET["obtenerRegistrosTurno"])) {
    $BitacoraElectronica = new Semaforo();
    $BitacoraElectronica->obtenerRegistrosTurno();
}
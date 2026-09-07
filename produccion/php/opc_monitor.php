<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';

class OpcMonitor
{
    // ─── HookMesh (maquina 67) — tabla tblMXPRBitacoraHook ───────────────────

    function getDataNow()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $maquina = $_GET['maquina'];
        $query = "SELECT TOP 1 Turno, MilesMetrosHora, Metros, Rechazos, TiempoParoMin, TiempoEnhebrandoMin,
        TiempoCorriendoMin, MermaMaquina, TiempoPerdido, ParosMaquina, PorcentajeTiempoPerdido, CorriendoParada, VelocidadActual,
        FechaHora
        FROM tblMXPRBitacoraHook WHERE NoMaquina=$maquina ORDER BY Id DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'turno'                   => $row['Turno'],
                'velocidad'               => round($row['MilesMetrosHora'], 2),
                'metros'                  => round($row['Metros'], 2),
                'rechazos'                => round($row['Rechazos'], 2),
                'tiempoParoMin'           => round($row['TiempoParoMin'], 2),
                'tiempoEnhebrandoMin'     => round($row['TiempoEnhebrandoMin'], 2),
                'tiempoCorriendoMin'      => round($row['TiempoCorriendoMin'], 2),
                'merma'                   => round($row['MermaMaquina'], 2),
                'tiempoPerdido'           => round($row['TiempoPerdido'], 2),
                'paros'                   => round($row['ParosMaquina'], 2),
                'porcentajeTiempoPerdido' => round($row['PorcentajeTiempoPerdido'], 2),
                'corriendoParada'         => $row['CorriendoParada'],
                'velocidadActual'         => round($row['VelocidadActual'], 2),
                'fechaHora'               => $row['FechaHora']->format('H:i:s')
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function getDataMonitor()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $maquina = $_GET['maquina'];
        $numregs = (int) $_GET['numhrs'] * 180;
        $hora = $operacion = $merma = $velocidad = array();
        $query = "SELECT * FROM (SELECT TOP ($numregs) * FROM tblMXPRBitacoraHook WHERE NoMaquina=$maquina ORDER BY Id DESC) as T
            ORDER BY Id ASC;";
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($hora,      $row["FechaHora"]->format("H:i:s"));
            array_push($operacion, $row["CorriendoParada"] * 10);
            array_push($merma,     number_format($row["MermaMaquina"], 2));
            array_push($velocidad, number_format($row["VelocidadActual"], 2));
        }
        $respuesta = ["hora" => $hora, "operacion" => $operacion, "merma" => $merma, "velocidad" => $velocidad];
        echo json_encode($respuesta);
        sqlsrv_close($conn);
    }

    // ─── BCM4 y otras máquinas — tabla tblMXPRResumenMaquinasTurno ───────────

    /**
     * Devuelve el ultimo registro del turno actual para una maquina.
     * Mismo formato que getDataNow de bitacora.php para que el JS no cambie.
     */
    function getDataNowMult()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $maquina = (int) $_GET['maquina'];
        $query = "SELECT TOP 1
                    NoMaquina, Turno, FechaHora,
                    Cortes, Rechazos, MinutosParo, MinutosCorriendo,
                    MermaMaquina, TiempoPerdidoTotal, ParoMaquinaTurno,
                    VelocidadPromedio, VelocidadActual
                  FROM dbo.tblMXPRResumenMaquinasTurno
                  WHERE NoMaquina = ?
                  ORDER BY Id DESC";
        $result = sqlsrv_query($conn, $query, array($maquina));
        $array  = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            // Merma calculada igual que bitacora.php: (rechazos/cortes)*100
            $cortes   = $row['Cortes']   ?? 0;
            $rechazos = $row['Rechazos'] ?? 0;
            $mermacalc = ($cortes > 0) ? round(($rechazos / $cortes) * 100, 2) : 0;

            // Estado: la maquina corre si VelocidadActual > 0
            $estado = ($row['VelocidadActual'] > 0) ? 1 : 0;

            array_push($array, [
                'turno'              => $row['Turno'],
                'velocidadprom'      => round($row['VelocidadPromedio'], 2),
                'velocidadact'       => round($row['VelocidadActual'],   2),
                'cortes'             => round($row['Cortes'],            2),
                'rechazos'           => round($row['Rechazos'],          2),
                'tcorrida'           => round($row['MinutosCorriendo'],  2),
                'tparo'              => round($row['MinutosParo'],       2),
                'TiempoarribaTurno'  => round($row['MinutosCorriendo'],  2),
                'TiempoabajoTurno'   => round($row['MinutosParo'],       2),
                'merma'              => $mermacalc,
                'tiempoPerdidoTotal' => round($row['TiempoPerdidoTotal'],2),
                'paroMaquinaTurno'   => round($row['ParoMaquinaTurno'],  2),
                'estado'             => $estado,
                'fechaHora'          => $row['FechaHora']->format('H:i:s')
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }

    /**
     * Devuelve los ultimos N registros para graficar velocidad y merma.
     * Mismo formato que GetDataMonitor de bitacora.php.
     * numhrs * 180 registros (1 cada 20s = 180 por hora)
     */
    function GetDataMonitorMult()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX004MXDB");
        $maquina  = (int) $_GET['maquina'];
        $numregs  = (int) $_GET['numhrs'] * 180;
        $hora = $merma = $velocidad = array();

        $query = "SELECT * FROM (
                    SELECT TOP (?) *
                    FROM dbo.tblMXPRResumenMaquinasTurno
                    WHERE NoMaquina = ?
                    ORDER BY Id DESC
                  ) AS T
                  ORDER BY Id ASC";
        $result = sqlsrv_query($conn, $query, array($numregs, $maquina));
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $cortes   = $row['Cortes']   ?? 0;
            $rechazos = $row['Rechazos'] ?? 0;
            $mermacalc = ($cortes > 0) ? ($rechazos / $cortes) * 100 : 0;

            array_push($hora,      $row['FechaHora']->format('H:i:s'));
            array_push($merma,     number_format($mermacalc, 2));
            array_push($velocidad, number_format($row['VelocidadActual'], 2, '.', ''));
        }
        $respuesta = ["hora" => $hora, "merma" => $merma, "velocidad" => $velocidad];
        echo json_encode($respuesta);
        sqlsrv_close($conn);
    }
}

if (isset($_GET["getDataNow"])) {
    $OpcMonitor = new OpcMonitor();
    $OpcMonitor->getDataNow();
} else if (isset($_GET["getDataMonitor"])) {
    $OpcMonitor = new OpcMonitor();
    $OpcMonitor->getDataMonitor();
} else if (isset($_GET["getDataNowMult"])) {
    $OpcMonitor = new OpcMonitor();
    $OpcMonitor->getDataNowMult();
} else if (isset($_GET["GetDataMonitorMult"])) {
    $OpcMonitor = new OpcMonitor();
    $OpcMonitor->GetDataMonitorMult();
}
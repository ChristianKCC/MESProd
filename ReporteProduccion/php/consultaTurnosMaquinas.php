<?php
require_once "..\..\conexion.php";

function nombreMes($numMes)
{
    $numMes == 1 && $numMes = "Enero";
    $numMes == 2 && $numMes = "Febrero";
    $numMes == 3 && $numMes = "Marzo";
    $numMes == 4 && $numMes = "Abril";
    $numMes == 5 && $numMes = "Mayo";
    $numMes == 6 && $numMes = "Junio";
    $numMes == 7 && $numMes = "Julio";
    $numMes == 8 && $numMes = "Agosto";
    $numMes == 9 && $numMes = "Septiembre";
    $numMes == 10 && $numMes = "Octubre";
    $numMes == 11 && $numMes = "Noviembre";
    $numMes == 12 && $numMes = "Diciembre";

    return $numMes;
}
class Turnos
{

    public function generarReportePorID($id)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        // Obtener el registro base por ID
        $queryBase = "
        SELECT NoMaquina, fTurnoA, fAñoA, MesA, DiaA
        FROM tblBitacoraMaquinasAnterior
        WHERE id = ?
    ";
        $paramsBase = [$id];
        $resultBase = sqlsrv_query($conn, $queryBase, $paramsBase);

        if ($resultBase === false || !sqlsrv_has_rows($resultBase)) {
            die(json_encode([
                "error" => "No se encontró el registro con el ID proporcionado.",
                "detalle" => sqlsrv_errors()
            ]));
        }

        $rowBase = sqlsrv_fetch_array($resultBase, SQLSRV_FETCH_ASSOC);

        $maquina = $rowBase['NoMaquina'];
        $turnoMax = $rowBase['fTurnoA'];
        $anio = $rowBase['fAñoA'];
        $mes = $rowBase['MesA'];
        $dia = $rowBase['DiaA'];

        $datos = [];

        for ($t = 1; $t <= $turnoMax; $t++) {

            // Obtener el folio (IdEncabezadoBitacora)
            $queryEnc = "
            SELECT IdEncabezadoBItacora
            FROM tblEncabezadoBitacora
            WHERE NoMaquina = ? AND Turno = ? AND Fecha = ?
        ";

            $fecha = "$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
            $paramsEnc = [$maquina, $t, $fecha];

            $resultEnc = sqlsrv_query($conn, $queryEnc, $paramsEnc);
            $folio = null;

            if ($resultEnc && sqlsrv_has_rows($resultEnc)) {
                $rowEnc = sqlsrv_fetch_array($resultEnc, SQLSRV_FETCH_ASSOC);
                $folio = $rowEnc['IdEncabezadoBItacora'];
            }
            $noempAsis = null;
            $nombreAsis = null;
            $puestoNombreAsis = null;

            if ($folio !== null) {

                $queryAsis = "
                SELECT TOP 1
                    tblBitAsistencias.noemp,
                    tblempleados.nombre,
                    tblPuestos.nombre AS PuestoNombre
                FROM TLX002MXDB.dbo.tblBitAsistencias
                INNER JOIN TLX032MXDB.dbo.tblempleados
                    ON tblempleados.noemp = tblBitAsistencias.noemp
                INNER JOIN TLX009MXDB.dbo.tblPuestos
                    ON tblPuestos.id = tblempleados.puesto
                WHERE tblBitAsistencias.folio = ?
                  AND tblPuestos.id IN (8, 69, 70, 74)
                ORDER BY tblBitAsistencias.fecha ASC
            ";

                $paramsAsis = [$folio];
                $resultAsis = sqlsrv_query($conn, $queryAsis, $paramsAsis);

                if ($resultAsis && sqlsrv_has_rows($resultAsis)) {
                    $rowA = sqlsrv_fetch_array($resultAsis, SQLSRV_FETCH_ASSOC);

                    $noempAsis = $rowA['noemp'];
                    $nombreAsis = $rowA['nombre'];
                    $puestoNombreAsis = $rowA['PuestoNombre'];
                }
            }

            $query = "
            WITH UltimosAcumulados AS (
                SELECT tblBPS.idpresentacionenc, tblBPE.presentacion AS NoClave,
                       MAX(tblBPS.hora) AS UltimaHora
                FROM TLX002MXDB.dbo.tblBitPresentacionSub tblBPS
                INNER JOIN TLX002MXDB.dbo.tblBitPresentacionEnc tblBPE
                    ON tblBPE.idpresentacionenc = tblBPS.idpresentacionenc
                WHERE tblBPE.folio = ?
                GROUP BY tblBPS.idpresentacionenc, tblBPE.presentacion
            ),
            DatosFinales AS (
                SELECT tblBPS.idpresentacionenc, tblBPE.presentacion AS NoClave,
                       tblValeEClaves.panalxcaja, tblBPS.acumulado,
                       (tblBPS.acumulado * tblValeEClaves.panalxcaja) AS TotalPañales
                FROM TLX002MXDB.dbo.tblBitPresentacionSub tblBPS
                INNER JOIN TLX002MXDB.dbo.tblBitPresentacionEnc tblBPE
                    ON tblBPE.idpresentacionenc = tblBPS.idpresentacionenc
                INNER JOIN TLX002MXDB.dbo.tblValeEClaves
                    ON tblValeEClaves.NoClave = tblBPE.presentacion
                INNER JOIN UltimosAcumulados UA
                    ON UA.idpresentacionenc = tblBPS.idpresentacionenc
                   AND UA.UltimaHora = tblBPS.hora
                WHERE tblBPE.folio = ?
            )
            SELECT
                tblBMA.id,
                tblEB.IdEncabezadoBItacora,
                tblBMA.NoMaquina,
                tblM.NombreMaquina,
                tblBMA.CortesA,
                tblBMA.RechazosA,
                tblBMA.TAbajoA,
                tblBMA.fMinEnhebrandoA,
                tblBMA.TArribaA,
                tblBMA.fMermaMaquinaA,
                tblBMA.fTiempoPerdidoA,
                tblBMA.fParoMaqinaA,
                tblEB.HorasTrabajadas,
                tblBMA.fTurnoA,
                tblBMA.fAñoA,
                tblBMA.MesA,
                tblBMA.DiaA,
                tblEB.Fecha,
                DF.idpresentacionenc,
                DF.NoClave,
                DF.acumulado,
                DF.panalxcaja,
                DF.TotalPañales,
                (SELECT SUM(TotalPañales) FROM DatosFinales) AS TotalGeneralPañales
            FROM TLX004MXDB.dbo.tblBitacoraMaquinasAnterior tblBMA
            LEFT JOIN TLX009MXDB.dbo.tblMaquinas tblM
                ON tblM.NoMaquina = tblBMA.NoMaquina
            LEFT JOIN TLX004MXDB.dbo.tblEncabezadoBitacora tblEB
                ON tblEB.NoMaquina = tblBMA.NoMaquina
               AND tblEB.Turno = tblBMA.fTurnoA
               AND tblEB.Fecha =
                    TRY_CAST(
                        CONCAT(tblBMA.fAñoA, '-', RIGHT('00' + CAST(tblBMA.MesA AS VARCHAR), 2),
                               '-', RIGHT('00' + CAST(tblBMA.DiaA AS VARCHAR), 2)) AS DATE
                    )
            LEFT JOIN DatosFinales DF ON 1 = 1
            WHERE tblBMA.NoMaquina = ?
              AND tblBMA.fTurnoA = ?
              AND tblBMA.fAñoA = ?
              AND tblBMA.MesA = ?
              AND tblBMA.DiaA = ?
            ORDER BY tblBMA.fTurnoA DESC
        ";

            $params = [$folio, $folio, $maquina, $t, $anio, $mes, $dia];
            $result = sqlsrv_query($conn, $query, $params);

            if ($result === false) {
                die(json_encode([
                    "error" => "Error en la consulta SQL",
                    "detalle" => sqlsrv_errors()
                ]));
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            if ($row) {

                $datos[] = [
                    'id' => $row['id'],
                    'IdEncabezadoBitacora' => $row['IdEncabezadoBItacora'],
                    'NoMaquina' => $row['NoMaquina'],
                    'NombreMaquina' => $row['NombreMaquina'],
                    'CortesA' => $row['CortesA'],
                    'RechazosA' => $row['RechazosA'],
                    'TiempoAbajoA' => $row['TAbajoA'],
                    'MinEnhebrandoA' => $row['fMinEnhebrandoA'],
                    'TTiempoArribaA' => $row['TArribaA'],
                    'MermaMaquinaA' => $row['fMermaMaquinaA'],
                    'TiempoPerdidoA' => $row['fTiempoPerdidoA'],
                    'NoParosMaquinaA' => $row['fParoMaqinaA'],
                    'HorasTrabajadas' => $row['HorasTrabajadas'],
                    'Turno' => $row['fTurnoA'],
                    'Año' => $row['fAñoA'],
                    'Mes' => $row['MesA'],
                    'Dia' => $row['DiaA'],
                    'Fecha' => $row['Fecha']
                        ? $row['Fecha']->format('j') . ' - ' . nombreMes($row['Fecha']->format('n')) . ' - ' . $row['Fecha']->format('Y')
                        : null,
                    'idpresentacionenc' => $row['idpresentacionenc'],
                    'NoClave' => $row['NoClave'],
                    'acumulado' => $row['acumulado'],
                    'panalxcaja' => $row['panalxcaja'],
                    'TotalPañales' => $row['TotalPañales'],
                    'TotalGeneralPañales' => $row['TotalGeneralPañales'],
                    'NoEmpleadoAsistencia' => $noempAsis,
                    'EmpleadoNombreAsistencia' => $nombreAsis,
                    'PuestoNombreAsistencia' => $puestoNombreAsis
                ];

            } else {

                $datos[] = [
                    'id' => null,
                    'IdEncabezadoBitacora' => $folio,
                    'NoMaquina' => $maquina,
                    'NombreMaquina' => '',
                    'CortesA' => 0,
                    'RechazosA' => 0,
                    'TiempoAbajoA' => 0,
                    'MinEnhebrandoA' => 0,
                    'TTiempoArribaA' => 0,
                    'MermaMaquinaA' => 0,
                    'TiempoPerdidoA' => 0,
                    'NoParosMaquinaA' => 0,
                    'HorasTrabajadas' => 0,
                    'Turno' => null,
                    'Año' => $anio,
                    'Mes' => $mes,
                    'Dia' => $dia,
                    'Fecha' => intval($dia) . ' - ' . nombreMes($mes) . '-' . $anio,
                    'idpresentacionenc' => null,
                    'NoClave' => null,
                    'acumulado' => 0,
                    'panalxcaja' => 0,
                    'TotalPañales' => 0,
                    'TotalGeneralPañales' => 0,
                    'completo' => false,
                    'NoEmpleadoAsistencia' => $noempAsis,
                    'EmpleadoNombreAsistencia' => $nombreAsis,
                    'PuestoNombreAsistencia' => $puestoNombreAsis
                ];
            }
        }

        // echo json_encode($datos, JSON_PRETTY_PRINT);
        return $datos;
    }

    public function clavesProduccion($id)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $query = "
        WITH DatosBase AS (
            SELECT 
                tblBMA.NoMaquina,
                tblBMA.fAñoA,
                tblBMA.MesA,
                tblBMA.DiaA,
                tblBMA.fTurnoA,
                TRY_CAST(CONCAT(fAñoA, '-', RIGHT('00' + CAST(MesA AS VARCHAR), 2), '-', RIGHT('00' + CAST(DiaA AS VARCHAR), 2)) AS DATE) AS Fecha
            FROM TLX004MXDB.dbo.tblBitacoraMaquinasAnterior tblBMA
            WHERE id = ?
        ),
        TurnosIncluidos AS (
            SELECT 1 AS Turno
            UNION ALL
            SELECT 2 WHERE EXISTS (SELECT 1 FROM DatosBase WHERE fTurnoA >= 2)
            UNION ALL
            SELECT 3 WHERE EXISTS (SELECT 1 FROM DatosBase WHERE fTurnoA = 3)
        ),
        BitacorasFiltradas AS (
            SELECT EB.IdEncabezadoBitacora, EB.Turno, EB.Fecha, EB.NoMaquina, tblM.NombreMaquina
            FROM TLX004MXDB.dbo.tblEncabezadoBitacora EB
            JOIN DatosBase DB ON EB.NoMaquina = DB.NoMaquina AND EB.Fecha = DB.Fecha
            JOIN TurnosIncluidos TI ON EB.Turno = TI.Turno
            INNER JOIN TLX009MXDB.dbo.tblMaquinas tblM ON tblM.NoMaquina = EB.NoMaquina
        ),
        ClavesPorTurno AS (
            SELECT
                EB.IdEncabezadoBitacora,
                BPE.idpresentacionenc, 
                BPE.notbl AS NoTabla,
                EB.Fecha,
                EB.Turno,
                EB.NombreMaquina,
                BPE.presentacion AS NoClave,
                VE.Descripcion_Articulo AS Descripcion,
                VE.panalxcaja,
                BPS.cajasxp AS Piezas,
                BPS.acumulado,
                BPS.std AS STD,
                (BPS.acumulado * VE.panalxcaja) AS TotalPañales,
                ROW_NUMBER() OVER (PARTITION BY BPE.idpresentacionenc ORDER BY BPS.acumulado DESC) AS rn
            FROM BitacorasFiltradas EB
            JOIN TLX002MXDB.dbo.tblBitPresentacionEnc BPE ON BPE.folio = EB.IdEncabezadoBitacora
            JOIN TLX002MXDB.dbo.tblBitPresentacionSub BPS ON BPS.idpresentacionenc = BPE.idpresentacionenc
            JOIN TLX002MXDB.dbo.tblValeEClaves VE ON VE.NoClave = BPE.presentacion
        ),
        TotalesPorPresentacion AS (
            SELECT 
                idpresentacionenc,
                NoTabla,
                IdEncabezadoBitacora,
                Turno,
                NombreMaquina,
                NoClave,
                Descripcion,
                acumulado AS TotalAcumulado,
                STD AS TotalSTD,
                TotalPañales
            FROM ClavesPorTurno
            WHERE rn = 1
        ),
        TotalesPorTurno AS (
            SELECT 
                IdEncabezadoBitacora,
                Turno,
                SUM(TotalAcumulado) AS TotalPañalesTurno,
                SUM(TotalSTD) AS TotalSTDTurno
            FROM TotalesPorPresentacion
            GROUP BY IdEncabezadoBitacora, Turno
        )
        SELECT 
            TP.idpresentacionenc,
            TP.NoTabla,
            TP.IdEncabezadoBitacora,
            TP.Turno,
            TP.NombreMaquina,
            TP.NoClave,
            TP.Descripcion,
            TP.TotalAcumulado,
            TP.TotalSTD,
            TP.TotalPañales,
            TT.TotalPañalesTurno,
            TT.TotalSTDTurno
        FROM TotalesPorPresentacion TP
        JOIN TotalesPorTurno TT ON TP.IdEncabezadoBitacora = TT.IdEncabezadoBitacora AND TP.Turno = TT.Turno
        ORDER BY TP.IdEncabezadoBitacora, TP.Turno, TP.NoTabla DESC;
    ";

        $params = [$id];
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            die(json_encode([
                "error" => "Error en la consulta SQL",
                "detalle" => sqlsrv_errors()
            ]));
        }

        $datosAgrupados = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $turno = $row['Turno'];

            if (!isset($datosAgrupados[$turno])) {
                $datosAgrupados[$turno] = [];
            }

            $datosAgrupados[$turno][] = [
                'idpresentacionenc' => $row['idpresentacionenc'],
                'NoTabla' => $row['NoTabla'],
                'Turno' => $row['Turno'],
                'IdEncabezadoBitacora' => $row['IdEncabezadoBitacora'],
                'NombreMaquina' => $row['NombreMaquina'],
                'NoClave' => $row['NoClave'],
                'Descripcion' => $row['Descripcion'],
                'TotalAcumulado' => $row['TotalAcumulado'],
                'TotalSTD' => $row['TotalSTD'],
                'TotalPañales' => $row['TotalPañales'],
                'TotalPañalesTurno' => $row['TotalPañalesTurno'],
                'TotalSTDTurno' => $row['TotalSTDTurno']
            ];
        }

        // echo json_encode($datosAgrupados, JSON_PRETTY_PRINT);

        return $datosAgrupados;
    }

    public function seccionesPorParo($id)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $query = " WITH DatosBase AS (
                    SELECT TOP (1)
                        tblBMA.NoMaquina,
                        tblBMA.fAñoA,
                        tblBMA.MesA,
                        tblBMA.DiaA,
                        tblBMA.fTurnoA,
                        DATEFROMPARTS(tblBMA.fAñoA, tblBMA.MesA, tblBMA.DiaA) AS Fecha
                    FROM TLX004MXDB.dbo.tblBitacoraMaquinasAnterior AS tblBMA
                    WHERE tblBMA.id = ?
                ),
                TurnosIncluidos AS (
                    SELECT 1 AS Turno
                    UNION ALL SELECT 2 WHERE EXISTS (SELECT 1 FROM DatosBase WHERE fTurnoA >= 2)
                    UNION ALL SELECT 3 WHERE EXISTS (SELECT 1 FROM DatosBase WHERE fTurnoA = 3)
                ),
                Eventos AS (
                    SELECT
                        V.*,
                        CASE
                            WHEN V.Turno = 3
                            AND CAST(V.HoraParo AS TIME) < '07:00:00'
                            THEN DATEADD(DAY, -1, V.Fecha)
                            ELSE V.Fecha
                        END AS FechaOperativa
                    FROM TLX004MXDB.dbo.vwBitacoraParosMaquinas AS V
                )
                SELECT
                    E.FechaOperativa AS Fecha,   -- devolvemos la fecha 'operativa' con el mismo alias
                    E.Turno,
                    E.NoMaquina,
                    E.NombreMaquina,
                    E.NoSeccion,
                    E.Seccion,
                    E.NoModulo,
                    E.Modulos,
                    E.HorasTrabajadas,
                    BMA.TArribaA AS TiempoArriba,
                    BMA.TAbajoA AS TiempoAbajo,
                    COUNT(*) AS TotalPorSeccion
                FROM Eventos AS E
                INNER JOIN DatosBase AS DB
                    ON E.NoMaquina      = DB.NoMaquina
                AND E.FechaOperativa = DB.Fecha
                INNER JOIN TurnosIncluidos AS TI
                    ON E.Turno          = TI.Turno
                LEFT JOIN TLX004MXDB.dbo.tblBitacoraMaquinasAnterior AS BMA
                    ON BMA.NoMaquina = E.NoMaquina
                AND BMA.fTurnoA  = E.Turno
                AND BMA.fAñoA    = YEAR(E.FechaOperativa)
                AND BMA.MesA     = MONTH(E.FechaOperativa)
                AND BMA.DiaA     = DAY(E.FechaOperativa)
                GROUP BY
                    E.FechaOperativa,
                    E.Turno,
                    E.NoMaquina,
                    E.NombreMaquina,
                    E.NoSeccion,
                    E.Seccion,
                    E.NoModulo,
                    E.Modulos,
                    BMA.TArribaA,
                    BMA.TAbajoA,
                    E.HorasTrabajadas
                ORDER BY
                    E.Turno ASC,
                    E.FechaOperativa ASC;
                ";

        $params = [$id];
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            die(json_encode([
                "error" => "Error en la consulta SQL",
                "detalle" => sqlsrv_errors()
            ]));
        }

        $datosAgrupados = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {

            $turno = $row['Turno'];

            $registro = [
                'Fecha' => $row['Fecha'] ? $row['Fecha']->format('Y-m-d') : null,
                'Turno' => $turno,
                'NoMaquina' => $row['NoMaquina'],
                'NombreMaquina' => $row['NombreMaquina'],
                'NoSeccion' => $row['NoSeccion'],
                'Seccion' => $row['Seccion'],
                'NoModulo' => $row['NoModulo'],
                'Modulos' => $row['Modulos'],
                'TiempoArriba' => $row['TiempoArriba'],
                'TiempoAbajo' => $row['TiempoAbajo'],
                'TotalPorSeccion' => $row['TotalPorSeccion'],
                'HorasTrabajadas' => $row['HorasTrabajadas']
            ];

            if (!isset($datosAgrupados[$turno])) {
                $datosAgrupados[$turno] = [];
            }

            $datosAgrupados[$turno][] = $registro;
        }

        // echo json_encode($datosAgrupados, JSON_PRETTY_PRINT);
        return $datosAgrupados;
    }

    public function seccionesParosModulos($id)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $query = "
            WITH DatosBase AS (
                SELECT TOP (1)
                    tblBMA.NoMaquina,
                    tblBMA.fAñoA,
                    tblBMA.MesA,
                    tblBMA.DiaA,
                    tblBMA.fTurnoA,
                    DATEFROMPARTS(tblBMA.fAñoA, tblBMA.MesA, tblBMA.DiaA) AS Fecha
                FROM TLX004MXDB.dbo.tblBitacoraMaquinasAnterior AS tblBMA
                WHERE tblBMA.id = ?
            ),
            TurnosIncluidos AS (
                SELECT 1 AS Turno
                UNION ALL SELECT 2 WHERE EXISTS (SELECT 1 FROM DatosBase WHERE fTurnoA >= 2)
                UNION ALL SELECT 3 WHERE EXISTS (SELECT 1 FROM DatosBase WHERE fTurnoA = 3)
            ),
            -- Normalizamos la fecha al 'día operativo' según horarios de turnos:
            -- Turno 1: 07:00 - 15:00
            -- Turno 2: 15:00 - 22:30
            -- Turno 3: 22:30 - 07:00 (día siguiente) => eventos entre 00:00 y 06:59 se asignan al día previo
            Eventos AS (
                SELECT
                    V.*,
                    CASE
                        WHEN V.Turno = 3
                        AND CAST(V.HoraParo AS TIME) < '07:00:00'
                        THEN DATEADD(DAY, -1, V.Fecha)
                        ELSE V.Fecha
                    END AS FechaOperativa
                FROM TLX004MXDB.dbo.vwBitacoraParosMaquinas AS V
            )
            SELECT
                E.id,
                E.FechaOperativa AS Fecha, 
                E.HoraParo        AS Hora,
                E.Turno,
                E.NoMaquina,
                E.NombreMaquina,
                E.NoSeccion,
                E.Seccion,
                E.NoModulo,
                E.Modulos,
                E.TiempoParo,
                E.Motivo,
                E.Correccion,
                BMA.TArribaA AS TiempoArriba
            FROM Eventos AS E
            INNER JOIN DatosBase AS DB
                ON E.NoMaquina      = DB.NoMaquina
            AND E.FechaOperativa = DB.Fecha
            INNER JOIN TurnosIncluidos AS TI
                ON E.Turno          = TI.Turno
            LEFT JOIN TLX004MXDB.dbo.tblBitacoraMaquinasAnterior AS BMA
                ON BMA.NoMaquina = E.NoMaquina
            AND BMA.fTurnoA  = E.Turno
            AND BMA.fAñoA    = YEAR(E.FechaOperativa)
            AND BMA.MesA     = MONTH(E.FechaOperativa)
            AND BMA.DiaA     = DAY(E.FechaOperativa)
            ORDER BY
                E.Turno ASC,
                E.id ASC;
            ";

        $params = [$id];
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            die(json_encode([
                'error' => 'Error en la consulta SQL',
                'detalle' => sqlsrv_errors()
            ]));
        }

        $datosAgrupados = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {

            $turno = $row['Turno'];

            $registro = [
                'Fecha' => $row['Fecha'] ? $row['Fecha']->format('Y-m-d') : null,
                'Hora' => ($row['Hora'] instanceof DateTime) ? $row['Hora']->format('H:i:s') : $row['Hora'],
                'Turno' => $row['Turno'],
                'NoMaquina' => $row['NoMaquina'],
                'NombreMaquina' => $row['NombreMaquina'],
                'NoSeccion' => $row['NoSeccion'],
                'Seccion' => $row['Seccion'],
                'NoModulo' => $row['NoModulo'],
                'Modulos' => $row['Modulos'],
                'TiempoParo' => $row['TiempoParo'],
                'Motivo' => $row['Motivo'],
                'Correccion' => $row['Correccion'],
                'TiempoArriba' => $row['TiempoArriba']
            ];

            if (!isset($datosAgrupados[$turno])) {
                $datosAgrupados[$turno] = [];
            }

            $datosAgrupados[$turno][] = $registro;
        }

        return $datosAgrupados;
    }

}

class TurnosSinConexion
{
    public function getDataTurnosAnteriores($fecha, $maquina, $turno)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        if (empty($fecha) || empty($maquina)) {
            die(json_encode(["error" => "Parámetros insuficientes. Se requieren 'fecha' y 'maquina'."]));
        }

        $array = [];

        // Agrupa TODAS las claves del turno con SUM para los campos numéricos.
        // Para campos de contexto (conductor, std, etc.) toma el del último IdEncabezadoBItacora.
        $query = "
                SELECT
                MAX(IdEncabezadoBItacora)   AS IdEncabezadoBItacora,
                MAX(presentacion)           AS presentacion,
                MAX(Descripcion_Articulo)   AS Descripcion_Articulo,
                MAX(Fecha)                  AS Fecha,
                Turno,
                MAX(HorasTrabajadas)        AS HorasTrabajadas,
                MAX(NoMaquina)              AS NoMaquina,
                MAX(NombreMaquina)          AS NombreMaquina,
                MAX(golpes)                 AS golpes,          -- SUM → MAX
                MAX(merma)                  AS merma,           -- SUM → MAX
                SUM(real)                   AS CajasReales,
                SUM(cajasxp)                AS CajasxPanal,
                SUM(acumulado)              AS acumulado,
                MAX(std)                    AS std,
                SUM(Rechazos)               AS Rechazos,
                MAX(MinutosTurno)           AS MinutosTurno,
                MAX(TotalTiempoPerdido)     AS TiempoPerdido,   -- SUM → MAX
                MAX(TiempoArriba)           AS TiempoArriba,    -- SUM → MAX
                MAX(ParosMaquina)           AS ParosMaquina,    -- SUM → MAX
                MAX(NombreEmpleado)         AS NombreEmpleado
            FROM ProduccionesMaquinasSinRed
            WHERE Fecha     = ?
            AND NoMaquina = ?
        ";

        $params = [$fecha, $maquina];

        if (!empty($turno)) {
            $query .= " AND Turno <= ?";
            $params[] = $turno;
        }

        $query .= " GROUP BY Turno
                ORDER BY Turno ASC";

        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $fechaFormateada = isset($row['Fecha']) && $row['Fecha'] instanceof DateTime
                ? $row['Fecha']->format('Y-m-d')
                : (is_string($row['Fecha']) ? $row['Fecha'] : null);

            $fechaBonita = null;

            if ($fechaFormateada) {
                $fechaObj = new DateTime($fechaFormateada);
                $dia = $fechaObj->format('d');
                $mes = nombreMes((int) $fechaObj->format('m'));
                $anio = $fechaObj->format('Y');
                $fechaBonita = "{$dia} - {$mes} - {$anio}";
            }

            $array[] = [
                "IdEncabezadoBItacora" => $row['IdEncabezadoBItacora'],
                "presentacion" => $row['presentacion'],
                "Descripcion_Articulo" => $row['Descripcion_Articulo'],
                "Fecha" => $fechaBonita,
                "Turno" => $row['Turno'],
                "HorasTrabajadas" => $row['HorasTrabajadas'],
                "NoMaquina" => $row['NoMaquina'],
                "NombreMaquina" => $row['NombreMaquina'],
                "Cortes" => $row['golpes'],
                "merma" => $row['merma'],
                "CajasReales" => $row['CajasReales'],
                "PañalEmpacado" => $row['CajasxPanal'],
                "acumulado" => $row['acumulado'],
                "std" => $row['std'],
                "Rechazos" => $row['Rechazos'],
                "MinutosTurno" => $row['MinutosTurno'],
                "TiempoPerdido" => $row['TiempoPerdido'],
                "TiempoArriba" => $row['TiempoArriba'],
                "ParosMaquina" => $row['ParosMaquina'],
                "NombreEmpleado" => $row['NombreEmpleado']
            ];
        }

        // Asegurar que existan los 3 turnos (1, 2, 3)
        $turnosFinales = [];

        $indexados = [];
        foreach ($array as $item) {
            $indexados[(int) $item['Turno']] = $item;
        }

        for ($i = 1; $i <= 3; $i++) {
            if (isset($indexados[$i])) {
                $turnosFinales[] = $indexados[$i];
            } else {
                $turnosFinales[] = [
                    "IdEncabezadoBItacora" => null,
                    "presentacion" => null,
                    "Descripcion_Articulo" => null,
                    "Fecha" => $fecha ?? null,
                    "Turno" => $i,
                    "HorasTrabajadas" => 0,
                    "NoMaquina" => $maquina ?? null,
                    "NombreMaquina" => null,
                    "Cortes" => 0,
                    "merma" => 0,
                    "CajasReales" => 0,
                    "PañalEmpacado" => 0,
                    "acumulado" => 0,
                    "std" => 0,
                    "Rechazos" => 0,
                    "MinutosTurno" => 0,
                    "TiempoPerdido" => 0,
                    "TiempoArriba" => 0,
                    "ParosMaquina" => 0,
                    "NombreEmpleado" => null
                ];
            }
        }

        usort($turnosFinales, function ($a, $b) {
            return $a['Turno'] <=> $b['Turno'];
        });

        return $turnosFinales;
    }

    public function clavesMaquinasSinRed($fecha, $maquina, $turno)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        if (empty($fecha) || empty($maquina) || empty($turno)) {
            die(json_encode(["error" => "Parámetros insuficientes. Se requieren 'fecha', 'maquina' y 'turno'."]));
        }

        $query = " WITH DatosDetalle AS (
                        SELECT 
                        IdEncabezadoBItacora,
                        presentacion,
                        Descripcion_Articulo,
                        Fecha,
                        Turno,
                        NoMaquina,
                        NombreMaquina,
                        acumulado,
                        cajasxp,
                        std,
                        ROW_NUMBER() OVER (PARTITION BY Turno ORDER BY presentacion DESC) AS NoTabla
                    FROM [TLX004MXDB].[dbo].[ProduccionesMaquinasSinRed]
                    WHERE Fecha = ?
                    AND NoMaquina = ?
                    AND Turno <= ?
                ),
                TotalesPorTurno AS (
                    SELECT 
                        Turno,
                        SUM(acumulado) AS TotalAcumuladoTurno,
                        SUM(std) AS TotalSTDTurno
                    FROM DatosDetalle
                    GROUP BY Turno
                )
                SELECT 
                    DD.Turno,
                    DD.IdEncabezadoBItacora,
                    DD.presentacion,
                    DD.NombreMaquina,
                    DD.presentacion AS NoClave,
                    DD.Descripcion_Articulo AS Descripcion,
                    DD.acumulado AS TotalAcumulado,
                    DD.std AS TotalSTD,
                    DD.cajasxp,
                    TT.TotalAcumuladoTurno,
                    TT.TotalSTDTurno
                FROM DatosDetalle DD
                INNER JOIN TotalesPorTurno TT ON DD.Turno = TT.Turno
                ORDER BY DD.Turno, DD.NoTabla DESC
            ";

        $params = [$fecha, $maquina, $turno];

        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }


        $resultadoPorTurno = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            // Usar la llave del turno como string: "1", "2", "3"
            $keyTurno = (string) $row['Turno'];

            if (!isset($resultadoPorTurno[$keyTurno])) {
                $resultadoPorTurno[$keyTurno] = [];
            }

            // Mantener los mismos campos que ya generas
            $resultadoPorTurno[$keyTurno][] = [
                "Turno" => $row['Turno'],
                "IdEncabezadoBItacora" => $row['IdEncabezadoBItacora'],
                "NoClave" => $row['NoClave'],
                "NombreMaquina" => $row['NombreMaquina'],
                "Descripcion" => $row['Descripcion'],
                "TotalAcumulado" => $row['TotalAcumulado'],
                "TotalSTD" => $row['TotalSTD'],
                "cajasxp" => $row['cajasxp'],
                "TotalAcumuladoTurno" => $row['TotalAcumuladoTurno'],
                "TotalSTDTurno" => $row['TotalSTDTurno']
            ];
        }


        // echo json_encode($resultadoPorTurno, JSON_PRETTY_PRINT);
        return $resultadoPorTurno;
    }

    public function seccionesMaquinasSinConexion($fecha, $maquina, $turno)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        if (empty($fecha) || empty($maquina) || empty($turno)) {
            die(json_encode(["error" => "Parámetros insuficientes. Se requieren 'fecha', 'maquina' y 'turno'."]));
        }

        $query = " SELECT
                tblBCT.[fecha],
                tblEB.Turno,
                tblEB.NoMaquina,
                tblEB.HorasTrabajadas,
                tblBCT.[horainicio],
                tblBCT.[seccion],
                tblBS.NombreSeccion,
                tblBCT.[modulo],
                tblBM.NombreModulo,
                tblBCT.[motivo],
                tblBCT.[correccion],
                tblBCT.[subtotal] AS TiempoParo,
                
                -- Acumulado correcto por turno
                SUM(tblBCT.subtotal) OVER (
                    PARTITION BY tblEB.fecha, tblEB.NoMaquina, tblEB.Turno
                    ORDER BY tblBCT.horainicio
                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                ) AS TiempoPerdido,
                
                -- Total de paros por turno
                COUNT(*) OVER (
                    PARTITION BY tblEB.fecha, tblEB.NoMaquina, tblEB.Turno
                ) AS ParosMaquina,
                
                -- Total de paros por sección
                COUNT(*) OVER (
                    PARTITION BY tblEB.fecha, tblEB.NoMaquina, tblEB.Turno, tblBCT.seccion
                ) AS TotalPorSeccion,
                
                -- Información de la vista ProduccionesMaquinasSinRed       
                PMR.TiempoArriba,
                PMR.MinutosTurno,
                PMR.TotalTiempoPerdido AS TotalTiempoPerdidoTurno
                
            FROM [TLX002MXDB].[dbo].[tblBitCtrltiempos] tblBCT
            INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora tblEB
                ON tblEB.IdEncabezadoBItacora = tblBCT.folio
            INNER JOIN TLX002MXDB.dbo.tblBitSecciones tblBS 
                ON tblBS.NoSeccion = tblBCT.seccion
            INNER JOIN TLX002MXDB.dbo.tblBitModulos tblBM 
                ON tblBM.NoModulo = tblBCT.modulo
            LEFT JOIN (
                SELECT DISTINCT
                    IdEncabezadoBItacora,
                    Fecha,
                    Turno,
                    NoMaquina,
                    TiempoArriba,
                    MinutosTurno,
                    TotalTiempoPerdido
                FROM [TLX004MXDB].[dbo].[ProduccionesMaquinasSinRed]
            ) PMR
                ON PMR.IdEncabezadoBItacora = tblEB.IdEncabezadoBItacora
            AND PMR.Fecha = tblEB.fecha
            AND PMR.Turno = tblEB.Turno
            AND PMR.NoMaquina = tblEB.NoMaquina
            WHERE tblEB.fecha = ?
            AND tblEB.NoMaquina = ?
            AND tblEB.Turno <= ?
            ORDER BY tblEB.Turno, tblBCT.horainicio";

        $params = [$fecha, $maquina, $turno];
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }

        $datosAgrupados = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $turno = $row['Turno'];

            $registro = [
                'Fecha' => $row['fecha'] ? $row['fecha']->format('Y-m-d') : null,
                'Turno' => $row['Turno'],
                'NoMaquina' => $row['NoMaquina'],
                'HoraInicio' => ($row['horainicio'] instanceof DateTime) ? $row['horainicio']->format('H:i:s') : $row['horainicio'],
                'Seccion' => $row['seccion'],
                'NombreSeccion' => $row['NombreSeccion'],
                'Modulo' => $row['modulo'],
                'NombreModulo' => $row['NombreModulo'],
                'Motivo' => $row['motivo'],
                'Correccion' => $row['correccion'],
                'TiempoParo' => $row['TiempoParo'],
                'TiempoPerdido' => $row['TiempoPerdido'],
                'TotalPorSeccion' => $row['TotalPorSeccion'],
                'TiempoArriba' => $row['TiempoArriba'],
                'MinutosTurno' => $row['MinutosTurno'],
                'TotalTiempoPerdidoTurno' => $row['TotalTiempoPerdidoTurno'],
                'HorasTrabajadas' => $row['HorasTrabajadas'],
            ];

            if (!isset($datosAgrupados[$turno])) {
                $datosAgrupados[$turno] = [];
            }

            $datosAgrupados[$turno][] = $registro;
        }

        // echo json_encode($datosAgrupados, JSON_PRETTY_PRINT);
        return $datosAgrupados;
    }
}

class TurnosHook
{
    public function generarDatosAnterioresHook($id)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        // Paso 1 - Obtener contexto del registro
        $queryBase = "
        SELECT NoMaquina, Turno AS TurnoMax, FechaTurno
        FROM [TLX004MXDB].[dbo].[tblMXPRResumenTurnoHook]
        WHERE id = ?
    ";
        $resultBase = sqlsrv_query($conn, $queryBase, [$id]);

        if ($resultBase === false || !sqlsrv_has_rows($resultBase)) {
            die(json_encode([
                "error" => "No se encontró el registro.",
                "detalle" => sqlsrv_errors()
            ]));
        }

        $rowBase = sqlsrv_fetch_array($resultBase, SQLSRV_FETCH_ASSOC);
        $maquina = $rowBase['NoMaquina'];
        $turnoMax = $rowBase['TurnoMax'];
        $fecha = $rowBase['FechaTurno']->format('Y-m-d');

        $datos = [];

        // Paso 2 - Loop de turno 1 hasta turnoMax
        for ($t = 1; $t <= $turnoMax; $t++) {

            $query = "
            SELECT
                tblRTH.[Id]
                ,tblRTH.[FechaTurno]
                ,tblRTH.[NoMaquina]
                ,tblMaquinas.NombreMaquina
                ,tblRTH.[IdEncabezadoBitacora]
                ,tblRTH.[Turno]
                ,tblRTH.[Metros]
                ,ISNULL(merma.TotalMetrosLineales, 0)                                        AS MetrosRechazados
                ,ISNULL(etiquetas.AccML, 0) * 1000                                           AS MetrosLineales
                ,(ISNULL(etiquetas.AccML, 0) * 1000) - ISNULL(merma.TotalMetrosLineales, 0) AS MetrosEntregados
                ,tblRTH.[TiempoParoMin]      AS TiempoAbajo
                ,tblRTH.[TiempoCorriendoMin] AS TiempoArriba
                ,tblRTH.[ParosMaquina]
                ,enc.HorasTrabajadas
                ,asis.noemp          AS NoEmpleado
                ,asis.nombre         AS NombreEmpleado
                ,asis.PuestoNombre
            FROM [TLX004MXDB].[dbo].[tblMXPRResumenTurnoHook] tblRTH
            INNER JOIN TLX009MXDB.dbo.tblMaquinas
                ON tblMaquinas.NoMaquina = tblRTH.NoMaquina
            LEFT JOIN [TLX004MXDB].[dbo].[tblEncabezadoBitacora] enc
                ON enc.IdEncabezadoBItacora = tblRTH.IdEncabezadoBitacora
            LEFT JOIN (
                SELECT
                    [folio]
                    ,SUM([MetrosLineales]) AS TotalMetrosLineales
                FROM [TLX004MXDB].[dbo].[tblMXPR_Hook_Merma]
                WHERE [esMerma] = 1
                GROUP BY [folio]
            ) AS merma
                ON merma.folio = tblRTH.IdEncabezadoBitacora
            LEFT JOIN (
                SELECT
                    [folio]
                    ,SUM([AccML]) AS AccML
                    ,SUM([AccMC]) AS AccMC
                FROM (
                    SELECT
                        [folio]
                        ,[idEncabezadoHook]
                        ,[AccML]
                        ,[AccMC]
                        ,ROW_NUMBER() OVER (
                            PARTITION BY [folio], [idEncabezadoHook]
                            ORDER BY [NumeroEtiqueta] DESC
                        ) AS rn
                    FROM [TLX004MXDB].[dbo].[tblMXPR_Produccion_Hook_Etiquetas]
                ) ranked
                WHERE rn = 1
                GROUP BY [folio]
            ) AS etiquetas
                ON etiquetas.folio = tblRTH.IdEncabezadoBitacora
            LEFT JOIN (
                SELECT
                    ba.folio
                    ,ba.noemp
                    ,emp.nombre
                    ,puestos.nombre AS PuestoNombre
                    ,ROW_NUMBER() OVER (
                        PARTITION BY ba.folio
                        ORDER BY ba.fecha ASC
                    ) AS rn
                FROM TLX002MXDB.dbo.tblBitAsistencias ba
                INNER JOIN TLX032MXDB.dbo.tblempleados emp
                    ON emp.noemp = ba.noemp
                INNER JOIN TLX009MXDB.dbo.tblPuestos puestos
                    ON puestos.id = emp.puesto
                WHERE puestos.id IN (8, 69, 70, 74)
            ) AS asis
                ON asis.folio = tblRTH.IdEncabezadoBitacora
                AND asis.rn = 1
            WHERE tblRTH.NoMaquina = ?
              AND tblRTH.FechaTurno = ?
              AND tblRTH.Turno = ?
        ";

            $params = [$maquina, $fecha, $t];
            $result = sqlsrv_query($conn, $query, $params);

            if ($result === false) {
                die(json_encode([
                    "error" => "Error en consulta turno $t",
                    "detalle" => sqlsrv_errors()
                ]));
            }

            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

            if ($row) {
                $datos[] = [
                    'Id' => $row['Id'],
                    'FechaTurno' => $row['FechaTurno']?->format('Y-m-d'),
                    'NoMaquina' => $row['NoMaquina'],
                    'NombreMaquina' => $row['NombreMaquina'],
                    'IdEncabezadoBitacora' => $row['IdEncabezadoBitacora'],
                    'Turno' => $row['Turno'],
                    'Metros' => $row['Metros'],
                    'MetrosRechazados' => $row['MetrosRechazados'],
                    'MetrosLineales' => $row['MetrosLineales'],
                    'MetrosEntregados' => $row['MetrosEntregados'],
                    'TiempoAbajo' => $row['TiempoAbajo'],
                    'TiempoArriba' => $row['TiempoArriba'],
                    'ParosMaquina' => $row['ParosMaquina'],
                    'HorasTrabajadas' => $row['HorasTrabajadas'],
                    'NoEmpleado' => $row['NoEmpleado'],
                    'NombreEmpleado' => $row['NombreEmpleado'],
                    'PuestoNombre' => $row['PuestoNombre'],
                ];
            } else {
                // Turno sin datos — fila vacía
                $datos[] = [
                    'Id' => null,
                    'FechaTurno' => $fecha,
                    'NoMaquina' => $maquina,
                    'NombreMaquina' => '',
                    'IdEncabezadoBitacora' => null,
                    'Turno' => $t,
                    'Metros' => 0,
                    'MetrosRechazados' => 0,
                    'MetrosLineales' => 0,
                    'MetrosEntregados' => 0,
                    'TiempoAbajo' => 0,
                    'TiempoArriba' => 0,
                    'ParosMaquina' => 0,
                    'HorasTrabajadas' => 0,
                    'NoEmpleado' => null,
                    'NombreEmpleado' => null,
                    'PuestoNombre' => null,
                ];
            }
        }

        // echo json_encode($datos, JSON_PRETTY_PRINT);

        return $datos;
    }
}
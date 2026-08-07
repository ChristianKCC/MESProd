<?php
require_once "..\..\conexion.php";

class ReporteDepartamentos
{
    public function getReporteDepartamentos($departamento, $fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sql = "EXEC dbo.pa_ObtenerProduccionPorTurno @NoDepto = ?, @FechaInicio = ?, @FechaFin = ?";
        $params = array($departamento, $fechai, $fechaf);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        // Avanza al resultset con columnas
        do {
            $meta = sqlsrv_field_metadata($stmt);
            if ($meta !== false && count($meta) > 0)
                break;
        } while (sqlsrv_next_result($stmt));

        // Estructura: $datosMaquinas[NoMaquina][Fecha][Turno] = row
        $datosMaquinas = [];

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $noMaq = $row['NoMaquina'];
            $fecha = $row["Fecha"]->format('Y-m-d');
            $turno = (int) $row["Turno"]; // 1,2,3

            if (!isset($datosMaquinas[$noMaq])) {
                $datosMaquinas[$noMaq] = [];
            }
            if (!isset($datosMaquinas[$noMaq][$fecha])) {
                // Inicializa los 3 turnos a NULL (o estructuras vacías)
                $datosMaquinas[$noMaq][$fecha] = [
                    1 => null,
                    2 => null,
                    3 => null,
                ];
            }

            $datosMaquinas[$noMaq][$fecha][$turno] = [
                "Fecha" => $fecha,
                "Turno" => $turno,
                "NoMaquina" => $noMaq,
                "NombreMaquina" => $row["NombreMaquina"],
                "Departamento" => $row["NombreDepto"],
                "Reales" => $row["Reales"],
                "Piezas" => $row["Piezas"],
                "USTD" => $row["USTD"],
                "TotalPiezas" => (int) $row["TotalPiezas"],
                "TotalUSTD" => (float) $row["TotalUSTDTurno"],
                "TotalReal" => (float) $row["TotalRealTurno"],
            ];
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        $salida = [];
        foreach ($datosMaquinas as $noMaq => $porFecha) {
            foreach ($porFecha as $fecha => $turnos) {
                // Garantiza 1..3 existen; si algún turno quedó null, lo rellenas con valores por defecto
                for ($t = 1; $t <= 3; $t++) {
                    if ($turnos[$t] === null) {
                        // Puedes decidir si quieres null o ceros. Para el PDF, null te permite imprimir vacío.
                        $salida[$noMaq][] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoMaquina" => $noMaq,
                            "NombreMaquina" => $turnos[1]["NombreMaquina"] ?? $turnos[2]["NombreMaquina"] ?? $turnos[3]["NombreMaquina"] ?? null,
                            "Departamento" => $turnos[1]["Departamento"] ?? $turnos[2]["Departamento"] ?? $turnos[3]["Departamento"] ?? null,
                            "Reales" => 0,
                            "Piezas" => 0,
                            "USTD" => 0.0,
                            "TotalPiezas" => 0,
                            "TotalUSTD" => 0.0,
                            "TotalReal" => 0.0,
                        ];
                    } else {
                        $salida[$noMaq][] = $turnos[$t];
                    }
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($salida, JSON_PRETTY_PRINT);
        return $salida;
    }
    public function getInfoCortesRechazos($departamento, $fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        // =========================
        // 1) Primer SP (bitácora)
        // =========================
        $sqlQuery = "EXEC dbo.usp_ObtenerBitacoraMaquinasConTotalPiezas
            @NoDepto = ?,
            @FechaInicio = ?,
            @FechaFin = ?";
        $params = array($departamento, $fechai, $fechaf);

        $stmt = sqlsrv_query($conn, $sqlQuery, $params);
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "ok" => false,
                "error" => "Error ejecutando usp_ObtenerBitacoraMaquinasConTotalPiezas",
                "details" => $errors
            ]);
            return;
        }

        // Avanza hasta el result set que tenga columnas
        do {
            $meta = sqlsrv_field_metadata($stmt);
            if ($meta !== false && count($meta) > 0) {
                break;
            }
        } while (sqlsrv_next_result($stmt));

        $map = [];
        $datosMaquinas = [];

        $makeKey = function ($fecha, $turno, $noMaquina) {
            if ($fecha instanceof DateTime) {
                $fecha = $fecha->format('Y-m-d');
            }
            return $fecha . '|' . $turno . '|' . $noMaquina;
        };

        // =========================
        // 1.a) Leer primer SP
        // =========================
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

            $cortes = isset($row["Cortes"]) ? (int) $row["Cortes"] : 0;
            $rechazos = isset($row["Rechazos"]) ? (int) $row["Rechazos"] : 0;

            // ❌ FILTRO: ignorar registros vacíos
            if ($cortes === 0 && $rechazos === 0) {
                continue;
            }

            $fechaNorm = ($row["Fecha"] instanceof DateTime)
                ? $row["Fecha"]->format('Y-m-d')
                : $row["Fecha"];

            $key = $makeKey($fechaNorm, $row["Turno"], $row["NoMaquina"]);

            $map[$key] = [
                "id" => $row["id"],
                "Fecha" => $fechaNorm,
                "Turno" => $row["Turno"],
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"],
                "NoDepto" => $row["NoDepto"],
                "NombreDepto" => $row["NombreDepto"],
                "Cortes" => $cortes,
                "Rechazos" => $rechazos,
                "TiempoAbajo" => isset($row["TiempoAbajo"]) ? (float) $row["TiempoAbajo"] : null,
                "HorasTrabajadas" => isset($row["HorasTrabajadas"]) ? (float) $row["HorasTrabajadas"] : null,
                "MermaPorc" => isset($row["MermaPorc"]) ? (float) $row["MermaPorc"] : null,
                "TiempoPerdidoPorc" => isset($row["TiempoPerdidoPorc"]) ? (float) $row["TiempoPerdidoPorc"] : null,
                "TotalPiezas" => isset($row["TotalPiezas"]) ? (int) $row["TotalPiezas"] : null,
            ];
        }
        sqlsrv_free_stmt($stmt);

        // ============================================
        // 2) Segundo SP SOLO si $departamento == 24
        // ============================================
        if ((int) $departamento === 24) {
            $sqlQuery2 = "EXEC dbo.pa_PRSD_ObtenerResumenProduccionMaquinasSinRed
                 @NoDepto = ?,
                 @FechaInicio = ?,
                 @FechaFin = ?";
            $params2 = array($departamento, $fechai, $fechaf);

            $stmt2 = sqlsrv_query($conn, $sqlQuery2, $params2);

            if ($stmt2 !== false) {

                do {
                    $meta2 = sqlsrv_field_metadata($stmt2);
                    if ($meta2 !== false && count($meta2) > 0) {
                        break;
                    }
                } while (sqlsrv_next_result($stmt2));

                while ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {

                    $cortes2 = isset($row2["Cortes"]) ? (int) $row2["Cortes"] : 0;
                    $rechazos2 = isset($row2["Rechazos"]) ? (int) $row2["Rechazos"] : 0;

                    // ❌ FILTRO
                    if ($cortes2 === 0 && $rechazos2 === 0) {
                        continue;
                    }

                    $fechaNorm2 = ($row2["Fecha"] instanceof DateTime)
                        ? $row2["Fecha"]->format('Y-m-d')
                        : $row2["Fecha"];

                    $key2 = $makeKey($fechaNorm2, $row2["Turno"], $row2["NoMaquina"]);

                    if (!isset($map[$key2])) {
                        $map[$key2] = [
                            "Fecha" => $fechaNorm2,
                            "Turno" => $row2["Turno"],
                            "NoMaquina" => $row2["NoMaquina"],
                            "NombreMaquina" => $row2["NombreMaquina"],
                            "NoDepto" => $row2["NoDepto"],
                            "NombreDepto" => $row2["NombreDepto"],
                            "Cortes" => $cortes2,
                            "Rechazos" => $rechazos2,
                            "TotalPiezas" => isset($row2["TotalPiezas"]) ? (int) $row2["TotalPiezas"] : null,
                            "HorasTrabajadas" => isset($row2["HorasTrabajadas"]) ? (float) $row2["HorasTrabajadas"] : null,
                            "TiempoAbajo" => isset($row2["TiempoAbajo"]) ? (float) $row2["TiempoAbajo"] : null,
                        ];
                    }
                }
                sqlsrv_free_stmt($stmt2);
            }
        }

        sqlsrv_close($conn);

        // ==========================
        // 3) Reagrupar por máquina
        // ==========================
        $turnosEsperados = [1, 2, 3];

        foreach ($map as $rec) {
            $maq = $rec["NoMaquina"];
            $fecha = $rec["Fecha"];
            $turno = (int) $rec["Turno"];

            if (!isset($datosMaquinas[$maq])) {
                $datosMaquinas[$maq] = [];
            }

            if (!isset($datosMaquinas[$maq][$fecha])) {
                $datosMaquinas[$maq][$fecha] = [];
            }

            $datosMaquinas[$maq][$fecha][$turno] = $rec;
        }


        // Ordenar por Fecha y Turno

        foreach ($datosMaquinas as $maq => $fechas) {
            foreach ($fechas as $fecha => $turnos) {

                // Tomamos un registro base para copiar datos fijos
                $base = reset($turnos);

                foreach ($turnosEsperados as $t) {
                    if (!isset($turnos[$t])) {

                        $datosMaquinas[$maq][$fecha][$t] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoMaquina" => $maq,
                            "NombreMaquina" => $base["NombreMaquina"],
                            "NoDepto" => $base["NoDepto"],
                            "NombreDepto" => $base["NombreDepto"],

                            // 🔽 Valores en 0
                            "Cortes" => 0,
                            "Rechazos" => 0,
                            "TotalPiezas" => 0,
                            "HorasTrabajadas" => 0,
                            "TiempoAbajo" => 0,

                            // Opcionales si existen en tu estructura
                            "MermaPorc" => 0,
                            "TiempoPerdidoPorc" => 0
                        ];
                    }
                }

                // Ordenar turnos
                ksort($datosMaquinas[$maq][$fecha]);
            }

            // Aplastar estructura Fecha → lista
            $listaFinal = [];
            foreach ($datosMaquinas[$maq] as $fecha => $turnos) {
                foreach ($turnos as $rec) {
                    $listaFinal[] = $rec;
                }
            }

            // Orden final
            usort($listaFinal, function ($a, $b) {
                $cmp = strcmp($a["Fecha"], $b["Fecha"]);
                if ($cmp !== 0)
                    return $cmp;
                return (int) $a["Turno"] <=> (int) $b["Turno"];
            });

            $datosMaquinas[$maq] = $listaFinal;
        }

        unset($lista);
        // echo json_encode($datosMaquinas, JSON_PRETTY_PRINT);
        return $datosMaquinas;
    }
    public function getInfoTiemposDeptos($departamento, $fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        // =========================
        // 1) Fuente primaria (vista con red)
        // =========================
        $sql1 = "SELECT 
                    BMTD.*,
                    tblEB.HorasTrabajadas
                FROM 
                    dbo.vw_MXPRSD_BitacoraMaquinasTiemposDeptos AS BMTD
                    INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora AS tblEB 
                        ON tblEB.Fecha = BMTD.Fecha
                        AND tblEB.Turno = BMTD.Turno
                        AND tblEB.NoMaquina = BMTD.NoMaquina
             WHERE BMTD.Fecha BETWEEN ? AND ?
               AND BMTD.NoDepto = ?
             ORDER BY BMTD.NoMaquina, BMTD.Fecha, BMTD.Turno";
        $params1 = array($fechai, $fechaf, $departamento);

        $stmt1 = sqlsrv_query($conn, $sql1, $params1);
        if ($stmt1 === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            throw new RuntimeException(
                'Error ejecutando vw_MXPRSD_BitacoraMaquinasTiemposDeptos: ' . json_encode($errors)
            );
        }

        // Estructuras de trabajo
        $map = [];           // clave Fecha|Turno|NoMaquina => registro normalizado (prioridad fuente 1)
        $datosMaquinas = []; // salida final agrupada por NoMaquina

        // Helper: normaliza fecha y arma clave
        $makeKey = function ($fecha, $turno, $noMaquina) {
            if ($fecha instanceof DateTime) {
                $fecha = $fecha->format('Y-m-d');
            }
            return $fecha . '|' . $turno . '|' . $noMaquina;
        };

        // Lee fuente primaria
        while ($row = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {
            $fechaNorm = ($row["Fecha"] instanceof DateTime)
                ? $row["Fecha"]->format('Y-m-d')
                : (is_string($row["Fecha"]) ? substr($row["Fecha"], 0, 10) : $row["Fecha"]); // fallback

            $key = $makeKey($fechaNorm, $row["Turno"], $row["NoMaquina"]);

            $map[$key] = [
                "Fecha" => $fechaNorm,
                "Turno" => $row["Turno"],
                "NoDepto" => $row["NoDepto"],
                "NombreDepto" => $row["NombreDepto"] ?? null,
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"] ?? null,
                "Cortes" => isset($row["Cortes"]) ? (int) $row["Cortes"] : null,
                "TiempoAbajo" => isset($row["TiempoAbajo"]) ? (float) $row["TiempoAbajo"] : null,
                "TiempoArriba" => isset($row["TiempoArriba"]) ? (float) $row["TiempoArriba"] : null,
                "TiempoPerdido" => isset($row["TiempoPerdido"]) ? (float) $row["TiempoPerdido"] : null,
                "HorasTrabajadas" => isset($row["HorasTrabajadas"]) ? (float) $row["HorasTrabajadas"] : null,
            ];
        }
        sqlsrv_free_stmt($stmt1);

        // =========================================
        // 2) Fuente secundaria (sin red) SOLO si 24
        //    Completar faltantes sin sobreescribir
        // =========================================
        if ((int) $departamento === 24) {
            $sql2 = "SELECT *
                 FROM dbo.vw_TiemposMaquinasSinRed
                 WHERE Fecha BETWEEN ? AND ?
                   AND NoDepto = ?
                 ORDER BY NoMaquina, Fecha DESC"; // el ORDER no afecta la fusión
            $params2 = array($fechai, $fechaf, $departamento);

            $stmt2 = sqlsrv_query($conn, $sql2, $params2);
            if ($stmt2 !== false) {
                while ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
                    $fechaNorm2 = ($row2["Fecha"] instanceof DateTime)
                        ? $row2["Fecha"]->format('Y-m-d')
                        : (is_string($row2["Fecha"]) ? substr($row2["Fecha"], 0, 10) : $row2["Fecha"]);

                    $key2 = $makeKey($fechaNorm2, $row2["Turno"], $row2["NoMaquina"]);

                    if (!isset($map[$key2])) {
                        $map[$key2] = [
                            "Fecha" => $fechaNorm2,
                            "Turno" => $row2["Turno"],
                            "NoDepto" => $row2["NoDepto"],
                            "NombreDepto" => $row2["NombreDepto"] ?? null,
                            "NoMaquina" => $row2["NoMaquina"],
                            "NombreMaquina" => $row2["NombreMaquina"] ?? null,
                            "Cortes" => isset($row2["golpes"]) ? (int) $row2["golpes"] : null,
                            "TiempoAbajo" => isset($row2["TiempoAbajo"]) ? (float) $row2["TiempoAbajo"] : null,
                            "TiempoArriba" => isset($row2["TiempoArriba"]) ? (float) $row2["TiempoArriba"] : null,
                            "HorasTrabajadas" => isset($row2["HorasTrabajadas"]) ? (float) $row2["HorasTrabajadas"] : null,
                        ];
                    }
                }
                sqlsrv_free_stmt($stmt2);
            }
            // Si falla la secundaria, no detenemos el flujo (es opcional); si prefieres lanzar excepción, avísame y lo ajustamos.
        }

        // Cerrar conexión
        sqlsrv_close($conn);

        // ==========================
        // 3) Agrupar por NoMaquina
        // ==========================
        $turnosEsperados = [1, 2, 3];

        foreach ($map as $rec) {
            $maq = $rec["NoMaquina"];
            $fecha = $rec["Fecha"];
            $turno = (int) $rec["Turno"];

            $agrupado[$maq][$fecha][$turno] = $rec;
        }


        // ==========================
        // 4) Ordenar por Fecha y Turno
        // ==========================

        foreach ($agrupado as $maq => $fechas) {
            foreach ($fechas as $fecha => $turnos) {

                // Tomar registro base para copiar info fija
                $base = reset($turnos);

                foreach ($turnosEsperados as $t) {
                    if (!isset($turnos[$t])) {
                        $turnos[$t] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoDepto" => $base["NoDepto"],
                            "NombreDepto" => $base["NombreDepto"],
                            "NoMaquina" => $base["NoMaquina"],
                            "NombreMaquina" => $base["NombreMaquina"],

                            // 🔻 Valores en cero
                            "Cortes" => 0,
                            "TiempoAbajo" => 0,
                            "TiempoArriba" => 0,
                            "TiempoPerdido" => 0,
                            "HorasTrabajadas" => 0,
                        ];
                    }
                }

                // Ordenar turnos 1,2,3
                ksort($turnos);

                foreach ($turnos as $rec) {
                    $datosMaquinas[$maq][] = $rec;
                }
            }

            // Orden final por fecha + turno
            usort($datosMaquinas[$maq], function ($a, $b) {
                $cmp = strcmp($a["Fecha"], $b["Fecha"]);
                if ($cmp !== 0)
                    return $cmp;
                return (int) $a["Turno"] <=> (int) $b["Turno"];
            });
        }


        // echo json_encode($datosMaquinas, JSON_PRETTY_PRINT);
        return $datosMaquinas;
    }
    public function getInfoClavesProduccion($departamento, $fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sqlQuery = "EXEC dbo.pa_PRSD_ObtenerClavesProduccionDepto
                    @FechaInicio = ?,
                    @FechaFin = ?,
                    @NoDepto = ?
                    ";
        $params = array($fechai, $fechaf, $departamento);
        $stmt = sqlsrv_query($conn, $sqlQuery, $params);
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        $datosClaves = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $maquina = $row['NoMaquina'];

            $registro[] = [
                "Fecha" => $row["Fecha"]->format('Y-m-d'),
                "Turno" => $row["Turno"],
                "NoDepto" => $row["NoDepto"],
                "NombreDepto" => $row["NombreDepto"],
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"],
                "Clave" => $row["Clave"],
                "Descripcion" => $row["Descripcion"],
                "Reales" => $row["acumulado"],
                "USTD" => $row["std"],
            ];

            if (!isset($datosMaquinas[$maquina])) {
                $datosMaquinas[$maquina] = [];
            }
            $datosClaves[$maquina][] = end($registro);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($datosClaves, JSON_PRETTY_PRINT);
        return $datosClaves;

    }

    public function getInfoPlanProduccion($departamento, $fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $sqlQuery = "EXEC dbo.pa_PRSD_ConsultarProduccionPlan
                 @FechaInicio = ?,
                 @FechaFin = ?,
                 @NoDepto = ?";
        $params = [$fechai, $fechaf, $departamento];
        $stmt = sqlsrv_query($conn, $sqlQuery, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $datosClaves = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $idProducto = $row['Producto'];
            $clave = trim($row['clave']);
            if (!isset($datosClaves[$idProducto][$clave])) {
                $datosClaves[$idProducto][$clave] = [
                    "Fecha" => $row["fecha"]->format('Y-m-d'),
                    "Clave" => $clave,
                    "Descripcion" => $row["Descripcion"],
                    "idProducto" => $row["Producto"],
                    "Producto" => $row["ProductoNombre"],
                    "idEtapa" => $row["Etapa"],
                    "Etapa" => $row["EtapaNombre"],
                    "idCategoria" => $row["Categoria"],
                    "Categoria" => $row["NombreCatergoria"],
                    "NoDepto" => $row["NoDepto"],
                    "NombreDepto" => $row["NombreDepto"],
                    "Reales" => 0,
                    "USTD" => 0,
                    "PlanProduccion" => 0,
                    "DiferenciaUSTD" => 0,
                ];
            }

            $datosClaves[$idProducto][$clave]["Reales"] += (float) $row["AcumuladoReales"];
            $datosClaves[$idProducto][$clave]["USTD"] += (float) $row["STDAcumulado"];
            $datosClaves[$idProducto][$clave]["PlanProduccion"] += (float) $row["PlanProduccion"];
            $datosClaves[$idProducto][$clave]["DiferenciaUSTD"] =
                $datosClaves[$idProducto][$clave]["PlanProduccion"] -
                $datosClaves[$idProducto][$clave]["USTD"];

        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        // Reindexar para que quede como arrays normales
        foreach ($datosClaves as $producto => $claves) {
            $datosClaves[$producto] = array_values($claves);
        }

        // echo json_encode($datosClaves, JSON_PRETTY_PRINT);
        return $datosClaves;
    }
    public function getReporteTelasNoTejidas($fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sql = "EXEC dbo.sp_PRSD_ObtenerDatosProduccionTNT_Conjunto @FechaInicio = ?, @FechaFin = ?";
        $params = array($fechai, $fechaf);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        // Avanza al resultset con columnas
        do {
            $meta = sqlsrv_field_metadata($stmt);
            if ($meta !== false && count($meta) > 0)
                break;
        } while (sqlsrv_next_result($stmt));

        // Estructura: $datosMaquinas[NoMaquina][Fecha][Turno] = row
        $datosMaquinas = [];

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $noMaq = $row['NoMaquina'];
            $fecha = $row["Fecha"]->format('Y-m-d');
            $turno = (int) $row["Turno"]; // 1,2,3

            if (!isset($datosMaquinas[$noMaq])) {
                $datosMaquinas[$noMaq] = [];
            }
            if (!isset($datosMaquinas[$noMaq][$fecha])) {
                // Inicializa los 3 turnos a NULL (o estructuras vacías)
                $datosMaquinas[$noMaq][$fecha] = [
                    1 => null,
                    2 => null,
                    3 => null,
                ];
            }

            $datosMaquinas[$noMaq][$fecha][$turno] = [
                "Fecha" => $fecha,
                "Turno" => $turno,
                "NoMaquina" => $noMaq,
                "NombreMaquina" => $row["NombreMaquina"],
                "Departamento" => $row["NombreDepto"],
                "AcuML" => $row["AcuML"],
                "AcMMC" => $row["AcMMC"],
                "AcKG" => $row["AcKG"],
                "TotalML" => (int) $row["TotalML"],
                "TotalMC" => (float) $row["TotalMC"],
                "TotalPeso" => (float) $row["TotalPeso"],
            ];
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        $salida = [];
        foreach ($datosMaquinas as $noMaq => $porFecha) {
            foreach ($porFecha as $fecha => $turnos) {
                // Garantiza 1..3 existen; si algún turno quedó null, lo rellenas con valores por defecto
                for ($t = 1; $t <= 3; $t++) {
                    if ($turnos[$t] === null) {
                        // Puedes decidir si quieres null o ceros. Para el PDF, null te permite imprimir vacío.
                        $salida[$noMaq][] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoMaquina" => $noMaq,
                            "NombreMaquina" => $turnos[1]["NombreMaquina"] ?? $turnos[2]["NombreMaquina"] ?? $turnos[3]["NombreMaquina"] ?? null,
                            "Departamento" => $turnos[1]["Departamento"] ?? $turnos[2]["Departamento"] ?? $turnos[3]["Departamento"] ?? null,
                            "AcuML" => 0,
                            "AcMMC" => 0,
                            "AcKG" => 0.0,
                            "TotalML" => 0,
                            "TotalMC" => 0.0,
                            "TotalPeso" => 0.0,
                        ];
                    } else {
                        $salida[$noMaq][] = $turnos[$t];
                    }
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($salida, JSON_PRETTY_PRINT);
        return $salida;
    }

    public function getInfoReporteTurnosTNT($fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sql = "EXEC dbo.sp_PRSD_ProduccionTurnosTNT_V2 @FechaInicio = ?, @FechaFin = ?";
        $params = array($fechai, $fechaf);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        // Estructuras de trabajo
        $map = [];           // clave Fecha|Turno|NoMaquina => registro normalizado (prioridad fuente 1)
        $datosTiemposTNN = [];

        // Helper: normaliza fecha y arma clave
        $makeKey = function ($fecha, $turno, $noMaquina) {
            if ($fecha instanceof DateTime) {
                $fecha = $fecha->format('Y-m-d');
            }
            return $fecha . '|' . $turno . '|' . $noMaquina;
        };


        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $fechaNorm = ($row["Fecha"] instanceof DateTime)
                ? $row["Fecha"]->format('Y-m-d')
                : (is_string($row["Fecha"]) ? substr($row["Fecha"], 0, 10) : $row["Fecha"]); // fallback

            $key = $makeKey($fechaNorm, $row["Turno"], $row["NoMaquina"]);
            $map[$key] = [
                "Fecha" => $fechaNorm,
                "Turno" => $row["Turno"],
                "NoDepto" => $row["NoDepto"],
                "NombreDepto" => $row["NombreDepto"] ?? null,
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"] ?? null,
                "HorasTrabajadas" => isset($row["HorasTrabajadas"]) ? (float) $row["HorasTrabajadas"] : null,
                "TiempoAbajo" => isset($row["TiempoAbajo"]) ? (float) $row["TiempoAbajo"] : null,
                "AccMLineales" => isset($row["AccMLineales"]) ? (float) $row["AccMLineales"] : null,
                "AccKG" => isset($row["AccKG"]) ? (float) $row["AccKG"] : null,
                "Rechazos" => isset($row["Rechazos"]) ? (float) $row["Rechazos"] : null,
                "AccMMCuadrados" => isset($row["AccMMCuadrados"]) ? (float) $row["AccMMCuadrados"] : null,
            ];

        }
        sqlsrv_free_stmt($stmt);
        // Cerrar conexión
        sqlsrv_close($conn);
        $turnosEsperados = [1, 2, 3];

        foreach ($map as $rec) {
            $maq = $rec["NoMaquina"];
            $fecha = $rec["Fecha"];
            $turno = (int) $rec["Turno"];

            $agrupado[$maq][$fecha][$turno] = $rec;
        }

        foreach ($agrupado as $maq => $fechas) {
            foreach ($fechas as $fecha => $turnos) {

                // Tomar registro base para copiar info fija
                $base = reset($turnos);

                foreach ($turnosEsperados as $t) {
                    if (!isset($turnos[$t])) {
                        $turnos[$t] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoDepto" => $base["NoDepto"],
                            "NombreDepto" => $base["NombreDepto"],
                            "NoMaquina" => $base["NoMaquina"],
                            "NombreMaquina" => $base["NombreMaquina"],

                            // 🔻 Valores en cero
                            "TiempoAbajo" => 0,
                            "AccMLineales" => 0,
                            "AccKG" => 0,
                            "Rechazos" => 0,
                            "AccMMCuadrados" => 0,
                            "HorasTrabajadas" => 0,
                        ];
                    }
                }

                // Ordenar turnos 1,2,3
                ksort($turnos);

                foreach ($turnos as $rec) {
                    $datosMaquinas[$maq][] = $rec;
                }
            }

            // Orden final por fecha + turno
            usort($datosMaquinas[$maq], function ($a, $b) {
                $cmp = strcmp($a["Fecha"], $b["Fecha"]);
                if ($cmp !== 0)
                    return $cmp;
                return (int) $a["Turno"] <=> (int) $b["Turno"];
            });
        }


        // echo json_encode($datosMaquinas, JSON_PRETTY_PRINT);
        return $datosMaquinas;


    }
    public function getInfoClavesTNT($fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sqlQuery = "EXEC dbo.sp_PRSD_ObtenerClaveProduccionTNT_V2
                    @FechaInicio = ?,
                    @FechaFin = ?";

        $params = array($fechai, $fechaf);
        $stmt = sqlsrv_query($conn, $sqlQuery, $params);
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        $datosClaves = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $maquina = $row['NoMaquina'];

            $registro[] = [
                "Fecha" => $row["Fecha"]->format('Y-m-d'),
                "Turno" => $row["Turno"],
                "NoDepto" => $row["NoDepto"],
                "NombreDepto" => $row["NombreDepto"],
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"],
                "Clave" => $row["Clave"],
                "Descripcion" => $row["Descripcion"],
                "AcMMCuadrados" => $row["AcMCuadrados"],
                "AcKilos" => $row["AcKilos"],
            ];

            if (!isset($datosMaquinas[$maquina])) {
                $datosMaquinas[$maquina] = [];
            }
            $datosClaves[$maquina][] = end($registro);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        header('Content-Type: application/json; charset=utf-8');
        // echo json_encode($datosClaves, JSON_PRETTY_PRINT);
        return $datosClaves;

    }

    public function getTiemposTNT($fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $sqlQuery = "SELECT *
                        FROM dbo.vw_MXPRSD_ReporteTNT_Tiempos
                        WHERE Fecha BETWEEN ? AND ?";
        $params = array($fechai, $fechaf);
        $stmt = sqlsrv_query($conn, $sqlQuery, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return;
        }

        // Estructuras de trabajo
        $map = [];           // clave Fecha|Turno|NoMaquina => registro normalizado (prioridad fuente 1)
        $datosTiemposTNN = [];

        // Helper: normaliza fecha y arma clave
        $makeKey = function ($fecha, $turno, $noMaquina) {
            if ($fecha instanceof DateTime) {
                $fecha = $fecha->format('Y-m-d');
            }
            return $fecha . '|' . $turno . '|' . $noMaquina;
        };


        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $fechaNorm = ($row["Fecha"] instanceof DateTime)
                ? $row["Fecha"]->format('Y-m-d')
                : (is_string($row["Fecha"]) ? substr($row["Fecha"], 0, 10) : $row["Fecha"]); // fallback

            $key = $makeKey($fechaNorm, $row["Turno"], $row["NoMaquina"]);
            $map[$key] = [
                "Fecha" => $fechaNorm,
                "Turno" => $row["Turno"],
                "NoDepto" => $row["NoDepto"],
                "NombreDepto" => $row["NombreDepto"] ?? null,
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"] ?? null,
                "TotalML" => isset($row["TotalML"]) ? (int) $row["TotalML"] : null,
                "TiempoAbajo" => isset($row["TiempoAbajo"]) ? (float) $row["TiempoAbajo"] : null,
                "TiempoArriba" => isset($row["TiempoArriba"]) ? (float) $row["TiempoArriba"] : null,
                "HorasTrabajadas" => isset($row["HorasTrabajadas"]) ? (float) $row["HorasTrabajadas"] : null,
            ];

        }
        sqlsrv_free_stmt($stmt);
        // Cerrar conexión
        sqlsrv_close($conn);
        $turnosEsperados = [1, 2, 3];

        foreach ($map as $rec) {
            $maq = $rec["NoMaquina"];
            $fecha = $rec["Fecha"];
            $turno = (int) $rec["Turno"];

            $agrupado[$maq][$fecha][$turno] = $rec;
        }

        foreach ($agrupado as $maq => $fechas) {
            foreach ($fechas as $fecha => $turnos) {

                // Tomar registro base para copiar info fija
                $base = reset($turnos);

                foreach ($turnosEsperados as $t) {
                    if (!isset($turnos[$t])) {
                        $turnos[$t] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoDepto" => $base["NoDepto"],
                            "NombreDepto" => $base["NombreDepto"],
                            "NoMaquina" => $base["NoMaquina"],
                            "NombreMaquina" => $base["NombreMaquina"],

                            // 🔻 Valores en cero
                            "TotalML" => 0,
                            "TiempoAbajo" => 0,
                            "TiempoArriba" => 0,
                            "HorasTrabajadas" => 0,
                        ];
                    }
                }

                // Ordenar turnos 1,2,3
                ksort($turnos);

                foreach ($turnos as $rec) {
                    $datosMaquinas[$maq][] = $rec;
                }
            }

            // Orden final por fecha + turno
            usort($datosMaquinas[$maq], function ($a, $b) {
                $cmp = strcmp($a["Fecha"], $b["Fecha"]);
                if ($cmp !== 0)
                    return $cmp;
                return (int) $a["Turno"] <=> (int) $b["Turno"];
            });
        }


        // echo json_encode($datosMaquinas, JSON_PRETTY_PRINT);
        return $datosMaquinas;
    }

    public function getInfoPlanProduccionTNT($fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $sqlQuery = "EXEC dbo.pa_PRSD_ConsultarProduccionPlanTNT_V2
                 @FechaInicio = ?,
                 @FechaFin = ?";
        $params = [$fechai, $fechaf];
        $stmt = sqlsrv_query($conn, $sqlQuery, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $datosClaves = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $idProducto = $row['Producto'];
            $clave = trim($row['clave']);

            if (!isset($datosClaves[$idProducto][$clave])) {
                $datosClaves[$idProducto][$clave] = [
                    "Fecha" => $row["fecha"]->format('Y-m-d'),
                    "Clave" => $clave,
                    "Descripcion" => $row["Descripcion"],
                    "idProducto" => $row["Producto"],
                    "Producto" => $row["ProductoNombre"],
                    "idEtapa" => $row["Etapa"],
                    "Etapa" => $row["EtapaNombre"],
                    "idCategoria" => $row["Categoria"],
                    "Categoria" => $row["NombreCatergoria"],
                    "NoDepto" => $row["NoDepto"],
                    "NombreDepto" => $row["NombreDepto"],
                    "TotalKilos" => 0,
                    "TotalMMC" => 0,
                    "PlanProduccion" => 0,
                    "DiferenciaMMC" => 0,
                ];
            }

            // Kg ya viene redondeado por bajada desde el SP
            // (pa_PRSD_ConsultarProduccionPlanTNT_V2), así que aquí solo
            // se suma directo. MMC y PlanProduccion sin cambios.
            $datosClaves[$idProducto][$clave]["TotalKilos"] += (float) $row["TotalKilos"];
            $datosClaves[$idProducto][$clave]["TotalMMC"] += (float) $row["TotalMMC"];
            $datosClaves[$idProducto][$clave]["PlanProduccion"] += (float) $row["PlanProduccion"];
            $datosClaves[$idProducto][$clave]["DiferenciaMMC"] =
                $datosClaves[$idProducto][$clave]["PlanProduccion"] -
                $datosClaves[$idProducto][$clave]["TotalMMC"];
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Reindexar para que quede como arrays normales
        foreach ($datosClaves as $producto => $claves) {
            $datosClaves[$producto] = array_values($claves);
        }

        // echo json_encode($datosClaves, JSON_PRETTY_PRINT);
        return $datosClaves;
    }

    public function getInfoParosMaquinas($departamento, $fechai, $fechaf)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if (!$conn) {
            $errors = sqlsrv_errors();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error conectando a BD", "details" => $errors]);
            return [];
        }

        $sqlQuery = "EXEC dbo.sp_PRSD_ReporteParosMaquinas
                 @FechaInicio = ?,
                 @FechaFin = ?,
                 @NoDepto = ?";

        $params = array($fechai, $fechaf, $departamento);
        $stmt = sqlsrv_query($conn, $sqlQuery, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sqlsrv_close($conn);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["ok" => false, "error" => "Error ejecutando query", "details" => $errors]);
            return [];
        }

        // Estructura: $datosMaquinas[NoMaquina][Fecha][Turno] = row
        $datosMaquinas = [];

        while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $noMaq = $fila['NoMaquina'];
            // Convertir fecha si viene como DateTime object
            $fecha = is_object($fila["Fecha"])
                ? $fila["Fecha"]->format('Y-m-d')
                : $fila["Fecha"];
            $turno = (int) $fila["Turno"];

            // Inicializar estructura de máquina si no existe
            if (!isset($datosMaquinas[$noMaq])) {
                $datosMaquinas[$noMaq] = [];
            }

            // Inicializar estructura de fecha con 3 turnos vacíos
            if (!isset($datosMaquinas[$noMaq][$fecha])) {
                $datosMaquinas[$noMaq][$fecha] = [
                    1 => null,
                    2 => null,
                    3 => null,
                ];
            }

            // Guardar el registro en el turno correspondiente
            $datosMaquinas[$noMaq][$fecha][$turno] = [
                "Fecha" => $fecha,
                "Turno" => $turno,
                "NoDepto" => $fila["NoDepto"],
                "NombreDepto" => $fila["NombreDepto"],
                "NoMaquina" => $noMaq,
                "NombreMaquina" => $fila["NombreMaquina"],
                "MinutosTurno" => $fila["MinutosTurno"],
                "TiempoAbajo" => $fila["TiempoAbajo"],
                "TiempoArriba" => $fila["TiempoArriba"],
                "ParosMaquinaTurno" => $fila["ParosMaquinaTurno"],
                "Origen" => $fila["Origen"] ?? null,
            ];
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        // Si no hay datos, retornar array vacío
        if (empty($datosMaquinas)) {
            return [];
        }

        // Generar array AGRUPADO POR MÁQUINA - Estructura: $salida["68"] = [registros...]
        $salida = [];

        // Ordenar máquinas numéricamente
        ksort($datosMaquinas, SORT_NUMERIC);

        foreach ($datosMaquinas as $noMaq => $porFecha) {
            // Inicializar array de registros para esta máquina
            $registrosMaquina = [];

            // Ordenar fechas
            ksort($porFecha);

            foreach ($porFecha as $fecha => $turnos) {
                // Garantiza que existan turnos 1, 2, 3
                for ($t = 1; $t <= 3; $t++) {
                    if ($turnos[$t] === null) {
                        // Turno vacío: tomar datos de referencia de otro turno
                        $nombreMaq = null;
                        $nombreDepto = null;
                        $noDepto = null;

                        for ($i = 1; $i <= 3; $i++) {
                            if ($turnos[$i] !== null) {
                                $nombreMaq = $turnos[$i]["NombreMaquina"];
                                $nombreDepto = $turnos[$i]["NombreDepto"];
                                $noDepto = $turnos[$i]["NoDepto"];
                                break;
                            }
                        }

                        // Crear registro vacío para este turno
                        $registrosMaquina[] = [
                            "Fecha" => $fecha,
                            "Turno" => $t,
                            "NoDepto" => $noDepto,
                            "NombreDepto" => $nombreDepto,
                            "NoMaquina" => $noMaq,
                            "NombreMaquina" => $nombreMaq,
                            "MinutosTurno" => 0,
                            "TiempoAbajo" => 0,
                            "TiempoArriba" => 0,
                            "ParosMaquinaTurno" => 0,
                            "Origen" => null,
                        ];
                    } else {
                        // Turno con datos
                        $registrosMaquina[] = $turnos[$t];
                    }
                }
            }

            // Agregar máquina al resultado (clave como string)
            $salida[(string) $noMaq] = $registrosMaquina;
        }
        // echo json_encode($salida, JSON_PRETTY_PRINT);

        return $salida;
    }


}
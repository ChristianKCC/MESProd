<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
require_once(__DIR__ . "/../../BDNominas/config.php"); // Carga de datos para datos de GERENTE
require_once(__DIR__ . "/deptosSupervisor.php");

class Tiempoextra
{
    // Funcion para crear registro de tiempo extra 
    // Crear registro en donde se autoriza o no y se sabe quien lo hace (supervisor es la misma persona que abre el registro)

    // Al abrir el registro se asigna el supervisor que lo abre, la fecha, el departamento, la fecha de guardado y el noemp del gerente que debe autorizarlo
    function abrirtiempoextra()
    {
        header('Content-Type: application/json');
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $supervisor = $_SESSION["ibm"];
        $fecha = $_POST["fechaenc"];
        $departamentoenc = $_POST["departamentoenc"];

        $semana = date("W", strtotime($fecha)); // número de semana ISO

        // Resolver el nombre del depto (NoDepto -> NombreDepto) para ubicar
        // al gerente/superintendente CORRECTO cuando el supervisor tiene varios deptos
        $nombreDepto = nombreDeptoPorNo($departamentoenc);

        // Buscar jefe inmediato en CSV        
        $datosJefes = $this->buscarJefeInmediato($supervisor, $nombreDepto);
        $GerNum   = $datosJefes["jefe"];
        $Superint = $datosJefes["superintendente"];
            
        // $datosJefes = $this->buscarJefeInmediato($supervisor);
        // $GerNum = $datosJefes["jefe"];
        // $Superint = $datosJefes["superintendente"];

        if (!$GerNum) {
            error_log("No se encontró jefe inmediato para supervisor=" . $supervisor);
            echo json_encode([
                "error" => "No se encontró jefe inmediato en BD Nóminas. Verifica que tu IBM esté registrado o que tenga jefe inmediato asignado."
            ]);
            exit;
        }
        error_log("Jefe inmediato encontrado=" . $GerNum);

        // Construccion de query para el registro del tiempo extra, 
        // se guarda el registro con el supervisor que lo creo, 
        // la fecha que se solicita, el departamento al que pertenece, la fecha de guardado y el numero de empleado del gerente que debe autorizarlo
        // $query = "INSERT INTO TiempoextraEnc(supervisor,fecha,departamento,datesave,noempautoriza)
        //         VALUES (?, ?, ?, GETDATE(), ?); SELECT SCOPE_IDENTITY() AS id;";
        // $params = [$supervisor, $fecha, $departamentoenc, $GerNum];
        $query = "INSERT INTO TiempoextraEnc(
                supervisor,
                fecha,
                departamento,
                datesave,
                noempautoriza,
                noempSupIntendente
              )
              VALUES (?, ?, ?, GETDATE(), ?, ?);
              SELECT SCOPE_IDENTITY() AS id;";
        $params = [$supervisor, $fecha, $departamentoenc, $GerNum, $Superint];

        $result = sqlsrv_query($conn, $query, $params);
        if ($result === false) {
            echo json_encode(["error" => sqlsrv_errors()]);
            exit;
        }

        sqlsrv_next_result($result);
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

         // Devolver id y semana en un JSON válido
        echo json_encode(["id" => $row['id'], "semana" => $semana]);
    }

    // function buscarJefeInmediato(string $ibmSupervisor): array {
    //     $resultado = ["jefe" => null, "superintendente" => null];

    //     if (!file_exists(CSV_NOMINAS_FILE)) {
    //         error_log("CSV no encontrado en: " . CSV_NOMINAS_FILE);
    //         return $resultado;
    //     }
    //     $handle = fopen(CSV_NOMINAS_FILE, "r");
    //     if (!$handle) {
    //         error_log("No se pudo abrir el CSV");
    //         return $resultado;
    //     }

    //     $bom = fread($handle, 3);
    //     if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    //     $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    //     if (!$headers) { fclose($handle); return $resultado; }

    //     $headers = array_map(function($h) {
    //         return preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
    //     }, $headers);

    //     error_log("Buscando supervisor IBM=" . $ibmSupervisor);

    //     while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
    //         if (array_filter($line) === []) continue;

    //         if (count($line) < count($headers)) {
    //             $line = array_pad($line, count($headers), '');
    //         } elseif (count($line) > count($headers)) {
    //             $line = array_slice($line, 0, count($headers));
    //         }

    //         $row = @array_combine($headers, $line);
    //         if (!$row) continue;

    //         $num       = trim($row[COL_NUMERO] ?? '');
    //         $idJefe    = trim($row[COL_ID_JEFE] ?? '');
    //         $superint  = trim($row[COL_IBM] ?? '');

    //         if ($num !== '' && $num === trim($ibmSupervisor)) {
    //             if ($idJefe !== '') {
    //                 error_log("Supervisor $ibmSupervisor tiene jefe inmediato: $idJefe");
    //                 $resultado["jefe"] = $idJefe;
    //             } else {
    //                 error_log("Supervisor $ibmSupervisor encontrado pero sin jefe inmediato asignado");
    //             }

    //             if ($superint !== '') {
    //                 error_log("Supervisor $ibmSupervisor tiene superintendente asignado: $superint");
    //                 $resultado["superintendente"] = $superint;
    //             } else {
    //                 error_log("Supervisor $ibmSupervisor no tiene superintendente asignado");
    //             }

    //             break;
    //         }
    //     }
    //     fclose($handle);

    //     if ($resultado["jefe"] === null) {
    //         error_log("No se encontró coincidencia para IBM=" . $ibmSupervisor);
    //     }

    //     return $resultado;
    // }

    function buscarJefeInmediato(string $ibmSupervisor, string $nombreDepto = ''): array {
        $resultado = ["jefe" => null, "superintendente" => null];

        if (!file_exists(CSV_NOMINAS_FILE)) {
            error_log("CSV no encontrado en: " . CSV_NOMINAS_FILE);
            return $resultado;
        }
        $handle = fopen(CSV_NOMINAS_FILE, "r");
        if (!$handle) {
            error_log("No se pudo abrir el CSV");
            return $resultado;
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
        if (!$headers) { fclose($handle); return $resultado; }

        $headers = array_map(function($h) {
            return preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
        }, $headers);

        $deptoNorm = normalizarDepto($nombreDepto); // objetivo (puede venir vacío)
        $fallback  = null;                            // 1er match por IBM (respaldo)

        error_log("Buscando supervisor IBM=$ibmSupervisor depto='$deptoNorm'");

        while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
            if (array_filter($line) === []) continue;

            if (count($line) < count($headers)) {
                $line = array_pad($line, count($headers), '');
            } elseif (count($line) > count($headers)) {
                $line = array_slice($line, 0, count($headers));
            }

            $row = @array_combine($headers, $line);
            if (!$row) continue;

            $num = trim($row[COL_NUMERO] ?? '');
            if ($num === '' || $num !== trim($ibmSupervisor)) continue; // no es este supervisor

            $idJefe    = trim($row[COL_ID_JEFE] ?? '');
            $superint  = trim($row[COL_IBM] ?? '');
            $deptoFila = normalizarDepto($row[COL_DEPTO] ?? '');

            $fila = [
                "jefe"            => $idJefe   !== '' ? $idJefe   : null,
                "superintendente" => $superint !== '' ? $superint : null
            ];

            // Coincidencia exacta supervisor + departamento -> gerente correcto
            if ($deptoNorm !== '' && $deptoFila === $deptoNorm) {
                error_log("Match exacto supervisor=$ibmSupervisor depto=$deptoNorm jefe=$idJefe superint=$superint");
                fclose($handle);
                return $fila;
            }

            // Primer match por IBM como respaldo
            if ($fallback === null) $fallback = $fila;
        }
        fclose($handle);

        // Sin depto o sin coincidencia exacta -> respaldo por IBM (comportamiento anterior)
        if ($fallback !== null) {
            if ($deptoNorm !== '') {
                error_log("Sin match exacto de depto para $ibmSupervisor; usando respaldo por IBM");
            }
            return $fallback;
        }

        error_log("No se encontró coincidencia para IBM=" . $ibmSupervisor);
        return $resultado;
    }

    // Funcion para sacar los motivos existentes de tiempos extra para forma
    function motivostiempoextra()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        // Query para consulta general de tiempos extra
        $query = "SELECT * FROM Tiempoextramotivos";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        // Parseo de datos a array con keys para su identificacion
        while ($row = sqlsrv_fetch_array($result))
            array_push($array, ["id" => $row[0], "nombre" => $row[1]]);
        echo json_encode($array);
    }

    function tblDeptosDinam() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $query = "SELECT DISTINCT(Vcs_depto) AS departamentos FROM tblMXPRVacacionesSubEnc";
        $result = sqlsrv_query($conn, $query);
        $array = [];

        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = ["id" => $row[0], "nombre" => $row[0]];
        }
        echo json_encode($array);
    }

    function guardartiempoextra()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $noemp      = $_POST["noemp"];
        $folio      = $_POST["folio"];
        $fechainput = $_POST["fechainput"];
        $horai      = $_POST["horai"];
        $horaf      = $_POST["horaf"];
        $motivos    = $_POST["motivos"];
        $maquina    = $_POST["maquina"];
        $razon      = $_POST["razon"];
        $turnosel   = $_POST["turnosel"];
        $nombre     = $_POST["nombre"];

        // ── Flag: registro excedente generado automáticamente ────────────────────
        // Si es excedente, saltar validaciones (duplicados, 60.5 hrs, doblete)
        // porque es una consecuencia del cálculo, no un registro voluntario.
        $esExcedente = isset($_POST['esExcedente']) && $_POST['esExcedente'] === '1';

        if (!$esExcedente) {

            // ── Validación de duplicados ──────────────────────────────────────────
            $valdup = "SELECT motivo
                    FROM TiempoextraSubEnc
                    WHERE noemp = $noemp AND folio = $folio AND fecha = '$fechainput'";
            $resvalidador = sqlsrv_query($conn, $valdup);

            $motivosExistentes = [];
            while ($rowExist = sqlsrv_fetch_array($resvalidador, SQLSRV_FETCH_ASSOC)) {
                $motivosExistentes[] = (int)$rowExist['motivo'];
            }
            $exist = count($motivosExistentes);

            if ($exist >= 3) {
                echo json_encode("Existe");
                return false;
            }

            if ($exist == 1) {
                // Con un registro, cualquier motivo es válido
            }

            if ($exist == 2) {
                $m1 = $motivosExistentes[0];
                $m2 = $motivosExistentes[1];

                if ((in_array(10, [$m1,$m2]) && ($m1 != $m2) && !in_array(8, [$m1,$m2]))) {
                    // permitido
                } elseif ((in_array(8, [$m1,$m2]) && ($m1 != $m2) && !in_array(10, [$m1,$m2]))) {
                    // permitido
                } else {
                    echo json_encode("Existe");
                    return false;
                }

                $motivosSimulados   = $motivosExistentes;
                $motivosSimulados[] = (int)$motivos;

                if (count($motivosSimulados) == 3) {
                    sort($motivosSimulados);
                    $tiene8  = in_array(8,  $motivosSimulados);
                    $tiene10 = in_array(10, $motivosSimulados);
                    $tieneX  = count(array_filter($motivosSimulados, fn($m) => $m != 8 && $m != 10)) == 1;
                    if (!($tiene8 && $tiene10 && $tieneX)) {
                        echo json_encode("Existe");
                        return false;
                    }
                }
            }

            // ── Horas base por turno ──────────────────────────────────────────────
            $horasTurno = [
                "turno1" => 48, "turno2" => 45, "turno3" => 51,
                "mixto1" => 48, "mixto2" => 48, "mixto3" => 48, "mixto4" => 48
            ];

            // ── Validación de 60.5 hrs semanales ─────────────────────────────────
            $queryTotal = "SELECT SUM(DATEDIFF(MINUTE, horai, horaf)) AS totalMinutos
                FROM TiempoextraSubEnc
                WHERE folio = ? AND noemp = ?";
            $paramsTotal = [$folio, $noemp];
            $resTotal    = sqlsrv_query($conn, $queryTotal, $paramsTotal);
            $rowTotal    = sqlsrv_fetch_array($resTotal, SQLSRV_FETCH_ASSOC);
            $totalMinutos          = $rowTotal['totalMinutos'] ?? 0;
            $totalHorasRegistradas = $totalMinutos / 60;

            $horaiObj    = new DateTime($horai);
            $horafObj    = new DateTime($horaf);
            $nuevasHoras = ($horafObj->getTimestamp() - $horaiObj->getTimestamp()) / 3600;
            $horasBase   = $horasTurno[$turnosel] ?? 0;
            $totalFinal  = $horasBase + $totalHorasRegistradas + $nuevasHoras;

            // ── Nombre del turno para mensajes ────────────────────────────────────
            $turnoNuevoFor = "";
            if     ($turnosel === null || $turnosel === "") $turnoNuevoFor = 'Sin turno';
            elseif ($turnosel === "turno1")   $turnoNuevoFor = '1er Turno';
            elseif ($turnosel === "turno2")   $turnoNuevoFor = '2do Turno';
            elseif ($turnosel === "turno3")   $turnoNuevoFor = '3er Turno';
            elseif ($turnosel === "mixto1")   $turnoNuevoFor = '1er Mixto';
            elseif ($turnosel === "mixto2")   $turnoNuevoFor = '2do Mixto';
            elseif ($turnosel === "mixto3")   $turnoNuevoFor = '3er Mixto';
            elseif ($turnosel === "mixto4")   $turnoNuevoFor = '4to Mixto';

            // ── Validación de doblete (lógica original conservada) ────────────────
            $queryPrev = "SELECT TOP 1 fecha, horai, horaf, turnoAsignado
                FROM TiempoextraSubEnc
                WHERE folio = ? AND noemp = ?
                ORDER BY fecha DESC, horai DESC";
            $paramsPrev = [$folio, $noemp];
            $resPrev    = sqlsrv_query($conn, $queryPrev, $paramsPrev);
            $rowPrev    = sqlsrv_fetch_array($resPrev, SQLSRV_FETCH_ASSOC);

            $esDoblete  = false;
            $gapMinutos = 0;

            $turnos = [
                'turno1'       => ['inicio' => '07:00:00', 'fin' => '15:00:00'],
                'turno2'       => ['inicio' => '15:00:00', 'fin' => '22:30:00'],
                'turno3'       => ['inicio' => '22:30:00', 'fin' => '07:00:00'],
                'turno3_12hrs' => ['inicio' => '19:00:00', 'fin' => '07:00:00'],
                'turno2_12hrs' => ['inicio' => '11:30:00', 'fin' => '03:00:00'],
                'mixto1'       => ['inicio' => '07:30:00', 'fin' => '17:00:00'],
                'mixto2'       => ['inicio' => '08:30:00', 'fin' => '18:30:00'],
                'mixto3'       => ['inicio' => '07:00:00', 'fin' => '16:30:00'],
                'mixto4'       => ['inicio' => '07:00:00', 'fin' => '17:00:00'],
            ];

            $turnoNuevo = $_POST["turnosel"];

            if ($rowPrev) {
                $prevInicio = new DateTime(
                    $rowPrev['fecha']->format('Y-m-d') . ' ' . $rowPrev['horai']->format('H:i:s')
                );
                $prevFin = new DateTime(
                    $rowPrev['fecha']->format('Y-m-d') . ' ' . $rowPrev['horaf']->format('H:i:s')
                );

                if ($prevFin < $prevInicio) {
                    $prevFin->modify('+1 day');
                }

                if ($rowPrev['turnoAsignado'] === 'turno3_12hrs') {
                    $prevFin = new DateTime($rowPrev['fecha']->format('Y-m-d') . ' 07:00:00');
                    $prevFin->modify('+1 day');
                }

                $nuevoInicioTurno = new DateTime($fechainput . ' ' . $turnos[$turnoNuevo]['inicio']);

                if ($nuevoInicioTurno < $prevFin) {
                    $nuevoInicioTurno->modify('+1 day');
                }

                $gapMinutos = ($nuevoInicioTurno->getTimestamp() - $prevFin->getTimestamp()) / 60;

                error_log(
                    "DEBUG DOBLETE:" .
                    " prevHorai="        . $prevInicio->format('Y-m-d H:i:s') .
                    " prevHoraf="        . $prevFin->format('Y-m-d H:i:s') .
                    " nuevoInicioTurno=" . $nuevoInicioTurno->format('Y-m-d H:i:s') .
                    " gapMinutos="       . $gapMinutos
                );

                if ($gapMinutos >= 360 && $gapMinutos <= 600) {
                    $esDoblete = true;
                }
            }

            // ── Warnings ──────────────────────────────────────────────────────────
            $warnings = [];

            if ($totalFinal > 60.5) {
                $warnings[] = "excede las 60.5 horas semanales (Horas en total: $totalFinal hrs)";
            }

            if ($esDoblete) {
                $warnings[] = "genera un DOBLETE (descanso de $gapMinutos minutos entre turnos)";
            }

            if (!empty($warnings)) {
                $mensaje = "El registro para $nombre con el turno $turnoNuevoFor "
                        . implode(" y ", $warnings) . ". ¿Desea continuar?";
                echo json_encode(["warning" => true, "message" => $mensaje]);
                return;
            }

        } // ── fin if (!$esExcedente) ──────────────────────────────────────────────

        // ── INSERT — aplica siempre (excedente o no) ──────────────────────────────
        $query = "INSERT INTO TiempoextraSubEnc(noemp,folio,fecha,horai,horaf,maquina,motivo,razon,turnoAsignado)
                VALUES ('$noemp','$folio','$fechainput','$horai','$horaf','$maquina','$motivos','$razon','$turnosel')";

        $result = sqlsrv_query($conn, $query);

        if ($result === false) {
            echo json_encode("sqlerror");
            return;
        }

        // ── Si es excedente, marcar como validado automáticamente ─────────────────
        if ($esExcedente) {
            $idQuery = sqlsrv_query($conn, "SELECT SCOPE_IDENTITY() AS id");
            $idRow   = sqlsrv_fetch_array($idQuery, SQLSRV_FETCH_ASSOC);
            $nuevoId = $idRow['id'] ?? null;

            if ($nuevoId) {
                sqlsrv_query($conn, "UPDATE TiempoextraSubEnc SET validado = 1 WHERE id = $nuevoId");
            }
        }

        echo json_encode("Listo");
    }

    // Funcion de prueba para registro de tiempo extra aun con advertencia
    function guardartiempoextraExt()
    {
        // Instancia de conexion a la BD
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        // Obtencion de datos segun form
        $noemp = $_POST["noemp"];
        $folio = $_POST["folio"];
        $fechainput = $_POST["fechainput"];
        $horai = $_POST["horai"];
        $horaf = $_POST["horaf"];
        $motivos = $_POST["motivos"];
        $maquina = $_POST["maquina"];
        $razon = $_POST["razon"];
        $turnosel = $_POST["turnosel"];

        // Obtener todos los motivos existentes
        $valdup = "SELECT motivo 
                FROM TiempoextraSubEnc 
                WHERE noemp = $noemp AND folio = $folio AND fecha = '$fechainput'";
        $resvalidador = sqlsrv_query($conn, $valdup);

        $motivosExistentes = [];
        while ($rowExist = sqlsrv_fetch_array($resvalidador, SQLSRV_FETCH_ASSOC)) {
            $motivosExistentes[] = (int)$rowExist['motivo'];
        }
        $exist = count($motivosExistentes);

        // Reglas
        if ($exist >= 3) {
            echo json_encode("Existe");
            return false;
        }

        if ($exist == 1) {
            // Con un registro, cualquier motivo es válido
        }

        if ($exist == 2) {
            $m1 = $motivosExistentes[0];
            $m2 = $motivosExistentes[1];

            // Caso válido: un 10 y un X
            if ((in_array(10, [$m1,$m2]) && ($m1 != $m2) && !in_array(8, [$m1,$m2]))) {
                // permitido
            }
            // Caso válido: un 8 y un X
            elseif ((in_array(8, [$m1,$m2]) && ($m1 != $m2) && !in_array(10, [$m1,$m2]))) {
                // permitido
            }
            else {
                // cualquier otra combinación (dos X, 10+8, dos iguales) → bloquear
                echo json_encode("Existe");
                return false;
            }

            // Si se intenta agregar un tercer registro
            $motivosSimulados = $motivosExistentes;
            $motivosSimulados[] = (int)$motivos;

            if (count($motivosSimulados) == 3) {
                sort($motivosSimulados);
                // combinación exacta debe ser [8,10,X]
                $tiene8 = in_array(8, $motivosSimulados);
                $tiene10 = in_array(10, $motivosSimulados);
                $tieneX = count(array_filter($motivosSimulados, fn($m)=>$m!=8 && $m!=10)) == 1;

                if (!($tiene8 && $tiene10 && $tieneX)) {
                    echo json_encode("Existe");
                    return false;
                }
            }
        }

        // Construccion de query con el insert para el ingreso de datos
        // Detalles del tiempo extra -> Con el folio asociado a TiempoextraEnc
        $query = "INSERT INTO TiempoextraSubEnc(noemp,folio,fecha,horai,horaf,maquina,motivo,razon, turnoAsignado) 
                VALUES ('$noemp','$folio','$fechainput','$horai','$horaf','$maquina','$motivos','$razon','$turnosel')";

        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode("sqlerror") : json_encode("Listo");
    }   
    

    // Obtener los datos de horas extras de x folio seleccionado si es que existen
    function tblsubenc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $folio = $_GET["folio"];
        // Construccion de Query principal para obtener
        // id / noemp / Nombre / depto / puesto / fecha / horai / horaf / NombreMaquina / razon / motivo
        // #  /  #    /    #   /  TNT  / Tecnico electrico / 2025-07-# / 17:00:00 / 18:30:00 / VFL / OTRO 
        // La consulta se estructura respecto a un folio
        $query = "SELECT 
            TiempoextraSubEnc.id,
            TiempoextraSubEnc.noemp,
            tblEmpleados.Nombre,
            tblDepartamentos.NombreDepto as depto,
            tblPuestos.nombre as puesto,
            TiempoextraSubEnc.fecha,
            TiempoextraSubEnc.horai,
            TiempoextraSubEnc.horaf,
            tblMaquinas.NombreMaquina,
            TiempoextraSubEnc.razon,
            TiempoextraSubEnc.turnoAsignado,
            Tiempoextramotivos.nombre as motivo,
            TiempoextraEnc.autorizado,
            CambioTurno.Ctt_id
        FROM TiempoextraSubEnc 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
        INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id= tblEmpleados.Puesto
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        INNER JOIN Tiempoextramotivos on Tiempoextramotivos.id = TiempoextraSubEnc.motivo
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= TiempoextraSubEnc.maquina 
        INNER JOIN TLX003MXDB.dbo.TiempoextraEnc ON TiempoextraEnc.id = TiempoextraSubEnc.folio 
        --Filtro por personas (Menos cofiable)
        -- LEFT JOIN TLX002MXDB.dbo.tblMXPRCambioTurnoTemporal as CambioTurno ON CambioTurno.Ctt_folio = TiempoextraSubEnc.folio 
        -- Filtro por ID de registro unico con la tabla de tiempos extra
        LEFT JOIN TLX002MXDB.dbo.tblMXPRCambioTurnoTemporal as CambioTurno ON CambioTurno.Ctt_fol_TE = TiempoextraSubEnc.id
        AND CambioTurno.Ctt_a LIKE tblEmpleados.Nombre + '%'
        WHERE TiempoextraSubEnc.folio=$folio
        ORDER BY TiempoextraSubEnc.id DESC";

        $result = sqlsrv_query($conn, $query);
        $array = array();

        // Creacion de array asociativo para guardar y regresar los datos 
        while ($row = sqlsrv_fetch_array($result)) {
            $terminado = $row["autorizado"];
            $turnoAsignado = $row["turnoAsignado"];

            // Definicion de estados para los tiempos extra dependiendo del valor en autorizado (terminado)
            if($terminado === null || $terminado === ''){
                $estadoClass = 'badge bg-warning text-dark';
                $estadoTexto = 'En espera';
            } elseif ($terminado == 2){
                $estadoClass = 'badge bg-danger';
                $estadoTexto = 'Rechazado';
            } elseif ($terminado == 1){
                $estadoClass = 'badge bg-success';
                $estadoTexto = 'Aprobado';
            }

            // Definicion de turnos
            if($turnoAsignado === null || $turnoAsignado === "") {
                $estadoTurno = 'Sin turno';
            } else if($turnoAsignado === "turno1") {
                $estadoTurno = '1er Turno';
            } else if($turnoAsignado === "turno2") {
                $estadoTurno = '2do Turno';
            } else if($turnoAsignado === "turno3") {
                $estadoTurno = '3er Turno';
            } else if($turnoAsignado === "mixto1") {
                $estadoTurno = '1er Mixto';
            } else if($turnoAsignado === "mixto2") {
                $estadoTurno = '2do Mixto';
            } else if($turnoAsignado === "mixto3") {
                $estadoTurno = '3er Mixto';
            } else if($turnoAsignado === "mixto4") {
                $estadoTurno = '4to Mixto';
            } else if($turnoAsignado === "turno3_12hrs") {
                $estadoTurno = '3er Turno (12 hrs)';
            } else if($turnoAsignado === "turno2_12hrs") {
                $estadoTurno = '2do Turno (12 hrs)';
            }

            // Se agregan al array los datos obtenidos con la consulta y se les asigna un estado dependiendo 
            // del valor de autorizado para mostrarlo posteriormente en la tabla de detalles del tiempo extra
            $cambioTempExiste = $row["Ctt_id"] !== null;

            $array[] = [
                "id" => $row["id"],
                "noemp" => $row["noemp"],
                "nombre" => $row["Nombre"],
                "depto" => $row["depto"],
                "puesto" => $row["puesto"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "horai" => $row["horai"]->format("H:i:s"),
                "horaf" => $row["horaf"]->format("H:i:s"),
                "maquina" => $row["NombreMaquina"],
                "motivo" => $row["motivo"],
                "razon" => $row["razon"],
                "turnoAsignado" => $row["turnoAsignado"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto,
                "estadoTurno" => $estadoTurno,
                "cambioTempExiste" => $cambioTempExiste,
                "Ctt_id" => $row["Ctt_id"]
            ];
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

    // Obtener datos en general para la tabla de validacion
    function tblValidarTE(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        // Parámetros de paginación
        $pageSize = isset($_GET["pageSize"]) ? intval($_GET["pageSize"]) : 10;
        $page     = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
        $offset   = ($page - 1) * $pageSize;

        // Parámetro de folio (opcional)
        $folio = isset($_GET["folio"]) ? $_GET["folio"] : null;
        // Parámetro de folio (opcional)
        $deptoS = isset($_GET["deptoSelect"]) ? $_GET["deptoSelect"] : null;

        // $query = "SELECT 
        //         TiempoextraSubEnc.id,
        //         TiempoextraSubEnc.folio,
        //         TiempoextraSubEnc.noemp,
        //         tblEmpleados.Nombre,
        //         tblDepartamentos.NombreDepto as depto,
        //         tblPuestos.nombre as puesto,
        //         TiempoextraSubEnc.horai,
        //         TiempoextraSubEnc.horaf,
        //         Tiempoextramotivos.nombre as motivo,
        //         TiempoextraSubEnc.razon,
        //         TiempoextraSubEnc.turnoAsignado,    
        //         TiempoextraEnc.autorizado    
        //     FROM TiempoextraSubEnc 
        //     INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
        //     INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id= tblEmpleados.Puesto
        //     INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        //     INNER JOIN Tiempoextramotivos on Tiempoextramotivos.id = TiempoextraSubEnc.motivo
        //     INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= TiempoextraSubEnc.maquina 
        //     INNER JOIN TLX003MXDB.dbo.TiempoextraEnc ON TiempoextraEnc.id = TiempoextraSubEnc.folio
        //     WHERE 1=1
        //     ";

        $query = "SELECT 
            TiempoextraSubEnc.id,
            TiempoextraSubEnc.folio,
            TiempoextraSubEnc.noemp,
            tblEmpleados.Nombre,
            tblDepartamentos.NombreDepto as depto,
            tblPuestos.nombre as puesto,
            TiempoextraSubEnc.horai,
            TiempoextraSubEnc.horaf,
            Tiempoextramotivos.nombre as motivo,
            TiempoextraSubEnc.razon,
            TiempoextraSubEnc.turnoAsignado,    
            TiempoextraEnc.autorizado    
        FROM TiempoextraSubEnc 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
        INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        INNER JOIN Tiempoextramotivos ON Tiempoextramotivos.id = TiempoextraSubEnc.motivo
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = TiempoextraSubEnc.maquina 
        INNER JOIN TLX003MXDB.dbo.TiempoextraEnc ON TiempoextraEnc.id = TiempoextraSubEnc.folio
        WHERE 1=1";

        $params = [];

        // Filtro por folio
        if ($folio) {
            $query .= " AND TiempoextraSubEnc.folio = ? ";
            $params[] = $folio;
        }

        // Filtro por departamento (id)
        if ($deptoS) {
            $query .= " AND tblDepartamentos.NoDepto = ? ";
            $params[] = $deptoS;
        }


        $query .= " ORDER BY TiempoextraSubEnc.id DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
        $params[] = $offset;
        $params[] = $pageSize;

        $result = sqlsrv_query($conn, $query, $params);

        $array = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = [
                "id" => $row["id"],
                "folio" => $row["folio"],
                "noemp" => $row["noemp"],
                "Nombre" => $row["Nombre"],
                "depto" => $row["depto"],
                "puesto" => $row["puesto"],
                "horai" => $row["horai"] ? $row["horai"]->format("H:i:s") : "",
                "horaf" => $row["horaf"] ? $row["horaf"]->format("H:i:s") : "",
                "motivo" => $row["motivo"],
                "razon" => $row["razon"],
                "turnoAsignado" => $row["turnoAsignado"],
                "autorizado" => $row["autorizado"]
            ];
        }

        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    } 
    
    function tblValidarTEFolios(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $query = "SELECT DISTINCT folio FROM TiempoextraSubEnc ORDER BY folio DESC";

        $result = sqlsrv_query($conn, $query);

        $array = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = [                
                "folio" => $row["folio"]                
            ];
        }

        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }    
        

    // Recuperacion de valores segun modal
    function tblGetTE(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = intval($_GET["tblGetTE"]);

        $query = "SELECT
            TiempoextraSubEnc.id,
            TiempoextraSubEnc.folio,
            TiempoextraSubEnc.noemp,
            tblEmpleados.Nombre,
            tblDepartamentos.NombreDepto as depto,
            tblPuestos.nombre as puesto,
            TiempoextraSubEnc.horai,
            TiempoextraSubEnc.horaf,
            Tiempoextramotivos.nombre as motivo,
            TiempoextraSubEnc.razon,
            TiempoextraSubEnc.turnoAsignado,    
            TiempoextraEnc.autorizado    
        FROM TiempoextraSubEnc 
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
        INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id= tblEmpleados.Puesto
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        INNER JOIN Tiempoextramotivos on Tiempoextramotivos.id = TiempoextraSubEnc.motivo
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= TiempoextraSubEnc.maquina 
        INNER JOIN TLX003MXDB.dbo.TiempoextraEnc ON TiempoextraEnc.id = TiempoextraSubEnc.folio            
        WHERE TiempoextraSubEnc.id = $id
        ORDER BY TiempoextraSubEnc.id DESC";

        $result = sqlsrv_query($conn, $query);
        $array = [];

        while ($row = sqlsrv_fetch_array($result)) {
            $array = [
                "id" => $row["id"],
                "folio" => $row["folio"],
                "noemp" => $row["noemp"],
                "Nombre" => $row["Nombre"],
                "depto" => $row["depto"],
                "puesto" => $row["puesto"],
                
                "horai" => $row["horai"] ? $row["horai"]->format("H:i:s") : "",
                "horaf" => $row["horaf"] ? $row["horaf"]->format("H:i:s") : "",
                "motivo" => $row["motivo"],
                "razon" => $row["razon"],
                "turnoAsignado" => $row["turnoAsignado"],
                "autorizado" => $row["autorizado"]
            ];
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }    

    // Query para eliminar un registro de los detalles de los tiempos extra
    function deletesub()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_POST["id"];
        $query = "DELETE FROM TiempoextraSubEnc WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }    

    // Funcion para obtener:
    // id / fecha / NombreDepto / datesave / terminado / autorizo
    // 846 / 2026-02-09 / Pañal / 2026-02-17 12:54:40. 107 /  NULL / NULL
    // function tblenc()
    // {
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX003MXDB");

    //     $ibm = $_SESSION['ibm'];
    //     $clvDepto = $_SESSION['clvDepartamento'];

    //     // Lista de IBM con acceso total
    //     $ibmsAccesoTotal = ["58998","51947","55268","53224", "60040"];

    //     // Condición dinámica
    //     $whereSupervisor = in_array($ibm, $ibmsAccesoTotal) 
    //         ? "" 
    //         : "WHERE TiempoextraEnc.departamento = '" . $clvDepto . "'";

    //     $query = "SELECT 
    //         TiempoextraEnc.id,
    //         TiempoextraEnc.fecha,
    //         tblDepartamentos.NombreDepto,
    //         TiempoextraEnc.datesave,
    //         TiempoextraEnc.terminado, 
    //         TiempoextraEnc.autorizado,
    //         tblEmpleados.Nombre AS supervisorNombre
    //     FROM TiempoextraEnc 
    //     INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = TiempoextraEnc.departamento 
    //     INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraEnc.supervisor
    //     $whereSupervisor
    //     ORDER BY TiempoextraEnc.id DESC";

    //     $result = sqlsrv_query($conn, $query);
    //     $array = array();        
    //     while ($row = sqlsrv_fetch_array($result)) {
    //     array_push($array, [
    //         "id" => $row["id"],
    //         "fecha" => $row["fecha"]->format("Y-m-d"),
    //         "departamento" => $row["NombreDepto"],
    //         "creado" => $row["datesave"]->format("Y-m-d H:i:s"),
    //         "terminado" => $row["terminado"],
    //         "autorizado" => $row["autorizado"],
    //         "autor" => $row["supervisorNombre"]
    //     ]);
    //     }

    //     echo $result === false ? json_encode("sqlerror") : json_encode($array);
    // }

    function tblenc()
{
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX003MXDB");

    $ibm = $_SESSION['ibm'];
    $clvDepto = $_SESSION['clvDepartamento'];

    // IBM con acceso total (ven todo)
    $ibmsAccesoTotal = ["58998","51947","55268","53224","60040"];

    if (in_array($ibm, $ibmsAccesoTotal)) {
        $where  = "";
        $params = [];
    } else {
        $permitidos = deptosPermitidosSupervisor($ibm);
        // su propio departamento siempre incluido
        if ($clvDepto !== '' && !in_array((string)$clvDepto, $permitidos, true)) {
            $permitidos[] = (string)$clvDepto;
        }
        if (empty($permitidos)) {
            $where  = "WHERE 1 = 0"; // sin deptos asignados -> nada
            $params = [];
        } else {
            $ph     = implode(',', array_fill(0, count($permitidos), '?'));
            $where  = "WHERE TiempoextraEnc.departamento IN ($ph)";
            $params = $permitidos;
        }
    }

    $query = "SELECT
        TiempoextraEnc.id,
        TiempoextraEnc.fecha,
        tblDepartamentos.NombreDepto,
        TiempoextraEnc.datesave,
        TiempoextraEnc.terminado,
        TiempoextraEnc.autorizado,
        tblEmpleados.Nombre AS supervisorNombre
    FROM TiempoextraEnc
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = TiempoextraEnc.departamento
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraEnc.supervisor
    $where
    ORDER BY TiempoextraEnc.id DESC";

    $result = sqlsrv_query($conn, $query, $params);
    $array = array();
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($array, [
            "id" => $row["id"],
            "fecha" => $row["fecha"]->format("Y-m-d"),
            "departamento" => $row["NombreDepto"],
            "creado" => $row["datesave"]->format("Y-m-d H:i:s"),
            "terminado" => $row["terminado"],
            "autorizado" => $row["autorizado"],
            "autor" => $row["supervisorNombre"]
        ]);
    }

    echo $result === false ? json_encode("sqlerror") : json_encode($array);
}

    
    
    function tblencfolio()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;

        if (in_array($ibm, $admins)) {
            // $query = "SELECT 
            //             teEnc.id,
            //             teEnc.fecha,
            //             d.NombreDepto,
            //             teEnc.datesave,
            //             teEnc.terminado, 
            //             teEnc.autorizado,
            //             teEnc.supervisor,
            //             eSup.Nombre AS NombreSupervisor,
            //             teEnc.noempSupIntendente,
            //             teEnc.autorizaSupInt,
            //             (SELECT COUNT(*) 
            //             FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
            //             WHERE sub.folio = teEnc.id 
            //             AND (sub.validado IS NULL OR sub.validado <> 1)
            //             ) AS pendientesValidar
            //         FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
            //         INNER JOIN TLX009MXDB.dbo.tblDepartamentos d 
            //             ON d.NoDepto = teEnc.departamento 
            //         INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
            //             ON eSup.NoEmp = teEnc.supervisor
            //         WHERE 1=1";
            $query = "SELECT 
                teEnc.id,
                teEnc.fecha,
                d.NombreDepto,
                teEnc.datesave,
                teEnc.terminado, 
                teEnc.autorizado,
                teEnc.supervisor,
                eSup.Nombre AS NombreSupervisor,
                teEnc.noempSupIntendente,
                teEnc.autorizaSupInt,
                (SELECT COUNT(*) 
                FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
                WHERE sub.folio = teEnc.id 
                AND (sub.validado IS NULL OR sub.validado <> 1)
                ) AS pendientesValidar,
                (SELECT COUNT(*) 
                FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
                WHERE sub.folio = teEnc.id
                ) AS totalRegistros
            FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos d 
                ON d.NoDepto = teEnc.departamento 
            INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                ON eSup.NoEmp = teEnc.supervisor
            WHERE 1=1";
            $params = [];
        } else {
            $query = "SELECT 
                teEnc.id,
                teEnc.fecha,
                d.NombreDepto,
                teEnc.datesave,
                teEnc.terminado, 
                teEnc.autorizado,
                teEnc.supervisor,
                eSup.Nombre AS NombreSupervisor,
                teEnc.noempSupIntendente,
                teEnc.autorizaSupInt,
                (SELECT COUNT(*) 
                FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
                WHERE sub.folio = teEnc.id 
                AND (sub.validado IS NULL OR sub.validado <> 1)
                ) AS pendientesValidar,
                (SELECT COUNT(*) 
                FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
                WHERE sub.folio = teEnc.id
                ) AS totalRegistros
            FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos d 
                ON d.NoDepto = teEnc.departamento 
            INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                ON eSup.NoEmp = teEnc.supervisor
            WHERE teEnc.noempautoriza = ?";                    
            $params = [$ibm];
        }

        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND teEnc.fecha = ?";
            $params[] = $fechaFiltro;
        }

        // Filtro por estatus
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            if ((int)$estatusFiltro === 0) {
                // En espera: autorizado es NULL o vacío
                $query .= " AND (teEnc.autorizado IS NULL OR teEnc.autorizado = '')";
            } else {
                $query .= " AND teEnc.autorizado = ?";
                $params[] = (int)$estatusFiltro;
            }
        }

        $query .= " ORDER BY teEnc.id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $autorizado = $row["autorizado"];

            if ($autorizado === null || $autorizado === '') {
                $estadoClass = 'badge bg-warning text-dark';
                $estadoTexto = 'En espera';
            } elseif ($autorizado == 1) {
                $estadoClass = 'badge bg-success';
                $estadoTexto = 'Aprobado';
            } elseif ($autorizado == 2) {
                $estadoClass = 'badge bg-danger';
                $estadoTexto = 'Rechazado';
            }

            $array[] = [
                "id" => $row["id"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "departamento" => $row["NombreDepto"],
                "creado" => $row["datesave"]->format("Y-m-d H:i:s"),
                "terminado" => $row["terminado"],
                "autorizado" => $autorizado,
                "supervisor" => $row["supervisor"],
                "NombreSupervisor" => $row["NombreSupervisor"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto,
                "pendientesValidar" => $row["pendientesValidar"],
                "autorizaSupInt" => $row["autorizaSupInt"],
                "noempSupIntendente" => $row["noempSupIntendente"],
                "totalRegistros" => $row["totalRegistros"]
            ];
        }

        echo json_encode($array);
    }

    function tblencfolioSupInt()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;

        if (in_array($ibm, $admins)) {
            $query = "SELECT 
                        teEnc.id,
                        teEnc.fecha,
                        d.NombreDepto,
                        teEnc.datesave,
                        teEnc.terminado, 
                        teEnc.autorizado,
                        teEnc.supervisor,
                        eSup.Nombre AS NombreSupervisor,
                        teEnc.noempSupIntendente,
                        teEnc.autorizaSupInt,
                        (SELECT COUNT(*) 
                        FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
                        WHERE sub.folio = teEnc.id 
                        AND (sub.validado IS NULL OR sub.validado <> 1)
                        ) AS pendientesValidar
                    FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos d 
                        ON d.NoDepto = teEnc.departamento 
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = teEnc.supervisor
                    WHERE 1=1
                    AND teEnc.noempSupIntendente IS NOT NULL";
            $params = [];
        } else {
            $query = "SELECT 
                        teEnc.id,
                        teEnc.fecha,
                        d.NombreDepto,
                        teEnc.datesave,
                        teEnc.terminado, 
                        teEnc.autorizado,
                        teEnc.supervisor,
                        eSup.Nombre AS NombreSupervisor,
                        teEnc.noempSupIntendente,
                        teEnc.autorizaSupInt,
                        (SELECT COUNT(*) 
                        FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
                        WHERE sub.folio = teEnc.id 
                        AND (sub.validado IS NULL OR sub.validado <> 1)
                        ) AS pendientesValidar
                    FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos d 
                        ON d.NoDepto = teEnc.departamento 
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = teEnc.supervisor
                    WHERE teEnc.noempSupIntendente = ?";
            $params = [$ibm];
        }

        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND teEnc.fecha = ?";
            $params[] = $fechaFiltro;
        }

        // Filtro por estatus
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            if ((int)$estatusFiltro === 0) {
                // En espera: autorizado es NULL o vacío
                $query .= " AND (teEnc.autorizado IS NULL OR teEnc.autorizado = '')";
            } else {
                $query .= " AND teEnc.autorizado = ?";
                $params[] = (int)$estatusFiltro;
            }
        }

        $query .= " ORDER BY teEnc.id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $autorizado = $row["autorizado"];

            if ($autorizado === null || $autorizado === '') {
                $estadoClass = 'badge bg-warning text-dark';
                $estadoTexto = 'En espera';
            } elseif ($autorizado == 1) {
                $estadoClass = 'badge bg-success';
                $estadoTexto = 'Aprobado';
            } elseif ($autorizado == 2) {
                $estadoClass = 'badge bg-danger';
                $estadoTexto = 'Rechazado';
            }

            $array[] = [
                "id" => $row["id"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "departamento" => $row["NombreDepto"],
                "creado" => $row["datesave"]->format("Y-m-d H:i:s"),
                "terminado" => $row["terminado"],
                "autorizado" => $autorizado,
                "supervisor" => $row["supervisor"],
                "NombreSupervisor" => $row["NombreSupervisor"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto,
                "pendientesValidar" => $row["pendientesValidar"],
                "autorizaSupInt" => $row["autorizaSupInt"],
                "noempSupIntendente" => $row["noempSupIntendente"]
            ];
        }

        echo json_encode($array);
    }

    function editarTE(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $folioRegistro = $_POST['folioRegistro'];        

        $query = "UPDATE TiempoextraSubEnc SET validado = 1 WHERE id = ?";
        $params = array($folioRegistro);

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode([
                "success" => false,
                "error" => $errors[0]['message'] ?? "sqlerror"
            ]);
        } else {
            echo json_encode(["success" => true]);
        }
    }

    function deleteModalSub() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_POST["id"];
        $query = "DELETE FROM TiempoextraSubEnc WHERE id = ?";
        $params = [$id];
        $result = sqlsrv_query($conn, $query, $params);
        echo json_encode(["success" => $result !== false]);
    }

    // Actualizar registro
    function updateTE(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data["id"];
        $horain = $data["horai"];
        $horafin = $data["horaf"];
        $razon = $data["razon"];
        $turno = $data["turnoAsignado"];

        $query = "UPDATE TiempoextraSubEnc 
                SET horai = ?, horaf = ?, razon = ?, turnoAsignado = ?, validado = 1 
                WHERE id = ?";
        $params = [$horain, $horafin, $razon, $turno, $id];

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode([
                "success" => false,
                "error" => $errors[0]['message'] ?? "sqlerror"
                ]);
            } else {
                echo json_encode(["success" => true]);
        }
    }


    /*
    // Consulta general para recuperar datos y editarlos en la siguiente funcion
    // Accionamiento para recuperar los datos y mostrarlos en la tabla y ver su informacion
    function editenc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $query = "SELECT * FROM TiempoextraEnc WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row["id"], "fecha" => $row["fecha"]->format("Y-m-d"), "departamento" => $row["departamento"]]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }
    */

    function editenc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $query = "SELECT * FROM TiempoextraEnc WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $semana = date("W", strtotime($row["fecha"]->format("Y-m-d")));
            array_push($array, [
                "id" => $row["id"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "departamento" => $row["departamento"],
                "semana" => $semana
            ]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

    function getDataReporte(){
        $ClassConexion = new ClassConexion;
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $fechaI = $_GET["fechai"] ?? null;
        $fechaF = $_GET["fechaf"] ?? null;
        $turno = $_GET["turno"] ?? null;
        $departamento = $_GET["departamento"] ?? null;

        $params = [$fechaI, $fechaF, $turno ?: null, $departamento ?: null];

        // Llamada a sp
        $sql = "EXEC dbo.pa_MXPRReporteHorasExtras ?, ?, ?, ?";

        $result = sqlsrv_query($conn, $sql, $params);            

        if($result === false){
            if(($errors = sqlsrv_errors()) != null){
                foreach($errors as $error){
                    echo "SQLSTATE: ".$error['SQLSTATE']."<br />";
                    echo "Code: ".$error['code']."<br />";
                    echo "Message: ".$error['message']."<br />";
                }
            }
            exit;
        }

        $array = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $semana = date("W", strtotime($row["fecha"]->format("Y-m-d")));
            $array[] = [
                "id" => $row["id"],
                "folio" => $row["FolioTurnoExtra"],
                "noemp" => $row["noemp"],
                "departamento" => $row["departamento"],
                "nombre" => $row["NombreSolicitante"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "horaInicioTurnoExtra" => $row["horaInicioTurnoExtra"]->format("H:i"),
                "horaFinTurnoExtra" => $row["horaFinTurnoExtra"]->format("H:i"),
                "turnoAsignado" => $row["turnoAsignado"],
                "horasExtrasRegistro" => $row["horasExtrasRegistro"],
                "totalHorasExtrasSolicitadas" => $row["totalHorasExtrasSolicitadas"],
                "horasTotales" => $row["horasTotales"],
                "semana" => $semana,
                "esDoblete" => $row["esDoblete"]
            ];
        }

        echo json_encode($array);
    }

    function guardarCambioTurno(){
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        $folio = $_GET["folio"];



        $ibmEmpleado = !empty($_POST['ibm_empleado']) ? intval($_POST['ibm_empleado']) : null;
        $ibmAutoriza = !empty($_POST['ibm_autoriza']) ? intval($_POST['ibm_autoriza']) : null;

        // Usar datos del folio del registro ddel tiempo extra y usarlo para identificar los nuevos datos
        $folioTE = $_POST['folioTiempoExtra'];
        $fecha = $_POST['fecha_emision'];
        $depto = $_POST['Depto_m'];        
        $a = $_POST['nombre_receptor'];
        $de = $_POST['de_area'];        
        //$tripulacion = $_POST['tripulacion'];
        $tripulacion = null;
        $horario = $_POST['horario_texto'];
        $rol = $_POST['rol'];
        $aPartirDel = $_POST['fecha_inicio'];
        $hastaEl = $_POST['hasta_el'];
        $horaPresentacion = $_POST['hora_presentacion'];
        $turnoPresentacion = $_POST['turno_presentacion'];
        $horarioDe = $_POST['horario_desde'];
        $horarioA = $_POST['horario_hasta'];
        $hastaTripulacion = $_POST['hasta_tripulacion'];
        $descansos = $_POST['descansos'];
        $diaAdd = $_POST['dias_adicionales'];
        $horarioAdd = $_POST['horario_adicional'];
        $pdfDir = "";
        $estado = 0;

        $sql = "INSERT INTO tblMXPRCambioTurnoTemporal(
                Ctt_folio, Ctt_fol_TE, Ctt_fecha, Ctt_depto, Ctt_a, Ctt_de, 
                Ctt_tripulacion, Ctt_horario, Ctt_rol,
                Ctt_aPartirDel, Ctt_hastaEl, Ctt_horaPresentacion, 
                Ctt_turnoPresentacion, Ctt_tripulacionDe, Ctt_horarioDe, 
                Ctt_horarioA, Ctt_descansos, Ctt_diaAdd, Ctt_horarioAdd, 
                Ctt_PDFDir, Ctt_estado, Ctt_ibmEmpleado, Ctt_ibmAutoriza)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params = array($folio, $folioTE, $fecha, $depto, $a, $de, $tripulacion, $horario, $rol, $aPartirDel, $hastaEl, $horaPresentacion, $turnoPresentacion, 
        $hastaTripulacion, $horarioDe, $horarioA, $descansos, $diaAdd, $horarioAdd, $pdfDir, $estado, $ibmEmpleado, $ibmAutoriza);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if($stmt){
            echo json_encode(["success"=>true, "message"=>"Guardado en BD"]);
        } else {
            echo json_encode(["success"=>false, "message"=>sqlsrv_errors()]);
        }
        exit;
    }

    function getSemana() {
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX002MXDB");
    error_log("Conexión establecida: " . ($conn ? "OK" : "FALLÓ"));

    $noemp  = intval($_GET['noemp']  ?? 0);
    $semana = intval($_GET['semana'] ?? 0);
    $anio   = intval($_GET['anio']   ?? 0);
    error_log("Parámetros recibidos → noemp=$noemp, semana=$semana, anio=$anio");

    if (!$noemp || !$semana || !$anio) {
        error_log("Error: parámetros incompletos");
        echo json_encode(['error' => 'Parámetros incompletos']);
        exit;
    }

    // 1. Buscar calendario
    $sqlCal = "SELECT lunes, martes, miercoles, jueves, viernes, sabado, domingo, total_minutos
               FROM tblMXPRSemanaTurnos
               WHERE noemp = ? AND semana = ? AND anio = ?";
    error_log("Ejecutando SQL calendario: $sqlCal");
    $resCal = sqlsrv_query($conn, $sqlCal, [$noemp, $semana, $anio]);
    if (!$resCal) error_log("Error SQL calendario: " . print_r(sqlsrv_errors(), true));
    $filaCal = $resCal ? sqlsrv_fetch_array($resCal, SQLSRV_FETCH_ASSOC) : null;
    error_log("Resultado calendario: " . var_export($filaCal, true));

    // 2. Extras
    $sqlExtras = "SELECT ISNULL(SUM(DATEDIFF(MINUTE, horai, horaf)), 0) AS extras_minutos
                  FROM TLX003MXDB.dbo.TiempoextraSubEnc
                  WHERE noemp = ?
                    AND DATEPART(ISO_WEEK, fecha) = ?
                    AND YEAR(fecha) = ?";
    error_log("Ejecutando SQL extras: $sqlExtras");
    $resExtras = sqlsrv_query($conn, $sqlExtras, [$noemp, $semana, $anio]);
    if (!$resExtras) error_log("Error SQL extras: " . print_r(sqlsrv_errors(), true));
    $filaExtras = $resExtras ? sqlsrv_fetch_array($resExtras, SQLSRV_FETCH_ASSOC) : null;
    $extrasMin  = $filaExtras ? intval($filaExtras['extras_minutos']) : 0;
    error_log("Resultado extras: " . var_export($filaExtras, true));

    // Respuesta final
    if ($filaCal) {
        error_log("Devolviendo datos con calendario y extras");
        echo json_encode([
            'datos' => [
                'lunes'     => $filaCal['lunes'],
                'martes'    => $filaCal['martes'],
                'miercoles' => $filaCal['miercoles'],
                'jueves'    => $filaCal['jueves'],
                'viernes'   => $filaCal['viernes'],
                'sabado'    => $filaCal['sabado'],
                'domingo'   => $filaCal['domingo'],
            ],
            'total_minutos'  => intval($filaCal['total_minutos']),
            'extras_minutos' => $extrasMin
        ]);
    } else {
        error_log("No se encontró calendario, devolviendo solo extras");
        echo json_encode([
            'datos'          => null,
            'extras_minutos' => $extrasMin
        ]);
    }
    exit;
}


    function getExtraAcumuladas(){        
    $ClassConexion = new ClassConexion();
    $conn = $ClassConexion->conexion("TLX002MXDB");
    error_log("[getExtraAcumuladas] Conexión: " . ($conn ? "OK" : "FALLÓ"));

    $noemp  = intval($_GET['noemp']  ?? 0);
    $semana = intval($_GET['semana'] ?? 0);
    $anio   = intval($_GET['anio']   ?? 0);
    error_log("[getExtraAcumuladas] Params → noemp=$noemp, semana=$semana, anio=$anio");

    $sql = "SELECT ISNULL(SUM(DATEDIFF(MINUTE, horai, horaf)), 0) AS extras_minutos
            FROM TiempoextraSubEnc
            WHERE noemp = ?
              AND DATEPART(ISO_WEEK, fecha) = ?
              AND YEAR(fecha) = ?";
    error_log("[getExtraAcumuladas] SQL: $sql");
    $res  = sqlsrv_query($conn, $sql, [$noemp, $semana, $anio]);
    if (!$res) error_log("[getExtraAcumuladas] Error SQL: " . print_r(sqlsrv_errors(), true));
    $fila = $res ? sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC) : null;
    error_log("[getExtraAcumuladas] Resultado: " . var_export($fila, true));

    echo json_encode([
        'extras_minutos' => $fila ? intval($fila['extras_minutos']) : 0
    ]);
    exit;
}


    function guardarSemana(){
        $ClassConexion = new ClassConexion();
        $conn          = $ClassConexion->conexion("TLX002MXDB");
        error_log("[guardarSemana] Conexión: " . ($conn ? "OK" : "FALLÓ"));

        $noemp         = intval($_POST['noemp']         ?? 0);
        $semana        = intval($_POST['semana']        ?? 0);
        $anio          = intval($_POST['anio']          ?? 0);
        $folio         = intval($_POST['folio']         ?? 0);
        $total_minutos = intval($_POST['total_minutos'] ?? 0);
        error_log("[guardarSemana] Params → noemp=$noemp, semana=$semana, anio=$anio, folio=$folio, total_minutos=$total_minutos");

        // Valores válidos para cada día
        $opcionesValidas = ['D','turno1','turno2','turno3',
                            'mixto1','mixto2','mixto3','mixto4',
                            'turno2_13hrs','turno3_12hrs'];

        $dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

        $valoresDias = [];
        foreach ($dias as $d) {
            $v = trim($_POST[$d] ?? 'D');
            $valoresDias[$d] = in_array($v, $opcionesValidas) ? $v : 'D';
        }
        error_log("[guardarSemana] Valores días: " . var_export($valoresDias, true));

        if (!$noemp || !$semana || !$anio || !$folio) {
            echo json_encode(['ok' => false, 'error' => 'Parámetros incompletos']);
            exit;
        }

        // ── 1. Leer registro anterior para detectar cambios ──────────────────────
        $sqlSel = "SELECT lunes, martes, miercoles, jueves, viernes, sabado, domingo
                FROM tblMXPRSemanaTurnos
                WHERE noemp = ? AND semana = ? AND anio = ?";

        $resSel   = sqlsrv_query($conn, $sqlSel, [$noemp, $semana, $anio]);
        $anterior = $resSel ? sqlsrv_fetch_array($resSel, SQLSRV_FETCH_ASSOC) : null;

        // ── 2. UPSERT con MERGE ───────────────────────────────────────────────────
        $sqlMerge = "
            MERGE tblMXPRSemanaTurnos AS target
            USING (SELECT ? AS noemp, ? AS semana, ? AS anio) AS source
                ON  target.noemp  = source.noemp
                AND target.semana = source.semana
                AND target.anio   = source.anio
            WHEN MATCHED THEN
                UPDATE SET
                    folio         = ?,
                    lunes         = ?,
                    martes        = ?,
                    miercoles     = ?,
                    jueves        = ?,
                    viernes       = ?,
                    sabado        = ?,
                    domingo       = ?,
                    total_minutos = ?,
                    actualizado   = GETDATE()
            WHEN NOT MATCHED THEN
                INSERT (noemp, semana, anio, folio,
                        lunes, martes, miercoles, jueves, viernes, sabado, domingo,
                        total_minutos, creado, actualizado)
                VALUES (?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, GETDATE(), GETDATE());
        ";

        $params = [
            // USING clause
            $noemp, $semana, $anio,
            // UPDATE SET
            $folio,
            $valoresDias['lunes'],   $valoresDias['martes'], $valoresDias['miercoles'],
            $valoresDias['jueves'],  $valoresDias['viernes'],
            $valoresDias['sabado'],  $valoresDias['domingo'],
            $total_minutos,
            // INSERT VALUES
            $noemp, $semana, $anio, $folio,
            $valoresDias['lunes'],   $valoresDias['martes'], $valoresDias['miercoles'],
            $valoresDias['jueves'],  $valoresDias['viernes'],
            $valoresDias['sabado'],  $valoresDias['domingo'],
            $total_minutos
        ];
        error_log("[guardarSemana] Ejecutando MERGE");

        $resMerge = sqlsrv_query($conn, $sqlMerge, $params);

        if (!$resMerge) {
            $errores = sqlsrv_errors();
            echo json_encode(['ok' => false, 'error' => $errores[0]['message'] ?? 'Error en MERGE']);
            exit;
        }

        // ── 3. Detectar qué días cambiaron de turno ───────────────────────────────
        $diasCambiados = [];
        if ($anterior) {
            foreach ($dias as $d) {
                if ($anterior[$d] !== $valoresDias[$d]) {
                    $diasCambiados[$d] = [
                        'anterior' => $anterior[$d],
                        'nuevo'    => $valoresDias[$d]
                    ];
                }
            }
        }
        error_log("[guardarSemana] Días cambiados: " . var_export($diasCambiados, true));

        // ── 4. Si hubo cambios buscar registros del folio afectados ──────────────
        $registrosAfectados = [];

        if (!empty($diasCambiados)) {
            // Calcular las fechas exactas lun-dom de la semana ISO desde PHP
            // El 4-ene siempre cae en semana 1 ISO
            $jan4    = new DateTime("{$anio}-01-04");
            $jan4Dow = (int)$jan4->format('N');          // 1=Lun ... 7=Dom
            $lunes   = clone $jan4;
            $lunes->modify('-' . ($jan4Dow - 1) . ' days');  // lunes semana 1
            $lunes->modify('+' . ($semana - 1) . ' weeks');  // lunes de la semana pedida

            // Mapear día nombre → fecha exacta
            $fechasPorDia = [];
            foreach ($dias as $i => $d) {
                $fecha = clone $lunes;
                $fecha->modify("+{$i} days");
                $fechasPorDia[$d] = $fecha->format('Y-m-d');
            }

            // Fechas que cambiaron
            $fechasFiltro = [];
            foreach (array_keys($diasCambiados) as $d) {
                $fechasFiltro[] = $fechasPorDia[$d];
            }

            // Construir IN con fechas seguras (generadas por PHP, no input usuario)
            $inPlaceholders = implode(',', array_fill(0, count($fechasFiltro), '?'));

            $sqlReg = "SELECT id, noemp, CONVERT(varchar,fecha,23) AS fecha, turnoAsignado
                    FROM TiempoextraSubEnc
                    WHERE folio = ?
                        AND CONVERT(varchar,fecha,23) IN ({$inPlaceholders})";

            $paramsReg = array_merge([$folio], $fechasFiltro);
            $resReg    = sqlsrv_query($conn, $sqlReg, $paramsReg);

            if ($resReg) {
                while ($reg = sqlsrv_fetch_array($resReg, SQLSRV_FETCH_ASSOC)) {
                    // Encontrar a qué día corresponde
                    $diaReg = null;
                    foreach ($fechasPorDia as $d => $f) {
                        if ($f === $reg['fecha']) { $diaReg = $d; break; }
                    }
                    if ($diaReg && isset($diasCambiados[$diaReg])) {
                        $registrosAfectados[] = [
                            'id'             => $reg['id'],
                            'fecha'          => $reg['fecha'],
                            'turno_anterior' => $diasCambiados[$diaReg]['anterior'],
                            'turno_nuevo'    => $diasCambiados[$diaReg]['nuevo']
                        ];
                    }
                }
            }
        }
        error_log("[guardarSemana] Registros afectados: " . var_export($registrosAfectados, true));

        echo json_encode([
            'ok'        => true,
            'recalculo' => $registrosAfectados
        ]);
        exit;
    }
    /*
    ANTIGUA FUNCION PARA ENVIAR DATOS, YA NO SE USA
    // Actualizacion de datos basadas en ID
    // Se actualiza el registro ya creado anteriormente y el campo de (inicialmente en 0) en terminado pasa a 1 para mostrar que ya esta autorizado
    // Todo esto basado en un id
    function enviarfol()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $query = "UPDATE TiempoextraEnc SET terminado=1 WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }
    */
    
}

// Clase de reportes
class Reportes
{
    function reportegenral()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION['ibm'];

        // Recuperar parámetros con fallback
        $folio   = $_POST["folio"]   ?? '';
        $fechai  = $_POST["fechai"]  ?? '';
        $fechaf  = $_POST["fechaf"]  ?? '';
        $noemp   = $_POST["noemp"]   ?? '';
        $departamento = $_POST["departamento"] ?? '';
        $motivo  = $_POST["motivo"]  ?? '';

        // LOG de entrada
        error_log("reportegenral() => IBM: $ibm | folio: $folio | fechai: $fechai | fechaf: $fechaf | noemp: $noemp | depto: $departamento | motivo: $motivo");

        // Construcción del WHERE
        if ($folio === '') {
            $addwhere = "WHERE TiempoextraSubEnc.fecha BETWEEN '" . $fechai . "' AND '" . $fechaf . "'";
        } else {
            $addwhere = "WHERE TiempoextraSubEnc.folio = " . (int)$folio;
        }

        if (!empty($noemp)) {
            $addwhere .= " AND TiempoextraSubEnc.noemp = " . (int)$noemp;
        }
        if (!empty($folio)) {
            $addwhere .= " AND TiempoextraSubEnc.folio = " . (int)$folio;
        }
        if (!empty($departamento)) {
            $addwhere .= " AND TiempoextraEnc.departamento = " . (int)$departamento;
        }
        if (!empty($motivo)) {
            $addwhere .= " AND TiempoextraSubEnc.motivo = " . (int)$motivo;
        }

        // Lista blanca de IBM con acceso total
        $ibmsAccesoTotal = ["58998","51947",'55268', '53224', '60040']; 

        // Condición de supervisor
        $idGerente = in_array($ibm, $ibmsAccesoTotal) 
            ? "" 
            : " AND TiempoextraEnc.supervisor = '" . $ibm . "'";

        // LOG del WHERE final
        error_log("reportegenral() => WHERE: $addwhere $idGerente");

        $query = "SELECT
                    TiempoextraSubEnc.id, 
                    TiempoextraEnc.id as folio, 
                    TiempoextraSubEnc.noemp as noempsub,
                    tblEmpleados.Nombre as nombresub,
                    TiempoextraSubEnc.fecha,
                    DATEDIFF(MINUTE, TiempoextraSubEnc.horai,TiempoextraSubEnc.horaf) as dif, 
                    tblMaquinas.NombreMaquina as maquina, 
                    Tiempoextramotivos.nombre as motivo,
                    TiempoextraSubEnc.razon,
                    TiempoextraEnc.supervisor, 
                    tbl2emp.Nombre as nombresup, 
                    tblDepartamentos.NombreDepto,
                    TiempoextraEnc.autorizado
            FROM TiempoextraSubEnc 
            INNER JOIN TiempoextraEnc ON TiempoextraEnc.id= TiempoextraSubEnc.folio
            INNER JOIN Tiempoextramotivos ON Tiempoextramotivos.id= TiempoextraSubEnc.motivo
            INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = TiempoextraSubEnc.noemp
            INNER JOIN TLX032MXDB.dbo.tblEmpleados as tbl2emp ON tbl2emp.NoEmp = TiempoextraEnc.supervisor
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto= TiempoextraEnc.departamento
            INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= TiempoextraSubEnc.maquina
            $addwhere $idGerente
            ORDER BY id desc";

        // LOG del query final
        error_log("reportegenral() => QUERY: $query");

        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            error_log("reportegenral() => SQL ERROR: " . print_r(sqlsrv_errors(), true));
            echo json_encode("sqlerror");
            return;
        }

        $array = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $terminado = $row["autorizado"];

            if ($terminado === null || $terminado === '') {
                $estadoClass = 'badge bg-warning text-dark';
                $estadoTexto = 'En espera';
            } elseif ($terminado == 2) {
                $estadoClass = 'badge bg-danger';
                $estadoTexto = 'Rechazado';
            } elseif ($terminado == 1) {
                $estadoClass = 'badge bg-success';
                $estadoTexto = 'Aprobado';
            }

            $row["dif"] = number_format($row["dif"] / 60, 1);
            if ($row["dif"] <= 0) {
                $row["dif"] = ($row["dif"] + 24);
            }

            $array[] = [
                "id" => $row["id"],
                "folio" => $row["folio"],
                "noempsub" => $row["noempsub"],
                "nombresub" => $row["nombresub"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "dif" => $row["dif"],
                "maquina" => $row["maquina"],
                "motivo" => $row["motivo"],
                "razon" => $row["razon"],
                "supervisor" => $row["supervisor"],
                "nombresup" => $row["nombresup"],
                "depto" => $row["NombreDepto"],
                "autorizado" => $terminado,
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto
            ];
        }

        echo json_encode($array);
    }

    
    // Funcion de busqueda de tiempos extra para autorizarlos o rechazarlos
    // Permite mostrar los registros de x persona segun su ibm
    // Se usan los casos correspondientes del where segun su ibm
    function tblautorizatp()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION['ibm'];          
        $clvDepto = $_SESSION['clvDepartamento'];      

        // Si es el IBM maestro, no se aplican filtros
        if ($ibm == '58998') {
            $where = "";
            $params = [];
        }
        // Si es el superusuario este ve todos los registros o solo los que estan a su nivel de permisos (supervisor) 
        else if($ibm == '51947' || $ibm == '53224' || $ibm == '55268' || $ibm == '60040'){
            $where = "";
            $params = [];
        } 
        // else {
        //     // En el caso de que el usuario sea un supervisor este solo vera sus propios registros asignados por el supervisor
        //     // cambiamos el noempautoriza que es de gerente por supervisor que es quien valida 1 a 1 cada registro
        //     $where = "WHERE teEnc.departamento = ?";
        //     $params = [$clvDepto];
        // }
        else {
           // El supervisor ve TODOS los departamentos que le corresponden (no solo uno)
            $permitidos = deptosPermitidosSupervisor($ibm);
            if ($clvDepto !== '' && !in_array((string)$clvDepto, $permitidos, true)) {
                $permitidos[] = (string)$clvDepto;
            }
            if (empty($permitidos)) {
                $where  = "WHERE 1 = 0";
                $params = [];
            } else {
                $ph     = implode(',', array_fill(0, count($permitidos), '?'));
                $where  = "WHERE teEnc.departamento IN ($ph)";
                $params = $permitidos;
            }
        }

        // Query principal para la busqueda de datos con base en los parametros asignados segun sea el caso
        $query = "SELECT
                        teSub.id AS folioRegistro,
                        teEnc.id,
                        teEnc.fecha,
                        d.NombreDepto,
                        teEnc.datesave,
                        teEnc.terminado, 
                        teEnc.autorizado,
                        eSup.NoEmp, 
                        eSup.Nombre AS SupervisorNombre,
                        teEnc.noempautoriza,
                        teSub.fecha AS fechaSol,
                        teSub.noemp AS NoEmpleadoSol,
                        teSub.horai AS HoraInicio,
                        teSub.horaf AS HoraFin,
                        teSub.validado AS validado,
                        teSub.turnoAsignado,
                        eSol.Nombre AS NombreEmpleadoSol,
                        teSub.motivo AS motivo,
                        teSub.razon AS razon,
                        teSub.maquina AS maquina
                  FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
                  INNER JOIN TLX009MXDB.dbo.tblDepartamentos d ON d.NoDepto = teEnc.departamento 
                  INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup ON eSup.NoEmp = teEnc.supervisor
                  INNER JOIN TLX003MXDB.dbo.TiempoextraSubEnc teSub ON teSub.folio = teEnc.id
                  INNER JOIN TLX032MXDB.dbo.tblEmpleados eSol ON eSol.NoEmp = teSub.noemp
                  $where
                  ORDER BY teEnc.id DESC";

        // Manejo de datos
        $result = sqlsrv_query($conn, $query, $params);
        if ($result === false) {
            $errors = sqlsrv_errors();
            $array = ["error" => $errors[0]['message'] ?? "sqlerror"];
            echo json_encode($array);
            exit;
        }
        function formatoOracion(string $texto): string {
            $texto = strtolower($texto);
            return ucwords($texto);
        }

        // Obtencion y recorrido de valores
        $array = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "folioRegistro" => $row["folioRegistro"],
                "id" => $row["id"], 
                "fecha" => $row["fecha"]->format("Y-m-d"), 
                "departamento" => $row["NombreDepto"],
                "creado" => $row["datesave"]->format("Y-m-d H:i:s"), 
                "terminado" => $row["terminado"], 
                "autorizado" => $row["autorizado"], 
                "NoEmp" => $row["NoEmp"], 
                "SupervisorNombre" => formatoOracion($row["SupervisorNombre"]),
                "noempautoriza" => $row['noempautoriza'],
                "fechaSol" => $row["fechaSol"]->format("Y-m-d"),
                "NoEmpleadoSol" => $row["NoEmpleadoSol"], 
                "HoraInicio" => $row["HoraInicio"]->format("H:i:s"), 
                "HoraFin" => $row["HoraFin"]->format("H:i:s"), 
                "NombreEmpleadoSol" => formatoOracion($row["NombreEmpleadoSol"]), 
                "validado" => $row["validado"], 
                "turnoAsignado" => $row["turnoAsignado"],
                "motivo" => $row["motivo"],
                "razon" => $row["razon"],
                "maquina" => $row["maquina"],
                "ibm" => $ibm
            ];
        }
        echo json_encode($array);
    }

    // Funcion que tiene las siguintes funciones:
    // modificar el estado de la solicitud para ello:
    // Cambia el estado de terminado a 1 para finalizar el proceso de la solicitud
    // Cambia los estados de autorizado y noempautoriza con los datos de la persona que autoriza o rechaza los datos
    // Con esto se completa el ciclo del envio y rechazo/confirmacion del tiempo extra
    function autorizafol()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $autor =$_GET["autor"];
        // Esta variable contiene el IBM de la persona que esta autorizando o rechazando el tiempo extra
        $session = $_SESSION['ibm'];
        // El query actualiza el registro del tiempo extra con el id seleccionado, asigna el valor de autorizado dependiendo de si se esta autorizando o rechazando, 
        // asigna el noempautoriza con el IBM de la persona que esta realizando la accion y cambia el estado de terminado a 1 para indicar que ya se proceso la solicitud
        $query = "UPDATE TiempoextraEnc SET autorizado=$autor, noempautoriza=$session, terminado=1  WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }

    function autorizafolSupInt()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $autor =$_GET["autor"];
        // Esta variable contiene el IBM de la persona que esta autorizando o rechazando el tiempo extra
        $session = $_SESSION['ibm'];
        // El query actualiza el registro del tiempo extra con el id seleccionado, asigna el valor de autorizado dependiendo de si se esta autorizando o rechazando, 
        // asigna el noempautoriza con el IBM de la persona que esta realizando la accion y cambia el estado de terminado a 1 para indicar que ya se proceso la solicitud
        $query = "UPDATE TiempoextraEnc SET autorizaSupInt=$autor  WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }    

    function actualizarHoraFin() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $folioRegistro = $_POST['folioRegistro'];
        $nuevaHoraFin = $_POST['nuevaHoraFin'];

        $query = "UPDATE TiempoextraSubEnc SET horaf = ?, validado = 1 WHERE id = ?";
        $params = array($nuevaHoraFin, $folioRegistro);

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode([
                "success" => false,
                "error" => $errors[0]['message'] ?? "sqlerror"
            ]);
        } else {
            echo json_encode(["success" => true]);
        }
    }

    // Actualizar horas con las del checador
    function actualizarHorasReales()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $folioRegistro   = $_POST['folioRegistro'];
        $nuevaHoraInicio = $_POST['nuevaHoraInicio'];
        $nuevaHoraFin    = $_POST['nuevaHoraFin'];

        $query  = "UPDATE TiempoextraSubEnc SET horai = ?, horaf = ?, validado = 1 WHERE id = ?";
        $params = array($nuevaHoraInicio, $nuevaHoraFin, $folioRegistro);
        $stmt   = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode([
                "success" => false,
                "error"   => $errors[0]['message'] ?? "sqlerror"
            ]);
        } else {
            echo json_encode(["success" => true]);
        }
    }
    
    // Funcion solo activa para tercer turno de 12 horas
    function actualizarEstadoValidado() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $folioRegistro = $_POST['folioRegistro'];        

        $query = "UPDATE TiempoextraSubEnc SET validado = 1 WHERE id = ?";
        $params = array($folioRegistro);

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors();
            echo json_encode([
                "success" => false,
                "error" => $errors[0]['message'] ?? "sqlerror"
            ]);
        } else {
            echo json_encode(["success" => true]);
        }
    }

    function reporteturno() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $folio = $_POST["folio"];
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $depto = $_POST["departamento"];

        $addwhere = "";
        $folio == '' ? 
            $addwhere = "WHERE Ctt_fecha BETWEEN '" . $fechai . "' and '" . $fechaf . "'" :
            $addwhere = "WHERE Ctt_folio = $folio";
        !empty($depto) ? $addwhere .= " AND Ctt_depto = '" . $depto . "'" : null;

        $query = "SELECT * FROM tblMXPRCambioTurnoTemporal $addwhere";
        $result = sqlsrv_query($conn, $query);
        $array = [];

        while ($row = sqlsrv_fetch_array($result)) {
            $estado = $row["Ctt_estado"];
            if ($estado == null || $estado == '') {
                $estadoClass = 'badge bg-warning text-dark';
                $estadoTexto = 'En espera';
            } else if ($estado == 1) {
                $estadoClass = 'badge bg-success';
                $estadoTexto = 'Aprobado';
            } else if ($estado == 2) {
                $estadoClass = 'badge bg-danger';
                $estadoTexto = 'Rechazado';
            }

            $array[] = [
                "Ctt_id" => $row["Ctt_id"],
                "Ctt_folio" => $row["Ctt_folio"],
                "Ctt_fecha" => $row["Ctt_fecha"]->format("Y-m-d"),
                "Ctt_depto" => $row["Ctt_depto"],
                "Ctt_de" => $row["Ctt_de"],
                "Ctt_a" => $row["Ctt_a"],
                "Ctt_tripulacion" => $row["Ctt_tripulacionDe"],
                "Ctt_horario" => $row["Ctt_horario"],
                "Ctt_rol" => $row["Ctt_rol"],
                "Ctt_aPartirDel" => $row["Ctt_aPartirDel"]->format("Y-m-d"),
                "Ctt_hastaEl" => $row["Ctt_hastaEl"]->format("Y-m-d"),
                "Ctt_horaPresentacion" => $row["Ctt_horaPresentacion"]->format("H:i:s"),
                "Ctt_turnoPresentacion" => $row["Ctt_turnoPresentacion"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto
            ];
        }

        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

}

if (isset($_GET["abrirtiempoextra"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->abrirtiempoextra();
} else if (isset($_GET["motivostiempoextra"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->motivostiempoextra();
} else if (isset($_GET["guardartiempoextra"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->guardartiempoextra();
} else if (isset($_GET["guardartiempoextraExt"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->guardartiempoextraExt();
} else if (isset($_GET["tblsubenc"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblsubenc();
} else if (isset($_GET["deletesub"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->deletesub();
} else if (isset($_GET["deleteModalSub"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->deleteModalSub();
} else if (isset($_GET["tblenc"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblenc();
} else if (isset($_GET["tblencfolio"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblencfolio();
} else if (isset($_GET["tblencfolioSupInt"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblencfolioSupInt();
} 
else if (isset($_GET["editenc"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->editenc();
} else if (isset($_GET["enviarfol"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->enviarfol();
} else if(isset($_GET['guardarCambioTurno'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->guardarCambioTurno();
} else if(isset($_GET['getDataReporte'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->getDataReporte();
} else if(isset($_GET['tblValidarTE'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblValidarTE();
} else if(isset($_GET['editarTE'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->editarTE();
} else if(isset($_GET['tblGetTE'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblGetTE();
} else if(isset($_GET['updateTE'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->updateTE();
} else if(isset($_GET['getSemana'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->getSemana();
} else if(isset($_GET['getExtrasAcumulados'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->getExtrasAcumulados();
} else if(isset($_GET['guardarSemana'])){
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->guardarSemana();
} else if (isset($_GET["tblValidarTEFolios"])) {
    $Tiempoextra = new Tiempoextra();
    $Tiempoextra->tblValidarTEFolios();
}


if (isset($_GET["reportegenral"])) {
    $Reportes = new Reportes();
    $Reportes->reportegenral();
} else if(isset($_GET['actualizarHoraFin'])) {
    $Reportes = new Reportes();
    $Reportes->actualizarHoraFin();
} else if(isset($_GET['actualizarHorasReales'])) {
    $Reportes = new Reportes();
    $Reportes->actualizarHorasReales();
} 
else if(isset($_GET['actualizarEstadoValidado'])) {
    $Reportes = new Reportes();
    $Reportes->actualizarEstadoValidado();
} 
else if (isset($_GET["tblautorizatp"])) {
    $Reportes = new Reportes();
    $Reportes->tblautorizatp();
} else if (isset($_GET["autorizafol"])) {
    $Reportes = new Reportes();
    $Reportes->autorizafol();
} else if (isset($_GET["autorizafolSupInt"])) {
    $Reportes = new Reportes();
    $Reportes->autorizafolSupInt();
} else if (isset($_GET["reporteturno"])) {
    $Reportes = new Reportes();
    $Reportes->reporteturno();
}
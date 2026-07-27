<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
require_once(__DIR__ . "/../../BDNominas/config.php"); // Carga de datos para datos de GERENTE

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

        // Sacar datos del gerente
        // $gerenteNum = "SELECT JefeInm FROM TLX032MXDB.dbo.tblEmpleados WHERE NoEmp = ?";
        // $resGerNum = sqlsrv_query($conn, $gerenteNum, [$supervisor]);
        // if ($resGerNum === false) {
        //     echo json_encode(["error" => sqlsrv_errors()]);
        //     exit;
        // }
        // $rowGerNum = sqlsrv_fetch_array($resGerNum, SQLSRV_FETCH_ASSOC);
        // $GerNum = $rowGerNum['JefeInm'];

        // Buscar jefe inmediato en CSV        
        $datosJefes = $this->buscarJefeInmediato($supervisor);
        $GerNum = $datosJefes["jefe"];
        $Superint = $datosJefes["superintendente"];

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

    function buscarJefeInmediato(string $ibmSupervisor): array {
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

        error_log("Buscando supervisor IBM=" . $ibmSupervisor);

        while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
            if (array_filter($line) === []) continue;

            if (count($line) < count($headers)) {
                $line = array_pad($line, count($headers), '');
            } elseif (count($line) > count($headers)) {
                $line = array_slice($line, 0, count($headers));
            }

            $row = @array_combine($headers, $line);
            if (!$row) continue;

            $num       = trim($row[COL_NUMERO] ?? '');
            $idJefe    = trim($row[COL_ID_JEFE] ?? '');
            $superint  = trim($row[COL_IBM] ?? '');

            if ($num !== '' && $num === trim($ibmSupervisor)) {
                if ($idJefe !== '') {
                    error_log("Supervisor $ibmSupervisor tiene jefe inmediato: $idJefe");
                    $resultado["jefe"] = $idJefe;
                } else {
                    error_log("Supervisor $ibmSupervisor encontrado pero sin jefe inmediato asignado");
                }

                if ($superint !== '') {
                    error_log("Supervisor $ibmSupervisor tiene superintendente asignado: $superint");
                    $resultado["superintendente"] = $superint;
                } else {
                    error_log("Supervisor $ibmSupervisor no tiene superintendente asignado");
                }

                break;
            }
        }
        fclose($handle);

        if ($resultado["jefe"] === null) {
            error_log("No se encontró coincidencia para IBM=" . $ibmSupervisor);
        }

        return $resultado;
    }
    // function buscarJefeInmediato(string $ibmSupervisor): ?string {
    //     if (!file_exists(CSV_NOMINAS_FILE)) {
    //         error_log("CSV no encontrado en: " . CSV_NOMINAS_FILE);
    //         return null;
    //     }
    //     $handle = fopen(CSV_NOMINAS_FILE, "r");
    //     if (!$handle) {
    //         error_log("No se pudo abrir el CSV");
    //         return null;
    //     }

    //     // Quitar BOM si existe
    //     $bom = fread($handle, 3);
    //     if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    //     $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    //     if (!$headers) { fclose($handle); return null; }

    //     // Normalizar encabezados (trim y quitar BOM residual)
    //     $headers = array_map(function($h) {
    //         return preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
    //     }, $headers);

    //     error_log("Encabezados: " . implode(", ", $headers));
    //     error_log("Buscando supervisor IBM=" . $ibmSupervisor);

    //     $encontrado = false;
    //     $jefe = null;

    //     while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
    //         if (array_filter($line) === []) continue;

    //         // Ajustar tamaño de fila
    //         if (count($line) < count($headers)) {
    //             $line = array_pad($line, count($headers), '');
    //         } elseif (count($line) > count($headers)) {
    //             $line = array_slice($line, 0, count($headers));
    //         }

    //         $row = @array_combine($headers, $line);
    //         if (!$row) {
    //             error_log("Fila inválida, no se pudo combinar encabezados con valores");
    //             continue;
    //         }

    //         $num = trim($row[COL_NUMERO] ?? '');
    //         $idJefe = trim($row[COL_ID_JEFE] ?? '');

    //         error_log("Fila NUMERO=$num | ID JEFE=$idJefe");

    //         if ($num !== '' && $num === trim($ibmSupervisor)) {
    //             $encontrado = true;
    //             $jefe = $idJefe !== '' ? $idJefe : null;
    //             break;
    //         }
    //     }
    //     fclose($handle);

    //     if ($encontrado) {
    //         if ($jefe) {
    //             error_log("Coincidencia encontrada. Jefe inmediato=" . $jefe);
    //             return $jefe;
    //         } else {
    //             error_log("Supervisor encontrado pero sin ID JEFE asignado");
    //             return null;
    //         }
    //     } else {
    //         error_log("No se encontró coincidencia para IBM=" . $ibmSupervisor);
    //         return null;
    //     }
    // }

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
    
    // Funcion de guardar datos en Detalles de tiempo extra
    function guardartiempoextra()
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
        $nombre = $_POST["nombre"];
        
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

        // Definicion de horas semanales porturnos
        $horasTurno = [
            "turno1" => 48,
            "turno2" => 45,
            "turno3" => 51,
            // "mixto1" => 60,
            // "mixto2" => 60,
            // "mixto3" => 57
            "mixto1" => 48,
            "mixto2" => 48,
            "mixto3" => 48,
            "mixto4" => 48
        ];

        // VALIDACION DE 60 HOPRAS POR EMPLEADO
        $queryTotal = "SELECT SUM(DATEDIFF(MINUTE, horai, horaf)) AS totalMinutos
               FROM TiempoextraSubEnc
               WHERE folio = ? AND noemp = ?";
        $paramsTotal = [$folio, $noemp];
        $resTotal = sqlsrv_query($conn, $queryTotal, $paramsTotal);
        $rowTotal = sqlsrv_fetch_array($resTotal, SQLSRV_FETCH_ASSOC);
        $totalMinutos = $rowTotal['totalMinutos'] ?? 0;
        $totalHorasRegistradas = $totalMinutos / 60;

        // Calcular horas del nuevo registro
        $horaiObj = new DateTime($horai);
        $horafObj = new DateTime($horaf);
        
        // Diferencia entre horas para el calculo de minutos        
        $turnoNuevoFor = "";

        if($turnosel === null || $turnosel === "") {
            $turnoNuevoFor = 'Sin turno';
        } else if($turnosel === "turno1") {
            $turnoNuevoFor = '1er Turno';
        } else if($turnosel === "turno2") {
            $turnoNuevoFor = '2do Turno';
        } else if($turnosel === "turno3") {
            $turnoNuevoFor = '3er Turno';
        } else if($turnosel === "mixto1") {
            $turnoNuevoFor = '1er Mixto';
        } else if($turnosel === "mixto2") {
            $turnoNuevoFor = '2do Mixto';
        } else if($turnosel === "mixto3") {
            $turnoNuevoFor = '3er Mixto';
        } else if($turnosel === "mixto4") {
            $turnoNuevoFor = '4to Mixto';
        }

        $nuevasHoras = ($horafObj->getTimestamp() - $horaiObj->getTimestamp()) / 3600;

        $horasBase = $horasTurno[$turnosel] ?? 0;
        $totalFinal = $horasBase + $totalHorasRegistradas + $nuevasHoras;

        // Validacion de dobletes CASO 1
        // $queryPrev = "SELECT TOP 1 horai, horaf, turnoAsignado
        //       FROM TiempoextraSubEnc
        //       WHERE folio = ? AND noemp = ?
        //       ORDER BY fecha DESC, horai DESC";
        // $paramsPrev = [$folio, $noemp];
        // $resPrev = sqlsrv_query($conn, $queryPrev, $paramsPrev);
        // $rowPrev = sqlsrv_fetch_array($resPrev, SQLSRV_FETCH_ASSOC);

        // $esDoblete = false;
        // $gapMinutos = 0;

        // if ($rowPrev) {                                        
        //     $prevHorai = $rowPrev['horai']->getTimestamp();
        //     $prevHoraf = $rowPrev['horaf']->getTimestamp();
        //     $nuevoHorai = $horaiObj->getTimestamp();

        //     // Normalizar cruces de medianoche
        //     if ($prevHoraf < $prevHorai) {
        //         $prevHoraf += 24*60*60;
        //     }            

        //     // Caso especial turno3_12hrs
        //     if ($rowPrev['turnoAsignado'] === 'turno3_12hrs') {
        //         $gapMinutos = ($nuevoHorai - strtotime("07:00")) / 60;
        //     } else {
        //         $gapMinutos = ($nuevoHorai - $prevHoraf) / 60;
        //     }

        //     if ($gapMinutos >= 360 && $gapMinutos <= 600) {
        //         $esDoblete = true;
        //     }            
        // }        


        // Busqueda de dobletes entre gaps (No analiza inicio de turno del dia siguiente)
        // if ($rowPrev) {
        //     // Construir DateTime completos con fecha + hora
        //     $prevInicio = new DateTime($rowPrev['fecha']->format('Y-m-d') . ' ' . $rowPrev['horai']->format('H:i:s'));
        //     $prevFin    = new DateTime($rowPrev['fecha']->format('Y-m-d') . ' ' . $rowPrev['horaf']->format('H:i:s'));
            
        //     // Ajuste general: si la salida es menor que la entrada → pasó al día siguiente
        //     if ($prevFin < $prevInicio) {
        //         $prevFin->modify('+1 day');
        //     }

        //     // Nuevo turno
        //     $nuevoInicio = new DateTime($fechainput . ' ' . $horai);
        //     $nuevoFin    = new DateTime($fechainput . ' ' . $horaf);

        //     if ($nuevoFin < $nuevoInicio) {
        //         $nuevoFin->modify('+1 day');
        //     }

        //     // Calcular gap en minutos
        //     $gapMinutos = ($nuevoInicio->getTimestamp() - $prevFin->getTimestamp()) / 60;

        //     // Depuración
        //     error_log(
        //         "DEBUG DOBLETE: prevHorai=" . $prevInicio->format('Y-m-d H:i:s') .
        //         " prevHoraf=" . $prevFin->format('Y-m-d H:i:s') .
        //         " nuevoHorai=" . $nuevoInicio->format('Y-m-d H:i:s') .
        //         " gapMinutos=" . $gapMinutos
        //     );

        //     // Validación de doblete: entre 6 y 10 horas (360–600 minutos)
        //     if ($gapMinutos >= 360 && $gapMinutos <= 600) {
        //         $esDoblete = true;
        //     }
        // }

        $queryPrev = "SELECT TOP 1 fecha, horai, horaf, turnoAsignado
              FROM TiempoextraSubEnc
              WHERE folio = ? AND noemp = ?
              ORDER BY fecha DESC, horai DESC";
        $paramsPrev = [$folio, $noemp];
        $resPrev = sqlsrv_query($conn, $queryPrev, $paramsPrev);
        $rowPrev = sqlsrv_fetch_array($resPrev, SQLSRV_FETCH_ASSOC);

        $esDoblete = false;
        $gapMinutos = 0;

        // Definición de horarios base por turno
        $turnos = [
            'turno1'      => ['inicio' => '07:00:00', 'fin' => '15:00:00'],
            'turno2'      => ['inicio' => '15:00:00', 'fin' => '22:30:00'],
            'turno3'      => ['inicio' => '22:30:00', 'fin' => '07:00:00'],
            'turno3_12hrs'=> ['inicio' => '19:00:00', 'fin' => '07:00:00'],
            'turno2_12hrs'=> ['inicio' => '11:30:00', 'fin' => '03:00:00'],
            'mixto1'      => ['inicio' => '07:30:00', 'fin' => '17:00:00'],
            'mixto2'      => ['inicio' => '08:30:00', 'fin' => '18:30:00'],
            'mixto3'      => ['inicio' => '07:00:00', 'fin' => '16:30:00'],
            'mixto4'      => ['inicio' => '07:00:00', 'fin' => '17:00:00'],
        ];

        // Turno del nuevo registro recibido desde el fetch
        $turnoNuevo = $_POST["turnosel"]; // 'turno1', 'turno2', etc.

        if ($rowPrev) {
            // prevFin: hora real en que terminó el tiempo extra anterior
            $prevInicio = new DateTime(
                $rowPrev['fecha']->format('Y-m-d') . ' ' . $rowPrev['horai']->format('H:i:s')
            );
            $prevFin = new DateTime(
                $rowPrev['fecha']->format('Y-m-d') . ' ' . $rowPrev['horaf']->format('H:i:s')
            );

            // Si horaf < horai, cruzó medianoche → sumar un día
            if ($prevFin < $prevInicio) {
                $prevFin->modify('+1 day');
            }

            // Ajuste para turno3_12hrs -> salida real es 07:00 del día siguiente
            if ($rowPrev['turnoAsignado'] === 'turno3_12hrs') {
                $prevFin = new DateTime($rowPrev['fecha']->format('Y-m-d') . ' 07:00:00');
                $prevFin->modify('+1 day'); // porque cruza medianoche
            }

            // Comparar contra el inicio del TURNO NORMAL del nuevo registro ──
            $nuevoInicioTurno = new DateTime($fechainput . ' ' . $turnos[$turnoNuevo]['inicio']);

            // Si el inicio del turno nuevo es anterior a prevFin, es del día siguiente
            // (ej: turno3 que inicia 22:30 pero prevFin cae al día siguiente)
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

            // Doblete: descanso entre 6 y 10 horas (360–600 min)
            if ($gapMinutos >= 360 && $gapMinutos <= 600) {
                $esDoblete = true;
            }
        }

        $warnings = [];

        // Validación de 60.5 hrs
        if ($totalFinal > 60.5) {
            $warnings[] = "excede las 60.5 horas semanales (Horas en total: $totalFinal hrs)";
        }

        // Validación de doblete
        if ($esDoblete) {
            $warnings[] = "genera un DOBLETE (descanso de $gapMinutos minutos entre turnos)";
        }

        // Mostrar mensaje combinado si hay alguna advertencia
        if (!empty($warnings)) {
            $mensaje = "El registro para $nombre con el turno $turnoNuevoFor " . implode(" y ", $warnings) . ". ¿Desea continuar?";
            echo json_encode(["warning" => true, "message" => $mensaje]);
            return;
        }
        
        // Construccion de query con el insert para el ingreso de datos
        // Detalles del tiempo extra -> Con el folio asociado a TiempoextraEnc
        $query = "INSERT INTO TiempoextraSubEnc(noemp,folio,fecha,horai,horaf,maquina,motivo,razon, turnoAsignado) 
                VALUES ('$noemp','$folio','$fechainput','$horai','$horaf','$maquina','$motivos','$razon','$turnosel')";

        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode("sqlerror") : json_encode("Listo");
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

    // // Parámetro de folio (opcional)
    // $folio = isset($_GET["folio"]) ? $_GET["folio"] : null;

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
        ";

    $params = [];

    // Si se pasa folio, agregamos filtro
    // if ($folio) {
    //     $query .= " WHERE TiempoextraSubEnc.folio = ? ";
    //     $params[] = $folio;
    // }

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
    function tblenc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $query = "SELECT 
        TiempoextraEnc.id,
        TiempoextraEnc.fecha,
        tblDepartamentos.NombreDepto,
        TiempoextraEnc.datesave,
        TiempoextraEnc.terminado, 
        TiempoextraEnc.autorizado 
        FROM TiempoextraEnc 
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = TiempoextraEnc.departamento 
        WHERE TiempoextraEnc.supervisor='" . $_SESSION['ibm'] . "' ORDER BY TiempoextraEnc.id DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        // Recorrer resultados en array para tener datos
        /*
            {id: 846, fecha: "2026-02-09", departamento: "Pañal", creado: "2026-02-17 12:54:40", terminado: null,…}
            autorizado: null
            creado: "2026-02-17 12:54:40"
            departamento: "Pañal"
            fecha: "2026-02-09"
            id: 846
            terminado: null
        */
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row["id"], "fecha" => $row["fecha"]->format("Y-m-d"), "departamento" => $row["NombreDepto"], "creado" => $row["datesave"]->format("Y-m-d H:i:s"), "terminado" => $row["terminado"]
            , "autorizado" => $row["autorizado"]]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

    // function tblencfolio()
    // {
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX003MXDB");

    //     $query = "SELECT 
    //         teEnc.id,
    //         teEnc.fecha,
    //         d.NombreDepto,
    //         teEnc.datesave,
    //         teEnc.terminado, 
    //         teEnc.autorizado,
    //         teEnc.supervisor,
    //         eSup.Nombre AS NombreSupervisor,
    //         -- contar registros no validados
    //         (SELECT COUNT(*) 
    //         FROM TLX003MXDB.dbo.TiempoextraSubEnc sub 
    //         WHERE sub.folio = teEnc.id AND (sub.validado IS NULL OR sub.validado <> 1)
    //         ) AS pendientesValidar
    //     FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
    //     INNER JOIN TLX009MXDB.dbo.tblDepartamentos d ON d.NoDepto = teEnc.departamento 
    //     INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup ON eSup.NoEmp = teEnc.supervisor
    //     WHERE teEnc.supervisor='" . $_SESSION['ibm'] . "' 
    //     ORDER BY teEnc.id DESC";

    //     $result = sqlsrv_query($conn, $query);
    //     $array = array();

    //     while ($row = sqlsrv_fetch_array($result)) {
    //         $autorizado = $row["autorizado"];

    //         if ($autorizado === null || $autorizado === '') {
    //             $estadoClass = 'badge bg-warning text-dark';
    //             $estadoTexto = 'En espera';
    //         } elseif ($autorizado == 1) {
    //             $estadoClass = 'badge bg-success';
    //             $estadoTexto = 'Aprobado';
    //         } elseif ($autorizado == 2) {
    //             $estadoClass = 'badge bg-danger';
    //             $estadoTexto = 'Rechazado';
    //         }

    //         $array[] = [
    //             "id" => $row["id"],
    //             "fecha" => $row["fecha"]->format("Y-m-d"),
    //             "departamento" => $row["NombreDepto"],
    //             "creado" => $row["datesave"]->format("Y-m-d H:i:s"),
    //             "terminado" => $row["terminado"],
    //             "autorizado" => $autorizado,
    //             "supervisor" => $row["supervisor"],
    //             "NombreSupervisor" => $row["NombreSupervisor"],
    //             "estadoClass" => $estadoClass,
    //             "estadoTexto" => $estadoTexto,
    //             "pendientesValidar" => $row["pendientesValidar"]
    //         ];
    //     }

    //     echo json_encode($array);
    // }
    function tblencfolio()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '22622'];

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
                        ) AS pendientesValidar
                    FROM TLX003MXDB.dbo.TiempoextraEnc teEnc
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos d 
                        ON d.NoDepto = teEnc.departamento 
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = teEnc.supervisor
                    WHERE teEnc.supervisor = ?";
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

    function tblencfolioSupInt()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '22622'];

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
                Ctt_PDFDir, Ctt_estado)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $params = array($folio, $folioTE, $fecha, $depto, $a, $de, $tripulacion, $horario, $rol, $aPartirDel, $hastaEl, $horaPresentacion, $turnoPresentacion, 
        $hastaTripulacion, $horarioDe, $horarioA, $descansos, $diaAdd, $horarioAdd, $pdfDir, $estado);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if($stmt){
            echo json_encode(["success"=>true, "message"=>"Guardado en BD"]);
        } else {
            echo json_encode(["success"=>false, "message"=>sqlsrv_errors()]);
        }
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
        $folio = $_POST["folio"];
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $addwhere = "";
        $folio == '' ? $addwhere = "WHERE TiempoextraSubEnc.fecha BETWEEN '" . $fechai . "' and '" . $fechaf . "'" :
            $addwhere = "WHERE folio = $folio";
        !empty($_POST["noemp"]) ? $addwhere .= 'AND TiempoextraSubEnc.noemp = ' . $_POST['noemp'] : null;
        !empty($_POST["folio"]) ? $addwhere .= 'AND TiempoextraSubEnc.folio = ' . $_POST['folio'] : null;
        !empty($_POST["departamento"]) ? $addwhere .= 'AND TiempoextraEnc.departamento = ' . $_POST['departamento'] : null;
        !empty($_POST["motivo"]) ? $addwhere .= 'AND TiempoextraSubEnc.motivo = ' . $_POST['motivo'] : null;
        // Solo se muestran datos de la persona que inicia la sesion 
        // Se agrego esta condicion para que los supervisores solo puedan ver los tiempos extra de las personas que ellos supervisan,
        // y los gerentes puedan ver todos los tiempos extra de su departamento
        $idGerente = "AND supervisor = '" . $_SESSION['ibm'] . "'";
        $query = "SELECT
                TiempoextraSubEnc.id, 
                TiempoextraEnc.id as folio, 
                TiempoextraSubEnc.noemp as noempsub,
                tblEmpleados.Nombre as nombresub,
                TiempoextraSubEnc.fecha,
                DATEDIFF(MINUTE,  TiempoextraSubEnc.horai,TiempoextraSubEnc.horaf) as dif, 
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
        $addwhere $idGerente";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
             $terminado = $row["autorizado"];

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
            
            $row["dif"] = number_format($row["dif"] / 60, 1);
            $row["dif"] <= 0 ? $row["dif"] = ($row["dif"] + 24) : NULL;
            array_push($array, [
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
            ]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }
    
    // Funcion de busqueda de tiempos extra para autorizarlos o rechazarlos
    // Permite mostrar los registros de x persona segun su ibm
    // Se usan los casos correspondientes del where segun su ibm
    function tblautorizatp()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION['ibm'];

        // Si es el IBM maestro, no se aplican filtros
        if ($ibm == '58998') {
            $where = "AND eSup.NoEmp = '" . $_SESSION['ibm'] . "'";
            $params = [];
        }
        // Si es el superusuario este ve todos los registros o solo los que estan a su nivel de permisos (supervisor) 
        else if($ibm == '51947'){
            $where = "";
            $params = [];
        } 
        else {
            // En el caso de que el usuario sea un supervisor este solo vera sus propios registros asignados por el supervisor
            $where = "WHERE teEnc.noempautoriza = ?";
            $params = [$ibm];
        }
    

        // Query principal para la busqueda de datos con base en los parametros asignados segun sea el caso
        $query = "SELECT TOP 100
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
                        eSol.Nombre AS NombreEmpleadoSol
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
                "Ctt_tripulacion" => $row["Ctt_tripulacion"],
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
}

if (isset($_GET["reportegenral"])) {
    $Reportes = new Reportes();
    $Reportes->reportegenral();
} else if(isset($_GET['actualizarHoraFin'])) {
    $Reportes = new Reportes();
    $Reportes->actualizarHoraFin();
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
}

else if (isset($_GET["reporteturno"])) {
    $Reportes = new Reportes();
    $Reportes->reporteturno();
}
<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
require_once(__DIR__ . "/../../BDNominas/config.php");
// require_once(__DIR__ . "/../../Vacaciones/config.php");

class Empleados
{
    function getDeptoPuesto()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX032MXDB");

        $ibm = $_POST["ibm"] ?? '';

        $query = "SELECT 
                    e.NoEmp,
                    d.NombreDepto AS depto,
                    p.Nombre AS puesto
                  FROM TLX032MXDB.dbo.tblEmpleados e
                  INNER JOIN TLX009MXDB.dbo.tblPuestos p ON p.id = e.Puesto
                  INNER JOIN TLX009MXDB.dbo.tblDepartamentos d ON d.NoDepto = e.NombreDepartamento
                  WHERE e.NoEmp = ?";
        
        $params = [$ibm];
        $result = sqlsrv_query($conn, $query, $params);

        $array = [];
        if ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array = [
                "noemp" => $row["NoEmp"],
                "depto" => $row["depto"],
                "puesto" => $row["puesto"]
            ];
        }

        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

    function guardarSolicitudVacaciones() 
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        // Recuperacion de dias por parametros
        $nombre = $_POST["nombre"] ?? '';
        $puesto = $_POST["puesto"] ?? '';
        $fecha_ingreso = $_POST["fecha_ingreso"] ?? '';
        $diasSolicitados = (int)($_POST["dias_solicitados"] ?? 0);
        $fechaDe = $_POST["vacaciones_de"] ?? '';
        $fechaHasta = $_POST["vacaciones_hasta"] ?? '';
        $totalDias = (int)($_POST["total_dias"] ?? 0);
        $solVacBy = (int)($_POST["solicitud_por"] ?? 0);
        $diasByAntiguedad = (int)($_POST["dias_antiguedad"] ?? 0);
        $priVacEq = (int)($_POST["prima_vacacional"] ?? 0);
        $diasRF = (int)($_POST["dias_reposicion"] ?? 0);
        $diasD = (int)($_POST["dias_descanso"] ?? '');
        $noTarjeta = (int)($_POST["tarjeta"] ?? 0);
        $depto = $_POST["departamento"] ?? '';
        $antiguedad = $_POST["antiguedad_de"] ?? '';
        $diasHabAp = $_POST["dias_habiles_partir"] ?? '';
        $periodo = $_POST["periodo_solicitado"] ?? '';
        $impOne = $_POST["importe1"] ?? '';
        $impTwo = $_POST["importe2"] ?? '';
        $impThree = $_POST["importe3"] ?? '';
        $impFour = $_POST["importe4"] ?? '';
        $impFive = $_POST["importe5"] ?? '';
        $observaciones = $_POST["observaciones"] ?? '';
        $fechasRF = $_POST["fechas_reposicion"] ?? '';
        $saldoPeriodo = $_POST["saldo_periodo"] ?? '';
        $diasHabiles = $_POST["dias_habiles_saldo"] ?? '';
        $tipoSol = $_POST["tipo_solicitud"] ?? '';

        // 1. Buscar jefe inmediato
        // $gerenteNum = "SELECT JefeInm FROM TLX032MXDB.dbo.tblEmpleados WHERE NoEmp = ?";
        // $resGerNum = sqlsrv_query($conn, $gerenteNum, [$noTarjeta]);
        // if ($resGerNum === false) {
        //     echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
        //     exit;
        // }
        // $rowGerNum = sqlsrv_fetch_array($resGerNum, SQLSRV_FETCH_ASSOC);
        // $GerNum = $rowGerNum['JefeInm'];

        // Obtener al gerente
        // $GerNum = $this->buscarJefeInmediato($noTarjeta);
        $datosJefes = $this->buscarJefeInmediato($noTarjeta);
        $GerNum = $datosJefes["jefe"];
        $Superint = $datosJefes["superintendente"];

        if (!$GerNum) {
            error_log("No se encontró jefe inmediato para supervisor=" . $noTarjeta);
            echo json_encode(["error" => "No se encontró jefe inmediato en BD Nóminas"]);
            exit;
        }

        error_log("Jefe inmediato encontrado=" . $GerNum);
        error_log("Superintendente=" . $Superint);

        // 2. Insertar en tblMXPRVacacionesEnc
        $queryEnc = "INSERT INTO 
                    tblMXPRVacacionesEnc (
                    Vc_ibm,
                    Vc_autorizado, 
                    Vc_autoriza, 
                    Vc_terminado,
                    Vc_revisado,
                    Vc_tipo,
                    Vc_noempSupIntendente)                    
                    OUTPUT INSERTED.Vc_id
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $paramsEnc = [$noTarjeta, 0, $GerNum, 0, 0, $tipoSol, $Superint];
        $resEnc = sqlsrv_query($conn, $queryEnc, $paramsEnc);
        if ($resEnc === false) {
            echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
            exit;
        }
        $rowEnc = sqlsrv_fetch_array($resEnc, SQLSRV_FETCH_ASSOC);
        $vc_id = $rowEnc['Vc_id'];

        // 3. Insertar en tblMXPRVacacionesSubEnc
        $querySub = "INSERT INTO tblMXPRVacacionesSubEnc
            (Vcs_nombre, 
            Vcs_vc_id, 
            Vcs_puesto, 
            Vcs_fingreso, 
            Vcs_solVacBy,
            Vcs_de, 
            Vcs_hasta,
            Vcs_diasByAntiguedad,
            Vcs_diasVacSol,             
            Vcs_priVacEq,
            Vcs_diasRF,
            Vcs_diasD,            
            Vcs_totalDias,
            Vcs_noTarjeta,
            Vcs_depto,
            Vcs_antiguedad,
            Vcs_diasHabAp,
            Vcs_periodo,
            Vcs_impOne,
            Vcs_impTwo,
            Vcs_impThree,
            Vcs_impFour,
            Vcs_impFive,
            Vcs_Observacion,
            Vcs_fechasRF,
            Vcs_saldoPeriodo,
            Vcs_diasHabiles)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $paramsSub = [$nombre, 
                      $vc_id, 
                      $puesto, 
                      $fecha_ingreso, 
                      $solVacBy, 
                      $fechaDe, 
                      $fechaHasta, 
                      $diasByAntiguedad,                       
                      $diasSolicitados,
                      $priVacEq, 
                      $diasRF, 
                      $diasD, 
                      $totalDias,                      
                      $noTarjeta,
                      $depto,
                      $antiguedad,
                      $diasHabAp,
                      $periodo,
                      $impOne,
                      $impTwo,
                      $impThree,
                      $impFour,
                      $impFive,
                      $observaciones,
                      $fechasRF,
                      $saldoPeriodo,
                      $diasHabiles];
        $resSub = sqlsrv_query($conn, $querySub, $paramsSub);
        if ($resSub === false) {
            echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
            exit;
        }

        // 4. Insertar días en tblMXPRCalendarioVacaciones
        $fechaInicio = new DateTime($_POST['vacaciones_de']);
        $fechaFin    = new DateTime($_POST['vacaciones_hasta']);
        $cursor      = clone $fechaInicio;

        // 4. Insertar días en tblMXPRCalendarioVacaciones
        // Iterar sobre TODOS los campos del POST que empiecen con "dia_"
        // así capturamos cualquier día que el usuario haya marcado, sin depender del rango
        foreach ($_POST as $key => $campo) {
            if (strpos($key, 'dia_') !== 0) continue; // solo campos dia_YYYY-MM-DD
            if ($campo === '') continue;               // ignorar días sin tipo asignado

            $fechaStr = substr($key, 4); // quitar el prefijo "dia_"

            // Validar que sea una fecha real
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaStr);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fechaStr) continue;

            $dia = (int)$fechaObj->format('d');

            $queryDia = "INSERT INTO tblMXPRCalendarioVacaciones
                            (Cav_folio, Cav_dia, Cav_fecha, Cav_seleccionado, Cav_tipoDia)
                        VALUES (?, ?, ?, ?, ?)";
            $paramsDia = [$vc_id, $dia, $fechaStr, 1, $campo];
            sqlsrv_query($conn, $queryDia, $paramsDia);
        }

        echo json_encode(["success" => true, "vc_id" => $vc_id]);
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

    function actualizarSolicitudVacaciones() {
        header('Content-Type: application/json');

        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        // El folio viene en POST como el id real (no base64)
        $folio = $_POST["folio"] ?? '';
        if ($folio === '') {
            echo json_encode(["success" => false, "error" => "Folio vacío"]);
            exit;
        }

        // Normalizar entradas
        $nombre = normalizeString($_POST["nombre"] ?? '');
        $puesto = normalizeString($_POST["puesto"] ?? '');
        $fecha_ingreso = normalizeString($_POST["fecha_ingreso"] ?? '');
        $diasSolicitados = normalizeInt($_POST["dias_solicitados"] ?? null);
        $fechaDe = normalizeString($_POST["vacaciones_de"] ?? '');
        $fechaHasta = normalizeString($_POST["vacaciones_hasta"] ?? '');
        $totalDias = normalizeInt($_POST["total_dias"] ?? null);
        $solVacBy = normalizeInt($_POST["solicitud_por"] ?? null);
        $diasByAntiguedad = normalizeInt($_POST["dias_antiguedad"] ?? null);
        $priVacEq = normalizeString($_POST["prima_vacacional"] ?? '');
        $diasRF = normalizeInt($_POST["dias_reposicion"] ?? null);
        $diasD = normalizeInt($_POST["dias_descanso"] ?? null);
        $noTarjeta = normalizeInt($_POST["tarjeta"] ?? null);
        $depto = normalizeString($_POST["departamento"] ?? '');
        $antiguedad = normalizeString($_POST["antiguedad_de"] ?? '');
        $diasHabAp = normalizeString($_POST["dias_habiles_partir"] ?? '');
        $periodo = normalizeString($_POST["periodo_solicitado"] ?? '');
        $impOne = normalizeString($_POST["importe1"] ?? '');
        $impTwo = normalizeString($_POST["importe2"] ?? '');
        $impThree = normalizeString($_POST["importe3"] ?? '');
        $impFour = normalizeString($_POST["importe4"] ?? '');
        $impFive = normalizeString($_POST["importe5"] ?? '');
        $observaciones = normalizeString($_POST["observaciones"] ?? '');
        $fechasRF = normalizeString($_POST["fechas_reposicion"] ?? '');
        $saldoPeriodo = normalizeString($_POST["saldo_periodo"] ?? '');
        $diasHabiles = normalizeString($_POST["dias_habiles_saldo"] ?? '');
        $tipoSolicitud = normalizeString($_POST["tipo_sol"] ?? '');

        // Iniciar transacción
        if (!sqlsrv_begin_transaction($conn)) {
            echo json_encode(["success" => false, "error" => "No se pudo iniciar transacción"]);
            exit;
        }

        // 1. Update en tblMXPRVacacionesSubEnc        
        $querySub = "UPDATE tblMXPRVacacionesSubEnc SET
                Vcs_nombre = ?, 
                Vcs_puesto = ?, 
                Vcs_fingreso = ?, 
                Vcs_solVacBy = ?, 
                Vcs_de = ?, 
                Vcs_hasta = ?, 
                Vcs_diasByAntiguedad = ?, 
                Vcs_diasVacSol = ?, 
                Vcs_priVacEq = ?, 
                Vcs_diasRF = ?, 
                Vcs_diasD = ?, 
                Vcs_totalDias = ?, 
                Vcs_noTarjeta = ?, 
                Vcs_depto = ?, 
                Vcs_antiguedad = ?, 
                Vcs_diasHabAp = ?, 
                Vcs_periodo = ?, 
                Vcs_impOne = ?, 
                Vcs_impTwo = ?, 
                Vcs_impThree = ?, 
                Vcs_impFour = ?, 
                Vcs_impFive = ?, 
                Vcs_Observacion = ?, 
                Vcs_fechasRF = ?, 
                Vcs_saldoPeriodo = ?, 
                Vcs_diasHabiles = ?
            WHERE Vcs_vc_id = ?";

        $paramsSub = [
            $nombre, $puesto, $fecha_ingreso, $solVacBy, $fechaDe, $fechaHasta, $diasByAntiguedad,
            $diasSolicitados, $priVacEq, $diasRF, $diasD, $totalDias, $noTarjeta, $depto, $antiguedad,
            $diasHabAp, $periodo, $impOne, $impTwo, $impThree, $impFour, $impFive, $observaciones,
            $fechasRF, $saldoPeriodo, $diasHabiles, $folio
        ];

        $resSub = sqlsrv_query($conn, $querySub, $paramsSub);
        if ($resSub === false) {
            sqlsrv_rollback($conn);
            echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
            exit;
        }            

        // Marcar como revisado en tblMXPRVacacionesEnc para que Relaciones Industriales coloque su firma
        $queryEnc = "UPDATE tblMXPRVacacionesEnc SET Vc_revisado = 1 WHERE Vc_id = ?";
        $resEnc = sqlsrv_query($conn, $queryEnc, [$folio]);
        if ($resEnc === false) {
            sqlsrv_rollback($conn);
            echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
            exit;
        }
        
        // 2. Update calendario: eliminar y volver a insertar los seleccionados
            $deleteCal = "DELETE FROM tblMXPRCalendarioVacaciones WHERE Cav_folio = ?";
            $resDel = sqlsrv_query($conn, $deleteCal, [$folio]);
            if ($resDel === false) {
                sqlsrv_rollback($conn);
                echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
                exit;
            }

            // Recorrer todos los campos POST que empiecen con "dia_"
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'dia_') === 0 && $value !== '') {
                    $fechaStr = substr($key, 4); // quitar "dia_"
                    try {
                        $fechaObj = new DateTime($fechaStr);
                        $diaNum   = (int)$fechaObj->format('d');
                    } catch (Exception $e) {
                        continue; // si la fecha no es válida, saltar
                    }

                    $queryDia = "INSERT INTO tblMXPRCalendarioVacaciones 
                                (Cav_folio, Cav_dia, Cav_fecha, Cav_seleccionado, Cav_tipoDia)
                                VALUES (?, ?, ?, ?, ?)";
                    $paramsDia = [$folio, $diaNum, $fechaStr, 1, $value];
                    $resDia = sqlsrv_query($conn, $queryDia, $paramsDia);
                    if ($resDia === false) {
                        sqlsrv_rollback($conn);
                        echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
                        exit;
                    }
                }
            }

            sqlsrv_commit($conn);
            echo json_encode(["success" => true, "vc_id" => $folio]);
    }

    function eliminarVacacion() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode("Folio inválido");
            return;
        }

        // Eliminacion de folio
        $queryCal = "DELETE FROM tblMXPRCalendarioVacaciones WHERE Cav_folio = ?";
        sqlsrv_query($conn, $queryCal, [$id]);

        // Eliminacion de detalles
        $querySub = "DELETE FROM tblMXPRVacacionesSubEnc WHERE Vcs_vc_id = ?";
        sqlsrv_query($conn, $querySub, [$id]);

        // Eliminacion de dias en calendario
        $queryEnc = "DELETE FROM tblMXPRVacacionesEnc WHERE Vc_id = ?";
        $res = sqlsrv_query($conn, $queryEnc, [$id]);

        if ($res) {
            echo json_encode("Listo");
        } else {
            echo json_encode("Error");
        }
    }

    // Funciones para la autorizacion de vacaciones
    // function tblVacacionesEnc() {
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX002MXDB");

    //     $ibm = $_SESSION['ibm'];
    //     $admins = ['58998', '51947', '22622'];

    //     if (in_array($ibm, $admins)) {
    //         $query = "SELECT 
    //                     enc.Vc_id AS id,
    //                     enc.Vc_ibm AS noemp,
    //                     sub.Vcs_nombre AS nombre,
    //                     sub.Vcs_depto AS departamento,
    //                     sub.Vcs_de AS fecha,
    //                     enc.Vc_autorizado AS autorizado,
    //                     enc.Vc_firmaRI AS firmaRI
    //                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
    //                 INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
    //                     ON sub.Vcs_vc_id = enc.Vc_id
    //                 ORDER BY enc.Vc_id DESC";
    //         $params = [];
    //     } else {
    //         $query = "SELECT 
    //                     enc.Vc_id AS id,
    //                     enc.Vc_ibm AS noemp,
    //                     sub.Vcs_nombre AS nombre,
    //                     sub.Vcs_depto AS departamento,
    //                     sub.Vcs_de AS fecha,
    //                     enc.Vc_autorizado AS autorizado,
    //                     enc.Vc_firmaRI AS firmaRI
    //                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
    //                 INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
    //                     ON sub.Vcs_vc_id = enc.Vc_id
    //                 WHERE enc.Vc_autoriza = ?
    //                 ORDER BY enc.Vc_id DESC";
    //         $params = [$ibm];
    //     }

    //     $result = sqlsrv_query($conn, $query, $params);
    //     $array = [];

    //     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    //         $array[] = [
    //             "id" => $row["id"],
    //             "noemp" => $row["noemp"],
    //             "nombre" => $row["nombre"],
    //             "departamento" => $row["departamento"],
    //             "fecha" => $row["fecha"]->format("Y-m-d"),
    //             "autorizado" => $row["autorizado"],
    //             "firmaRI" => $row["firmaRI"]
    //         ];
    //     }

    //     echo json_encode($array);
    // }

    function tblDeptosDinam() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $query = "SELECT DISTINCT(Vcs_depto) AS departamentos FROM tblMXPRVacacionesSubEnc";
        $result = sqlsrv_query($conn, $query);
        $array = [];

        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = ["id" => $row[0], "nombre" => $row[0]];
        }
        echo json_encode($array);
    }

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

    function tblVacacionesEnc() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;
        $deptoFiltro = $_GET['depto'] ?? null;

        if (in_array($ibm, $admins)) {
            $query = "SELECT 
                        enc.Vc_id AS id,
                        enc.Vc_ibm AS noemp,
                        sub.Vcs_nombre AS nombre,
                        sub.Vcs_depto AS departamento,
                        sub.Vcs_de AS fecha,
                        enc.Vc_autorizado AS autorizado,
                        enc.Vc_firmaRI AS firmaRI,
                        enc.Vc_noempSupIntendente,
                        enc.Vc_autSupIn,
                        enc.Vc_revisado
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
                    INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                        ON sub.Vcs_vc_id = enc.Vc_id
                    WHERE 1=1";
            $params = [];
        } else {
            $query = "SELECT 
                        enc.Vc_id AS id,
                        enc.Vc_ibm AS noemp,
                        sub.Vcs_nombre AS nombre,
                        sub.Vcs_depto AS departamento,
                        sub.Vcs_de AS fecha,
                        enc.Vc_autorizado AS autorizado,
                        enc.Vc_firmaRI AS firmaRI,
                        enc.Vc_noempSupIntendente,
                        enc.Vc_autSupIn,
                        enc.Vc_revisado
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
                    INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                        ON sub.Vcs_vc_id = enc.Vc_id
                    WHERE enc.Vc_autoriza = ?";
            $params = [$ibm];
        }

        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND sub.Vcs_de = ?";
            $params[] = $fechaFiltro;
        }

        // Filtro por estatus
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            $query .= " AND enc.Vc_revisado = ?";
            $params[] = (int)$estatusFiltro;
        }

        // Filtro por estatus
        if ($deptoFiltro) {
            $query .= " AND sub.Vcs_depto = ?";
            $params[] = $deptoFiltro;
        }

        $query .= " ORDER BY enc.Vc_id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "id" => $row["id"],
                "noemp" => $row["noemp"],
                "nombre" => $row["nombre"],
                "departamento" => $row["departamento"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "autorizado" => $row["autorizado"],
                "firmaRI" => $row["firmaRI"],
                "Vc_noempSupIntendente" => $row["Vc_noempSupIntendente"],
                "Vc_autSupIn" => $row["Vc_autSupIn"],
                "Vc_revisado" => $row["Vc_revisado"]
            ];
        }

        echo json_encode($array);
    }    

    // Query para reportes desde vista de supervisor en epartadop de consulta
    // function tblVacacionesEncSupervisor() {
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX002MXDB");

    //     $ibm = $_SESSION['ibm'];
    //     $admins = ['58998', '51947', '22622', '55268', '53224', '60040'];

    //     $fechaFiltro = $_GET['fecha'] ?? null;
    //     $estatusFiltro = $_GET['estatus'] ?? null;

    //     $params = [];

    //     if (in_array($ibm, $admins)) {
    //         // Admin ve todo
    //         $query = "SELECT enc.Vc_id AS id, enc.Vc_ibm AS noemp, sub.Vcs_nombre AS nombre,
    //                         sub.Vcs_depto AS departamento, sub.Vcs_de AS fecha,
    //                         enc.Vc_autorizado AS autorizado,
    //                         enc.Vc_revisado AS revisado,
    //                         enc.Vc_firmaRI AS firmaRI
    //                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
    //                 INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
    //                     ON sub.Vcs_vc_id = enc.Vc_id
    //                 WHERE 1=1";
    //     } else {
    //         $ibmsSubordinados = $this->obtenerEmpleadosDeSupervisor($ibm);
    //         $ibmsSubordinados[] = $ibm;

    //         if (empty($ibmsSubordinados)) {
    //             echo json_encode([]);
    //             return;
    //         }

    //         $placeholders = implode(',', array_fill(0, count($ibmsSubordinados), '?'));
    //         $query = "SELECT enc.Vc_id AS id, enc.Vc_ibm AS noemp, sub.Vcs_nombre AS nombre,
    //                         sub.Vcs_depto AS departamento, sub.Vcs_de AS fecha,
    //                         enc.Vc_autorizado AS autorizado,
    //                         enc.Vc_revisado AS revisado,
    //                         enc.Vc_firmaRI AS firmaRI
    //                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
    //                 INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
    //                     ON sub.Vcs_vc_id = enc.Vc_id
    //                 WHERE enc.Vc_ibm IN ($placeholders)";
    //         $params = $ibmsSubordinados;
    //     }

    //     if ($fechaFiltro) {
    //         $query .= " AND sub.Vcs_de = ?";
    //         $params[] = $fechaFiltro;
    //     }

    //     if ($estatusFiltro !== null && $estatusFiltro !== '') {
    //         $query .= " AND enc.Vc_autorizado = ?";
    //         $params[] = (int)$estatusFiltro;
    //     }

    //     $query .= " ORDER BY enc.Vc_id DESC";

    //     $result = sqlsrv_query($conn, $query, $params);
    //     $array = [];

    //     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    //         $array[] = [
    //             "id" => $row["id"],
    //             "noemp" => $row["noemp"],
    //             "nombre" => $row["nombre"],
    //             "departamento" => $row["departamento"],
    //             "fecha" => $row["fecha"]->format("Y-m-d"),
    //             "autorizado" => $row["autorizado"],
    //             "revisado" => $row["revisado"],
    //             "firmaRI" => $row["firmaRI"]
    //         ];
    //     }

    //     echo json_encode($array);
    // }

    // function tblVacacionesEncSupervisor() {
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX002MXDB");

    //     $ibm = $_SESSION['ibm'];
    //     $admins = ['58998', '51947', '22622', '55268', '53224', '60040'];

    //     $fechaFiltro = $_GET['fecha'] ?? null;
    //     $estatusFiltro = $_GET['estatus'] ?? null;
    //     $ibmFiltro = $_GET['ibmFiltro'] ?? null;

    //     $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    //     $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
    //     $offset = ($page - 1) * $pageSize;

    //     $params = [];

    //     if (in_array($ibm, $admins)) {
    //         $query = "SELECT enc.Vc_id AS id, enc.Vc_ibm AS noemp, sub.Vcs_nombre AS nombre,
    //                         sub.Vcs_depto AS departamento, sub.Vcs_de AS fecha,
    //                         enc.Vc_autorizado AS autorizado,
    //                         enc.Vc_revisado AS revisado,
    //                         enc.Vc_firmaRI AS firmaRI
    //                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
    //                 INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
    //                     ON sub.Vcs_vc_id = enc.Vc_id
    //                 WHERE 1=1";
    //     } else {
    //         $ibmsSubordinados = $this->obtenerEmpleadosDeSupervisor($ibm);
    //         $ibmsSubordinados[] = $ibm;

    //         if (empty($ibmsSubordinados)) {
    //             echo json_encode([]);
    //             return;
    //         }

    //         $placeholders = implode(',', array_fill(0, count($ibmsSubordinados), '?'));
    //         $query = "SELECT enc.Vc_id AS id, enc.Vc_ibm AS noemp, sub.Vcs_nombre AS nombre,
    //                         sub.Vcs_depto AS departamento, sub.Vcs_de AS fecha,
    //                         enc.Vc_autorizado AS autorizado,
    //                         enc.Vc_revisado AS revisado,
    //                         enc.Vc_firmaRI AS firmaRI
    //                 FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
    //                 INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
    //                     ON sub.Vcs_vc_id = enc.Vc_id
    //                 WHERE enc.Vc_ibm IN ($placeholders)";
    //         $params = $ibmsSubordinados;
    //     }

    //     if ($fechaFiltro) {
    //         $query .= " AND sub.Vcs_de = ?";
    //         $params[] = $fechaFiltro;
    //     }

    //     if ($ibmFiltro) {
    //         $query .= " AND enc.Vc_ibm = ?";
    //         $params[] = $ibmFiltro;
    //     }

    //     if ($estatusFiltro !== null && $estatusFiltro !== '') {
    //         $query .= " AND enc.Vc_autorizado = ?";
    //         $params[] = (int)$estatusFiltro;
    //     }

    //     $query .= " ORDER BY enc.Vc_id DESC OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
    //     $params[] = $offset;
    //     $params[] = $pageSize;

    //     $result = sqlsrv_query($conn, $query, $params);
    //     $array = [];

    //     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    //         $array[] = [
    //             "id" => $row["id"],
    //             "noemp" => $row["noemp"],
    //             "nombre" => $row["nombre"],
    //             "departamento" => $row["departamento"],
    //             "fecha" => $row["fecha"]->format("Y-m-d"),
    //             "autorizado" => $row["autorizado"],
    //             "revisado" => $row["revisado"],
    //             "firmaRI" => $row["firmaRI"]
    //         ];
    //     }

    //     echo json_encode($array);
    // }

    function tblVacacionesEncSupervisor() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '22622', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;
        $ibmFiltro = $_GET['ibmFiltro'] ?? null;

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
        $offset = ($page - 1) * $pageSize;

        $params = [];

        // Base query
        if (in_array($ibm, $admins)) {
            $baseQuery = "FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
                        INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                        ON sub.Vcs_vc_id = enc.Vc_id
                        WHERE 1=1";
        } else {
            $ibmsSubordinados = $this->obtenerEmpleadosDeSupervisor($ibm);
            $ibmsSubordinados[] = $ibm;

            if (empty($ibmsSubordinados)) {
                echo json_encode(["total" => 0, "data" => []]);
                return;
            }

            $placeholders = implode(',', array_fill(0, count($ibmsSubordinados), '?'));
            $baseQuery = "FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
                        INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                        ON sub.Vcs_vc_id = enc.Vc_id
                        WHERE enc.Vc_ibm IN ($placeholders)";
            $params = $ibmsSubordinados;
        }

        // Filtros
        if ($fechaFiltro) {
            $baseQuery .= " AND sub.Vcs_de = ?";
            $params[] = $fechaFiltro;
        }

        if ($ibmFiltro) {
            $baseQuery .= " AND enc.Vc_ibm = ?";
            $params[] = $ibmFiltro;
        }

        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            $baseQuery .= " AND enc.Vc_autorizado = ?";
            $params[] = (int)$estatusFiltro;
        }

        // Query total
        $countQuery = "SELECT COUNT(*) AS total " . $baseQuery;
        $countResult = sqlsrv_query($conn, $countQuery, $params);
        $countRow = sqlsrv_fetch_array($countResult, SQLSRV_FETCH_ASSOC);
        $total = $countRow["total"];

        // Query datos paginados
        $dataQuery = "SELECT enc.Vc_id AS id, enc.Vc_ibm AS noemp, sub.Vcs_nombre AS nombre,
                            sub.Vcs_depto AS departamento, sub.Vcs_de AS fecha,
                            enc.Vc_autorizado AS autorizado,
                            enc.Vc_revisado AS revisado,
                            enc.Vc_firmaRI AS firmaRI
                    " . $baseQuery . "
                    ORDER BY enc.Vc_id DESC
                    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
        $paramsData = array_merge($params, [$offset, $pageSize]);

        $result = sqlsrv_query($conn, $dataQuery, $paramsData);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "id" => $row["id"],
                "noemp" => $row["noemp"],
                "nombre" => $row["nombre"],
                "departamento" => $row["departamento"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "autorizado" => $row["autorizado"],
                "revisado" => $row["revisado"],
                "firmaRI" => $row["firmaRI"]
            ];
        }

        echo json_encode(["total" => $total, "data" => $array]);
    }


    function obtenerEmpleadosDeSupervisor(string $ibmSupervisorSesion): array {
        $empleados = [];

        // Ruta directa al CSV de sindicalizados
        // $csvFile = __DIR__ . "../uploads/SINDICALIZADO_CON_LA_RELACION_DE_LOS_SUPERVISORES.csv";
        $csvFile = dirname(__DIR__) . "/uploads/SINDICALIZADO_CON_LA_RELACION_DE_LOS_SUPERVISORES.csv";
        error_log(">>> [obtenerEmpleadosDeSupervisor] Supervisor en sesión: $ibmSupervisorSesion");
        error_log(">>> [obtenerEmpleadosDeSupervisor] Ruta CSV: $csvFile");

        if (!file_exists($csvFile)) {
            error_log(">>> CSV no encontrado");
            return $empleados;
        }
        $handle = fopen($csvFile, "r");
        if (!$handle) {
            error_log(">>> No se pudo abrir el CSV");
            return $empleados;
        }

        $separator = ",";
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $headers = fgetcsv($handle, 0, $separator);
        if (!$headers) {
            error_log(">>> No se pudieron leer encabezados");
            fclose($handle);
            return $empleados;
        }
        error_log(">>> Encabezados leídos: " . implode(" | ", $headers));

        $supervisorCols = [
            'IBM DEL SUPERVISOR1',
            'IBM DEL SUPERVISOR2',
            'IBM DEL SUPERVISOR3',
            'IBM DEL SUPERVISOR4',
            'IBM DEL SUPERVISOR5',
            'IBM DEL SUPERVISOR6',
        ];

        $rowNum = 0;
        while (($line = fgetcsv($handle, 0, $separator)) !== false) {
            $rowNum++;
            if (array_filter($line) === []) continue;
            $row = array_combine($headers, array_pad($line, count($headers), ''));

            foreach ($supervisorCols as $col) {
                $valor = trim($row[$col] ?? '');
                if ($valor !== '') {
                    error_log("Fila $rowNum: SupervisorCol=$col Valor=$valor");
                }
                if ($valor === trim($ibmSupervisorSesion)) {
                    $empleadoIBM = trim($row['IBM'] ?? '');
                    error_log(">>> MATCH encontrado en fila $rowNum: empleado=$empleadoIBM bajo supervisor=$ibmSupervisorSesion");
                    $empleados[] = $empleadoIBM;
                    break;
                }
            }
        }
        fclose($handle);

        error_log(">>> Total empleados encontrados: " . count($empleados));
        return $empleados;
    }

    function tblVacacionesEncSupInt() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '22622', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;

        if (in_array($ibm, $admins)) {
            $query = "SELECT 
                        enc.Vc_id AS id,
                        enc.Vc_ibm AS noemp,
                        sub.Vcs_nombre AS nombre,
                        sub.Vcs_depto AS departamento,
                        sub.Vcs_de AS fecha,
                        enc.Vc_autorizado AS autorizado,
                        enc.Vc_firmaRI AS firmaRI,
                        enc.Vc_noempSupIntendente,
                        enc.Vc_autSupIn
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
                    INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                        ON sub.Vcs_vc_id = enc.Vc_id
                    WHERE 1=1
                    AND Vc_noempSupIntendente IS NOT NULL";
            $params = [];
        } else {
            $query = "SELECT 
                        enc.Vc_id AS id,
                        enc.Vc_ibm AS noemp,
                        sub.Vcs_nombre AS nombre,
                        sub.Vcs_depto AS departamento,
                        sub.Vcs_de AS fecha,
                        enc.Vc_autorizado AS autorizado,
                        enc.Vc_firmaRI AS firmaRI,
                        enc.Vc_noempSupIntendente,
                        enc.Vc_autSupIn
                    FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
                    INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                        ON sub.Vcs_vc_id = enc.Vc_id
                    WHERE enc.Vc_noempSupIntendente = ?";
            $params = [$ibm];
        }        

        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND sub.Vcs_de = ?";
            $params[] = $fechaFiltro;
        }

        // Filtro por estatus    
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            if ($estatusFiltro == 0) {
                // En espera de firma (NULL o 0)
                $query .= " AND (enc.Vc_autSupIn IS NULL OR enc.Vc_autSupIn = 0)";
            } else {
                $query .= " AND enc.Vc_autSupIn = ?";
                $params[] = (int)$estatusFiltro;
            }
        }

        $query .= " ORDER BY enc.Vc_id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "id" => $row["id"],
                "noemp" => $row["noemp"],
                "nombre" => $row["nombre"],
                "departamento" => $row["departamento"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "autorizado" => $row["autorizado"],
                "firmaRI" => $row["firmaRI"],
                "Vc_noempSupIntendente" => $row["Vc_noempSupIntendente"],
                "Vc_autSupIn" => $row["Vc_autSupIn"]
            ];
        }

        echo json_encode($array);
    }

    function tblVacacionesRIEnc() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '22622', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;

        if (in_array($ibm, $admins)) {
            $query = "SELECT 
                enc.Vc_id AS id,
                enc.Vc_ibm AS noemp,
                sub.Vcs_nombre AS nombre,
                sub.Vcs_depto AS departamento,
                sub.Vcs_de AS fecha,
                enc.Vc_autorizado AS autorizado,
                enc.Vc_revisado AS revisado,
                enc.Vc_firmaRI AS firmaRI
            FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
            INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                ON sub.Vcs_vc_id = enc.Vc_id
            WHERE 1=1";
            $params = [];
        } else {
            $query = "SELECT 
                enc.Vc_id AS id,
                enc.Vc_ibm AS noemp,
                sub.Vcs_nombre AS nombre,
                sub.Vcs_depto AS departamento,
                sub.Vcs_de AS fecha,
                enc.Vc_autorizado AS autorizado,
                enc.Vc_revisado AS revisado,
                enc.Vc_firmaRI AS firmaRI
            FROM TLX002MXDB.dbo.tblMXPRVacacionesEnc enc
            INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesSubEnc sub 
                ON sub.Vcs_vc_id = enc.Vc_id
            WHERE enc.Vc_autoriza = ?";
            $params = [$ibm];
        }

        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND sub.Vcs_de = ?";
            $params[] = $fechaFiltro;
        }

        // Filtro por estado de firma RI
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            if ($estatusFiltro == 0) {
                // En espera de firma (NULL o 0)
                $query .= " AND (enc.Vc_firmaRI IS NULL OR enc.Vc_firmaRI = 0)";
            } else {
                $query .= " AND enc.Vc_firmaRI = ?";
                $params[] = (int)$estatusFiltro;
            }
        }

        $query .= " ORDER BY enc.Vc_id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "id" => $row["id"],
                "noemp" => $row["noemp"],
                "nombre" => $row["nombre"],
                "departamento" => $row["departamento"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "autorizado" => $row["autorizado"],
                "revisado" => $row["revisado"],
                "firmaRI" => $row["firmaRI"]
            ];
        }

        echo json_encode($array);
    }


    function autorizaVac() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $id = $_GET["id"];
        $autor = $_GET["autor"];
        $session = $_SESSION['ibm'];

        $query = "UPDATE tblMXPRVacacionesEnc 
                SET Vc_autorizado = ?, Vc_autoriza = ?, Vc_terminado = 1 
                WHERE Vc_id = ?";
        $params = [$autor, $session, $id];
        $result = sqlsrv_query($conn, $query, $params);

        echo json_encode($result === false ? "Errorsql" : "Listo");
    }

    // Funcion de autorización para superintendente
    function autorizaVacSupInt() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $id = $_GET["id"];
        $autor = $_GET["autor"];
        $session = $_SESSION['ibm'];

        $query = "UPDATE tblMXPRVacacionesEnc 
                SET Vc_autSupIn = ?
                WHERE Vc_id = ?";
        $params = [$autor, $id];
        $result = sqlsrv_query($conn, $query, $params);

        echo json_encode($result === false ? "Errorsql" : "Listo");
    }

    function reporteVacaciones() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $fechai = $_POST["fechai"] ?? null;
        $fechaf = $_POST["fechaf"] ?? null;
        $folio = $_POST["folio"] ?? null;
        $noemp = $_POST["noemp"] ?? null;
        $departamento = $_POST["departamento"] ?? null;

        $query = "SELECT enc.Vc_id AS folio,
                        enc.Vc_ibm AS noemp,
                        sub.Vcs_nombre AS nombre,
                        sub.Vcs_depto AS departamento,
                        sub.Vcs_puesto AS puesto,
                        sub.Vcs_de As del,
                        sub.Vcs_hasta As hasta,
                        sub.Vcs_diasByAntiguedad AS diasPorAntiguedad,
                        sub.Vcs_totalDias AS totalDias,
                        enc.Vc_autorizado AS autorizado,
                        enc.Vc_tipo AS tipoSolicitud,
                        enc.Vc_revisado,
                        enc.Vc_firmaRI
                FROM tblMXPRVacacionesEnc enc
                INNER JOIN tblMXPRVacacionesSubEnc sub ON sub.Vcs_vc_id = enc.Vc_id
                WHERE 1=1";

        $params = [];
        if ($fechai && $fechaf) {
            $query .= " AND sub.Vcs_de BETWEEN ? AND ?";
            $params[] = $fechai;
            $params[] = $fechaf;
        }
        if ($folio) {
            $query .= " AND enc.Vc_id = ?";
            $params[] = $folio;
        }
        if ($noemp) {
            $query .= " AND enc.Vc_ibm = ?";
            $params[] = $noemp;
        }
        if ($departamento) {
            $query .= " AND sub.Vcs_depto = ?";
            $params[] = $departamento;
        }

        $query .= " ORDER BY enc.Vc_id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $queryDias = "SELECT Cav_fecha, Cav_tipoDia 
                FROM tblMXPRCalendarioVacaciones 
                WHERE Cav_folio = ?";
        $resultDias = sqlsrv_query($conn, $queryDias, [$row["folio"]]);
        $dias = [];
        while ($d = sqlsrv_fetch_array($resultDias, SQLSRV_FETCH_ASSOC)) {
            $dias[] = [
                "fecha" => $d["Cav_fecha"]->format("Y-m-d"),
                "tipo" => $d["Cav_tipoDia"]
            ];
        }           

        $array[] = [
            "folio" => $row["folio"],
            "noemp" => $row["noemp"],
            // "nombre" => $row["nombre"],
            "nombre" => mb_convert_case(strtolower($row["nombre"]), MB_CASE_TITLE, "UTF-8"),
            "puesto" => $row["puesto"],
            "departamento" => $row["departamento"],
            "del" => $row["del"]->format("Y-m-d"),
            "hasta" => $row["hasta"]->format("Y-m-d"),
            "diasPorAntiguedad" => $row["diasPorAntiguedad"],
            "totalDias" => $row["totalDias"],
            "autorizado" => $row["autorizado"],
            "tipoSolicitud" => $row["tipoSolicitud"],
            "diasCalendario" => $dias,
            "Vc_firmaRI" => $row["Vc_firmaRI"],
            "Vc_revisado" => $row["Vc_revisado"]
        ];

        }
        echo json_encode($array);
    }

    function enviarSolicitudVacaciones()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $query = "SELECT * 
                FROM TLX002MXDB.dbo.tblMXPRVacacionesSubEnc";
        
        $array = [];
        $result = sqlsrv_query($conn, $query);

        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }

    function firmarVac() {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");

        $id = $_GET['id'] ?? '';
        if ($id === '') {
            echo json_encode(["success" => false, "error" => "Folio vacío"]);
            exit;
        }

        $query = "UPDATE tblMXPRVacacionesEnc SET Vc_firmaRI = 1 WHERE Vc_id = ?";
        $res = sqlsrv_query($conn, $query, [$id]);

        if ($res === false) {
            echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
            exit;
        }

        echo json_encode(["success" => true]);
    }

    function diasSolicitados() {
        $folio = $_GET["folio"] ?? null;
        if (!$folio) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            exit;
        }

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");

        $query = "SELECT Cav_fecha AS fechasSolicitada
                FROM TLX002MXDB.dbo.tblMXPRCalendarioVacaciones
                WHERE Cav_folio = ?";
        $params = [$folio];
        $result = sqlsrv_query($conn, $query, $params);

        $array = [];
        if ($result !== false) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $array[] = [
                    "fecha" => $row["fechasSolicitada"] instanceof DateTime
                        ? $row["fechasSolicitada"]->format("Y-m-d")
                        : null
                ];
            }
        }

        sqlsrv_close($conn);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($array);
        
    }

    function datoshoraysalida(){
        $noemp = $_GET["noemp"] ?? null;
        $fecha = $_GET["fechabien"] ?? null;

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX001MXDB");

        $query = "SELECT pin, CONVERT(time, event_time) AS fecha_h
                FROM TLX001MXDB.dbo.acc_transaction
                INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=pin 
                WHERE pin = ? AND CONVERT(DATE, event_time) = ?;";

        $params = [$noemp, $fecha];
        $result = sqlsrv_query($conn, $query, $params);

        $array = [];
        if ($result !== false) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $array[] = [
                    "pin" => $row["pin"],
                    "fecha_h" => $row["fecha_h"] instanceof DateTime
                        ? $row["fecha_h"]->format('H:i:s')
                        : null
                ];
            }
        }

        sqlsrv_close($conn);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($array);
        
    }

}

function normalizeInt($val) {
    if ($val === null) return null;
    $v = trim((string)$val);
    if ($v === '' || strtolower($v) === 'null') return null;
    return (int)$v;
}

function normalizeString($val) {
    if ($val === null) return '';
    $v = trim((string)$val);
    if (strtolower($v) === 'null') return '';
    return $v;
}

if (isset($_GET["getDeptoPuesto"])) {
    $Empleados = new Empleados();
    $Empleados->getDeptoPuesto();
} else if(isset($_GET["guardarSolicitudVacaciones"])) {
    $Empleados = new Empleados();
    $Empleados->guardarSolicitudVacaciones();
} else if(isset($_GET["enviarSolicitudVacaciones"])) {
    $Empleados = new Empleados();
    $Empleados->enviarSolicitudVacaciones();
}
else if(isset($_GET["autorizaVac"])) {
    $Empleados = new Empleados();
    $Empleados->autorizaVac();
}
else if(isset($_GET["autorizaVacSupInt"])) {
    $Empleados = new Empleados();
    $Empleados->autorizaVacSupInt();
}
else if(isset($_GET["tblVacacionesEnc"])) {
    $Empleados = new Empleados();
    $Empleados->tblVacacionesEnc();
} else if(isset($_GET["tblVacacionesEncSupInt"])) {
    $Empleados = new Empleados();
    $Empleados->tblVacacionesEncSupInt();
} else if(isset($_GET["tblVacacionesEncSupervisor"])) {
    $Empleados = new Empleados();
    $Empleados->tblVacacionesEncSupervisor();
}

else if (isset($_GET["tblDeptosDinam"])) {
    $Empleados = new Empleados();
    $Empleados->tblDeptosDinam();
}

else if(isset($_GET["tblVacacionesRIEnc"])) {
    $Empleados = new Empleados();
    $Empleados->tblVacacionesRIEnc();
}
else if(isset($_GET["reporteVacaciones"])) {
    $Empleados = new Empleados();
    $Empleados->reporteVacaciones();
}
else if(isset($_GET["actualizarSolicitudVacaciones"])) {
    $Empleados = new Empleados();
    $Empleados->actualizarSolicitudVacaciones();
}
else if(isset($_GET["firmarVac"])) {
    $Empleados = new Empleados();
    $Empleados->firmarVac();
}
else if (isset($_GET["diasSolicitados"])) {
    $Empleados = new Empleados();
    $Empleados ->diasSolicitados();
    
}
else if (isset($_GET["datoshoraysalida"])) {
    $Empleados = new Empleados();
    $Empleados ->datoshoraysalida();    
}
else if (isset($_GET["eliminarVacacion"])){
    $Empleados = new Empleados();
    $Empleados ->eliminarVacacion();
}
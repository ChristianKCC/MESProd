<?php
require_once('../../Session/seguridad.php');
require_once('../../conexion.php');
require_once(__DIR__ . "/../../BDNominas/config.php"); // Carga de datos para datos de GERENTE

// CLASE PRINCIPAL PARA LOS CAMBIOS DE PUESTO
class Cambiopuesto
{
    // Funcion principal que sirve para abrir un registro en la principal de CambiopuestoEnc, el unico valor que no se agrega es terminado ya que al ser nuevo se va en null
    function abrircambiopuesto()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion('TLX003MXDB');
        $supervisor = $_SESSION['ibm'];
        $fechainicio = $_POST['fechainicio'];
        $nosemana = $_POST['nosemana'];

        // Obtener al gerente        
        $datosJefes = $this->buscarJefeInmediato($supervisor);
        $GerNum = $datosJefes["jefe"];
        $Superint = $datosJefes["superintendente"];
        $departamento = $_POST["departamentoenc"];

        // Cambios de puesto  →  antes de $query = "INSERT INTO CambiopuestoEnc..."
        $GerNum = $this->resolverJefeConDelegacion($GerNum);
        $Superint = $this->resolverJefeConDelegacion($Superint);

        if (!$GerNum) {
            error_log("No se encontró jefe inmediato para supervisor=" . $supervisor);
            echo json_encode(["error" => "No se encontró jefe inmediato en BD Nóminas"]);
            exit;
        }
        error_log("Jefe inmediato encontrado=" . $GerNum);

        $query = "INSERT INTO CambiopuestoEnc(
                supervisor,
                fecha,
                fechacreacion,
                noempautoriza,
                noSemana,
                noempSupIntendente,
                departamento
              )
              VALUES (?, ?, GETDATE(), ?, ?, ?, ?);
              SELECT SCOPE_IDENTITY() AS id;";
        $params = [$supervisor, $fechainicio, $GerNum, $nosemana, $Superint, $departamento];

        $result = sqlsrv_query($conn, $query, $params);
        sqlsrv_next_result($result);
        sqlsrv_fetch($result);
        $folioget = sqlsrv_get_field($result, 0);
        echo $result === false ? json_encode("sqlerror") : json_encode($folioget);
        sqlsrv_close($conn);
    }

    function resolverJefeConDelegacion($ibm)
    {
        if ($ibm === null || trim((string) $ibm) === '')
            return $ibm;
        $ibm = trim((string) $ibm);

        $conn = (new ClassConexion())->conexion("TLX002MXDB"); // BD de la tabla
        $sql = "SELECT TOP 1 IBMDelegado
            FROM dbo.tblMXPRDelegaciones
            WHERE IBMDelegante = ?
              AND Activo = 1
              AND CAST(GETDATE() AS DATE) BETWEEN FechaInicio AND FechaFin
            ORDER BY FechaRegistro DESC";
        $stmt = sqlsrv_query($conn, $sql, [$ibm]);

        $efectivo = $ibm;
        if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            if (!empty($row['IBMDelegado'])) {
                $efectivo = trim((string) $row['IBMDelegado']);
                error_log("Delegación activa: $ibm -> $efectivo");
            }
        }
        if ($stmt)
            sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        return $efectivo;
    }

    function buscarJefeInmediato(string $ibmSupervisor): array
    {
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
        if ($bom !== "\xEF\xBB\xBF")
            rewind($handle);

        $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
        if (!$headers) {
            fclose($handle);
            return $resultado;
        }

        $headers = array_map(function ($h) {
            return preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
        }, $headers);

        error_log("Buscando supervisor IBM=" . $ibmSupervisor);

        while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
            if (array_filter($line) === [])
                continue;

            if (count($line) < count($headers)) {
                $line = array_pad($line, count($headers), '');
            } elseif (count($line) > count($headers)) {
                $line = array_slice($line, 0, count($headers));
            }

            $row = @array_combine($headers, $line);
            if (!$row)
                continue;

            $num = trim($row[COL_NUMERO] ?? '');
            $idJefe = trim($row[COL_ID_JEFE] ?? '');
            $superint = trim($row[COL_IBM] ?? '');

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

    function guardarcambiopuesto()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $noemp = $_POST["noemp"];
        $lunes = (int) $_POST["lunes"];
        $martes = (int) $_POST["martes"];
        $miercoles = (int) $_POST["miercoles"];
        $jueves = (int) $_POST["jueves"];
        $viernes = (int) $_POST["viernes"];
        $sabado = (int) $_POST["sabado"];
        $domingo = (int) $_POST["domingo"];
        $maquina = $_POST["maquina"];
        $puestoant = $_POST["puestoant"];
        $temportal = $_POST["temportal"];
        $folio = (int) $_POST["folio"];
        $ibmACubrir = trim((string) ($_POST["ibmACubrir"] ?? ''));
        $motivos = $_POST["motivos"];
        $porcion = in_array(($_POST["porcionTurno"] ?? 'completo'), ['completo', 'primera_mitad', 'segunda_mitad'])
            ? $_POST["porcionTurno"] : 'completo';
        $esExcepcion = (isset($_POST["esExcepcion"]) && $_POST["esExcepcion"] == '1') ? 1 : 0;
        $motivoExcepcion = trim($_POST["motivoExcepcion"] ?? '');

        $esVacante = ($ibmACubrir === '' || $ibmACubrir === '0');

        $rSem = sqlsrv_query($conn, "SELECT noSemana, fecha FROM CambiopuestoEnc WHERE id = ?", [$folio]);
        if ($rSem === false || !($enc = sqlsrv_fetch_array($rSem, SQLSRV_FETCH_ASSOC))) {
            echo json_encode("sqlerror");
            sqlsrv_close($conn);
            return;
        }
        $noSemana = $enc['noSemana'];
        $anioFolio = $enc['fecha']->format('Y');

        // Todos los registros de la semana (cualquier folio/depto)
        $qWeek = "SELECT sub.noemp, sub.ibmACubrir, sub.maquina, sub.puestotemporal, sub.porcionTurno,
                     sub.lunes, sub.martes, sub.miercoles, sub.jueves, sub.viernes, sub.sabado, sub.domingo
              FROM CambiopuestoSubEnc sub
              INNER JOIN CambiopuestoEnc enc ON enc.id = sub.folio
              WHERE enc.noSemana = ? AND YEAR(enc.fecha) = ?";
        $rWeek = sqlsrv_query($conn, $qWeek, [$noSemana, $anioFolio]);
        $semanaRegs = [];
        if ($rWeek !== false)
            while ($row = sqlsrv_fetch_array($rWeek, SQLSRV_FETCH_ASSOC))
                $semanaRegs[] = $row;

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $solapa = function ($a, $b) {
            return ($a === 'completo' || $b === 'completo') ? true : ($a === $b);
        };
        $slotDe = function ($ibm, $maq, $pt) {
            return ($ibm === null || trim((string) $ibm) === '')
                ? ('V|' . $maq . '|' . $pt) : ('P|' . trim((string) $ibm));
        };
        $nuevoSlot = $esVacante ? ('V|' . $maquina . '|' . $temportal) : ('P|' . $ibmACubrir);

        // $diasConflicto = [];
        // $haySlot = false;
        // $hayCoverer = false;

        // foreach ($dias as $di) {
        //     if ($$di != 1)
        //         continue;                       // día solicitado activo
        //     $slotCount = 0;
        //     $slotOverlap = false;
        //     $covererClash = false;

        //     foreach ($semanaRegs as $ex) {
        //         if ($ex[$di] != 1)
        //             continue;
        //         $exSlot = $slotDe($ex['ibmACubrir'], $ex['maquina'], $ex['puestotemporal']);

        //         // Mismo slot cubierto (persona o vacante) → duplicado si se traslapan porciones
        //         if ($exSlot === $nuevoSlot) {
        //             $slotCount++;
        //             if ($solapa($porcion, $ex['porcionTurno']))
        //                 $slotOverlap = true;
        //         }
        //         // Mismo empleado cubriendo OTRO slot el mismo día → coverer duplicado
        //         if ((string) $ex['noemp'] === (string) $noemp && $exSlot !== $nuevoSlot) {
        //             $covererClash = true;
        //         }
        //     }

        //     if ($slotOverlap || $slotCount >= 2)
        //         $haySlot = true;
        //     if ($covererClash)
        //         $hayCoverer = true;
        //     if ($slotOverlap || $slotCount >= 2 || $covererClash)
        //         $diasConflicto[] = $di;
        // }

        // if (!empty($diasConflicto) && !$esExcepcion) {
        //     $tipo = ($haySlot && $hayCoverer) ? 'ambos' : ($hayCoverer ? 'coverer' : 'slot');
        //     echo json_encode([
        //         "estado" => "DuplicadoSemana",
        //         "dias" => $diasConflicto,
        //         "semana" => $noSemana,
        //         "tipo" => $tipo,
        //         "vacante" => $esVacante ? 1 : 0
        //     ]);
        //     sqlsrv_close($conn);
        //     return;
        // }
        // if (!empty($diasConflicto) && $esExcepcion && $motivoExcepcion === '') {
        //     echo json_encode(["estado" => "FaltaMotivoExcepcion"]);
        //     sqlsrv_close($conn);
        //     return;
        // }

        $diasBloqueo = [];   // slot ya cubierto → bloqueo definitivo
        $diasCoverer = [];   // mismo empleado cubre a otra persona → excepción con motivo

        foreach ($dias as $di) {
            if ($$di != 1)
                continue;
            $cCompleto = 0;
            $cPrimera = 0;
            $cSegunda = 0;
            $covererClash = false;

            foreach ($semanaRegs as $ex) {
                if ($ex[$di] != 1)
                    continue;
                $exSlot = $slotDe($ex['ibmACubrir'], $ex['maquina'], $ex['puestotemporal']);

                if ($exSlot === $nuevoSlot) {
                    if ($ex['porcionTurno'] === 'completo')
                        $cCompleto++;
                    elseif ($ex['porcionTurno'] === 'primera_mitad')
                        $cPrimera++;
                    elseif ($ex['porcionTurno'] === 'segunda_mitad')
                        $cSegunda++;
                }
                if ((string) $ex['noemp'] === (string) $noemp && $exSlot !== $nuevoSlot)
                    $covererClash = true;
            }

            // ¿La porción solicitada choca con lo ya cubierto en ese día?
            $bloquea = false;
            if ($porcion === 'completo')
                $bloquea = ($cCompleto > 0 || $cPrimera > 0 || $cSegunda > 0);
            elseif ($porcion === 'primera_mitad')
                $bloquea = ($cCompleto > 0 || $cPrimera > 0);
            elseif ($porcion === 'segunda_mitad')
                $bloquea = ($cCompleto > 0 || $cSegunda > 0);

            if ($bloquea)
                $diasBloqueo[] = $di;
            if ($covererClash)
                $diasCoverer[] = $di;
        }

        // Slot ocupado → bloqueo duro, sin excepción posible
        if (!empty($diasBloqueo)) {
            echo json_encode([
                "estado" => "BloqueoSlot",
                "dias" => $diasBloqueo,
                "semana" => $noSemana,
                "vacante" => $esVacante ? 1 : 0
            ]);
            sqlsrv_close($conn);
            return;
        }

        // Coverer duplicado → requiere motivo de excepción
        if (!empty($diasCoverer) && !$esExcepcion) {
            echo json_encode([
                "estado" => "DuplicadoSemana",
                "dias" => $diasCoverer,
                "semana" => $noSemana,
                "tipo" => "coverer",
                "vacante" => $esVacante ? 1 : 0
            ]);
            sqlsrv_close($conn);
            return;
        }
        if (!empty($diasCoverer) && $esExcepcion && $motivoExcepcion === '') {
            echo json_encode(["estado" => "FaltaMotivoExcepcion"]);
            sqlsrv_close($conn);
            return;
        }

        $ins = "INSERT INTO CambiopuestoSubEnc
            (noemp,folio,maquina,lunes,martes,miercoles,jueves,viernes,sabado,domingo,
             puestoregular,puestotemporal,ibmACubrir,puestoActOcupado,motivos,
             porcionTurno,esExcepcion,motivoExcepcion)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NULL,?,?,?,?)";
        $params = [
            $noemp,
            $folio,
            $maquina,
            $lunes,
            $martes,
            $miercoles,
            $jueves,
            $viernes,
            $sabado,
            $domingo,
            $puestoant,
            $temportal,
            ($esVacante ? null : $ibmACubrir),
            $motivos,
            $porcion,
            $esExcepcion,
            ($esExcepcion ? $motivoExcepcion : null)
        ];
        $result = sqlsrv_query($conn, $ins, $params);

        echo json_encode($result === false ? 'sqlerror' : 'Listo');
        sqlsrv_close($conn);
    }

    // Funcion para el llenado de datos de la tabla principal que incluye los datos de:
    // id, noemp, Nombre, depto, puesto, dias de la semana, NombreMaquina, puestoant, regular
    function tblsubenc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $folio = $_GET["folio"];
        $query = "SELECT 
                    CambiopuestoSubEnc.id,
                    CambiopuestoSubEnc.folio,
                    CambiopuestoSubEnc.noemp,
                    tblEmpleados.Nombre,
                    tblDepartamentos.NombreDepto as depto,
                    tblPuestos.nombre as puesto,
                    CambiopuestoSubEnc.lunes,
                    CambiopuestoSubEnc.martes,
                    CambiopuestoSubEnc.miercoles,
                    CambiopuestoSubEnc.jueves,
                    CambiopuestoSubEnc.viernes,
                    CambiopuestoSubEnc.sabado,
                    CambiopuestoSubEnc.domingo,
                    CambiopuestoSubEnc.motivos,
                    tblMXPRmotivosCambioTemporalPuesto.nombre AS nombreMotivos,
                    tblMaquinas.NombreMaquina,
                    Cambiopuestolistpuestos.nombre as puestoant,
                    tbl2.nombre as regular FROM CambiopuestoSubEnc 
                INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = CambiopuestoSubEnc.noemp
                INNER JOIN TLX009MXDB.dbo.tblPuestos On tblPuestos.id= tblEmpleados.Puesto
                INNER JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
                INNER JOIN Cambiopuestolistpuestos ON Cambiopuestolistpuestos.id=CambiopuestoSubEnc.puestoregular
                INNER JOIN Cambiopuestolistpuestos as tbl2 ON tbl2.id=CambiopuestoSubEnc.puestotemporal
                INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= CambiopuestoSubEnc.maquina 
                LEFT JOIN TLX002MXDB.dbo.tblMXPRmotivosCambioTemporalPuesto ON tblMXPRmotivosCambioTemporalPuesto.id = CambiopuestoSubEnc.motivos
                WHERE CambiopuestoSubEnc.folio=$folio 
                ORDER BY CambiopuestoSubEnc.id DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row["id"],
                "folio" => $row["folio"],
                "noemp" => $row["noemp"],
                "nombre" => $row["Nombre"],
                "depto" => $row["depto"],
                "puesto" => $row["puesto"],
                "lunes" => $row["lunes"],
                "martes" => $row["martes"],
                "miercoles" => $row["miercoles"],
                "jueves" => $row["jueves"],
                "viernes" => $row["viernes"],
                "sabado" => $row["sabado"],
                "domingo" => $row["domingo"],
                "maquina" => $row["NombreMaquina"],
                "puestoant" => $row["puestoant"],
                "regular" => $row["regular"],
                "nombreMotivos" => $row["nombreMotivos"]
            ]);
        }
        echo $result === false ? json_encode('sqlerror') : json_encode($array);
        sqlsrv_close($conn);
    }

    // Funcion para traer los puestos y mostrarlos en puestos regulares y temporales
    // Solo el id y el nombre
    function slclistpuestos()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $query = "SELECT id, nombre FROM Cambiopuestolistpuestos";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row["id"], "nombre" => $row["nombre"]]);
        }
        echo $result === false ? json_encode('sqlerror') : json_encode($array);
        sqlsrv_close($conn);
    }

    //  Obtener puestos precisos para cambio de puestos
    function slcistpuestoscambiopuesto()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $query = "SELECT id, nombre 
                  FROM TLX003MXDB.dbo.Cambiopuestolistpuestos
                  WHERE id NOT IN (3,6,7)";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row["id"], "nombre" => $row["nombre"]]);
        }

        echo $result === false ? json_encode('sqlerror') : json_encode($array);
        sqlsrv_close($conn);
    }

    // ----------------------------------------------------------------------------
    // Funcion de llenado de tabla en ver folios
    // ----------------------------------------------------------------------------
    function tblenc()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '55268', '53224', '60040'];

        $base = "SELECT CambiopuestoEnc.*, tblEmpleados.Nombre as NombreEmpleado
             FROM TLX003MXDB.dbo.CambiopuestoEnc
             INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = CambiopuestoEnc.supervisor ";

        if (in_array($ibm, $admins)) {
            $query = $base . "WHERE fecha >= '2026-01-01' AND noempautoriza IS NOT NULL
                          ORDER BY CambiopuestoEnc.id DESC";
            $result = sqlsrv_query($conn, $query);
        } else {
            require_once(__DIR__ . "/departamentosPermitidos.php");
            $info = deptosPermitidosIBM($ibm);
            $deptos = $info['ids'];
            $propio = trim((string) ($_SESSION['clvDepartamento'] ?? ''));
            if ($propio !== '' && !in_array($propio, $deptos))
                $deptos[] = $propio;

            if (empty($deptos)) { // sin match en CSV → solo lo suyo
                $query = $base . "WHERE CambiopuestoEnc.supervisor = ? ORDER BY CambiopuestoEnc.id DESC";
                $result = sqlsrv_query($conn, $query, [$ibm]);
            } else {
                $ph = implode(',', array_fill(0, count($deptos), '?'));
                $query = $base . "WHERE CambiopuestoEnc.departamento IN ($ph) ORDER BY CambiopuestoEnc.id DESC";
                $result = sqlsrv_query($conn, $query, $deptos);
            }
        }

        $array = [];
        if ($result !== false)
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
                $array[] = [
                    "id" => $row["id"],
                    "supervisor" => $row["supervisor"],
                    "NombreEmpleado" => $row["NombreEmpleado"],
                    "fecha" => $row["fecha"]->format('Y-m-d'),
                    "terminado" => $row["terminado"]
                ];
        echo $result === false ? json_encode('sqlerror') : json_encode($array);
        sqlsrv_close($conn);
    }


    function tblencfolio()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");

        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '55268', '53224', '60040'];

        $fechaFiltro = $_GET['fecha'] ?? null;
        $estatusFiltro = $_GET['estatus'] ?? null;
        $ibmFiltro = $_GET['ibm'] ?? null;

        if (in_array($ibm, $admins)) {
            $query = "SELECT DISTINCT
                        CamPE.id,
                        CamPE.supervisor AS NoempSupervisor,
                        eSup.Nombre AS NombreSupervisor,
                        CamPE.fecha AS fechai,
                        DATEADD(DAY, 6, CamPE.fecha) AS fechaf,
                        CamPE.estadoTer,
                        CamPE.noempSupIntendente,
                        CamPE.autorizaSupInt,
                        (SELECT COUNT(*) 
                        FROM TLX003MXDB.dbo.CambiopuestoSubEnc sub 
                        WHERE sub.folio = CamPE.id
                        ) AS totalRegistros
                    FROM TLX003MXDB.dbo.CambiopuestoEnc CamPE
                    LEFT JOIN TLX003MXDB.dbo.CambiopuestoSubEnc CamPSub 
                        ON CamPSub.folio = CamPE.id
                    LEFT JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = CamPE.supervisor
                    WHERE 1=1
                    AND CamPE.fecha > '2026-01-01'
                    AND CamPE.noempautoriza IS NOT NULL";
            $params = [];
        } else {
            $query = "SELECT DISTINCT
                        CamPE.id,
                        CamPE.supervisor AS NoempSupervisor,
                        eSup.Nombre AS NombreSupervisor,
                        CamPE.fecha AS fechai,
                        DATEADD(DAY, 6, CamPE.fecha) AS fechaf,
                        CamPE.estadoTer,
                        CamPE.noempSupIntendente,
                        CamPE.autorizaSupInt,
                        (SELECT COUNT(*) 
                        FROM TLX003MXDB.dbo.CambiopuestoSubEnc sub 
                        WHERE sub.folio = CamPE.id
                        ) AS totalRegistros
                    FROM TLX003MXDB.dbo.CambiopuestoEnc CamPE
                    LEFT JOIN TLX003MXDB.dbo.CambiopuestoSubEnc CamPSub 
                        ON CamPSub.folio = CamPE.id
                    LEFT JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = CamPE.supervisor
                    WHERE CamPE.noempautoriza = ?";
            $params = [$ibm];
        }


        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND CONVERT(date, CamPE.fecha) = ?";
            $params[] = $fechaFiltro;
        }


        // Filtro por estatus
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            if ((int) $estatusFiltro === 0) {
                // Pendiente/En espera: estadoTer es NULL o vacío
                $query .= " AND (CamPE.estadoTer IS NULL OR CamPE.estadoTer = '')";
            } else {
                $query .= " AND CamPE.estadoTer = ?";
                $params[] = (int) $estatusFiltro;
            }
        }

        if ($ibmFiltro) {
            $query .= " AND CamPE.supervisor = ?";
            $params[] = $ibmFiltro;
        }

        $query .= " GROUP BY CamPE.id, CamPE.supervisor, eSup.Nombre, CamPE.estadoTer , CamPE.fecha, CamPE.terminado, CamPE.noempSupIntendente, CamPE.autorizaSupInt
                    ORDER BY CamPE.id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $autorizado = $row["estadoTer"];

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
                "NoempSupervisor" => $row["NoempSupervisor"],
                "NombreSupervisor" => $row["NombreSupervisor"],
                "fechai" => $row["fechai"]->format("Y-m-d"),
                "fechaf" => $row["fechaf"]->format("Y-m-d"),
                "estadoTer" => $row["estadoTer"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto,
                "noempSupIntendente" => $row["noempSupIntendente"],
                "autorizaSupInt" => $row["autorizaSupInt"],
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
        $ibmFiltro = $_GET['ibm'] ?? null;

        if (in_array($ibm, $admins)) {
            $query = "SELECT DISTINCT
                        CamPE.id,
                        CamPE.supervisor AS NoempSupervisor,
                        eSup.Nombre AS NombreSupervisor,
                        CamPE.fecha AS fechai,
                        DATEADD(DAY, 6, CamPE.fecha) AS fechaf,
                        CamPE.estadoTer,
                        CamPE.noempSupIntendente,
                        CamPE.autorizaSupInt
                    FROM TLX003MXDB.dbo.CambiopuestoEnc CamPE
                    INNER JOIN TLX003MXDB.dbo.CambiopuestoSubEnc CamPSub 
                        ON CamPSub.folio = CamPE.id
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = CamPE.supervisor
                    WHERE 1=1
                    AND CamPe.noempSupIntendente IS NOT NULL";
            $params = [];
        } else {
            $query = "SELECT DISTINCT
                        CamPE.id,
                        CamPE.supervisor AS NoempSupervisor,
                        eSup.Nombre AS NombreSupervisor,
                        CamPE.fecha AS fechai,
                        DATEADD(DAY, 6, CamPE.fecha) AS fechaf,
                        CamPE.estadoTer,
                        CamPE.noempSupIntendente,
                        CamPE.autorizaSupInt
                    FROM TLX003MXDB.dbo.CambiopuestoEnc CamPE
                    INNER JOIN TLX003MXDB.dbo.CambiopuestoSubEnc CamPSub 
                        ON CamPSub.folio = CamPE.id
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados eSup 
                        ON eSup.NoEmp = CamPE.supervisor
                    WHERE CamPE.noempSupIntendente = ?";
            $params = [$ibm];
        }

        // Filtro por fecha
        if ($fechaFiltro) {
            $query .= " AND CONVERT(date, CamPE.fecha) = ?";
            $params[] = $fechaFiltro;
        }

        // Filtro por estatus
        if ($estatusFiltro !== null && $estatusFiltro !== '') {
            if ((int) $estatusFiltro === 0) {
                // Pendiente/En espera: estadoTer es NULL o vacío
                $query .= " AND (CamPE.estadoTer IS NULL OR CamPE.estadoTer = '')";
            } else {
                $query .= " AND CamPE.estadoTer = ?";
                $params[] = (int) $estatusFiltro;
            }
        }

        if ($ibmFiltro) {
            $query .= " AND CamPE.supervisor = ?";
            $params[] = $ibmFiltro;
        }


        $query .= " GROUP BY CamPE.id, CamPE.supervisor, eSup.Nombre, CamPE.estadoTer , CamPE.fecha, CamPE.terminado, CamPE.noempSupIntendente, CamPE.autorizaSupInt
                    ORDER BY CamPE.id DESC";

        $result = sqlsrv_query($conn, $query, $params);
        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $autorizado = $row["estadoTer"];

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
                "NoempSupervisor" => $row["NoempSupervisor"],
                "NombreSupervisor" => $row["NombreSupervisor"],
                "fechai" => $row["fechai"]->format("Y-m-d"),
                "fechaf" => $row["fechaf"]->format("Y-m-d"),
                "estadoTer" => $row["estadoTer"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto,
                "noempSupIntendente" => $row["noempSupIntendente"],
                "autorizaSupInt" => $row["autorizaSupInt"]
            ];
        }

        echo json_encode($array);
    }


    // Funcion para autorizar el Cambio de puesto
    function autorizafol()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $autor = $_GET["autor"];

        // Esta variable contiene el IBM de la persona que esta autorizando o rechazando el tiempo extra
        $session = $_SESSION['ibm'];
        // El query actualiza el registro del cambio de puesto con el id seleccionado, asigna el valor de autorizado dependiendo de si se esta autorizando o rechazando, 
        // asigna el noempautoriza con el IBM de la persona que esta realizando la accion y cambia el estado de terminado a 1 para indicar que ya se proceso la solicitud
        $query = "UPDATE CambiopuestoEnc SET terminado=1, estadoTer=$autor, noempautoriza=$session WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }

    function autorizafolSupInt()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $autor = $_GET["autor"];

        // Esta variable contiene el IBM de la persona que esta autorizando o rechazando el tiempo extra
        $session = $_SESSION['ibm'];
        // El query actualiza el registro del cambio de puesto con el id seleccionado, asigna el valor de autorizado dependiendo de si se esta autorizando o rechazando, 
        // asigna el noempautoriza con el IBM de la persona que esta realizando la accion y cambia el estado de terminado a 1 para indicar que ya se proceso la solicitud
        $query = "UPDATE CambiopuestoEnc SET autorizaSupInt=? WHERE id=?";
        $params = [$autor, $id];
        $result = sqlsrv_query($conn, $query, $params);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }

    // Actualizacion de datos de CambiopuestoEnc en el campo de terminado a 1 para finalizar el registro
    function enviarfol()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET["id"];
        $query = "UPDATE CambiopuestoEnc SET terminado=1 WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        echo json_encode($result === false ? "Errorsql" : "Listo");
    }

    // Funcion para obtener de CambiopuestoEnc solo el id y la fecha basados en un Id
    function getheader()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "SELECT * FROM CambiopuestoEnc 
                    WHERE id=" . $id;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push(
                $array,
                [
                    "id" => $row["id"],
                    "fecha" => $row["fecha"]->format('Y-m-d')
                ]
            );
        }
        echo $result === false ? json_encode('sqlerror') : json_encode($array);
        sqlsrv_close($conn);
    }

    // Funcion para eliminacion de datos basados en un Id
    function deleteitemsub()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $id = $_POST['id'];
        $query = "DELETE FROM CambiopuestoSubEnc WHERE id=" . $id;
        $result = sqlsrv_query($conn, $query);
        echo $result === false ? json_encode('sqlerror') : json_encode('Listo');
        sqlsrv_close($conn);
    }

    function motivosCambioPuesto()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX002MXDB");
        // Query para consulta general de tiempos extra
        $query = "SELECT * FROM tblMXPRmotivosCambioTemporalPuesto";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        // Parseo de datos a array con keys para su identificacion
        while ($row = sqlsrv_fetch_array($result))
            array_push($array, ["id" => $row[0], "nombre" => $row[1]]);
        echo json_encode($array);
    }


    // ── Nueva: reporte general de coberturas (punto 6) ───────────────────────────
    // function reporteCoberturas()
    // {
    //     $ClassConexion = new ClassConexion();
    //     $conn = $ClassConexion->conexion("TLX003MXDB");
    //     $ibm = $_SESSION['ibm'];
    //     $admins = ['58998', '51947', '55268', '53224', '60040'];

    //     $semana = $_GET['semana'] ?? null;
    //     $anio = $_GET['anio'] ?? date('Y');
    //     $depto = $_GET['departamento'] ?? null;

    //     $where = " WHERE 1=1 ";
    //     $params = [];

    //     if (!in_array($ibm, $admins)) {
    //         require_once(__DIR__ . "/departamentosPermitidos.php");
    //         $info = deptosPermitidosIBM($ibm);
    //         $permitidos = $info['ids'];
    //         $propio = trim((string) ($_SESSION['clvDepartamento'] ?? ''));
    //         if ($propio !== '' && !in_array($propio, $permitidos))
    //             $permitidos[] = $propio;
    //         if (empty($permitidos))
    //             $permitidos = ['-1'];
    //         $ph = implode(',', array_fill(0, count($permitidos), '?'));
    //         $where .= " AND enc.departamento IN ($ph) ";
    //         foreach ($permitidos as $d)
    //             $params[] = $d;
    //     }
    //     if ($semana) {
    //         $where .= " AND enc.noSemana = ? AND YEAR(enc.fecha) = ? ";
    //         $params[] = $semana;
    //         $params[] = $anio;
    //     }
    //     if ($depto) {
    //         $where .= " AND enc.departamento = ? ";
    //         $params[] = $depto;
    //     }

    //     $query = "SELECT enc.id AS folio, enc.noSemana, enc.fecha, dep.NombreDepto,
    //                  sub.noemp AS noempCubre, empC.Nombre AS nombreCubre,
    //                  sub.ibmACubrir, empA.Nombre AS nombreCubierto,
    //                  sub.porcionTurno, sub.esExcepcion, sub.motivoExcepcion,
    //                  sub.lunes, sub.martes, sub.miercoles, sub.jueves, sub.viernes, sub.sabado, sub.domingo,
    //                  supEmp.Nombre AS supervisor, maq.NombreMaquina,
    //                  pReg.nombre AS puestoRegular, pTmp.nombre AS puestoTemporal
    //           FROM CambiopuestoSubEnc sub
    //           INNER JOIN CambiopuestoEnc enc ON enc.id = sub.folio
    //           INNER JOIN TLX032MXDB.dbo.tblEmpleados empC   ON empC.NoEmp   = sub.noemp
    //           INNER JOIN TLX032MXDB.dbo.tblEmpleados empA   ON empA.NoEmp   = sub.ibmACubrir
    //           INNER JOIN TLX032MXDB.dbo.tblEmpleados supEmp ON supEmp.NoEmp = enc.supervisor
    //           LEFT JOIN TLX009MXDB.dbo.tblDepartamentos dep ON dep.NoDepto  = enc.departamento
    //           LEFT JOIN TLX009MXDB.dbo.tblMaquinas maq      ON maq.NoMaquina = sub.maquina
    //           LEFT JOIN Cambiopuestolistpuestos pReg ON pReg.id = sub.puestoregular
    //           LEFT JOIN Cambiopuestolistpuestos pTmp ON pTmp.id = sub.puestotemporal
    //           $where
    //           ORDER BY enc.noSemana DESC, sub.ibmACubrir, sub.noemp";
    //     $result = sqlsrv_query($conn, $query, $params);

    //     $array = [];
    //     if ($result !== false)
    //         while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
    //             $array[] = [
    //                 "folio" => $row["folio"],
    //                 "noSemana" => $row["noSemana"],
    //                 "fecha" => $row["fecha"]->format('Y-m-d'),
    //                 "departamento" => $row["NombreDepto"],
    //                 "noempCubre" => $row["noempCubre"],
    //                 "nombreCubre" => $row["nombreCubre"],
    //                 "ibmACubrir" => $row["ibmACubrir"],
    //                 "nombreCubierto" => $row["nombreCubierto"],
    //                 "porcion" => $row["porcionTurno"],
    //                 "esExcepcion" => $row["esExcepcion"],
    //                 "motivoExcepcion" => $row["motivoExcepcion"],
    //                 "maquina" => $row["NombreMaquina"],
    //                 "puestoRegular" => $row["puestoRegular"],
    //                 "puestoTemporal" => $row["puestoTemporal"],
    //                 "supervisor" => $row["supervisor"],
    //                 "dias" => [
    //                     (int) $row["lunes"],
    //                     (int) $row["martes"],
    //                     (int) $row["miercoles"],
    //                     (int) $row["jueves"],
    //                     (int) $row["viernes"],
    //                     (int) $row["sabado"],
    //                     (int) $row["domingo"]
    //                 ]
    //             ];
    //     echo $result === false ? json_encode('sqlerror') : json_encode($array);
    //     sqlsrv_close($conn);
    // }

    function reporteCoberturas()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $ibm = $_SESSION['ibm'];
        $admins = ['58998', '51947', '55268', '53224', '60040'];

        $semana = $_GET['semana'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');
        $depto = $_GET['departamento'] ?? null;

        $where = " WHERE 1=1 ";
        $params = [];

        if (!in_array($ibm, $admins)) {
            require_once(__DIR__ . "/departamentosPermitidos.php");
            $info = deptosPermitidosIBM($ibm);
            $permitidos = $info['ids'];
            $propio = trim((string) ($_SESSION['clvDepartamento'] ?? ''));
            if ($propio !== '' && !in_array($propio, $permitidos))
                $permitidos[] = $propio;
            if (empty($permitidos))
                $permitidos = ['-1'];
            $ph = implode(',', array_fill(0, count($permitidos), '?'));
            $where .= " AND enc.departamento IN ($ph) ";
            foreach ($permitidos as $d)
                $params[] = $d;
        }
        if ($semana) {
            $where .= " AND enc.noSemana = ? AND YEAR(enc.fecha) = ? ";
            $params[] = $semana;
            $params[] = $anio;
        }
        if ($depto) {
            $where .= " AND enc.departamento = ? ";
            $params[] = $depto;
        }

        $query = "SELECT enc.id AS folio, enc.noSemana, enc.fecha,
                     DATEADD(DAY,6,enc.fecha) AS fechaFin,
                     enc.fechacreacion, dep.NombreDepto,
                     enc.supervisor AS ibmSupervisor, supEmp.Nombre AS supervisor,
                     sub.noemp AS noempCubre, empC.Nombre AS nombreCubre,
                     sub.ibmACubrir, empA.Nombre AS nombreCubierto,
                     sub.porcionTurno, sub.esExcepcion, sub.motivoExcepcion,
                     sub.lunes, sub.martes, sub.miercoles, sub.jueves, sub.viernes, sub.sabado, sub.domingo,
                     maq.NombreMaquina, pReg.nombre AS puestoRegular, pTmp.nombre AS puestoTemporal
              FROM CambiopuestoSubEnc sub
              INNER JOIN CambiopuestoEnc enc ON enc.id = sub.folio
              INNER JOIN TLX032MXDB.dbo.tblEmpleados empC   ON empC.NoEmp   = sub.noemp
              LEFT  JOIN TLX032MXDB.dbo.tblEmpleados empA   ON empA.NoEmp   = sub.ibmACubrir
              INNER JOIN TLX032MXDB.dbo.tblEmpleados supEmp ON supEmp.NoEmp = enc.supervisor
              LEFT JOIN TLX009MXDB.dbo.tblDepartamentos dep ON dep.NoDepto  = enc.departamento
              LEFT JOIN TLX009MXDB.dbo.tblMaquinas maq      ON maq.NoMaquina = sub.maquina
              LEFT JOIN Cambiopuestolistpuestos pReg ON pReg.id = sub.puestoregular
              LEFT JOIN Cambiopuestolistpuestos pTmp ON pTmp.id = sub.puestotemporal
              $where
              ORDER BY enc.noSemana DESC, sub.ibmACubrir, sub.noemp";
        $result = sqlsrv_query($conn, $query, $params);

        $array = [];
        if ($result !== false)
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC))
                $array[] = [
                    "folio" => $row["folio"],
                    "noSemana" => $row["noSemana"],
                    "fecha" => $row["fecha"]->format('Y-m-d'),
                    "fechaFin" => $row["fechaFin"]->format('Y-m-d'),
                    "fechaCreacion" => $row["fechacreacion"] ? $row["fechacreacion"]->format('Y-m-d H:i') : '',
                    "departamento" => $row["NombreDepto"],
                    "supervisor" => $row["supervisor"],
                    "ibmSupervisor" => $row["ibmSupervisor"],
                    "noempCubre" => $row["noempCubre"],
                    "nombreCubre" => $row["nombreCubre"],
                    "ibmACubrir" => $row["ibmACubrir"],
                    "nombreCubierto" => $row["nombreCubierto"],
                    "porcion" => $row["porcionTurno"],
                    "esExcepcion" => $row["esExcepcion"],
                    "motivoExcepcion" => $row["motivoExcepcion"],
                    "maquina" => $row["NombreMaquina"],
                    "puestoRegular" => $row["puestoRegular"],
                    "puestoTemporal" => $row["puestoTemporal"],
                    "dias" => [
                        (int) $row["lunes"],
                        (int) $row["martes"],
                        (int) $row["miercoles"],
                        (int) $row["jueves"],
                        (int) $row["viernes"],
                        (int) $row["sabado"],
                        (int) $row["domingo"]
                    ]
                ];
        echo $result === false ? json_encode('sqlerror') : json_encode($array);
        sqlsrv_close($conn);
    }

    function estadoCoberturaIBM()
    {
        $conn = (new ClassConexion())->conexion("TLX003MXDB");
        $ibm = $_GET['ibm'] ?? '';
        $semana = $_GET['semana'] ?? '';
        $anio = $_GET['anio'] ?? date('Y');

        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $final = array_fill_keys($dias, 'LIBRE');
        if ($ibm === '' || $semana === '') {
            echo json_encode($final);
            return;
        }

        $acc = array_fill_keys($dias, ['c' => 0, 'p' => 0, 's' => 0]);
        $q = "SELECT CPS.porcionTurno, CPS.lunes,CPS.martes,CPS.miercoles,CPS.jueves,CPS.viernes,CPS.sabado,CPS.domingo
          FROM CambiopuestoSubEnc CPS
          INNER JOIN CambiopuestoEnc CE ON CE.id = CPS.folio
          WHERE CPS.ibmACubrir = ? AND CE.noSemana = ? AND YEAR(CE.fecha) = ?";
        $r = sqlsrv_query($conn, $q, [$ibm, $semana, $anio]);
        if ($r !== false) {
            while ($row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC)) {
                foreach ($dias as $d) {
                    if ($row[$d] != 1)
                        continue;
                    if ($row['porcionTurno'] === 'completo')
                        $acc[$d]['c']++;
                    elseif ($row['porcionTurno'] === 'primera_mitad')
                        $acc[$d]['p']++;
                    elseif ($row['porcionTurno'] === 'segunda_mitad')
                        $acc[$d]['s']++;
                }
            }
        }
        foreach ($dias as $d) {
            $c = $acc[$d];
            if ($c['c'] > 0 || ($c['p'] > 0 && $c['s'] > 0))
                $final[$d] = 'TAKEN';
            elseif ($c['p'] > 0)
                $final[$d] = 'FREE_SEGUNDA'; // 1ª tomada → libre la 2ª
            elseif ($c['s'] > 0)
                $final[$d] = 'FREE_PRIMERA'; // 2ª tomada → libre la 1ª
            else
                $final[$d] = 'LIBRE';
        }
        echo json_encode($final);
        sqlsrv_close($conn);
    }

}

// Inicio de clase de reportes en PDF
class Reportes
{
    // Funcion de llenado de tabla en reportegnral (Reporte de cambio de puesto)
    function reportegenral()
    {
        $ClassConexion = new ClassConexion();
        $conn = $ClassConexion->conexion("TLX003MXDB");
        $folio = $_POST["folio"];
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $addwhere = "";
        $folio == '' ?
            $addwhere = "WHERE CambiopuestoEnc.fecha BETWEEN '" . $fechai . "' and '" . $fechaf . "'" :
            $addwhere = "WHERE folio = $folio";
        !empty($_POST["noemp"]) ? $addwhere .= 'AND CambiopuestoSubEnc.noemp = ' . $_POST['noemp'] : null;
        !empty($_POST["folio"]) ? $addwhere .= 'AND CambiopuestoSubEnc.folio = ' . $_POST['folio'] : null;
        !empty($_POST["departamento"]) ? $addwhere .= 'AND CambiopuestoSubEnc.maquina = ' . $_POST['departamento'] : null;
        $query = "SELECT 
                    CambiopuestoSubEnc.id, 
                    CambiopuestoEnc.id AS folio, 
                    CambiopuestoSubEnc.noemp AS noempsub,
                    tblEmpleados.Nombre AS nombresub,
                    CambiopuestoEnc.fecha AS fecha,
                    CambiopuestoEnc.terminado AS terminado,
                    tblMaquinas.NombreMaquina AS maquina,
                    CambiopuestoSubEnc.lunes,
                    CambiopuestoSubEnc.martes,
                    CambiopuestoSubEnc.miercoles,
                    CambiopuestoSubEnc.jueves,
                    CambiopuestoSubEnc.viernes,
                    CambiopuestoSubEnc.sabado,
                    CambiopuestoSubEnc.domingo,
                    listPuestos1.nombre AS puestoregular,
                    listPuestos2.nombre AS puestotemporal,
                    CambiopuestoEnc.estadoTer AS Estado_Termino,
                    CambiopuestoSubEnc.ibmACubrir,
                    empCubrir.Nombre AS nombreCubrir
                FROM CambiopuestoSubEnc
                INNER JOIN CambiopuestoEnc ON CambiopuestoEnc.id = CambiopuestoSubEnc.folio
                INNER JOIN TLX032MXDB.dbo.tblEmpleados AS tblEmpleados ON tblEmpleados.NoEmp = CambiopuestoSubEnc.noemp
                INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = CambiopuestoSubEnc.maquina
                INNER JOIN Cambiopuestolistpuestos AS listPuestos1 ON listPuestos1.id = CambiopuestoSubEnc.puestoregular
                INNER JOIN Cambiopuestolistpuestos AS listPuestos2 ON listPuestos2.id = CambiopuestoSubEnc.puestotemporal
                INNER JOIN TLX032MXDB.dbo.tblEmpleados AS empCubrir ON empCubrir.NoEmp = CambiopuestoSubEnc.ibmACubrir 
                $addwhere";
        $result = sqlsrv_query($conn, $query);
        $array = array();

        // Recorrido de datos
        while ($row = sqlsrv_fetch_array($result)) {
            $terminado = $row["Estado_Termino"];

            // Definicion de estados para los tiempos extra dependiendo del valor en terminado
            if ($terminado === null || $terminado === '') {
                $estadoClass = 'badge bg-warning text-dark';
                $estadoTexto = 'En espera de aprobación';
            } else if ($terminado == 1) {
                $estadoClass = 'badge bg-success';
                $estadoTexto = 'Aprobado';
            } else if ($terminado == 2) {
                $estadoClass = 'badge bg-danger';
                $estadoTexto = 'Rechazado';
            }

            array_push($array, [
                "id" => $row["id"],
                "folio" => $row["folio"],
                "noempsub" => $row["noempsub"],
                "nombresub" => $row["nombresub"],
                "fecha" => $row["fecha"]->format("Y-m-d"),
                "terminado" => $row["terminado"],
                "maquina" => $row["maquina"],
                "lunes" => $row["lunes"],
                "martes" => $row["martes"],
                "miercoles" => $row["miercoles"],
                "jueves" => $row["jueves"],
                "viernes" => $row["viernes"],
                "sabado" => $row["sabado"],
                "domingo" => $row["domingo"],
                "puestoregular" => $row["puestoregular"],
                "puestotemporal" => $row["puestotemporal"],
                "estadoClass" => $estadoClass,
                "estadoTexto" => $estadoTexto,
                "nombreACubrir" => $row["nombreCubrir"],
                "ibmACubrir" => $row["ibmACubrir"]

            ]);
        }
        echo $result === false ? json_encode("sqlerror") : json_encode($array);
    }
}



if (isset($_GET['abrircambiopuesto'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->abrircambiopuesto();
} else if (isset($_GET['guardarcambiopuesto'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->guardarcambiopuesto();
} else if (isset($_GET['tblsubenc'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->tblsubenc();
} else if (isset($_GET['tblencfolio'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->tblencfolio();
} else if (isset($_GET['tblencfolioSupInt'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->tblencfolioSupInt();
} else if (isset($_GET['autorizafol'])) {
    $Cambiopuesto = new CambioPuesto();
    $Cambiopuesto->autorizafol();
} else if (isset($_GET['autorizafolSupInt'])) {
    $Cambiopuesto = new CambioPuesto();
    $Cambiopuesto->autorizafolSupInt();
} else if (isset($_GET['slclistpuestos'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->slclistpuestos();
} else if (isset($_GET['slcistpuestoscambiopuesto'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->slcistpuestoscambiopuesto();
} else if (isset($_GET['tblenc'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->tblenc();
} else if (isset($_GET['getheader'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->getheader();
} else if (isset($_GET['deleteitemsub'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->deleteitemsub();
} else if (isset($_GET['enviarfol'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->enviarfol();
} else if (isset($_GET['motivosCambioPuesto'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->motivosCambioPuesto();
} else if (isset($_GET['reportegenral'])) {
    $Reportes = new Reportes();
    $Reportes->reportegenral();
} else if (isset($_GET['reporteCoberturas'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->reporteCoberturas();
} else if (isset($_GET['estadoCoberturaIBM'])) {
    $Cambiopuesto = new Cambiopuesto();
    $Cambiopuesto->estadoCoberturaIBM();
}
<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class CtrolTiempos
{
    function tblParos()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $folio = $_GET['folio'];
        $query = "SELECT tblSubEncabezadoBItacora.*,tblProduccionSecciones.Seccion,tblProduccionModulos.Modulos,tblFallas.DescripcionFalla,tblEncabezadoBitacora.NoMaquina FROM tblSubEncabezadoBItacora
        LEFT JOIN TLX002MXDB.dbo.tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblSubEncabezadoBItacora.NoSeccion
        LEFT JOIN TLX002MXDB.dbo.tblProduccionModulos ON tblProduccionModulos.idModulos = tblSubEncabezadoBItacora.NoModulo
        LEFT JOIN TLX009MXDB.dbo.tblFallas ON tblFallas.IdFalla = tblSubEncabezadoBItacora.IdFalla
        LEFT JOIN tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblSubEncabezadoBItacora.IdEncabezadoBItacora
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblEncabezadoBitacora.NoMaquina WHERE tblEncabezadoBitacora.NoMaquina = ? AND tblEncabezadoBitacora.IdEncabezadoBItacora=? ORDER BY tblSubEncabezadoBItacora.EstadoParo ASC, tblSubEncabezadoBItacora.IdSubEncabezadoBitacora DESC";
        $result = sqlsrv_query($conn, $query, array($_SESSION['idmaquina'], $folio));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['IdSubEncabezadoBitacora'],
                'folio' => $row['IdEncabezadoBItacora'],
                'seccion' => $row['NoSeccion'],
                'modulo' => $row['NoModulo'],
                'falla' => $row['IdFalla'],
                'comentarios' => $row['Comentarios'],
                'cortes' => $row['Cortes'],
                'rechazos' => $row['Rechazos'],
                'tarriba' => $row['TArriba'],
                'tabajo' => $row['TAbajo'],
                'hora' => $row['Hora']->format('Y-m-d H:i:s'),
                'paroxequipo' => $row['ParoPorEquipo'],
                'cortescorrida' => $row['CortesCorrida'],
                'rechazoscorrida' => $row['RechazosCorrida'],
                'tiempocorrida' => $row['TCorrida'],
                'tiempoparo' => $row['TParo'],
                'arranqueCorruendo' => $row['ArranqueCorriendo'],
                'paroxcalidad' => $row['PorCalidad'],
                'paroxtecnologia' => $row['NoTecnologia'],
                'velpromedio' => $row['VelProm'],
                'velocidad' => $row['VelStp'],
                'corridaseco' => $row['CorridaSeco'],
                'nombreseccion' => $row['Seccion'],
                'nombremodulo' => $row['Modulos'],
                'nombrefalla' => $row['DescripcionFalla'],
                'nomaquina' => $row['NoMaquina'],
                'estadoparo' => $row['EstadoParo']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function tblParosxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $folio = $_GET['folio'];
        $query = "SELECT tblSubEncabezadoBItacora.*,tblProduccionSecciones.idSeccion,tblProduccionModulos.idModulos,tblFallas.DescripcionFalla,tblEncabezadoBitacora.NoMaquina FROM tblSubEncabezadoBItacora
        LEFT JOIN TLX002MXDB.dbo.tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblSubEncabezadoBItacora.NoSeccion
        LEFT JOIN TLX002MXDB.dbo.tblProduccionModulos ON tblProduccionModulos.idModulos = tblSubEncabezadoBItacora.NoModulo
        LEFT JOIN TLX009MXDB.dbo.tblFallas ON tblFallas.IdFalla = tblSubEncabezadoBItacora.IdFalla
        LEFT JOIN tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblSubEncabezadoBItacora.IdEncabezadoBItacora
        LEFT JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblEncabezadoBitacora.NoMaquina WHERE tblSubEncabezadoBItacora.IdSubEncabezadoBitacora = $folio";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['IdSubEncabezadoBitacora'],
                'folio' => $row['IdEncabezadoBItacora'],
                'seccion' => $row['NoSeccion'],
                'modulo' => $row['NoModulo'],
                'falla' => $row['IdFalla'],
                'comentarios' => $row['Comentarios'],
                'cortes' => $row['Cortes'],
                'rechazos' => $row['Rechazos'],
                'tarriba' => $row['TArriba'],
                'tabajo' => $row['TAbajo'],
                'hora' => $row['Hora']->format('Y-m-d H:i:s'),
                'paroxequipo' => $row['ParoPorEquipo'],
                'cortescorrida' => $row['CortesCorrida'],
                'rechazoscorrida' => $row['RechazosCorrida'],
                'tiempocorrida' => $row['TCorrida'],
                'tiempoparo' => $row['TParo'],
                'arranqueCorruendo' => $row['ArranqueCorriendo'],
                'paroxcalidad' => $row['PorCalidad'],
                'paroxtecnologia' => $row['NoTecnologia'],
                'velpromedio' => $row['VelProm'],
                'velocidad' => $row['VelStp'],
                'corridaseco' => $row['CorridaSeco'],
                'motivo' => $row['Motivo'],
                'correccion' => $row['Correccion']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function updateDataParo()
    {

        // Primera conexión y actualización en TLX004MXDB
        $conexion1 = new ClassConexion();
        $conn1 = $conexion1->conexion('TLX004MXDB');

        $folio = $_POST['folio'];
        $seccion = $_POST['seccion'];
        $modulo = $_POST['modulo'];
        $motivo = $_POST['motivo'];
        $correccion = $_POST['correccion'];
        $motivosanitizacion = $_POST['motivosanitizacion'];
        $tiemposanitizacion = $_POST['tiemposanitizacion'];

        $query1 = "UPDATE tblBitacoraParosMaquinas 
                SET NoSeccion = ?, NoModulo = ?, Motivo = ?, Correccion = ?, EstadoParo = 1 
                WHERE id = ?";
        $result1 = sqlsrv_query($conn1, $query1, array($seccion, $modulo, $motivo, $correccion, $folio));

        // Segunda conexión e inserción en TLX002MXDB
        $conexion2 = new ClassConexion();
        $conn2 = $conexion2->conexion('TLX002MXDB');
        $empleados = json_decode($_POST['empleados'], true);

        // Guardar empleados (asumiendo que el método existe en la misma clase)
        $this->saveEmpSanitizacion($folio, $empleados);

        $query2 = "INSERT INTO tblBitSanitizacionEnc (idparo, motivo, tiempo) VALUES (?, ?, ?)";
        $result2 = sqlsrv_query($conn2, $query2, array($folio, $motivosanitizacion, $tiemposanitizacion));

        // Respuesta HTTP según resultados
        if ($result1 === false || $result2 === false) {
            http_response_code(500);
        } else {
            http_response_code(200);
        }

    }

    function saveEmpSanitizacion($folio, $dataemp)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        foreach ($dataemp as $employee) {
            $emp = $employee['id'];
            $sql = "INSERT INTO tblBitSanitizacionEmp (folio,NoEmp) VALUES (?, ?)";
            $params = array($folio, $emp);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                http_response_code(500);
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }

    public function crearNuevoParo()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $folio = $_POST['folio'];
        $seccion = $_POST['seccion'];
        $modulo = $_POST['modulo'];
        $cortes = $_POST['cortes'];
        $rechazos = $_POST['rechazos'];
        $tiempoparo = $_POST['tiempoparo'];
        $hora = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['hora']);
        $horaFormateada = $hora?->format('Y-m-d H:i:s');
        $estado = 1;
        $motivo = $_POST['motivo'];
        $correccion = $_POST['correccion'];
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

        // Validar el usuario y la contraseña
        $sql_validacion = "SELECT ContrasenaOpcional 
                        FROM TLX032MXDB.dbo.tblEmpleados 
                        WHERE NoEmp = ? AND ContrasenaOpcional = ?";
        $params_validacion = [$usuario, $password];
        $res_validacion = sqlsrv_query($conn, $sql_validacion, $params_validacion);

        if ($res_validacion === false) {
            http_response_code(501);
            die(json_encode(['error' => 'Error al ejecutar la consulta de validación']));
        }

        $usuario_valido = sqlsrv_fetch_array($res_validacion);

        if (!$usuario_valido) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
            return; // Esto detiene la ejecución de la función
        }

        $array = [
            $folio,
            $seccion,
            $modulo,
            $cortes,
            $rechazos,
            $horaFormateada,
            $tiempoparo,
            $estado,
            $motivo,
            $correccion
        ];

        $query = "INSERT INTO tblSubEncabezadoBItacora (IdEncabezadoBItacora, NoSeccion, NoModulo, Cortes, Rechazos, Hora, TParo, EstadoParo, Motivo, Correccion) 
                    VALUES (?,?,?,?,?,?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, $array);

        if ($result === false) {
            echo $result;
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }

    function eliminarParo()
    {

        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $folio = $_POST['folio'];
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];
        // Validar el usuario y la contraseña
        $sql_validacion = "SELECT ContrasenaOpcional
                        FROM TLX032MXDB.dbo.tblEmpleados 
                        WHERE NoEmp = ? AND ContrasenaOpcional = ?";
        $params_validacion = [$usuario, $password];
        $res_validacion = sqlsrv_query($conn, $sql_validacion, $params_validacion);

        if ($res_validacion === false) {
            http_response_code(501);
            die(json_encode(['error' => 'Error al ejecutar la consulta de validación']));
        }

        $usuario_valido = sqlsrv_fetch_array($res_validacion);

        if (!$usuario_valido) {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
            return; // Esto detiene la ejecución de la función
        }

        $query = "DELETE FROM tblSubEncabezadoBItacora WHERE IdSubEncabezadoBitacora = ?";
        $result = sqlsrv_query($conn, $query, array($folio));
        $result === false ? http_response_code(500) : http_response_code(200);
    }

    function parosAutomaticos()
    {
        // Código para manejar paros automáticos
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $idBitacora = $_GET['folio'];
        $query = "SELECT * FROM vwBitacoraParosMaquinas WHERE NoMaquina = ? AND IdEncabezadoBitacora = ? ORDER BY id DESC";
        $result = sqlsrv_query($conn, $query, array($_SESSION['idmaquina'], $idBitacora));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'idBitacora' => $row['IdEncabezadoBitacora'],
                'Turno' => $row['Turno'],
                'NoMaquina' => $row['NoMaquina'],
                'Cortes' => $row['Cortes'],
                'Rechazos' => $row['Rechazos'],
                'TiempoParo' => $row['TiempoParo'],
                'HoraParo' => $row['HoraParo']->format('H:i:s'),
                'Fecha' => $row['Fecha']->format('Y-m-d'),
                'Seccion' => $row['Seccion'],
                'Modulo' => $row['Modulos'],
                'Falla' => $row['DescripcionFalla'],
                'EstadoParo' => $row['EstadoParo']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function parosAutomaticosxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $id = intval($_GET['folio']); // Sanitiza el valor

        // Consulta principal
        $queryParos = "
        SELECT * 
        FROM vwBitacoraParosMaquinas 
        WHERE id = $id
    ";
        $resultParos = sqlsrv_query($conn, $queryParos);

        if ($resultParos === false) {
            die(json_encode(['error' => sqlsrv_errors()]));
        }

        $array = [];

        while ($row = sqlsrv_fetch_array($resultParos, SQLSRV_FETCH_ASSOC)) {
            // Datos base del paro
            $item = [
                'id' => $row['id'],
                'idBitacora' => $row['IdEncabezadoBitacora'],
                'Turno' => $row['Turno'],
                'NoMaquina' => $row['NoMaquina'],
                'Cortes' => $row['Cortes'],
                'Rechazos' => $row['Rechazos'],
                'TiempoParo' => $row['TiempoParo'],
                'HoraParo' => $row['HoraParo']->format('H:i:s'),
                'Fecha' => $row['Fecha']->format('Y-m-d'),
                'NoSeccion' => $row['NoSeccion'],
                'Seccion' => $row['Seccion'],
                'NoModulo' => $row['NoModulo'],
                'Modulo' => $row['Modulos'],
                'NoFalla' => $row['NoFalla'],
                'Falla' => $row['DescripcionFalla'],
                'Motivo' => $row['Motivo'],
                'Correccion' => $row['Correccion'],
            ];

            // Consulta 1: tblBitSanitizacionEmp (ligada por folio)
            $queryEmp = "
            SELECT id AS idEmp, folio, NoEmp 
            FROM [TLX002MXDB].[dbo].[tblBitSanitizacionEmp] 
            WHERE folio = $id
        ";
            $resultEmp = sqlsrv_query($conn, $queryEmp);
            if ($resultEmp !== false && $rowEmp = sqlsrv_fetch_array($resultEmp, SQLSRV_FETCH_ASSOC)) {
                $item['idEmp'] = $rowEmp['idEmp'];
                $item['folio'] = $rowEmp['folio'];
                $item['NoEmp'] = $rowEmp['NoEmp'];

                // Consulta 3: Datos del empleado en tlx032MXDB.dbo.tblEmpleados
                $queryEmpleado = "
                SELECT Nombre, ApellidoPaterno, ApellidoMaterno, Puesto 
                FROM [tlx032MXDB].[dbo].[tblEmpleados] 
                WHERE NoEmp = {$rowEmp['NoEmp']}
            ";
                $resultEmpleado = sqlsrv_query($conn, $queryEmpleado);
                if ($resultEmpleado !== false && $rowEmpleado = sqlsrv_fetch_array($resultEmpleado, SQLSRV_FETCH_ASSOC)) {
                    $item['EmpleadoNombre'] = $rowEmpleado['Nombre'];
                    $item['EmpleadoApellidoPaterno'] = $rowEmpleado['ApellidoPaterno'];
                    $item['EmpleadoApellidoMaterno'] = $rowEmpleado['ApellidoMaterno'];
                    $item['EmpleadoPuesto'] = $rowEmpleado['Puesto'];
                }
            }

            // Consulta 2: tblBitSanitizacionEnc (ligada por idParo)
            $queryEnc = "
            SELECT id AS idEnc, idParo, motivo, tiempo 
            FROM [TLX002MXDB].[dbo].[tblBitSanitizacionEnc] 
            WHERE idParo = $id
        ";
            $resultEnc = sqlsrv_query($conn, $queryEnc);
            if ($resultEnc !== false && $rowEnc = sqlsrv_fetch_array($resultEnc, SQLSRV_FETCH_ASSOC)) {
                $item['idEnc'] = $rowEnc['idEnc'];
                $item['idParo'] = $rowEnc['idParo'];
                $item['motivo'] = $rowEnc['motivo'];
                $item['tiempo'] = $rowEnc['tiempo'];
            }

            $array[] = $item;
        }

        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function tablEmpleadosSanitizacion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['folio'];
        $query = " SELECT id AS idEmp, folio, tblBSE.NoEmp, Nombre
            FROM [TLX002MXDB].[dbo].[tblBitSanitizacionEmp] tblBSE
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblBSE.NoEmp
            WHERE folio = $id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'idEmp' => $row['idEmp'],
                'folio' => $row['folio'],
                'NoEmp' => $row['NoEmp'],
                'Nombre' => $row['Nombre']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
}

if (isset($_GET["tblParos"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->tblParos();
} else if (isset($_GET["tblParosxid"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->tblParosxid();
} else if (isset($_GET["updateDataParo"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->updateDataParo();
} else if (isset($_GET["crearNuevoParo"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->crearNuevoParo();
} else if (isset($_GET["eliminarParo"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->eliminarParo();
} else if (isset($_GET["tblParosAutomaticos"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->parosAutomaticos();
} else if (isset($_GET["parosAutomaticosxid"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->parosAutomaticosxid();
} else if (isset($_GET["tablEmpleadosSanitizacion"])) {
    $CtrolTiempos = new CtrolTiempos();
    $CtrolTiempos->tablEmpleadosSanitizacion();
}

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
        $result = sqlsrv_query($conn, $query, array($_SESSION['idmaquina'],$folio));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['IdSubEncabezadoBitacora'], 'folio' => $row['IdEncabezadoBItacora'], 'seccion' => $row['NoSeccion'], 'modulo' => $row['NoModulo'],
                'falla' => $row['IdFalla'], 'comentarios' => $row['Comentarios'], 'cortes' => $row['Cortes'], 'rechazos' => $row['Rechazos'], 'tarriba' => $row['TArriba'],
                'tabajo' => $row['TAbajo'], 'hora' => $row['Hora']->format('Y-m-d H:i:s'), 'paroxequipo' => $row['ParoPorEquipo'], 'cortescorrida' => $row['CortesCorrida'],
                'rechazoscorrida' => $row['RechazosCorrida'], 'tiempocorrida' => $row['TCorrida'], 'tiempoparo' => $row['TParo'], 'arranqueCorruendo' => $row['ArranqueCorriendo'],
                'paroxcalidad' => $row['PorCalidad'], 'paroxtecnologia' => $row['NoTecnologia'], 'velpromedio' => $row['VelProm'], 'velocidad' => $row['VelStp'],
                'corridaseco' => $row['CorridaSeco'], 'nombreseccion' => $row['Seccion'], 'nombremodulo' => $row['Modulos'], 'nombrefalla' => $row['DescripcionFalla'],
                'nomaquina' => $row['NoMaquina'], 'estadoparo' => $row['EstadoParo']
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
                'id' => $row['IdSubEncabezadoBitacora'], 'folio' => $row['IdEncabezadoBItacora'], 'seccion' => $row['NoSeccion'], 'modulo' => $row['NoModulo'],
                'falla' => $row['IdFalla'], 'comentarios' => $row['Comentarios'], 'cortes' => $row['Cortes'], 'rechazos' => $row['Rechazos'], 'tarriba' => $row['TArriba'],
                'tabajo' => $row['TAbajo'], 'hora' => $row['Hora']->format('Y-m-d H:i:s'), 'paroxequipo' => $row['ParoPorEquipo'], 'cortescorrida' => $row['CortesCorrida'],
                'rechazoscorrida' => $row['RechazosCorrida'], 'tiempocorrida' => $row['TCorrida'], 'tiempoparo' => $row['TParo'], 'arranqueCorruendo' => $row['ArranqueCorriendo'],
                'paroxcalidad' => $row['PorCalidad'], 'paroxtecnologia' => $row['NoTecnologia'], 'velpromedio' => $row['VelProm'], 'velocidad' => $row['VelStp'],
                'corridaseco' => $row['CorridaSeco'], 'motivo' => $row['Motivo'], 'correccion' => $row['Correccion']
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

        $query1 = "UPDATE tblSubEncabezadoBItacora 
                SET NoSeccion = ?, NoModulo = ?, Motivo = ?, Correccion = ?, EstadoParo = 1 
                WHERE IdSubEncabezadoBitacora = ?";
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
}

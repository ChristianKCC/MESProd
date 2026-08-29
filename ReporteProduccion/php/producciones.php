<?php
require_once "../../Session/seguridad.php";
require_once "../../conexion.php";
class Producciones
{
    function saveProduccionesEnc()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $fecha = $_POST['fecha'];
        $departamento = $_POST['departamento'];
        $maquina = $_POST['maquina'];
        $clave = $_POST['clave'];
        $noemp = $_POST['noemp'];
        $turno = $_POST['turno'];
        $horastrabajadas = $_POST['horastrabajadas'];
        $cajastotales = $_POST['cajastotales'];
        $cajasreales = $_POST['cajasreales'];
        $query = "INSERT INTO tblProduccionesEnc(fecha,departamento,maquina,clave,noemp,turno,hrs,golpestotales,cajasreales,foliobitacora) VALUES (?,?,?,?,?,?,?,?,?,
        (SELECT COALESCE(
                (SELECT TOP 1 IdEncabezadoBitacora 
                FROM TLX004MXDB.dbo.tblEncabezadoBitacora 
                WHERE Fecha = '$fecha' AND Turno = $turno AND NoMaquina = $maquina), 
                0) AS IdEncabezadoBitacora))";
        $result = sqlsrv_query($conn, $query, array($fecha, $departamento, $maquina, $clave, $noemp, $turno, $horastrabajadas, $cajastotales, $cajasreales));
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Registro exitoso');
        }
    }
    function updateProduccionesEnc()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_POST['id'];
        $fecha = $_POST['fecha'];
        $departamento = $_POST['departamento'];
        $maquina = $_POST['maquina'];
        $clave = $_POST['clave'];
        $noemp = $_POST['noemp'];
        $turno = $_POST['turno'];
        $horastrabajadas = $_POST['horastrabajadas'];
        $golpestotales = $_POST['cajastotales'];
        $cajasreales = $_POST['cajasreales'];
        $query = "UPDATE tblProduccionesEnc SET fecha = ?,departamento =?,maquina =?,clave =?,noemp = ?,turno = ?,
        hrs = ?,golpestotales = ?,cajasreales = ?,foliobitacora = (SELECT COALESCE(
                (SELECT TOP 1 IdEncabezadoBitacora 
                FROM TLX004MXDB.dbo.tblEncabezadoBitacora 
                WHERE Fecha = '$fecha' AND Turno = $turno AND NoMaquina = $maquina), 
                0) AS IdEncabezadoBitacora) WHERE idProduccion = $id";
        $result = sqlsrv_query($conn, $query, array($fecha, $departamento, $maquina, $clave, $noemp, $turno, $horastrabajadas, $golpestotales, $cajasreales));
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Se actualizo la información');
        }
    }

    function tblProduccionesEnc()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $maquina = $_POST['maquina'];
        $idprod = $_POST['idproduccion'];
        $array = array();
        $addwhere = '';
        if ($_POST["fechai"] == 0) {
            $fechai = 'GETDATE()';
            $fechaf = 'GETDATE()';
        } else {
            $fechai = "'" . $_POST["fechai"] . "'";
            $fechaf = "'" . $_POST["fechaf"] . "'";
        }
        $maquina != '' && $addwhere .= " AND tblProduccionesEnc.maquina = $maquina";
        $idprod != '' && $addwhere .= " AND tblProduccionesEnc.idProduccion = $idprod";
        $query = "SELECT idProduccion,fecha,tblDepartamentos.NombreDepto as departamento,tblMaquinas.NombreMaquina as maquina,tblValeEClaves.Descripcion_Articulo as clave,
            tblEmpleados.Nombre as conductor, turno,hrs,golpestotales,cajasreales,tblValeEClaves.factor,tblValeEClaves.panalxcaja,tblValeEClaves.NoClave,
             tblProduccionesClaveTipo.DescripcionTipo,tblProduccionesClaveClase.DescripcionClase,std,merma FROM tblProduccionesEnc
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblProduccionesEnc.departamento
            INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionesEnc.maquina
            INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblProduccionesEnc.clave
            LEFT JOIN tblProduccionesClaveTipo ON tblProduccionesClaveTipo.idTipo = tblValeEClaves.tipo
            LEFT JOIN tblProduccionesClaveClase ON tblProduccionesClaveClase.idClase = tblValeEClaves.clase
            INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblProduccionesEnc.noemp WHERE fecha BETWEEN $fechai AND $fechaf $addwhere ORDER BY idProduccion Desc";
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'idProduccion' => $row['idProduccion'],
                'fecha' => $row['fecha']->format('Y-m-d'),
                'departamento' => $row['departamento'],
                'maquina' => $row['maquina'],
                'clave' => $row['NoClave'],
                'clavenombre' => $row['clave'],
                'conductor' => $row['conductor'],
                'turno' => $row['turno'],
                'hrs' => $row['hrs'],
                'golpestotales' => $row['golpestotales'],
                'cajasreales' => $row['cajasreales'],
                'clase' => $row['DescripcionClase'],
                'tipo' => $row['DescripcionTipo'],
                'panalxcaja' => $row['panalxcaja'],
                'std' => number_format($row['std'], 2),
                'merma' => number_format($row['merma'], 2),
                'factor' => $row['factor']
            ]);
        }
        echo json_encode($array);
    }
    function tblProduccionesEncxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $array = array();
        $id = $_GET['id'];
        $query = "SELECT idProduccion,fecha,tblDepartamentos.NoDepto as departamento,tblMaquinas.NoMaquina as maquina,tblValeEClaves.Descripcion_Articulo as clave,
            tblEmpleados.Nombre as conductor, turno,hrs,golpestotales,cajasreales,tblValeEClaves.factor,tblValeEClaves.panalxcaja,tblValeEClaves.NoClave,
             tblProduccionesClaveTipo.DescripcionTipo,tblProduccionesClaveClase.DescripcionClase,std,merma,tblEmpleados.NoEmp FROM tblProduccionesEnc
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblProduccionesEnc.departamento
            INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionesEnc.maquina
            INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblProduccionesEnc.clave
            LEFT JOIN tblProduccionesClaveTipo ON tblProduccionesClaveTipo.idTipo = tblValeEClaves.tipo
            LEFT JOIN tblProduccionesClaveClase ON tblProduccionesClaveClase.idClase = tblValeEClaves.clase
            INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblProduccionesEnc.noemp WHERE idProduccion=$id";
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'idProduccion' => $row['idProduccion'],
                'fecha' => $row['fecha']->format('Y-m-d'),
                'departamento' => $row['departamento'],
                'maquina' => $row['maquina'],
                'clave' => $row['NoClave'],
                'clavenombre' => $row['clave'],
                'noemp' => $row['NoEmp'],
                'conductor' => $row['conductor'],
                'turno' => $row['turno'],
                'hrs' => $row['hrs'],
                'golpestotales' => $row['golpestotales'],
                'cajasreales' => $row['cajasreales'],
                'clase' => $row['DescripcionClase'],
                'tipo' => $row['DescripcionTipo'],
                'panalxcaja' => $row['panalxcaja'],
                'std' => number_format($row['std'], 2),
                'merma' => number_format($row['merma'], 2),
                'factor' => $row['factor']
            ]);
        }
        echo json_encode($array);
    }
    function tblctrltiempos()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_GET["folio"];
        $query = "SELECT *,tblBitSecciones.NombreSeccion,tblBitModulos.NombreModulo FROM tblBitCtrltiempos 
		INNER JOIN tblBitSecciones on tblBitSecciones.NoSeccion= tblBitCtrltiempos.seccion
		INNER JOIN tblBitModulos ON tblBitModulos.NoModulo=tblBitCtrltiempos.modulo WHERE folio=(SELECT foliobitacora  FROM tblProduccionesEnc WHERE idProduccion=$folio)";
        $array = array();
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "folio" => $row[1],
                "horainicio" => $row[2]->format("H:i:s"),
                "horafinal" => $row[3]->format("H:i:s"),
                "operacion" => $row[4],
                "electrico" => $row[5],
                "mecanico" => $row[6],
                "materias" => $row[7],
                "grado" => $row[8],
                "prev" => $row[9],
                "servicios" => $row[10],
                "subtotal" => $row[11],
                "seccion" => $row["NombreSeccion"],
                "modulo" => $row["NombreModulo"],
                "motivo" => $row[14],
                "correccion" => $row[15],
                "id" => $row[0]
            ]);
        }
        echo json_encode($array);
    }
    function tblpresentacionesbit()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $folio = $_GET["folio"];
        $query = "SELECT  tblBitPresentacionSub.idpresentacionenc,tblBitPresentacionEnc.presentacion,tblBitTurnohoras.horastr,
		tblBitPresentacionSub.real,tblBitPresentacionSub.acumulado,tblBitPresentacionSub.std,tblBitPresentacionSub.golpes,
		tblBitPresentacionSub.merma,tblEncabezadoBitacora.Turno FROM tblBitPresentacionSub 
		INNER JOIN tblBitPresentacionEnc ON tblBitPresentacionSub.idpresentacionenc = tblBitPresentacionEnc.idpresentacionenc
		INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave= tblBitPresentacionEnc.presentacion
		INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id = tblBitPresentacionSub.hora
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitPresentacionEnc.folio
		WHERE tblBitPresentacionEnc.folio = (SELECT foliobitacora  FROM tblProduccionesEnc WHERE idProduccion=$folio)";
        $array = array();
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "presentacion" => $row[1],
                "hora" => $row[2],
                "real" => $row[3],
                "acumulado" => $row[4],
                "std" => $row[5],
                "golpes" => $row['golpes'],
                "merma" => $row['merma'],
                "turno" => $row["Turno"]
            ]);
        }
        echo json_encode($array);
    }
    function saveEntregados()
    {

        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $fecha = $_POST['fecha'];
        $maquinas = $_POST['maquinas'];
        $clave = $_POST['clave'];
        $Entregado = $_POST['Entregado'];
        $query = "INSERT INTO tblProduccionesEntregas(folio,fecha,maquina,clave,entregados) VALUES (?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, array($folio, $fecha, $maquinas, $clave, $Entregado));
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Registro exitoso');
        }
    }
    function tblEntregados()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT TOP 100 tblProduccionesEntregas.*,tblMaquinas.NombreMaquina as maquinanombre,tblValeEClaves.Descripcion_Articulo as clavenombre,
        tblProduccionesClaveTipo.DescripcionTipo,tblProduccionesClaveClase.DescripcionClase,tblValeEClaves.factor,(tblValeEClaves.factor*entregados) as Entstd
		  FROM tblProduccionesEntregas
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionesEntregas.maquina
        INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblProduccionesEntregas.clave
        INNER JOIN tblProduccionesClaveTipo ON tblProduccionesClaveTipo.idTipo = tblValeEClaves.tipo
        INNER JOIN tblProduccionesClaveClase ON tblProduccionesClaveClase.idClase = tblValeEClaves.clase";
        $array = array();
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['idEntregas'],
                "folio" => $row['folio'],
                "fecha" => $row['fecha']->format('Y-m-d'),
                "maquina" => $row['maquina'],
                "maquinanombre" => $row['maquinanombre'],
                "clave" => $row['clave'],
                "clavenombre" => $row['clavenombre'],
                "tipo" => $row['DescripcionTipo'],
                "clase" => $row['DescripcionClase'],
                "factor" => $row['factor'],
                "Entstd" => number_format($row['Entstd'], 2),
                "entregados" => $row['entregados']
            ]);
        }
        echo json_encode($array);
    }
    function deleteEntregados()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_POST['id'];
        $query = "DELETE FROM tblProduccionesEntregas WHERE idEntregas = $id";
        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Registro Eliminado');
        }
    }
    function deleteEncProduccion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_POST['id'];
        $query = "DELETE FROM tblProduccionesEnc WHERE idproduccion = $id";
        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            http_response_code(500);
            $errors = sqlsrv_errors();
            foreach ($errors as $error) {
                echo json_encode("message: " . $error['message']);
            }
        } else {
            http_response_code(200);
            echo json_encode('Registro Eliminado');
        }
    }
    function datagraficasdiario()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $maq = $_GET['maq'];

        $sql = "WITH CTE AS ( SELECT tblBitPresentacionEnc.presentacion, tblEncabezadoBitacora.turno, tblBitTurnohoras.horastr, tblBitPresentacionSub.real, 
    tblBitPresentacionGolpes.golpes, tblBitPresentacionSub.acumulado, tblBitPresentacionSub.std, tblBitPresentacionGolpes.merma, tblValeEClaves.panalxcaja,
    ROW_NUMBER() OVER (PARTITION BY tblBitPresentacionEnc.presentacion, tblEncabezadoBitacora.turno ORDER BY tblBitTurnohoras.id DESC) AS rn FROM tblBitPresentacionEnc 
    INNER JOIN tblBitPresentacionSub ON tblBitPresentacionSub.idpresentacionenc = tblBitPresentacionEnc.idpresentacionenc 
    INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitPresentacionEnc.folio 
    INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id = tblBitPresentacionSub.hora 
	INNER JOIN tblBitPresentacionGolpes ON tblBitPresentacionGolpes.idbitacora = tblBitPresentacionEnc.folio 
	INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblBitPresentacionEnc.presentacion
    WHERE tblBitPresentacionGolpes.hora = tblBitPresentacionSub.hora AND tblBitPresentacionSub.real <> 0 AND tblEncabezadoBitacora.fecha =  CONVERT(VARCHAR,DATEADD(day,-1, GETDATE()), 23) AND tblEncabezadoBitacora.NoMaquina = 76)
    SELECT presentacion, turno, horastr, real, golpes, acumulado, std, merma,panalxcaja FROM CTE WHERE rn = 1 ORDER BY turno, presentacion, std DESC;";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $GolpesA = 0;
        $gps = 0;
        $turnoAnterior = null;
        $primerTurno = true;
        $labels = [];
        $data = [];
        while ($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (!$primerTurno && $turnoAnterior !== $fila['turno']) {
                $GolpesA == 0 ? $merma = 0 : $merma = (($GolpesA - $gps) / $GolpesA) * 100;
                $labels[] = $fila['turno'];
                $data[] = number_format($merma, 2);
                $gps = 0;
                $GolpesA = 0;
            }
            $GolpesA = max($GolpesA, $fila['golpes']);
            $gp = $fila['acumulado'] * $fila['panalxcaja'];
            $gps += $gp;
            $turnoAnterior = $fila['turno'];
            $primerTurno = false;
        }
        if (!$primerTurno) {
            $GolpesA == 0 ? $merma = 0 : $merma = (($GolpesA - $gps) / $GolpesA) * 100;
            $labels[] = 1;
            $data[] = number_format($merma, 2);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        $backgroundColor = [
            "rgba(255, 99, 132, 0.2)",
            "rgba(54, 162, 235, 0.2)",
            "rgba(255, 206, 86, 0.2)",
            "rgba(75, 192, 192, 0.2)",
            "rgba(153, 102, 255, 0.2)",
            "rgba(255, 159, 64, 0.2)"
        ];
        $borderColor = [
            "rgba(255, 99, 132, 1)",
            "rgba(54, 162, 235, 1)",
            "rgba(255, 206, 86, 1)",
            "rgba(75, 192, 192, 1)",
            "rgba(153, 102, 255, 1)",
            "rgba(255, 159, 64, 1)"
        ];
        $response = [
            "labels" => $labels,
            "datasets" => [
                [
                    "label" => "Categorías",
                    "backgroundColor" => $backgroundColor,
                    "borderColor" => $borderColor,
                    "borderWidth" => 1,
                    "data" => $data
                ]
            ]
        ];
        echo json_encode($response);
    }
    function gethrs($folio)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $query = "SELECT sum(hrs) as hrs FROM tblProduccionesEnc WHERE foliobitacora = $folio";
        $stmt = sqlsrv_query($conn, $query);
        $hrs = 1;
        while ($fila = sqlsrv_fetch_array($stmt))
            $hrs = $fila['hrs'];
        return $hrs;
    }
    function datagraficasdiario2()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $maq = $_GET['maq'];
        $sql = "	WITH CTE AS (
            SELECT 
                tblProduccionesEnc.foliobitacora,
                tblProduccionesEnc.fecha,
                tblProduccionesEnc.turno,
                tblMaquinas.NombreMaquina,
                tblProduccionesEnc.clave,
                tblProduccionesEnc.merma,
                tblProduccionesEnc.golpestotales,
                tblProduccionesEnc.cajasreales,
                tblProduccionesEnc.std,
                SUM(tblBitCtrltiempos.operacion) as operacion,
                SUM(tblBitCtrltiempos.electrico) as electrico,
                SUM(tblBitCtrltiempos.mecanico)  as mecanico,
                SUM(tblBitCtrltiempos.materias)  as materias,
                SUM(tblBitCtrltiempos.grado)  as grado,
                SUM(tblBitCtrltiempos.prev)  as prev,
                SUM(tblBitCtrltiempos.servicios)  as servicios,
                SUM(tblBitCtrltiempos.operacion + tblBitCtrltiempos.electrico + tblBitCtrltiempos.mecanico + tblBitCtrltiempos.materias + 
                    tblBitCtrltiempos.grado + tblBitCtrltiempos.prev + tblBitCtrltiempos.servicios) AS SumaTotal,
                ROW_NUMBER() OVER (PARTITION BY tblProduccionesEnc.foliobitacora ORDER BY tblProduccionesEnc.Fecha DESC) AS rn
            FROM tblBitCtrltiempos 
            INNER JOIN tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblBitCtrltiempos.seccion
            INNER JOIN tblProduccionModulos ON tblProduccionModulos.idModulos = tblBitCtrltiempos.modulo
            INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCtrltiempos.folio
            INNER JOIN tblProduccionesEnc ON tblProduccionesEnc.foliobitacora = tblBitCtrltiempos.folio
            INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblProduccionesEnc.maquina
            WHERE tblEncabezadoBitacora.fecha BETWEEN CONVERT(VARCHAR,DATEADD(day,-1, GETDATE()), 23) AND CONVERT(VARCHAR,DATEADD(day,-1, GETDATE()), 23) AND tblProduccionesEnc.maquina = 76
            GROUP BY tblProduccionesEnc.foliobitacora, tblProduccionesEnc.Fecha, tblProduccionesEnc.clave, tblProduccionesEnc.cajasreales, tblProduccionesEnc.std,
                tblProduccionesEnc.golpestotales, tblProduccionesEnc.merma, tblMaquinas.NombreMaquina, tblProduccionesEnc.turno
        )
        SELECT *
        FROM CTE
        WHERE rn = 1
        ORDER BY NombreMaquina DESC, Fecha DESC;";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $calculo = 0;
        $labels = [];
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $labels[] = $row['turno'];
            $calculo = $row['SumaTotal'] / 60;
            $hrsobj = new Producciones();
            $hrs = $hrsobj->gethrs($row['foliobitacora']);
            $calculo = $calculo / $hrs;
            $calculo = $calculo * 100;
            $data[] = number_format($calculo, 2);
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        $backgroundColor = [
            "rgba(255, 99, 132, 0.2)",
            "rgba(54, 162, 235, 0.2)",
            "rgba(255, 206, 86, 0.2)",
            "rgba(75, 192, 192, 0.2)",
            "rgba(153, 102, 255, 0.2)",
            "rgba(255, 159, 64, 0.2)"
        ];
        $borderColor = [
            "rgba(255, 99, 132, 1)",
            "rgba(54, 162, 235, 1)",
            "rgba(255, 206, 86, 1)",
            "rgba(75, 192, 192, 1)",
            "rgba(153, 102, 255, 1)",
            "rgba(255, 159, 64, 1)"
        ];
        $response = [
            "labels" => $labels,
            "datasets" => [
                [
                    "label" => "Categorías",
                    "backgroundColor" => $backgroundColor,
                    "borderColor" => $borderColor,
                    "borderWidth" => 1,
                    "data" => $data
                ]
            ]
        ];
        echo json_encode($response);
    }

    function RegistrarPlanProduccion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

        header('Content-Type: application/json; charset=utf-8');

        if ($conn === false) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => 'No se pudo conectar a la base de datos.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Datos de entrada (JSON)
        $data = json_decode(file_get_contents('php://input'), true);

        $clave = $data['clave'] ?? '';
        $fechaMes = $data['fecha'] ?? ''; // viene como "YYYY-MM" desde <input type="month">
        $maquina = $data['maquina'] ?? '';
        $STD = $data['STD'] ?? '';
        $sessionsave = $_SESSION['ibm'] ?? '';
        $produccion = $data['produccion'] ?? '';
        $configuracion = $data['configuracion'] ?? '';

        // Validar formato YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $fechaMes)) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'message' => 'Formato de mes inválido. Se esperaba YYYY-MM.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Construir primer día del mes: YYYY-MM-01
        $fechaInicioMesStr = $fechaMes . '-01';

        // Crear objeto DateTime (más seguro para sqlsrv)
        $fechaInicioMes = DateTime::createFromFormat('Y-m-d', $fechaInicioMesStr);
        if ($fechaInicioMes === false) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'message' => 'No se pudo interpretar la fecha como primer día del mes.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Opcional: normalizar hora a 00:00:00
        $fechaInicioMes->setTime(0, 0, 0);

        // Inserción parametrizada
        $sql = "
        INSERT INTO TLX002MXDB.dbo.tblProduccionPlan
            (clave, fecha, maquina, STD, sessionsave, produccion, configuracion)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";

        // Para sqlsrv, puedes pasar DateTime directamente o usar string 'Y-m-d'
        // Si tu columna es DATETIME, el objeto DateTime funciona bien.
        $params = [
            $clave,
            $fechaInicioMes,       // objeto DateTime
            $maquina,
            $STD,
            $sessionsave,
            $produccion,
            $configuracion
        ];

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
            error_log('Insert failed: ' . print_r($errors, true));
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'message' => 'Error al registrar en la base de datos.',
                'sqlsrv_errors' => $errors  // Quita esto en producción si no quieres exponer detalles
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'ok' => true,
            'message' => 'Registro realizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    function actualizarPlanProduccion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $data = json_decode(file_get_contents('php://input'), true);

        $folio = $data['folio'] ?? '';
        $clave = $data['clave'] ?? '';
        $fecha = $data['fecha'] ?? '';
        $maquina = $data['maquina'] ?? '';
        $STD = $data['STD'] ?? '';
        $sessionsave = $_SESSION['ibm'] ?? '';
        $configuracion = $data['configuracion'] ?? '';


        // Validar formato YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $fecha)) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'message' => 'Formato de mes inválido. Se esperaba YYYY-MM.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Construir primer día del mes: YYYY-MM-01
        $fechaInicioMesStr = $fecha . '-01';

        // Crear objeto DateTime (más seguro para sqlsrv)
        $fechaInicioMes = DateTime::createFromFormat('Y-m-d', $fechaInicioMesStr);
        if ($fechaInicioMes === false) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'message' => 'No se pudo interpretar la fecha como primer día del mes.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Opcional: normalizar hora a 00:00:00
        $fechaInicioMes->setTime(0, 0, 0);

        $array = array(
            $clave,
            $fechaInicioMes,
            $maquina,
            $STD,
            $sessionsave,
            $configuracion,
            $folio
        );

        $query = "UPDATE tblProduccionPlan SET clave=?, fecha=?, maquina=?, STD=?, sessionsave=?, configuracion=?
                    WHERE id=?
                    ";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            echo json_encode('Error al actualizar registro en la base de datos');
        } else {
            http_response_code(200);
            echo json_encode('Registro actualizado exitosamente');
        }

    }

    function ObtenerdatosPlanProduccion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        // NoEmp del usuario logueado (IBM)
        $noEmp = $_SESSION['ibm'];
        $sql = "SELECT
            tblPP.id,
            tblPP.fecha,
            tblPP.maquina,
            tblm.NombreMaquina,
            tblPP.configuracion,
            tblMC.NoDepto,
            tblPP.clave,
            tblDEP.NombreDepto,
            tblcla.Descripcion_Articulo AS descripcion,
            tblPP.STD,
            tblPP.sessionsave
            FROM tblProduccionPlan tblPP
            INNER JOIN TLX009MXDB.dbo.tblMaquinas tblm ON tblm.NoMaquina = tblPP.maquina
            LEFT JOIN TLX002MXDB.dbo.tblValeEClaves tblcla ON tblcla.NoClave = tblPP.clave
            LEFT JOIN TLX002MXDB.dbo.tblProduccionProductos tblppr ON tblppr.idProducto = tblcla.Producto
            LEFT JOIN TLX002MXDB.dbo.tblProduccionEtapas tblpet ON tblpet.idEtapa = tblcla.Etapa
            INNER JOIN TLX009MXDB.dbo.tblMaquinasCombo tblMC ON tblMC.NoMaquina = tblPP.maquina
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos tblDEP ON tblDEP.NoDepto = tblMC.NoDepto
            INNER JOIN TLX032MXDB.dbo.tblEmpleados tblEMP ON tblEMP.NoEmp = ?
            WHERE YEAR(tblPP.fecha) = YEAR(GETDATE())
            AND tblMC.NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 87, 89, 73, 76, 101, 137, 139)
              AND (
                  ? IN (34374, 58998)  -- Empleados especiales ven todas
                  OR tblEMP.NombreDepartamento = tblMC.NoDepto  -- Otros ven solo su depto
                )
            ORDER BY tblPP.id DESC";
        // Pasamos $noEmp dos veces: para empUser.NoEmp = ? y para la condición IN (34374, 58998)
        $params = [$noEmp, $noEmp];

        $array = [];
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
            return;
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = $row;
        }

        http_response_code(200);
        echo json_encode($array);
    }

    // Tabla de filtrado de plan de produccion 
    function tblDatosProduccion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

        $idMaquina = $_POST['idMaquina'];
        $clave = $_POST['clave'];

        $condiciones = [];

        // Verificar que el $idMaquina no sea vacio
        if (!empty($idMaquina)) {
            $idMaquina = "'%" . $idMaquina . "%'";
            $condiciones[] = "tblm.NoMaquina LIKE $idMaquina";
        }

        // Verificar que el valor de $clave no sea vacio
        if (!empty($clave)) {
            $clave = "'%" . $clave . "%'";
            $condiciones[] = "tblcla.NoClave LIKE $clave";
        }

        $filtro = '';

        // Verifica los elementos dentro del arreglo
        if (count($condiciones) === 1) {
            $filtro = ' WHERE ' . $condiciones[0];
        } elseif (count($condiciones) === 2) {
            $filtro = ' WHERE ' . implode(' AND ', $condiciones);
        }

        $query = "SELECT TOP 100 tblProduccionPlan.*, tblcla.Descripcion_Articulo AS descripcion,
                tblcla.Producto, tblppr.Producto AS nombreProducto, tblcla.Etapa, tblpet.Etapa AS nombreEtapa,
                tblm.NombreMaquina 
              FROM tblProduccionPlan
              INNER JOIN TLX009MXDB.dbo.tblMaquinas tblm ON tblm.NoMaquina = tblProduccionPlan.maquina
              LEFT JOIN TLX002MXDB.dbo.tblValeEClaves tblcla ON tblcla.NoClave = tblProduccionPlan.clave
              LEFT JOIN TLX002MXDB.dbo.tblProduccionProductos tblppr ON tblppr.idProducto = tblcla.Producto
              LEFT JOIN TLX002MXDB.dbo.tblProduccionEtapas tblpet ON tblpet.idEtapa = tblcla.Etapa
              $filtro
              ORDER BY tblProduccionPlan.id DESC";

        $array = [];
        $result = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($result))
            $array[] = $row;

        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
        } else {
            http_response_code(200);
            echo json_encode($array);
        }
    }

    function Buscarclave()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $key = $_POST['clave'];
        $sql = "SELECT tblValeEClaves.*, tblpet.Etapa as nombreEtapa, tblppr.Producto AS nombreProducto
                FROM tblValeEClaves
                LEFT JOIN TLX002MXDB.dbo.tblProduccionProductos tblppr ON tblppr.idProducto = tblValeEClaves.Producto
                LEFT JOIN TLX002MXDB.dbo.tblProduccionEtapas tblpet ON tblpet.idEtapa = tblValeEClaves.Etapa
                WHERE NoClave=?";
        $array = array();
        $result = sqlsrv_query($conn, $sql, array($key));
        while ($row = sqlsrv_fetch_array($result))
            $array[] = $row;
        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
        } else {
            http_response_code(200);
            echo json_encode($array);
        }
    }
    function getdataxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $sql = "SELECT tblProduccionPlan.*,tblValeEClaves.Descripcion_Articulo AS descripcion, tblValeEClaves.Etapa,
                tblpet.Etapa AS nombreEtapa , tblValeEClaves.Producto, tblppr.Producto AS nombreProducto
                FROM tblProduccionPlan
                INNER JOIN TLX002MXDB.dbo.tblValeEClaves ON tblValeEClaves.NoClave = tblProduccionPlan.clave
                LEFT JOIN TLX002MXDB.dbo.tblProduccionProductos tblppr ON tblppr.idProducto = tblValeEClaves.Producto
                LEFT JOIN TLX002MXDB.dbo.tblProduccionEtapas tblpet ON tblpet.idEtapa = tblValeEClaves.Etapa
                WHERE tblProduccionPlan.id=?";
        $array = array();
        $result = sqlsrv_query($conn, $sql, array($id));
        while ($row = sqlsrv_fetch_array($result))
            $array[] = $row;
        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
        } else {
            http_response_code(200);
            echo json_encode($array);
        }
    }

    function ObtenerdatosPlanProduccionMod()
    {
        $idMaquina = isset($_POST['idMaquina']) ? $_POST['idMaquina'] : $_SESSION['idmaquina'];
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $sql = "SELECT tblProduccionPlan.*,tblcla.Descripcion_Articulo AS descripcion,
                tblcla.Producto, tblppr.Producto AS nombreProducto, tblcla.Etapa, tblpet.Etapa AS nombreEtapa,
                tblm.NombreMaquina 
                FROM tblProduccionPlan
                INNER JOIN TLX009MXDB.dbo.tblMaquinas tblm ON tblm.NoMaquina = tblProduccionPlan.maquina
                LEFT JOIN TLX002MXDB.dbo.tblValeEClaves tblcla ON tblcla.NoClave = tblProduccionPlan.clave
                LEFT JOIN TLX002MXDB.dbo.tblProduccionProductos tblppr ON tblppr.idProducto = tblcla.Producto
                LEFT JOIN TLX002MXDB.dbo.tblProduccionEtapas tblpet ON tblpet.idEtapa = tblcla.Etapa
				WHERE tblProduccionPlan.maquina = $idMaquina                 
                AND MONTH(tblProduccionPlan.fechasave) = MONTH(GETDATE())
                AND YEAR(tblProduccionPlan.fechasave) = YEAR(GETDATE())
                ORDER BY tblProduccionPlan.id DESC";
        $array = array();
        $result = sqlsrv_query($conn, $sql);
        while ($row = sqlsrv_fetch_array($result))
            $array[] = $row;
        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
        } else {
            http_response_code(200);
            echo json_encode($array);
        }
    }

    function BorrarRegistroProduccion()
    {

        if (isset($_POST["id"])) {
            $idFolio = intval($_POST["id"]);
            $conexion = new ClassConexion();
            $conn = $conexion->conexion('TLX002MXDB');

            $query = "DELETE FROM tblProduccionPlan WHERE id = ?";
            $params = array($idFolio);

            $result = sqlsrv_query($conn, $query, $params);

            if ($result === false) {
                http_response_code(500);
            } else {
                http_response_code(200);
            }
        } else {
            http_response_code(400); // Bad Request si no se recibe el ID
        }

    }
}

class InfoMaquinas
{
    public function infoTurnosAnteriores()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $ibm = $_SESSION['ibm'];

        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        $fecha = $_POST['fecha'] ?? null;
        $maquina = $_POST['maquina'] ?? null;
        $turno = $_POST['turno'] ?? null;

        $array = [];
        $query = "SELECT TOP 50 tblBMA.id, tblBMA.NoMaquina, tblM.NombreMaquina AS NombreMaquina, 
               CortesA, RechazosA, TAbajoA, fMinEnhebrandoA, TArribaA, fMermaMaquinaA, 
               fTiempoPerdidoA, fParoMaqinaA, fTurnoA, fAñoA, MesA, DiaA,
               CASE 
                   WHEN fAñoA BETWEEN 1900 AND 2100
                     AND MesA BETWEEN 1 AND 12
                     AND DiaA BETWEEN 1 AND 31
                   THEN FORMAT(DATEFROMPARTS(fAñoA, MesA, DiaA), 'yyyy-MM-dd')
                   ELSE 'Fecha inválida'
               END AS Fecha
        FROM tblBitacoraMaquinasAnterior tblBMA
        LEFT JOIN TLX009MXDB.dbo.tblMaquinas tblM ON tblM.NoMaquina = tblBMA.NoMaquina
        WHERE fAñoA BETWEEN 1900 AND 2100
          AND MesA BETWEEN 1 AND 12
          AND DiaA BETWEEN 1 AND 31
          ";

        $params = [];

        if (!empty($fecha)) {
            $fechaParts = explode('-', $fecha);
            if (count($fechaParts) === 3) {
                $query .= " AND DATEFROMPARTS(fAñoA, MesA, DiaA) = DATEFROMPARTS(?, ?, ?)";
                $params[] = (int) $fechaParts[0]; // Año
                $params[] = (int) $fechaParts[1]; // Mes
                $params[] = (int) $fechaParts[2]; // Día
            }
        }

        if (!empty($maquina)) {
            $query .= " AND tblBMA.NoMaquina = ?";
            $params[] = $maquina;
        }

        if (!empty($turno)) {
            $query .= " AND fTurnoA = ?";
            $params[] = $turno;
        }

        $query .= " ORDER BY Fecha DESC, fTurnoA DESC";

        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }



        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "id" => $row['id'],
                "Maquina" => $row['NoMaquina'],
                "NombreMaquina" => $row['NombreMaquina'],
                "Cortes" => $row['CortesA'],
                "Rechazos" => $row['RechazosA'],
                "TiempoAbajo" => $row['TAbajoA'],
                "MinutosEnhebrando" => $row['fMinEnhebrandoA'],
                "TiempoArriba" => $row['TArribaA'],
                "MermaMaquina" => $row['fMermaMaquinaA'],
                "TiempoPerdido" => $row['fTiempoPerdidoA'],
                "ParosMaquina" => $row['fParoMaqinaA'],
                "Fecha" => $row['Fecha'],
                "Turno" => $row['fTurnoA'],
                "IBM" => $ibm
            ];
        }

        echo json_encode($array);
    }

    public function infoTurnoHook()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $ibm = $_SESSION['ibm'];

        if ($conn === false) {
            die(json_encode(["error" => "Error en la conexión a la base de datos"]));
        }

        $fecha = $_POST['fecha'] ?? null;
        $turno = $_POST['turno'] ?? null;

        $query = "SELECT [Id]
            ,[FechaTurno]
            ,tblRTH.[NoMaquina]
            ,tblMaquinas.NombreMaquina
            ,[IdEncabezadoBitacora]
            ,[Turno]
            ,[MilesMetrosHora]
            ,[Metros]
            ,[TiempoParoMin] AS TiempoAbajo
            ,[TiempoCorriendoMin] AS TiempoArriba
            ,[TiempoPerdido]
            ,[ParosMaquina]
        FROM [TLX004MXDB].[dbo].[tblMXPRResumenTurnoHook] tblRTH
        INNER JOIN  TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblRTH.NoMaquina
        WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($fecha)) {
            $query .= " AND FechaTurno = ?";
            $params[] = $fecha;
            $types .= "s";
        }

        if (!empty($turno)) {
            $query .= " AND Turno = ?";
            $params[] = $turno;
            $types .= "s";
        }

        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }

        $array = [];

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "id" => $row["Id"],
                "Fecha" => $row["FechaTurno"]->format('Y-m-d'),
                "NoMaquina" => $row["NoMaquina"],
                "NombreMaquina" => $row["NombreMaquina"],
                "Turno" => $row["Turno"],
                "MilesMetros" => $row["MilesMetrosHora"],
                "Metros" => $row["Metros"],
                "TiempoAbajo" => $row["TiempoAbajo"],
                "TiempoArriba" => $row["TiempoArriba"],
                "ParosMaquina" => $row["ParosMaquina"],
                "IBM" => $ibm
            ];
        }

        echo json_encode($array);

    }

    public function infoMaquinasSinRed()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $ibm = $_SESSION['ibm'];

        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        // Entradas
        $fecha = $_POST['fecha'] ?? null;    // Obligatoria
        $maquina = $_POST['maquina'] ?? null; // Obligatoria
        $turno = $_POST['turno'] ?? null;     // Opcional (si no viene, regresamos los 3 turnos)

        // Validaciones mínimas para evitar consultas inválidas
        if (empty($fecha) || empty($maquina)) {
            die(json_encode(["error" => "Parámetros insuficientes. Se requieren 'fecha' y 'maquina'."]));
        }

        $array = [];

        // Base de la consulta
        $query = " SELECT 
                    IdEncabezadoBItacora,
                    presentacion,
                    Descripcion_Articulo,
                    Fecha,
                    Turno,
                    HorasTrabajadas,
                    NoMaquina,
                    NombreMaquina,
                    golpes,
                    merma,
                    real AS CajasReales,
                    cajasxp AS CajasxPanal,
                    acumulado,
                    std,
                    Rechazos,
                    MinutosTurno,
                    TotalTiempoPerdido AS TiempoPerdido,
                    TiempoArriba,
                    ParosMaquina
                FROM (
                    SELECT *,
                        ROW_NUMBER() OVER (PARTITION BY Turno ORDER BY IdEncabezadoBItacora DESC) AS rn
                    FROM ProduccionesMaquinasSinRed
                    WHERE Fecha = ?
                    AND NoMaquina = ?
                ) AS ranked
                WHERE rn = 1
            ";

        // Parámetros base
        $params = [$fecha, $maquina];

        // Agregar filtro de turno solo si viene en el POST
        if (!empty($turno)) {
            $query .= " AND Turno = ?";
            $params[] = $turno;
        }

        // Ordenar por fecha (desc) y turno (desc) para ver primero lo más reciente
        $query .= " ORDER BY Fecha DESC, Turno DESC";

        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $fechaFormateada = isset($row['Fecha']) && $row['Fecha'] instanceof DateTime
                ? $row['Fecha']->format('Y-m-d')
                : (is_string($row['Fecha']) ? $row['Fecha'] : null);

            $array[] = [
                "IdEncabezadoBItacora" => $row['IdEncabezadoBItacora'],
                "presentacion" => $row['presentacion'],
                "Descripcion_Articulo" => $row['Descripcion_Articulo'],
                "Fecha" => $fechaFormateada,
                "Turno" => $row['Turno'],
                "HorasTrabajadas" => $row['HorasTrabajadas'],
                "NoMaquina" => $row['NoMaquina'],
                "NombreMaquina" => $row['NombreMaquina'],
                "golpes" => $row['golpes'],
                "merma" => $row['merma'],
                "CajasReales" => $row['CajasReales'],
                "CajasxPanal" => $row['CajasxPanal'],
                "acumulado" => $row['acumulado'],
                "std" => $row['std'],
                "Rechazos" => $row['Rechazos'],
                "MinutosTurno" => $row['MinutosTurno'],
                "TiempoPerdido" => $row['TiempoPerdido'],
                "TiempoArriba" => $row['TiempoArriba'],
                "ParosMaquina" => $row['ParosMaquina'],
                "IBM" => $ibm
            ];
        }

        echo json_encode($array);
    }

    function getDataRegistroTurno()
    {
        $id = $_GET['folio'];
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        // Consulta base
        $queryBase = "SELECT tblBMA.id, tblBMA.NoMaquina, tblM.NombreMaquina, CortesA, RechazosA, TAbajoA, 
                    fMinEnhebrandoA, TArribaA, fMermaMaquinaA, 
                    fTiempoPerdidoA, fParoMaqinaA, fTurnoA, fAñoA, MesA, DiaA
                  FROM tblBitacoraMaquinasAnterior tblBMA
                  INNER JOIN TLX009MXDB.dbo.tblMaquinas tblM ON tblM.NoMaquina = tblBMA.NoMaquina
                  WHERE tblBMA.id = $id";

        $resultBase = sqlsrv_query($conn, $queryBase);
        $rowBase = sqlsrv_fetch_array($resultBase, SQLSRV_FETCH_ASSOC);

        if (!$rowBase) {
            die(json_encode(["error" => "No se encontró el registro base."]));
        }

        // Extraer datos para segunda consulta
        $noMaquina = $rowBase['NoMaquina'];
        $turno = $rowBase['fTurnoA'];
        $anio = $rowBase['fAñoA'];
        $mes = str_pad($rowBase['MesA'], 2, '0', STR_PAD_LEFT);
        $dia = str_pad($rowBase['DiaA'], 2, '0', STR_PAD_LEFT);
        $fecha = "$anio-$mes-$dia";

        // Segunda consulta
        $queryEncabezado = "SELECT IdEncabezadoBItacora, HorasTrabajadas
                        FROM tblEncabezadoBitacora
                        WHERE NoMaquina = ? AND Turno = ? AND Fecha = ?";
        $params = [$noMaquina, $turno, $fecha];
        $resultEncabezado = sqlsrv_query($conn, $queryEncabezado, $params);
        $rowEncabezado = sqlsrv_fetch_array($resultEncabezado, SQLSRV_FETCH_ASSOC);

        // Combinar resultados en un solo arreglo
        $resultadoFinal = array_merge($rowBase, $rowEncabezado ?: ["IdEncabezadoBItacora" => null]);

        echo json_encode($resultadoFinal);
    }
    function actualizarRegistroTurnoMaquina()
    {
        $id = $_POST['folio'];
        $idEncabezado = $_POST['folioBitacora'];
        $cortes = $_POST['cortes'];
        $rechazos = $_POST['rechazos'];
        $tiempoAbajo = $_POST['tiempoAbajo'];
        $minutosEnhebrando = $_POST['minutosEnhebrando'];
        $tiempoArriba = $_POST['tiempoArriba'];
        $tiempoPerdido = $_POST['tiempoPerdido'];
        $paros = $_POST['paros'];
        $horasTrabajadas = $_POST['horasTrabajadas'];
        $motivoCambio = $_POST['motivoCambio'];
        $usr = $_SESSION['ibm'];

        // Validar que se recibió el folio y folioBitacora
        if (!isset($id) || !isset($idEncabezado)) {
            echo json_encode(["error" => "Folio(s) no proporcionado(s)"]);
            return;
        }

        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if ($conn === false) {
            echo json_encode(["error" => "Error de conexión a la base de datos"]);
            return;
        }

        // Actualizar tblBitacoraMaquinasAnterior
        $queryUpdateBMA = "UPDATE tblBitacoraMaquinasAnterior
                       SET CortesA = ?, RechazosA = ?, TAbajoA = ?, 
                           fMinEnhebrandoA = ?, TArribaA = ?, 
                           fTiempoPerdidoA = ?, fParoMaqinaA = ?
                       WHERE id = ?";
        $paramsUpdateBMA = [
            $cortes,
            $rechazos,
            $tiempoAbajo,
            $minutosEnhebrando,
            $tiempoArriba,
            $tiempoPerdido,
            $paros,
            $id
        ];
        $resultBMA = sqlsrv_query($conn, $queryUpdateBMA, $paramsUpdateBMA);

        // Actualizar tblEncabezadoBitacora
        $queryUpdateEnc = "UPDATE tblEncabezadoBitacora
                       SET HorasTrabajadas = ?
                       WHERE IdEncabezadoBItacora = ?";
        $paramsUpdateEnc = [
            $horasTrabajadas,
            $idEncabezado
        ];
        $resultEnc = sqlsrv_query($conn, $queryUpdateEnc, $paramsUpdateEnc);

        $queryMotivo = "INSERT INTO tblBitacoraMotivoCambios (folioBitacoraAnterior, folioEncBitacora, noemp, motivo) 
                    VALUES (?, ?, ?, ?)";
        $paramsUpdateBMC = [
            $id,
            $idEncabezado,
            $usr,
            $motivoCambio
        ];
        $resultMotivo = sqlsrv_query($conn, $queryMotivo, $paramsUpdateBMC);

        if ($resultBMA === false || $resultEnc === false || $resultMotivo === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }

    }
    function getDataRegistroTurnoSinRed()
    {
        $id = $_GET['folio'];
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if ($conn === false) {
            die(json_encode(["error" => "Error de conexión a la base de datos."]));
        }

        $query = " SELECT 
                        IdEncabezadoBItacora,
                        Fecha,
                        Turno,
                        NoMaquina,
                        NombreMaquina,
                        golpes AS Cortes,
                        Rechazos,
                        TotalTiempoPerdido AS TiempoPerdido,
                        TiempoArriba,
                        ParosMaquina,
                        HorasTrabajadas
                    FROM (
                        SELECT *,
                            ROW_NUMBER() OVER (PARTITION BY Turno ORDER BY IdEncabezadoBItacora DESC) AS rn
                        FROM ProduccionesMaquinasSinRed
                    ) AS ranked
                    WHERE IdEncabezadoBItacora = ?
                ";
        $params = [$id];
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            $errors = sqlsrv_errors();
            die(json_encode(["error" => "Error en la consulta SQL", "detalle" => $errors]));
        }

        $array = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $fechaFormateada = isset($row['Fecha']) && $row['Fecha'] instanceof DateTime
                ? $row['Fecha']->format('Y-m-d')
                : (is_string($row['Fecha']) ? $row['Fecha'] : null);
            $array[] = [
                "IdEncabezadoBItacora" => $row['IdEncabezadoBItacora'],
                "Fecha" => $fechaFormateada,
                "Turno" => $row['Turno'],
                "NoMaquina" => $row['NoMaquina'],
                "NombreMaquina" => $row['NombreMaquina'],
                "Cortes" => $row['Cortes'],
                "Rechazos" => $row['Rechazos'],
                "TiempoPerdido" => $row['TiempoPerdido'],
                "TiempoArriba" => $row['TiempoArriba'],
                "ParosMaquina" => $row['ParosMaquina'],
                "HorasTrabajadas" => $row['HorasTrabajadas']
            ];
        }

        echo json_encode($array);
    }

    function actualizarRegistroTurnoMaquinaSinRed()
    {
        $id = $_POST['folio'];
        $idEncabezado = $_POST['folioBitacora'];
        $cortes = $_POST['cortes'];
        $rechazos = $_POST['rechazos'];
        $tiempoAbajo = $_POST['tiempoAbajo'];
        $tiempoArriba = $_POST['tiempoArriba'];
        $paros = $_POST['paros'];
        $horasTrabajadas = $_POST['horasTrabajadas'];
        $motivoCambio = $_POST['motivoCambio'];
        $usr = $_SESSION['ibm'];

        // Validar que se recibió el folio y folioBitacora
        if (!isset($id) || !isset($idEncabezado)) {
            echo json_encode(["error" => "Folio(s) no proporcionado(s)"]);
            return;
        }

        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        if ($conn === false) {
            echo json_encode(["error" => "Error de conexión a la base de datos"]);
            return;
        }

        // Actualizar tblBitacoraMaquinasAnterior
        $queryUpdateBitacora = "UPDATE dbo.tblEncabezadoBitacora
                            SET HorasTrabajadas = ?, Rechazos = ?
                            WHERE IdEncabezadoBItacora = ?;";
        $paramsUpdateBMA = [
            $horasTrabajadas,
            $rechazos,
            $idEncabezado
        ];
        $resultBMA = sqlsrv_query($conn, $queryUpdateBitacora, $paramsUpdateBMA);

        // Actualizar golpes

        $queryUpdateGolpes = "UPDATE TLX002MXDB.dbo.tblBitPresentacionGolpes
                                SET golpes = ?
                                WHERE idbitacora = ?
                                AND hora = (
                                    SELECT MAX(hora)
                                    FROM TLX002MXDB.dbo.tblBitPresentacionGolpes
                                    WHERE idbitacora = ?
                            );
                        ";

        $paramsUpdateGolpes = [
            $cortes,
            $idEncabezado,
            $idEncabezado
        ];
        $resultEnc = sqlsrv_query($conn, $queryUpdateGolpes, $paramsUpdateGolpes);

        $queryMotivo = "INSERT INTO tblBitacoraMotivoCambios (folioBitacoraAnterior, folioEncBitacora, noemp, motivo) 
                    VALUES (?, ?, ?, ?)";
        $paramsUpdateBMC = [
            $id,
            $idEncabezado,
            $usr,
            $motivoCambio
        ];
        $resultMotivo = sqlsrv_query($conn, $queryMotivo, $paramsUpdateBMC);

        if ($resultBMA === false || $resultEnc === false || $resultMotivo === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }

    }

    function getDataRegistroTurnoHook(){
        $id = $_GET['folio'];
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');

        $query = "SELECT [Id]
                    ,[FechaTurno]
                    ,tblRTH.[NoMaquina]
                    ,tblMaquinas.NombreMaquina
                    ,tblRTH.[IdEncabezadoBitacora]
                    ,tblEB.[Turno]
                    ,[MilesMetrosHora]
                    ,[Metros]
                    ,[TiempoParoMin] AS TiempoAbajo
                    ,[TiempoCorriendoMin] AS TiempoArriba
                    ,[TiempoPerdido]
                    ,[ParosMaquina]
                    ,tblEB.HorasTrabajadas
                FROM [TLX004MXDB].[dbo].[tblMXPRResumenTurnoHook] tblRTH
                INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblRTH.NoMaquina
                INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora tblEB ON tblEB.IdEncabezadoBItacora = tblRTH.IdEncabezadoBitacora
                WHERE Id = ?";
        
        $params = [$id];
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            die(json_encode(["error" => "Error en consulta", "detalle" => sqlsrv_errors()]));
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $fechaFormateada = isset($row['FechaTurno']) && $row['FechaTurno'] instanceof DateTime
                ? $row['FechaTurno']->format('Y-m-d')
                : (is_string($row['FechaTurno']) ? $row['FechaTurno'] : null);
            $data[] = [
                'Fecha' => $fechaFormateada,
                'Turno' => $row['Turno'],
                'IdEncabezadoBitacora' => $row['IdEncabezadoBitacora'],
                'Maquina' => $row['NombreMaquina'],
                'MetrosLineales' => $row['Metros'],
                'TiempoAbajo' => $row['TiempoAbajo'],
                'TiempoArriba' => $row['TiempoArriba'],
                'ParosMaquina' => $row['ParosMaquina'],
                'Horas' => $row['HorasTrabajadas']
            ];
        }

        echo json_encode($data);

    }
    function getDataMaquinas()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX009MXDB');

        // NoEmp del usuario logueado (IBM)
        $noEmp = $_SESSION['ibm'];

        // Consulta parametrizada:
        // 1) Une al empleado actual (empUser) con NoEmp = ?
        // 2) Si es especial (34374 o 58998) ve todas las máquinas de la lista
        // 3) Si no, solo las de su departamento (empUser.NombreDepartamento = tblMC.NoDepto)
        $sql = "
        SELECT DISTINCT
            tblMC.NoDepto,
            tblMC.NoMaquina,
            tblM.NombreMaquina
        FROM TLX009MXDB.dbo.tblMaquinasCombo AS tblMC
        INNER JOIN TLX009MXDB.dbo.tblMaquinas AS tblM
            ON tblM.NoMaquina = tblMC.NoMaquina
        INNER JOIN TLX032MXDB.dbo.tblEmpleados AS empUser
            ON empUser.NoEmp = ?
        WHERE tblMC.NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 87, 89, 73, 76, 101, 137, 138, 139)
          AND (
              ? IN (34374, 58998)  -- Empleados especiales ven todas
              OR empUser.NombreDepartamento = tblMC.NoDepto  -- Otros ven solo su depto
          )
        ORDER BY tblM.NombreMaquina ASC;
    ";

        // Pasamos $noEmp dos veces: para empUser.NoEmp = ? y para la condición IN (34374, 58998)
        $params = [$noEmp, $noEmp];

        $array = [];

        $result = sqlsrv_query($conn, $sql, $params);

        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
            return;
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = $row;
        }

        http_response_code(200);
        echo json_encode($array);
    }


}

if (isset($_GET['saveProduccionesEnc'])) {
    $producciones = new Producciones();
    $producciones->saveProduccionesEnc();
} else if (isset($_GET['tblProduccionesEnc'])) {
    $producciones = new Producciones();
    $producciones->tblProduccionesEnc();
} else if (isset($_GET['tblctrltiempos'])) {
    $producciones = new Producciones();
    $producciones->tblctrltiempos();
} else if (isset($_GET['tblpresentacionesbit'])) {
    $producciones = new Producciones();
    $producciones->tblpresentacionesbit();
} else if (isset($_GET['saveEntregados'])) {
    $producciones = new Producciones();
    $producciones->saveEntregados();
} else if (isset($_GET['tblEntregados'])) {
    $producciones = new Producciones();
    $producciones->tblEntregados();
} else if (isset($_GET['deleteEntregados'])) {
    $producciones = new Producciones();
    $producciones->deleteEntregados();
} else if (isset($_GET['deleteEncProduccion'])) {
    $producciones = new Producciones();
    $producciones->deleteEncProduccion();
} else if (isset($_GET['tblProduccionesEncxid'])) {
    $producciones = new Producciones();
    $producciones->tblProduccionesEncxid();
} else if (isset($_GET['updateProduccionesEnc'])) {
    $producciones = new Producciones();
    $producciones->updateProduccionesEnc();
} else if (isset($_GET['datagraficasdiario'])) {
    $producciones = new Producciones();
    $producciones->datagraficasdiario();
} else if (isset($_GET['datagraficasdiario2'])) {
    $producciones = new Producciones();
    $producciones->datagraficasdiario2();
} else if (isset($_GET['RegistrarPlanProduccion'])) {
    $producciones = new Producciones();
    $producciones->RegistrarPlanProduccion();
} else if (isset($_GET['actualizarPlanProduccion'])) {
    $producciones = new Producciones();
    $producciones->actualizarPlanProduccion();
} else if (isset($_GET['ObtenerdatosPlanProduccion'])) {
    $producciones = new Producciones();
    $producciones->ObtenerdatosPlanProduccion();
} else if (isset($_GET['Buscarclave'])) {
    $producciones = new Producciones();
    $producciones->Buscarclave();
} else if (isset($_GET['getdataxid'])) {
    $producciones = new Producciones();
    $producciones->getdataxid();
} else if (isset($_GET['tblDatosProduccion'])) {
    $producciones = new Producciones();
    $producciones->tblDatosProduccion();
} else if (isset($_GET['ObtenerdatosPlanProduccionMod'])) {
    $producciones = new Producciones();
    $producciones->ObtenerdatosPlanProduccionMod();
} else if (isset($_GET['BorrarRegistroProduccion'])) {
    $producciones = new Producciones();
    $producciones->BorrarRegistroProduccion();
}


if (isset($_GET['infoTurnosAnteriores'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->infoTurnosAnteriores();
} else if (isset($_GET['getDataRegistroTurno'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->getDataRegistroTurno();
} else if (isset($_GET['actualizarRegistroTurnoMaquina'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->actualizarRegistroTurnoMaquina();
} else if (isset($_GET['infoMaquinasSinRed'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->infoMaquinasSinRed();
} else if (isset($_GET['getDataMaquinas'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->getDataMaquinas();
} else if (isset($_GET['getDataRegistroTurnoSinRed'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->getDataRegistroTurnoSinRed();
} else if (isset($_GET['actualizarRegistroTurnoMaquina'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->actualizarRegistroTurnoMaquina();
} else if (isset($_GET['actualizarRegistroTurnoMaquinaSinRed'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->actualizarRegistroTurnoMaquinaSinRed();
} else if (isset($_GET['infoTurnoHook'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->infoTurnoHook();
} else if (isset($_GET['getDataRegistroTurnoHook'])) {
    $infoMaquinas = new InfoMaquinas();
    $infoMaquinas->getDataRegistroTurnoHook();
} 

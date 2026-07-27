<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class CtrolTiempos
{

    function hora_actual()
	{
		date_default_timezone_set('America/Mexico_City');
		$hora_actual = date('H:i');
		if ($hora_actual >= '07:00' && $hora_actual < '15:00') {
			$turno = 1;
		} else if ($hora_actual >= '15:00' && $hora_actual < '22:30') {
			$turno = 2;
		} else {
			$turno = 3;
		}
		return $turno;
	}

    function tblParos()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $turno = $this->hora_actual();
        $hora_actual = date('H:i');
		$numerohrs = 24;
		if ($turno == 3 && $hora_actual < '07:00')
			$numerohrs = 35;
		$cont = 0;
        $folio = $_GET['folio'];
        $query = "SELECT tblSubEncabezadoBItacora.*,tblProduccionSecciones.Seccion,tblProduccionModulos.Modulos,tblFallas.DescripcionFalla,tblEncabezadoBitacora.NoMaquina FROM tblSubEncabezadoBItacora
        LEFT JOIN TLX002MXDB.dbo.tblProduccionSecciones ON tblProduccionSecciones.idSeccion = tblSubEncabezadoBItacora.NoSeccion
        LEFT JOIN TLX002MXDB.dbo.tblProduccionModulos ON tblProduccionModulos.idModulos = tblSubEncabezadoBItacora.NoModulo
        LEFT JOIN TLX009MXDB.dbo.tblFallas ON tblFallas.IdFalla = tblSubEncabezadoBItacora.IdFalla
        LEFT JOIN tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblSubEncabezadoBItacora.IdEncabezadoBItacora
        INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblEncabezadoBitacora.NoMaquina 
        WHERE tblEncabezadoBitacora.NoMaquina = ? AND tblEncabezadoBitacora.IdEncabezadoBItacora=? 
        ORDER BY tblSubEncabezadoBItacora.EstadoParo ASC, 
        tblSubEncabezadoBItacora.IdSubEncabezadoBitacora DESC";
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
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        $folio = $_POST['folio'];
        $seccion = $_POST['seccion'];
        $modulo = $_POST['modulo'];
        $falla = $_POST['falla'];
        $rechazos = $_POST['rechazos'];
        $tiempoparo = $_POST['tiempoparo'];
        $hora = $_POST['hora'];
        $rechazoscorrida = $_POST['rechazoscorrida'];
        $motivo = $_POST['motivo'];
        $correccion = $_POST['correccion'];
        // $comentarios = $_POST['comentarios'];
        $query = "UPDATE tblSubEncabezadoBItacora SET NoSeccion = ?, NoModulo = ?, IdFalla = ?, Rechazos = ?, TParo = ?,
        Hora = ?, RechazosCorrida = ?, EstadoParo = 1, Motivo = ?, Correccion = ? WHERE IdSubEncabezadoBitacora = ?";
        $result = sqlsrv_query($conn, $query, array($seccion, $modulo, $falla, $rechazos, $tiempoparo, $hora, $rechazoscorrida, $motivo, $correccion, $folio));
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
}

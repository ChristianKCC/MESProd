<?php
require_once "../../conexion.php";
require_once "../../Session/seguridad.php";
Class ReporteBit{
	function tblasistenciasbitrep(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fechai = $_POST["fechai"];
		$fechaf = $_POST["fechaf"];
		$turno = $_POST["turno"];
		$maquina = $_POST["maquinas"];
		empty($turno) ? $turno="" : $turno="AND tblEncabezadoBitacora.Turno=$turno";
		$query="SELECT tblBitAsistencias.folio,tblBitAsistencias.noemp,tblempleados.nombre,tblPuestos.nombre,tblEncabezadoBitacora.Turno FROM tblBitAsistencias 
		INNER JOIN TLX032MXDB.dbo.tblempleados ON tblempleados.noemp=tblBitAsistencias.noemp
		INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblempleados.puesto
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitAsistencias.folio
		WHERE tblEncabezadoBitacora.Fecha >= '$fechai' AND tblEncabezadoBitacora.Fecha < DATEADD(day, 1, '$fechaf') AND tblEncabezadoBitacora.NoMaquina=".$maquina." $turno";
		$array=array();
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
			array_push($array,["folio"=>$row[0],"noemp"=>$row[1],"nombre"=>$row[2],"puesto"=>$row[3],"turno"=>$row[4]]);
		}
		echo json_encode($array);
	}
	function tblcomentariosbitrep(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fechai = $_POST["fechai"];
		$fechaf = $_POST["fechaf"];
		$turno = $_POST["turno"];
		$maquina = $_POST["maquinas"];
		empty($turno) ? $turno="" : $turno="AND tblEncabezadoBitacora.Turno=$turno";
		$query="SELECT tblBitComentarios.*,tblEncabezadoBitacora.Turno FROM tblBitComentarios 
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitComentarios.folio 
		WHERE tblEncabezadoBitacora.Fecha >= '$fechai' AND tblEncabezadoBitacora.Fecha < DATEADD(day, 1, '$fechaf') AND tblEncabezadoBitacora.NoMaquina=".$maquina." $turno";
		$array=array();
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
			array_push($array,["folio"=>$row[1],"seguridad"=>$row[2],"calidad"=>$row[3],"oyl"=>$row[4],"pendientes"=>$row[5]
			,"otros"=>$row[6],"turno"=>$row["Turno"]]);
		}
		echo json_encode($array);
	}
	function tblctrltiemposbitrep(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fechai = $_POST["fechai"];
		$fechaf = $_POST["fechaf"];
		$turno = $_POST["turno"];
		$maquina = $_POST["maquinas"];
		empty($turno) ? $turno="" : $turno="AND tblEncabezadoBitacora.Turno=$turno";
		$query="SELECT tblBitCtrltiempos.*,tblEncabezadoBitacora.Turno,tblProduccionSecciones.Seccion,tblProduccionModulos.Modulos,tblEncabezadoBitacora.Fecha as fechah FROM tblBitCtrltiempos 
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCtrltiempos.folio
		INNER JOIN tblProduccionSecciones on tblProduccionSecciones.idSeccion= tblBitCtrltiempos.seccion
		INNER JOIN tblProduccionModulos ON tblProduccionModulos.idModulos=tblBitCtrltiempos.modulo
		WHERE tblEncabezadoBitacora.Fecha >= '$fechai' AND tblEncabezadoBitacora.Fecha < DATEADD(day, 1, '$fechaf') AND tblEncabezadoBitacora.NoMaquina=".$maquina." $turno";
		$array=array();
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
			array_push($array,["folio"=>$row[1],"horainicio"=>$row[2]->format("H:i:s"),"horafinal"=>$row[3]->format("H:i:s"),"operacion"=>$row[4],"electrico"=>$row[5]
			,"mecanico"=>$row[6],"materias"=>$row[7],"grado"=>$row[8],"prev"=>$row[9],"servicios"=>$row[10],"subtotal"=>$row[11],"seccion"=>$row["Seccion"],"modulo"=>$row["Modulos"]
			,"motivo"=>$row[14],"correccion"=>$row[15],"turno"=>$row["Turno"],"fechah"=>$row["fechah"]->format('Y-m-d')]);
		}
		echo json_encode($array);
	}
	function tblpresentacionesbitrep(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fechai = $_POST["fechai"];
		$fechaf = $_POST["fechaf"];
		$turno = $_POST["turno"];
		$maquina = $_POST["maquinas"];
		empty($turno) ? $turno="" : $turno="AND tblEncabezadoBitacora.Turno=$turno";
		$query="SELECT  tblBitPresentacionSub.idpresentacionenc,tblBitPresentacionEnc.presentacion,tblBitTurnohoras.horastr,
		tblBitPresentacionSub.real,tblBitPresentacionSub.acumulado,tblBitPresentacionSub.std,tblBitPresentacionGolpes.golpes,
		tblBitPresentacionGolpes.merma,tblEncabezadoBitacora.Turno FROM tblBitPresentacionSub 
		INNER JOIN tblBitPresentacionEnc ON tblBitPresentacionSub.idpresentacionenc = tblBitPresentacionEnc.idpresentacionenc
		INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave= tblBitPresentacionEnc.presentacion
		INNER JOIN tblBitTurnohoras ON tblBitTurnohoras.id = tblBitPresentacionSub.hora
		INNER JOIN tblBitPresentacionGolpes ON tblBitPresentacionGolpes.idbitacora = tblBitPresentacionEnc.folio
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitPresentacionEnc.folio
		WHERE tblBitPresentacionGolpes.hora = tblBitPresentacionSub.hora AND tblEncabezadoBitacora.Fecha BETWEEN '$fechai' AND  '$fechaf' AND tblEncabezadoBitacora.NoMaquina=".$maquina." $turno
		ORDER BY Turno";
		$array=array();
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
			array_push($array,["presentacion"=>$row[1],"hora"=>$row[2],"real"=>$row[3],"acumulado"=>$row[4],"std"=>$row[5],
			"golpes"=>$row['golpes'],"merma"=>$row['merma'],"turno"=>$row["Turno"]]);
		}
		echo json_encode($array);
	}
	function tblcorrugadosbitrep(){
		$Conecta = new ClassConexion();
		$conn = $Conecta->conexion("TLX002MXDB");
		$fechai = $_POST["fechai"];
		$fechaf = $_POST["fechaf"];
		$turno = $_POST["turno"];
		$maquina = $_POST["maquinas"];
		empty($turno) ? $turno="" : $turno="AND tblEncabezadoBitacora.Turno=$turno";
		$query="SELECT tblBitCorrugados.*,tblEncabezadoBitacora.Turno FROM tblBitCorrugados 
		INNER JOIN TLX004MXDB.dbo.tblEncabezadoBitacora ON tblEncabezadoBitacora.IdEncabezadoBItacora = tblBitCorrugados.folio
		WHERE tblEncabezadoBitacora.Fecha >= '$fechai' AND tblEncabezadoBitacora.Fecha < DATEADD(day, 1, '$fechaf') AND tblEncabezadoBitacora.NoMaquina=".$maquina." $turno";
		$array=array();
		$result=sqlsrv_query($conn,$query);
		while($row = sqlsrv_fetch_array($result)){
			array_push($array,["folio"=>$row[1],"crecibidas"=>$row[2],"calmacen"=>$row[3],"cproducidas"=>$row[4],"centregadas"=>$row[5],"claveproducto"=>$row[6],
			"turno"=>$row["Turno"]]);
		}
		echo json_encode($array);
	}
}

$ReporteBit = new ReporteBit();
if (isset($_GET["tblasistenciasbitrep"])) {
	$ReporteBit->tblasistenciasbitrep();
}
else if (isset($_GET["tblcomentariosbitrep"])) {
	$ReporteBit->tblcomentariosbitrep();
}
else if (isset($_GET["tblctrltiemposbitrep"])) {
	$ReporteBit->tblctrltiemposbitrep();
}
else if (isset($_GET["tblpresentacionesbitrep"])) {
	$ReporteBit->tblpresentacionesbitrep();
}
else if (isset($_GET["tblcorrugadosbitrep"])) {
	$ReporteBit->tblcorrugadosbitrep();
}
?>
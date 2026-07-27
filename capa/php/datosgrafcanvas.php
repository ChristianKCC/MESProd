<?php
include "../../../csql.php";
$id=$_GET['id'];
			header('Content-Type: application/json');
		   $query = "SELECT tblCapaElementos.elementos,COUNT(*) FROM tblCapaAnalisis INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=tblCapaAnalisis.idcapa INNER JOIN tblCapaElementos ON tblCapaElementos.id=tblCapaAnalisis.elemento WHERE TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=$id GROUP BY tblCapaElementos.elementos";
		 	$resultado = sqlsrv_query($conn, $query);
			$x=0;
			$dir = array();
			while($row=sqlsrv_fetch_array($resultado)){
			$dir[$x]=array("elementos"=>$row[0],"cont"=>$row[1]);
			$x++;
			}
			echo json_encode($dir);
?>
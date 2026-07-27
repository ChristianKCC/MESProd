<?php
require_once "../../conexion.php";
$Conexion1 = new ClassConexion();
$conn=$Conexion1->conexioniap();
Class herraminetas{
    public static function llenarslc($query,$conn){
        $res=sqlsrv_query($conn,$query);
        $datos = [];
        $i=0;
        while($row = sqlsrv_fetch_array($res)){
            $datos[$i]=["valor"=>$row[0],"informacion"=>$row[1]];
            $i++;
        }
        echo json_encode($datos);
        }
}
Class BitacoraElectronica{
    function bitacoraestado(){
        global $conn;
        $query="SELECT TOP 5 * FROM pa05_Operacion ORDER BY pa05_operacion_ndx DESC";
        $result = sqlsrv_query($conn,$query);
        $i=0;
        $datos=[];
        while($row = sqlsrv_fetch_array($result)){
            $datos[$i]=["id"=>$row[0],"Cortes"=>$row[1],"Rechazos"=>$row[2],"Velocidad"=>$row[3],"Opermaq"=>$row[4],"fecha"=>$row[5]->format("Y-m-d"),"hora"=>$row[5]->format("H:i:s")];
            $i++;
        }
        echo json_encode($datos);
    }
    function bitacoraparos($addwhere){
        global $conn;
        $query="SELECT TOP 50 * FROM pa05_Operacionparos WHERE completo IS NULL $addwhere ORDER BY pa05_Operacionparos_ndx DESC";
        $result = sqlsrv_query($conn,$query);
        $i=0;
        $datos=[];
        while($row = sqlsrv_fetch_array($result)){
            $datos[$i]=["id"=>$row[0],"Cortes"=>$row[1],"Rechazos"=>$row[2],"Velocidad"=>$row[3],"Opermaq"=>$row[4],"fecha"=>$row[5]->format("Y-m-d"),"hora"=>$row[5]->format("H:i:s"),"fechacompleta"=>$row[5]->format("Y-m-d H:i:s")];
            $i++;
        }
        echo json_encode($datos);
    }
    function guardar(){
        $Conexion1 = new ClassConexion();
        $conn=$Conexion1->conexion("TLX003MXDB");
        $idparo= $_POST["idparo"];
        $maquinas= $_POST["maquinas"];
        $turno= $_POST["turno"];
        $presentacion= $_POST["presentacion"];
        $Usuarios= $_POST["Usuarios"];
        $secciones= $_POST["secciones"];
        $falla= $_POST["falla"];
        $enarranque= $_POST["enarranque"];
        $equipo= $_POST["equipo"];
        $calidad= $_POST["calidad"];
        $comentarios= $_POST["comentarios"];
        $sql ="INSERT INTO tblRegparosbitacora(idparo,maquinas,turno,presentacion,Usuarios,secciones,falla,enarranque,equipo,calidad,comentarios)
         VALUES ('".$idparo."','".$maquinas."','".$turno."','".$presentacion."','".$Usuarios."','".$secciones."','".$falla."','".$enarranque."','".$equipo."','".$calidad."','".$comentarios."')";
        sqlsrv_query( $conn, $sql );
        sqlsrv_close($conn);
        $conn=$Conexion1->conexioniap();
        $sql ="UPDATE pa05_Operacionparos SET completo=1 WHERE pa05_Operacionparos_ndx= $idparo";
        sqlsrv_query( $conn, $sql );
        sqlsrv_close($conn);
        echo json_encode("done");
    }
}
$BitacoraElectronica =new BitacoraElectronica();
if(isset($_GET["tblparos"])){
    $BitacoraElectronica->bitacoraparos("");
}
if(isset($_GET["tblparosid"])){
    $id=$_GET["id"];
    $BitacoraElectronica->bitacoraparos("AND pa05_Operacionparos_ndx=".$id);
}
if(isset($_GET["Usuarios"])){
    $conn=$Conexion1->conexion('TLX009MXDB');
    herraminetas :: llenarslc("Select NoEmp,Nombre from TLX032MXDB.dbo.tblEmpleados WHERE NombreDepartamento=25 Order by nombre",$conn);
}
if(isset($_GET["Turnos"])){
    $conn=$Conexion1->conexion('TLX036MXDB');
    herraminetas :: llenarslc("Select id,nombre from Trazabilidadturno",$conn);
}

if(isset($_GET["presentaciones"])){
    $conn=$Conexion1->conexion('TLX009MXDB');
    herraminetas :: llenarslc("SELECT tblPresentaciones.IdPresentacion,tblPresentaciones.NombrePresentacion FROM tblPresentaciones INNER JOIN tblPresentacionesMaquinaCombo ON tblPresentacionesMaquinaCombo.IdPresentacion = tblPresentaciones.IdPresentacion WHERE tblPresentacionesMaquinaCombo.NoMaquina=97",$conn);
}

if(isset($_GET["tamano"])){
    $conn=$Conexion1->conexion('TLX003MXDB');
    $presentacion=$_GET["presentacion"];
    herraminetas :: llenarslc("Select id,nombre from tblTamanoBitacora Where id_presentacion= $presentacion",$conn);
}

if(isset($_GET["Fallas"])){
    $conn=$Conexion1->conexion('TLX009MXDB');
    herraminetas :: llenarslc("SELECT IdFalla,DescripcionFalla FROM tblFallas ORDER BY DescripcionFalla Asc",$conn);
}


if(isset($_GET["Seccion"])){
    $conn=$Conexion1->conexion('TLX009MXDB');
    herraminetas :: llenarslc("SELECT tblSecciones.NoSeccion,tblSecciones.NombreSeccion FROM tblSecciones inner join tblSeccionesCombo ON tblSeccionesCombo.NoSeccion = tblSecciones.NoSeccion WHERE tblSeccionesCombo.NoMaquina='97' ",$conn);
}
if(isset($_GET["Modulos"])){
    $conn=$Conexion1->conexion('TLX009MXDB');
    $seccion=$_GET["seccion"];
    herraminetas :: llenarslc("Select tblModulos.NoModulo,tblModulos.NombreModulo FROM tblModulos INNER JOIN tblModulosCombo On tblModulosCombo.NoModulo = tblModulos.NoModulo WHERE tblModulosCombo.NoSeccion=$seccion",$conn);
}

if(isset($_GET["guardar"])){
    $BitacoraElectronica->guardar();
}

if(isset($_GET["pa05"])){
  $conn= $Conexion1->conexioniap();
	$etiquetas = $Opera =  $merma = [];
    $query="SELECT TOP 1  * FROM pa05_Operacion where OperMaq is not null ORDER BY pa05_operacion_ndx DESC";
	$result=sqlsrv_query($conn,$query);
	while ($row = sqlsrv_fetch_array($result)) {
		$row["Rechazos"]==0 || $row["Cortes"] == 0 ? $resultado=0 : $resultado = ($row["Rechazos"]/$row["Cortes"])*100;
		array_push($etiquetas, $row["t_stamp2"]->format("H:i:s"));
		array_push($Opera, $row["OperMaq"]);
		array_push($merma, $resultado);
	}
   $respuesta = [ "etiquetas" => $etiquetas,"datos" => $Opera,"merma" => $merma];
   echo json_encode($respuesta);
}
if(isset($_GET["pa05_tiempultparo"])){
    $conn= $Conexion1->conexioniap();
      $etiquetas = $Opera =  $turno = [];
      $query="SELECT TOP 1 * FROM pa05_Operacionparos WHERE OperMaq=1 ORDER BY pa05_Operacionparos_ndx DESC"; 
      $result = sqlsrv_query($conn,$query);
        $i=0;
        $datos=[];
        while($row = sqlsrv_fetch_array($result)){
            $datos[$i]=["id"=>$row[0],"fechacompleta"=>$row[5]->format("Y-m-d H:i:s")];
            $i++;
        }
        echo json_encode($datos);
  }
function color_rand() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
 }
if(isset($_GET["consultagraf"])){
     $etiquetas = [];
     $datosVentas = [];
     $colores = [];
     $query="SELECT * FROM pa05_Operacionparos "; 
     $result=sqlsrv_query($conn,$query);
     while ($row = sqlsrv_fetch_array($result)) {
         array_push($etiquetas, $row[0]);
          array_push($datosVentas, $row[1]);
          array_push($colores, color_rand());
     }
    $respuesta = [ "etiquetas" => $etiquetas,"datos" => $datosVentas,"colores" => $colores];
    echo json_encode($respuesta);
}



if(isset($_GET["supervisores"])){
    $conn=$Conexion1->conexion('TLX003MXDB');
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query="SELECT tblEmpleados.Nombre,COUNT(*) FROM tblRegparosbitacora INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblRegparosbitacora.Usuarios GROUP BY tblEmpleados.Nombre"; 
    $result=sqlsrv_query($conn,$query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
         array_push($datosVentas, $row[1]);
         array_push($colores, color_rand());
    }
   $respuesta = [ "etiquetas" => $etiquetas,"datos" => $datosVentas,"colores" => $colores];
   echo json_encode($respuesta);
}
if(isset($_GET["fallas"])){
    $conn=$Conexion1->conexion('TLX003MXDB');
    $etiquetas = [];
    $datosVentas = [];
    $colores = [];
    $query="SELECT tblFallas.DescripcionFalla,COUNT(*) FROM tblRegparosbitacora 
    INNER JOIN TLX009MXDB.dbo.tblFallas ON tblFallas.IdFalla = tblRegparosbitacora.falla GROUP BY  tblFallas.DescripcionFalla"; 
    $result=sqlsrv_query($conn,$query);
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($etiquetas, $row[0]);
         array_push($datosVentas, $row[1]);
         array_push($colores, color_rand());
    }
   $respuesta = [ "etiquetas" => $etiquetas,"datos" => $datosVentas,"colores" => $colores];
   echo json_encode($respuesta);
}

?>
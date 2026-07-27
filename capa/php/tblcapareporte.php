<?php 
require_once "../../../csql6.php";
  require_once("../../Session/seguridad.php");
$fechai=$_POST['fechai'];
$fechaf=$_POST['fechaf'];
$addwhere='';
$idsec=0;
$andor='';
$where='';
$contfilas=0;
if(!empty($_POST['departamento'])){
$departamento=$_POST['departamento'];
$cont=count($departamento);
 for ($i=0; $i<$cont ; $i++){
if($i>0 or $idsec!=0)
$addwhere .="or tblEncabezadoCAPAweb.NoDepto=".$departamento[$i]." ";
else
$addwhere .="where (tblEncabezadoCAPAweb.NoDepto=".$departamento[$i]." ";
}
$addwhere.=") ";
$idsec++;
}
if(!empty($_POST['tipofuente'])){
$tipofuente=$_POST['tipofuente'];
$cont=count($tipofuente);
 for ($i=0; $i<$cont ; $i++){
if($i==0)$andor='and (';else$andor='or';
if($i>0 OR $idsec!=0)
$addwhere .=$andor." tblEncabezadoCAPAweb.IdTipoFuente=".$tipofuente[$i]." ";
else
$addwhere .="where (tblEncabezadoCAPAweb.IdTipoFuente=".$tipofuente[$i]." ";
}
$idsec++;
$addwhere.=") ";
}
$key='';
if(isset($_POST['key'])){
$key=$_POST['key'];
}
 $fechaswhere='';
if($addwhere=='')$where="where";else $andor='and';
  if(!empty($fechai) OR !empty($fechaf)){
    $fechaswhere="tblEncabezadoCAPAweb.Fecha >= '$fechai' AND tblEncabezadoCAPAweb.Fecha < DATEADD(day,1,'$fechaf') AND";
  }
  $query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion, tblEncabezadoCapaweb.Edicionterminada FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion $addwhere $where $andor $fechaswhere (tblEncabezadoCapaweb.FolioCapa LIKE '%".$key."%' OR tblEncabezadoCapaweb.DescripcionCAPA LIKE '%".$key."%') ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
$result=sqlsrv_query($conn,$query);
  if( $result === false ) {
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
}
$suma=0;
 ?>
 <div class="table-responsive" style="height:400px; overflow: scroll;">
  <table  class="table table-sm table-hover table-striped">  
        <thead class="table-dark">
          <th>Folio</th>
          <th>Departamento</th>
          <th>Máquina</th>
          <th>Sección</th>
          <th>Fuente</th>
          <th>Tipo fuente</th>
          <th>Ver</th>
          <th>Resumen</th>
        </thead>
        <tbody>
            <?php 
      while ($fila=sqlsrv_fetch_array($result)){
          $suma=$fila[9]+$fila[10]+$fila[11];
          echo "<tr><td>".$fila[0]."</td>";
          echo "<td>Departamento: ".$fila[2]."</td>";
          echo "<td>Maquina: ".$fila[3]."</td>";
          echo "<td>Seccion: ".$fila[4]."</td>";
          echo "<td>Fuente: ".$fila[5]."</td>";
          echo "<td>Tipo de fuente: ".$fila[6]."</td>";
        if($suma<=10){
        echo '<td>
          <button type="button" class="btn text-white btn-sm btn-sm" style=" background:#023e8a;" onclick="vercorrecciones('.$fila[0].')"><i class="fas fa-eye"></i></button></td>';
        }else{
        echo '<td>
          <button type="button" class="btn text-white btn-sm btn-sm" style=" background:#023e8a;" onclick="vercapa('.$fila[0].')"><i class="fas fa-eye"></i></button>
          </td>';
           echo '<td>
           <button type="button" class="btn text-white btn-sm btn-sm" style=" background:#023e8a;" onclick="vercaparesumen('.$fila[0].')"><i class="fa-solid fa-square-poll-vertical"></i></button>';
             echo '
           <button type="button" class="btn text-white btn-sm btn-sm" style=" background:#023e8a;" onclick="vercaparesumengraf('.$fila[0].')"><i class="fa-solid fa-chart-pie"></i></button></td>';
        }
        echo "</tr>";
       }
       ?>
      </tbody>
    </table>
  </div>

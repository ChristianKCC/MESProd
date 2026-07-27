<?php 
if (isset($_GET['capas'])) {
require_once "../../../csql6.php";
  require_once("../../Session/seguridad.php");
    $key='';
    $validaauto=0;
  if(isset($_POST['key'])){
  $key=$_POST['key'];
  $key=str_replace('"', '', $key);
  $key=str_replace("'", '', $key);
  }
$query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion, tblEncabezadoCapaweb.Edicionterminada,TLX009MXDB.dbo.tblCapaNoExpuestas.riesgo AS noexpuestas FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion INNER JOIN TLX009MXDB.dbo.tblCapaNoExpuestas ON TLX009MXDB.dbo.tblCapaNoExpuestas.id=tblEncabezadoCapaweb.Noexpuestas WHERE ((tblEncabezadoCAPAweb.Noemp = '".$_SESSION["ibm"]."' OR tblEncabezadoCAPAweb.Asignado = '".$_SESSION["ibm"]."') AND tblEncabezadoCapaweb.FolioCapa LIKE '%".$key."%'  AND tblEncabezadoCapaweb.IdAutorizacion=0 AND Edicionterminada=0) ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
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
$foliocapamodal=0;
 ?>
 <div class="row justify-content-between m-2 align-items-center">
            <?php 
      while ($fila=sqlsrv_fetch_array($result)){
        $foliocapamodal=$fila[0];

        ?>
      <div class="col-3 mx-4 mb-2 border rounded shadow-sm p-2 bg-white">
        <?php
          echo "<h4 class='fw-bold'>Folio:".$fila[0]."</h4>";
          echo "<span>Departamento: ".$fila[2]."</span><br>";
          echo "<span>Máquina: ".$fila[3]."</span><br>";
          echo "<span>Sección: ".$fila[4]."</span><br>";
          echo "<span>Fuente: ".$fila[5]."</span><br>";
          echo "<span>Tipo de fuente: ".$fila[6]."</span><br>";
          echo '<ul class="list-group list-group-flush">
          <li class="list-group-item">';
          // Seguridad
             if($fila[7]=='Seguridad'){
              $suma=$fila[9]*$fila[10]*$fila[11]*$fila[13];
              if($suma<=5){
              echo '<p class="text-success">Riesgo aceptable.</p>';
              }else if($suma<=50){
              echo '<p class="text-warning">Riesgo bajo.</p>';
              }else if($suma<=500){
              echo '<p class="text-danger">Riesgo alto.</p>';
              }else if($suma>500){
              echo '<p class="text-danger">Riesgo inaceptable.</p>';
              }
                   echo '
                 <p><a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0096c7;" onclick="informemayor('.$fila[0].')"><i class="fa-solid fa-magnifying-glass"></i> Investigación</a>
                    <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0077B6;"  onclick="analisis('.$fila[0].')"> <i class="fa-solid fa-triangle-exclamation"></i> Análisis de causas</a>
                    <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#023E8A;" onclick="acciones('.$fila[0].')">
                 <i class="fa-solid fa-people-carry-box"></i> Acciones correctivas</a></p>';
              
          }
          // Calidad
            else if($fila[7]=='Calidad'){
            $suma=$fila[9]+$fila[10]+$fila[11];
              if($suma<=10){
              echo '
              <p class="text-warning">La desviación no representa un riesgo mayor o crítico</p>
              <p><a href="#"  class="btn text-white form-control btn-sm my-2" style="background:#0077B6;" onclick="informemayor('.$fila[0].')"><i class="fa-solid fa-magnifying-glass"></i> Investigación</a>
              <a href="#" class="btn text-white form-control btn-sm my-2" style="background:#023E8A;"  onclick="accionesmenor('.$fila[0].')"><i class="fa-solid fa-people-carry-box"></i> Correcciones</a></p>';
              }else{
                   echo '
              <p class="text-danger">La desviación representa un riesgo crítico</p>
                 <p><a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0096c7;" onclick="informemayor('.$fila[0].')"><i class="fa-solid fa-magnifying-glass"></i> Investigación';
                  $queryinv2="SELECT * FROM TLX009MXDB.dbo.tblCapaInforme WHERE idcapa=$foliocapamodal";
                  $resultinv2=sqlsrv_query($conn,$queryinv2);
                  $continv=0;
                  while($rowinv=sqlsrv_fetch_array($resultinv2)){
                    $continv=1;
                  }
                 if($continv!=0){
                  echo ' <i class=" text-dark fa-solid fa-circle-check"></i></a>';
                 }else{
                  echo  '</a>';
                 }
                 echo '<a href="#" class="btn text-white form-control btn-sm my-2" style="background:#0077B6;" onclick="analisis('.$fila[0].')"> <i class="fa-solid fa-triangle-exclamation"></i> Análisis de causas';
                  $queryan="SELECT * FROM TLX009MXDB.dbo.tblCapaAnalisis WHERE idcapa=$foliocapamodal";
                  $resultan=sqlsrv_query($conn,$queryan);
                  $contan=0;
                  while($rowinv=sqlsrv_fetch_array($resultan)){
                    $contan=1;
                  }
                 if($contan!=0){
                  echo ' <i class=" text-dark fa-solid fa-circle-check"></i></a>';
                 }else{
                  echo  '</a>';
                 }
                echo '<a href="#" class="btn text-white form-control btn-sm my-2" style="background:#023E8A;" onclick="acciones('.$fila[0].')">
               <i class="fa-solid fa-people-carry-box"></i> Acciones correctivas';
                $queryac="SELECT * FROM TLX009MXDB.dbo.tblCapaAcciones INNER JOIN TLX009MXDB.dbo.tblCapaAnalisis ON tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON tblEncabezadoCapaweb.FolioCapa=tblCapaAnalisis.idcapa WHERE FolioCapa=$foliocapamodal";
                  $resultac=sqlsrv_query($conn,$queryac);
                  $contac=0;
                  while($rowinv=sqlsrv_fetch_array($resultac)){
                    $contac=1;
                  }
                 if($contac!=0){
                  echo ' <i class=" text-dark fa-solid fa-circle-check"></i></a></p>';
                 }else{
                  echo  '</a></p>';
                 }
              }
          }
        echo '</li>';
        if($suma<=10){
        echo '<li class="list-group-item">
          <button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercorrecciones('.$fila[0].')">Ver Correcciones <i class="fas fa-eye"></i></button> ';
        }else{
        echo '<li class="list-group-item">
          <button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercapa('.$fila[0].')">CAPA <i class="fas fa-eye"></i></button> ';
          echo '<button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercaparesumen('.$fila[0].')">Resumen <i class="fa-solid fa-chart-line"></i></button> ';
           if(($contac+$contan+$continv)==3){
         echo '<button type="button" class="btn text-white btn-sm" style=" background:#307473;" data-bs-toggle="modal" data-bs-target="#termina" onclick="envfoliocapa('.$fila[0].')">Terminar <i class="far fa-paper-plane"></i></button></li>';
        }
        }
       
        echo '</li></ul>';
       ?>
          <small class="text-muted"><?php echo "Se cargó el ".$fila[1]->format("Y/m/d"); ?></small>
         </div>
<?php 
}
 ?>
</div>
   <script type="text/javascript">
       function envfoliocapa(val){
          $("#receiverfolio").val(val);
      }
  </script>
        <div class="modal fade" id="termina" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enviar para autorización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="receiverfolio">
                <select class='form-control form-control-sm' id='enviaaemp'>
                </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="sendcapa($('#receiverfolio').val())" data-bs-dismiss="modal">Enviar</button>
              </div>
            </div>
          </div>
    </div>
<?php }



else if (isset($_GET['capascst'])) {
 require_once '../../../csql6.php';
  require_once("../../Session/seguridad.php");
  $query = "SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM WHERE tblEncabezadoCAPAweb.Noemp = '".$_SESSION["ibm"]."' AND Edicionterminada=0 ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive my-custom-scrollbar">
<table class="table table-sm table-hover table-sm" id="capa">
  <thead class="table-dark">
    <th>Folio</th>
    <th>Fecha</th>
    <th>Departamento</th>
    <th>Máquina</th>
    <th>Sección</th>
    <th>Fuente</th>
    <th>Tipo fuente</th>
    <th>Responsabilidad</th>
    <th>Descripción</th>
    <th>Editar</th>
  </thead>
<tbody>
<?php
  while($row = sqlsrv_fetch_array($result)){
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[1]->format("Y-m-d")."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[6]."</td>";
    echo "<td>".$row[7]."</td>";
    echo "<td>".$row[8]."</td>";
    echo "<td><button class='btn btn-warning btn-sm' onclick='llnarctacapa(".$row[0].")''><i class='fa-solid fa-pen-to-square'></i></button></td></tr>";
  }
?>
</tbody>
</table>

</div>

<?php 
}
else if (isset($_GET['capasautoriza'])) {
 require_once '../../../csql6.php';
  require_once("../../Session/seguridad.php");
  $query = "SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion WHERE (tblEncabezadoCAPAweb.Autorizador=".$_SESSION['ibm']." AND Edicionterminada=1 AND IdAutorizacion=0)";
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive my-custom-scrollbar">
<table class="table table-sm table-striped table-hover table-sm" id="capa">
  <thead class="table-dark">
    <th>Folio</th>
    <th>Fecha</th>
    <th>Departamento</th>
    <th>Máquina</th>
    <th>Sección</th>
    <th>Fuente</th>
    <th>Tipo fuente</th>
    <th>Acciones</th>
  </thead>
<tbody>
<?php
$suma=0;
  while($row = sqlsrv_fetch_array($result)){
    $suma=$row['severidad']+$row['probabilidad']+$row['deteccion'];
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[1]->format("Y-m-d")."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[6]."</td>";
    if($suma<=10){
    echo '<td><button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercorrecciones('.$row[0].')">Correcciones<i class="fas fa-eye"></i></button> ';
    }else{
    echo '<td><button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercapa('.$row[0].')">CAPA <i class="fas fa-eye"></i></button> ';
    echo '<button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercaparesumen('.$row[0].')">Resumen <i class="fa-solid fa-chart-line"></i></button> ';
    }
    echo '<button type="button" class="btn btn-danger text-white btn-sm" onclick="devolver('.$row[0].')">Devolver <i class="fa-solid fa-arrow-rotate-left"></i></button> ';
    echo '<button type="button" class="btn btn-success text-white btn-sm" onclick="autorizacapa('.$row[0].')">Autorizar <i class="fa-solid fa-circle-check"></i></button></td></tr> ';
  }
?>
</tbody>
</table>

</div>

<?php 
}

else if (isset($_GET['investigacion'])) {
  require_once "../../../csql.php";
  $id=$_POST['id'];
  $query = "SELECT tblCapaInforme.id,tblCapaInforme.idcapa,tblCapaInforme.quesucedio,tblCapaInforme.cuandosucedio,tblCapaInforme.comosucedio,tblCapaInforme.porquesucedio,tblCapaInforme.dondesucedio,tblCapaInforme.quienoperaba ,TLX032MXDB.dbo.tblEmpleados.Nombre,tblCapaInforme.cuantasvecespaso, tblCapaInforme.confirmado,tblCapaInforme.descripcion,tblCapaInforme.archivo FROM tblCapaInforme INNER JOIN TLX032MXDB.dbo.tblEmpleados ON TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaInforme.quienoperaba WHERE tblCapaInforme.idcapa=".$id;
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive">
<table class="table table-sm table-hover table-sm" id="tblinvestigacionllnar">
  <thead class="table-dark">
    <th>ID</th>
    <th>¿Qué sucedió?</th>
    <th>¿Cuándo sucedió?</th>
    <th>¿Cómo sucedió?</th>
    <th>¿Por qué sucedió?</th>
    <th>¿Quién operaba?</th>
    <th>¿Cuántas veces pasó?</th>
    <th>Descripción</th>
    <th>Archivo</th>
    <th>Edit/Borrar</th>
  </thead>
<tbody>
<?php
  while($row = sqlsrv_fetch_array($result)){
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[3]->format("Y-m-d")."</td>";
    echo "<td>".$row[4]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[8]."</td>";
    echo "<td>".$row[9]."</td>";
    echo "<td>".$row[11]."</td>";
    echo "<td><a href='Arvhivos/".$row[12]."' target='_blank'>".$row[12]."<a></td>";
    echo "<td><button class='btn btn-warning btn-sm' onclick='llnarinv(".$row[0].")' title='Editar'><i class='fa-solid fa-pen-to-square'></i></button> <button class='btn btn-danger btn-sm' onclick='eliminainvestigacion(".$row[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button></td></tr>";
  }
?>
</tbody>
</table>
</div>
<?php 
}


else if (isset($_GET['causas'])) {
  require_once "../../../csql.php";
  $id=$_POST['id'];
  $query = "SELECT tblCapaAnalisis.id,tblCapaAnalisis.idcapa,tblCapaElementos.elementos,tblCapaAnalisis.porque1,tblCapaAnalisis.porque2,
tblCapaAnalisis.porque3,tblCapaAnalisis.porque4,tblCapaAnalisis.porque5, tblCapaEfectDeseado.efecto_deseado,tblCapaCondicionesSubestandar.condiciones_subestandar FROM tblCapaAnalisis
INNER JOIN tblCapaElementos ON tblCapaElementos.id = tblCapaAnalisis.elemento INNER JOIN tblCapaEfectDeseado ON tblCapaEfectDeseado.id= tblCapaAnalisis.proridad INNER JOIN tblCapaCondicionesSubestandar ON tblCapaCondicionesSubestandar.id= tblCapaAnalisis.raiz WHERE tblCapaAnalisis.idcapa=".$id;
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive">
<table class="table table-sm table-hover table-sm" id="causasllnar">
  <thead class="table-dark">
    <th>ID</th>
    <th>Elemento</th>
    <th>¿Por qué 1?</th>
    <th>¿Por qué 2?</th>
    <th>¿Por qué 3?</th>
    <th>¿Por qué 4?</th>
    <th>¿Por qué 5?</th>
    <th>Prioridad</th>
    <th>Edit/Borrar</th>
  </thead>
<tbody>
<?php
  while($row = sqlsrv_fetch_array($result)){
    echo "<tr><td>".$row[0]."</td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[4]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[6]."</td>";
    echo "<td>".$row[7]."</td>";
    echo "<td>".$row[8]."</td>";
    echo "<td><button class='btn btn-warning btn-sm' onclick='llnaranal(".$row[0].")' title='Editar'><i class='fa-solid fa-pen-to-square'></i></button> <button class='btn btn-danger btn-sm' onclick='eliminacausas(".$row[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button></td></tr>";
  }
?>
</tbody>
</table>
</div>
<?php 
}



else if (isset($_GET['acp'])) {
  require_once "../../../csql.php";
  $id=$_POST['id'];
  $query = "SELECT tblCapaAcciones.id,tblCapaAcciones.idcausas, tblCapaTipoAccion.nombre,tblCapaAcciones.actividad ,tblCapaAcciones.responsable,TLX032MXDB.dbo.tblEmpleados.Nombre, tblCapaAcciones.fechadecompromiso,tblCapaPracticasSub.PracticasSubEstandar, tblCapaCondicionesSubestandar.condiciones_subestandar FROM TLX009MXDB.dbo.tblCapaAcciones INNER JOIN TLX009MXDB.dbo.tblCapaAnalisis ON tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON tblEncabezadoCapaweb.FolioCapa=tblCapaAnalisis.idcapa INNER JOIN tblCapaTipoAccion ON tblCapaTipoAccion.id= tblCapaAcciones.tipodeaccion INNER JOIN TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaAcciones.responsable INNER JOIN tblCapaPracticasSub ON  tblCapaPracticasSub.id=tblCapaAcciones.causainm INNER JOIN tblCapaCondicionesSubestandar ON tblCapaCondicionesSubestandar.id=tblCapaAnalisis.raiz WHERE TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=".$id;
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive">
<table class="table table-sm table-hover table-sm" id="acpllnar">
  <thead class="table-dark">
    <th>ID</th>
    <th>Tipo acción</th>
    <th>Actividad</th>
    <th>Responsable</th>
    <th>Causa raíz</th>
    <th>Causa inmediata</th>
    <th>Fecha compromiso</th>
    <th>Edit/Borrar</th>
  </thead>
<tbody>
<?php
  while($row = sqlsrv_fetch_array($result)){
  echo "<tr><td>".$row[0]."</td>";
  echo "<td>".$row[2]."</td>";
  echo "<td>".$row[3]."</td>";
  echo "<td>".$row[5]."</td>";
  echo "<td>".$row[8]."</td>";
  echo "<td>".$row[7]."</td>";
  echo "<td>".$row[6]->format('Y-m-d')."</td>";
  echo "<td><button class='btn btn-warning btn-sm' onclick='acpllnar(".$row[0].")' title='Editar'><i class='fa-solid fa-pen-to-square'></i></button> <button class='btn btn-danger btn-sm' onclick=eliminaacciones('".$row[0]."') title='Borrar'><i class='fas fa-trash-alt'></i></button></td></tr>";
  }
?>
</tbody>
</table>
</div>
<?php 
}

else if (isset($_GET['acpmenor'])) {
  require_once "../../../csql.php";
  $id=$_POST['id'];
  $query = "SELECT tblCapaAccionesMenor.id,tblCapaAccionesMenor.idcapa, tblCapaTipoAccionMenor.nombre,tblCapaAccionesMenor.actividad ,tblCapaAccionesMenor.responsable,TLX032MXDB.dbo.tblEmpleados.Nombre, tblCapaAccionesMenor.fechadecompromiso FROM tblCapaAccionesMenor INNER JOIN tblCapaTipoAccionMenor ON tblCapaTipoAccionMenor.id= tblCapaAccionesMenor.tipodeaccion INNER JOIN TLX032MXDB.dbo.tblEmpleados on TLX032MXDB.dbo.tblEmpleados.NoEmp=tblCapaAccionesMenor.responsable WHERE tblCapaAccionesMenor.idcapa=".$id;
  $result = sqlsrv_query($conn, $query);
?>
<div class="table-responsive">
<table class="table table-sm table-striped table-hover table-sm" id="capa">
  <thead class="table-dark">
    <th>ID</th>
    <th>Tipo acción</th>
    <th>Actividad</th>
    <th>Responsable</th>
    <th>Fecha compromiso</th>
  </thead>
<tbody>
<?php
  while($row = sqlsrv_fetch_array($result)){
    echo "<tr><td>".$row[0]." <button class='btn btn-danger' onclick='eliminaaccionesmenor(".$row[0].")' title='Borrar'><i class='fas fa-trash-alt'></i></button></td>";
    echo "<td>".$row[2]."</td>";
    echo "<td>".$row[3]."</td>";
    echo "<td>".$row[5]."</td>";
    echo "<td>".$row[6]->format('Y-m-d')."</td></tr>";
  }

?>
</tbody>
</table>
</div>
<?php 
}



else if (isset($_GET['accionesmenoruser'])) {
require_once '../../../csql.php';
require_once("../../Session/seguridad.php");
$query="SELECT tblCapaAccionesMenor.*,TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa,tblCapaTipoAccionMenor.nombre AS tpan FROM tblCapaAccionesMenor INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb on TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=tblCapaAccionesMenor.idcapa INNER JOIN tblCapaTipoAccionMenor on tblCapaTipoAccionMenor.id=tblCapaAccionesMenor.tipodeaccion WHERE  (tblCapaAccionesMenor.responsable=".$_SESSION['ibm']." AND TLX006MXDB.dbo.tblEncabezadoCapaweb.IdAutorizacion=1 AND tblCapaAccionesMenor.accioncompleta='0')";
$resultado = sqlsrv_query($conn,$query);
 ?>
 <div class="table-responsive">
<table class="table table-sm table-striped table-hover table-sm" id="capa">
  <thead class="table-dark">
    <th>Folio</th>
              <th>Tipo acción</th>
              <th>Descripción</th>
              <th>Fecha compromiso</th>
              <th>Investigación</th>
              <th>Finalizar</th>
  </thead>
  <tbody>
    <?php 
    while ($row=sqlsrv_fetch_array($resultado)) {
                     echo "<tr><td>".$row[0]."</td>";
                     echo "<td>".$row['tpan']."</td>";
                     echo "<td>".$row[3]."</td>";
                     echo "<td>".$row[5]->format("Y/m/d")."</td>";
            echo "<td><button class='btn btn-primary btn-sm' onclick='vercapa2($row[9])'><i class='fas fa-eye'></i></button></td>";
            echo "<td><button class='btn btn-success btn-sm' onclick='finalizaraccion2($row[0])'><i class='fas fa-check-double'></i></button></td></tr>";
    }
     ?>
  </tbody>
 </table>
</div>
<?php 
  }


else if (isset($_GET['eficacia'])) {
require_once '../../../csql.php';
$id=$_POST['id'];
$query="SELECT * FROM tblCapaEficacia WHERE id_actividad=$id";
$resultado = sqlsrv_query($conn,$query);
?>
<div class="row">
  <div class="col-6">
<div class="table-responsive">
<table class="table table-sm table-striped table-hover table-sm" id="capa">
  <thead class="table-dark">
              <th>Día</th>
              <th>Eficacia</th>
  </thead>
  <tbody>
    <?php 
    $dias=0;
    $porcentaje=0;
    while ($row=sqlsrv_fetch_array($resultado)) {
      $dias++;
                     echo "<tr><td>".$dias."</td>";
                     if($row[2]==1){
                     $porcentaje++;
                     echo '<td class="text-success"><i class="fa-solid fa-circle-chevron-up"></i> 100%</td></tr>';
                      }

                     else if($row[2]==0)
                     echo '<td class="text-danger"><i class="fa-solid fa-circle-chevron-down"></i> 0%</td></tr>';
      }
      if($dias>0)
      $porcentaje=($porcentaje/$dias)*100;
       ?>
      
  </tbody>
 </table>
</div>
</div>
  <div class="col-6 p-5 text-center">
    <div class="m-0 row justify-content-center align-items-center">
    <h1><?php echo round($porcentaje,2)."%"; ?></h1>
    </div>
  </div>
</div>
<?php 
}





else if (isset($_GET['efectividad'])) {
require_once "../../../csql6.php";
  $query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion, tblEncabezadoCapaweb.Edicionterminada FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";

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
 <div class="table-responsive" style="height:100%; overflow: scroll;">
  <table  class="table table-sm table-hover table-striped">  
        <thead class="table-dark">
          <th>Folio</th>
          <th>Departamento</th>
          <th>Máquina</th>
          <th>Sección</th>
          <th>Fuente</th>
          <th>Tipo fuente</th>
          <th>Efectividad</th>
          <th>Mostrar</th>
        </thead>
        <tbody>
            <?php 
      while ($fila=sqlsrv_fetch_array($result)){
            $contaccionestotal=0;
            $contaccionescomp=0;
          $suma=$fila[9]+$fila[10]+$fila[11];
          $queryaccion="SELECT TLX009MXDB.dbo.tblCapaAcciones.* from TLX009MXDB.dbo.tblCapaAcciones inner join  TLX009MXDB.dbo.tblCapaAnalisis on  TLX009MXDB.dbo.tblCapaAnalisis.id=  TLX009MXDB.dbo.tblCapaAcciones.idcausas inner join tblEncabezadoCapaweb ON tblEncabezadoCapaweb.FolioCapa=  TLX009MXDB.dbo.tblCapaAnalisis.idcapa WHERE tblEncabezadoCapaweb.FolioCapa=$fila[0]";
          $resultaccion=sqlsrv_query($conn,$queryaccion);
          while ($filaaccion=sqlsrv_fetch_array($resultaccion)){
            if($filaaccion["accionvalidada"]!=0)$contaccionescomp++;
            $contaccionestotal++;
          }
          if($contaccionescomp==$contaccionestotal && $contaccionestotal>0){
          echo "<tr><td>".$fila[0]."</td>";
          echo "<td>Departamento: ".$fila[2]."</td>";
          echo "<td>Máquina: ".$fila[3]."</td>";
          echo "<td>Sección: ".$fila[4]."</td>";
          echo "<td>Fuente: ".$fila[5]."</td>";
          echo "<td>Tipo de fuente: ".$fila[6]."</td>";
          echo '<td><button type="button" class="btn text-white btn-success btn-sm" onclick="efectividad('.$fila[0].')">Efectividad </button></td>';
        if($suma<=10){
        echo '<td>
          <button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercorrecciones('.$fila[0].')">Ver Correcciones <i class="fas fa-eye"></i></button></td>';
        }else{
        echo '<td>
          <button type="button" class="btn text-white btn-sm" style=" background:#023e8a;" onclick="vercapa('.$fila[0].')">Ver CAPA<i class="fas fa-eye"></i></button></td>';
        }
        echo "</tr>";
       }
     }
       ?>
      </tbody>
    </table>
  </div>

<?php 
}

else if (isset($_GET['tblefectividadcalculos'])) {
require_once "../../../csql.php";
$idcapa=$_POST['foliocapa'];
$query="SELECT * FROM tblCapaefectividad WHERE idcapa=$idcapa";
$resultado=sqlsrv_query($conn,$query);
$porcentaje=0;
$acumulado=0;
$cont=0;
?>
 <div class="row">
  <div class="col-6">
<div class="table-responsive" style="height:100%; overflow: scroll;">
  <table  class="table table-sm table-hover table-striped">  
        <thead class="table-dark">
          <th>CAPA</th>
          <th>Efectividad</th>
          <th>Fecha</th>
        </thead>
        <tbody>
<?php 
while ($row=sqlsrv_fetch_array($resultado)) {
   echo "<tr>";
   echo "<td>$row[1]</td>";
   echo "<td>$row[2]%</td>";
   echo "<td>".$row[3]->format('Y/m/d')."</td>";
   echo "</tr>";
   $acumulado=$acumulado+$row[2];
   $cont++;
}
if ($cont>0) {
$porcentaje=$acumulado/$cont;
}
?>
</tbody>
</table>
</div>
</div>
 <div class="col-6 p-5 text-center">
    <div class="m-0 row justify-content-center align-items-center">
    <h1><?php echo round($porcentaje,2)."%"; ?></h1>
    </div>
  </div>
</div>
<?php 
}
?>


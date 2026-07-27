<?php
require_once "../../../csql6.php";
$id=$_POST['id']; 
$contaccionescomp=0;
$contaccionestotal=0;
$fechamasalta='';
$query="SELECT tblEncabezadoCAPAweb.FolioCapa,tblEncabezadoCAPAweb.Fecha,TLX009MXDB.dbo.tblDepartamentos.NombreDepto,TLX009MXDB.dbo.tblMaquinas.NombreMaquina,TLX009MXDB.dbo.tblSecciones.NombreSeccion,TLX009MXDB.dbo.tblFuentes.DescripcionFuente,TLX009MXDB.dbo.tblTipoFuente.DescripcionTipoFuente,TLX009MXDB.dbo.tblMCM.MCM,tblEncabezadoCAPAweb.DescripcionCAPA,TLX009MXDB.dbo.tblCapaSeveridades.riesgo AS severidad,TLX009MXDB.dbo.tblCapaProbabilidades.riesgo AS probabilidad,TLX009MXDB.dbo.tblCapaDetecciones.riesgo AS deteccion,TLX009MXDB.dbo.tblCapaNoExpuestas.riesgo AS noexpuestas,tblEncabezadoCAPAweb.Noemp AS NoEmp,TLX032MXDB.dbo.tblEmpleados.Nombre AS nombreemp FROM tblEncabezadoCAPAweb INNER JOIN TLX009MXDB.dbo.tblDepartamentos on TLX009MXDB.dbo.tblDepartamentos.NoDepto=tblEncabezadoCAPAweb.NoDepto INNER JOIN TLX009MXDB.dbo.tblSecciones on TLX009MXDB.dbo.tblSecciones.NoSeccion=tblEncabezadoCapaweb.NoSeccion INNER JOIN TLX009MXDB.dbo.tblMaquinas on TLX009MXDB.dbo.tblMaquinas.NoMaquina=tblEncabezadoCapaweb.NoMaquina INNER JOIN TLX009MXDB.dbo.tblFuentes ON TLX009MXDB.dbo.tblFuentes.IdFuente = tblEncabezadoCapaweb.IdFuente INNER JOIN TLX009MXDB.dbo.tblTipoFuente ON TLX009MXDB.dbo.tblTipoFuente.IdTipoFuente=tblEncabezadoCapaweb.IdTipoFuente  INNER JOIN TLX009MXDB.dbo.tblMCM ON TLX009MXDB.dbo.tblMCM.IdMCM= tblEncabezadoCapaweb.IdMCM INNER JOIN TLX009MXDB.dbo.tblCapaSeveridades ON TLX009MXDB.dbo.tblCapaSeveridades.id=tblEncabezadoCapaweb.Severidad INNER JOIN TLX009MXDB.dbo.tblCapaProbabilidades ON TLX009MXDB.dbo.tblCapaProbabilidades.id=tblEncabezadoCapaweb.Probabilidad INNER JOIN TLX009MXDB.dbo.tblCapaDetecciones ON TLX009MXDB.dbo.tblCapaDetecciones.id=tblEncabezadoCapaweb.Deteccion INNER JOIN TLX009MXDB.dbo.tblCapaNoExpuestas ON TLX009MXDB.dbo.tblCapaNoExpuestas.id=tblEncabezadoCapaweb.Noexpuestas INNER JOIN TLX032MXDB.dbo.tblEmpleados ON TLX032MXDB.dbo.tblEmpleados.NoEmp=tblEncabezadoCapaweb.NoEmp WHERE tblEncabezadoCAPAweb.FolioCapa = '".$id."' ORDER BY tblEncabezadoCAPAweb.FolioCapa DESC";
		$result=sqlsrv_query($conn,$query);
 ?>
 <div class="row justify-content-end">
 <div class="col-6">
 <h4>Resumen CAPA (Gráficas)</h4>
 </div>
 <div class="col-1 ">
 <button type="button" class="btn btn-secondary btn-sm ml-auto" style="float: right; position: fixed;" data-bs-dismiss="modal"><i class="fa-solid fa-arrow-right-from-bracket"></i> Salir</button>
  </div>
 </div>
 <hr>
 <div class="row">
 	<div class="col-4">
            <?php
      while ($fila=sqlsrv_fetch_array($result)) {
          echo " <h5 >Folio:".$fila[0]."</h5>";
          echo "<span>Cargada por: ".$fila['NoEmp']." - ".$fila['nombreemp']."</span><br>";
          echo "<span>Departamento: ".$fila[2]."</span><br>";
          echo "<span>Máquina: ".$fila[3]."</span><br>";
          echo "<span>Sección: ".$fila[4]."</span><br>";
          echo "<span>Fuente: ".$fila[5]."</span><br>";
          echo "<span>Tipo de fuente: ".$fila[6]."</span><br>";
          echo "<span>Responsabilidad: ".$fila[7]."</span><br>";
          echo "<span >Descipcion CAPA: ".$fila[8]."</span><br>";
          if($fila[7]=='Seguridad'){
          	 $suma=$fila[9]*$fila[10]*$fila[11]*$fila[12];
              if($suma<=5){
              echo '<p class="text-success">Riesgo aceptable.</p>';
              }else if($suma<=50){
              echo '<p class="text-warning">Riesgo bajo.</p>';
              }else if($suma<=500){
              echo '<p class="text-danger">Riesgo alto.</p>';
              }else if($suma>500){
              echo '<p class="text-danger">Riesgo inaceptable.</p>';
              }
          }else if($fila[7]=='Calidad'){
          echo "<p class='text-danger'>La desviación representa un riesgo crítico, por lo que es mandatorio ejecutar todas las etapas del proceso CAPA</p>";
        }
      	}
      	?>
      </div>
      <div class="col-4">
    <h6>Efectividad</h6>
<?php 
require_once "../../../csql.php";
$query="SELECT * FROM tblCapaefectividad WHERE idcapa=$id";
$resultado=sqlsrv_query($conn,$query);
$porcentaje=0;
$acumulado=0;
$cont=0;
while ($row=sqlsrv_fetch_array($resultado)) {
   $acumulado=$acumulado+$row[2];
   $cont++;
}
if ($cont>0) {
$porcentaje=$acumulado/$cont;
}
?>
<div class="col-6 p-5 text-center">
    <div class="m-0 row justify-content-center align-items-center">
    <h1><?php echo round($porcentaje,2)."%"; ?></h1>
    </div>
</div>
</div>
<div class="col-4">
    <h6>Eficacia</h6>
<?php 
require_once '../../../csql.php';
$id=$_POST['id'];
$dias=0;
$porcentaje=0;
$query="SELECT tblCapaEficacia.* FROM tblCapaEficacia INNER JOIN tblCapaAcciones ON tblCapaAcciones.id=tblCapaEficacia.id_actividad INNER JOIN tblCapaAnalisis ON tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON tblEncabezadoCapaweb.FolioCapa= tblCapaAnalisis.idcapa WHERE tblEncabezadoCapaweb.FolioCapa=$id";
$resultado = sqlsrv_query($conn,$query);
    while ($row=sqlsrv_fetch_array($resultado)) {
      $dias++;
      if($row[2]==1){
       $porcentaje++;
      }
      }
      if($dias>0)
      $porcentaje=($porcentaje/$dias)*100;
       ?>
<div class="col-6 p-5 text-center">
    <div class="m-0 row justify-content-center align-items-center">
    <h1><?php echo round($porcentaje,2)."%"; ?></h1>
    </div>
</div>
</div>
</div>
<a href="pdf/crearpdf.php?id=<?php echo $id; ?>" target="_blank" class="btn bg-target">Generar PDF</a>
  <div class="row">
    <div class="col-4" >
    <canvas id="acpt"></canvas>
  </div>

  <div class="col-4" >
    <canvas id="acpv"></canvas>
  </div>
    <div class="col-4">
    <canvas id="elementos"></canvas>
  </div>
</div>
<?php 
 include '../../../csql.php';
  require_once("../../Session/seguridad.php"); 
  $query = "SELECT COUNT(*),(SELECT COUNT(*)  FROM tblCapaAcciones INNER JOIN tblCapaAnalisis on tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa = tblCapaAnalisis.idcapa WHERE (TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=$id AND accioncompleta=1)) FROM tblCapaAcciones INNER JOIN tblCapaAnalisis on tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa = tblCapaAnalisis.idcapa WHERE TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=$id";
  $result = sqlsrv_query($conn, $query);
?>
<script type="text/javascript">
new Chart(document.getElementById("acpt"), {
    type: 'pie',
    data: {
      labels: ["Total Acciones", " Acciones terminadas"],
      datasets: [{
        label: "",
        backgroundColor: ["#337ca0", "#8e5ea2"],
        data: [
         <?php 
          while ($row = sqlsrv_fetch_array($result)) {
            echo $row[0].",";
            echo $row[1]."";
          }
         ?>
        ]
      }]
    },
    options: {
      responsive:true,
      title: {
        display: true,
        text: 'Acciones correctivas y preventivas terminadas'
      }
    }
});
</script>
<?php 
$query = "SELECT COUNT(*),(SELECT COUNT(*)  FROM tblCapaAcciones INNER JOIN tblCapaAnalisis on tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa = tblCapaAnalisis.idcapa WHERE (TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=$id AND accionvalidada=1)) FROM tblCapaAcciones INNER JOIN tblCapaAnalisis on tblCapaAnalisis.id=tblCapaAcciones.idcausas INNER JOIN TLX006MXDB.dbo.tblEncabezadoCapaweb ON TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa = tblCapaAnalisis.idcapa WHERE TLX006MXDB.dbo.tblEncabezadoCapaweb.FolioCapa=$id";
  $result = sqlsrv_query($conn, $query);
   ?>
<script type="text/javascript">
new Chart(document.getElementById("acpv"), {
    type: 'pie',
    data: {
      labels: ["Total Acciones", " Acciones validadas"],
      datasets: [{
        label: "",
        backgroundColor: ["#337ca0", "#8e5ea2"],
        data: [
         <?php 
          while ($row = sqlsrv_fetch_array($result)) {
            echo $row[0].",";
            echo $row[1]."";
          }
         ?>
        ]
      }]
    },
    options: {
      responsive:true,
      title: {
        display: true,
        text: 'Acciones correctivas y preventivas validadas'
      }
    }
});
</script>

    <script>
        $(document).ready(function () {
            showGraph();
        });
        function showGraph()
        {
            {
                $.post("php/datosgrafcanvas.php?id=<?php echo $id; ?>",
                function (data)
                {
                    console.log(data);
                     var elemento = [];
                    var cont = [];

                    for (var i in data) {
                        elemento.push(data[i].elementos);
                        cont.push(data[i].cont);
                    }
                    var chartdata = {
                        labels: elemento,
                        datasets: [
                            {
                                label: 'Elementos',
                                backgroundColor: '#337ca0',
                                borderColor: '#337ca0',
                                hoverBackgroundColor: '#CCCCCC',
                                hoverBorderColor: '#666666',
                                data: cont
                            }

                        ]
                    };
                    var graphTarget = $("#elementos");
                    var barGraph = new Chart(graphTarget, {
                        type: 'bar',
                        data: chartdata,
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                });

            }
        }
        </script>



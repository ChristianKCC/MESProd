<canvas id="myChart"></canvas>
<?php 
 include '../../../csql35.php';
  require_once("../../Session/seguridad.php"); 
  $query = "SELECT COUNT(*),(SELECT COUNT(*) FROM TLX009MXDB.dbo.tblCapaAcciones WHERE responsable=".$_SESSION['ibm']."),(SELECT COUNT(*) FROM TLX009MXDB.dbo.tblCapaAccionesMenor WHERE responsable=".$_SESSION['ibm'].") FROM tblSubEncabCapturaCapacitacion where (NoEmp=".$_SESSION['ibm'].")";
  $result = sqlsrv_query($conn, $query);
?>
<script type="text/javascript">
const ctx = document.getElementById('myChart');
const myChart = new Chart(ctx, {
    type: 'radar',
    data: {
        labels: [ 'SEGURIDAD','Orden y limpieza','Calidad','Manejo de activos','Materia Prima','Costos','RI','Producción','Servicios'],
        datasets: [{
            label: 'Responsabilidad',
            data: [10,10,10,10,10,10,10,10,10],
            backgroundColor: 'rgba(162, 248, 220, 0.5)',
            borderColor: [
                'rgba(0, 166, 112, 0.9)',
            ],
            borderWidth: 1
        }]
    }
});
</script>
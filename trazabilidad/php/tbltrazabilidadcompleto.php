<div class="table-responsive" style="height: 600px;">
<table class="table table-striped table-sm">
  <thead class="table-dark">
    <th>ID</th>
    <th>Maquina</th>
    <th>Clave</th>
    <th>Modulo</th>
    <th>Material</th>
    <th>Empleado</th>
    <th>Lote</th>
    <th>Folio</th>
    <th>Hora</th>
    <th>Turno</th>
    <th>Fecha</th>
  </thead>
  <tbody>
    <?php 
    require_once "../../conexion.php";
    $conexion = new ClassConexion();
    $conn= $conexion->conexion('TLX002MXDB');
    $fechaswhere='';
    $addwhere='';
    $whereand='WHERE';
    if (!empty($_POST['fechai']) || !empty($_POST['fechaf'])) {
    $fechai=$_POST['fechai'];
    $fechaf=$_POST['fechaf'];
    $fechaswhere="WHERE tblTrazabilidadEnc.fecha >= '$fechai' AND tblTrazabilidadEnc.fecha < DATEADD(day,1,'$fechaf')";
    $whereand='AND';
    } 
    if (!empty($_POST['maquina'])){
     $addwhere=$whereand." tblabreturno.maquina = '".$_POST['maquina']."'";
    }
    $query="SELECT TOP 500 tblTrazabilidadEnc.id,tblTrazabilidadClaves.Nombre as clave,tblTrazabilidadModulos.Nombre as modulos, tblTrazabilidadMateriales.Nombre as material,
    tblEmpleados.NoEmp,tblEmpleados.Nombre, tblTrazabilidadEnc.numlote,tblTrazabilidadEnc.folio,tblTrazabilidadEnc.hora,
    tblabreturno.turno, tblTrazabilidadEnc.fecha, tblMaquinas.NombreMaquina as maquina FROM tblTrazabilidadEnc
    INNER JOIN tblTrazabilidadClaves ON tblTrazabilidadClaves.id = tblTrazabilidadEnc.clave
    INNER JOIN tblTrazabilidadModulos ON tblTrazabilidadModulos.id = tblTrazabilidadEnc.modulo
    INNER JOIN tblTrazabilidadMateriales ON tblTrazabilidadMateriales.id = tblTrazabilidadEnc.material
    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblTrazabilidadEnc.empleado
    INNER JOIN tblabreturno ON tblabreturno.id = tblTrazabilidadEnc.idbitacora
    INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblabreturno.maquina $fechaswhere $addwhere ORDER BY tblTrazabilidadEnc.id DESC";
    $result=sqlsrv_query($conn,$query);
    while ($row=sqlsrv_fetch_array($result)) {
      echo "<tr>";
      echo "<td>".$row[0]."</td>";
      echo "<td>".$row[11]."</td>";
      echo "<td>".$row[1]."</td>";
      echo "<td>".$row[2]."</td>";
      echo "<td>".$row[3]."</td>";
      echo "<td>".$row[4]. " / ".$row[5]."</td>";
      echo "<td>".$row[6]."</td>";
      echo "<td>".$row[7]."</td>";
      echo "<td>".$row[8]->format('H:i:s')."</td>";
      echo "<td>".$row[9]."</td>";
      echo "<td>".$row[10]->format('Y-m-d')."</td>";
      echo "</tr>";
    }
     ?>
  </tbody>
</table>
</div>

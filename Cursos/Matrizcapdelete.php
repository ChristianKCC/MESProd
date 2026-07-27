<div class="loaderheader" id="loader">
  <span class="loader"></span>
</div>
<?php
require_once "../index/header.php";
require_once "../conexion.php";
?>
<!-- Contenido -->
<div class="container rounded shadow indexahref">
  <div class="row justify-content-center align-items-center " id="matriz" style="margin-top:5%;">
    <div class="col">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm">
          <thead>
            <th style="background: #fff;">
              <h4 align="center">Matriz de capacitación 2024</h4>
              <img src="../img/imglogoprosede.png" style=" display: block; margin-left: auto; margin-right: auto; width: 80%;">
            </th>
            <?php
            $Conection = new ClassConexion();
            $conn = $Conection->conexion("TLX035MXDB");
            $query = "SELECT * FROM TLX009MXDB.dbo.tblDepartamentos WHERE Filtro=1 AND NoDepto <> 56";
            $stpm = sqlsrv_query($conn, $query);
            while ($row = sqlsrv_fetch_array($stpm))
              echo "<th style='writing-mode: vertical-lr; transform: rotate(180deg);'>" . $row[1] . "</th>";
            echo "<th style='writing-mode: vertical-lr; transform: rotate(180deg); background: #1F3966; color: #fff;'>Total por curso</th>";
            ?>
          </thead>
          <tbody>
            <?php

            error_reporting(E_ERROR);
            $query = "SELECT * FROM tblCursos WHERE clasificacion=1";
            $stpm = sqlsrv_query($conn, $query);
            $resf = 0;
            while ($x = sqlsrv_fetch_array($stpm)) {
              echo "<tr><td heigth='50px' width='300px'>" . $x[1] . "</td>";
              $res2 = 0;
              $cont = 12;
              $totales[] = 0;
              $resrealf = 0;
              $query2 = "SELECT * FROM TLX009MXDB.dbo.tblDepartamentos WHERE Filtro=1 AND NoDepto <> 56";
              $stpm2 = sqlsrv_query($conn, $query2);
              while ($row = sqlsrv_fetch_array($stpm2)) {
                $query3 = "SELECT count(distinct tblEmpleados.NoEmp) as logincount,(SELECT count(distinct tblEmpleados.NoEmp) FROM TLX032MXDB.dbo.tblEmpleados 
             WHERE (EXISTS (SELECT TLX032MXDB.dbo.tblEmpleados.NoEmp FROM tblSubCursosXPuesto WHERE  tblSubCursosXPuesto.IdPuesto=tblEmpleados.Puesto AND 
             tblSubCursosXPuesto.IdCurso=$x[0]) AND tblEmpleados.bajas=0 AND tblEmpleados.NombreDepartamento=$row[0])) FROM tblSubEncabCapturaCapacitacion 
             INNER JOIN tblEncabezadoCapturaCapacitacion ON tblEncabezadoCapturaCapacitacion.IdEncabezadoCaptura = tblSubEncabCapturaCapacitacion.IdEncabezadoCaptura 
             INNER JOIN tblCursos ON tblCursos.IdCurso = tblEncabezadoCapturaCapacitacion.IdCurso INNER JOIN TLX032MXDB.dbo.tblEmpleados ON 
             tblEmpleados.NoEmp=tblSubEncabCapturaCapacitacion.NoEmp WHERE (EXISTS (SELECT TLX032MXDB.dbo.tblEmpleados.NoEmp FROM tblSubCursosXPuesto WHERE  
             tblSubCursosXPuesto.IdPuesto=tblEmpleados.Puesto AND tblSubCursosXPuesto.IdCurso=$x[0]) AND FORMAT(tblEncabezadoCapturaCapacitacion.FechaFinal,'yyyy') = '2024' AND 
             tblEmpleados.NombreDepartamento=$row[0] AND tblCursos.IdCurso=$x[0] AND tblEmpleados.bajas=0)";
                $stpm3 = sqlsrv_query($conn, $query3);
                while ($row2 = sqlsrv_fetch_array($stpm3)) {
                  if ($row2[1] == 0) $row2[1] = 1;
                  $res = ($row2[0] / $row2[1]) * 100;
                  $class = '';
                  if (($x[0] == 999 and ($row[0] == 8 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9 or $row[0] == 42 or $row[0] == 34)) or
                    ($x[0] == 995 and ($row[0] == 7 or $row[0] == 4 or $row[0] == 24 or $row[0] == 25 or $row[0] == 1 or $row[0] == 33 or $row[0] == 9 or $row[0] == 42 or $row[0] == 2 or $row[0] == 61 or $row[0] == 34)) or ($x == 996 and ($row[0] == 8 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9)) or
                    ($x[0] == 997 and ($row[0] == 61)) or
                    ($x[0] == 998 and ($row[0] == 8 or $row[0] == 7 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9)) or
                    ($x[0] == 1000 and ($row[0] == 61)) or
                    ($x[0] == 1005 and ($row[0] == 8 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9 or $row[0] == 42 or $row[0] == 34)) or
                    ($x[0] == 1006 and ($row[0] == 8 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9 or $row[0] == 42 or $row[0] == 34)) or
                    ($x[0] == 996 and ($row[0] == 8 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9 or $row[0] == 7 or $row[0] == 34 or $row[0] == 42)) or
                    ($x[0] == 1007 and ($row[0] == 8 or $row[0] == 61 or $row[0] == 43 or $row[0] == 9 or $row[0] == 42 or $row[0] == 34))
                  ) {
                    $class = 'bg-dark';
                    $cont--;
                  }
                  $mosttop = number_format($res, 1);
                  echo "<td width='20px' class='" . $class . "'>" . $mosttop . "%</td>";
                  $res2 = $res2 + $mosttop;
                  $totales[$row[0]] = $totales[$row[0]] + $res2;
                }
              }
              echo "<td width='20px'>" . number_format(($res2 / $cont), 1) . "%</td>";
              $resf = $resf + ($res2 / $cont);
              echo "</tr>";
            }
            echo "<tr>
                <td style='background: #1F3966; color: #fff;'>Total por departamento</td>
                <td>" . number_format(($totales[1] / 18), 1) . "%</td>
                <td>" . number_format((($totales[2] - $totales[1]) / 18), 1) . "%</td>
                <td>" . number_format((($totales[7] - $totales[2]) / 17), 1) . "%</td>
                <td>" . number_format((($totales[8] - $totales[7]) / 14), 1) . "%</td>
                <td>" . number_format((($totales[9] - $totales[8]) / 13), 1) . "%</td>
                <td>" . number_format((($totales[24] - $totales[9]) / 18), 1) . "%</td>
                <td>" . number_format((($totales[25] - $totales[24]) / 18), 1) . "%</td>
                <td>" . number_format((($totales[33] - $totales[25]) / 18), 1) . "%</td>
                <td>" . number_format((($totales[34] - $totales[33]) / 13), 1) . "%</td>
                <td>" . number_format((($totales[42] - $totales[34]) / 13), 1) . "%</td>
                <td>" . number_format((($totales[43] - $totales[42]) / 14), 1) . "%</td>
                <td>" . number_format((($totales[61] - $totales[43]) / 12), 1) . "%</td>
                <td>" . number_format($resf / 21, 1) . "%</td>";
            echo "</tr>";
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reporte.js" type="text/javascript"></script>
<script type="text/javascript">
  window.addEventListener('load', function() {
    document.getElementById("loader").hidden = true;
  });
</script>
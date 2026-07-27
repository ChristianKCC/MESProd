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
              <h4 align="center">Matriz de capacitación 2026</h4>
              <img src="../img/imglogoprosede.png"
                style=" display: block; margin-left: auto; margin-right: auto; width: 80%;">
            </th>
            <!-- IMPORTANTE -->
            <?php
            $Conection = new ClassConexion();
            $conn = $Conection->conexion("TLX035MXDB");
            $query = "SELECT * FROM TLX009MXDB.dbo.tblDepartamentos WHERE Filtro=1 AND NoDepto <> 56 AND NoDepto <> 33";
            $stpm = sqlsrv_query($conn, $query);
            while ($row = sqlsrv_fetch_array($stpm))
              echo "<th style='writing-mode: vertical-lr; transform: rotate(180deg);'>" . $row[1] . "</th>";
            echo "<th style='writing-mode: vertical-lr; transform: rotate(180deg); background: #1F3966; color: #fff;'>Total por curso</th>";
            ?>
          </thead>
          <tbody>
            <?php

            error_reporting(E_ERROR);
            $query = "SELECT * FROM tblCursos WHERE clasificacion=1 ORDER BY clasificacionCurso ASC";
            $stpm = sqlsrv_query($conn, $query);
            $resf = 0;
            while ($x = sqlsrv_fetch_array($stpm)) {
              echo "<tr><td heigth='50px' width='300px'>" . $x[1] . "</td>";
              $res2 = 0;
              $cont = 12;
              $totales[] = 0;
              $resrealf = 0;
              $query2 = "SELECT * FROM TLX009MXDB.dbo.tblDepartamentos WHERE Filtro=1 AND NoDepto <> 56 AND NoDepto <> 33";
              $stpm2 = sqlsrv_query($conn, $query2);
              while ($row = sqlsrv_fetch_array($stpm2)) {
                $query3 = "SELECT 
                COUNT(DISTINCT e1.NoEmp) AS logincount,
                (SELECT COUNT(DISTINCT e2.NoEmp)
                FROM TLX032MXDB.dbo.tblEmpleados e2
                JOIN tblSubCursosXPuesto scp2 ON scp2.IdPuesto = e2.Puesto
                WHERE scp2.IdCurso = $x[0]
                  AND e2.bajas = 0
                  AND e2.NombreDepartamento = $row[0]) AS totalcount
                FROM TLX032MXDB.dbo.tblEmpleados e1
                JOIN tblSubEncabCapturaCapacitacion secc ON e1.NoEmp = secc.NoEmp
                JOIN tblEncabezadoCapturaCapacitacion ecc ON ecc.IdEncabezadoCaptura = secc.IdEncabezadoCaptura
                JOIN tblCursos c ON c.IdCurso = ecc.IdCurso
                JOIN tblSubCursosXPuesto scp ON scp.IdPuesto = e1.Puesto
                WHERE scp.IdCurso = $x[0]
                AND e1.bajas = 0
                AND e1.NombreDepartamento = $row[0]
                -- Pasar a 2026
                AND FORMAT(ecc.FechaFinal, 'yyyy') = '2026' 
                AND c.IdCurso = $x[0]";

                $stpm3 = sqlsrv_query($conn, $query3);
                while ($row2 = sqlsrv_fetch_array($stpm3)) {
                  if ($row2[1] == 0)
                    $row2[1] = 1;
                  $res = ($row2[0] / $row2[1]) * 100;
                  $class = '';
                  /**
                   * Comprueba si la combinación de identificadores cumple alguna de las reglas permitidas.
                   * 
                   * - Evalúa $x[0] (p. ej. id de curso/programa) frente a varios valores concretos.
                   * - Para cada caso verifica que $row[0] (p. ej. id de asignatura/área) pertenezca a un conjunto
                   *   de valores aceptados para ese $x[0].
                   * - Si alguna de las condiciones se cumple, la expresión entera es verdadera y se ejecuta el
                   *   bloque asociado al if.
                   *
                   * Observaciones:
                   * - Hay condiciones redundantes y repeticiones de valores que podrían simplificarse.
                   * - Existe al menos un posible error tipográfico (uso de $x en vez de $x[0]) que convendría corregir.
                   * - Sería más claro y mantenible reemplazar estas múltiples comprobaciones por estructuras
                   *   de datos (arrays/mapeos) y comprobaciones mediante in_array o búsquedas en conjuntos.
                   */
                  $reglasCursoDepartamento = [
                    997 => [61, 33, 34, 73],
                    998 => [8, 7, 61, 43, 9],
                    1000 => [61],
                    1005 => [8, 61, 43, 9, 42, 34, 73, 81],
                    1006 => [8, 61, 43, 9, 42, 34, 73, 81],
                    1007 => [8, 61, 43, 9, 42, 34, 73, 81],
                    1418 => [7, 8, 61, 43, 9, 42, 34, 33, 73, 81],
                    1421 => [8, 61, 43, 9, 42, 34, 33, 73, 81],
                    1422 => [8, 61, 43, 9, 42, 34, 33, 73, 81],
                    1423 => [8, 61, 43, 9, 42, 34, 33, 73, 81],
                    1556 => [2, 7, 8, 61, 43, 9, 42, 34, 33, 81],
                  ];

                  if (isset($reglasCursoDepartamento[$x[0]]) && in_array($row[0], $reglasCursoDepartamento[$x[0]])) {
                    $class = 'bg-dark';
                    $cont--;
                  }
                  // Llenado de cada celda
                  $mosttop = number_format($res, 1);
                  if ($class === 'bg-dark') {
                    $mosttop = 0;
                  }
                  echo "<td width='20px' class='" . $class . "'>" . $mosttop . "%</td>";
                  $res2 = $res2 + $mosttop;
                  $totales[$row[0]] = $totales[$row[0]] + $res2;
                }
              }
              // Total por curso
              echo "<td width='20px'>" . number_format(($res2 / $cont), 1) . "%</td>";
              $resf = $resf + ($res2 / $cont);
              echo "</tr>";
            }
            echo "<tr>
              <td style='background: #1F3966; color: #fff;'>Total por departamento</td>
              <td>" . number_format(($totales[1] / 23), 1) . "%</td>
              <td>" . number_format((($totales[2] - $totales[1]) / 22), 1) . "%</td>
              <td>" . number_format((($totales[7] - $totales[2]) / 21), 1) . "%</td>
              <td>" . number_format((($totales[8] - $totales[7]) / 15), 1) . "%</td>
              <td>" . number_format((($totales[9] - $totales[8]) / 15), 1) . "%</td>
              <td>" . number_format((($totales[24] - $totales[9]) / 23), 1) . "%</td>
              <td>" . number_format((($totales[25] - $totales[24]) / 23), 1) . "%</td>
              <td>" . number_format((($totales[34] - $totales[25]) / 14), 1) . "%</td>
              <td>" . number_format((($totales[42] - $totales[34]) / 15), 1) . "%</td>
              <td>" . number_format((($totales[43] - $totales[42]) / 15), 1) . "%</td>
              <td>" . number_format((($totales[61] - $totales[43]) / 14), 1) . "%</td>
              <td>" . number_format((($totales[73] - $totales[61]) / 23), 1) . "%</td>
              <td>" . number_format((($totales[81] - $totales[73]) / 23), 1) . "%</td>
              <td>" . number_format($resf / 13, 1) . "%</td>";
            echo "</tr>";
            ?>
          </tbody>
        </table>
      </div>
      <a href='#' onclick="crearexcel('matriz')" class="btn btn-success">Crear Excel</a>
    </div>
  </div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/reporte.js" type="text/javascript"></script>
<script type="text/javascript">
  window.addEventListener('load', function () {
    document.getElementById("loader").hidden = true;
  });
</script>
<script type="module">
  import { Toolsjs } from "../Tools/Tools.js"
  const tools = new Toolsjs();
  window.crearexcel = (event) => tools.exportartablaexcel(event);
</script>
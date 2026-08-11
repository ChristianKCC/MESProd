<?php
require_once (__DIR__ . "../../../conexion.php");

$ibm = $_POST['ibm'] ?? $_GET['ibm'] ?? col($empleado, COL_IBM);

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

$query = "SELECT TOP(3)
            sub.Vcs_de,
            sub.Vcs_hasta,
            sub.Vcs_diasVacSol,
            enc.Vc_autorizado,
            enc.Vc_revisado,
            enc.Vc_firmaRI,
            enc.Vc_id
          FROM tblMXPRVacacionesEnc enc
          INNER JOIN tblMXPRVacacionesSubEnc sub 
            ON sub.Vcs_vc_id = enc.Vc_id
          WHERE enc.Vc_ibm = ?
          ORDER BY enc.Vc_id DESC";

$res = sqlsrv_query($conn, $query, [$ibm]);
if ($res && sqlsrv_has_rows($res)) {
    echo '<div class="mt-3">';
    echo '<p><strong>Solicitudes recientes de vacaciones:</strong></p>';
    echo '<table class="table table-sm table-bordered">';
    echo '<thead class="table-dark">
            <tr>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Días</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
          </thead><tbody>';

    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $estado = '<span class="badge bg-warning text-dark">En espera</span>';
        if ($row['Vc_autorizado'] == 1 && $row["Vc_revisado"] == 1 && $row["Vc_firmaRI"] == 1) {
            $estado = '<span class="badge bg-success">Autorizado</span>';
        } elseif ($row['Vc_autorizado'] == 2) {
            $estado = '<span class="badge bg-danger">Rechazado</span>';
        }        
        
        $accionesHTML = "";
        
        if ($row['Vc_revisado'] == 1 || $row['Vc_autorizado'] == 2){
            $accionesHTML = 
                    "<button class='btn btn-primary btn-sm' onclick='verPDF({$row['Vc_id']})'>
                        <i class='fa-solid fa-file-pdf'></i> Revisar info. en PDF
                    </button>";
        } else {
            $accionesHTML = 
                    "<button class='btn btn-primary btn-sm' onclick='verPDF({$row['Vc_id']})'>
                        <i class='fa-solid fa-file-pdf'></i> Revisar info. en PDF
                    </button>

                    <button class='btn btn-danger btn-sm' onclick='eliminarRegistro({$row['Vc_id']})'>
                        <i class='fa-solid fa-trash-can'></i> Eliminar
                    </button>";
        }

        echo "<tr>
                <td>{$row['Vcs_de']->format('Y-m-d')}</td>
                <td>{$row['Vcs_hasta']->format('Y-m-d')}</td>
                <td>{$row['Vcs_diasVacSol']}</td>
                <td>{$estado}</td>
                <td>{$accionesHTML}</td>
              </tr>";
    }

    echo '</tbody></table></div>';
}
?>
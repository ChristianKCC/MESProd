<?php
require_once "../../Session/seguridad.php";
require_once "../../conexion.php";

class Cuestionario
{
    function dataUserSesion()
    {
        $Conection = new ClassConexion();
        $usr = $_SESSION['ibm'];
        if (!isset($_SESSION["autentica"]) || $_SESSION["autentica"] != "SIP") {
            echo json_encode(["status" => false]);
        } else {
            $conn = $Conection->conexion("TLX032MXDB");
            $query = "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblEmpleados.NombreDepartamento as departamento, 
                    tblEmpleados.puesto as puesto, tblEmpleados.EmpleadoSindicalizado as sindicalizado FROM tblEmpleados WHERE NoEmp=$usr";
            $result = sqlsrv_query($conn, $query);
            $array = array();
            while ($row = sqlsrv_fetch_array($result)) {
                array_push($array, [
                    "noemp" => $row['NoEmp'],
                    "nombre" => $row['Nombre'],
                    "departamento" => $row['departamento'],
                    "puesto" => $row['puesto'],
                    "sindicalizado" => $row['sindicalizado'],
                    'status' => true
                ]);
            }
            sqlsrv_close($conn);
            echo json_encode($array);
        }
    }


    function consultarCuestionario()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

        $noEmp = $_POST['noemp'] ?? null;

        if ($noEmp === null) {
            echo json_encode(["error" => "No se recibió noemp"]);
            exit;
        }

        $query = "SELECT * FROM tblCuestionarioHerramientaAcoso WHERE noemp = ?";
        $params = [$noEmp];

        $stpm = sqlsrv_query($conn, $query, $params);

        if (!$stpm || !sqlsrv_has_rows($stpm)) {
            echo json_encode([]);
            exit;
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stpm, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }

        echo json_encode($data);
        exit;
    }

    function resultadosCuestionario()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

        $query = "SELECT tblCHA.id, neap, igap, imap, respuestas, estadoCuestionario, tblCHA.noemp, tblEMP.Nombre, tblDEP.NombreDepto, tblPU.nombre AS NombrePuesto, fecha 
                    FROM tblCuestionarioHerramientaAcoso tblCHA
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados tblEMP ON tblEMP.NoEmp = tblCHA.noemp
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos tblDEP ON tblDEP.NoDepto = tblEMP.NombreDepartamento
                    INNER JOIN TLX009MXDB.dbo.tblPuestos tblPU ON tblPU.id = tblEMP.Puesto";
        $stpm = sqlsrv_query($conn, $query);

        if (!$stpm || !sqlsrv_has_rows($stpm)) {
            echo json_encode([]);
            exit;
        }

        $data = [];
        while ($row = sqlsrv_fetch_array($stpm, SQLSRV_FETCH_ASSOC)) {
            $data[] = [
                'id' => $row['id'],
                'neap' => $row['neap'],
                'igap' => $row['igap'],
                'imap' => $row['imap'],
                'respuestas' => $row['respuestas'],
                'estadoCuestionario' => $row['estadoCuestionario'],
                'noemp' => $row['noemp'],
                'fecha' => $row['fecha']->format('Y-m-d'),
                'nombre' => $row['Nombre'],
                'departamento' => $row['NombreDepto'],
                'puesto' => $row['NombrePuesto'],
            ];
        }

        echo json_encode($data);
        exit;
    }

    function guardarCuestionario()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

        $sum = $_POST['sum'];
        $neap = $_POST['NEAP'];
        $igap = $_POST['IGAP'];
        $imap = $_POST['IMAP'];
        $responses = $_POST['responses'];
        $estadoCuestionario = 1; // Asumiendo que 1 significa completado 
        $noEmp = $_SESSION['ibm'];

        $query = "INSERT INTO tblCuestionarioHerramientaAcoso (sum, neap, igap, imap, respuestas, estadoCuestionario, noEmp) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [$sum, $neap, $igap, $imap, $responses, $estadoCuestionario, $noEmp];

        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }
}

if (isset($_GET['consultarCuestionario'])) {
    $cuestionario = new Cuestionario();
    $cuestionario->consultarCuestionario();
} else if (isset($_GET['guardarCuestionario'])) {
    $cuestionario = new Cuestionario();
    $cuestionario->guardarCuestionario();
} else if (isset($_GET['dataUserSesion'])) {
    $cuestionario = new Cuestionario();
    $cuestionario->dataUserSesion();
} else if (isset($_GET['resultadosCuestionario'])) {
    $cuestionario = new Cuestionario();
    $cuestionario->resultadosCuestionario();
}
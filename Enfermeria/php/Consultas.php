<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
class EnfermeriaConsulta
{
    function dataUserCompleate()
    {
        $noemp = $_POST["noemp"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX032MXDB");
        $query = "SELECT tblEmpleados.NoEmp,
                            tblEmpleados.Nombre,
                            tblEmpleados.NombreDepartamento as departamento, 
                            tblEmpleados.puesto as puesto, 
                            tblEmpleados.EmpleadoSindicalizado as sindicalizado 
                            -- ,SUM(tblEI.dias) AS DiasAcumulados
                            FROM tblEmpleados 
                            -- LEFT JOIN TLX002MXDB.dbo.tblEnfermeriaIncapacidades tblEI ON tblEI.noemp = tblEmpleados.NoEmp
                            WHERE tblEmpleados.NoEmp=$noemp
                            GROUP BY 
                                tblEmpleados.NoEmp,
                                tblEmpleados.Nombre,
                                tblEmpleados.NombreDepartamento,
                                tblEmpleados.puesto,
                                tblEmpleados.EmpleadoSindicalizado";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            // array_push($array, ["noemp" => $row[0], "nombre" => $row[1], "departamento" => $row[2], "puesto" => $row[3], "sindicalizado" => $row[4], "DiasAcumulados" => $row[5]]);
            array_push($array, ["noemp" => $row[0], "nombre" => $row[1], "departamento" => $row[2], "puesto" => $row[3], "sindicalizado" => $row[4]]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function saveConsulta()
    {
        $noemp = $_POST["noemp"];
        $departamento = $_POST["departamento"];
        $puesto = $_POST["puesto"];
        $maquina = $_POST["maquina"];
        $edad = $_POST["edad"];
        $antiguedad = $_POST["antiguedad"];
        $tratamiento = $_POST["tratamiento"];
        $observacion = $_POST["observacion"];
        $tipoaparato = $_POST["tipoaparato"];
        $tipoenfermedad = $_POST["tipoenfermedad"];
        $tipoconsulta = $_POST["tipoconsulta"];
        $sexo = $_POST["sexo"];
        $rolturno = $_POST["rolturno"];
        $temperatura = $_POST["temperatura"];
        $frecuencia = $_POST["frecuencia"];
        $pasistolica = $_POST["pasistolica"];
        $padistolica = $_POST["padistolica"];
        $nombreexterno = $_POST["nombreexterno"];
        $empresaexterna = $_POST["empresaexterna"];
        $fecharevision = $_POST['fecharevision'];
        $horaRevision = $_POST['horaRevision'];

        $firma = $_POST['firma'];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $array = array(
            $noemp,
            $departamento,
            $puesto,
            $maquina,
            $edad,
            $antiguedad,
            $tratamiento,
            $observacion,
            $tipoaparato,
            $tipoenfermedad,
            $tipoconsulta,
            $sexo,
            $rolturno,
            $temperatura,
            $frecuencia,
            $pasistolica,
            $padistolica,
            $nombreexterno,
            $empresaexterna,
            $_SESSION['ibm'],
            $firma,
            $fecharevision,
            $horaRevision

        );
        $query = "INSERT INTO tblEnfermeriaConsultas(noemp,departamento,puesto,maquina,edad,antiguedad,tratamiento,observacion,
        tipoaparato,tipoenfermedad,tipoconsulta,sexo,rolturno,temperatura,frecuencia,pasistolica,padistolica,nombreexterno,empresaexterna,idsession,firma,fecharevision,horarevision) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }
    function updateConsulta()
    {
        $folio = $_POST["folio"];
        $noemp = $_POST["noemp"];
        $departamento = $_POST["departamento"];
        $puesto = $_POST["puesto"];
        $maquina = $_POST["maquina"];
        $edad = $_POST["edad"];
        $antiguedad = $_POST["antiguedad"];
        $tratamiento = $_POST["tratamiento"];
        $observacion = $_POST["observacion"];
        $tipoaparato = $_POST["tipoaparato"];
        $tipoenfermedad = $_POST["tipoenfermedad"];
        $tipoconsulta = $_POST["tipoconsulta"];
        $sexo = $_POST["sexo"];
        $rolturno = $_POST["rolturno"];
        $temperatura = $_POST["temperatura"];
        $frecuencia = $_POST["frecuencia"];
        $pasistolica = $_POST["pasistolica"];
        $padistolica = $_POST["padistolica"];
        $nombreexterno = $_POST["nombreexterno"];
        $empresaexterna = $_POST["empresaexterna"];
        $firmaBase64 = $_POST['firma'];
        $fecharevision = $_POST['fecharevision'];
        $horaRevision = $_POST['horaRevision'];
        $firmaBase64 = str_replace('data:image/png;base64,', '', $firmaBase64);
        $firmaBase64 = str_replace(' ', '+', $firmaBase64);
        $firmaBinaria = base64_decode($firmaBase64);
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $array = array(
            $noemp,
            $departamento,
            $puesto,
            $maquina,
            $edad,
            $antiguedad,
            $tratamiento,
            $observacion,
            $tipoaparato,
            $tipoenfermedad,
            $tipoconsulta,
            $sexo,
            $rolturno,
            $temperatura,
            $frecuencia,
            $pasistolica,
            $padistolica,
            $nombreexterno,
            $empresaexterna,
            $_SESSION['ibm'],
            $firmaBase64,
            $fecharevision,
            $horaRevision,
            $folio
        );
        $query = "UPDATE tblEnfermeriaConsultas SET noemp=?,departamento=?,puesto=?,maquina=?,edad=?,antiguedad=?,tratamiento=?,observacion=?,
        tipoaparato=?,tipoenfermedad=?,tipoconsulta=?,sexo=?,rolturno=?,temperatura=?,frecuencia=?,pasistolica=?,padistolica=?,nombreexterno=?,
        empresaexterna=?,idsession=?,firma=?,fecharevision=?,horarevision=? WHERE id=?";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }
    function tblConsultas()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT TOP 99 tblEnfermeriaConsultas.id,tblEmpleados.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblPuestos.nombre as puesto,tblMaquinas.NombreMaquina as maquina,edad,antiguedad,
		    tratamiento,observacion,tblEnfermeriaEquipos.equipomedico,tblEnfermeriaEnfermedades.enfermedad,tblEnfermeriaTipoConsult.tipoconsulta,fecharevision,firma,horarevision
			 FROM tblEnfermeriaConsultas
			  INNER JOIN tblEnfermeriaEquipos ON tblEnfermeriaEquipos.id = tblEnfermeriaConsultas.tipoaparato
			  INNER JOIN tblEnfermeriaEnfermedades ON tblEnfermeriaEnfermedades.id = tblEnfermeriaConsultas.tipoenfermedad
			  INNER JOIN tblEnfermeriaTipoConsult ON tblEnfermeriaTipoConsult.id = tblEnfermeriaConsultas.tipoconsulta
			  INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEnfermeriaConsultas.departamento
			  INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEnfermeriaConsultas.puesto
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblEnfermeriaConsultas.noemp
			  INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblEnfermeriaConsultas.maquina ORDER BY tblEnfermeriaConsultas.id DESC ";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'],
                "noemp" => $row['noemp'],
                "Nombre" => $row['Nombre'],
                "departamento" => $row['NombreDepto'],
                "puesto" => $row['puesto'],
                "maquina" => $row['maquina'],
                "edad" => $row['edad'],
                "antiguedad" => $row['antiguedad'],
                "tratamiento" => $row['tratamiento'],
                "observacion" => $row['observacion'],
                "equipomedico" => $row['equipomedico'],
                "enfermedad" => $row['enfermedad'],
                "tipoconsulta" => $row['tipoconsulta'],
                "firma" => $row['firma'],
                "fecharevision" => $row['fecharevision']->format('Y-m-d'),
                "horarevision" => $row['horarevision'] ? $row['horarevision']->format('H:i') : null
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
    // function dataforeditConsulta()
    // {
    //     $Conecta = new ClassConexion();
    //     $conn = $Conecta->conexion("TLX002MXDB");
    //     $id = $_GET['id'];
    //     $query = "SELECT tblEnfermeriaConsultas.*,tblEmpleados.Nombre FROM tblEnfermeriaConsultas 
    //     INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=tblEnfermeriaConsultas.noemp WHERE id=$id";
    //     $result = sqlsrv_query($conn, $query);
    //     $array = array();
    //     while ($row = sqlsrv_fetch_array($result)) {
    //         $row[12] = $row[12]->format('Y-m-d');
    //         $row[23] = $row[23] ? $row[23]->format('H:i') : null;
    //         $array[] = $row;
    //     }
    //     sqlsrv_close($conn);
    //     echo json_encode($array);
    // }

     function dataforeditConsulta()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET['id'];

        $query = "SELECT c.*,
                        CASE 
                            WHEN c.nombreexterno IS NOT NULL AND c.nombreexterno <> '' 
                            THEN c.nombreexterno 
                            ELSE e.Nombre 
                        END AS Nombre
                FROM tblEnfermeriaConsultas c
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados e 
                        ON e.NoEmp = c.noemp
                WHERE c.id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            // Ajusta índices según tu estructura
            if ($row['fecharevision'] instanceof DateTime) {
                $row['fecharevision'] = $row['fecharevision']->format('Y-m-d');
            }
            if ($row['horarevision'] instanceof DateTime) {
                $row['horarevision'] = $row['horarevision']->format('H:i');
            }
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }


    function reporteConsultasdata()
    {
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $aparato = $_POST["aparato"];
        $enfermedad = $_POST["enfermedad"];
        $departamento = $_POST["departamento"];
        $maquina = $_POST["maquina"];
        $noemp = $_POST["noemp"];
        $addwhere = "";
        empty($_POST["aparato"]) ? $addwhere .= "" : $addwhere .= " AND tblEnfermeriaEquipos.id = $aparato";
        empty($_POST["enfermedad"]) ? $addwhere .= "" : $addwhere .= " AND tblEnfermeriaEnfermedades.id = $enfermedad";
        empty($_POST["noemp"]) ? $addwhere .= "" : $addwhere .= " AND tblEmpleados.noemp = $noemp";
        empty($_POST["departamento"]) ? $addwhere .= "" : $addwhere .= " AND tblDepartamentos.NoDepto = $departamento";
        empty($_POST["maquina"]) ? $addwhere .= "" : $addwhere .= " AND tblMaquinas.NoMaquina = $maquina";
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT tblEnfermeriaConsultas.id,tblEmpleados.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblPuestos.nombre as puesto,tblMaquinas.NombreMaquina as maquina,edad,antiguedad,
		    tratamiento,observacion,tblEnfermeriaEquipos.equipomedico,tblEnfermeriaEnfermedades.enfermedad,tblEnfermeriaTipoConsult.tipoconsulta,fecharevision,firma
			 FROM tblEnfermeriaConsultas
			  INNER JOIN tblEnfermeriaEquipos ON tblEnfermeriaEquipos.id = tblEnfermeriaConsultas.tipoaparato
			  INNER JOIN tblEnfermeriaEnfermedades ON tblEnfermeriaEnfermedades.id = tblEnfermeriaConsultas.tipoenfermedad
			  INNER JOIN tblEnfermeriaTipoConsult ON tblEnfermeriaTipoConsult.id = tblEnfermeriaConsultas.tipoconsulta
			  INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEnfermeriaConsultas.departamento
			  INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEnfermeriaConsultas.puesto
			  INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblEnfermeriaConsultas.noemp
              INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblEnfermeriaConsultas.maquina WHERE fecharevision BETWEEN '$fechai' AND DATEADD(DAY,1, '$fechaf') $addwhere";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'],
                "noemp" => $row['noemp'],
                "Nombre" => $row['Nombre'],
                "departamento" => $row['NombreDepto'],
                "puesto" => $row['puesto'],
                "maquina" => $row['maquina'],
                "edad" => $row['edad'],
                "antiguedad" => $row['antiguedad'],
                "tratamiento" => $row['tratamiento'],
                "observacion" => $row['observacion'],
                "equipomedico" => $row['equipomedico'],
                "enfermedad" => $row['enfermedad'],
                "tipoconsulta" => $row['tipoconsulta'],
                "firma" => $row['firma'],
                "fecharevision" => $row['fecharevision']->format('Y-m-d H:i:s')
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }


}


if (isset($_GET['dataUserCompleate'])) {
    $Consultas = new EnfermeriaConsulta();
    $Consultas->dataUserCompleate();
} else if (isset($_GET['saveConsulta'])) {
    $Consultas = new EnfermeriaConsulta();
    $Consultas->saveConsulta();
} else if (isset($_GET['tblConsultas'])) {
    $Consultas = new EnfermeriaConsulta();
    $Consultas->tblConsultas();
} else if (isset($_GET['reporteConsultasdata'])) {
    $Consultas = new EnfermeriaConsulta();
    $Consultas->reporteConsultasdata();
} else if (isset($_GET['dataforeditConsulta'])) {
    $Consultas = new EnfermeriaConsulta();
    $Consultas->dataforeditConsulta();
} else if (isset($_GET['updateConsulta'])) {
    $Consultas = new EnfermeriaConsulta();
    $Consultas->updateConsulta();
}
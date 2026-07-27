<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";

class AccCorrectivas{
  function consultaAcciones (){
    $Conection = new ClassConexion();
    $usr = $_SESSION['ibm'];
    $conn = $Conection -> conexion(("TLX003MXDB"));

    $query = "SELECT e4.id, ic.Comportamiento, icausa.CauInmediata, e4.porquecausa,
              ibasica.CauBasica, e4.porque1, iraiz.CausaRaiz, e4.porqueraiz,
              e4.accioncorrectiva, e4.responsableetapa4, e.Nombre, e4.fechaac,
              e4.fechasave, e4.folioenc
            FROM 
              tblIncidenciasEncEtapa4 e4
            INNER JOIN TLX032MXDB.dbo.tblEmpleados e ON e4.responsableetapa4 = e.NoEmp
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasComportamiento ic ON e4.comportamiento = ic.NoComportamiento
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasCauInmediata icausa ON e4.causainmediata = icausa.NoCauInmediata
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasCauBasica ibasica ON e4.causabasica = ibasica.NoCauBasica
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasCausaRaiz iraiz ON e4.causaraiz = iraiz.NoCausaRaiz 
            WHERE responsableetapa4 = $usr";
    $result = sqlsrv_query($conn, $query);
    $array = array();
    while ($row = sqlsrv_fetch_array($result)) {
      array_push($array, [
        "id"=> $row["id"],
        "Comportamiento"=> $row["Comportamiento"],
        "CauInmediata"=> $row["CauInmediata"],
        "porquecausa"=> $row["porquecausa"],
        "CauBasica"=> $row["CauBasica"],
        "porque1"=> $row["porque1"],
        "CausaRaiz"=> $row["CausaRaiz"],  
        "porqueraiz"=> $row["porqueraiz"],
        "accioncorrectiva"=> $row["accioncorrectiva"],
        "responsableetapa4"=> $row["responsableetapa4"],
        "Nombre"=> $row["Nombre"],
        "fechaac"=> $row["fechaac"],
        "fechasave"=> $row["fechasave"],
        "folioenc"=> $row["folioenc"],
      ]);
    }

    sqlsrv_close($conn);
    echo json_encode($array); 
  }

  function saveRegistroAcciones () {
    $folio = $_POST["folio"];
    $comentarios = $_POST["comentarios"];
    $fecha = $_POST["fecha"];
    $checkRegAcc = $_POST["checkRegAcc"];
    $rutaArchivo = null;

    // Manejo del archivo
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $directorio = "../archivos/";
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = basename($_FILES['file']['name']);
        $rutaArchivo = $directorio . $nombreArchivo;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $rutaArchivo)) {
            http_response_code(500);
            echo "Error al mover el archivo.";
            exit;
        }
    }

    $Conection = new ClassConexion();
    $conn = $Conection -> conexion("TLX003MXDB");
    $array = array(
      $folio, 
      $comentarios,
      $fecha,
      $checkRegAcc,
      $rutaArchivo,
    );

    $query = "INSERT INTO tblIncidenciasEncEtapa4Acciones(Folio, Comentarios, Fecha, Estado, Archivo) 
              VALUES (?, ?, ?, ?, ?)";
    $result = sqlsrv_query($conn, $query, $array);  
    if ($result === false) {
        http_response_code(500);
        die(print_r(sqlsrv_errors(), true));
    } else {
        http_response_code(200);
    }

    
  }

  function datosIncidencias(){
    $usr = $_SESSION["ibm"];
    $_SESSION["ibm"];
    $Conection = new ClassConexion();
    $conn = $Conection -> conexion(("TLX003MXDB"));

    $query = "SELECT ie.id AS Folio, ie.fecha, ie.noemgenero AS NumeroEmpleado,
              e.ApellidoPaterno, e.ApellidoMaterno, e.Nombres AS NombreEmpleado,
              p.nombre AS Area, depto.NombreDepto AS Departamento, ie.noempimplicado AS EmpleadoImplicado, 
              emp.ApellidoPaterno AS APaternoImplicado,	emp.ApellidoMaterno AS AMaternoImplicado,	emp.Nombres AS NombresImplicado,
              puesto.nombre AS AreaImplicado,	deptoImp.NombreDepto AS DepartamentoImplicado, i.Incidencia AS SubClasificacion,
              ic.Clasificacion, iv.Version,	ie.descripcion AS Evento,	ae.AntEmpresa, ap.AntPuesto,
              ie.diasincapacidad,	ie.diastrabajo,	tc.TipContacto,	tl.TipLesion,	ie.provoco,
              cuerpo.ParCuerp AS ParteCuerpoAfectada,	s.Severidad, pro.Probabilidad,
              f.Frecuencia,	per.Personas,	ie.ruta AS Evidencia,	ie.lesion, ie.equipos
              From tblIncidenciasEnc ie
              INNER JOIN TLX032MXDB.dbo.tblEmpleados emp ON ie.noempimplicado = emp.NoEmp
              INNER JOIN TLX009MXDB.dbo.tblDepartamentos deptoImp ON emp.NombreDepartamento = deptoImp.NoDepto
              INNER JOIN TLX009MXDB.dbo.tblPuestos puesto ON emp.Puesto = puesto.id
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasPersonas per ON per.NoPersonas = ie.numafectados
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasFrecuencia f ON f.NoFrecuencia = ie.frecuencia
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasProbabilidad pro ON pro.NoProbabilidad = ie.probabilidad
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasSeveridad s ON s.NoSeveridad = ie.severidad
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasCuerpAfectada cuerpo ON cuerpo.NoParCuerpo = ie.parteafectada
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasTipLesion tl ON tl.NoTipLesion = ie.tipolesion
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasTipContacto tc ON tc.NoTipContacto = ie.tipocontacto
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasAntPuesto ap ON ap.NoAntPuesto = ie.antiguedadpuesto
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasAntEmpresa ae ON ae.NoantEmpresa = ie.antiguedadempresa
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasVersion iv ON iv.id = ie.vesion
              INNER JOIN TLX003MXDB.dbo.tblIncidenciasClasificacion ic ON ic.NoClasificacion = ie.clasificacion
              INNER JOIN TLX003MXDB.dbo.tblIncidencias i ON ie.incidencia = i.NoInci
              INNER JOIN TLX032MXDB.dbo.tblEmpleados e ON ie.noemgenero = e.NoEmp
              INNER JOIN TLX009MXDB.dbo.tblPuestos p ON e.Puesto = p.id 
              INNER JOIN TLX009MXDB.DBO.tblDepartamentos depto ON e.NombreDepartamento = depto.NoDepto 
              WHERE noemgenero = $usr
              AND Estado = 1";
    $result = sqlsrv_query($conn, $query);
    $array = array();
      while ($row = sqlsrv_fetch_array($result)) {
      array_push($array, [
        "Folio"=> $row["Folio"],
        "fecha"=> $row["fecha"],
        "NumeroEmpleado"=> $row["NumeroEmpleado"],
        "ApellidoPaterno"=> $row["ApellidoPaterno"],
        "ApellidoMaterno"=> $row["ApellidoMaterno"],
        "NombreEmpleado"=> $row["NombreEmpleado"],
        "Area"=> $row["Area"],
        "Departamento"=> $row["Departamento"],
        "EmpleadoImplicado"=> $row["EmpleadoImplicado"],
        "APaternoImplicado"=> $row["APaternoImplicado"],  
        "AMaternoImplicado"=> $row["AMaternoImplicado"],
        "NombresImplicado"=> $row["NombresImplicado"],
        "AreaImplicado"=> $row["AreaImplicado"],
        "DepartamentoImplicado"=> $row["DepartamentoImplicado"],
        "SubClasificacion"=> $row["SubClasificacion"],
        "Clasificacion"=> $row["Clasificacion"],
        "Version"=> $row["Version"],
        "Evento"=> $row["Evento"],
        "AntEmpresa"=> $row["AntEmpresa"],
        "AntPuesto"=> $row["AntPuesto"],
        "diasincapacidad"=> $row["diasincapacidad"],
        "diastrabajo"=> $row["diastrabajo"],
        "TipContacto"=> $row["TipContacto"],
        "TipLesion"=> $row["TipLesion"],
        "provoco"=> $row["provoco"],
        "ParteCuerpoAfectada"=> $row["ParteCuerpoAfectada"],
        "Severidad"=> $row["Severidad"],
        "Probabilidad"=> $row["Probabilidad"],
        "Frecuencia"=> $row["Frecuencia"],
        "Personas"=> $row["Personas"],
        "Evidencia"=> $row["Evidencia"],
        "lesion"=> $row["lesion"],
        "equipos"=> $row["equipos"],
      ]);
    }
    sqlsrv_close($conn);
    echo json_encode($array); 
  }

  function saveIncidencias(){
    $folio = $_POST["folio"];
    $estado = $_POST["estado"];

    $Conection = new ClassConexion();
    $conn = $Conection -> conexion(("TLX003MXDB"));
    $array = array($estado);    
    $query = "UPDATE tblIncidenciasEnc SET Estado = ? WHERE id = $folio";
    $result = sqlsrv_query($conn, $query, $array);
    if ($result === false) {
        http_response_code(500);
        die(print_r(sqlsrv_errors(), true));
    } else {
        http_response_code(200);
    }
  }
}

if (isset($_GET['consultaAcciones'])) {
    $Consultas = new AccCorrectivas();
    $Consultas->consultaAcciones();
} else if (isset($_GET['saveRegistroAcciones'])) {
    $Consultas = new AccCorrectivas();
    $Consultas->saveRegistroAcciones();
} else if (isset($_GET['datosIncidencias'])) {
    $Consultas = new AccCorrectivas();
    $Consultas->datosIncidencias();
} else if (isset($_GET['saveIncidencias'])) {
    $Consultas = new AccCorrectivas();
    $Consultas->saveIncidencias();
}
<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";

date_default_timezone_set("America/Mexico_City");

class SalaJuntasConsultas {

  function dataUserCompleate(){
        $noemp = $_POST["noemp"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX032MXDB");
        $query = "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblEmpleados.NombreDepartamento as departamento, 
                    tblEmpleados.puesto as puesto, tblEmpleados.EmpleadoSindicalizado as sindicalizado, tblEmpleados.ContrasenaOpcional as contrasena FROM tblEmpleados WHERE NoEmp=$noemp";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "noemp" => $row[0], 
                "nombre" => $row[1], 
                "departamento" => $row[2], 
                "puesto" => $row[3],
                "sindicalizado" => $row[4],
                "contrasena" => $row[5],
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

  function saveReservacionSala() {
    $nosala = $_POST["numeroSala"];
    $noemp = $_POST["noemp"];
    $fecha = $_POST["fecha"];
    $horaInicio = $_POST["horaInicio"];
    $horaFinal = $_POST["horaFinal"];
    $titulo = $_POST["titulo"];
    $descripcion = $_POST["descripcion"];
    $capacitacion = $_POST["capacitacion"];
    $estado = $_POST["estado"];

    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");

    // Primero validamos si ya existe una reunión con Estado = 2 que se cruce en la misma sala y fecha
    $queryValidacion = "SELECT * FROM tblSalaJuntasAgenda
        WHERE NumSala = ?
        AND Fecha = ?
        AND Estado = 2
        AND (
            (HoraInicio <= ? AND HoraFin > ?) OR
            (HoraInicio < ? AND HoraFin >= ?) OR
            (? <= HoraInicio AND ? >= HoraFin)
        )";

    $paramsValidacion = array(
        $nosala,
        $fecha,
        $horaInicio, $horaInicio,
        $horaFinal, $horaFinal,
        $horaInicio, $horaFinal
    );

    $stmt = sqlsrv_query($conn, $queryValidacion, $paramsValidacion);

    if ($stmt === false) {
        http_response_code(500);
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        // Ya hay una reunión en ese horario
        http_response_code(409); // Conflicto
        echo "Ya hay una reunión agendada en este horario";
        return;
    }

    // Si no hay conflicto, se inserta el registro
    $array = array(
        $nosala,
        $noemp,
        $titulo,
        $descripcion,
        $fecha,
        $horaInicio,
        $horaFinal,
        $capacitacion,
        $estado,
        $_SESSION["ibm"],
    );

    $queryInsert = "INSERT INTO tblSalaJuntasAgenda (NumSala, NombreAgenda, Titulo, Descripcion, Fecha, HoraInicio, HoraFin, Capacitacion, Estado,idsession)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $result = sqlsrv_query($conn, $queryInsert, $array);

    if ($result === false) {
        http_response_code(500);
        die(print_r(sqlsrv_errors(), true));
    } else {
        http_response_code(200);
    }
}

  function infoSalas() {
    $Conecta = new ClassConexion();
    $conn = $Conecta -> conexion("TLX002MXDB");
    $query = "SELECT tblSalaJuntas.Id as id, tblSalaJuntas.NombreSala as nomSala,
              tblSalaJuntas.Administrador as administrador 
              FROM tblSalaJuntas";
    $result = sqlsrv_query($conn, $query);
    $array = array();
    while($row = sqlsrv_fetch_array($result)) {
      array_push($array, [
        "id" => $row["id"],
        "sala" => $row["nomSala"],
        "administrador"=> $row["administrador"],
      ]);
    }
    sqlsrv_close($conn);
    echo json_encode($array);
  }

  function salasReservadas($bit) {
    $SESSION = $_SESSION['ibm'];
    $bit == 1 && $var = ' WHERE tblSalaJuntas.Administrador = '.$SESSION.' ORDER BY tblSalaJuntasAgenda.id DESC';
    $bit == 2 && $var =  ' WHERE tblSalaJuntasAgenda.idsession = '.$SESSION.' ORDER BY tblSalaJuntasAgenda.HoraInicio DESC';
    $bit == 0 && $var = ' ORDER BY tblSalaJuntasAgenda.HoraInicio ASC';
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");
    $query = "SELECT TOP 30 tblSalaJuntasAgenda.Id AS id, tblSalaJuntasAgenda.NumSala AS numSala, tblSalaJuntas.NombreSala AS NombreSala,
              tblEmpleados.Nombre AS administrador, tblEmpleados2.NoEmp AS noemp, tblEmpleados2.Nombre AS NombreAgenda, tblSalaJuntasAgenda.Titulo AS titulo,
              tblSalaJuntasAgenda.Descripcion AS descripcion, tblSalaJuntasAgenda.Fecha AS fecha, tblSalaJuntasAgenda.HoraInicio AS HoraInicio,
              tblSalaJuntasAgenda.HoraFin AS HoraFin, tblSalaJuntasAgenda.Capacitacion AS capacitacion, tblSalaJuntasAgenda.Estado AS estado
              FROM tblSalaJuntasAgenda
              INNER JOIN tblSalaJuntas ON tblSalaJuntas.Id = NumSala
              INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblSalaJuntas.Administrador
              INNER JOIN TLX032MXDB.dbo.tblEmpleados as tblEmpleados2 ON tblEmpleados2.NoEmp = tblSalaJuntasAgenda.NombreAgenda $var";
    $result = sqlsrv_query($conn, $query);
    $array = array();
    while($row = sqlsrv_fetch_array($result)){

      date_default_timezone_set("America/Mexico_City"); // Asegura que está en tu zona
      $fechaActual = new DateTime();
      $fechaReunion = DateTime::createFromFormat('Y-m-d H:i:s', $row["fecha"]->format('Y-m-d') . ' ' . $row["HoraInicio"]->format('H:i:s'));

      // Verificar si está pendiente y ya pasó la hora
      $pendiente = 0;
      if ($row["estado"] == 1 && $fechaReunion < $fechaActual) {
        $pendiente = 1;
      }

      array_push($array, [
        "id" => $row["id"],
        "numSala" => $row["numSala"],
        "NombreSala"=> $row["NombreSala"],
        "administrador"=> $row["administrador"],
        "noemp"=> $row["noemp"],
        "NombreAgenda" => $row["NombreAgenda"],
        "titulo"=> $row["titulo"],
        "descripcion"=> $row["descripcion"],
        "fecha"=> $row["fecha"]->format('Y-m-d'),
        "HoraInicio"=> $row["HoraInicio"]->format('H:i'),
        "HoraFin"=> $row["HoraFin"]->format('H:i'),
        "capacitacion"=> $row["capacitacion"],
        "estado"=> $row["estado"],
        "pendiente"=> $pendiente
      ]);
    }
    sqlsrv_close($conn);
    echo json_encode($array);
  }

  function dataJuntasAgendadas() {
    $idSala = $_POST["idSala"];
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");
    $query = "SELECT tblSalaJuntasAgenda.Id AS id, tblEmpleados.Nombre as nombre,
              tblSalaJuntasAgenda.Titulo AS titulo, tblSalaJuntasAgenda.Descripcion AS descripcion,
              tblSalaJuntasAgenda.Fecha AS fecha, tblSalaJuntasAgenda.HoraInicio as horaInicio, tblSalaJuntasAgenda.HoraFin AS horaFin,
              tblSalaJuntasAgenda.Capacitacion AS capacitacion, tblSalaJuntasAgenda.Estado AS estado
              FROM tblSalaJuntasAgenda 
              INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblSalaJuntasAgenda.NombreAgenda
              WHERE NumSala = $idSala 
              AND Fecha = CONVERT(DATE, GETDATE())
              AND Estado = 2
              ORDER BY HoraInicio ASC
              ";
    $result = sqlsrv_query($conn, $query);
    $array = array();
    while($row = sqlsrv_fetch_array($result)){
      array_push($array, [
        "idJunta" => $row["id"],
        "nombre"=> $row["nombre"],
        "titulo"=> $row["titulo"],
        "descripcion"=> $row["descripcion"],
        "fecha"=> $row["fecha"] -> format('Y-m-d'),
        "horaInicio"=> $row["horaInicio"] -> format('H:i'),
        "horaFin"=> $row["horaFin"] -> format('H:i'),
        "capacitacion"=> $row["capacitacion"],
        "estado"=> $row["estado"],
      ]);
    }
    sqlsrv_close( $conn );
    echo json_encode([
      "reuniones" => $array, 
      "hoyServidor" => date("Y-m-d"),
    ]);
  }

  function actualizarEstadoReuniones() {
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");
    $fechaActual = date("Y-m-d");
    $horaActual = date("H:i");

    $query = "UPDATE tblSalaJuntasAgenda
              SET Estado = 4
              WHERE Estado = 2
              AND (
                  Fecha < ?
                  OR (Fecha = ? AND HoraFin <= ?)
              )";

    $params = array($fechaActual, $fechaActual, $horaActual);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
    } else {
        echo json_encode(["success" => true]);
    }

    sqlsrv_close($conn);
  }

  function actualizarEstadoReunionesCaducadas() {
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");
    $fechaActual = date("Y-m-d");
    $horaActual = date("H:i");

    $query = "UPDATE tblSalaJuntasAgenda
              SET Estado = 5
              WHERE Estado = 1
              AND (
                  Fecha < ?
                  OR (Fecha = ? AND HoraFin <= ?)
              )";

    $params = array($fechaActual, $fechaActual, $horaActual);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        echo json_encode(["success" => false, "error" => sqlsrv_errors()]);
    } else {
        echo json_encode(["success" => true]);
    }

    sqlsrv_close($conn);
  }

  function actualizarEstado(){
    $id = $_POST["id"];
    $estado = $_POST["estado"];

    $Conecta = new ClassConexion();
    $conn = $Conecta -> conexion("TLX002MXDB");
    $array = array($estado);
    $querry = "UPDATE tblSalaJuntasAgenda SET Estado = ? WHERE Id = $id";
    $result = sqlsrv_query($conn, $querry, $array);
    if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
  }

  function guardarRegistroReunion() {
    $idReunionChar = $_POST["idReunion"];
    $idReunion = intval($idReunionChar);
    $noEmpChar = $_POST["noEmp"];
    $noEmp = intval($noEmpChar);

    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX002MXDB");

    $array = array(
      $idReunion,
      $noEmp,
    );

    $query = "INSERT INTO tblSalaJuntasRegistros(NumReunion, NumEmp) VALUES (?, ?)";

    $result = sqlsrv_query($conn, $query, $array);
    if ($result === false) {
      http_response_code(500);
      die(print_r(sqlsrv_errors(), true));
    } else {
      http_response_code(200);
    }


  }

}

if (isset($_GET['dataUserCompleate'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->dataUserCompleate();
} else if (isset($_GET['saveReservacionSala'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->saveReservacionSala();
} else if (isset($_GET['infoSalas'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->infoSalas();
} else if (isset($_GET['mostrarTablaSalasReservadas'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->salasReservadas(2);
} else if (isset($_GET['salasReservadasPorSesion'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->salasReservadas(1);
} else if (isset($_GET['salasReservadas'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->salasReservadas(0);
} else if (isset($_GET['dataJuntasAgendadas'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->dataJuntasAgendadas();
} else if (isset($_GET['actualizarEstadoReuniones'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->actualizarEstadoReuniones();
} else if (isset($_GET['actualizarEstadoReunionesCaducadas'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->actualizarEstadoReunionesCaducadas();
} else if (isset($_GET['actualizarEstado'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->actualizarEstado();
} else if (isset($_GET['guardarRegistroReunion'])) {
    $Consultas = new SalaJuntasConsultas();
    $Consultas->guardarRegistroReunion();
} 

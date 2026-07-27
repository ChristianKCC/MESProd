<?php
// Instancia de conexion a la BD
require_once "./CatalogosF.php";

// Si los parametros de los isset no son null entonces se ejecuta la query segun la conexion y la cadena en si misma
// Datos para pasarlos a los que se necesitan obtener en los Tiempos extras
if (isset($_GET["datosemp"])) {
    $noemp = $_GET["noemp"];
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX032MXDB");
    $query = "SELECT 
                tblEmpleados.NoEmp,
                tblEmpleados.Nombre,
                tblDepartamentos.NombreDepto as departamento,
                tblPuestos.nombre as puesto 
                FROM TLX032MXDB.dbo.tblEmpleados
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto=tblEmpleados.NombreDepartamento
            INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblEmpleados.Puesto 
            WHERE NoEmp=$noemp
            AND NoEmp <> 1 AND NoEmp <> 2";
    $result = sqlsrv_query($conn, $query);
    $array = array();
    // Los datos se almacenan un un array con las keys de cada columna y finalmente se parsea a JSON para su posterior uso
    while ($row = sqlsrv_fetch_array($result)) {
        array_push($array, ["noemp" => $row[0], "nombre" => $row[1], "departamento" => $row[2], "puesto" => $row[3]]);
    }
    sqlsrv_close($conn);
    echo json_encode($array);
} 

/*
// Metodo para obtener los datos recuperados de los puestos disponibles basados en:
Puesto regular = Puestos 
PuestoActOcupado (Hace referencia a que si el pesto ya fue cubierto o aun no)
noSemana = Solo obtenemos datos de la semana segun el folio seleccionado o creado, de esta forma filtramos todos los registros a la semana en la que se hace la solicitud

Alternativas a usar en la seleccion y consulta de los datos
WHERE CPS.puestoregular IN ($puestos); -> Para ser mas estrictos y obtener los puestos segun los puestos disponibles del empleado
WHERE CPS.puestoregular IN (1,2,4,5,8); -> Para ser mas flexibles y mostrar todas las vacantes disponibles en la semana
*/
else if (isset($_GET["disponibles"])) {
    $puestos = $_GET["puestos"];
    $nosemana = $_GET["nosemana"];
    $noemp = $_GET["noemp"];
    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX003MXDB");

    // $query = "SELECT 
    //             CPS.noemp,
    //             CPLP.nombre AS puestoRegular,
    //             TM.NombreMaquina,
    //             CPS.lunes,
    //             CPS.martes,
    //             CPS.miercoles,
    //             CPS.jueves,
    //             CPS.viernes,
    //             CPS.sabado,
    //             CPS.domingo,
    //             CPE.noSemana
    //         FROM TLX003MXDB.dbo.CambiopuestoSubEnc AS CPS
    //         INNER JOIN TLX003MXDB.dbo.Cambiopuestolistpuestos AS CPLP ON CPLP.id = CPS.puestoregular
    //         INNER JOIN TLX009MXDB.dbo.tblMaquinas AS TM ON TM.NoMaquina = CPS.maquina
    //         INNER JOIN TLX003MXDB.dbo.CambiopuestoEnc AS CPE ON CPE.id = CPS.folio
    //         WHERE CPS.puestoregular IN (1,2,4,5,8)
    //           AND CPS.puestoActOcupado IS NULL
    //           AND CPE.noSemana = $nosemana
    //           AND $noemp IS NOT IN CPS.noemp";
    $query = "WITH CTE AS(
                SELECT 
                    CPS.noemp,
                    CPLP.nombre AS puestoRegular,
                    TM.NombreMaquina,
                    CPS.lunes,
                    CPS.martes,
                    CPS.miercoles,
                    CPS.jueves,
                    CPS.viernes,
                    CPS.sabado,
                    CPS.domingo,
                    CPE.noSemana
                FROM TLX003MXDB.dbo.CambiopuestoSubEnc AS CPS
                INNER JOIN TLX003MXDB.dbo.Cambiopuestolistpuestos AS CPLP ON CPLP.id = CPS.puestoregular
                INNER JOIN TLX009MXDB.dbo.tblMaquinas AS TM ON TM.NoMaquina = CPS.maquina
                INNER JOIN TLX003MXDB.dbo.CambiopuestoEnc AS CPE ON CPE.id = CPS.folio
                WHERE CPS.puestoregular IN (1,2,4,5,8)
                    AND CPS.puestoActOcupado IS NULL
                    AND CPE.noSemana = $nosemana)
                    SELECT * FROM CTE 
                    WHERE noemp <> $noemp";

    $result = sqlsrv_query($conn, $query);
    $array = [];

    if ($result !== false) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                "noemp" => $row["noemp"],
                "puestoRegular" => $row["puestoRegular"],
                "NombreMaquina" => $row["NombreMaquina"],
                "lunes" => $row["lunes"],
                "martes" => $row["martes"],
                "miercoles" => $row["miercoles"],
                "jueves" => $row["jueves"],
                "viernes" => $row["viernes"],
                "sabado" => $row["sabado"],
                "domingo" => $row["domingo"],
                "noSemana" => $row["noSemana"]
            ];
        }
    }

    sqlsrv_close($conn);
    echo json_encode($array);
}

else if(isset($_GET["datoshoraysalida"])) {
    $noemp = $_GET["noemp"];
    $fecha = $_GET["fechabien"];

    $Conecta = new ClassConexion();
    $conn = $Conecta->conexion("TLX001MXDB");

    $query = "SELECT pin, CONVERT(time, event_time) AS fecha_h
              FROM TLX001MXDB.dbo.acc_transaction
              INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp=pin 
              WHERE pin = ? AND CONVERT(DATE, event_time) = ?;";

    $params = [$noemp, $fecha];
    $result = sqlsrv_query($conn, $query, $params);

    $array = [];

    if ($result === false) {
        // Si la consulta falla, devolvemos un JSON vacío
        echo json_encode([]);
        sqlsrv_close($conn);
        exit;
    }

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $array[] = [
            "pin" => $row["pin"],
            "fecha_h" => $row["fecha_h"] instanceof DateTime ? $row["fecha_h"]->format('H:i:s') : null
        ];
    }


    sqlsrv_close($conn);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($array);
    exit;
}


else if (isset($_GET["GetSlcAreas"])) {
    $dep = $_GET["dep"];
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT tblProactAreas.Id,tblProactAreas.NombreArea from tblProactAreas 
    INNER JOIN tblProactAreasCombo ON tblProactAreasCombo.NoArea = tblProactAreas.Id 
    WHERE tblProactAreas.AreaObsoleta=0 AND tblProactAreasCombo.NoDepto=$dep Order by tblProactAreas.NombreArea");
} else if (isset($_GET["GetSlcDeteccionRiesto"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT id,detectasteelrisgo FROM tblIMCDetecRisgo");
} else if (isset($_GET["GetSlcTipoRiesgo"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblIMCTipRisgo");
} else if (isset($_GET["GetSlcTipo"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblIMCTipo");
} else if (isset($_GET["GetSlcIMCEstado"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblIMCEstatus");
} else if (isset($_GET["GetSlcTipoPlaticas5"])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "SELECT * FROM tblTipoPlatica5min");
} else if (isset($_GET["GetSlctblIncidenciasVersion"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasVersion");
} else if (isset($_GET["GetSlctbltblIncidenciasClasEven"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasClasEven");
} else if (isset($_GET["GetSlcIncidencias"])) {
    $clasificacion = $_GET['clasificacion'];
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT tblIncidencias.* FROM tblIncidencias
INNER JOIN tblInsidenciasConClasEvenInci ON tblInsidenciasConClasEvenInci.NoCLasEven= tblIncidencias.NoInci
WHERE tblInsidenciasConClasEvenInci.NoInci=$clasificacion");
} else if (isset($_GET["GetSlcAntiguedadpuesto"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasAntPuesto");
} else if (isset($_GET["GetSlcAntiguedadempresa"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasAntEmpresa");
} else if (isset($_GET["GetSlcTipocontacto"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasTipContacto");
} else if (isset($_GET["GetSlcTipolesion"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasTipLesion");
} else if (isset($_GET["GetSlcParteAfect"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasCuerpAfectada");
} else if (isset($_GET["GetSlcSeveridad"])) {
    Catalogos::getDataSlcDBValor("TLX003MXDB", "SELECT * FROM tblIncidenciasSeveridad");
} else if (isset($_GET["GetSlcProbabilidad"])) {
    Catalogos::getDataSlcDBValor("TLX003MXDB", "SELECT * FROM tblIncidenciasProbabilidad");
} else if (isset($_GET["GetSlcFrecuencia"])) {
    Catalogos::getDataSlcDBValor("TLX003MXDB", "SELECT * FROM tblIncidenciasFrecuencia");
} else if (isset($_GET["GetSlcPersonasafectadas"])) {
    Catalogos::getDataSlcDBValor("TLX003MXDB", "SELECT * FROM tblIncidenciasPersonas");
} else if (isset($_GET["getComportamiento"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasComportamiento");
} else if (isset($_GET["getCausainmediata"])) {
    $comp = $_GET['comportamiento'];
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT tblIncidenciasCauInmediata.* FROM tblIncidenciasCauInmediata 
INNER JOIN tblIncidenciasComCauinmComp ON tblIncidenciasComCauinmComp.NoCauInmediata = tblIncidenciasCauInmediata.NoCauInmediata
WHERE tblIncidenciasComCauinmComp.NoComportamiento=$comp");
} else if (isset($_GET["getCausabasica"])) {
    $comp = $_GET['comportamiento'];
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT tblIncidenciasCauBasica.* FROM tblIncidenciasCauBasica 
INNER JOIN tblIncidenciasComCauBasicaFactores ON tblIncidenciasComCauBasicaFactores.NoCausaBasica = tblIncidenciasCauBasica.NoCauBasica
WHERE tblIncidenciasComCauBasicaFactores.NoFactores=$comp");
}else if (isset($_GET["getCausaraiz"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasCausaRaiz");
}else if (isset($_GET["sistemaGestion"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasElementosTipo");
}else if (isset($_GET["sistemaGestionsub"])) {
    $tipo = $_GET['tipoelemento'];
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasElementos WHERE tipo = $tipo");
}else if (isset($_GET["TipoEvaluador"])) {
    Catalogos::getDataSlcDB("TLX003MXDB", "SELECT * FROM tblIncidenciasTipoEvaluador WHERE tipoEvaluador NOT IN ('INVESTIGADOR', 'JEFE DE DEPARTAMENTO') ORDER BY tipoEvaluador ASC");
}


<?php

use Vtiful\Kernel\Format;
require_once "../../conexion.php";
require_once "../../Session/seguridad.php";
class Incidencia
{
    function saveIncidencia()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");

        // Datos del formulario
        $fecha = $_POST['fecha'];
        $NoDepto = $_POST['NoDepto'];
        $NoMaquina = $_POST['NoMaquina'];
        $version = $_POST['version'];
        $clasificacion = $_POST['clasificacion'];
        $incidencias = $_POST['incidencias'];
        $descripcioncapa = $_POST['descripcioncapa'];
        $implicado = $_POST['implicado'];
        $antiguedadpuesto = $_POST['antiguedadpuesto'];
        $antiguedadempresa = $_POST['antiguedadempresa'];
        $diasincapacidad = $_POST['diasincapacidad'];
        $diastrabajo = $_POST['diastrabajo'];
        $tipocontacto = $_POST['tipocontacto'];
        $provocolesion = $_POST['provocolesion'];
        $tipolesion = $_POST['tipolesion'];
        $parteafectada = $_POST['parteafectada'];
        $severidad = $_POST['severidad'];
        $probabilidad = $_POST['probabilidad'];
        $frecuencia = $_POST['frecuencia'];
        $noexpuetas = $_POST['noexpuetas'];
        $etapa1check1 = $_POST['etapa1check1'];
        $etapa1check2 = $_POST['etapa1check2'];

        // Manejo del archivo
        $ruta = "Sin archivo"; // Valor por defecto
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $ruta = "../Files/" . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $ruta);
        }

        $noReporte = $_POST["noReporte"];
        $totalE1 = $_POST["totalE1"];

        // Consulta SQL
        $query = "INSERT INTO tblIncidenciasEnc(
            fecha, departamento, area, vesion, clasificacion, incidencia, descripcion,
            noemgenero, noempimplicado, antiguedadpuesto, antiguedadempresa, diasincapacidad,
            diastrabajo, tipocontacto, provoco, tipolesion, parteafectada, severidad,
            probabilidad, frecuencia, numafectados, lesion, equipos, ruta, NoReporte, Total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $result = sqlsrv_query($conn, $query, array(
            $fecha,
            $NoDepto,
            $NoMaquina,
            $version,
            $clasificacion,
            $incidencias,
            $descripcioncapa,
            $_SESSION['ibm'],
            $implicado,
            $antiguedadpuesto,
            $antiguedadempresa,
            $diasincapacidad,
            $diastrabajo,
            $tipocontacto,
            $provocolesion,
            $tipolesion,
            $parteafectada,
            $severidad,
            $probabilidad,
            $frecuencia,
            $noexpuetas,
            $etapa1check1,
            $etapa1check2,
            $ruta,
            $noReporte, 
            $totalE1
        ));

        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardó la información correctamente");
        }
    }


    function tblEncabezado()
    {
        $ibm =  $_SESSION['ibm'];
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $query = "SELECT tblIncidenciasEnc.id,tblIncidenciasEnc.fecha, TLX009MXDB.dbo.tblDepartamentos.NombreDepto, tblProactAreas.NombreArea, 
                        tblIncidenciasVersion.Version, tblIncidenciasClasificacion.Clasificacion, tblIncidencias.Incidencia, 
                         tblIncidenciasEnc.descripcion, tblEmpleados_1.Nombre as nombregenero, TLX032MXDB.dbo.tblEmpleados.Nombre AS nombreimplicado, 
                         tblIncidenciasAntPuesto.AntPuesto, tblIncidenciasAntEmpresa.AntEmpresa, tblIncidenciasEnc.diasincapacidad, 
                         tblIncidenciasEnc.diastrabajo, tblIncidenciasTipContacto.TipContacto, tblIncidenciasEnc.provoco, 
                         tblIncidenciasTipLesion.TipLesion, tblIncidenciasCuerpAfectada.ParCuerp, tblIncidenciasSeveridad.Severidad, 
                         tblIncidenciasProbabilidad.Probabilidad, tblIncidenciasFrecuencia.Frecuencia, tblIncidenciasEnc.numafectados, 
                         tblIncidenciasEnc.datacarga, tblEA.adminIncidencias, tblIncidenciasEnc.Total
                         FROM tblIncidenciasEnc INNER JOIN
                         TLX009MXDB.dbo.tblDepartamentos ON tblIncidenciasEnc.departamento = TLX009MXDB.dbo.tblDepartamentos.NoDepto INNER JOIN
                         tblProactAreas ON tblIncidenciasEnc.area = tblProactAreas.Id 
                         LEFT JOIN tblIncidenciasVersion ON tblIncidenciasEnc.vesion = tblIncidenciasVersion.id 
                         LEFT JOIN tblIncidenciasClasificacion ON tblIncidenciasEnc.clasificacion = tblIncidenciasClasificacion.NoClasificacion 
                         LEFT JOIN tblIncidencias ON tblIncidenciasEnc.incidencia = tblIncidencias.NoInci 
                         LEFT JOIN tblIncidenciasAntPuesto ON tblIncidenciasEnc.antiguedadpuesto = tblIncidenciasAntPuesto.NoAntPuesto 
                         LEFT JOIN tblIncidenciasAntEmpresa ON tblIncidenciasEnc.antiguedadempresa = tblIncidenciasAntEmpresa.NoantEmpresa 
                         LEFT JOIN tblIncidenciasTipContacto ON tblIncidenciasEnc.tipocontacto = tblIncidenciasTipContacto.NoTipContacto 
                         LEFT JOIN tblIncidenciasTipLesion ON tblIncidenciasEnc.tipolesion = tblIncidenciasTipLesion.NoTipLesion 
                         LEFT JOIN tblIncidenciasCuerpAfectada ON tblIncidenciasEnc.parteafectada = tblIncidenciasCuerpAfectada.NoParCuerpo 
                         LEFT JOIN tblIncidenciasSeveridad ON tblIncidenciasEnc.severidad = tblIncidenciasSeveridad.NoSeveridad 
                         LEFT JOIN tblIncidenciasProbabilidad ON tblIncidenciasEnc.probabilidad = tblIncidenciasProbabilidad.NoProbabilidad 
                         LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS tblEmpleados_1 ON tblIncidenciasEnc.noemgenero = tblEmpleados_1.NoEmp 
                         LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblIncidenciasEnc.noempimplicado = tblEmpleados.NoEmp 
                         LEFT JOIN tblIncidenciasFrecuencia ON tblIncidenciasEnc.frecuencia = tblIncidenciasFrecuencia.NoFrecuencia
                         INNER JOIN TLX032MXDB.dbo.tblEmpleadosnvlautoriza tblEA ON tblEA.ibm = $ibm";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'fecha' => $row['fecha'],
                'departamento' => $row['NombreDepto'],
                'area' => $row['NombreArea'],
                'vesion' => $row['Version'],
                'clasificacion' => $row['Clasificacion'],
                'incidencia' => $row['Incidencia'],
                'descripcion' => $row['descripcion'],
                'noemgenero' => $row['nombregenero'],
                'noempimplicado' => $row['nombreimplicado'],
                'lvlAutoriza' => $row['adminIncidencias'],
                'total' => $row['Total']
            ]);
        }
        echo json_encode($array);
    }

    function dataForEditEncEtapa(){
        $ibm =  $_SESSION['ibm'];
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];

        $query = "SELECT tblinc.*, tblem.Nombres,tblp.nombre AS NombrePuesto
                  FROM tblIncidenciasEnc tblinc
                  LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblem ON tblem.NoEmp = tblinc.noempimplicado
                  LEFT JOIN TLX009MXDB.dbo.tblPuestos tblp ON tblp.id = tblem.Puesto
                  WHERE tblinc.id = $id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id"=> $row["id"],
                "fecha"=> $row["fecha"]->format("Y-m-d"),
                "departamento"=> $row["departamento"],
                "area"=> $row["area"],
                "vesion"=> $row["vesion"],
                "clasificacion"=> $row["clasificacion"],
                "incidencia"=> $row["incidencia"],
                "descripcion"=> $row["descripcion"],
                "noempimplicado"=> $row["noempimplicado"],
                "Nombres"=> $row["Nombres"],
                "NombrePuesto"=> $row["NombrePuesto"],
                "antiguedadpuesto"=> $row["antiguedadpuesto"],
                "antiguedadempresa"=> $row["antiguedadempresa"],
                "diasincapacidad"=> $row["diasincapacidad"],
                "diastrabajo"=> $row["diastrabajo"],
                "tipocontacto"=> $row["tipocontacto"],
                "provoco"=> $row["provoco"],
                "tipolesion"=> $row["tipolesion"],
                "parteafectada"=> $row["parteafectada"],
                "severidad"=> $row["severidad"],
                "probabilidad"=> $row["probabilidad"],
                "frecuencia"=> $row["frecuencia"],
                "numafectados"=> $row["numafectados"],
                "lesion"=> $row["lesion"],
                "equipos"=> $row["equipos"],
                "NoReporte"=>$row["NoReporte"],
                "Total"=>$row["Total"],
                "ibm" => $ibm,
                "ruta" => $row["ruta"]
            ]);
        }
        echo json_encode($array);
    }

    function updateIncidencia(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");

        $fecha = $_POST['fecha'];
        $NoDepto = $_POST['NoDepto'];
        $NoMaquina = $_POST['NoMaquina'];
        $version = $_POST['version'];
        $clasificacion = $_POST['clasificacion'];
        $incidencias = $_POST['incidencias'];
        $descripcioncapa = $_POST['descripcioncapa'];
        $implicado = $_POST['implicado'];
        $antiguedadpuesto = $_POST['antiguedadpuesto'];
        $antiguedadempresa = $_POST['antiguedadempresa'];
        $diasincapacidad = $_POST['diasincapacidad'];
        $diastrabajo = $_POST['diastrabajo'];
        $tipocontacto = $_POST['tipocontacto'];
        $provocolesion = $_POST['provocolesion'];
        $tipolesion = $_POST['tipolesion'];
        $parteafectada = $_POST['parteafectada'];
        $severidad = $_POST['severidad'];
        $probabilidad = $_POST['probabilidad'];
        $frecuencia = $_POST['frecuencia'];
        $noexpuetas = $_POST['noexpuetas'];
        $etapa1check1 = $_POST['etapa1check1'];
        $etapa1check2 = $_POST['etapa1check2'];

        // Manejo del archivo
        $ruta = "Sin archivo"; // Valor por defecto
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $ruta = "../Files/" . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $ruta);
        }

        $folio = $_POST['folio'];
        $noReporte = $_POST["noReporte"];
        $totalE1 = $_POST["totalE1"];

        $array = array(
            $fecha,
            $NoDepto,
            $NoMaquina,
            $version,
            $clasificacion,
            $incidencias,
            $descripcioncapa,
            $_SESSION['ibm'],
            $implicado,
            $antiguedadpuesto,
            $antiguedadempresa,
            $diasincapacidad,
            $diastrabajo,
            $tipocontacto,
            $provocolesion,
            $tipolesion,
            $parteafectada,
            $severidad,
            $probabilidad,
            $frecuencia,
            $noexpuetas,
            $etapa1check1,
            $etapa1check2,
            $ruta, 
            $noReporte,
            $totalE1,
            $folio
        );

        $query = "UPDATE tblIncidenciasEnc 
                SET fecha=?, departamento=?, area=?, 
                vesion=?, clasificacion=?, incidencia=?, descripcion=?, noemgenero=?,
                noempimplicado=?, antiguedadpuesto=?, antiguedadempresa=?, diasincapacidad=?,
                diastrabajo=?, tipocontacto=?, provoco=?, tipolesion=?, parteafectada=?,
                severidad=?, probabilidad=?, frecuencia=?, numafectados=?, lesion=?,
                equipos=?, ruta=?, NoReporte=?, Total=? 
                WHERE id=?";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se actualizo la información correctamente");
        }
    }
    
    // ---------- ETAPA 3 -------------
    function saveIncidenciaEtapa3()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $eventosprev = $_POST['eventosprev'];
        $eventofalla = $_POST['eventofalla'];
        $equipos = $_POST['equipos'];
        $operacion = $_POST['operacion'];
        $producto = $_POST['producto'];
        $material = $_POST['material'];
        $otro = $_POST['otro'];
        $otroexplique = $_POST['otroexplique'];
        $descp1 = $_POST['descp1'];
        $responsable1 = $_POST['responsable1'];
        $fechaimp1 = $_POST['fechaimp1'];
        $descp2 = $_POST['descp2'];
        $responsable2 = $_POST['responsable2'];
        $fechaimp2 = $_POST['fechaimp2'];
        $descp3 = $_POST['descp3'];
        $responsable3 = $_POST['responsable3'];
        $fechaimp3 = $_POST['fechaimp3'];
        $folioenc = $_POST['folioenc'];
        $query =  "INSERT INTO tblIncidenciasEncEtapa3(EventosPrev,Eventofalla,danoequipo,suspension,producto,material,otro,otrodesc,
        desc1,resp1,fecha1, desc2,resp2,fecha2, desc3,resp3,fecha3,folioenc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)";
        $result = sqlsrv_query($conn, $query, array(
            $eventosprev,
            $eventofalla,
            $equipos,
            $operacion,
            $producto,
            $material,
            $otro,
            $otroexplique,
            $descp1,
            $responsable1,
            $fechaimp1,
            $descp2,
            $responsable2,
            $fechaimp2,
            $descp3,
            $responsable3,
            $fechaimp3,
            $folioenc
        ));
        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardo la información correctamente");
        }
    }

    function tblEtapa3()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $folioenc = $_GET['folioenc'];
        $query = "SELECT tblIncidenciasEncEtapa3.*, tblIncEnc.NoReporte, tblE1.Nombre AS Nombre1, tblE2.Nombre AS Nombre2, tblE3.Nombre AS Nombre3
                FROM  tblIncidenciasEncEtapa3 
                INNER JOIN TLX032MXDB.dbo.tblEmpleados tblE1 ON tblE1.NoEmp = tblIncidenciasEncEtapa3.resp1
                INNER JOIN TLX032MXDB.dbo.tblEmpleados tblE2 ON tblE2.NoEmp = tblIncidenciasEncEtapa3.resp1
                INNER JOIN TLX032MXDB.dbo.tblEmpleados tblE3 ON tblE3.NoEmp = tblIncidenciasEncEtapa3.resp1
                LEFT JOIN TLX003MXDB.dbo.tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblIncidenciasEncEtapa3.folioenc 
                WHERE folioenc=" . $folioenc;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'EventosPrev' => $row['EventosPrev'],
                'Eventofalla' => $row['Eventofalla'],
                'danoequipo' => $row['danoequipo'],
                'suspension' => $row['suspension'],
                'producto' => $row['producto'],
                'material' => $row['material'],
                'otro' => $row['otro'],
                'desc1' => $row['desc1'],
                'resp1' => $row['resp1'],
                'Nombre1'=> $row['Nombre1'],
                'desc2' => $row['desc2'],
                'resp2' => $row['resp2'],
                'Nombre2'=> $row['Nombre2'],
                'desc3' => $row['desc3'],
                'resp3' => $row['resp3'],
                'Nombre3'=> $row['Nombre3'],
                'NoReporte' => $row['NoReporte']
            ]);
        }
        echo json_encode($array);
    }

    function dataForEditEtapa3(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "SELECT tblIncidenciasEncEtapa3.*, tblEmpleados.Nombre AS Nombre1, tble2.Nombre AS Nombre2, 
                tble3.Nombre AS Nombre3, tblIncEnc.NoReporte AS NoReporte
                FROM tblIncidenciasEncEtapa3
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIncidenciasEncEtapa3.resp1
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS tble2  ON tble2.NoEmp = tblIncidenciasEncEtapa3.resp2
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados AS tble3  ON tble3.NoEmp = tblIncidenciasEncEtapa3.resp3
                LEFT JOIN TLX003MXDB.dbo.tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblIncidenciasEncEtapa3.folioenc
                WHERE tblIncidenciasEncEtapa3.id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $row["fecha1"] = $row["fecha1"] -> format('Y-m-d');
            $row["fecha2"] = $row["fecha2"] -> format('Y-m-d');
            $row["fecha3"] = $row["fecha3"] -> format('Y-m-d');
            $array[] = $row;
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function actualizarIncidenciaEtapa3(){
        $id = $_POST["id"];
        $eventosprev = $_POST["eventosprev"];
        $eventofalla = $_POST["eventofalla"];
        $equipos = $_POST["equipos"];
        $operacion = $_POST["operacion"];
        $producto = $_POST["producto"];
        $material = $_POST["material"];
        $otro = $_POST["otro"];
        $otroexplique = $_POST["otroexplique"];
        $descp1 = $_POST["descp1"];
        $responsable1 = $_POST["responsable1"];
        $fechaimp1 = $_POST["fechaimp1"];
        $descp2 = $_POST["descp2"];
        $responsable2 = $_POST["responsable2"];
        $fechaimp2 = $_POST["fechaimp2"];
        $descp3 = $_POST["descp3"];
        $responsable3 = $_POST["responsable3"];
        $fechaimp3 = $_POST["fechaimp3"];
        $folioenc = $_POST["folioenc"];

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $array = array(
            $eventosprev,
            $eventofalla,
            $equipos,
            $operacion,
            $producto,
            $material,
            $otro,
            $otroexplique,
            $descp1,
            $responsable1,
            $fechaimp1,
            $descp2,
            $responsable2,
            $fechaimp2,
            $descp3,
            $responsable3,
            $fechaimp3,
            $folioenc,
            $id
        );

       $query = "UPDATE tblIncidenciasEncEtapa3
                SET EventosPrev=?, Eventofalla=?, danoequipo=?, suspension=?, 
                producto=?, material=?, otro=?, otrodesc=?, desc1=?, resp1=?, fecha1=?, 
                desc2=?, resp2=?, fecha2=?, desc3=?, resp3=?, fecha3=?, folioenc=? 
                WHERE id=?";

        $result = sqlsrv_query($conn, $query, $array);
       if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se actualizo la información correctamente");
        }

    }

    // ---------- ETAPA 4 -------------
    function saveEtapa4()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $comportamiento = $_POST['comportamiento'];
        $causainmediata = $_POST['causainmediata'];
        $porquecausa = $_POST['porquecausa'];
        $causabasica = $_POST['causabasica'];
        $porque1 = $_POST['porque1'];
        $causaraiz = $_POST['causaraiz'];
        $porqueraiz = $_POST['porqueraiz'];
        $accioncorrectiva = $_POST['accioncorrectiva'];
        $responsableetapa4 = $_POST['responsableetapa4'];
        $fechaac = $_POST['fechaac'];
        $accioncorrectiva2 = $_POST['accioncorrectiva2'];
        $responsableetapa42 = $_POST['responsableetapa42'];
        $fechaac2 = $_POST['fechaac2'];
        $accioncorrectiva3 = $_POST['accioncorrectiva3'];
        $responsableetapa43 = $_POST['responsableetapa43'];
        $fechaac3 = $_POST['fechaac3'];
        $accioncorrectiva4 = $_POST['accioncorrectiva4'];
        $responsableetapa44 = $_POST['responsableetapa44'];
        $fechaac4 = $_POST['fechaac4'];
        $accioncorrectiva5 = $_POST['accioncorrectiva5'];
        $responsableetapa45 = $_POST['responsableetapa45'];
        $fechaac5 = $_POST['fechaac5'];

        $folioenc = $_POST['folioenc'];
       $query = "INSERT INTO tblIncidenciasEncEtapa4(
                comportamiento, causainmediata, porquecausa, causabasica, porque1, causaraiz, porqueraiz,
                accioncorrectiva, responsableetapa4, fechaac,
                accioncorrectiva2, responsableetapa42, fechaac2,
                accioncorrectiva3, responsableetapa43, fechaac3,
                accioncorrectiva4, responsableetapa44, fechaac4,
                accioncorrectiva5, responsableetapa45, fechaac5,
                folioenc) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = sqlsrv_query($conn, $query, array(
            $comportamiento,
            $causainmediata,
            $porquecausa,
            $causabasica,
            $porque1,
            $causaraiz,
            $porqueraiz,
            $accioncorrectiva,
            $responsableetapa4,
            $fechaac,
            $accioncorrectiva2,
            $responsableetapa42,
            $fechaac2,
            $accioncorrectiva3,
            $responsableetapa43,
            $fechaac3,
            $accioncorrectiva4,
            $responsableetapa44,
            $fechaac4,
            $accioncorrectiva5,
            $responsableetapa45,
            $fechaac5,
            $folioenc
        ));
        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardo la información correctamente");
        }
    }

    function tblEtapa4()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $folioenc = $_GET['folioenc'];
        $query = "SELECT tblincE4.*, tblIncEnc.NoReporte, tblinC.Comportamiento as NombreComportamiento, tblci.CauInmediata as NombreCausaInm,
                  tblcb.CauBasica as NombreCauB, tblcr.CausaRaiz AS NombreCauR, tblEm.Nombre AS NombreResp
                  FROM  tblIncidenciasEncEtapa4 tblincE4
                  LEFT JOIN TLX003MXDB.dbo.tblIncidenciasComportamiento tblinC ON tblinC.NoComportamiento = tblincE4.comportamiento
                  LEFT JOIN TLX003MXDB.dbo.tblIncidenciasCauInmediata tblci ON tblci.NoCauInmediata = tblincE4.causainmediata
                  LEFT JOIN TLX003MXDB.dbo.tblIncidenciasCauBasica tblcb ON tblcb.NoCauBasica = tblincE4.causabasica
                  LEFT JOIN TLX003MXDB.dbo.tblIncidenciasCausaRaiz tblcr ON tblcr.NoCausaRaiz = tblincE4.causaraiz
                  LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEm ON tblEm.NoEmp = tblincE4.responsableetapa4
                  LEFT JOIN TLX003MXDB.dbo.tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblincE4.folioenc
                  WHERE folioenc=" . $folioenc;
                  
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'comportamiento' => $row['comportamiento'],
                'NombreComp'=> $row['NombreComportamiento'],
                'causainmediata' => $row['causainmediata'],
                'NombreCausaInm'=> $row['NombreCausaInm'],
                'porquecausa' => $row['porquecausa'],
                'causabasica' => $row['causabasica'],
                'NombreCauB'=> $row['NombreCauB'],
                'causaraiz' => $row['causaraiz'],
                'NombreCauR'=> $row['NombreCauR'],
                'accioncorrectiva' => $row['accioncorrectiva'],
                'fechaac' => $row['fechaac']->format('Y-m-d'),
                'NombreResp'=> $row['NombreResp'],
                'NoReporte' => $row['NoReporte']
            ]);
        }
        echo json_encode($array);
    }

    function dataForEditEtapa4(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "SELECT tblIncEnc4.*, tblEmpleados.Nombre AS Nombre, tblEmp2.Nombre AS Nombre2, tblEmp3.Nombre AS Nombre3,
		tblEmp4.Nombre AS Nombre4, tblEmp5.Nombre AS Nombre5, tblIncEnc.NoReporte
                FROM tblIncidenciasEncEtapa4 tblIncEnc4 
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIncEnc4.responsableetapa4
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp2 ON tblEmp2.NoEmp = tblIncEnc4.responsableetapa42
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp3 ON tblEmp3.NoEmp = tblIncEnc4.responsableetapa43
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp4 ON tblEmp4.NoEmp = tblIncEnc4.responsableetapa44
                LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp5 ON tblEmp5.NoEmp = tblIncEnc4.responsableetapa45
                LEFT JOIN TLX003MXDB.dbo.tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblIncEnc4.folioenc
                WHERE tblIncEnc4.id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $row["fechaac"]   = ($row["fechaac"]   !== null) ? $row["fechaac"]->format('Y-m-d') : '';
            $row["fechaac2"]  = ($row["fechaac2"]  !== null) ? $row["fechaac2"]->format('Y-m-d') : '';
            $row["fechaac3"]  = ($row["fechaac3"]  !== null) ? $row["fechaac3"]->format('Y-m-d') : '';
            $row["fechaac4"]  = ($row["fechaac4"]  !== null) ? $row["fechaac4"]->format('Y-m-d') : '';
            $row["fechaac5"]  = ($row["fechaac5"]  !== null) ? $row["fechaac5"]->format('Y-m-d') : '';
            $array[] = $row;
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function actualizarEtapa4(){
        $folioetapa4        = $_POST["folioetapa4"];
        $comportamiento     = $_POST["comportamiento"];
        $causainmediata     = $_POST["causainmediata"];
        $porquecausa        = $_POST["porquecausa"];
        $causabasica        = $_POST["causabasica"];
        $porque1            = $_POST["porque1"];
        $causaraiz          = $_POST["causaraiz"];
        $porqueraiz         = $_POST["porqueraiz"];
        $accioncorrectiva   = $_POST["accioncorrectiva"];
        $responsableetapa4  = $_POST["responsableetapa4"];
        $fechaac            = $_POST["fechaac"];
        $folioenc           = $_POST["folioenc"];

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $array = array(
            $comportamiento,
            $causainmediata,
            $porquecausa,
            $causabasica,
            $porque1,
            $causaraiz,
            $porqueraiz,
            $accioncorrectiva,
            $responsableetapa4,
            $fechaac,
            $folioenc,
            $folioetapa4
        );

        $query = "UPDATE tblIncidenciasEncEtapa4 SET comportamiento=?, causainmediata=?, porquecausa=?,
                    causabasica=?, porque1=?, causaraiz=?, porqueraiz=?, accioncorrectiva=?,
                    responsableetapa4=?, fechaac=?, folioenc=?
                WHERE id=$folioetapa4";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
                http_response_code(500);
                echo json_encode(sqlsrv_errors()[0]['message']);
            } else {
                http_response_code(200);
                echo json_encode("Se actualizo la información correctamente");
            }

    }

    // ---------- ETAPA 5 -------------
    function saveEtapa5()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $incprisa = $_POST['incprisa'];
        $incojostarea = $_POST['incojostarea'];
        $frustracion = $_POST['frustracion'];
        $mente = $_POST['mente'];
        $fatiga = $_POST['fatiga'];
        $peligro = $_POST['peligro'];
        $riesgo = $_POST['riesgo'];
        $equilibrio = $_POST['equilibrio'];
        $interaccion1 = $_POST['interaccion1'];
        $interaccion2 = $_POST['interaccion2'];
        $interaccion3 = $_POST['interaccion3'];
        $interaccion4 = $_POST['interaccion4'];
        $interaccion5 = $_POST['interaccion5'];
        $interaccion6 = $_POST['interaccion6'];
        $riesgos1 = $_POST['riesgos1'];
        $riesgos1porque = $_POST['riesgos1porque'];
        $riesgos2 = $_POST['riesgos2'];
        $riesgos2porque = $_POST['riesgos2porque'];
        $riesgos3 = $_POST['riesgos3'];
        $riesgos3porque = $_POST['riesgos3porque'];
        $riesgos4 = $_POST['riesgos4'];
        $riesgos4porque = $_POST['riesgos4porque'];
        $folioenc = $_POST['folioenc'];
        $query =  "INSERT INTO tblIncidenciasEncEtapa5(
                    incprisa,incojostarea,frustracion,mente,fatiga,peligro,riesgo,equilibrio,
                    interaccion1,interaccion2,interaccion3,interaccion4,interaccion5,interaccion6,
                    riesgos1,riesgos1porque,riesgos2,riesgos2porque,riesgos3,riesgos3porque,
                    riesgos4,riesgos4porque,folioenc
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = sqlsrv_query($conn, $query, array(
            $incprisa,
            $incojostarea,
            $frustracion,
            $mente,
            $fatiga,
            $peligro,
            $riesgo,
            $equilibrio,
            $interaccion1,
            $interaccion2,
            $interaccion3,
            $interaccion4,
            $interaccion5,
            $interaccion6,
            $riesgos1,
            $riesgos1porque,
            $riesgos2,
            $riesgos2porque,
            $riesgos3,
            $riesgos3porque,
            $riesgos4,
            $riesgos4porque,
            $folioenc
        ));
        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardo la información correctamente");
        }
    }

    function tbletapa5()
    {

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $folioenc = $_GET['folioenc'];
        $query = "SELECT tblIncidenciasEncEtapa5.id,tblIncidenciasElementos.Elemento,tblIncidenciasElementosTipo.tipoelemento,tblIncidenciasEncEtapa5.sistemagestionporque, 
        tblIncidenciasEncEtapa5.incprisa,tblIncidenciasEncEtapa5.frustracion FROM tblIncidenciasEncEtapa5
        INNER JOIN tblIncidenciasElementos ON tblIncidenciasElementos.id= tblIncidenciasEncEtapa5.sistemagestionsub
        INNER JOIN tblIncidenciasElementosTipo ON tblIncidenciasElementosTipo.id= tblIncidenciasEncEtapa5.sistemagestion WHERE folioenc=" . $folioenc;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'Elemento' => $row['Elemento'],
                'tipoelemento' => $row['tipoelemento'],
                'sistemagestionporque' => $row['sistemagestionporque'],
                'incprisa' => $row['incprisa'],
                'frustracion' => $row['frustracion']
            ]);
        }
        echo json_encode($array);
    }

    function dataForEditEtapa5(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "SELECT * FROM tblIncidenciasEncEtapa5 WHERE id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function actualizarEtapa5(){
        $idEtapa5              = $_POST["idEtapa5"];
        $incprisa              = $_POST["incprisa"];
        $incojostarea          = $_POST["incojostarea"];
        $frustracion           = $_POST["frustracion"];
        $mente                 = $_POST["mente"];
        $fatiga                = $_POST["fatiga"];
        $peligro               = $_POST["peligro"];
        $riesgo                = $_POST["riesgo"];
        $equilibrio            = $_POST["equilibrio"];
        $interaccion1          = $_POST["interaccion1"];
        $interaccion2          = $_POST["interaccion2"];
        $interaccion3          = $_POST["interaccion3"];
        $interaccion4          = $_POST["interaccion4"];
        $interaccion5          = $_POST["interaccion5"];
        $interaccion6          = $_POST["interaccion6"];
        $riesgos1              = $_POST["riesgos1"];
        $riesgos2              = $_POST["riesgos2"];
        $riesgos3              = $_POST["riesgos3"];
        $riesgos4              = $_POST["riesgos4"];
        $riesgos1porque        = $_POST["riesgos1porque"];
        $riesgos2porque        = $_POST["riesgos2porque"];
        $riesgos3porque        = $_POST["riesgos3porque"];
        $riesgos4porque        = $_POST["riesgos4porque"];
        $sistemagestion        = $_POST["sistemagestion"];
        $sistemagestionsub     = $_POST["sistemagestionsub"];
        $sistemagestionporque  = $_POST["sistemagestionporque"];
        $folioenc              = $_POST["folioenc"];


        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $array = array(
            $incprisa,
            $incojostarea,
            $frustracion,
            $mente,
            $fatiga,
            $peligro,
            $riesgo,
            $equilibrio,
            $interaccion1,
            $interaccion2,
            $interaccion3,
            $interaccion4,
            $interaccion5,
            $interaccion6,
            $riesgos1,
            $riesgos1porque,
            $riesgos2,
            $riesgos2porque,
            $riesgos3,
            $riesgos3porque,
            $riesgos4,
            $riesgos4porque,
            $sistemagestion,
            $sistemagestionsub,
            $sistemagestionporque,
            $folioenc,
            $idEtapa5
        );

        $query = "UPDATE tblIncidenciasEncEtapa5 SET incprisa = ?, incojostarea = ?, frustracion = ?, mente = ?,
                fatiga = ?, peligro = ?, riesgo = ?, equilibrio = ?, interaccion1 = ?, interaccion2 = ?,
                interaccion3 = ?, interaccion4 = ?, interaccion5 = ?, interaccion6 = ?, riesgos1 = ?,
                riesgos1porque = ?, riesgos2 = ?, riesgos2porque = ?, riesgos3 = ?, riesgos3porque = ?,
                riesgos4 = ?, riesgos4porque = ?, sistemagestion = ?, sistemagestionsub = ?, sistemagestionporque = ?,
                folioenc = ?
                WHERE id=$idEtapa5";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
                http_response_code(500);
                echo json_encode(sqlsrv_errors()[0]['message']);
            } else {
                http_response_code(200);
                echo json_encode("Se actualizo la información correctamente");
            }

    }

    // ---------- ETAPA 6 -------------
    function saveEtapa6()
    {
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $sistemagestion = $_POST['sistemagestion'];
        $sistemagestionsub = $_POST['sistemagestionsub'];
        $sistemagestionporque = $_POST['sistemagestionporque'];
        $folio = $_POST['folio'];
        $query =  "INSERT INTO tblIncidenciasEncEtapa6(sistemagestion,sistemagestionsub,sistemagestionporque,folioenc) VALUES (?, ?, ?, ?)";
        $result = sqlsrv_query($conn, $query, array(
            $sistemagestion,
            $sistemagestionsub,
            $sistemagestionporque,
            $folio
        ));
        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardo la información correctamente");
        }
    }

    function tbletapa6()
    {

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $folioenc = $_GET['folioenc'];
        $query = "SELECT e6.id, e6.sistemagestion,tblIncidenciasElementosTipo.tipoelemento,  
                    e6.sistemagestionsub, tblIncidenciasElementos.elemento, e6.sistemagestionporque, tblIE.NoReporte
                    FROM tblIncidenciasEncEtapa6 e6
                    LEFT JOIN tblIncidenciasElementosTipo ON tblIncidenciasElementosTipo.id= e6.sistemagestion
                    LEFT JOIN tblIncidenciasElementos ON tblIncidenciasElementos.id= e6.sistemagestionsub
                    LEFT JOIN tblIncidenciasEnc tblIE ON tblIE.id = e6.folioenc
                    WHERE folioenc=" . $folioenc;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'sistemagestion' => $row['sistemagestion'],
                'tipoelemento' => $row['tipoelemento'],
                'sistemagestionsub' => $row['sistemagestionsub'],
                'elemento' => $row['elemento'],
                'sistemagestionporque' => $row['sistemagestionporque'],
                'NoReporte' => $row ['NoReporte']
            ]);
        }
        echo json_encode($array);
    }

    function dataForEditEtapa6(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "SELECT * FROM tblIncidenciasEncEtapa6 WHERE id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function actualizarEtapa6(){
        $idEtapa6 = $_POST["idEtapa6"];
        $sistemagestion = $_POST["sistemagestion"];
        $sistemagestionsub = $_POST["sistemagestionsub"];
        $sistemagestionporque = $_POST["sistemagestionporque"];
        $folio    = $_POST["folio"];

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $array = array(
           $sistemagestion,
           $sistemagestionsub,
           $sistemagestionporque,
           $folio,
           $idEtapa6
        );

        $query = "UPDATE tblIncidenciasEncEtapa6 SET sistemagestion=?, sistemagestionsub=?, sistemagestionporque=?, folioenc=?
                WHERE id=$idEtapa6";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
                http_response_code(500);
                echo json_encode(sqlsrv_errors()[0]['message']);
            } else {
                http_response_code(200);
                echo json_encode("Se actualizo la información correctamente");
            }

    }

    // ------------ ETAPA 7 ---------------
    function saveEtapa7(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $noempEtapa7 = $_POST['noempEtapa7'];
        $folioenc = $_POST['folioenc'];

        $query = "INSERT INTO tblIncidenciasEncEtapa7(noemp, folioenc) VALUES (?, ?)";
        $result = sqlsrv_query($conn, $query, array(
            $noempEtapa7,
            $folioenc
        ));

        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardo la información correctamente");
        }
    }

    function tblEtapa7(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $folioenc = $_GET['folioenc'];

        $query = "SELECT tblIncEnc7.*, tblEmpleados.Nombre AS Nombre, tblDepartamentos.NombreDepto as departamento, 
                    tblPuestos.nombre as puesto, tblIncEnc.NoReporte 
                    FROM tblIncidenciasEncEtapa7 tblIncEnc7
                    INNER JOIN  tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblIncEnc7.folioenc
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIncEnc7.noemp
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
                    INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblEmpleados.Puesto 
                    WHERE folioenc=" . $folioenc;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'noemp' => $row['noemp'],
                'Nombre' => $row['Nombre'],
                'departamento' => $row['departamento'],
                'puesto' => $row['puesto'],
                'folio' => $row['folioenc'],
                'NoReporte' => $row['NoReporte']
            ]);
        }
        echo json_encode($array);

    }

    function dataForEditEtapa7(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "SELECT tblIncEnc7.*, tblEmpleados.Nombre AS Nombre,tblDepartamentos.NombreDepto as departamento,
                    tblPuestos.nombre as puesto
                    FROM tblIncidenciasEncEtapa7 tblIncEnc7 
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIncEnc7.noemp
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
                    INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblEmpleados.Puesto
                    WHERE tblIncEnc7.id = $id";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function actualizarEtapa7(){
        $idEtapa7 = $_POST["idEtapa7"];
        $noempEtapa7 = $_POST["noempEtapa7"];
        $folioenc = $_POST["folioenc"];

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $array = array(
           $noempEtapa7,
           $folioenc,
           $idEtapa7
        );

        $query = "UPDATE tblIncidenciasEncEtapa7 SET noemp=?, folioenc=?
                WHERE id=$idEtapa7";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
                http_response_code(500);
                echo json_encode(sqlsrv_errors()[0]['message']);
            } else {
                http_response_code(200);
                echo json_encode("Se actualizo la información correctamente");
            }

    }

    //-------------- ETAPA 8 ----------------
    function saveEtapa8(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");

        $noEmpEtapa8 = $_POST['noEmpEtapa8'];
        $tipo = $_POST['tipo'];
        $folio = $_POST['folio'];

        $query = "INSERT INTO tblIncidenciasEncEtapa8(noemp, tipo, folioenc) VALUES (?, ?, ?)";

        $result = sqlsrv_query($conn, $query, array(
            $noEmpEtapa8,
            $tipo,
            $folio
        ));

        if ($result === false) {
            http_response_code(500);
            echo json_encode(sqlsrv_errors()[0]['message']);
        } else {
            http_response_code(200);
            echo json_encode("Se guardo la información correctamente");
        }
    }

    function tblEtapa8(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $folioenc = $_GET['folioenc'];

        $query = "  SELECT tblIncE8.*, tblEmpleados.Nombre AS Nombre, tblDepartamentos.NombreDepto as departamento, 
                    tblIncTipEv.tipoEvaluador, tblIncEnc.NoReporte
                    FROM tblIncidenciasEncEtapa8 tblIncE8
                    INNER JOIN  tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblIncE8.folioenc
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIncE8.noemp
                    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
                    INNER JOIN tblIncidenciasTipoEvaluador tblIncTipEv On tblIncTipEv.id = tblIncE8.tipo
                    WHERE folioenc=" . $folioenc;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while($row = sqlsrv_fetch_array($result)){
            array_push($array, [
                'id' => $row['id'],
                'noemp' => $row['noemp'],
                'Nombre' => $row['Nombre'],
                'departamento' => $row['departamento'],
                'tipoEvaluador' => $row['tipoEvaluador'],
                'NoReporte' => $row['NoReporte']
            ]);
        }
        echo json_encode($array);
    }

    function dataForEditEtapa8(){
        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $id = $_GET['id'];
        $query = "  SELECT tblIncE8.*, tblEmpleados.Nombre AS Nombre, tblDepartamentos.NombreDepto as departamento, 
                            tblIncTipEv.tipoEvaluador, tblIncEnc.NoReporte
                            FROM tblIncidenciasEncEtapa8 tblIncE8
                            INNER JOIN  tblIncidenciasEnc tblIncEnc ON tblIncEnc.id = tblIncE8.folioenc
                            INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblIncE8.noemp
                            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
                            INNER JOIN tblIncidenciasTipoEvaluador tblIncTipEv On tblIncTipEv.id = tblIncE8.tipo
                            WHERE tblIncE8.id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function actualizarEtapa8(){
        $idEtapa8 = $_POST["idEtapa8"];
        $noEmpEtapa8 = $_POST["noEmpEtapa8"];
        $tipo = $_POST['tipo'];
        $folio = $_POST["folio"];

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $array = array(
           $noEmpEtapa8,
           $tipo,
           $folio,
           $idEtapa8
        );

        $query = "UPDATE tblIncidenciasEncEtapa8 SET noemp=?, tipo=?, folioenc=?
                WHERE id=$idEtapa8";
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
                http_response_code(500);
                echo json_encode(sqlsrv_errors()[0]['message']);
            } else {
                http_response_code(200);
                echo json_encode("Se actualizo la información correctamente");
            }

    }

    // -------- Tabla Reporte Incidencias ----------
    function tblReporteIncidencias() {
        // $etapa = $_POST["etapa"];
        $fechaInicial = $_POST["fechaInicial"];
        $fechaFinal = $_POST["fechaFinal"];
        $departamento = $_POST["departamento"];

        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $query = "SELECT ie.id AS Folio, ie.fecha, ie.noemgenero AS NumeroEmpleado,
                    e.ApellidoPaterno, e.ApellidoMaterno, e.Nombres AS NombreEmpleado,
                    p.nombre AS Area, depto.NombreDepto AS Departamento, ie.noempimplicado AS EmpleadoImplicado, 
                    emp.ApellidoPaterno AS APaternoImplicado,	emp.ApellidoMaterno AS AMaternoImplicado,	emp.Nombres AS NombresImplicado,
                    puesto.nombre AS AreaImplicado,	deptoImp.NombreDepto AS DepartamentoImplicado, i.Incidencia AS SubClasificacion,
                    ic.Clasificacion, iv.Version,	ie.descripcion AS Evento,	ae.AntEmpresa, ap.AntPuesto,
                    ie.diasincapacidad,	ie.diastrabajo,	tc.TipContacto,	tl.TipLesion,	ie.provoco,
                    cuerpo.ParCuerp AS ParteCuerpoAfectada,	s.Severidad, pro.Probabilidad,
                    f.Frecuencia, per.Personas, ie.lesion, ie.equipos
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
                    WHERE ie.fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND ie.departamento = '$departamento'";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
        array_push($array, [
            "Folio"=> $row["Folio"],
            "fecha"=> $row["fecha"]->format('Y-m-d'),
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
            "lesion"=> $row["lesion"],
            "equipos"=> $row["equipos"],
        ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array); 

    }

    function eliminarRegistroIncidencia() {}

    
}

class AccionesCorrectivas{
    function tblAccionesCorrectivas() {
        $fechaInicial = $_POST["fechaInicial"];
        $fechaFinal = $_POST["fechaFinal"];
        $departamento = $_POST["departamento"];


        $connobj = new ClassConexion();
        $conn = $connobj->conexion("TLX003MXDB");
        $query = "SELECT e4.id, ic.Comportamiento, icausa.CauInmediata, e4.porquecausa,
              ibasica.CauBasica, e4.porque1, iraiz.CausaRaiz, e4.porqueraiz,
              e4.accioncorrectiva, e4.responsableetapa4, tble.Nombre, tble.NombreDepartamento AS NoDepto, 
              tblDepartamentos.NombreDepto, e4.fechaac,
              e4.fechasave, e4.folioenc
            FROM 
              tblIncidenciasEncEtapa4 e4
            INNER JOIN TLX032MXDB.dbo.tblEmpleados tble ON e4.responsableetapa4 = tble.NoEmp
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasComportamiento ic ON e4.comportamiento = ic.NoComportamiento
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasCauInmediata icausa ON e4.causainmediata = icausa.NoCauInmediata
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasCauBasica ibasica ON e4.causabasica = ibasica.NoCauBasica
            INNER JOIN TLX003MXDB.dbo.tblIncidenciasCausaRaiz iraiz ON e4.causaraiz = iraiz.NoCausaRaiz
            LEFT JOIN TLX009MXDB.dbo.tblDepartamentos On tblDepartamentos.NoDepto = tble.NombreDepartamento
            WHERE e4.fechasave BETWEEN '$fechaInicial' AND '$fechaFinal' AND tblDepartamentos.NoDepto = '$departamento'
            ORDER BY fechasave ASC
            ";

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
            'NoDepto'=> $row["NoDepto"],
            "NombreDepto"=> $row["NombreDepto"],
            "fechaac"=> $row["fechaac"],
            "fechasave"=> $row["fechasave"],
            "folioenc"=> $row["folioenc"],
        ]);
        }

        sqlsrv_close($conn);
        echo json_encode($array); 
    }
}

if (isset($_GET['saveIncidencia'])) {
    $incidencia = new Incidencia();
    $incidencia->saveIncidencia();
} else if (isset($_GET['tblEncabezado'])) {
    $incidencia = new Incidencia();
    $incidencia->tblEncabezado();
} else if (isset($_GET['saveIncidenciaEtapa3'])) {
    $incidencia = new Incidencia();
    $incidencia->saveIncidenciaEtapa3();
} else if (isset($_GET['saveEtapa4'])) {
    $incidencia = new Incidencia();
    $incidencia->saveEtapa4();
} else if (isset($_GET['tblEtapa3'])) {
    $incidencia = new Incidencia();
    $incidencia->tblEtapa3();
} else if (isset($_GET['tblEtapa4'])) {
    $incidencia = new Incidencia();
    $incidencia->tblEtapa4();
} else if (isset($_GET['saveEtapa5'])) {
    $incidencia = new Incidencia();
    $incidencia->saveEtapa5();
} else if (isset($_GET['tbletapa5'])) {
    $incidencia = new Incidencia();
    $incidencia->tbletapa5();
} else if (isset($_GET['saveEtapa6'])) {
    $incidencia = new Incidencia();
    $incidencia->saveEtapa6();
} else if (isset($_GET['tbletapa6'])) {
    $incidencia = new Incidencia();
    $incidencia->tbletapa6();
} else if (isset($_GET['dataForEditEtapa3'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEtapa3();
} else if (isset($_GET['actualizarIncidenciaEtapa3'])) {
    $incidencia = new Incidencia();
    $incidencia->actualizarIncidenciaEtapa3();
} else if (isset($_GET['dataForEditEtapa4'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEtapa4();
} else if (isset($_GET['actualizarEtapa4'])) {
    $incidencia = new Incidencia();
    $incidencia->actualizarEtapa4();
} else if (isset($_GET['dataForEditEtapa5'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEtapa5();
} else if (isset($_GET['actualizarEtapa5'])) {
    $incidencia = new Incidencia();
    $incidencia->actualizarEtapa5();
} else if (isset($_GET['dataForEditEtapa6'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEtapa6();
} else if (isset($_GET['actualizarEtapa6'])) {
    $incidencia = new Incidencia();
    $incidencia->actualizarEtapa6();
} else if (isset($_GET['tblReporteIncidencias'])) {
    $incidencia = new Incidencia();
    $incidencia->tblReporteIncidencias();
} else if (isset($_GET['dataForEditEncEtapa'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEncEtapa();
} else if (isset($_GET['updateIncidencia'])) {
    $incidencia = new Incidencia();
    $incidencia->updateIncidencia();
} else if (isset($_GET['saveEtapa7'])) {
    $incidencia = new Incidencia();
    $incidencia->saveEtapa7();
} else if (isset($_GET['tbletapa7'])) {
    $incidencia = new Incidencia();
    $incidencia->tbletapa7();
} else if (isset($_GET['dataForEditEtapa7'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEtapa7();
} else if (isset($_GET['actualizarEtapa7'])) {
    $incidencia = new Incidencia();
    $incidencia->actualizarEtapa7();
} else if (isset($_GET['saveEtapa8'])) {
    $incidencia = new Incidencia();
    $incidencia->saveEtapa8();
} else if (isset($_GET['tbletapa8'])) {
    $incidencia = new Incidencia();
    $incidencia->tbletapa8();
} else if (isset($_GET['dataForEditEtapa8'])) {
    $incidencia = new Incidencia();
    $incidencia->dataForEditEtapa8();
} else if (isset($_GET['actualizarEtapa8'])) {
    $incidencia = new Incidencia();
    $incidencia->actualizarEtapa8   ();
} else if(isset($_GET['eliminarRegistroIncidencia'])) {
    $incidencia = new Incidencia();
    $incidencia->eliminarRegistroIncidencia();
}

if(isset($_GET['tblAccionesCorrectivas'])) {
    $accion = new AccionesCorrectivas();
    $accion->tblAccionesCorrectivas();
}


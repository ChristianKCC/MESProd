<?php
require_once "..\..\conexion.php";

class Incidencias
{
    public function EncabezadoE1($folio)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX003MXDB');
        $queryEnc = "SELECT 
            ie.id AS Folio, ie.fecha, ie.noemgenero AS NumeroEmpleado, e.ApellidoPaterno, e.ApellidoMaterno, e.Nombres AS NombreEmpleado,
            p.nombre AS Area, depto.NombreDepto AS Departamento, ie.noempimplicado AS EmpleadoImplicado, emp.ApellidoPaterno AS APaternoImplicado,
            emp.ApellidoMaterno AS AMaternoImplicado, emp.Nombres AS NombresImplicado, puesto.nombre AS AreaImplicado, deptoImp.NombreDepto AS DepartamentoImplicado,
            i.Incidencia AS SubClasificacion, ic.Clasificacion, iv.Version, ie.descripcion AS Evento, ae.AntEmpresa, ap.AntPuesto, ie.diasincapacidad, ie.diastrabajo,
            tc.TipContacto, tl.TipLesion, ie.provoco, cuerpo.ParCuerp AS ParteCuerpoAfectada, s.Severidad, pro.Probabilidad, f.Frecuencia, per.Personas, ie.ruta AS Evidencia,
            ie.lesion, ie.equipos, ie.NoReporte
            FROM tblIncidenciasEnc ie
            LEFT JOIN TLX032MXDB.dbo.tblEmpleados emp ON ie.noempimplicado = emp.NoEmp
            LEFT JOIN TLX009MXDB.dbo.tblDepartamentos deptoImp ON emp.NombreDepartamento = deptoImp.NoDepto
            LEFT JOIN TLX009MXDB.dbo.tblPuestos puesto ON emp.Puesto = puesto.id
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasPersonas per ON per.NoPersonas = ie.numafectados
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasFrecuencia f ON f.NoFrecuencia = ie.frecuencia
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasProbabilidad pro ON pro.NoProbabilidad = ie.probabilidad
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasSeveridad s ON s.NoSeveridad = ie.severidad
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasCuerpAfectada cuerpo ON cuerpo.NoParCuerpo = ie.parteafectada
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasTipLesion tl ON tl.NoTipLesion = ie.tipolesion
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasTipContacto tc ON tc.NoTipContacto = ie.tipocontacto
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasAntPuesto ap ON ap.NoAntPuesto = ie.antiguedadpuesto
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasAntEmpresa ae ON ae.NoantEmpresa = ie.antiguedadempresa
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasVersion iv ON iv.id = ie.vesion
            LEFT JOIN TLX003MXDB.dbo.tblIncidenciasClasificacion ic ON ic.NoClasificacion = ie.clasificacion
            LEFT JOIN TLX003MXDB.dbo.tblIncidencias i ON ie.incidencia = i.NoInci
            LEFT JOIN TLX032MXDB.dbo.tblEmpleados e ON ie.noemgenero = e.NoEmp
            LEFT JOIN TLX009MXDB.dbo.tblPuestos p ON e.Puesto = p.id 
            LEFT JOIN TLX009MXDB.DBO.tblDepartamentos depto ON e.NombreDepartamento = depto.NoDepto
            WHERE ie.id = '$folio'";

        $result = sqlsrv_query($conn, $queryEnc);
        $datosempoarray = array();
        $row = sqlsrv_fetch_array($result);

        $fechaFormateada = isset($row['fecha']) && $row['fecha'] instanceof DateTime
            ? $row['fecha']->format('Y-m-d')
            : null;

        
        array_push(
            $datosempoarray,
            ['Serie'=>$row['Folio'],
            'Dia' => $row['fecha']->format('Y-m-d'),
            'NoEmp'=>$row['NumeroEmpleado'],
            'NoImp'=>$row['EmpleadoImplicado'],
            'APP'=>$row['ApellidoPaterno'],
            'APM'=>$row['ApellidoMaterno'],
            'NombreE'=>$row['NombreEmpleado'],
            'APPImp'=>$row['APaternoImplicado'],
            'APMImp'=>$row['AMaternoImplicado'],
            'NombreImp'=>$row['NombresImplicado'],
            'NoArea'=>$row['Area'],
            'Depto'=>$row['Departamento'],
            'AreaImp'=>$row['AreaImplicado'],
            'DepImp'=>$row['DepartamentoImplicado'],
            'SubInci'=>$row['SubClasificacion'],
            'ClaseInci'=>$row['Clasificacion'],
            'Ver'=>$row['Version'],
            'Desc'=>$row['Evento'],
            'AEmpresa'=>$row['AntEmpresa'],
            'APuesto'=>$row['AntPuesto'],
            'Contacto'=>$row['TipContacto'],
            'Lesion'=>$row['TipLesion'],
            'ParteAfectada'=>$row['ParteCuerpoAfectada'],
            'Provocacion'=>$row['provoco'],
            'Incapa'=>$row['diasincapacidad'],
            'Trabajo'=>$row['diastrabajo'],
            'Sev'=>$row['Severidad'],
            'Prob'=>$row['Probabilidad'],
            'Frec'=>$row['Frecuencia'],
            'Afectados'=>$row['Personas'],
            'Imagen'=>$row['Evidencia'],
            'CheckLesion'=>$row['lesion'],
            'CheckEquipos'=>$row['equipos'],
            'NoReporte'=> $row['NoReporte'],
        ]);
        return $datosempoarray;
    }
    public function Etapa3($folio) {
    $conexion = new ClassConexion();
    $conn = $conexion->conexion('TLX003MXDB');

    $queryEtapa3 = "SELECT 
        e3.EventosPrev,
        e3.Eventofalla,
        e3.danoequipo,
        e3.suspension,
        e3.producto,
        e3.material,
        e3.otro,
        e3.otrodesc,
        e3.desc1,
        e3.resp1,
        e3.fecha1,
        emp1.Nombre AS NombreResponsable1,
        e3.desc2,
        e3.resp2,
        emp2.Nombre AS NombreResponsable2,
        e3.fecha2,
        e3.desc3,
        e3.resp3,
        emp3.Nombre AS NombreResponsable3,
        e3.fecha3,
        e3.fechasave,
        e3.folioenc
    FROM tblIncidenciasEncEtapa3 e3
    LEFT JOIN TLX032MXDB.dbo.tblEmpleados emp1 ON e3.resp1 = emp1.NoEmp
    LEFT JOIN TLX032MXDB.dbo.tblEmpleados emp2 ON e3.resp2 = emp2.NoEmp
    LEFT JOIN TLX032MXDB.dbo.tblEmpleados emp3 ON e3.resp3 = emp3.NoEmp
    WHERE e3.folioenc = '$folio'";

    $result3 = sqlsrv_query($conn, $queryEtapa3);
    $datosetapa3 = array();
    $rowE3 = sqlsrv_fetch_array($result3);

    if (!$rowE3) {
        return [['error' => 'No se encontraron datos para el folio: ' . $folio]];
    }

    // Formatear fechas si existen
    $fecha1 = isset($rowE3['fecha1']) && $rowE3['fecha1'] instanceof DateTime ? $rowE3['fecha1']->format('Y-m-d') : null;
    $fecha2 = isset($rowE3['fecha2']) && $rowE3['fecha2'] instanceof DateTime ? $rowE3['fecha2']->format('Y-m-d') : null;
    $fecha3 = isset($rowE3['fecha3']) && $rowE3['fecha3'] instanceof DateTime ? $rowE3['fecha3']->format('Y-m-d') : null;
    $fechasave = isset($rowE3['fechasave']) && $rowE3['fechasave'] instanceof DateTime ? $rowE3['fechasave']->format('Y-m-d') : null;

    array_push($datosetapa3, [
        'FolioEtapa3'      => $rowE3['folioenc'],
        'Previos'          => $rowE3['EventosPrev'],
        'Falla'            => $rowE3['Eventofalla'],
        'Daños'            => $rowE3['danoequipo'],
        'Sus'              => $rowE3['suspension'],
        'Prod'             => $rowE3['producto'],
        'Mat'              => $rowE3['material'],
        'Observacion'      => $rowE3['otro'],
        'Descripcion'      => $rowE3['otrodesc'],
        'Descrip1'         => $rowE3['desc1'],
        'Responsable1'     => $rowE3['resp1'],
        'Dia1'             => $fecha1,
        'Descrip2'         => $rowE3['desc2'],
        'Responsable2'     => $rowE3['resp2'],
        'Dia2'             => $fecha2,
        'Descrip3'         => $rowE3['desc3'],
        'Responsable3'     => $rowE3['resp3'],
        'Dia3'             => $fecha3,
        'NombreEmpleado'   => $rowE3['NombreResponsable1'],
        'NombreEmpleado2'  => $rowE3['NombreResponsable2'],
        'NombreEmpleado3'  => $rowE3['NombreResponsable3'],
        'DiaGuardado'      => $fechasave
    ]);

        return $datosetapa3;
    }

    public function Etapa4($folio) {
    $conexion = new ClassConexion();
    $conn = $conexion->conexion('TLX003MXDB');

    $queryEtapa4 = "SELECT e4.id,
        ic.Comportamiento,
        icausa.CauInmediata,
        e4.porquecausa,
        ibasica.CauBasica,
        e4.porque1,
        iraiz.CausaRaiz,
        e4.porqueraiz,
        e4.accioncorrectiva,
        e4.responsableetapa4,
        emp.Nombre AS nombResp,
        e4.fechaac,
        e4.accioncorrectiva2,
        e4.responsableetapa42,
        tblEmp2.Nombre AS Nombre2,
        e4.fechaac2,
        e4.accioncorrectiva3,
        e4.responsableetapa43,
        tblEmp3.Nombre AS Nombre3,
        e4.fechaac3,
        e4.accioncorrectiva4,
        e4.responsableetapa44,
		tblEmp4.Nombre AS Nombre4,
        e4.fechaac4,
        e4.accioncorrectiva5,
        e4.responsableetapa45,
        tblEmp5.Nombre AS Nombre5,
        e4.fechaac5,
        e4.folioenc
    FROM tblIncidenciasEncEtapa4 e4
        INNER JOIN TLX003MXDB.dbo.tblIncidenciasComportamiento ic ON e4.comportamiento = ic.NoComportamiento
        INNER JOIN TLX003MXDB.dbo.tblIncidenciasCauInmediata icausa ON e4.causainmediata = icausa.NoCauInmediata
        INNER JOIN TLX003MXDB.dbo.tblIncidenciasCauBasica ibasica ON e4.causabasica = ibasica.NoCauBasica
        INNER JOIN TLX003MXDB.dbo.tblIncidenciasCausaRaiz iraiz ON e4.causaraiz = iraiz.NoCausaRaiz
        INNER JOIN TLX032MXDB.dbo.tblEmpleados emp ON e4.responsableetapa4 = emp.NoEmp
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp2 ON tblEmp2.NoEmp = e4.responsableetapa42
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp3 ON tblEmp3.NoEmp = e4.responsableetapa43
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp4 ON tblEmp4.NoEmp = e4.responsableetapa44
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp5 ON tblEmp5.NoEmp = e4.responsableetapa45
    WHERE e4.folioenc = '$folio'";

    $result4 = sqlsrv_query($conn, $queryEtapa4);
    $datosetapacuatro = array();

    if (!$result4 || !($rowE4 = sqlsrv_fetch_array($result4, SQLSRV_FETCH_ASSOC))) {
        return [['error' => 'No se encontraron datos para el folio: ' . $folio]];
    }

    // Validar fechas
    $fechaAccidente1 = isset($rowE4['fechaac']) && $rowE4['fechaac'] instanceof DateTime
        ? $rowE4['fechaac']->format('Y-m-d')
        : null;
    $fechaAccidente2 = isset($rowE4['fechaac2']) && $rowE4['fechaac2'] instanceof DateTime
        ? $rowE4['fechaac2']->format('Y-m-d')
        : null;
    $fechaAccidente3 = isset($rowE4['fechaac3']) && $rowE4['fechaac3'] instanceof DateTime
        ? $rowE4['fechaac3']->format('Y-m-d')
        : null;
    $fechaAccidente4 = isset($rowE4['fechaac4']) && $rowE4['fechaac4'] instanceof DateTime
        ? $rowE4['fechaac4']->format('Y-m-d')
        : null;
    $fechaAccidente5 = isset($rowE4['fechaac5']) && $rowE4['fechaac5'] instanceof DateTime
        ? $rowE4['fechaac5']->format('Y-m-d')
        : null;

    array_push($datosetapacuatro, [
        'Folio'             => $rowE4['folioenc'] ?? null,
        'Comp'              => $rowE4['Comportamiento'] ?? null,
        'Causa'             => $rowE4['CauInmediata'] ?? null,
        'Quepaso1'          => $rowE4['porquecausa'] ?? null,
        'Basica'            => $rowE4['CauBasica'] ?? null,
        'Quepaso2'          => $rowE4['porque1'] ?? null,
        'Raiz'              => $rowE4['CausaRaiz'] ?? null,
        'Quepaso3'          => $rowE4['porqueraiz'] ?? null,
        'Correcciones'      => $rowE4['accioncorrectiva'] ?? null,
        'NoEmp'             => $rowE4['responsableetapa4'] ?? null,
        'NombreResponsable' => $rowE4['nombResp'] ?? null,
        'DiaAccidente'      => $fechaAccidente1,
        'Correccion2'       => $rowE4['accioncorrectiva2'] ?? null,
        'responsableetapa42'=> $rowE4['responsableetapa42'] ?? null,
        'NombreResponsable2'=> $rowE4['Nombre2'] ?? null,
        'DiaAccidente2'     => $fechaAccidente2,
        'Correccion3'       => $rowE4['accioncorrectiva3'] ?? null,
        'responsableetapa43'=> $rowE4['responsableetapa43'] ?? null,
        'NombreResponsable3'=> $rowE4['Nombre3'] ?? null,
        'DiaAccidente3'     => $fechaAccidente3,
        'Correccion4'       => $rowE4['accioncorrectiva4'] ?? null,
        'responsableetapa44'=> $rowE4['responsableetapa44'] ?? null,
        'NombreResponsable4'=> $rowE4['Nombre4'] ?? null,
        'DiaAccidente4'     => $fechaAccidente4,
        'Correccion5'       => $rowE4['accioncorrectiva5'] ?? null,
        'responsableetapa45'=> $rowE4['responsableetapa45'] ?? null,
        'NombreResponsable5'=> $rowE4['Nombre5'] ?? null,
        'DiaAccidente5'     => $fechaAccidente5,
    ]);

    return $datosetapacuatro;
    }


    public function Etapa5($folio){
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX003MXDB');
        $queryEtapa5 = "SELECT
                        e5.incprisa,
                        e5.incojostarea,
                        e5.frustracion,
                        e5.mente,
                        e5.fatiga,
                        e5.peligro,
                        e5.riesgo,
                        e5.equilibrio,
                        e5.interaccion1 AS ColabImp,
                        e5.interaccion2 AS AccionesSeguras,
                        e5.interaccion3 AS PerObservada,
                        e5.interaccion4 AS Retroalimentacion,
                        e5.interaccion5 AS Experiencia,
                        e5.interaccion6 AS Seguimiento,
                        e5.riesgos1 AS IncOcurrido,
                        e5.riesgos1porque AS Descripcion1,
                        e5.riesgos2 AS RiesgoMaquina,
                        e5.riesgos2porque AS Descripcion2,
                        e5.riesgos3 AS AnalisisRiesgos,
                        e5.riesgos3porque AS Descripcion3,
                        e5.riesgos4 AS EscenarioRiesgo,
                        e5.riesgos4porque AS Descripcion4,
                        e5.folioenc
                    FROM tblIncidenciasEncEtapa5 e5
                    WHERE e5.folioenc='$folio'";

        $result5 = sqlsrv_query($conn, $queryEtapa5);
        $datosEtapa5 = array();

        $rowE5 = sqlsrv_fetch_array($result5);
        
        if ($rowE5 !== null) {
            array_push($datosEtapa5, [
                "Folio" => $rowE5["folioenc"],
                "Prisa" => $rowE5["incprisa"],
                "OjosTarea" => $rowE5["incojostarea"],
                "Frustracion" => $rowE5["frustracion"],
                "Mente" => $rowE5["mente"],
                "Fatiga" => $rowE5["fatiga"],
                "Peligro" => $rowE5["peligro"],
                "Riesgo" => $rowE5["riesgo"],
                "Equilibrio" => $rowE5["equilibrio"],
                "ColabImp" => $rowE5["ColabImp"],
                "AccionesSeguras" => $rowE5["AccionesSeguras"],
                "PerObservada" => $rowE5["PerObservada"],
                "Retroalimentacion" => $rowE5["Retroalimentacion"],
                "Experiencia" => $rowE5["Experiencia"],
                "Seguimiento" => $rowE5["Seguimiento"],
                "IncOcurrido" => $rowE5["IncOcurrido"],
                "Descripcion1" => $rowE5["Descripcion1"],
                "RiesgoMaquina" => $rowE5["RiesgoMaquina"],
                "Descripcion2" => $rowE5["Descripcion2"],
                "AnalisisRiesgos" => $rowE5["AnalisisRiesgos"],
                "Descripcion3" => $rowE5["Descripcion3"],
                "EscenarioRiesgo" => $rowE5["EscenarioRiesgo"],
                "Descripcion4" => $rowE5["Descripcion4"], 
            ]);
        }


        return $datosEtapa5;

        
    }

    public function Etapa6($folio){
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX003MXDB');
        $queryEtapa6 = "SELECT e6.sistemagestion,tblIncidenciasElementosTipo.tipoelemento,  
                        e6.sistemagestionsub, tblIncidenciasElementos.elemento, e6.sistemagestionporque
                        FROM tblIncidenciasEncEtapa6 e6
                        INNER JOIN tblIncidenciasElementosTipo ON tblIncidenciasElementosTipo.id= e6.sistemagestion
                        INNER JOIN tblIncidenciasElementos ON tblIncidenciasElementos.id= e6.sistemagestionsub
                        WHERE e6.folioenc='$folio'";
        $result6 = sqlsrv_query($conn, $queryEtapa6);
        $datosEtapa6 = array();

        $rowE6 = sqlsrv_fetch_array($result6);
        if ($rowE6 !== null){
             array_push($datosEtapa6, [
                "sistemagestion"=> $rowE6["sistemagestion"],
                "tipoelemento"=> $rowE6["tipoelemento"],
                "sistemagestionsub"=> $rowE6["sistemagestionsub"],
                "elemento"=> $rowE6["elemento"],
                "sistemagestionporque"=> $rowE6["sistemagestionporque"],
            ]);

        }        

        return $datosEtapa6;
    }

    public function Etapa7($folio){
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX003MXDB');
        $queryEtapa7 = "SELECT e7.noemp, tblEmp.Nombre, tblDepartamentos.NombreDepto, tblPuestos.nombre AS NombrePuesto, e7.folio 
                        FROM tblIncidenciasEncEtapa7 e7
                        INNER JOIN TLX032MXDB.dbo.tblEmpleados tblEmp ON tblEmp.NoEmp = e7.noemp
                        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmp.NombreDepartamento
                        INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id=tblEmp.Puesto 
                        WHERE e7.folio = '$folio'";
        $result7 = sqlsrv_query($conn, $queryEtapa7);
        $datosEtapa7 = array();

        $rowE7 =sqlsrv_fetch_array($result7);
        if($rowE7 !== null){
            array_push($datosEtapa7, [
                'noemp' => $rowE7['noemp'],
                'Nombre' => $rowE7['Nombre'],
                'NombreDepto' => $rowE7['NombreDepto'],
                'NombrePuesto' => $rowE7['NombrePuesto']
            ]);
        }
        return $datosEtapa7;
    }

    public function Etapa8($folio){
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX003MXDB');
        $queryEtapa7 = "SELECT e8.noemp, tblEmp.Nombre, tblDepartamentos.NombreDepto, 
                        tblIncTipEv.tipoEvaluador, e8.folio 
                        FROM tblIncidenciasEncEtapa8 e8
                        INNER JOIN TLX032MXDB.dbo.tblEmpleados tblEmp ON tblEmp.NoEmp = e8.noemp
                        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmp.NombreDepartamento
                        INNER JOIN tblIncidenciasTipoEvaluador tblIncTipEv On tblIncTipEv.id = e8.tipo
                        WHERE e8.folio = '$folio'";
        $result7 = sqlsrv_query($conn, $queryEtapa7);
        $datosEtapa7 = array();

        $rowE7 =sqlsrv_fetch_array($result7);
        if($rowE7 !== null){
            array_push($datosEtapa7, [
                'noemp' => $rowE7['noemp'],
                'Nombre' => $rowE7['Nombre'],
                'NombreDepto' => $rowE7['NombreDepto'],
                'tipoEvaluador' => $rowE7['tipoEvaluador']
            ]);
        }
        return $datosEtapa7;
    }
}


?>
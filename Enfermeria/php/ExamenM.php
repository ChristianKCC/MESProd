<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";
class ExamenMedico
{
    function saveExamenMedico()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $medico = $_SESSION['ibm'];
        // Manejo del archivo
        $ruta = "Sin archivo"; // Valor por defecto
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $ruta = "../Files/" . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $ruta);
        }
        $_POST['ruta'] = $ruta;
        $_POST['medico'] = $medico;
        $campos = [
            "noemp",
            "departamento",
            "puesto",
            "maquina",
            "fechanaimiento",
            "lugarnac",
            "domicilio",
            "escolaridad",
            "religion",
            "tiposangre",
            "fechaingreso",
            "fecharevision",
            "problemasdesalud",
            "tomamedicamento",
            "tratamientomedico",
            "enfermedadcronica",
            "tabaquismo",
            "alcoholismo",
            "altfisica",
            "quirurgicos",
            "traumaticos",
            "transfuciones",
            "antivioticos",
            "analgesitos",
            "antiinflamatorios",
            "otrosalergias",
            "alimentacion",
            "aseogeneral",
            "hobbies",
            "otrasactlaborales",
            "incapacidades",
            "diagnostico",
            "diasIncapacidad",
            "secuela",
            "rehabilitacion",
            "trayecto",
            "enfgeneral",
            "accidentetrabajo",
            "enfermedadtrabajo",
            "tos",
            "expectoracion",
            "dolortoracico",
            "taquicardia",
            "disnea",
            "cianosis",
            "edema",
            "obscardio",
            "dolorabdominal",
            "transintestinal",
            "excretaxdia",
            "orofaringeo",
            "abdomen",
            "hernia",
            "obsdigestivo",
            "Observaciongeneral",
            "peso",
            "talla",
            "imc",
            "fc",
            "fr",
            "ta",
            "ojoder",
            "ojoizq",
            "bilateral",
            "pupilas",
            "conciencia",
            "sensible",
            "sueno",
            "reflejo",
            "observacionnervios",
            "audicion",
            "agilidadvisual",
            "reflejos",
            "campimetria",
            "olfato",
            "tacto",
            "cardiopulmonar",
            "tecnicarte",
            "octocerosis",
            "timpano",
            "cardiopulmonar2",
            "tecnicarte2",
            "freccardiaca",
            "viasrespi",
            "camppulmonar",
            "obsgencardio",
            "digestivo",
            "peristalsis",
            "dolor",
            "organomegalias",
            "herniaumbilical",
            "cuello",
            "columnavertebral",
            "movilidad",
            "marcha",
            "rots",
            "puntorlumbar",
            "lasage",
            "bragard",
            "tinel",
            "phanel",
            "trendelemburg",
            "obsmusculo",
            "espnormal",
            "espobstructivo",
            "esprestrictivo",
            "espmixto",
            "d1",
            "d2",
            "d3",
            "d4",
            "d5",
            "d6",
            "d7",
            "d8",
            "i1",
            "i2",
            "i3",
            "i4",
            "i5",
            "i6",
            "i7",
            "i8",
            "diagnostivosano",
            "conductiva",
            "sensorial",
            "mixma",
            "unilateral",
            "bilateralstp",
            "superficial",
            "moderada",
            "profunda",
            "traumadegenerativo",
            "traumamixto",
            "traumaotros",
            "otocerosis",
            "infeccionfaringea",
            "perforanciatimpanica",
            "firma",
            "ClasificacionIMC",
            "UsoLentes",
            "DiagnosAudio",
            "obsAguVisual",
            "ruta",
            "puestoAnterior",
            "horarioAnterior",
            "tiempoTrabajoAnterior",
            "seguridadIndustrial",
            "expoRuidos",
            "expoQuimicos",
            "equipoproteccion",
            "tipoExamen",
            "medico"
        ];

        $firmaBase64 = $_POST['firma'];
        $firmaBase64 = str_replace('data:image/png;base64,', '', $firmaBase64);
        $firmaBase64 = str_replace(' ', '+', $firmaBase64);
        $firmaBinaria = base64_decode($firmaBase64);
        $columnas = implode(", ", $campos);
        $placeholders = implode(", ", array_fill(0, count($campos), "?"));
        $sql = "INSERT INTO tblEnfermeriaExamenM ($columnas) VALUES ($placeholders)";
        $valores = [];
        foreach ($campos as $campo) {
            $valores[] = $_POST[$campo] ?? null;
        }

        $stmt = sqlsrv_query($conn, $sql, $valores);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors()));
        } else {
            http_response_code(200);
            echo "Registro guardado correctamente.";
        }
        sqlsrv_close($conn);
    }

    function tblExamenMedico()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT tblEnfermeriaExamenM.id,tblEmpleados.NoEmp,tblEmpleados.Nombre as nomemp,tblDepartamentos.NombreDepto,tblPuestos.nombre as puesto,
        tblEnfermeriaExamenM.fecharevision,firma,tblEnfermeriaExamenM.fecharevision, ruta, tipoExamen FROM tblEnfermeriaExamenM
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEnfermeriaExamenM.noemp
        INNER JOIN TLX009MXDB.DBO.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        INNER JOIN TLX009MXDB.DBO.tblPuestos ON tblPuestos.id= tblEmpleados.Puesto ORDER BY tblEnfermeriaExamenM.fecharevision DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'],
                "noemp" => $row['NoEmp'],
                "nombre" => $row['nomemp'],
                "departamento" => $row['NombreDepto'],
                "puesto" => $row['puesto'],
                "firma" => $row['firma'],
                "fecha" => $row['fecharevision']->format("Y-m-d"),
                "ruta" => $row['ruta'],
                "tipoExamen" => $row['tipoExamen']
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    // Funcion que extrae la info para generar el reporte de examen medico
    function reporteExamen()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $fechai = $_POST["fechai"];
        $fechaf = $_POST["fechaf"];
        $departamento = $_POST["departamento"];
        $noemp = $_POST["noemp"];
        // $addwhere = "";
        // empty($_POST["noemp"]) ? $addwhere .= "" : $addwhere .= " AND tblEEM.noemp = $noemp";
        // empty($_POST["departamento"]) ? $addwhere .= "" : $addwhere .= " AND tblEEM.departamento = $departamento";
        $query = "SELECT tblEEM.id, tblEEM.noemp, tblEmp.Nombre as Nombre, tblDep.NombreDepto,tblPues.nombre as puesto,
        tblEEM.fecharevision,firma 
        FROM tblEnfermeriaExamenM tblEEM
        LEFT JOIN TLX032MXDB.dbo.tblEmpleados tblEmp ON tblEMP.NoEmp = tblEEM.noemp
        LEFT JOIN TLX009MXDB.dbo.tblDepartamentos tblDep ON tblDep.NoDepto = tblEEM.departamento
        LEFT JOIN TLX009MXDB.dbo.tblPuestos tblPues ON tblPues.id = tblEEM.puesto
        AND tblEEM.noemp = $noemp
        AND tblEEM.departamento = $departamento
        WHERE tblEEM.fecharevision BETWEEN '$fechai' AND DATEADD(DAY,1, '$fechaf') 
        ORDER BY tblEEM.id DESC";

        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row['id'],
                "noemp" => $row['noemp'],
                "nombre" => $row['Nombre'],
                "departamento" => $row['NombreDepto'],
                "puesto" => $row['puesto'],
                "firma" => $row['firma'],
                "fecha" => $row['fecharevision']->format("Y-m-d")
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function dataForEditExamenM()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $id = $_GET["id"];
        $query = "SELECT tblEnfermeriaExamenM.id AS id, tblEnfermeriaExamenM.noemp AS noemp,tblEmpleados.Nombre AS nombre, 
        tblEnfermeriaExamenM.departamento AS departamento, tblEnfermeriaExamenM.puesto AS puesto, tblEnfermeriaExamenM.maquina AS maquina,
        tblEnfermeriaExamenM.fechanaimiento AS fechanac, tblEnfermeriaExamenM.lugarnac AS lugarnac, tblEnfermeriaExamenM.domicilio AS domicilio,
        tblEnfermeriaExamenM.escolaridad AS escolaridad, tblEnfermeriaExamenM.religion AS religion, tblEnfermeriaExamenM.tiposangre AS sangre,
        tblEnfermeriaExamenM.fechaingreso AS fechaing,tblEnfermeriaExamenM.fecharevision AS fecharevision, tblEnfermeriaExamenM.problemasdesalud AS problemassalud, tblEnfermeriaExamenM.tomamedicamento AS tomaMedic,
        tblEnfermeriaExamenM.tratamientomedico AS tratamientoMed, tblEnfermeriaExamenM.enfermedadcronica AS enfermCron, tblEnfermeriaExamenM.tabaquismo AS tabaquismo,
        tblEnfermeriaExamenM.alcoholismo AS alcoholismo, tblEnfermeriaExamenM.altfisica AS altfisica, tblEnfermeriaExamenM.quirurgicos AS quirurgicos,
        tblEnfermeriaExamenM.traumaticos AS traumaticos, tblEnfermeriaExamenM.transfuciones AS transfuciones, tblEnfermeriaExamenM.antivioticos AS antivioticos, 
        tblEnfermeriaExamenM.analgesitos AS analgesitos, tblEnfermeriaExamenM.antiinflamatorios AS antiInfla, tblEnfermeriaExamenM.otrosalergias AS otrosalergias,
        tblEnfermeriaExamenM.alimentacion AS alimentacion, tblEnfermeriaExamenM.aseogeneral AS aseogeneral,
        tblEnfermeriaExamenM.hobbies AS hobbies, tblEnfermeriaExamenM.otrasactlaborales AS otrasAct, tblEnfermeriaExamenM.incapacidades AS inca,
        tblEnfermeriaExamenM.diagnostico AS diagnostico, tblEnfermeriaExamenM.diasIncapacidad AS diasInca, tblEnfermeriaExamenM.secuela AS secuela,
        tblEnfermeriaExamenM.rehabilitacion AS rehab, tblEnfermeriaExamenM.trayecto AS trayecto, tblEnfermeriaExamenM.enfgeneral AS enfgeneral,
        tblEnfermeriaExamenM.accidentetrabajo AS accTrab, tblEnfermeriaExamenM.enfermedadtrabajo AS enfTrab, tblEnfermeriaExamenM.Tos as tos,
        tblEnfermeriaExamenM.expectoracion AS expectoracion, tblEnfermeriaExamenM.dolortoracico AS dolorTora, tblEnfermeriaExamenM.taquicardia AS taquicardia,
        tblEnfermeriaExamenM.disnea AS disnea, tblEnfermeriaExamenM.cianosis AS cianosis, tblEnfermeriaExamenM.edema AS edema,
        tblEnfermeriaExamenM.obscardio AS obscardio, tblEnfermeriaExamenM.dolorabdominal AS dolorAbd, tblEnfermeriaExamenM.transintestinal AS transInst,
        tblEnfermeriaExamenM.excretaxdia AS excretaxdia, tblEnfermeriaExamenM.orofaringeo AS orofaringeo, tblEnfermeriaExamenM.abdomen AS abdomen,
        tblEnfermeriaExamenM.hernia AS hernia, tblEnfermeriaExamenM.obsdigestivo AS obsdigestivo, tblEnfermeriaExamenM.Observaciongeneral AS obsGeneral,
        tblEnfermeriaExamenM.peso AS Peso, tblEnfermeriaExamenM.talla AS talla, tblEnfermeriaExamenM.imc AS imc, tblEnfermeriaExamenM.fc AS fc, 
        tblEnfermeriaExamenM.fr AS fr, tblEnfermeriaExamenM.ta AS ta, tblEnfermeriaExamenM.ojoder AS ojoder, tblEnfermeriaExamenM.ojoizq AS ojoizq,
        tblEnfermeriaExamenM.bilateral AS bilateral, tblEnfermeriaExamenM.pupilas AS pupilas, tblEnfermeriaExamenM.conciencia AS conciencia,
        tblEnfermeriaExamenM.sensible AS sensible, tblEnfermeriaExamenM.sueno AS sueno, tblEnfermeriaExamenM.reflejo AS reflejo,
        tblEnfermeriaExamenM.observacionnervios AS obsNervios, tblEnfermeriaExamenM.audicion AS audicion, tblEnfermeriaExamenM.agilidadvisual AS agiVis,
        tblEnfermeriaExamenM.reflejos AS reflejos, tblEnfermeriaExamenM.campimetria AS campimetria, tblEnfermeriaExamenM.olfato AS olfato,
        tblEnfermeriaExamenM.tacto AS tacto, tblEnfermeriaExamenM.cardiopulmonar AS cardPulm, tblEnfermeriaExamenM.tecnicarte AS tecnicarte,
        tblEnfermeriaExamenM.octocerosis AS octocerosis, tblEnfermeriaExamenM.timpano AS timpano, tblEnfermeriaExamenM.cardiopulmonar2 AS cardPulm2,
        tblEnfermeriaExamenM.tecnicarte2 AS tecnicarte2, tblEnfermeriaExamenM.freccardiaca AS freccCard, tblEnfermeriaExamenM.viasrespi AS viasrespi,
        tblEnfermeriaExamenM.camppulmonar AS campPulm, tblEnfermeriaExamenM.obsgencardio AS obsGenCard,tblEnfermeriaExamenM.peristalsis AS peristalsis,
        tblEnfermeriaExamenM.dolor AS dolor, tblEnfermeriaExamenM.organomegalias AS organomegalias, tblEnfermeriaExamenM.herniaumbilical AS herniaumbilical,
        tblEnfermeriaExamenM.cuello AS cuello, tblEnfermeriaExamenM.columnavertebral AS columVert, tblEnfermeriaExamenM.movilidad AS movilidad,
        tblEnfermeriaExamenM.marcha AS marcha, tblEnfermeriaExamenM.rots AS rots, tblEnfermeriaExamenM.puntorlumbar AS puntorlumbar,
        tblEnfermeriaExamenM.lasage AS lasage, tblEnfermeriaExamenM.bragard AS bragard, tblEnfermeriaExamenM.tinel AS tinel,
        tblEnfermeriaExamenM.phanel AS phanel, tblEnfermeriaExamenM.trendelemburg AS trendelemburg, tblEnfermeriaExamenM.obsmusculo AS obsmusculo,
        tblEnfermeriaExamenM.espnormal AS espnormal, tblEnfermeriaExamenM.espobstructivo AS espobstructivo, tblEnfermeriaExamenM.esprestrictivo AS esprestrictivo, 
        tblEnfermeriaExamenM.espmixto AS espmixto, tblEnfermeriaExamenM.d1 AS d1, tblEnfermeriaExamenM.d2 AS d2, tblEnfermeriaExamenM.d3 AS d3,
        tblEnfermeriaExamenM.d4 AS d4, tblEnfermeriaExamenM.d5 AS d5, tblEnfermeriaExamenM.d6 AS d6, tblEnfermeriaExamenM.d7 AS d7,
        tblEnfermeriaExamenM.d1 AS i1, tblEnfermeriaExamenM.i2 AS i2, tblEnfermeriaExamenM.i3 AS i3, tblEnfermeriaExamenM.i4 AS i4, tblEnfermeriaExamenM.i5 AS i5, 
        tblEnfermeriaExamenM.i6 AS i6, tblEnfermeriaExamenM.i7 AS i7, tblEnfermeriaExamenM.diagnostivosano AS diagSano,
        tblEnfermeriaExamenM.conductiva AS conductiva, tblEnfermeriaExamenM.sensorial AS sensorial, tblEnfermeriaExamenM.mixma AS mixma,
        tblEnfermeriaExamenM.unilateral AS unilateral, tblEnfermeriaExamenM.bilateralstp AS bilateralstp, tblEnfermeriaExamenM.superficial AS superficial,
        tblEnfermeriaExamenM.moderada AS moderada, tblEnfermeriaExamenM.profunda AS profunda, tblEnfermeriaExamenM.traumadegenerativo AS traumDege,
        tblEnfermeriaExamenM.traumamixto AS traumamixto, tblEnfermeriaExamenM.traumaotros AS traumaotros, tblEnfermeriaExamenM.otocerosis AS otocerosis,
        tblEnfermeriaExamenM.infeccionfaringea AS indeccFaring, tblEnfermeriaExamenM.perforanciatimpanica AS perfAtimp, tblEnfermeriaExamenM.ClasificacionIMC AS ClassIMC,
        tblEnfermeriaExamenM.UsoLentes AS UsoLentes, tblEnfermeriaExamenM.DiagnosAudio AS DiagnosAudio, tblEnfermeriaExamenM.obsAguVisual AS obsAguVisual,
        tblEnfermeriaExamenM.firma AS firma, ruta, puestoAnterior, horarioAnterior, tiempoTrabajoAnterior, seguridadIndustrial, expoRuidos, expoQuimicos, equipoproteccion, tipoExamen
        FROM tblEnfermeriaExamenM
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblEnfermeriaExamenM.noemp
        WHERE id = $id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "id" => $row["id"],
                "noemp" => $row["noemp"],
                "nombre" => $row["nombre"],
                "departamento" => $row["departamento"],
                "puesto" => $row["puesto"],
                "maquina" => $row["maquina"],
                "fechanac" => $row["fechanac"]->format("Y-m-d"),
                "lugarnac" => $row["lugarnac"],
                "domicilio" => $row["domicilio"],
                "escolaridad" => $row["escolaridad"],
                "religion" => $row["religion"],
                "sangre" => $row["sangre"],
                "fechaing" => $row["fechaing"]->format("Y-m-d"),
                "fecharevision" => $row["fecharevision"]->format("Y-m-d"),
                "problemassalud" => $row["problemassalud"],
                "tomaMedic" => $row["tomaMedic"],
                "tratamientoMed" => $row["tratamientoMed"],
                "enfermCron" => $row["enfermCron"],
                "tabaquismo" => $row["tabaquismo"],
                "alcoholismo" => $row["alcoholismo"],
                "altfisica" => $row["altfisica"],
                "quirurgicos" => $row["quirurgicos"],
                "traumaticos" => $row["traumaticos"],
                "transfuciones" => $row["transfuciones"],
                "antivioticos" => $row["antivioticos"],
                "analgesitos" => $row["analgesitos"],
                "antiInfla" => $row["antiInfla"],
                "otrosalergias" => $row["otrosalergias"],
                "alimentacion" => $row["alimentacion"],
                "aseogeneral" => $row["aseogeneral"],
                "hobbies" => $row["hobbies"],
                "otrasAct" => $row["otrasAct"],
                "inca" => $row["inca"],
                "diagnostico" => $row["diagnostico"],
                "diasInca" => $row["diasInca"],
                "secuela" => $row["secuela"],
                "rehab" => $row["rehab"],
                "trayecto" => $row["trayecto"],
                "enfgeneral" => $row["enfgeneral"],
                "accTrab" => $row["accTrab"],
                "enfTrab" => $row["enfTrab"],
                "tos" => $row["tos"],
                "expectoracion" => $row["expectoracion"],
                "dolorTora" => $row["dolorTora"],
                "taquicardia" => $row["taquicardia"],
                "disnea" => $row["disnea"],
                "cianosis" => $row["cianosis"],
                "edema" => $row["edema"],
                "obscardio" => $row["obscardio"],
                "dolorAbd" => $row["dolorAbd"],
                "transInst" => $row["transInst"],
                "excretaxdia" => $row["excretaxdia"],
                "orofaringeo" => $row["orofaringeo"],
                "abdomen" => $row["abdomen"],
                "hernia" => $row["hernia"],
                "obsdigestio" => $row["obsdigestivo"],
                "obsGeneral" => $row["obsGeneral"],
                "Peso" => $row["Peso"],
                "talla" => $row["talla"],
                "imc" => $row["imc"],
                "fc" => $row["fc"],
                "fr" => $row["fr"],
                "ta" => $row["ta"],
                "ojoder" => $row["ojoder"],
                "ojoizq" => $row["ojoizq"],
                "bilateral" => $row["bilateral"],
                "pupilas" => $row["pupilas"],
                "conciencia" => $row["conciencia"],
                "sensible" => $row["sensible"],
                "sueno" => $row["sueno"],
                "reflejo" => $row["reflejo"],
                "obsNervios" => $row["obsNervios"],
                "audicion" => $row["audicion"],
                "agiVis" => $row["agiVis"],
                "reflejos" => $row["reflejos"],
                "campimetria" => $row["campimetria"],
                "olfato" => $row["olfato"],
                "tacto" => $row["tacto"],
                "cardPulm" => $row["cardPulm"],
                "tecnicarte" => $row["tecnicarte"],
                "octocerosis" => $row["octocerosis"],
                "timpano" => $row["timpano"],
                "cardPulm2" => $row["cardPulm2"],
                "tecnicarte2" => $row["tecnicarte2"],
                "freccCard" => $row["freccCard"],
                "viasrespi" => $row["viasrespi"],
                "campPulm" => $row["campPulm"],
                "obsGenCard" => $row["obsGenCard"],
                "peristalsis" => $row["peristalsis"],
                "dolor" => $row["dolor"],
                "organomegalias" => $row["organomegalias"],
                "herniaumbilical" => $row["herniaumbilical"],
                "cuello" => $row["cuello"],
                "columVert" => $row["columVert"],
                "movilidad" => $row["movilidad"],
                "marcha" => $row["marcha"],
                "rots" => $row["rots"],
                "puntorlumbar" => $row["puntorlumbar"],
                "lasage" => $row["lasage"],
                "bragard" => $row["bragard"],
                "tinel" => $row["tinel"],
                "phanel" => $row["phanel"],
                "trendelemburg" => $row["trendelemburg"],
                "obsmusculo" => $row["obsmusculo"],
                "espnormal" => $row["espnormal"],
                "espobstructivo" => $row["espobstructivo"],
                "esprestrictivo" => $row["esprestrictivo"],
                "espmixto" => $row["espmixto"],
                "d1" => $row["d1"],
                "d2" => $row["d2"],
                "d3" => $row["d3"],
                "d4" => $row["d4"],
                "d5" => $row["d5"],
                "d6" => $row["d6"],
                "d7" => $row["d7"],
                "i1" => $row["i1"],
                "i2" => $row["i2"],
                "i3" => $row["i3"],
                "i4" => $row["i4"],
                "i5" => $row["i5"],
                "i6" => $row["i6"],
                "i7" => $row["i7"],
                "diagSano" => $row["diagSano"],
                "conductiva" => $row["conductiva"],
                "sensorial" => $row["sensorial"],
                "mixma" => $row["mixma"],
                "unilateral" => $row["unilateral"],
                "bilateralstp" => $row["bilateralstp"],
                "superficial" => $row["superficial"],
                "moderada" => $row["moderada"],
                "profunda" => $row["profunda"],
                "traumDege" => $row["traumDege"],
                "traumamixto" => $row["traumamixto"],
                "traumaotros" => $row["traumaotros"],
                "otocerosis" => $row["otocerosis"],
                "indeccFaring" => $row["indeccFaring"],
                "perfAtimp" => $row["perfAtimp"],
                "ClassIMC" => $row["ClassIMC"],
                "UsoLentes" => $row["UsoLentes"],
                "DiagnosAudio" => $row["DiagnosAudio"],
                "obsAguVisual" => $row["obsAguVisual"],
                "firma" => $row["firma"],
                "ruta"=> $row["ruta"],
                "puestoAnterior" => $row["puestoAnterior"],
                "horarioAnterior" => $row["horarioAnterior"],
                "tiempoTrabajoAnterior" => $row["tiempoTrabajoAnterior"],
                "seguridadIndustrial" => $row["seguridadIndustrial"],
                "expoRuidos" => $row["expoRuidos"],
                "expoQuimicos" => $row["expoQuimicos"],
                "equipoproteccion" => $row["equipoproteccion"],
                "tipoExamen" => $row["tipoExamen"]
            ]);
        }

        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function updateExamenMedico()
    {
        $folio = $_POST["folio"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $medico = $_SESSION['ibm'];

        // Manejo del archivo
        $ruta = null;

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $ruta = "../Files/" . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $ruta);
        } else {
            // Si no se subió archivo, obtener la ruta actual desde la base de datos
            $sqlRuta = "SELECT ruta FROM tblEnfermeriaExamenM WHERE id = ?";
            $stmtRuta = sqlsrv_query($conn, $sqlRuta, [$folio]);
            if ($stmtRuta && $row = sqlsrv_fetch_array($stmtRuta, SQLSRV_FETCH_ASSOC)) {
                $ruta = $row['ruta'];
            } else {
                $ruta = "Sin archivo"; // Solo si no se puede obtener el valor actual
            }
        }

        $_POST['ruta'] = $ruta;
        $_POST['medico'] = $medico;
        $campos = [
            "noemp",
            "departamento",
            "puesto",
            "maquina",
            "fechanaimiento",
            "lugarnac",
            "domicilio",
            "escolaridad",
            "religion",
            "tiposangre",
            "fechaingreso",
            "fecharevision",
            "problemasdesalud",
            "tomamedicamento",
            "tratamientomedico",
            "enfermedadcronica",
            "tabaquismo",
            "alcoholismo",
            "altfisica",
            "quirurgicos",
            "traumaticos",
            "transfuciones",
            "antivioticos",
            "analgesitos",
            "antiinflamatorios",
            "otrosalergias",
            "alimentacion",
            "aseogeneral",
            "hobbies",
            "otrasactlaborales",
            "incapacidades",
            "diagnostico",
            "diasIncapacidad",
            "secuela",
            "rehabilitacion",
            "trayecto",
            "enfgeneral",
            "accidentetrabajo",
            "enfermedadtrabajo",
            "Tos",
            "expectoracion",
            "dolortoracico",
            "taquicardia",
            "disnea",
            "cianosis",
            "edema",
            "obscardio",
            "dolorabdominal",
            "transintestinal",
            "excretaxdia",
            "orofaringeo",
            "abdomen",
            "hernia",
            "obsdigestivo",
            "Observaciongeneral",
            "peso",
            "talla",
            "imc",
            "fc",
            "fr",
            "ta",
            "ojoder",
            "ojoizq",
            "bilateral",
            "pupilas",
            "conciencia",
            "sensible",
            "sueno",
            "reflejo",
            "observacionnervios",
            "audicion",
            "agilidadvisual",
            "reflejos",
            "campimetria",
            "olfato",
            "tacto",
            "cardiopulmonar",
            "tecnicarte",
            "octocerosis",
            "timpano",
            "cardiopulmonar2",
            "tecnicarte2",
            "freccardiaca",
            "viasrespi",
            "camppulmonar",
            "obsgencardio",
            "digestivo",
            "peristalsis",
            "dolor",
            "organomegalias",
            "herniaumbilical",
            "cuello",
            "columnavertebral",
            "movilidad",
            "marcha",
            "rots",
            "puntorlumbar",
            "lasage",
            "bragard",
            "tinel",
            "phanel",
            "trendelemburg",
            "obsmusculo",
            "espnormal",
            "espobstructivo",
            "esprestrictivo",
            "espmixto",
            "d1",
            "d2",
            "d3",
            "d4",
            "d5",
            "d6",
            "d7",
            "d8",
            "i1",
            "i2",
            "i3",
            "i4",
            "i5",
            "i6",
            "i7",
            "i8",
            "diagnostivosano",
            "conductiva",
            "sensorial",
            "mixma",
            "unilateral",
            "bilateralstp",
            "superficial",
            "moderada",
            "profunda",
            "traumadegenerativo",
            "traumamixto",
            "traumaotros",
            "otocerosis",
            "infeccionfaringea",
            "perforanciatimpanica",
            "firma",
            "ClasificacionIMC",
            "UsoLentes",
            "DiagnosAudio",
            "obsAguVisual",
            "ruta",
            "puestoAnterior",
            "horarioAnterior",
            "tiempoTrabajoAnterior",
            "seguridadIndustrial",
            "expoRuidos",
            "expoQuimicos",
            "equipoproteccion",
            "tipoExamen",
            "medico"
        ];

        $setClause = implode(", ", array_map(function ($campo) {
            return "$campo = ?";
        }, $campos));
        // $placeholders = implode(", ", array_fill(0, count($campos), "?"));
        $sql = "UPDATE tblEnfermeriaExamenM SET $setClause WHERE id = $folio";
        $valores = [];
        foreach ($campos as $campo) {
            $valores[] = $_POST[$campo] ?? null;
        }

        $stmt = sqlsrv_query($conn, $sql, $valores);

        if ($stmt === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }


        sqlsrv_close($conn);

    }

    function infoIMC()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT * FROM tblEnfermeriaTipoIMC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "idIMC" => $row["id"],
                "NombreTipo" => $row["Tipo"],
                "Minimo" => $row["minIMC"],
                "Maximo" => $row["maxIMC"],
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }
}


if (isset($_GET['saveExamenMedico'])) {
    $Examen = new ExamenMedico();
    $Examen->saveExamenMedico();
} else if (isset($_GET['tblExamenMedico'])) {
    $Examen = new ExamenMedico();
    $Examen->tblExamenMedico();
} else if (isset($_GET['reporteExamen'])) {
    $Examen = new ExamenMedico();
    $Examen->reporteExamen();
} else if (isset($_GET['dataForEditExamenM'])) {
    $Examen = new ExamenMedico();
    $Examen->dataForEditExamenM();
} else if (isset($_GET['updateExamenMedico'])) {
    $Examen = new ExamenMedico();
    $Examen->updateExamenMedico();
} else if (isset($_GET['infoIMC'])) {
    $Examen = new ExamenMedico();
    $Examen->infoIMC();
}

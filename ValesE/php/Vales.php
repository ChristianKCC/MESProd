<?php
require_once "../../conexion.php";
require_once '../../Session/seguridad.php';
class ValesElectronicos
{
    function ClaseMat()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $addquery = '';
        $valida = 0;
        $clave1 = $_POST['clave1'];
        $clave2 = $_POST['clave2'];
        $clave3 = $_POST['clave3'];
        $clave4 = $_POST['clave4'];
        $maquina = ($_POST['maquinaid'] == '' ? $_SESSION["idmaquina"] : $_POST['maquinaid']);
        ($clave1 != '' || $clave2 != '' || $clave3 != '' || $clave4 != '') && $valida = 1;
        $valida == 1 && $addquery .= 'AND (';
        $clave1 != '' && $addquery .= " tblValeConClavClasMat.NoClave = '$clave1'";
        $clave2 != '' && $addquery .= " OR tblValeConClavClasMat.NoClave = '$clave2'";
        $clave3 != '' && $addquery .= " OR tblValeConClavClasMat.NoClave = '$clave3'";
        $clave4 != '' && $addquery .= " OR tblValeConClavClasMat.NoClave = '$clave4'";
        $valida == 1 && $addquery .= ")";
        $query = "SELECT DISTINCT tblValeEClases.NoClase, tblValeEClases.Descripcion_Clase FROM tblValeEClases
        INNER JOIN tblValeConClavClasMat ON tblValeConClavClasMat.NoClase = tblValeEClases.NoClase WHERE tblValeConClavClasMat.NoMaquina=" . $maquina . "
        $addquery ORDER BY tblValeEClases.Descripcion_Clase ASC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['NoClase' => $row['NoClase'], 'Nombre' => $row['Descripcion_Clase']]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function tblMateriales()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $addquery = '';
        $idclase = $_POST['idclase'];
        $clave1 = $_POST['clave1'];
        $clave2 = $_POST['clave2'];
        $clave3 = $_POST['clave3'];
        $clave4 = $_POST['clave4'];
        $maquina = ($_POST['maquinaid'] == '' ? $_SESSION["idmaquina"] : $_POST['maquinaid']);
        ($clave1 != '' || $clave2 != '' || $clave3 != '' || $clave4 != '') && $valida = 1;
        $valida == 1 && $addquery .= 'AND (';
        $clave1 != '' && $addquery .= " tblValeConClavClasMat.NoClave = '$clave1'";
        $clave2 != '' && $addquery .= " OR tblValeConClavClasMat.NoClave = '$clave2'";
        $clave3 != '' && $addquery .= " OR tblValeConClavClasMat.NoClave = '$clave3'";
        $clave4 != '' && $addquery .= " OR tblValeConClavClasMat.NoClave = '$clave4'";
        $valida == 1 && $addquery .= ")";
        $query = "SELECT DISTINCT tblValeEMateriales.NoMaterial, tblValeEMateriales.NombreMaterial,tblValeEMateriales.CentroCosto,tblValeEMateriales.TiempoMaterial,tblValeEMateriales.TipoMontacargas FROM tblValeEMateriales
        INNER JOIN tblValeConClavClasMat ON tblValeConClavClasMat.NoMaterial = tblValeEMateriales.NoMaterial WHERE tblValeConClavClasMat.NoMaquina=" . $maquina . " 
        AND tblValeConClavClasMat.NoClase=$idclase $addquery";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'NoMaterial' => $row['NoMaterial'],
                'NombreMaterial' => $row['NombreMaterial'],
                'CentroCosto' => $row['CentroCosto'],
                'TiempoMaterial' => $row['TiempoMaterial'],
                'TipoMontacargas' => $row['TipoMontacargas']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function addMaterial()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $idenc    = $_POST['folio'];
        $idmat    = $_POST['idmat'];
        $cantidad = $_POST['cantidad'];

        $idmaquina = $_SESSION["idmaquina"];

        // Agregar aquí los IDs de máquinas que usan decimales (CantidadKgs)
        $maquinasKgs = [140, 141, 142, 143, 144, 145, 146, 147, 148, 149,
                        150, 151, 152, 153, 154, 155, 156, 157, 158];

        $usaKgs = in_array($idmaquina, $maquinasKgs);

        if ($usaKgs) {
            $query = "INSERT INTO tblValeEMaterialesAdd(idValeEnc, idMaterial, CantidadKgs) 
                    VALUES (?, ?, ?)";
        } else {
            $query = "INSERT INTO tblValeEMaterialesAdd(idValeEnc, idMaterial, Cantidad) 
                    VALUES (?, ?, ?)";
        }

        $result = sqlsrv_query($conn, $query, array($idenc, $idmat, $cantidad));
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function addMaterialadmin()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $idenc = $_POST['folio'];
        $idmat = $_POST['idmat'];
        $cantidad = $_POST['cantidad'];
        $estado = 3;
        $query = "INSERT INTO tblValeEMaterialesAdd(idValeEnc,idMaterial,Cantidad,estado) VALUES ($idenc,$idmat,$cantidad,$estado)";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function saveValeElectronico()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $noemp = $_POST['noemp'];
        $turno = $_POST['turno'];
        $clave1 = $_POST['clave1'];
        $clave2 = $_POST['clave2'];
        $clave3 = $_POST['clave3'];
        $clave4 = $_POST['clave4'];
        $query = "INSERT INTO tblValeEEnc(maquina,noemp,turno,clave1,clave2,clave3,clave4) VALUES (?,?,?,?,?,?,?)";
        $result = sqlsrv_query($conn, $query, array($_SESSION["idmaquina"], $noemp, $turno, $clave1, $clave2, $clave3, $clave4));
        if ($result === false)
            die("error al insertar registro");
        $sql = "SELECT @@IDENTITY AS id";
        $stpm = sqlsrv_query($conn, $sql);
        $fila = sqlsrv_fetch_array($stpm, SQLSRV_FETCH_ASSOC);
        $id = $fila['id'];
        $array = array();
        $query = "SELECT * FROM tblValeEEnc WHERE id=$id";
        $stpm = sqlsrv_query($conn, $query);
        while ($row = sqlsrv_fetch_array($stpm)) {
            array_push($array, ['id' => $row['id'], 'folio' => $row['foliocons']]);
        }
        echo json_encode($array);
    }
    function tblMaterialesAgregados()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];

        // $idmaquina = isset($_SESSION["idmaquina"]) ? $_SESSION["idmaquina"] : null;


        // $maquinasKgs = [140, 141, 142, 143, 144, 145, 146, 147, 148, 149,
        //                 150, 151, 152, 153, 154, 155, 156, 157, 158];
        
        $idmaquina = isset($_SESSION["idmaquina"])
            ? $_SESSION["idmaquina"]
            : (isset($_POST["maquinaid"]) ? (int) $_POST["maquinaid"] : null);

        $maquinasKgs = [
            140,
            141,
            142,
            143,
            144,
            145,
            146,
            147,
            148,
            149,
            150,
            151,
            152,
            153,
            154,
            155,
            156,
            157,
            158
        ];

        $usaKgs = $idmaquina !== null && in_array($idmaquina, $maquinasKgs);

        $query = "SELECT tblValeEMateriales.*, tblValeEMaterialesAdd.id as folio,
          tblValeEMaterialesAdd.Cantidad, tblValeEMaterialesAdd.CantidadKgs,
          tblValeEMaterialesAdd.estado as estadomat,
          tblValeEMaterialesAdd.mm2kg, tblValeEMaterialesAdd.envasesrec 
          FROM tblValeEMaterialesAdd 
          INNER JOIN tblValeEMateriales ON tblValeEMateriales.NoMaterial = tblValeEMaterialesAdd.idMaterial 
          WHERE tblValeEMaterialesAdd.idValeEnc = ?";

        $result = sqlsrv_query($conn, $query, array($folio));
        $array = array();

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            array_push($array, [
                'folio'          => $row['folio'],
                'NoMaterial'     => $row['NoMaterial'],
                'NombreMaterial' => $row['NombreMaterial'],
                'CentroCosto'    => $row['CentroCosto'],
                'TiempoMaterial' => $row['TiempoMaterial'],
                'TipoMontacargas'=> $row['TipoMontacargas'],
                'Cantidad'       => $usaKgs ? $row['CantidadKgs'] : $row['Cantidad'],
                'UM'             => $usaKgs ? $row['UMMaterial']  : $row['UM'],
                'estadomat'      => $row['estadomat'],
                'mm2kg'          => $row['mm2kg'],
                'envasesrec'     => $row['envasesrec']
            ]);
        }

        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function validaUltimoVale()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $query = "SELECT TOP 1 * FROM tblValeEEnc WHERE maquina=" . $_SESSION['idmaquina'] . " AND estado=1";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'maquina' => $row['maquina'],
                'noemp' => $row['noemp'],
                'turno' => $row['turno'],
                'clave1' => $row['clave1'],
                'clave2' => $row['clave2'],
                'clave3' => $row['clave3'],
                'clave4' => $row['clave4'],
                'foliocons' => $row['foliocons']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function actualizaEstado()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $estado = $_POST['estado'];
        $addupdate = '';
        $estado == 3 && $addupdate = ", fechaenviado=GETDATE()";
        $estado == 4 && $addupdate = ", fechasurtiendo=GETDATE(),supervisor=" . $_SESSION['ibm'];
        $query = "UPDATE tblValeEEnc SET estado=$estado $addupdate WHERE id=$folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function deleteMateriales()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $query = "DELETE FROM tblValeEMaterialesAdd WHERE id=$folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function tblValesCreados()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];

        $addquery = '';
        $_POST['turno'] != '' && $addquery .= "AND tblValeEEnc.turno=" . $_POST['turno'];
        $_POST['estado'] != '' && $addquery .= "AND tblValeEEnc.estado=" . $_POST['estado'];
        $_POST['maquina'] != '' && $addquery .= "AND tblValeEEnc.maquina=" . $_POST['maquina'];
        $query = "SELECT tblValeEEnc.*,tblEmpleados.Nombre as nombreEmp, tblValeEEstados.estado as estadonom,tblMaquinas.NombreMaquina FROM tblValeEEnc
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblValeEEnc.noemp 
		INNER JOIN tblValeEEstados ON tblValeEEstados.id=tblValeEEnc.estado
		INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina = tblValeEEnc.maquina
		WHERE CONVERT(date,tblValeEEnc.fecha) BETWEEN '$fechai' AND '$fechaf' $addquery";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'maquina' => $row['NombreMaquina'],
                'noemp' => $row['noemp'],
                'nombreEmp' => $row['nombreEmp'],
                'turno' => $row['turno'],
                'clave1' => $row['clave1'],
                'clave2' => $row['clave2'],
                'clave3' => $row['clave3'],
                'clave4' => $row['clave4'],
                'estado' => $row['estadonom'],
                'fechacreado' => $row['fecha']->format('Y-m-d H:i:s'),
                'fechaenviado' => $row['fechaenviado'] != '' ? $row['fechaenviado']->format('Y-m-d H:i:s') : '',
                'estadoid' => $row['estado'],
                'foliocons' => $row['foliocons'],
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function ValidaMatRemplazados()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT Count(*) as cont2,(SELECT Count(*) as cont FROM tblValeEMaterialesAdd WHERE tblValeEMaterialesAdd.idValeEnc = $id AND estado=3) as cont3
         FROM tblValeEMaterialesAdd WHERE tblValeEMaterialesAdd.idValeEnc = $id AND estado=2";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['cont2' => $row['cont2'], 'cont3' => $row['cont3']]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function ValesConstxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT tblValeEEnc.id,tblMaquinas.NombreMaquina as maquina,tblMaquinas.NoMaquina as maquinaid,tblEmpleados.noemp,tblEmpleados.Nombre,Trazabilidadturno.nombre as turno,
        tblValeEEnc.clave1,tblValeEEnc.clave2,tblValeEEnc.clave3,tblValeEEnc.clave4,tblValeEEstados.estado,tblValeEEnc.fecha, tblValeEEnc.fechaenviado,
        tblValeEEnc.fechasurtiendo,tblValeEEnc.foliocons
        FROM tblValeEEnc INNER JOIN TLX009MXDB.dbo.tblMaquinas ON tblMaquinas.NoMaquina= tblValeEEnc.maquina
        INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp= tblValeEEnc.noemp
        INNER JOIN TLX036MXDB.dbo.Trazabilidadturno ON Trazabilidadturno.id= tblValeEEnc.turno
        INNER JOIN tblValeEEstados ON tblValeEEstados.id= tblValeEEnc.estado WHERE tblValeEEnc.id=" . $id;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'maquina' => $row['maquina'],
                'noemp' => $row['noemp'],
                'nombre' => $row['Nombre'],
                'turno' => $row['turno'],
                'clave1' => $row['clave1'],
                'clave2' => $row['clave2'],
                'clave3' => $row['clave3'],
                'clave4' => $row['clave4'],
                'estado' => $row['estado'],
                'fechac' => $row['fecha']?->format('Y-m-d H:i:s') ?? '',
                'fechae' => $row['fechaenviado']?->format('Y-m-d H:i:s') ?? '',
                'surtiendo' => $row['fechasurtiendo']?->format('Y-m-d H:i:s') ?? '',
                'maquinaid' => $row['maquinaid'],
                'foliocons' => $row['foliocons']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function CancelaMaterial()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $query = "UPDATE tblValeEMaterialesAdd SET estado = 2 WHERE id=$folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function saveMM2()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $mm2 = $_POST['mm2'];
        $query = "UPDATE tblValeEMaterialesAdd SET mm2kg = $mm2 WHERE id=$folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function saveEnvases()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['folio'];
        $envases = $_POST['envases'];
        $query = "UPDATE tblValeEMaterialesAdd SET envasesrec = $envases WHERE id=$folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function tblclasesConf()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = " WHERE NoClase LIKE '%$busqueda%' OR Descripcion_Clase LIKE '%$busqueda%'";
        $query = "SELECT * FROM tblValeEClases $busqueda";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['id' => $row['id'], 'noclase' => $row['NoClase'], 'descclase' => $row['Descripcion_Clase']]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }

    function getDataMaquinas()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX009MXDB');

        // NoEmp del usuario logueado (IBM)
        $noEmp = $_SESSION['ibm'];

        // Consulta parametrizada:
        // 1) Une al empleado actual (empUser) con NoEmp = ?
        // 2) Si es especial (34374 o 58998) ve todas las máquinas de la lista
        // 3) Si no, solo las de su departamento (empUser.NombreDepartamento = tblMC.NoDepto)
        $sql = "
        SELECT DISTINCT
            tblMC.NoDepto,
            tblMC.NoMaquina,
            tblM.NombreMaquina
        FROM TLX009MXDB.dbo.tblMaquinasCombo AS tblMC
        INNER JOIN TLX009MXDB.dbo.tblMaquinas AS tblM
            ON tblM.NoMaquina = tblMC.NoMaquina
        INNER JOIN TLX032MXDB.dbo.tblEmpleados AS empUser
            ON empUser.NoEmp = ?
        WHERE tblMC.NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 87, 88, 89, 73, 76, 101, 137, 138, 139,
                                  140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158)
          AND (
              ? IN (34374, 58998)  -- Empleados especiales ven todas
              OR empUser.NombreDepartamento = tblMC.NoDepto  -- Otros ven solo su depto
          )
        ORDER BY tblMC.NoDepto ASC, tblM.NombreMaquina ASC;
    ";

        // Pasamos $noEmp dos veces: para empUser.NoEmp = ? y para la condición IN (34374, 58998)
        $params = [$noEmp, $noEmp];

        $array = [];

        $result = sqlsrv_query($conn, $sql, $params);

        if ($result === false) {
            http_response_code(500);
            echo json_encode('error');
            return;
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = $row;
        }

        http_response_code(200);
        echo json_encode($array);
    }

    function tblclavesConf()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX004MXDB');
        // $busqueda = $_POST['busqueda'];
        // empty($busqueda) ? $busqueda = '' : $busqueda = " WHERE NoClave LIKE '%$busqueda%' OR Descripcion_Articulo LIKE '%$busqueda%'";
        $noEmp = $_SESSION['ibm'];
        $query = "SELECT DISTINCT * 
                    FROM [TLX004MXDB].[dbo].[vwMXPRClaveMaquina]
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados tblEMP ON tblEMP.NoEmp = ?
                    WHERE (
                    ? IN (34374, 58998)  -- Empleados especiales ven todas
                    OR tblEMP.NombreDepartamento = vwMXPRClaveMaquina.NoDepto  -- Otros ven solo su depto
                    )
                    ORDER By id DESC";
        $params = [$noEmp, $noEmp];
        $result = sqlsrv_query($conn, $query, $params);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'NoDepto' => $row['NombreDepto'],
                'Nomaquina' => $row['NombreMaquina'],
                'noclave' => $row['NoClave'],
                'descclave' => $row['Descripcion_Articulo'],
                'xcaja' => $row['panalxcaja'],
                'factor' => $row['factor'],
                'Producto' => $row['Producto'],
                'Etapa' => $row['Etapa'],
                'categoria' => $row['Categoria'],
                'EstadoClave' => $row['EstadoClave']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function tblmaterialesConf()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = " WHERE NoMaterial LIKE '%$busqueda%' OR NombreMaterial LIKE '%$busqueda%'";
        $query = "SELECT * FROM tblValeEMateriales $busqueda";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'nomaterial' => $row['NoMaterial'],
                'descmaterial' => $row['NombreMaterial'],
                'ummaterial' => $row['UMMaterial'],
                'um' => $row['UM'],
                'montacargas' => $row['TipoMontacargas'],
                'costos' => $row['CentroCosto'],
                'TiempoMat' => $row['TiempoMaterial'],
                'EstadoMaterial' => $row['EstadoMaterial']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function editclasexid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT * FROM tblValeEClases WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['id' => $row['id'], 'noclase' => $row['NoClase'], 'descclase' => $row['Descripcion_Clase']]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function editclavexid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT T1.id, T1.NoClave, T1.Descripcion_Articulo, T1.panalxcaja, T1.factor,
                            T1.Producto, T1.Etapa, T1.Categoria, T1.EstadoClave, T1.EquivalenciaUSTD2,
                            T1.pesoBase, T1.ancho, T1.clavePuente, T2.Descripcion_Articulo AS Descripcion_Puente, T3.id_maquina
                    FROM [TLX002MXDB].[dbo].[tblValeEClaves] T1
                    LEFT JOIN [TLX002MXDB].[dbo].[tblValeEClaves] T2
                    ON T1.clavePuente = T2.NoClave
                    LEFT JOIN [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] T3 ON T1.id = T3.id_clave
                    WHERE T1.id = $id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'noclave' => $row['NoClave'],
                'descclave' => $row['Descripcion_Articulo'],
                'xcaja' => $row['panalxcaja'],
                'factor' => $row['factor'],
                'producto' => $row['Producto'],
                'tamaño' => $row['Etapa'],
                'categoria' => $row['Categoria'],
                'EquivalenciaUSTD' => $row['EquivalenciaUSTD2'],
                'pesoBase' => $row['pesoBase'],
                'ancho' => $row['ancho'],
                'maquina' => $row['id_maquina'],
                'clavePuente' => $row['clavePuente'],
                'Descripcion_Puente' => $row['Descripcion_Puente']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function editmaterialxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT * FROM tblValeEMateriales WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'nomaterial' => $row['NoMaterial'],
                'descmaterial' => $row['NombreMaterial'],
                'ummaterial' => $row['UMMaterial'],
                'um' => $row['UM'],
                'montacargas' => $row['TipoMontacargas'],
                'costos' => $row['CentroCosto'],
                'TiempoMat' => $row['TiempoMaterial']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function editconvinacionesxid()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT  tblValeConClavClasMat.id as idconv, tblMaquinas.NoMaquina, tblMaquinas.NombreMaquina,
        tblValeEClaves.NoClave, tblValeEClaves.Descripcion_Articulo,
        tblValeEClases.NoClase, tblValeEClases.Descripcion_Clase, 
        tblValeEMateriales.NoMaterial, tblValeEMateriales.NombreMaterial
        FROM dbo.tblValeConClavClasMat
        inner join dbo.tblValeEClases on tblValeConClavClasMat.NoClase = tblValeEClases.NoClase
        inner join dbo.tblValeEClaves  on tblValeConClavClasMat.NoClave = tblValeEClaves.NoClave
        inner join dbo.tblValeEMateriales  on tblValeConClavClasMat.NoMaterial = tblValeEMateriales.NoMaterial
        inner join TLX009MXDB.dbo.tblMaquinas  on tblValeConClavClasMat.NoMaquina = tblMaquinas.NoMaquina WHERE tblValeConClavClasMat.id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'idconv' => $row['idconv'],
                'nomaquina' => $row['NoMaquina'],
                'nommaquina' => $row['NombreMaquina'],
                'noclave' => $row['NoClave'],
                'nomclave' => $row['Descripcion_Articulo'],
                'noclase' => $row['NoClase'],
                'nomclase' => $row['Descripcion_Clase'],
                'nomaterial' => $row['NoMaterial'],
                'nommaterial' => $row['NombreMaterial']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function tblconvinaciones()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = " WHERE tblMaquinas.NombreMaquina LIKE '%$busqueda%' OR tblValeEClaves.NoClave LIKE '%$busqueda%'
         OR tblValeEClaves.Descripcion_Articulo LIKE '%$busqueda%' OR tblValeEClases.Descripcion_Clase LIKE '%$busqueda%' OR tblValeEMateriales.NoMaterial LIKE '%$busqueda%'
          OR tblValeEMateriales.NombreMaterial LIKE '%$busqueda%' ";
        $query = "SELECT TOP 250 tblValeConClavClasMat.id as idconv, tblMaquinas.NoMaquina, tblMaquinas.NombreMaquina,
        tblValeEClaves.NoClave, tblValeEClaves.Descripcion_Articulo,
        tblValeEClases.NoClase, tblValeEClases.Descripcion_Clase, 
        tblValeEMateriales.NoMaterial, tblValeEMateriales.NombreMaterial
        FROM dbo.tblValeConClavClasMat
        left join dbo.tblValeEClases on tblValeConClavClasMat.NoClase = tblValeEClases.NoClase
        left join dbo.tblValeEClaves  on tblValeConClavClasMat.NoClave = tblValeEClaves.NoClave
        left join dbo.tblValeEMateriales  on tblValeConClavClasMat.NoMaterial = tblValeEMateriales.NoMaterial
        left join TLX009MXDB.dbo.tblMaquinas  on tblValeConClavClasMat.NoMaquina = tblMaquinas.NoMaquina $busqueda ORDER BY tblValeConClavClasMat.id DESC";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['idconv'],
                'nomaquina' => $row['NoMaquina'],
                'nommaquina' => $row['NombreMaquina'],
                'noclave' => $row['NoClave'],
                'nomclave' => $row['Descripcion_Articulo'],
                'noclase' => $row['NoClase'],
                'nomclase' => $row['Descripcion_Clase'],
                'nomaterial' => $row['NoMaterial'],
                'nommaterial' => $row['NombreMaterial']
            ]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
    function saveClase()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $idclase = $_POST['idclase'];
        $noclase = $_POST['noclase'];
        $nombreclase = $_POST['nombreclase'];
        $query = '';
        if ($noclase === '' || $nombreclase === '') {
            http_response_code(201);
            die();
        }
        if ($idclase == '') {
            $queryval = "SELECT COUNT(*) FROM tblValeEClases WHERE NoClase = $noclase";
            $stpm = sqlsrv_query($conn, $queryval);
            sqlsrv_fetch($stpm);
            $res2 = sqlsrv_get_field($stpm, 0);
            if ($res2 > 0) {
                http_response_code(202);
                die();
            }
            $query = "INSERT INTO tblValeEClases(NoClase,Descripcion_Clase) VALUES (?, ?)";
        } else
            $query = "UPDATE tblValeEClases SET NoClase = ?,Descripcion_Clase = ? WHERE id = $idclase";
        $datos = array($noclase, $nombreclase);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function saveClave()
    {
        header('Content-Type: application/json');

        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');

        $body = file_get_contents('php://input');
        $input = json_decode($body, true);

        $post = !empty($input) ? $input : $_POST;
        $idclave = $post['idclave'] ?? '';
        $noclave = $post['NoClave'] ?? '';
        $descripcion = $post['Descripcion'] ?? '';
        $categoria = $post['Categoria'] ?? '';
        $claveproducto = $post['Producto'] ?? '';
        $tamaño = $post['Tamaño'] ?? '';
        $xcaja = $post['xcaja'] ?? '';
        $ustd = $post['ustd'] ?? '';
        $factor = $post['factor'] ?? '';
        $pesoBase = (isset($post['pesoBase']) && $post['pesoBase'] !== '') ? $post['pesoBase'] : 0;
        $ancho = (isset($post['ancho']) && $post['ancho'] !== '') ? $post['ancho'] : 0;
        $clavePuente = $post['clavePuente'] ?? '';
        $maquinas = $post['maquinas'] ?? [];

        header('Content-Type: application/json');
    ini_set('display_errors', 0); // No mostrar en pantalla
    error_reporting(E_ALL);

    // Capturar cualquier error fatal antes de que explote
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'mensaje' => 'Error fatal PHP',
                'detalle' => $error['message'] . ' en ' . $error['file'] . ':' . $error['line']
            ]);
        }
    });

        // Validación
        if (
            empty($noclave) || empty($descripcion) 
        ) {
            http_response_code(201);
            echo json_encode(['ok' => false, 'mensaje' => 'Faltan campos obligatorios.']);
            die();
        }

        // ─────────────────────────────────────────────
        // CASO EDICIÓN: idclave tiene valor → UPDATE
        // ─────────────────────────────────────────────
        if (!empty($idclave)) {

            // 1. Actualizar datos de la clave
            $sqlUpdate = "UPDATE tblValeEClaves 
                  SET NoClave              = ?,
                      Descripcion_Articulo = ?,
                      panalxcaja           = ?,
                      factor               = ?,
                      Producto             = ?,
                      Etapa                = ?,
                      Categoria            = ?,
                      EquivalenciaUSTD2     = ?,
                      PesoBase             = ?,
                      Ancho                = ?,
                      clavePuente          = ?
                  WHERE id = ?";

            $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, [
                $noclave,
                $descripcion,
                $xcaja,
                $factor,
                $claveproducto,
                $tamaño,
                $categoria,
                $ustd,
                $pesoBase,
                $ancho,
                $clavePuente,
                (int) $idclave
            ]);

            if (!$stmtUpdate) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'mensaje' => 'Error al actualizar la clave.']);
                die();
            }

            // 2. Sincronizar máquinas
            if (!empty($maquinas)) {

                $sqlActuales = "SELECT id_maquina FROM [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] 
                         WHERE id_clave = ?";
                $stmtActuales = sqlsrv_query($conn, $sqlActuales, [(int) $idclave]);

                $maquinasActuales = [];
                while ($row = sqlsrv_fetch_array($stmtActuales, SQLSRV_FETCH_ASSOC)) {
                    $maquinasActuales[] = (int) $row['id_maquina'];
                }

                $maquinasNuevas = array_map('intval', $maquinas);
                $maquinasQuitar = array_diff($maquinasActuales, $maquinasNuevas);
                $maquinasAgregar = array_diff($maquinasNuevas, $maquinasActuales);

                foreach ($maquinasQuitar as $id_maquina) {
                    $sqlDelete = "DELETE FROM [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] 
                          WHERE id_clave = ? AND id_maquina = ?";
                    sqlsrv_query($conn, $sqlDelete, [(int) $idclave, $id_maquina]);
                }

                foreach ($maquinasAgregar as $id_maquina) {
                    $sqlInsert = "INSERT INTO [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] (id_clave, id_maquina) 
                          VALUES (?, ?)";
                    sqlsrv_query($conn, $sqlInsert, [(int) $idclave, $id_maquina]);
                }
            }

            sqlsrv_close($conn);
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Clave actualizada correctamente.']);
            die();
        }

        // ─────────────────────────────────────────────
        // CASO NUEVA: verificar si NoClave ya existe
        // ─────────────────────────────────────────────
        $sqlClaveExistente = "SELECT id FROM tblValeEClaves WHERE NoClave = ?";
        $stmtClaveExistente = sqlsrv_query($conn, $sqlClaveExistente, [$noclave]);
        $claveExistente = sqlsrv_fetch_array($stmtClaveExistente, SQLSRV_FETCH_ASSOC);

        if ($claveExistente) {
            $id_clave = $claveExistente['id'];
            $asignadas = 0;
            $duplicadas = 0;

            foreach ($maquinas as $id_maquina) {
                $sqlYa = "SELECT id FROM [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] 
                       WHERE id_clave = ? AND id_maquina = ?";
                $stmtYa = sqlsrv_query($conn, $sqlYa, [$id_clave, (int) $id_maquina]);

                if (sqlsrv_fetch_array($stmtYa, SQLSRV_FETCH_ASSOC)) {
                    $duplicadas++;
                    continue;
                }

                $sqlAsignar = "INSERT INTO [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] (id_clave, id_maquina) VALUES (?, ?)";
                sqlsrv_query($conn, $sqlAsignar, [$id_clave, (int) $id_maquina]);
                $asignadas++;
            }
            sqlsrv_close($conn);
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Clave asignada a nuevas máquinas correctamente.']);
            die();
        }

        // ─────────────────────────────────────────────
        // CASO NUEVO INSERT completo
        // ─────────────────────────────────────────────
        $query = "INSERT INTO tblValeEClaves
                (NoClave, Descripcion_Articulo, panalxcaja, factor, Producto,
                 Etapa, Categoria, EquivalenciaUSTD2, PesoBase, Ancho, clavePuente)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
              SELECT SCOPE_IDENTITY() AS id_clave;";

        $stmt = sqlsrv_query($conn, $query, [
            $noclave,
            $descripcion,
            $xcaja,
            $factor,
            $claveproducto,
            $tamaño,
            $categoria,
            $ustd,
            $pesoBase,
            $ancho,
            $clavePuente
        ]);

        if (!$stmt) {
            $error = sqlsrv_errors();
            http_response_code(500);
            echo json_encode(['ok' => false, 'mensaje' => 'Error al guardar la clave.', 'detalle' => $error]);
            die();
        }

        sqlsrv_next_result($stmt);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $id_clave = (int) $row['id_clave'];

        $sqlCombo = "INSERT INTO [TLX004MXDB].[dbo].[tblMXPRClaveMaquina] (id_clave, id_maquina) VALUES (?, ?)";
        foreach ($maquinas as $id_maquina) {
            sqlsrv_query($conn, $sqlCombo, [$id_clave, (int) $id_maquina]);
        }

        sqlsrv_close($conn);
        http_response_code(200);
        echo json_encode(['ok' => true, 'mensaje' => 'Clave guardada correctamente.']);
        die();
    }
    function saveMaterial()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $idmaterial = $_POST['idmaterial'];
        $nomaterial = $_POST['nomaterial'];
        $nombrematerial = $_POST['nombrematerial'];
        $ummaterial = $_POST['ummaterial'];
        $ummat = $_POST['ummat'];
        $montacargas = $_POST['montacargas'];
        $costos = $_POST['costos'];
        $tiempo = $_POST['tiempo'];
        $query = '';


        if (
            $nomaterial === '' || $nombrematerial === '' || $ummaterial === ''
            || $ummat === '' || $montacargas === '' || $costos === '' || $tiempo === ''
        ) {
            http_response_code(201);
            die();
        }
        if ($idmaterial == '') {
            $queryval = "SELECT COUNT(*) FROM tblValeEMateriales WHERE NoMaterial = $nomaterial";
            $stpm = sqlsrv_query($conn, $queryval);
            sqlsrv_fetch($stpm);
            $res2 = sqlsrv_get_field($stpm, 0);
            if ($res2 > 0) {
                http_response_code(202);
                die();
            }
            $query = "INSERT INTO tblValeEMateriales(NoMaterial,NombreMaterial,UMMaterial,UM,TipoMontacargas,CentroCosto,TiempoMaterial) VALUES (?, ?, ?, ?, ?, ?, ?)";
        } else
            $query = "UPDATE tblValeEMateriales SET NoMaterial = ?,NombreMaterial = ?,UMMaterial = ?,UM = ?,TipoMontacargas = ?,CentroCosto = ?,TiempoMaterial = ? WHERE id = $idmaterial";
        $datos = array($nomaterial, $nombrematerial, $ummaterial, $ummat, $montacargas, $costos, $tiempo);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function saveConvinacion()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $idconvinacion = $_POST['idconvinacion'];
        $maquinaconv = $_POST['maquinaconv'];
        $claseconv = $_POST['claseconv'];
        $claveconv = $_POST['claveconv'];
        $materialconv = $_POST['materialconv'];
        if ($maquinaconv === '' || $claseconv === '' || $claveconv === '' || $materialconv === '') {
            http_response_code(201);
            die();
        }
        $queryval = "SELECT COUNT(*) FROM tblValeConClavClasMat WHERE NoMaquina = $maquinaconv AND NoClave = '$claveconv' AND NoClase = $claseconv AND NoMaterial = $materialconv";
        $stpm = sqlsrv_query($conn, $queryval);
        sqlsrv_fetch($stpm);
        $res2 = sqlsrv_get_field($stpm, 0);
        if ($res2 > 0) {
            http_response_code(202);
            die();
        }
        $idconvinacion == '' ?
            $query = "INSERT INTO tblValeConClavClasMat(NoMaquina,NoClave,NoClase,NoMaterial) VALUES (?, ?, ?, ?)" :
            $query = "UPDATE tblValeConClavClasMat SET NoMaquina = ?, NoClave = ?, NoClase = ?, NoMaterial = ? WHERE id = $idconvinacion";
        $datos = array($maquinaconv, $claveconv, $claseconv, $materialconv);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function autoclaves()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $a = $_GET['q'];
        $query = ("SELECT NoClave, Descripcion_Articulo FROM tblValeEClaves WHERE Descripcion_Articulo LIKE '%$a%' OR NoClave LIKE '%$a%'");
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $datos[] = ['id' => $row['NoClave'], 'text' => $row['Descripcion_Articulo']];
        }

        echo json_encode($datos);
    }
    function autoclases()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $a = $_GET['q'];
        $query = ("SELECT NoClase, Descripcion_Clase FROM tblValeEClases WHERE Descripcion_Clase LIKE '%$a%' OR NoClase LIKE '%$a%'");
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $datos[] = ['id' => $row['NoClase'], 'text' => $row['Descripcion_Clase']];
        }

        echo json_encode($datos);
    }
    function automateriales()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $a = $_GET['q'];
        $query = ("SELECT NoMaterial, NombreMaterial FROM tblValeEMateriales WHERE NombreMaterial LIKE '%$a%' OR NoMaterial LIKE '%$a%'");
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $datos[] = ['id' => $row['NoMaterial'], 'text' => $row['NombreMaterial']];
        }

        echo json_encode($datos);
    }

    function deleteMaterial()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['id'];
        $query = "UPDATE tblValeEMateriales SET EstadoMaterial = 1 WHERE id = $folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);

    }

    function deleteClave()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $folio = $_POST['id'];
        $query = "UPDATE tblValeEClaves SET EstadoClave = 1 WHERE id = $folio";
        $result = sqlsrv_query($conn, $query);
        $result === false ? http_response_code(500) : http_response_code(200);
    }

    function saveProducto()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $producto = $_POST['producto'];
        $query = "INSERT INTO tblProduccionProductos(Producto) VALUES (?)";
        $datos = array($producto);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);

    }
}

$vales = new ValesElectronicos();
if (isset($_GET['ClaseMat'])) {
    $vales->ClaseMat();
} else if (isset($_GET['tblMateriales'])) {
    $vales->tblMateriales();
} else if (isset($_GET['addMaterial'])) {
    $vales->addMaterial();
} else if (isset($_GET['saveValeElectronico'])) {
    $vales->saveValeElectronico();
} else if (isset($_GET['tblMaterialesAgregados'])) {
    $vales->tblMaterialesAgregados();
} else if (isset($_GET['validaUltimoVale'])) {
    $vales->validaUltimoVale();
} else if (isset($_GET['actualizaEstado'])) {
    $vales->actualizaEstado();
} else if (isset($_GET['deleteMateriales'])) {
    $vales->deleteMateriales();
} else if (isset($_GET['tblValesCreados'])) {
    $vales->tblValesCreados();
} else if (isset($_GET['ValesConstxid'])) {
    $vales->ValesConstxid();
} else if (isset($_GET['CancelaMaterial'])) {
    $vales->CancelaMaterial();
} else if (isset($_GET['saveMM2'])) {
    $vales->saveMM2();
} else if (isset($_GET['saveEnvases'])) {
    $vales->saveEnvases();
} else if (isset($_GET['ValidaMatRemplazados'])) {
    $vales->ValidaMatRemplazados();
} else if (isset($_GET['addMaterialadmin'])) {
    $vales->addMaterialadmin();
} else if (isset($_GET['tblclasesConf'])) {
    $vales->tblclasesConf();
} else if (isset($_GET['tblclavesConf'])) {
    $vales->tblclavesConf();
} else if (isset($_GET['tblmaterialesConf'])) {
    $vales->tblmaterialesConf();
} else if (isset($_GET['editclasexid'])) {
    $vales->editclasexid();
} else if (isset($_GET['editclavexid'])) {
    $vales->editclavexid();
} else if (isset($_GET['editmaterialxid'])) {
    $vales->editmaterialxid();
} else if (isset($_GET['editconvinacionesxid'])) {
    $vales->editconvinacionesxid();
} else if (isset($_GET['tblconvinaciones'])) {
    $vales->tblconvinaciones();
} else if (isset($_GET['saveClase'])) {
    $vales->saveClase();
} else if (isset($_GET['saveClave'])) {
    $vales->saveClave();
} else if (isset($_GET['saveMaterial'])) {
    $vales->saveMaterial();
} else if (isset($_GET['saveConvinacion'])) {
    $vales->saveConvinacion();
} else if (isset($_GET['autoclaves'])) {
    $vales->autoclaves();
} else if (isset($_GET['autoclases'])) {
    $vales->autoclases();
} else if (isset($_GET['automateriales'])) {
    $vales->automateriales();
} else if (isset($_GET['deleteMaterial'])) {
    $vales->deleteMaterial();
} else if (isset($_GET['saveProducto'])) {
    $vales->saveProducto();
} else if (isset($_GET['deleteClave'])) {
    $vales->deleteClave();
} else if (isset($_GET['getDataMaquinas'])) {
    $vales->getDataMaquinas();
}
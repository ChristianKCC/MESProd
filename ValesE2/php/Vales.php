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
        $idenc = $_POST['folio'];
        $idmat = $_POST['idmat'];
        $cantidad = $_POST['cantidad'];
        $query = "INSERT INTO tblValeEMaterialesAdd(idValeEnc,idMaterial,Cantidad) VALUES ($idenc,$idmat,$cantidad)";
        $result = sqlsrv_query($conn, $query);
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
        if ($result === false) die("error al insertar registro");
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
        $query = "SELECT  tblValeEMateriales.*,tblValeEMaterialesAdd.id as folio,tblValeEMaterialesAdd.cantidad,tblValeEMaterialesAdd.estado as estadomat,
        tblValeEMaterialesAdd.mm2kg, tblValeEMaterialesAdd.envasesrec FROM tblValeEMaterialesAdd 
        INNER JOIN tblValeEMateriales ON tblValeEMateriales.NoMaterial = tblValeEMaterialesAdd.idMaterial WHERE tblValeEMaterialesAdd.idValeEnc=$folio";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'folio' => $row['folio'],
                'NoMaterial' => $row['NoMaterial'],
                'NombreMaterial' => $row['NombreMaterial'],
                'CentroCosto' => $row['CentroCosto'],
                'TiempoMaterial' => $row['TiempoMaterial'],
                'TipoMontacargas' => $row['TipoMontacargas'],
                'Cantidad' => $row['cantidad'],
                'UM' => $row['UM'],
                'estadomat' => $row['estadomat'],
                'mm2kg' => $row['mm2kg'],
                'envasesrec' => $row['envasesrec']
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
    function tblclavesConf()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $busqueda = $_POST['busqueda'];
        empty($busqueda) ? $busqueda = '' : $busqueda = " WHERE NoClave LIKE '%$busqueda%' OR Descripcion_Articulo LIKE '%$busqueda%'";
        $query = "SELECT * FROM tblValeEClaves
        LEFT JOIN tblproduccionesClaveClase ON tblproduccionesClaveClase.idClase = tblValeEClaves.Clase
        LEFT JOIN tblProduccionesClaveTipo ON tblProduccionesClaveTipo.idTipo = tblValeEClaves.Tipo $busqueda";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'noclave' => $row['NoClave'],
                'descclave' => $row['Descripcion_Articulo'],
                'xcaja' => $row['panalxcaja'],
                'factor' => $row['factor'],
                'clase' => $row['DescripcionClase'],
                'tipo' => $row['DescripcionTipo']
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
                'TiempoMat' => $row['TiempoMaterial']
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
        $query = "SELECT * FROM tblValeEClaves WHERE id=$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'],
                'noclave' => $row['NoClave'],
                'descclave' => $row['Descripcion_Articulo'],
                'xcaja' => $row['panalxcaja'],
                'factor' => $row['factor'],
                'clase' => $row['Clase'],
                'tipo' => $row['Tipo']
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
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $idclave = $_POST['idclave'];
        $noclave = $_POST['noclave'];
        $nombreclave = $_POST['nombreclave'];
        $xcaja = $_POST['xcaja'];
        $factor = $_POST['factor'];
        $claveclase = $_POST['claveclase'];
        $clavetipo = $_POST['clavetipo'];
        $query = '';
        if (
            $noclave === '' || $nombreclave === '' || $xcaja === '' || $factor === ''
            || $claveclase === '' || $clavetipo === ''
        ) {
            http_response_code(201);
            die();
        }
        if ($idclave == '') {
            $queryval = "SELECT COUNT(*) FROM tblValeEClaves WHERE NoClave = '$noclave'";
            $stpm = sqlsrv_query($conn, $queryval);
            sqlsrv_fetch($stpm);
            $res2 = sqlsrv_get_field($stpm, 0);
            if ($res2 > 0) {
                http_response_code(202);
                die();
            }
            $query = "INSERT INTO tblValeEClaves(NoClave,Descripcion_Articulo,panalxcaja,factor,Tipo,Clase) VALUES (?, ?, ?, ?, ?, ?)";
        } else
            $query = "UPDATE tblValeEClaves SET NoClave = ?, Descripcion_Articulo = ?, panalxcaja = ?, factor = ?, Tipo = ?, Clase = ? WHERE id = $idclave";
        $datos = array($noclave, $nombreclave, $xcaja, $factor, $clavetipo, $claveclase);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);
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
            $query = "INSERT INTO tblValeConClavClasMat(NoMaquina,NoClave,NoClase,NoMaterial) VALUES (?, ?, ?, ?)":
            $query = "UPDATE tblValeConClavClasMat SET NoMaquina = ?, NoClave = ?, NoClase = ?, NoMaterial = ? WHERE id = $idconvinacion" ;
        $datos = array($maquinaconv, $claveconv, $claseconv, $materialconv);
        $result = sqlsrv_query($conn, $query, $datos);
        $result === false ? http_response_code(500) : http_response_code(200);
    }
    function autoclaves()
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion('TLX002MXDB');
        $a = $_GET['q'];
        $query=("SELECT NoClave, Descripcion_Articulo FROM tblValeEClaves WHERE Descripcion_Articulo LIKE '%$a%'");
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
        $query=("SELECT NoClase, Descripcion_Clase FROM tblValeEClases WHERE Descripcion_Clase LIKE '%$a%'");
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
        $query=("SELECT NoMaterial, NombreMaterial FROM tblValeEMateriales WHERE NombreMaterial LIKE '%$a%'");
        $result = sqlsrv_query($conn, $query);
        $datos = [];
        while ($row = sqlsrv_fetch_array($result)) {
            $datos[] = ['id' => $row['NoMaterial'], 'text' => $row['NombreMaterial']];
        }

        echo json_encode($datos);
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
}else if (isset($_GET['autoclaves'])) {
    $vales->autoclaves();
}else if (isset($_GET['autoclases'])) {
    $vales->autoclases();
}else if (isset($_GET['automateriales'])) {
    $vales->automateriales();
}

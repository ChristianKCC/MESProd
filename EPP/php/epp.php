<?php
require_once '../../conexion.php';
require_once '../../Session/seguridad.php';
require_once '../../conexion.php';
require __DIR__ . '/../../php/vendor/autoload.php';
class EPP
{
    // Obtencion de valores de EPP basandose en el tipo para renderiza los check input
    function ListEppBasico($tipo)
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $query = 'SELECT * FROM tblEPPListEquipo WHERE tipo=' . $tipo;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ['id' => $row['id'], 'nombre' => $row['nombre'], 'tipo' => $row['tipo']]);
        }
        echo json_encode($array);
    }

    // Funcion de guardado de EPP
    function saveEPP()
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $noemp = $_POST['noemp'];
        $noempres = $_POST['noempres'];
        $noempres == '' ? $_SESSION["ibm"] : $noempres;
        $idsession = '';
        $noempres == '' ? $idsession = $_SESSION["ibm"] : $idsession = $_SESSION["idmaquina"];
        $comentario = $_POST['comentario'];
        $checkbox = json_decode($_POST['checkbox']);
        $query = "INSERT INTO tblEPPEnc(noemp,cargo,comentario,idsession) VALUES (?,?,?,?)";
        $result = sqlsrv_query($conn, $query, array($noemp, $noempres, $comentario, $idsession));
        if ($result) {
            $query_get_id = "SELECT @@IDENTITY AS id";
            $result_get_id = sqlsrv_query($conn, $query_get_id);
            $row = sqlsrv_fetch_array($result_get_id, SQLSRV_FETCH_ASSOC);
            $querycheck = "INSERT INTO tblEPPSubEnc(idEnc,Equipo,valor) VALUES (?,?,?)";
            foreach ($checkbox as $checkrec)
                sqlsrv_query($conn, $querycheck, array($row['id'], $checkrec->nombre, $checkrec->valor));
            http_response_code(200);
        } else {
            http_response_code(500);
        }
    }

    // Renderizar vista de solicitudes EPP hechas bajo mi sesion (Ultima tabla del html)
    function tblEPPEnc($idSession)
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $query = 'SELECT tblEPPEnc.id,tblEPPEnc.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblEPPEnc.fecha,tblEPPEnc.comentario FROM tblEPPEnc 
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEPPEnc.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON  tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblEPPEnc.cargo = (?) OR tblEPPEnc.idsession = (?)';
        $result = sqlsrv_query($conn, $query, array($idSession == 1 ? $_SESSION['ibm'] : $_SESSION['idmaquina'], $idSession == 1 ? $_SESSION['ibm'] : $_SESSION['idmaquina']));
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'], 'noemp' => $row['noemp'], 'nombre' => $row['Nombre'], 'departamento' => $row['NombreDepto'], 'comentario' => $row['comentario'],
                'fecha' => $row['fecha']->format('Y/m/d H:i:s')
            ]);
        }
        echo json_encode($array);
    }
    
    // Funcion para mostrar info del EPP en un modal
    function tblEPPSubEnc()
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $folio = $_GET['folio'];
        $query = 'SELECT tblEPPEnc.id,tblEPPEnc.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblEPPEnc.fecha,tblEPPListEquipo.nombre as equipo,tblEPPListValor.nombre as valor FROM tblEPPEnc 
        INNER JOIN tblEPPSubEnc ON tblEPPSubEnc.idEnc = tblEPPEnc.id
        INNER JOIN tblEPPListEquipo ON tblEPPListEquipo.id = tblEPPSubEnc.Equipo
        INNER JOIN tblEPPListValor ON tblEPPListValor.id = tblEPPSubEnc.valor
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEPPEnc.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON  tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblEPPEnc.id=' . $folio;
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'], 'noemp' => $row['noemp'], 'nombre' => $row['Nombre'], 'departamento' => $row['NombreDepto'],
                'fecha' => $row['fecha']->format('Y/m/d H:i:s'), 'equipo' => $row['equipo'], 'valor' => $row['valor']
            ]);
        }
        echo json_encode($array);
    }

    // Funcion para obtener datos de info del epp en una vista aparte como reporte de equipo de protecion personal
    function tblEPPReporte()
    {
        $ClassConextion = new ClassConexion();
        $conn = $ClassConextion->conexion('TLX002MXDB');
        $fechai = $_POST['fechai'];
        $fechaf = $_POST['fechaf'];
        $departamento = $_POST['departamento'];
        $noemp = $_POST['noemp'];
        $observador = $_POST['observador'];
        $departamento != '' &&  $departamento = "AND tblDepartamentos.NoDepto=$departamento";
        $noemp != '' &&  $noemp = "AND tblEmpleados.NoEmp LIKE '%$noemp%'";
        $observador != '' &&  $observador = "AND tblEPPEnc.cargo LIKE '%$observador%'";
        $query = "SELECT tblEPPEnc.id,tblEPPEnc.noemp,tblEmpleados.Nombre,tblDepartamentos.NombreDepto,tblEPPEnc.fecha,tblEPPEnc.comentario FROM tblEPPEnc 
		INNER JOIN TLX032MXDB.dbo.tblEmpleados ON  tblEmpleados.NoEmp = tblEPPEnc.noemp
		INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON  tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento WHERE tblEPPEnc.fecha >= '$fechai' AND tblEPPEnc.fecha < '$fechaf' 
        $departamento $noemp $observador";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                'id' => $row['id'], 'noemp' => $row['noemp'], 'nombre' => $row['Nombre'], 'departamento' => $row['NombreDepto'], 'comentario' => $row['comentario'],
                'fecha' => $row['fecha']->format('Y/m/d H:i:s')
            ]);
        }
        echo json_encode($array);
    }

    // Compara textos ignorando acentos, mayúsculas y espacios extra
    // Normaliza: minúsculas, sin acentos, sin signos, espacios colapsados
    function normalizaTexto($txt)
    {
        $txt = mb_strtolower(trim((string)$txt), 'UTF-8');
        $txt = strtr($txt, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
        $txt = preg_replace('/[^a-z0-9\s]/', ' ', $txt); // quita signos
        return trim(preg_replace('/\s+/', ' ', $txt));
    }

    // Comparación exacta normalizada (la usa el departamento)
    function compararTexto($a, $b)
    {
        return $this->normalizaTexto($a) === $this->normalizaTexto($b);
    }

    // Devuelve solo palabras "fuertes": descarta conectores y palabras muy cortas
    function palabrasSignificativas($txt)
    {
        $stop = ['de','del','la','el','los','las','y','o','con','para','en','a'];
        $palabras = explode(' ', $this->normalizaTexto($txt));
        $result = [];
        foreach ($palabras as $p) {
            if ($p === '' || in_array($p, $stop) || strlen($p) < 4) continue;
            $result[] = $p;
        }
        return $result;
    }

    // Dos palabras coinciden si son iguales, una contiene a la otra,
    // o difieren por muy poco (plurales/género: electronico/electronica)
    function palabraCoincide($a, $b)
    {
        if ($a === $b) return true;
        if (strlen($a) >= 4 && strlen($b) >= 4 &&
            (strpos($a, $b) !== false || strpos($b, $a) !== false)) return true;
        if (strlen($a) >= 5 && strlen($b) >= 5 && levenshtein($a, $b) <= 1) return true;
        return false;
    }

    // Match flexible de puesto: todas las palabras del puesto más corto
    // deben encontrar pareja en el otro
    function puestoCoincide($puestoEmpleado, $puestoExcel)
    {
        if ($this->compararTexto($puestoEmpleado, $puestoExcel)) return true; // exacto

        $palEmp = $this->palabrasSignificativas($puestoEmpleado);
        $palExc = $this->palabrasSignificativas($puestoExcel);
        if (empty($palEmp) || empty($palExc)) return false;

        // El conjunto más corto manda: todas sus palabras deben coincidir
        $base   = count($palExc) <= count($palEmp) ? $palExc : $palEmp;
        $contra = count($palExc) <= count($palEmp) ? $palEmp : $palExc;

        foreach ($base as $pb) {
            $encontrada = false;
            foreach ($contra as $pc) {
                if ($this->palabraCoincide($pb, $pc)) { $encontrada = true; break; }
            }
            if (!$encontrada) return false;
        }
        return true;
    }

    // Funcion para obtener EPP del excel con registro en log
    function getEPPExcel()
    {        
        $logFile = __DIR__ . '/epp_debug.log';
        $log = function ($msg) use ($logFile) {
            file_put_contents($logFile, '[' . date('H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
        };

        $log('==================== NUEVA PETICION ====================');

        $departamento = trim($_GET['departamento'] ?? '');
        $puesto       = trim($_GET['puesto'] ?? '');
        $log("Departamento recibido: '$departamento'");
        $log("Puesto recibido: '$puesto'");
        $log("Departamento normalizado: '" . $this->normalizaTexto($departamento) . "'");
        $log("Puesto normalizado: '" . $this->normalizaTexto($puesto) . "'");

        $rutaExcel = __DIR__ . '/FO-OSHPS012-01_Identificación_y_selección_de_EPP.xlsx';
        $log("Ruta Excel: $rutaExcel");

        if (!file_exists($rutaExcel)) {
            $log('ERROR: el archivo NO existe en esa ruta');
            http_response_code(404);
            echo json_encode(['error' => 'No se encontró el archivo Excel']);
            return;
        }
        $log('OK: archivo encontrado');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($rutaExcel);

        // Listado de  las hojas
        foreach ($spreadsheet->getSheetNames() as $i => $nombreHoja){
            $log("Hoja [$i]: '$nombreHoja'");
        }
        $hoja = $spreadsheet->getActiveSheet();
        $log('Hoja activa: ' . $hoja->getTitle());

        $categorias = [
            'E' => 'Cabeza',
            'F' => 'Ojos y cara',
            'G' => 'Oídos',
            'H' => 'Aparato respiratorio',
            'I' => 'Extremidades superiores',
            'J' => 'Tronco',
            'K' => 'Extremidades inferiores',
            'L' => 'Otros',
        ];

        $resultado = [];
        $maxFila   = $hoja->getHighestDataRow();
        $depActual = '';
        $log("Ultima fila con datos: $maxFila");

        for ($fila = 6; $fila <= $maxFila; $fila++) {
            $depCelda = trim((string) $hoja->getCell('B' . $fila)->getValue());
            if ($depCelda !== '') {
                $depActual = $depCelda;
            }

            $puestoExcel = trim((string) $hoja->getCell('C' . $fila)->getValue());
            if ($puestoExcel === '') {
                $log("Fila $fila: sin puesto (depActual='$depActual') -> SALTA");
                continue;
            }

            $depOk    = $this->compararTexto($depActual, $departamento);
            $puestoOk = $this->puestoCoincide($puesto, $puestoExcel);

            $log("Fila $fila: dep='$depActual' puesto='$puestoExcel' | depOk=" .
                 ($depOk ? 'SI' : 'no') . " puestoOk=" . ($puestoOk ? 'SI' : 'no'));

            if ($depOk && $puestoOk) {
                $log("   >>> MATCH en fila $fila");
                $actividad = trim((string) $hoja->getCell('D' . $fila)->getValue());

                foreach ($categorias as $col => $nombreCat) {
                    $valorCelda = trim((string) $hoja->getCell($col . $fila)->getValue());
                    if ($valorCelda === '') continue;

                    $items = preg_split('/\r\n|\r|\n/', $valorCelda);
                    foreach ($items as $item) {
                        $item = trim($item);
                        if ($item === '') continue;
                        $resultado[] = [
                            'categoria' => $nombreCat,
                            'actividad' => $actividad,
                            'equipo'    => $item,
                        ];
                        $log("      + [$nombreCat] $item");
                    }
                }
            }
        }

        $log('Total de equipos encontrados: ' . count($resultado));
        $log('========================================================');

        echo json_encode($resultado);
    }

    // Funcion para obtener las herramientas del excel
    function getToolExcel()
    {        
        $logFile = __DIR__ . '/epp_debug.log';
        $log = function ($msg) use ($logFile) {
            file_put_contents($logFile, '[' . date('H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
        };

        $log('==================== NUEVA PETICION ====================');

        $departamento = trim($_GET['departamento'] ?? '');
        $puesto       = trim($_GET['puesto'] ?? '');
        $log("Departamento recibido: '$departamento'");
        $log("Puesto recibido: '$puesto'");
        $log("Departamento normalizado: '" . $this->normalizaTexto($departamento) . "'");
        $log("Puesto normalizado: '" . $this->normalizaTexto($puesto) . "'");

        $rutaExcel = __DIR__ . '/FO-OSHPS012-01_Identificación_y_selección_de_EPP.xlsx';
        $log("Ruta Excel: $rutaExcel");

        if (!file_exists($rutaExcel)) {
            $log('ERROR: el archivo NO existe en esa ruta');
            http_response_code(404);
            echo json_encode(['error' => 'No se encontró el archivo Excel']);
            return;
        }
        $log('OK: archivo encontrado');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($rutaExcel);

        // Listado de  las hojas
        foreach ($spreadsheet->getSheetNames() as $i => $nombreHoja){
            $log("Hoja [$i]: '$nombreHoja'");
        }
        $hoja = $spreadsheet->getActiveSheet();
        $log('Hoja activa: ' . $hoja->getTitle());

        $categorias = [
            'E' => 'Cabeza',
            'F' => 'Ojos y cara',
            'G' => 'Oídos',
            'H' => 'Aparato respiratorio',
            'I' => 'Extremidades superiores',
            'J' => 'Tronco',
            'K' => 'Extremidades inferiores',
            'L' => 'Otros',
        ];

        $resultado = [];
        $maxFila   = $hoja->getHighestDataRow();
        $depActual = '';
        $log("Ultima fila con datos: $maxFila");

        for ($fila = 6; $fila <= $maxFila; $fila++) {
            $depCelda = trim((string) $hoja->getCell('B' . $fila)->getValue());
            if ($depCelda !== '') {
                $depActual = $depCelda;
            }

            $puestoExcel = trim((string) $hoja->getCell('C' . $fila)->getValue());
            if ($puestoExcel === '') {
                $log("Fila $fila: sin puesto (depActual='$depActual') -> SALTA");
                continue;
            }

            $depOk    = $this->compararTexto($depActual, $departamento);
            $puestoOk = $this->puestoCoincide($puesto, $puestoExcel);

            $log("Fila $fila: dep='$depActual' puesto='$puestoExcel' | depOk=" .
                 ($depOk ? 'SI' : 'no') . " puestoOk=" . ($puestoOk ? 'SI' : 'no'));

            if ($depOk && $puestoOk) {
                $log("   >>> MATCH en fila $fila");
                $actividad = trim((string) $hoja->getCell('D' . $fila)->getValue());

                foreach ($categorias as $col => $nombreCat) {
                    $valorCelda = trim((string) $hoja->getCell($col . $fila)->getValue());
                    if ($valorCelda === '') continue;

                    $items = preg_split('/\r\n|\r|\n/', $valorCelda);
                    foreach ($items as $item) {
                        $item = trim($item);
                        if ($item === '') continue;
                        $resultado[] = [
                            'categoria' => $nombreCat,
                            'actividad' => $actividad,
                            'equipo'    => $item,
                        ];
                        $log("      + [$nombreCat] $item");
                    }
                }
            }
        }

        $log('Total de equipos encontrados: ' . count($resultado));
        $log('========================================================');

        echo json_encode($resultado);
    }

    /* ---------------------------------------------------------------------------------------------
    Acciones para nuevo guardado de datos desde la BD    
    ---------------------------------------------------------------------------------------------- */ 
    // Solicitudes hechas por / bajo la sesión actual
    function misSolicitudes()
    {
        $c = new ClassConexion(); $conn = $c->conexion('TLX002MXDB');
        $ibm = $_SESSION['ibm'] ?? '';
        $q = "SELECT VET_id,VET_tipo,VET_motivo,VET_estado,VET_fecha
              FROM tblEPPVale WHERE VET_ibmSol = ? OR VET_ibmSup = ? ORDER BY VET_id DESC";
        $r = sqlsrv_query($conn, $q, array($ibm, $ibm));
        $arr = array();
        while ($row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC)) {
            $arr[] = array('folio'=>$row['VET_id'],'tipo'=>$row['VET_tipo'],'motivo'=>$row['VET_motivo'],
                'estado'=>$row['VET_estado'],
                'fecha'=>$row['VET_fecha'] ? $row['VET_fecha']->format('d/m/Y H:i') : '');
        }

        echo json_encode($arr);
    }

    // ¿La sesión es de almacén? + conteo de pendientes (para el badge)
    function esAlmacen()
    {
        $ibm = $_SESSION['ibm'] ?? '';
        $almacenIBMs = ['27751', '11111'];
        $es = in_array($ibm, $almacenIBMs);
        $count = 0;
        if ($es) {
            $c = new ClassConexion(); $conn = $c->conexion('TLX002MXDB');
            $r = sqlsrv_query($conn, "SELECT COUNT(*) AS n FROM tblEPPVale WHERE VET_entrego=0 AND VET_estado=0");
            $row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC);
            $count = $row['n'];
        }
        echo json_encode(['esAlmacen'=>$es, 'pendientes'=>intval($count)]);
    }

    // Pendientes de un empleado (para el modal de almacén)
    function pendientesPorEmp()
    {
        $c = new ClassConexion(); $conn = $c->conexion('TLX002MXDB');
        $ibm = trim($_GET['ibm'] ?? '');
        $q = "SELECT VET_id,VET_tipo,VET_motivo,VET_fecha FROM tblEPPVale
              WHERE VET_ibmSol = ? AND VET_entrego=0 AND VET_estado=0 ORDER BY VET_id DESC";
        $r = sqlsrv_query($conn, $q, array($ibm));
        $arr = array();
        while ($row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC)) {
            $arr[] = array('folio'=>$row['VET_id'],'tipo'=>$row['VET_tipo'],'motivo'=>$row['VET_motivo'],
                'fecha'=>$row['VET_fecha'] ? $row['VET_fecha']->format('d/m/Y H:i') : '');
        }
        echo json_encode($arr);
    }

    function entregarVale()
    {
        $c = new ClassConexion(); $conn = $c->conexion('TLX002MXDB');
        $folio   = intval($_POST['folio'] ?? 0);
        $clave   = trim($_POST['clave'] ?? '');
        $almacen = $_SESSION['ibm'] ?? '';

        $r = sqlsrv_query($conn, "SELECT VET_clave, VET_entrego FROM tblEPPVale WHERE VET_id = ?", array($folio));
        $row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC);
        if (!$row) { echo json_encode(['ok'=>false,'msg'=>'Vale no encontrado']); return; }
        if ($row['VET_entrego']) { echo json_encode(['ok'=>false,'msg'=>'Este vale ya fue entregado']); return; }
        if (!password_verify($clave, $row['VET_clave'])) {
            echo json_encode(['ok'=>false,'msg'=>'Clave incorrecta']); return;
        }
        $u = "UPDATE tblEPPVale SET VET_entrego=1, VET_recibio=1, VET_almacen=?, VET_estado=1,
              VET_fechaEntrega=GETDATE() WHERE VET_id=? AND VET_entrego=0";
        $ru = sqlsrv_query($conn, $u, array($almacen, $folio));
        echo json_encode(['ok'=>(bool)$ru]);
    }

    // Paso 2: TRABAJADOR confirma recepción con SU clave.
    function recibirVale()
    {
        $c = new ClassConexion(); $conn = $c->conexion('TLX002MXDB');
        $folio = intval($_POST['folio'] ?? 0);
        $clave = trim($_POST['clave'] ?? '');
        $r = sqlsrv_query($conn, "SELECT VET_clave, VET_entrego, VET_recibio
                                  FROM tblEPPVale WHERE VET_id = ?", array($folio));
        $row = sqlsrv_fetch_array($r, SQLSRV_FETCH_ASSOC);
        if (!$row) { echo json_encode(['ok'=>false,'msg'=>'Vale no encontrado']); return; }
        if (!$row['VET_entrego']) {
            echo json_encode(['ok'=>false,'msg'=>'Aún no ha sido entregado por almacén']); return;
        }
        if ($row['VET_recibio']) {
            echo json_encode(['ok'=>false,'msg'=>'Este vale ya fue recibido']); return;
        }
        if (!password_verify($clave, $row['VET_clave'])) {
            echo json_encode(['ok'=>false,'msg'=>'Clave incorrecta']); return;
        }
        $u = "UPDATE tblEPPVale SET VET_recibio=1 WHERE VET_id=?";
        $ru = sqlsrv_query($conn, $u, array($folio));
        echo json_encode(['ok'=>(bool)$ru]);
    }

    // Rechazar solicitud
    function rechazarVale()
    {
        $c = new ClassConexion(); $conn = $c->conexion('TLX002MXDB');
        $folio = intval($_POST['folio'] ?? 0);
        $r = sqlsrv_query($conn, "UPDATE tblEPPVale SET VET_estado=2 WHERE VET_id=?", array($folio));
        echo json_encode(['ok'=>(bool)$r]);
    }
}

$EppObj = new EPP();
if (isset($_GET['ListEppBasico'])) {
    $EppObj->ListEppBasico(3);
} else if (isset($_GET['ListEppEspecifico'])) {
    $EppObj->ListEppBasico(1);
} else if (isset($_GET['ListEppBPM'])) {
    $EppObj->ListEppBasico(2);
} else if (isset($_GET['saveEPP'])) {
    $EppObj->saveEPP();
} else if (isset($_GET['tblEPPEnc'])) {
    $session = $_GET['session'];
    $EppObj->tblEPPEnc($session);
} else if (isset($_GET['tblEPPSubEnc'])) {
    $EppObj->tblEPPSubEnc();
} else if (isset($_GET['tblEPPReporte'])) {
    $EppObj->tblEPPReporte();
} else if (isset($_GET['getEPPExcel'])) {
    $EppObj->getEPPExcel();
} else if (isset($_GET['getToolExcel'])) {
    $EppObj->getToolExcel();
} else if (isset($_GET['misSolicitudes'])) {
    $EppObj->misSolicitudes();
} else if (isset($_GET['esAlmacen'])) {
    $EppObj->esAlmacen();
} else if (isset($_GET['pendientesPorEmp'])) {
    $EppObj->pendientesPorEmp();
} else if (isset($_GET['entregarVale'])) {
    $EppObj->entregarVale();
} else if (isset($_GET['recibirVale'])) {
    $EppObj->recibirVale();
} else if (isset($_GET['rechazarVale'])) {
    $EppObj->rechazarVale();
}


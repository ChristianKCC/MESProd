<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../../Session/seguridad.php';
require_once __DIR__ . '/../../fpdf/fpdf.php';

function t($s) { return iconv('UTF-8','windows-1252//TRANSLIT',(string)$s); }

// Sin uso al momento (Usar en caso de generar clave aleatoriamente)
// function generarClave($long = 6) {
//     $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
//     $clave = '';
//     for ($i=0;$i<$long;$i++) $clave .= $chars[random_int(0, strlen($chars)-1)];
//     return $clave;
// }

// Busqueda de firmas en carpetas
function buscarFirma($noemp) {
    if (empty($noemp)) return null;
    $extensiones = ['png', 'jpg', 'jpeg'];
    foreach ($extensiones as $ext) {
        $ruta = __DIR__ . "/../firmas/" . $noemp . "." . $ext;
        if (file_exists($ruta)) return ['ruta'=>$ruta, 'size'=>13];
    }
    foreach ($extensiones as $ext) {
        $ruta = __DIR__ . "/../../FirmaDigital/firmas/" . $noemp . "." . $ext;
        if (file_exists($ruta)) return ['ruta'=>$ruta, 'size'=>40];
    }
    return null;
}

$conexion = new ClassConexion();
$conn = $conexion->conexion('TLX002MXDB');

if (isset($_GET['folio'])) {
    // Busqueda de datos en caso de existir para mostrar la informacion al generar el PDF
    $folio = intval($_GET['folio']);
    $rEnc = sqlsrv_query($conn, "SELECT * FROM tblEPPVale WHERE VET_id = ?", array($folio));
    $enc  = sqlsrv_fetch_array($rEnc, SQLSRV_FETCH_ASSOC);
    if (!$enc) { http_response_code(404); exit('Vale no encontrado'); }

    // Busqueda de detalles para mostrar los EPP p herramientas solicitadas
    $rDet = sqlsrv_query($conn, "SELECT * FROM tblEPPValeDet WHERE VED_idVale = ?", array($folio));
    $items = array();
    while ($d = sqlsrv_fetch_array($rDet, SQLSRV_FETCH_ASSOC)) {
        $items[] = array('categoria'=>$d['VED_categoria'],'equipo'=>$d['VED_equipo'], 'cantidad'=>$d['VED_cantidad'],'unidad'=>$d['VED_unidad']);
    }

    // Nombre del supervisor relacion generada con VET_ibmSup
    $nombreSup = '';
    if (!empty($enc['VET_ibmSup'])) {
        $rSup = sqlsrv_query($conn,
            "SELECT Nombre FROM TLX032MXDB.dbo.tblEmpleados WHERE NoEmp = ?",
            array($enc['VET_ibmSup']));
        $rowSup = sqlsrv_fetch_array($rSup, SQLSRV_FETCH_ASSOC);
        if ($rowSup) $nombreSup = $rowSup['Nombre'];
    }

    // Nombre de almacén (CONDICIONAL => solo si ya se entregó el EPP e Herramienta y existe un IBM)
    $nombreAlm = '';
    if ($enc['VET_estado'] == 1 && !empty($enc['VET_almacen'])) {
        $rAlm = sqlsrv_query($conn,
            "SELECT Nombre FROM TLX032MXDB.dbo.tblEmpleados WHERE NoEmp = ?",
            array($enc['VET_almacen']));
        $rowAlm = sqlsrv_fetch_array($rAlm, SQLSRV_FETCH_ASSOC);
        if ($rowAlm) $nombreAlm = $rowAlm['Nombre'];
    }

    // Busqueda de fecha de entrega segun el momento en el que se haga la misma
    $fechaEntrega = ($enc['VET_fechaEntrega'] instanceof DateTime) ? $enc['VET_fechaEntrega']->format('d/m/Y') : '';

    // data con el contenido a mostrar en el PDF
    $data = array(
        'tipo'=>$enc['VET_tipo'],'noemp'=>$enc['VET_ibmSol'],'nombre'=>$enc['VET_nombre'],
        'departamento'=>$enc['VET_departamento'],'puesto'=>$enc['VET_puesto'],
        'motivo'=>$enc['VET_motivo'],'clave'=>$enc['VET_clave'],'folio'=>$enc['VET_id'],
        'nombreSup'=>$nombreSup, 'nombreAlm'=>$nombreAlm,
        'ibmSup'=>$enc['VET_ibmSup'], 'ibmAlm'=>$enc['VET_almacen'],
        'recibio'=>$enc['VET_recibio'], 'entrego'=>$enc['VET_entrego'], 'estado'=>$enc['VET_estado'],
        'fechaEntrega'=>$fechaEntrega, 'items'=>$items
    );
} 
// Caso de que no exista y se deba de hacer una nueva insercion
  else {    
    $data = json_decode($_POST['payload'] ?? '', true);
    if (!$data) { http_response_code(400); exit('Datos inválidos'); }

    // Uso anterior de clave de forma aleatoria
    // $clave  = generarClave();
    
    // Creacion de clave manual por medio de hash
    $clavePlano = trim($data['clave'] ?? '');
    if (strlen($clavePlano) < 4) { http_response_code(400); exit('Clave inválida'); }
    $claveHash = password_hash($clavePlano, PASSWORD_DEFAULT);

    // Obtencion del IBM de la sesion para IBM del supervisor
    $ibmSup = $_SESSION['ibm'] ?? '';

    // QUERY de insercion de datos
    $qIns = "INSERT INTO tblEPPVale
             (VET_tipo,VET_ibmSol,VET_ibmSup,VET_nombre,VET_departamento,VET_puesto,VET_motivo,VET_clave)
             VALUES (?,?,?,?,?,?,?,?)";
    $rIns = sqlsrv_query($conn, $qIns, array(
        $data['tipo'] ?? 'epp', $data['noemp'] ?? '', $ibmSup, $data['nombre'] ?? '',
        $data['departamento'] ?? '', $data['puesto'] ?? '', $data['motivo'] ?? '', $claveHash
    ));

    $folio = null;

    // Realizar insercion de detalles en forma consecutiva despues del primer registro tomando el id para su relacion
    if ($rIns) {
        $rId = sqlsrv_query($conn, "SELECT @@IDENTITY AS id");
        $rowId = sqlsrv_fetch_array($rId, SQLSRV_FETCH_ASSOC);
        $folio = $rowId['id'];

        $qDet = "INSERT INTO tblEPPValeDet (VED_idVale,VED_categoria,VED_equipo,VED_cantidad,VED_unidad)
                 VALUES (?,?,?,?,?)";
        foreach (($data['items'] ?? []) as $it) {
            sqlsrv_query($conn, $qDet, array(
                $folio, $it['categoria'] ?? '', $it['equipo'] ?? '',
                intval($it['cantidad'] ?? 1), 'Pza'
            ));
        }
    }

    // Carga de datos
    $data['folio'] = $folio;
    $data['nombreSup'] = '';
    $data['nombreAlm'] = '';
    $data['ibmSup'] = $ibmSup;
    $data['ibmAlm'] = '';
    $data['fechaEntrega'] = '';
    $data['recibio'] = 0;
    $data['entrego'] = 0;
    $data['estado'] = 0;
}

// Validacion de tipo para cargar encabezados correctos
$tipo  = $data['tipo'] ?? 'epp';
$esEpp = ($tipo === 'epp');

// Caso de solicitud de EPP
if ($esEpp) {
    $titulo = 'VALE DE ENTREGA DE EQUIPO DE PROTECCIÓN PERSONAL';
    $tituloTabla = 'Equipo de protección personal';
    $compromiso = [
        'Así mismo el trabajador se compromete a:',
        '- Usar el EPP conforme a las instrucciones y capacitación recibida.',
        '- Mantener el equipo en buen estado y reportar cualquier daño o pérdida.',
        '- No modificar el equipo sin autorización.',
    ];
} 
// Caso de solicitud de herramientas
   else {
    $titulo = 'VALE DE ENTREGA DE HERRAMIENTAS';
    $tituloTabla = 'Herramientas';
    $compromiso = [
        'Así mismo el trabajador se compromete a:',
        '- Usar las herramientas conforme a las instrucciones y capacitación recibida.',
        '- Mantener las herramientas en buen estado y reportar cualquier daño o pérdida.',
        '- Devolver las herramientas en las condiciones establecidas.',
    ];
}

// Asignacion de valores segun la QUERY a valores asignados
$nombre       = $data['nombre'] ?? '';
$noemp        = $data['noemp'] ?? '';
$departamento = $data['departamento'] ?? '';
$puesto       = $data['puesto'] ?? '';
$motivo       = $data['motivo'] ?? '';
$clave        = $data['clave'] ?? '';
$items        = $data['items'] ?? [];
$nombreSup    = $data['nombreSup'] ?? '';
$nombreAlm    = $data['nombreAlm'] ?? '';
$ibmSup       = $data['ibmSup'] ?? '';
$ibmAlm       = $data['ibmAlm'] ?? '';
$recibio      = $data['recibio'] ?? 0;
$entrego      = $data['entrego'] ?? 0;
$estado       = $data['estado'] ?? 0;
$fechaEntrega = $data['fechaEntrega'] ?? ''; 

// Array de motivos
$motivos = [
    'Olvido'       => 'Olvido',
    'Perdida'      => 'Pérdida',
    'Reposicion'   => 'Reposición vida útil',
    'Nuevo/Cambio' => 'Nuevo ingreso / Cambio de puesto',
];

// Diseño de PDF
// Hoja carta, vale en cuadrante superior izquierdo
$QW = 107.95; // ancho de 1/4 de carta
$QH = 139.7;  // alto  de 1/4 de carta

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->SetMargins(4, 4, 4);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$navy = array(28, 49, 94);
$W = 99.95;

// Encabezado con logo
$logo = __DIR__ . '/../../img/imglogoprosede.png';
if (file_exists($logo)) {
    $pdf->Image($logo, 4, 4, 40);
    $pdf->SetXY(4, 11);
} else {
    $pdf->SetXY(4, 4);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(50, 4, t('KIMBERLY-CLARK'), 0, 2);
}
$pdf->SetX(4);
$pdf->SetFont('Arial', 'I', 5);

// Logo derecho de seguridad
$logoDer = __DIR__ . '/../../img/Seguridad.png';
if (file_exists($logoDer)) {
    $pdf->Image($logoDer, 96,4,8);
} else {
    $pdf->SetXY(96, 4);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(50, 4, t('SEGURIDAD KCC'), 0, 2);
}

// Identificación del documento
$pdf->SetXY(56, 4);
$pdf->SetFont('Arial', '', 4.5);
$pdf->Cell(37.95, 2.6, t('Identificación del Documento'), 0, 2, 'R');
$pdf->Cell(37.95, 2.6, t('FO-OSHPS12-03    Rev. 00'), 0, 2, 'R');
$pdf->Cell(37.95, 2.6, t('Referencia: NOM-017-STPS-2024'), 0, 2, 'R');

// Barra de título
$pdf->SetXY(4, 14);
$pdf->SetFillColor($navy[0], $navy[1], $navy[2]);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->Cell($W, 5, t($titulo), 0, 1, 'C', true);
$pdf->SetTextColor(0);

$y = 21;

// Motivo
$pdf->SetXY(4, $y);
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(48, 4, t('Motivo:'), 0, 2);
$pdf->SetFont('Arial', '', 5.5);
foreach ($motivos as $k => $label) {
    $mark = ($motivo === $k) ? 'X' : ' ';
    $pdf->SetX(4);
    $pdf->Cell(4, 3.6, '[' . $mark . ']', 0, 0);
    $pdf->Cell(44, 3.6, t($label), 0, 2);
}

// Datos del trabajador
$pdf->SetXY(56, $y);
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(47.95, 4, t('Datos del trabajador'), 0, 2);
$pdf->SetFont('Arial', '', 5.5);
foreach (['Nombre: '.$nombre, 'No IBM: '.$noemp, 'Área: '.$departamento, 'Puesto: '.$puesto] as $d) {
    $pdf->SetX(56);
    $pdf->Cell(47.95, 3.6, t($d), 0, 2);
}

// Tabla de equipo solicitado
$pdf->SetXY(4, $y + 20);
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell($W, 4, t($tituloTabla), 0, 1, 'C');

$pdf->SetX(4);
$pdf->SetFillColor($navy[0], $navy[1], $navy[2]);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell(60, 4, t('Equipo'), 1, 0, 'C', true);
$pdf->Cell(20, 4, t('Cantidad'), 1, 0, 'C', true);
$pdf->Cell(19.95, 4, t('Unidad'), 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 5.5);

// Caso de no existir datos disponibles
if (empty($items)) {
    $pdf->SetX(4);
    $pdf->Cell($W, 4, t('Sin equipo seleccionado'), 1, 1, 'C');
} else {
    foreach ($items as $it) {
        $pdf->SetX(4);
        $pdf->Cell(60, 4, t($it['equipo'] ?? ''), 1, 0, 'L');
        $pdf->Cell(20, 4, t($it['cantidad'] ?? 1), 1, 0, 'C');
        $pdf->Cell(19.95, 4, t('Pza'), 1, 1, 'C');
    }
}

// Generacion de fecha y folios de solicitud
$pdf->Ln(2);
$pdf->SetX(4);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell($W, 4, t('Folio de entrega: #' . $folio), 0, 1);
$pdf->SetX(4);
$pdf->SetFont('Arial', 'B', 5.5);
$pdf->Cell($W, 4, t('Fecha de entrega: ' . ($fechaEntrega !== '' ? $fechaEntrega : '___________________')));
$pdf->Ln(4);

// Seccion de firmas
$pdf->Ln(15);
$yf = $pdf->GetY();

// Dibujar las líneas
$pdf->Line(8, $yf, 38, $yf);
$pdf->Line(40, $yf, 68, $yf);
$pdf->Line(70, $yf, 100, $yf);

// Imagen de firmas
// Trabajador: solo si ya recibió (CONDICIONAL => VET_recibio=1)
if ($recibio == 1) {
    $f = buscarFirma($noemp);
    if ($f) $pdf->Image($f['ruta'], 14, $yf - 10, $f['size'] > 20 ? 20 : $f['size']);
}

// Supervisor: siempre que exista su firma
$fSup = buscarFirma($ibmSup);
if ($fSup) $pdf->Image($fSup['ruta'], 46, $yf - 10, $fSup['size'] > 20 ? 20 : $fSup['size']);

// Almacén: solo si entregó y estado cerrado y hay IBM
if ($estado == 1 && $entrego == 1 && !empty($ibmAlm)) {
    $fAlm = buscarFirma($ibmAlm);
    if ($fAlm) $pdf->Image($fAlm['ruta'], 76, $yf - 10, $fAlm['size'] > 20 ? 20 : $fAlm['size']);
}

// Nombres arriba de las líneas (en blanco si aún no existen)
$pdf->SetXY(4, $yf - 3);
$pdf->SetFont('Arial', 'I', 4.5);
$pdf->Cell(34, 3, t(ucwords(strtolower($nombre))), 0, 0, 'C');       
$pdf->Cell(30, 3, t(ucwords(strtolower($nombreSup))), 0, 0, 'C');    
$pdf->Cell(31.95, 3, t(ucwords(strtolower($nombreAlm))), 0, 1, 'C'); 

// Textos de barras
$pdf->SetXY(4, $yf + 0.5);
$pdf->SetFont('Arial', '', 4.5);
$pdf->Cell(34, 3, t('Nombre y firma de trabajador'), 0, 0, 'C');
$pdf->Cell(30, 3, t('Nombre y firma de supervisor'), 0, 0, 'C');
$pdf->Cell(31.95, 3, t('Nombre y firma de almacén'), 0, 1, 'C');

// Leyenda de compromiso
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 4.5);
foreach ($compromiso as $linea) {
    $pdf->SetX(4);
    $pdf->Cell($W, 2.8, t($linea), 0, 1, 'L');
}

// Guías de corte del cuadrante
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.1);
$pdf->Line($QW, 0, $QW, $QH);   // borde derecho
$pdf->Line(0, $QH, $QW, $QH);   // borde inferior
$pdf->SetDrawColor(0, 0, 0);

// Guard anti-corrupt para evitar anti-corrupcion
if (ob_get_length()) ob_end_clean();
$pdf->Output('I', 'Vale_' . strtoupper($tipo) . '.pdf');
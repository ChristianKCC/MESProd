<?php
/* =====================================================================
   php/index.php  -  Endpoints de enlaces EMC (multipropósito por tipo)
   ---------------------------------------------------------------------
   POST  accion=guardarEnlace  + tipo  -> recorta y guarda el enlace
   GET   ?getEnlaceActivo&tipo=N        -> devuelve el enlace activo (JSON)

   tipo: 1 = Excel (Matriz), 2 = PowerPoint (Organigrama), ...
   ===================================================================== */

/* Sesion / seguridad  (KCMes/EMC/php/index.php -> subir 2 niveles) */
require_once(dirname(__DIR__, 2) . "/Session/seguridad.php");

header('Content-Type: application/json; charset=utf-8');

require_once(dirname(__DIR__, 2) . "/conexion.php");
require_once(__DIR__ . "/funciones_enlace.php");

$ClassConexion = new ClassConexion();
$conn = $ClassConexion->conexion("TLX002MXDB");

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos.']);
    exit;
}

/* =====================================================================
   GUARDAR ENLACE  (POST)
   ===================================================================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['accion'] ?? '') === 'guardarEnlace'
) {

    $tipo = intval($_POST['tipo'] ?? 0);
    $nombre = trim($_POST['nombre_archivo'] ?? '');
    $raw = trim($_POST['enlace'] ?? '');

    if ($tipo <= 0) {
        echo json_encode(['success' => false, 'message' => 'Tipo de enlace no especificado.']);
        exit;
    }
    if ($nombre === '' || $raw === '') {
        echo json_encode(['success' => false, 'message' => 'Falta el nombre del archivo o el enlace.']);
        exit;
    }

    $cut = recortarEnlaceEMC($raw);
    if ($cut === null) {
        echo json_encode([
            'success' => false,
            'message' => 'El enlace no es válido. Debe contener "sourcedoc=" (pega el enlace o el código embed de Office Online).'
        ]);
        exit;
    }

    $ibm = isset($_SESSION['ibm']) ? intval($_SESSION['ibm']) : null;

    /* 1) Desactivar el activo anterior SOLO de este tipo */
    sqlsrv_query(
        $conn,
        "UPDATE tblMXPREnlaceEMC SET activo = 0 WHERE activo = 1 AND tipo = ?",
        [$tipo]
    );

    /* 2) Insertar el nuevo como activo */
    $sql = "INSERT INTO tblMXPREnlaceEMC
                (tipo, nombre_archivo, enlace, sourcedoc, fecha_registro, ibm_registro, activo)
            VALUES (?, ?, ?, ?, GETDATE(), ?, 1)";

    $res = sqlsrv_query($conn, $sql, [$tipo, $nombre, $cut['enlace'], $cut['sourcedoc'], $ibm]);

    if ($res === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar el enlace en la base de datos.',
            'detalle' => sqlsrv_errors()
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Enlace guardado correctamente.',
        'tipo' => $tipo,
        'enlace' => $cut['enlace'],
        'sourcedoc' => $cut['sourcedoc'],
        'embed' => construirEnlaceEmbed($cut['enlace'], $tipo)
    ]);
    exit;
}

/* =====================================================================
   CONSULTAR ENLACE ACTIVO  (GET)
   ===================================================================== */
if (isset($_GET['getEnlaceActivo'])) {

    $tipo = intval($_GET['tipo'] ?? EMC_TIPO_EXCEL);
    $row = obtenerEnlaceActivo($conn, $tipo);

    if ($row && isset($row['fecha_registro']) && $row['fecha_registro'] instanceof DateTime) {
        $row['fecha_registro'] = $row['fecha_registro']->format('d/m/Y H:i:s');
    }

    echo json_encode([
        'success' => (bool) $row,
        'data' => $row,
        'embed' => $row ? construirEnlaceEmbed($row['enlace'], $tipo) : null
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
exit;
?>
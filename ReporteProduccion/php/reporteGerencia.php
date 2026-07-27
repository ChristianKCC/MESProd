<?php
require_once '../../vendor/autoload.php';
require_once 'pdf/PdfGenerator.php';
require_once "./services/consultasDireccion.php";
require_once "./hooks/transformarDatos.php";
require_once "./hooks/transformarTabbi.php";
require_once "./hooks/transformarProgramaTNT.php";
require_once "./hooks/transformarPrograma.php";
require_once "./hooks/transformarSpooler.php";
require_once "./hooks/transformarHook.php";

ini_set('memory_limit', '256M');
ini_set('max_execution_time', 120); // 2 minutos

// ─────────────────────────────────────────────
// 1. PARÁMETROS
// ─────────────────────────────────────────────
$fecha    = $_GET['fecha'];
$departamento = $_GET['departamento'] ?? null; // No se usa en esta versión, pero se puede mantener para compatibilidad
$logoPath = '../../img/imglogoprosede.png';
$diasMes  = (int) date('t', strtotime($fecha));

// Orden de departamentos en el reporte general
$ordenDepartamentos = [1, 24, 25];
$nombresDepto = [
    1  => 'CUIDADO INFANTIL',
    24 => 'PROTECCIÓN FEMENINA',
    25 => 'INCONTINENCIA',
];

// ─────────────────────────────────────────────
// 2. OBTENER DATOS DE LA BD
// ─────────────────────────────────────────────
$direccionObj = new ReporteDiario();

// Ahora obtener datos de TODOS los departamentos de una sola vez
// Asumiendo que tu función obtenerUSTD() ahora devuelve todos los deptos
$dataDiariaCompleta = $direccionObj->obtenerUSTD($fecha); // Sin parámetro de depto
$dataTabbiCompleta  = $direccionObj->obtenerDatosTabbi($fecha); // Si TABBI también se obtiene sin filtrar por depto
$dataPlanCompleta   = $direccionObj->obtenerPlanProduccion($fecha); // Sin parámetro de depto
$dataPlanTNTCompleta  = $direccionObj->obtenerPlanproduccionTNT($fecha); // Sin parámetro de depto
$dataSpoolerCompleta = $direccionObj->obtenerDatosSpooler($fecha);   // ← NUEVO
$dataHookCompleta    = $direccionObj->obtenerDatosHook($fecha);      // ← NUEVO (Hook, MS01)

// Organizar datos por departamento si es necesario
// Si ya vienen organizados como [1 => [...], 24 => [...], 25 => [...]]:
if (isset($dataDiariaCompleta[1])) {
    $datosDeptos = $dataDiariaCompleta; // Ya está organizado
} else {
    // Si vienen en array simple, organizarlos por NoDepto
    $datosDeptos = [];
    foreach ($dataDiariaCompleta as $registro) {
        $noDepto = $registro['NoDepto'];
        if (!isset($datosDeptos[$noDepto])) {
            $datosDeptos[$noDepto] = [];
        }
        $datosDeptos[$noDepto][] = $registro;
    }
}

// El plan ya viene agrupado por NoDepto desde obtenerPlanProduccion()
// Se asigna directamente para filtrar por depto en el foreach siguiente
$dataPlanPorDepto = $dataPlanCompleta;

// ─────────────────────────────────────────────
// 3. GENERAR PDF CON TODOS LOS DEPARTAMENTOS
// ─────────────────────────────────────────────
$pdf = new PdfGenerator();
$esPrimero = true;

foreach ($ordenDepartamentos as $noDepto) {
    // Verificar si este departamento tiene datos
    if (!isset($datosDeptos[$noDepto]) || empty($datosDeptos[$noDepto])) {
        continue;
    }

    // ── Transformar datos USTD para este departamento ──
    $resultado = transformarDatos($datosDeptos[$noDepto], $fecha, $noDepto);
    $fechas    = $resultado['fechas'];
    $tablas    = $resultado['tablas'];

    // ── Transformar datos Programa para este departamento ──
    // Solo las filas que pertenecen a este departamento (por NoDepto)
    $dataPlanDepto = $dataPlanPorDepto[$noDepto] ?? [];
    
    $programa = transformarPrograma($dataPlanDepto, $fecha, $diasMes);
    $programa['nombreDepto'] = $nombresDepto[$noDepto];

    // ── Agregar al PDF ──
    if ($esPrimero) {
        // Primera sección — inicializar con header
        $pdf->iniciar($fechas, $tablas, $programa, $fecha, $logoPath);
        $esPrimero = false;
    } else {
        // Departamentos siguientes — agregar sección
        $pdf->agregarSeccion($fechas, $tablas, $programa, $nombresDepto[$noDepto]);
    }
}

// ── Finalizar y mostrar PDF ──
// ── Sección TABBI ──
if (!empty($dataTabbiCompleta)) {
    $resultadoTabbi = transformarTabbi($dataTabbiCompleta, $fecha);

    if (!empty($resultadoTabbi['tablas'])) {

        $programaTNT = [];
        if (!empty($dataPlanTNTCompleta)) {
            $programaTNT = transformarProgramaTNT($dataPlanTNTCompleta, $fecha, $diasMes);
        }

        // ── Spooler: transformar y fusionar sus tablas en el mismo bloque TNT ──
        $resultadoSpooler = ['tablas' => []];
        if (!empty($dataSpoolerCompleta) && !isset($dataSpoolerCompleta['ok'])) {
            $resultadoSpooler = transformarSpooler($dataSpoolerCompleta, $fecha);
        }

        // Fusionar tablas: TABBI primero, luego Spooler
        // agregarTabbi() recibe un único array $tablas con todas las operaciones
        $tablasUnificadas = array_merge(
            $resultadoTabbi['tablas'],
            $resultadoSpooler['tablas']
        );

        // Las fechas del encabezado: unión de ambas (por si difieren)
        $fechasUnificadas = array_values(array_unique(array_merge(
            $resultadoTabbi['fechas'],
            $resultadoSpooler['fechas']
        )));
        sort($fechasUnificadas);

        $pdf->agregarTabbi($fechasUnificadas, $tablasUnificadas, $programaTNT);
    }
}

// ── Hook: hoja propia (a ancho completo), separada de TABBI/Spooler ──
// Hoy solo tiene una clave, pero puede crecer a 2 o más y necesita su
// propio espacio en vez de competir con TABBI/Spooler. Se dibuja aparte
// para que no dependa de si TABBI tuvo datos ese día.
if (!empty($dataHookCompleta) && !isset($dataHookCompleta['ok'])) {
    $resultadoHook = transformarHook($dataHookCompleta, $fecha);
    if (!empty($resultadoHook['tablas'])) {
        $pdf->agregarHook($resultadoHook['fechas'], $resultadoHook['tablas']);
    }
}

$pdf->finalizar('reporte_diario_gerencia.pdf');
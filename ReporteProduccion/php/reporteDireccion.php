<?php
require_once '../../vendor/autoload.php';
require_once 'pdf/PdfGenerator2.php';
require_once "./services/consultasDireccion.php";
require_once "./hooks/transformarDatos2.php";
require_once "./hooks/transformarTabbi2.php";
require_once "./hooks/transformarProgramaTNT.php";
require_once "./hooks/transformarPrograma.php";
require_once "./hooks/transformarSpooler2.php";
require_once "./hooks/transformarHook2.php";

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
$ordenDepartamentos = [1, 25, 24];
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

// ── Cuidado Infantil (1) + Incontinencia (25) van juntos en la primera
// hoja, con sus 2 tablas de Programa lado a lado. Si algún día falta data
// de cualquiera de los dos, se cae al flujo normal (iniciar/agregarSeccion)
// más abajo, para no romper el reporte.
$idParA = 1;  // Cuidado Infantil
$idParB = 25; // Incontinencia

$tieneParA = isset($datosDeptos[$idParA]) && !empty($datosDeptos[$idParA]);
$tieneParB = isset($datosDeptos[$idParB]) && !empty($datosDeptos[$idParB]);

if ($tieneParA && $tieneParB) {
    $resultadoA = transformarDatos($datosDeptos[$idParA], $fecha, $idParA);
    $dataPlanA  = $dataPlanPorDepto[$idParA] ?? [];
    $programaA  = transformarPrograma($dataPlanA, $fecha, $diasMes);

    $resultadoB = transformarDatos($datosDeptos[$idParB], $fecha, $idParB);
    $dataPlanB  = $dataPlanPorDepto[$idParB] ?? [];
    $programaB  = transformarPrograma($dataPlanB, $fecha, $diasMes);

    $pdf->iniciarPar(
        $resultadoA['fechas'], $resultadoA['tablas'], $programaA, $nombresDepto[$idParA],
        $resultadoB['fechas'], $resultadoB['tablas'], $programaB, $nombresDepto[$idParB],
        $fecha, $logoPath
    );
    $esPrimero = false;
}

// ── Protección Femenina (24) se dibuja junto con TABBI y SOAR/Hook en
// una sola hoja (ver agregarProteccionTabbiSoar más abajo), así que se
// excluye del loop normal y se prepara aquí para usarla al final.
$idPF    = 24;
$tienePF = isset($datosDeptos[$idPF]) && !empty($datosDeptos[$idPF]);
$fechasPF   = [];
$tablasPF   = [];
$programaPF = [];
if ($tienePF) {
    $resultadoPF = transformarDatos($datosDeptos[$idPF], $fecha, $idPF);
    $fechasPF    = $resultadoPF['fechas'];
    $tablasPF    = $resultadoPF['tablas'];
    $dataPlanPF  = $dataPlanPorDepto[$idPF] ?? [];
    $programaPF  = transformarPrograma($dataPlanPF, $fecha, $diasMes);
}

foreach ($ordenDepartamentos as $noDepto) {
    // Ya se dibujaron juntos arriba — no repetirlos aquí
    if (($noDepto === $idParA || $noDepto === $idParB) && $tieneParA && $tieneParB) {
        continue;
    }

    // Protección Femenina se dibuja más abajo junto con TABBI y SOAR
    if ($noDepto === $idPF) {
        continue;
    }

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

// ── Preparar TABBI + Spooler (misma lógica de siempre, ya no se dibuja
// aquí — se dibuja junto con Protección Femenina y SOAR más abajo) ──
$fechasTabbi = [];
$tablasTabbi = [];
$programaTNT = [];
if (!empty($dataTabbiCompleta)) {
    $resultadoTabbi = transformarTabbi($dataTabbiCompleta, $fecha);

    if (!empty($resultadoTabbi['tablas'])) {

        if (!empty($dataPlanTNTCompleta)) {
            $programaTNT = transformarProgramaTNT($dataPlanTNTCompleta, $fecha, $diasMes);
        }

        // ── Spooler: transformar y fusionar sus tablas en el mismo bloque TNT ──
        $resultadoSpooler = ['tablas' => []];
        if (!empty($dataSpoolerCompleta) && !isset($dataSpoolerCompleta['ok'])) {
            $resultadoSpooler = transformarSpooler($dataSpoolerCompleta, $fecha);
        }

        // Fusionar tablas: TABBI primero, luego Spooler
        // agregarProteccionTabbiSoar() recibe un único array $tablas con
        // todas las operaciones, igual que antes recibía agregarTabbi()
        $tablasTabbi = array_merge(
            $resultadoTabbi['tablas'],
            $resultadoSpooler['tablas']
        );

        // Las fechas del encabezado: unión de ambas (por si difieren)
        $fechasTabbi = array_values(array_unique(array_merge(
            $resultadoTabbi['fechas'],
            $resultadoSpooler['fechas']
        )));
        sort($fechasTabbi);
    }
}

// ── Preparar Hook/SOAR (misma lógica de siempre) ──
$fechasSoar = [];
$tablasSoar = [];
if (!empty($dataHookCompleta) && !isset($dataHookCompleta['ok'])) {
    $resultadoHook = transformarHook($dataHookCompleta, $fecha);
    if (!empty($resultadoHook['tablas'])) {
        $fechasSoar = $resultadoHook['fechas'];
        $tablasSoar = $resultadoHook['tablas'];
    }
}

// ── Protección Femenina + TABBI + SOAR, todo en una sola hoja ──
if ($tienePF) {
    $pdf->agregarProteccionTabbiSoar(
        $fechasPF, $tablasPF, $programaPF, $nombresDepto[$idPF],
        $fechasTabbi, $tablasTabbi, $programaTNT, 'TELAS NO TEJIDAS',
        $fechasSoar, $tablasSoar, 'HOOK'
    );
} else {
    // Sin datos de Protección Femenina ese día: mantener el comportamiento
    // anterior (hojas separadas) para no perder TABBI/Hook si sí tienen datos.
    if (!empty($tablasTabbi)) {
        $pdf->agregarTabbi($fechasTabbi, $tablasTabbi, $programaTNT);
    }
    if (!empty($tablasSoar)) {
        $pdf->agregarHook($fechasSoar, $tablasSoar);
    }
}

$pdf->finalizar('reporte_diario_gerencia.pdf');
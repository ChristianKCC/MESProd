<?php
require_once('../../fpdf/fpdf.php');
require_once('../../conexion.php');
$conection = new ClassConexion();
$conn = $conection->conexion("TLX001MXDB");

// Importación de repositorios y generacion de PDF
require_once __DIR__ . '/../src/Repositorios/DescansosRepositorio.php';
require_once __DIR__ . '/../src/Repositorios/EmpleadoRepositorio.php';
require_once __DIR__ . '/../src/Repositorios/TransaccionRepositorio.php';
require_once __DIR__ . '/../src/Repositorios/ConceptosRepositorio.php';
require_once __DIR__ . '/../src/Pdf/TarjetaPdf.php';

use src\Repositorios\ConceptosRepositorio;
use src\Repositorios\DescansosRepositorio;
use src\Repositorios\EmpleadoRepositorio;
use src\Repositorios\TransaccionRepositorio;
use src\Pdf\TarjetaPdf;


/* =====================================================================
   FUNCIONES DE APOYO
   ===================================================================== */

/**
 * Corta el rango solicitado en semanas ISO reales (lunes a domingo).
 * Si el rango ya es de lunes a domingo devuelve una sola semana, por lo que
 * el modo semanal y el modo rango usan exactamente la misma ruta de codigo.
 */
function cortarRangoEntreSemanas(string $inicio, string $fin): array
{
    $inicioDt = new DateTime($inicio);
    $finDt = new DateTime($fin);

    // Retrocede al lunes de la semana donde cae la fecha inicial
    $cursor = (clone $inicioDt)->modify('monday this week');

    $semanas = [];
    while ($cursor <= $finDt) {
        $semanas[] = [
            'start' => clone $cursor,
            'end' => (clone $cursor)->modify('+6 days')
        ];
        $cursor = (clone $cursor)->modify('+7 days');
    }
    return $semanas;
}

/**
 * Busca el numero de nomina que corresponde a una semana concreta.
 * Se calcula por semana y no una sola vez para todo el rango, porque un
 * rango de varias semanas nunca cabe dentro de una sola nomina.
 */
function nominaDeRango(array $nominas, DateTime $ini, DateTime $fin): string
{
    $i = $ini->format('Y-m-d');
    $f = $fin->format('Y-m-d');
    foreach ($nominas as $n) {
        if ($i >= $n['inicio'] && $f <= $n['fin']) {
            return (string) $n['nomina'];
        }
    }
    return 'N/A';
}

/**
 * Filtra las transacciones de un empleado que caen dentro de la semana.
 */
function filtrarTXSporRango(array $txs, DateTime $inicioDt, DateTime $finDt): array
{
    $salida = [];
    $desde = $inicioDt->format('Y-m-d');
    $hasta = $finDt->format('Y-m-d');

    foreach ($txs as $t) {
        $eventTime = $t['event_time'];
        $eventDt = ($eventTime instanceof \DateTime) ? $eventTime : new DateTime($eventTime);

        $fecha = $eventDt->format('Y-m-d');
        if ($fecha >= $desde && $fecha <= $hasta) {
            $salida[] = $t;
        }
    }
    return $salida;
}

/**
 * Filtra los descansos de un empleado que caen dentro de la semana.
 */
function filtrarDescPorRango(array $colDescanso, DateTime $inicioDt, DateTime $finDt): array
{
    $out = [];
    foreach ($colDescanso as $d) {
        if (!empty($d['fecha'])) {
            $fechaObj = ($d['fecha'] instanceof \DateTime) ? $d['fecha'] : new DateTime($d['fecha']);
            if ($fechaObj >= $inicioDt && $fechaObj <= $finDt) {
                $out[] = $d;
            }
        }
    }
    return $out;
}

/**
 * Filtra los conceptos de la semana y los resume en renglones listos para el PDF.
 * Tiempo extra se acumula en una sola linea; vacaciones cuentan dias;
 * cambio de puesto genera una linea por cada puesto destino distinto.
 *
 * NOTA: su resultado actualmente NO se pinta (el llenado del bloque de
 * conceptos esta oculto en TarjetaPdf::dibujarConceptos). Se conserva
 * completa para reactivar los conceptos sin reescribir nada.
 */
function resumirConceptos(array $eventos, DateTime $inicioDt, DateTime $finDt): array
{
    $desde = $inicioDt->format('Y-m-d');
    $hasta = $finDt->format('Y-m-d');

    $te = ['minutos' => 0, 'ini' => null, 'fin' => null];
    $vac = ['dias' => 0, 'ini' => null, 'fin' => null];
    $cps = [];

    foreach ($eventos as $ev) {
        if ($ev['fecha'] < $desde || $ev['fecha'] > $hasta)
            continue;

        if ($ev['tipo'] === 'TIEMPO EXTRA') {
            $te['minutos'] += $ev['minutos'];
            if ($te['ini'] === null || $ev['fecha'] < $te['ini'])
                $te['ini'] = $ev['fecha'];
            if ($te['fin'] === null || $ev['fecha'] > $te['fin'])
                $te['fin'] = $ev['fecha'];

        } elseif ($ev['tipo'] === 'VACACIONES') {
            $vac['dias']++;
            // Rango completo de la solicitud, aunque cruce semanas
            if ($vac['ini'] === null || $ev['ini'] < $vac['ini'])
                $vac['ini'] = $ev['ini'];
            if ($vac['fin'] === null || $ev['fin'] > $vac['fin'])
                $vac['fin'] = $ev['fin'];

        } elseif ($ev['tipo'] === 'CAMBIO DE PUESTO') {
            $clave = $ev['puesto'];
            if (!isset($cps[$clave])) {
                $cps[$clave] = ['dias' => 0, 'ini' => $ev['ini'], 'fin' => $ev['fin']];
            }
            $cps[$clave]['dias'] += $ev['dias'];
            if ($ev['ini'] < $cps[$clave]['ini'])
                $cps[$clave]['ini'] = $ev['ini'];
            if ($ev['fin'] > $cps[$clave]['fin'])
                $cps[$clave]['fin'] = $ev['fin'];
        }
    }

    $filas = [];

    if ($te['minutos'] > 0) {
        $filas[] = [
            'concepto' => 'TIEMPO EXTRA',
            'dias' => '',
            'hrs' => sprintf('%02d:%02d', intdiv($te['minutos'], 60), $te['minutos'] % 60),
            'puesto' => '',
            'obs' => "del {$te['ini']} al {$te['fin']}"
        ];
    }

    if ($vac['dias'] > 0) {
        $filas[] = [
            'concepto' => 'VACACIONES',
            'dias' => (string) $vac['dias'],
            'hrs' => '',
            'puesto' => '',
            'obs' => "del {$vac['ini']} al {$vac['fin']}"
        ];
    }

    foreach ($cps as $puesto => $cp) {
        $filas[] = [
            'concepto' => 'CAMBIO DE PUESTO',
            'dias' => (string) $cp['dias'],
            'hrs' => '',
            'puesto' => $puesto,
            'obs' => "del {$cp['ini']} al {$cp['fin']}"
        ];
    }

    return $filas;
}

/* =====================================================================
   PROCESO PRINCIPAL
   ===================================================================== */

try {
    // Parámetros recibidos del formulario
    $fechai = $_POST['fechai'] ?? null;
    $fechaf = $_POST['fechaf'] ?? null;
    $tipemp = $_POST['tipemp'] ?? '';
    $ctrocstos = $_POST['ctrocstos'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $empno = trim(($_POST['empno'] ?? ''));

    if (!$fechai) {
        throw new Exception('Fecha inicio requerida');
    }

    // Validacion por si no viene fecha final, se calcula como inicio + 6 días
    if (empty($fechaf)) {
        $fechaf = date("Y-m-d", strtotime($fechai . " +6 days"));
    }

    // Arreglo de nóminas
    $nominas = [
        ["nomina" => 1, "inicio" => "2025-12-29", "fin" => "2026-01-04"],
        ["nomina" => 2, "inicio" => "2026-01-05", "fin" => "2026-01-11"],
        ["nomina" => 3, "inicio" => "2026-01-12", "fin" => "2026-01-18"],
        ["nomina" => 4, "inicio" => "2026-01-19", "fin" => "2026-01-25"],
        ["nomina" => 5, "inicio" => "2026-01-26", "fin" => "2026-02-01"],
        ["nomina" => 6, "inicio" => "2026-02-02", "fin" => "2026-02-08"],
        ["nomina" => 7, "inicio" => "2026-02-09", "fin" => "2026-02-15"],
        ["nomina" => 8, "inicio" => "2026-02-16", "fin" => "2026-02-22"],
        ["nomina" => 9, "inicio" => "2026-02-23", "fin" => "2026-03-01"],
        ["nomina" => 10, "inicio" => "2026-03-02", "fin" => "2026-03-08"],
        ["nomina" => 11, "inicio" => "2026-03-09", "fin" => "2026-03-15"],
        ["nomina" => 12, "inicio" => "2026-03-16", "fin" => "2026-03-22"],
        ["nomina" => 13, "inicio" => "2026-03-23", "fin" => "2026-03-29"],
        ["nomina" => 14, "inicio" => "2026-03-30", "fin" => "2026-04-05"],
        ["nomina" => 15, "inicio" => "2026-04-06", "fin" => "2026-04-12"],
        ["nomina" => 16, "inicio" => "2026-04-13", "fin" => "2026-04-19"],
        ["nomina" => 17, "inicio" => "2026-04-20", "fin" => "2026-04-26"],
        ["nomina" => 18, "inicio" => "2026-04-27", "fin" => "2026-05-03"],
        ["nomina" => 19, "inicio" => "2026-05-04", "fin" => "2026-05-10"],
        ["nomina" => 20, "inicio" => "2026-05-11", "fin" => "2026-05-17"],
        ["nomina" => 21, "inicio" => "2026-05-18", "fin" => "2026-05-24"],
        ["nomina" => 22, "inicio" => "2026-05-25", "fin" => "2026-05-31"],
        ["nomina" => 23, "inicio" => "2026-06-01", "fin" => "2026-06-07"],
        ["nomina" => 24, "inicio" => "2026-06-08", "fin" => "2026-06-14"],
        ["nomina" => 25, "inicio" => "2026-06-15", "fin" => "2026-06-21"],
        ["nomina" => 26, "inicio" => "2026-06-22", "fin" => "2026-06-28"],
        ["nomina" => 27, "inicio" => "2026-06-29", "fin" => "2026-07-05"],
        ["nomina" => 28, "inicio" => "2026-07-06", "fin" => "2026-07-12"],
        ["nomina" => 29, "inicio" => "2026-07-13", "fin" => "2026-07-19"],
        ["nomina" => 30, "inicio" => "2026-07-20", "fin" => "2026-07-26"],
        ["nomina" => 31, "inicio" => "2026-07-27", "fin" => "2026-08-02"],
        ["nomina" => 32, "inicio" => "2026-08-03", "fin" => "2026-08-09"],
        ["nomina" => 33, "inicio" => "2026-08-10", "fin" => "2026-08-16"],
        ["nomina" => 34, "inicio" => "2026-08-17", "fin" => "2026-08-23"],
        ["nomina" => 35, "inicio" => "2026-08-24", "fin" => "2026-08-30"],
        ["nomina" => 36, "inicio" => "2026-08-31", "fin" => "2026-09-06"],
        ["nomina" => 37, "inicio" => "2026-09-07", "fin" => "2026-09-13"],
        ["nomina" => 38, "inicio" => "2026-09-14", "fin" => "2026-09-20"],
        ["nomina" => 39, "inicio" => "2026-09-21", "fin" => "2026-09-27"],
        ["nomina" => 40, "inicio" => "2026-09-28", "fin" => "2026-10-04"],
        ["nomina" => 41, "inicio" => "2026-10-05", "fin" => "2026-10-11"],
        ["nomina" => 42, "inicio" => "2026-10-12", "fin" => "2026-10-18"],
        ["nomina" => 43, "inicio" => "2026-10-19", "fin" => "2026-10-25"],
        ["nomina" => 44, "inicio" => "2026-10-26", "fin" => "2026-11-01"],
        ["nomina" => 45, "inicio" => "2026-11-02", "fin" => "2026-11-08"],
        ["nomina" => 46, "inicio" => "2026-11-09", "fin" => "2026-11-15"],
        ["nomina" => 47, "inicio" => "2026-11-16", "fin" => "2026-11-22"],
        ["nomina" => 48, "inicio" => "2026-11-23", "fin" => "2026-11-29"],
        ["nomina" => 49, "inicio" => "2026-11-30", "fin" => "2026-12-06"],
        ["nomina" => 50, "inicio" => "2026-12-07", "fin" => "2026-12-13"],
        ["nomina" => 51, "inicio" => "2026-12-14", "fin" => "2026-12-20"],
        ["nomina" => 52, "inicio" => "2026-12-21", "fin" => "2026-12-27"],
    ];

    // Semanas ISO que cubre el rango solicitado
    $weeks = cortarRangoEntreSemanas($fechai, $fechaf);
    if (empty($weeks)) {
        throw new Exception('El rango de fechas no genera ninguna semana valida');
    }

    // El rango se expande a semanas completas: si no, los dias anteriores al
    // lunes de la primera semana saldrian vacios en la tarjeta
    $rangoIni = $weeks[0]['start']->format('Y-m-d');
    $rangoFin = end($weeks)['end']->format('Y-m-d');

    // Instancia de repositorios con el parametro de la conexion
    $descRepo = new DescansosRepositorio($conn);
    $empRepo = new EmpleadoRepositorio($conn);
    $txRepo = new TransaccionRepositorio($conn);
    $conceptosRepo = new ConceptosRepositorio($conn);

    // Cargas globales sobre el rango ya expandido a semanas completas
    $transaccionesAgrupadas = $txRepo->getTransaccionesAgrupadas($rangoIni, $rangoFin);
    $descansosAgrupados = $descRepo->getDescansosAgrupados($rangoIni, $rangoFin);

    // Conceptos OCULTOS temporalmente: el llenado del bloque de conceptos esta
    // desactivado en TarjetaPdf::dibujarConceptos, asi que se omite la consulta
    // (son 3 consultas a bases distintas) para no cargar de mas.
    // Reactivar cambiando la linea de abajo por:
    // $conceptosAgrupados = $conceptosRepo->getConceptosAgrupados($rangoIni, $rangoFin);
    $conceptosAgrupados = [];

    // Validacion de datos para su uso segun el caso:
    // Si la variable esta vacia le agrega una cadena vacia que no busca nada
    // Si la variable no esta vacia se agrega una condicion sql a cada variable
    $departamento = $departamento === '' ? '' : " AND tblEmpleados.NombreDepartamento=" . intval($departamento);
    $tipemp = $tipemp === '' ? '' : " AND tblEmpleados.EmpleadoSindicalizado=" . intval($tipemp);
    $ctrocstos = $ctrocstos === '' ? '' : " AND tblEmpleados.IdCentroCosto=" . intval($ctrocstos);
    $empnoFiltro = $empno === '' ? '' : " AND tblEmpleados.NoEmp=" . intval($empno);

    // Obtener empleados sin/con filtros
    // 1er. -> Caso de consulta general
    // 2do. -> Caso de consulta especifica agregando un cuarto filtro
    if (empty($empno)) {
        $empleados = $empRepo->getEmpleados($tipemp . $ctrocstos . $departamento);
    } else {
        $empleados = $empRepo->getEmpleados($tipemp . $ctrocstos . $departamento . $empnoFiltro);
    }

    // Generacion de PDF (la primera hoja la crea la propia tarjeta al renderizar)
    $pdfGen = new TarjetaPdf();

    /*
    UNA SOLA RUTA PARA AMBOS MODOS
    El modo semanal es simplemente un rango que abarca una sola semana ISO,
    por lo que se renderiza una tarjeta por empleado y por semana.
    */
    foreach ($empleados as $emp) {
        $noemp = $emp['NoEmp'];
        $todosTxs = $transaccionesAgrupadas[$noemp] ?? [];
        $todosDescs = $descansosAgrupados[$noemp] ?? [];
        $todosConceptos = $conceptosAgrupados[$noemp] ?? [];

        foreach ($weeks as $w) {
            $inicioSemana = $w['start'];
            $finSemana = $w['end'];

            $txsSemana = filtrarTXSporRango($todosTxs, $inicioSemana, $finSemana);
            $descsSemana = filtrarDescPorRango($todosDescs, $inicioSemana, $finSemana);
            // Se sigue calculando aunque no se pinte, para reactivar sin tocar el bucle
            $conceptos = resumirConceptos($todosConceptos, $inicioSemana, $finSemana);

            $pdfGen->renderizarEmpleado(
                $emp,
                $txsSemana,
                $descsSemana,
                $conceptos,
                $inicioSemana->format('Y-m-d'),
                $finSemana->format('Y-m-d'),
                nominaDeRango($nominas, $inicioSemana, $finSemana)
            );
        }
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="tarjetas.pdf"');
    $pdfGen->output('I');

} catch (\Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
<?php
require_once('../../fpdf/fpdf.php');
require_once('../../conexion.php');
$conection = new ClassConexion();
$conn = $conection->conexion("TLX001MXDB");

// Importación de repositorios y generacion de PDF
require_once __DIR__ . '/../src/Repositorios/DescansosRepositorio.php';
require_once __DIR__ . '/../src/Repositorios/EmpleadoRepositorio.php';
require_once __DIR__ . '/../src/Repositorios/TransaccionRepositorio.php';
require_once __DIR__ . '/../src/Pdf/TarjetaPdf.php';

use src\Repositorios\DescansosRepositorio;
use src\Repositorios\EmpleadoRepositorio;
use src\Repositorios\TransaccionRepositorio;
use src\Pdf\TarjetaPdf;

try {
    // Parámetros recibidos del formulario
    $fechai = $_POST['fechai'] ?? null;
    $fechaf = $_POST['fechaf'] ?? null;
    $tipemp = $_POST['tipemp'] ?? '';
    $ctrocstos = $_POST['ctrocstos'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $empno = trim(($_POST['empno']));

    if (!$fechai) {
        throw new Exception('Fecha inicio requerida');
    }
    // Validacion por si no viene fecha final, se calcula como inicio + 6 días
    $fechaf = date("Y-m-d", strtotime($fechai . " +6 days"));
    
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

    // Buscar a qué nómina pertenece el rango seleccionado
    $nominaActual = 'N/A';
    foreach ($nominas as $n) {
        if ($fechai >= $n['inicio'] && $fechaf <= $n['fin']) {
            $nominaActual = $n['nomina'];
            break;
        }
    }

    // Validacion de datos para su uso segun el caso:
    // Si la variable esta vacia le agrega una cadena vacia que no busca nada
    // Si la variable no esta vacia se agrega una condicion sql a cada variable
    $departamento = $departamento === '' ? '' : " AND tblEmpleados.NombreDepartamento=" . intval($departamento);
    $tipemp = $tipemp === '' ? '' : " AND tblEmpleados.EmpleadoSindicalizado=" . intval($tipemp);
    $ctrocstos = $ctrocstos === '' ? '' : " AND tblEmpleados.IdCentroCosto=" . intval($ctrocstos);
    $empnoFiltro = $empno === '' ? '' : " AND tblEmpleados.NoEmp=" . intval($empno);

    // Instanciación de repositorios con el parametro de la conexion
    $descRepo = new DescansosRepositorio($conn);
    $empRepo  = new EmpleadoRepositorio($conn);
    $txRepo   = new TransaccionRepositorio($conn);
    
    // Cargas globales para obtencion de datos de transacciones
    $transaccionesAgrupadas = $txRepo->getTransaccionesAgrupadas($fechai, $fechaf);
    // Cargas globales para obtencion de datos de descansos
    $descansosAgrupados = $descRepo->getDescansosAgrupados($fechai, $fechaf);

    // Obtener empleados sin/con filtros 
    // 1er. -> Caso de consulta general
    // 2do. -> Caso de consulta especifica agregando un cuarto filtro
    if (empty($empno)){
        $empleados = $empRepo->getEmpleados($tipemp . $ctrocstos . $departamento);
    } else {
        $empleados = $empRepo->getEmpleados($tipemp . $ctrocstos . $departamento . $empnoFiltro);
    }

    // Generar PDF
    $pdfGen = new TarjetaPdf();
    $pdfGen->addPage();
    foreach ($empleados as $emp) {
        $noemp  = $emp['NoEmp'];
        $txs    = $transaccionesAgrupadas[$noemp] ?? [];
        $descs  = $descansosAgrupados[$noemp] ?? [];
        $pdfGen->renderizarEmpleado($emp, $txs, $descs, $fechai, $fechaf, $nominaActual);
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="tarjetas.pdf"');
    $pdfGen->output('I');

} catch (\Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}

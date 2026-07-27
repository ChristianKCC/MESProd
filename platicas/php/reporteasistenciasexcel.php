<?php
require_once "../../conexion.php";
require __DIR__ . '/../../php/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$conection = new ClassConexion();
$conn = $conection->conexion("TLX002MXDB");

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];
$noemp = $_POST["noemp"];
$departamento = $_POST["departamento"];
$maquinas = $_POST["maquinas"];

empty($noemp) ? $noemp="" : $noemp = "AND tblEmpleados.NoEmp=".$_POST["noemp"];
empty($departamento) ? $departamento = "" : $departamento = "AND tblEmpleados.NombreDepartamento=".$_POST["departamento"];
empty($maquinas) ? $maquinas = "" : $maquinas = "AND tblPlaticas5minAsistencias.idsession=".$_POST["maquinas"];

$query = "SELECT 
    tblPlaticas5minAsistencias.id,
    tblPlaticas5min.fecha,
    tblPlaticas5min.minutos,
    tblPlaticas5minAsistencias.noemp,
    tblEmpleados.Nombre,
    tblPlaticas5min.nombreplatica
FROM tblPlaticas5min 
INNER JOIN tblPlaticas5minAsistencias ON tblPlaticas5min.id = tblPlaticas5minAsistencias.idplatica
INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblPlaticas5minAsistencias.noemp
WHERE tblPlaticas5min.fecha BETWEEN '".$fechai."' AND '".$fechaf."' $noemp $departamento $maquinas
ORDER BY tblPlaticas5minAsistencias.noemp ASC";

$result = sqlsrv_query($conn, $query);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Logo
$drawing = new Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Logo');
$drawing->setPath('../../img/imglogoprosede.png'); // ruta de tu logo
$drawing->setHeight(60);
$drawing->setCoordinates('A1');
$drawing->setWorksheet($sheet);

// Encabezado
$sheet->mergeCells('D1:E1');
$sheet->setCellValue('D1', 'Reporte pláticas de 5 minutos asistencias');
$sheet->getStyle('D1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Variables para totales
$totalMin = 0;

// Encabezados de tabla
$sheet->setCellValue('A6', 'NoEmp');
$sheet->setCellValue('B6', 'Nombre');
$sheet->setCellValue('C6', 'Fecha');
$sheet->setCellValue('D6', 'Tiempo');
$sheet->setCellValue('E6', 'Platica');

// Ajustar ancho de columnas
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(60);

// Estilo encabezados
$headerStyle = [
    'font' => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFD9D9D9']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];
$sheet->getStyle('A6:E6')->applyFromArray($headerStyle);

$row = 7;

while ($fila = sqlsrv_fetch_array($result)) {
    $sheet->setCellValue('A'.$row, $fila[3]);
    $sheet->setCellValue('B'.$row, $fila[4]);
    $sheet->setCellValue('C'.$row, $fila[1]->format("Y-m-d"));
    $sheet->setCellValue('D'.$row, $fila[2]." min");
    $sheet->setCellValue('E'.$row, $fila[5]);
    $totalMin += $fila[2];
    $row++;
}

// Estilo filas de datos
$dataStyle = [
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];
$sheet->getStyle('A7:E'.($row-1))->applyFromArray($dataStyle);

// Totales en el encabezado junto a fechas
$sheet->setCellValue('C3', 'Fecha inicial: '.$fechai);
$sheet->setCellValue('E3', 'Tiempo total: '.$totalMin);

$sheet->setCellValue('C4', 'Fecha final: '.$fechaf);
$sheet->setCellValue('E4', 'Tiempo total: '.number_format($totalMin/60,2));

// Descargar
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReportePlaticas.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

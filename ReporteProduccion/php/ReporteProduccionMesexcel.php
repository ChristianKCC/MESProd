<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

require_once "../../conexion.php";
require __DIR__ . '/../../php/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$conection = new ClassConexion();
$conn = $conection->conexion("TLX004MXDB");

$fechai = $_POST["fechai"];
$fechaf = $_POST["fechaf"];

// Llamada al procedimiento almacenado
$sql = "{CALL dbo.sp_PRSD_DatosPROSEDEProduccion_Contraloria(?, ?)}";
$params = array($fechai, $fechaf);

$result = sqlsrv_query($conn, $sql, $params);

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Logo
$drawing = new Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Logo');
$drawing->setPath('../../img/imglogoprosede.png');
$drawing->setHeight(60);
$drawing->setCoordinates('A1');
$drawing->setWorksheet($sheet);

// Encabezado
$sheet->mergeCells('D1:F1');
$sheet->setCellValue('D1', 'Reporte Producciones Contraloria');
$sheet->getStyle('D1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Encabezados de tabla
$sheet->setCellValue('A6', 'Clave del Articulo');
$sheet->setCellValue('B6', 'Descripcion');
$sheet->setCellValue('C6', 'Centro De Costos');
$sheet->setCellValue('D6', 'Maquina');
$sheet->setCellValue('E6', 'Corrugados Real');
$sheet->setCellValue('F6', 'Unidades Estandar');
$sheet->setCellValue('G6', 'Kg');
$sheet->setCellValue('H6', 'Metros Lineales');
$sheet->setCellValue('I6', 'MM2');
$sheet->setCellValue('J6', 'Unidad de Medida');
$sheet->setCellValue('K6', 'Rechazos');
$sheet->setCellValue('L6', '% De Merma');
$sheet->setCellValue('M6', 'Horas Trabajadas');

// Ancho de columnas
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(50);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);
$sheet->getColumnDimension('I')->setWidth(15);
$sheet->getColumnDimension('J')->setWidth(15);
$sheet->getColumnDimension('K')->setWidth(15);
$sheet->getColumnDimension('L')->setWidth(15);
$sheet->getColumnDimension('M')->setWidth(15);

// Estilo encabezados
$cabeceraEstilos = [
    'font' => [
        'bold' => true,
        'size' => 11,
        'color' => ['argb' => 'FFFFFFFF']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF0B3D91']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];

$sheet->getStyle('A6:M6')->applyFromArray($cabeceraEstilos);

// Columna desde la que se iniciara a escribir los valores
$row = 7;

while ($fila = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $sheet->setCellValue('A'.$row, $fila['Clave del articulo']);
    $sheet->setCellValue('B'.$row, $fila['Descripcion']);
    $sheet->setCellValue('C'.$row, $fila['Centro de costos']);
    $sheet->setCellValue('D'.$row, $fila['Maquina']);
    $sheet->setCellValue('E'.$row, $fila['Corrugados real']);
    $sheet->setCellValue('F'.$row, $fila['Unidades estandar']);
    $sheet->setCellValue('G'.$row, $fila['Kg']);
    $sheet->setCellValue('H'.$row, $fila['Metros Lineales']);
    $sheet->setCellValue('I'.$row, $fila['MM2']);
    $sheet->setCellValue('J'.$row, $fila['Unidad de Medida']);
    $sheet->setCellValue('K'.$row, $fila['Rechazos']);
    $sheet->setCellValue('L'.$row, $fila['% Merma']);
    $sheet->setCellValue('M'.$row, $fila['HorasTrabajadas']);
    $row++;
}

// Ajustar altura de fila
$sheet->getRowDimension(6)->setRowHeight(40);

// Estilo filas de datos
$datosEstilos = [    
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
];

$sheet->getStyle('A7:M'.($row-1))->applyFromArray($datosEstilos);

// Fechas de busqueda
$sheet->setCellValue('D3', 'Fecha inicial: '.$fechai);
$sheet->setCellValue('D4', 'Fecha final: '.$fechaf);

if (ob_get_length()) {
    ob_end_clean();
}

// Cabeceras de descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ReporteProduccionMes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
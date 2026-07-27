<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

header('Content-Type: application/json');

$keyword = trim($_POST['keyword'] ?? '');
$carpetas = [
    __DIR__ . '/../carpeta1/uploads',
    __DIR__ . '/../carpeta2/uploads',
    __DIR__ . '/../carpeta3/uploads',
    __DIR__ . '/../carpeta4/uploads',
];

$resumen = new Spreadsheet();
$resumenSheet = $resumen->getActiveSheet();
$rowResumen = 1;
$resultados = [];

foreach ($carpetas as $carpeta) {
    $files = glob($carpeta . "/*.xlsx");
    foreach ($files as $filePath) {
        $spreadsheet = IOFactory::load($filePath);
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = $worksheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, true);
                if ($rowData && isset($rowData[$row])) {
                    $rowData = $rowData[$row];
                    foreach ($rowData as $cellValue) {
                        if (stripos((string)$cellValue, $keyword) !== false) {
                            $resultados[] = $rowData;
                            $colIndex = 1;
                            foreach ($rowData as $value) {
                                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                                $resumenSheet->setCellValue($colLetter . $rowResumen, $value);
                                $colIndex++;
                            }
                            $rowResumen++;
                            break;
                        }
                    }
                }
            }
        }
    }
}

$outputDir = __DIR__ . '/../PruebaResumenXLSX';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}
$outputFile = $outputDir . '/resumen.xlsx';
$writer = IOFactory::createWriter($resumen, 'Xlsx');
$writer->save($outputFile);

echo json_encode([
    'resultados' => $resultados,
    'archivo' => '../PruebaResumenXLSX/resumen.xlsx'
]);

<?php

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()->setCreator("yp")->setLastModifiedBy("yo")->setTitle("yo")->setDescription("yo");
$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setTitle("hoja 1");
$activeWorksheet->setCellValue('A1', 'Hola mundo !');
$activeWorksheet->setCellValue('A2', 'DNI');


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Números del 1 al 30 en la fila 3, desde la columna A hacia la derecha
//for ($i = 1; $i <= 30; $i++) {
 //   $columna = Coordinate::stringFromColumnIndex($i); // A, B, C, ..., AD
   // $activeWorksheet->setCellValue($columna . '3', $i); // Escribe en fila 3
//}
$filaInicio = 2; // Empezamos en la fila 2

for ($i = 1; $i <= 10; $i++) {
    $fila = $filaInicio + ($i - 1); // Fila actual
    
    $activeWorksheet->setCellValue('A' . $fila, 1);         // 1
    $activeWorksheet->setCellValue('B' . $fila, 'x');       // x
    $activeWorksheet->setCellValue('C' . $fila, $i);        // i
    $activeWorksheet->setCellValue('D' . $fila, '=');       // =
    $activeWorksheet->setCellValue('E' . $fila, 1 * $i);    // resultado
}





$writer = new Xlsx($spreadsheet);
$writer->save('hello world.xlsx');
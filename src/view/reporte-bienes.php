<?php
require './vendor/autoload.php';
require_once './src/library/conexionn.php'; // Ruta corregida a tu archivo conexión

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// OBTENER CONEXIÓN DESDE LA CLASE Conexion
$conexion = Conexion::connect();

// CONSULTA A LA BD
$sql = "SELECT * FROM bienes ORDER BY id ASC";
$resultado = $conexion->query($sql);

// CREAR EXCEL
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("yp")
    ->setLastModifiedBy("yo")
    ->setTitle("Bienes")
    ->setDescription("Listado de bienes");

$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle("Bienes");

// FUNCIÓN PARA CONVERTIR ÍNDICES A LETRAS DE COLUMNA
function getColLetter($index) {
    $letter = '';
    while ($index > 0) {
        $index--;
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = intval($index / 26);
    }
    return $letter;
}

// LLENAR DATOS SI HAY RESULTADOS
if ($resultado->num_rows > 0) {
    $campos = $resultado->fetch_fields();
    foreach ($campos as $i => $campo) {
        $col = getColLetter($i + 1);
        $hoja->setCellValue($col . '1', strtoupper($campo->name));
    }

    $filaExcel = 2;
    while ($fila = $resultado->fetch_assoc()) {
        foreach (array_values($fila) as $i => $valor) {
            $col = getColLetter($i + 1);
            $hoja->setCellValue($col . $filaExcel, $valor);
        }
        $filaExcel++;
    }
} else {
    $hoja->setCellValue("A1", "No hay datos en la tabla bienes.");
}

// CERRAR CONEXIÓN
$conexion->close();

// FORZAR DESCARGA DEL EXCEL
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_bienes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
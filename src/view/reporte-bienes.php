<?php
require './vendor/autoload.php';
require_once './src/library/conexionn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// CONECTAR A LA BD
$conexion = Conexion::connect();

// CONSULTA
$sql = "SELECT * FROM bienes ORDER BY id ASC";
$resultado = $conexion->query($sql);

// CREAR EXCEL
$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle("Bienes");

// FUNCIONES
function getColLetter($index) {
    $letter = '';
    while ($index > 0) {
        $index--;
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = intval($index / 26);
    }
    return $letter;
}

// SI HAY DATOS
if ($resultado->num_rows > 0) {
    $campos = $resultado->fetch_fields();
    
    // ENCABEZADOS
    foreach ($campos as $i => $campo) {
        $col = getColLetter($i + 1);
        $hoja->setCellValue($col . '2', strtoupper($campo->name));

        // Estilo del encabezado
        $hoja->getStyle($col . '2')->getFont()->setBold(true);
        $hoja->getStyle($col . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle($col . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
    }

    // CONTENIDO
    $filaExcel = 3;
    while ($fila = $resultado->fetch_assoc()) {
        foreach (array_values($fila) as $i => $valor) {
            $col = getColLetter($i + 1);
            $hoja->setCellValue($col . $filaExcel, $valor);
        }
        $filaExcel++;
    }

    // AJUSTAR ANCHO DE COLUMNA Y AGREGAR BORDES
    $totalColumnas = count($campos);
    $ultimaFila = $filaExcel - 1;
    for ($i = 1; $i <= $totalColumnas; $i++) {
        $col = getColLetter($i);
        $hoja->getColumnDimension($col)->setAutoSize(true);
        
        // Bordes para cada celda
        $rango = $col . '2:' . $col . $ultimaFila;
        $hoja->getStyle($rango)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

} else {
    $hoja->setCellValue("A2", "No hay datos en la tabla bienes.");
}

// CERRAR BD
$conexion->close();

// DESCARGA
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_bienes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
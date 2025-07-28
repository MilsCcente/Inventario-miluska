<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

// ---------------- CONEXIÓN A BASE DE DATOS ----------------
$host = "localhost";
$dbname = "inventario";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ---------------- CONSULTA DE MOVIMIENTOS ----------------
$sql = "
    SELECT 
        m.id,
        ao.detalle AS ambiente_origen,
        ad.detalle AS ambiente_destino,
        u.nombres_apellidos AS usuario_registro,
        m.fecha_registro,
        m.descripcion,
        i.nombre AS institucion
    FROM movimientos m
    LEFT JOIN ambientes_institucion ao ON m.id_ambiente_origen = ao.id
    LEFT JOIN ambientes_institucion ad ON m.id_ambiente_destino = ad.id
    LEFT JOIN usuarios u ON m.id_usuario_registro = u.id
    LEFT JOIN institucion i ON m.id_ies = i.id
    ORDER BY m.fecha_registro DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$movimientos = $stmt->fetchAll(PDO::FETCH_OBJ);

// ---------------- CLASE PDF PERSONALIZADA ----------------
class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);
        $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');
        $y = $this->GetY();
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->Line(15, $y, 195, $y);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'REPORTE GENERAL DE MOVIMIENTOS', 0, 1, 'C');
        $this->Ln(4);
    }

    public function Footer() {
        $y = $this->GetY();
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->Line(15, $y, 195, $y);
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// ---------------- GENERAR PDF ----------------
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Listado de Movimientos');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage();

// ---------------- TABLA DE MOVIMIENTOS ----------------
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'LISTADO GENERAL DE MOVIMIENTOS', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 8);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
        <tr>
            <th width="4%" align="center">N°</th>
            <th width="15%" align="center">Ambiente Origen</th>
            <th width="15%" align="center">Ambiente Destino</th>
            <th width="20%" align="center">Usuario Registro</th>
            <th width="15%" align="center">Fecha Registro</th>
            <th width="20%" align="center">Descripción</th>
            <th width="11%" align="center">Institución</th>
        </tr>
    </thead>
    <tbody>';

$contador = 1;
foreach ($movimientos as $mov) {
    $contenido .= '<tr>
        <td width="4%" align="center">' . $contador . '</td>
        <td width="15%" align="left">' . htmlspecialchars($mov->ambiente_origen ?? 'N/A') . '</td>
        <td width="15%" align="left">' . htmlspecialchars($mov->ambiente_destino ?? 'N/A') . '</td>
        <td width="20%" align="left">' . htmlspecialchars($mov->usuario_registro ?? 'N/A') . '</td>
        <td width="15%" align="center">' . htmlspecialchars(date('d/m/Y', strtotime($mov->fecha_registro))) . '</td>
        <td width="20%" align="left">' . htmlspecialchars($mov->descripcion) . '</td>
        <td width="11%" align="left">' . htmlspecialchars($mov->institucion ?? 'N/A') . '</td>
    </tr>';
    $contador++;
}

$contenido .= '</tbody></table>';

$pdf->writeHTML($contenido, true, false, true, false, '');

// ---------------- SALIDA FINAL ----------------
ob_clean();
$pdf->Output('reporte_movimientos.pdf', 'I');

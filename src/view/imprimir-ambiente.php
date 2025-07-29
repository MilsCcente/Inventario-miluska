<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

// ---------------- CONEXIÓN A BASE DE DATOS ----------------
$host = "localhost";
$dbname = "desarro4_miluska";
$username = "desarro4_miluska";
$password = "ccente.25.Rima";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ---------------- CONSULTA DE AMBIENTES ----------------
$sql = "
    SELECT a.id, i.nombre AS institucion, a.encargado, a.codigo, a.detalle, a.otros_detalle
    FROM ambientes_institucion a
    INNER JOIN institucion i ON a.id_ies = i.id
    ORDER BY i.nombre ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$ambientes = $stmt->fetchAll(PDO::FETCH_OBJ);

// ---------------- CLASE PDF PERSONALIZADA ----------------
class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('./src/view/pp/assets/images/gobierno.png', 15, -8, 43);
        $this->Image('./src/view/pp/assets/images/dreaa.png', 170, 1, 18);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');
        $y = $this->GetY();
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->Line(15, $y, 195, $y);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'REPORTE DE AMBIENTES POR INSTITUCIÓN EDUCATIVA', 0, 1, 'C');
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
$pdf->SetTitle('Ambientes por Institución');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

// ---------------- TABLA DE AMBIENTES ----------------
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'LISTADO DE AMBIENTES DE LAS INSTITUCIONES EDUCATIVAS', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 9);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
        <tr>
            <th width="5%" align="center">N°</th>
            <th width="25%" align="center">Institución</th>
            <th width="15%" align="center">Encargado</th>
            <th width="15%" align="center">Código</th>
            <th width="20%" align="center">Detalle</th>
            <th width="20%" align="center">Otros</th>
        </tr>
    </thead>
    <tbody>';

$contador = 1;
foreach ($ambientes as $a) {
    $contenido .= '<tr>
        <td width="5%" align="center">' . $contador . '</td>
        <td width="25%" align="left">' . htmlspecialchars($a->institucion) . '</td>
        <td width="15%" align="center">' . htmlspecialchars($a->encargado) . '</td>
        <td width="15%" align="center">' . htmlspecialchars($a->codigo) . '</td>
        <td width="20%" align="left">' . htmlspecialchars($a->detalle) . '</td>
        <td width="20%" align="left">' . htmlspecialchars($a->otros_detalle) . '</td>
    </tr>';
    $contador++;
}
$contenido .= '</tbody></table>';

$pdf->writeHTML($contenido, true, false, true, false, '');

// ---------------- SALIDA FINAL ----------------
ob_clean();
$pdf->Output('reporte_ambientes.pdf', 'I');

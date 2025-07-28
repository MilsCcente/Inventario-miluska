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

// ---------------- CONSULTA ----------------
$sql = "
    SELECT b.cod_patrimonial, b.denominacion, b.marca, b.modelo, b.color, 
           b.estado_conservacion, a.detalle AS nombre_ambiente
    FROM bienes b
    LEFT JOIN ambientes_institucion a ON b.id_ambiente = a.id
    ORDER BY b.id ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$bienes = $stmt->fetchAll(PDO::FETCH_OBJ);

// ---------------- PDF PERSONALIZADO ----------------
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
        $this->Cell(0, 10, 'REPORTE GENERAL DE BIENES', 0, 1, 'C');
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
$pdf->SetTitle('Listado General de Bienes');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'LISTADO GENERAL DE BIENES', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 9);

// Tabla HTML con columnas ajustadas
$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
        <tr style="text-align:center;">
            <th width="4%">N°</th>
            <th width="14%">Cód. Patrimonial</th>
            <th width="18%">Denominación</th>
            <th width="12%">Marca</th>
            <th width="12%">Modelo</th>
            <th width="10%">Color</th>
            <th width="10%">Estado</th>
            <th width="20%">Ambiente</th>
        </tr>
    </thead>
    <tbody>';

$contador = 1;
foreach ($bienes as $bien) {
    $contenido .= '<tr>
        <td width="4%" align="center">' . $contador . '</td>
        <td  width="14%">' . htmlspecialchars($bien->cod_patrimonial) . '</td>
        <td width="18%">' . htmlspecialchars($bien->denominacion) . '</td>
        <td  width="12%">' . htmlspecialchars($bien->marca) . '</td>
        <td  width="12%">' . htmlspecialchars($bien->modelo) . '</td>
        <td  width="10%">' . htmlspecialchars($bien->color) . '</td>
        <td  width="10%">' . htmlspecialchars($bien->estado_conservacion) . '</td>
        <td  width="20%">' . htmlspecialchars($bien->nombre_ambiente) . '</td>
    </tr>';
    $contador++;
}

$contenido .= '</tbody></table>';
$pdf->writeHTML($contenido, true, false, true, false, '');

// Salida
ob_clean();
$pdf->Output('reporte_general_bienes.pdf', 'I');

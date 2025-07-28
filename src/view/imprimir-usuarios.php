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

// ---------------- CONSULTA DE USUARIOS ----------------
$sql = "
    SELECT dni, nombres_apellidos, correo, telefono, estado 
    FROM usuarios
    ORDER BY nombres_apellidos ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);

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
        $this->Cell(0, 10, 'REPORTE GENERAL DE USUARIOS', 0, 1, 'C');
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
$pdf->SetTitle('Listado de Usuarios');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'LISTADO GENERAL DE USUARIOS', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 9);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
        <tr>
            <th width="5%" align="center">N°</th>
            <th width="15%" align="center">DNI</th>
            <th width="30%" align="center">Nombres y Apellidos</th>
            <th width="25%" align="center">Correo</th>
            <th width="15%" align="center">Teléfono</th>
            <th width="10%" align="center">Estado</th>
        </tr>
    </thead>
    <tbody>';

$contador = 1;
foreach ($usuarios as $u) {
    $contenido .= '<tr>
        <td width="5%" align="center">' . $contador . '</td>
        <td width="15%" align="center">' . htmlspecialchars($u->dni) . '</td>
        <td width="30%" align="left">' . htmlspecialchars($u->nombres_apellidos) . '</td>
        <td width="25%" align="left">' . htmlspecialchars($u->correo) . '</td>
        <td width="15%" align="center">' . htmlspecialchars($u->telefono) . '</td>
        <td width="10%" align="center">' . htmlspecialchars($u->estado) . '</td>
    </tr>';
    $contador++;
}


$contenido .= '</tbody></table>';
$pdf->writeHTML($contenido, true, false, true, false, '');

// Salida
ob_clean();
$pdf->Output('reporte_usuarios.pdf', 'I');

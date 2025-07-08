<?php
$ruta = explode("/", $_GET['views']);
if(!isset($ruta[1]) ||$ruta[1]==""){
    header("location: ".BASE_URL."movimientos");
}

$curl = curl_init(); //inicia la sesión cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=".$_SESSION['sesion_id']."&token=".$_SESSION['sesion_token']."&data=".$ruta[1], //url a la que se conecta
        CURLOPT_RETURNTRANSFER => true, //devuelve el resultado como una cadena del tipo curl_exec
        CURLOPT_FOLLOWLOCATION => true, //sigue el encabezado que le envíe el servidor
        CURLOPT_ENCODING => "", // permite decodificar la respuesta y puede ser"identity", "deflate", y "gzip", si está vacío recibe todos los disponibles.
        CURLOPT_MAXREDIRS => 10, // Si usamos CURLOPT_FOLLOWLOCATION le dice el máximo de encabezados a seguir
        CURLOPT_TIMEOUT => 30, // Tiempo máximo para ejecutar
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // usa la versión declarada
        CURLOPT_CUSTOMREQUEST => "GET", // el tipo de petición, puede ser PUT, POST, GET o Delete dependiendo del servicio
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), //configura las cabeceras enviadas al servicio
    )); //curl_setopt_array configura las opciones para una transferencia cURL

    $response = curl_exec($curl); // respuesta generada
    $err = curl_error($curl); // muestra errores en caso de existir

    curl_close($curl); // termina la sesión 

    if ($err) {
        echo "cURL Error #:" . $err; // mostramos el error
    } else { // en caso de funcionar correctamente
        /*echo $_SESSION['sesion_sigi_id'];
        echo $_SESSION['sesion_sigi_token'];*/
        $respuesta = json_decode($response);
        //print_r ($respuesta);   
        $contenido_pdf='';
        $contenido_pdf.='
        <!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Formato de Movimiento de Bienes</title>

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }
    .header, .footer {
      margin-bottom: 20px;
    }
    .titulo {
      font-weight: bold;
    }
    .motivo {
      font-weight: bold;
      margin-top: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 30px;
    }
    table, th, td {
      border: 1px solid black;
    }
    th, td {
      padding: 8px;
      text-align: center;
    }
    .firma {
      display: flex;
      justify-content: space-between;
      margin-top: 60px;
    }
    .firma div {
      text-align: center;
    }
    .espacio {
      height: 60px;
    }
  </style>
</head>
<body>
<h5>PAPELETA DE ROTACION DE BIENES</h5>
  <div class="header">
    <p><span class="titulo">ENTIDAD :</span> DIRECCION REGIONAL DE EDUCACION - AYACUCHO</p>
    <p><span class="titulo">AREA :</span> OFICINA DE ADMINISTRACIÓN</p>

    <p><span class="titulo">ORIGEN :</span> '.  $respuesta->amb_origen->codigo.'-'.$respuesta->amb_origen->detalle.' </p>

    <p><span class="titulo">DESTINO :</span> '.  $respuesta->amb_destino->codigo.'-'.$respuesta->amb_destino->detalle.'</p>
  </div>

  <div class="motivo">MOTIVO (*) :'.$respuesta->movimiento->descripcion.'</div>
  <table>
    <thead>
      <tr>
        <th>ITEM</th>
        <th>CODIGO PATRIMONIAL</th>
        <th>NOMBRE DEL BIEN</th>
        <th>MARCA</th>
        <th>COLOR</th>
        <th>MODELO</th>
        <th>ESTADO</th>
      </tr>
    </thead>
    <tbody>
        ';        
  
?>


      <tr>
      <?php 
     $contador = 1;
     foreach ($respuesta->detalle as $detalle) {
        $contenido_pdf .= "<tr>";
        $contenido_pdf .= "<td>".$contador."</td>";
        $contenido_pdf .= "<td>".$detalle->cod_patrimonial."</td>";
        $contenido_pdf .= "<td>".$detalle->denominacion."</td>";
        $contenido_pdf .= "<td>".$detalle->marca."</td>";
        $contenido_pdf .= "<td>".$detalle->modelo."</td>";
        $contenido_pdf .= "<td>".$detalle->color."</td>";
        $contenido_pdf .= "<td>".$detalle->estado_conservacion."</td>";
        $contenido_pdf .= "</tr>";
        $contador+=1;

     }

     $contenido_pdf.='
     </tbody>
   </table>
 
   <div class="footer">
     <p>Ayacucho, _________ de __________ del 2024</p>
   </div>
 
   <div class="firma">
     <div>
       <div>------------------------------</div>
       <div>ENTREGUE CONFORME</div>
     </div>
     <div>
       <div>------------------------------</div>
       <div>RECIBÍ CONFORME</div>
     </div>
   </div>
 
 </body>
 </html>
 ';
            ?>
    
<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Miluska');
$pdf->SetTitle('Reporte de movimiento');

//asignar 
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//salto de pagina automatico
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set font
$pdf->SetFont('helvetica', 'B', 12);

//add a page
$pdf->AddPage();
$pdf->writeHTML($contenido_pdf);
ob_clean();
$pdf->Output('reporte_movimiento.pdf', 'I');

  }
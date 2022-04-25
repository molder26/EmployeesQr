<?php

require_once("../phpqrcode/qrlib.php");
require "../config.php";

if (array_key_exists('id', $_POST) && $_POST['id'] !== '' && array_key_exists('condicion', $_POST) && $_POST['condicion'] !== '' && array_key_exists('url', $_POST) && $_POST['url'] !== '') {
    $id = $_POST['id'];
    $condicion = $_POST['condicion'];
    $url = $_POST['url'];

    if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
        if ($stmt = $mysqli->prepare("SELECT dni, nombre FROM empleados WHERE id = ?")) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($dni, $nombre);
            if (!$stmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode("El empleado no se pudo encontrar");
                exit();
            } else {
                $stmt->close();
            }
        }
    }

    $fecha = date('Y-m-d');
    $md5 = md5($dni . $condicion . $fecha);

    $query = $url . "/qrprocesado.php?id=" . $dni . "&nombre=" . urlencode($nombre) . "&accion=" . $condicion . "&check=" . $md5;

    // Path where the images will be saved
    $filepath = 'qr.png';
    // Image (logo) to be drawn
    $logopath = '730.jpg';
    // qr code content
    $codeContents = $query;

    //array_map( "unlink", glob( "*.png" ) );
    unlink($filepath);
    // foreach (glob("*.png") as $file) {
    //     /*** if file is 24 hours (86400 seconds) old then delete it ***/
    //     if (filemtime($file) < time() - 600) { // 1 hour
    //         unlink($file);
    //     }
    // }

    // Create the file in the providen path
    // Customize how you want
    QRcode::png($codeContents,$filepath , QR_ECLEVEL_H, 6);

    // Start DRAWING LOGO IN QRCODE

    $QR = imagecreatefrompng($filepath);

    // START TO DRAW THE IMAGE ON THE QR CODE
    $logo = imagecreatefromstring(file_get_contents($logopath));

    /**
     *  Fix for the transparent background
     */
    imagecolortransparent($logo , imagecolorallocatealpha($logo , 0, 0, 0, 127));
    imagealphablending($logo , false);
    imagesavealpha($logo , true);

    $QR_width = imagesx($QR);
    $QR_height = imagesy($QR);

    $logo_width = imagesx($logo);
    $logo_height = imagesy($logo);

    // Scale logo to fit in the QR Code
    $logo_qr_width = $QR_width/3;
    $scale = $logo_width/$logo_qr_width;
    $logo_qr_height = $logo_height/$scale;

    imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

    // Save QR code again, but with logo on it
    imagepng($QR,$filepath);

    header('Content-Type: application/json');
    echo json_encode("Qr generado correctamente");
}

?>
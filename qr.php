<?php

require_once("../../phpqrcode/qrlib.php");

function CreateQr($nombre, $apellido, $dni, $condicion, $manzana, $casa){

    $fecha = date('Y-m-d');
    $md5 = "";
    
    if ($condicion == 0){
        $condicion = "Propietario";
        $md5 = md5($dni . "Propietario");
    } else if ($condicion == 1){
        $condicion = "Visita";
        $md5 = md5($fecha . $dni. "Visita");
    } else if ($condicion == 2){
        $condicion = "Empleado";
        $md5 = md5($dni . "Empleado");
    }


    // Path where the images will be saved
    $filepath = $dni .'.png';
    // Image (logo) to be drawn
    $logopath = 'bastilla.jpg';
    // qr code content
    $codeContents = '@@' . $dni . '@@' . $md5;

    //array_map( "unlink", glob( "*.png" ) );
    unlink($filepath);
    foreach (glob("*.png") as $file) {
        /*** if file is 24 hours (86400 seconds) old then delete it ***/
        if (filemtime($file) < time() - 600) { // 1 hour
            unlink($file);
        }
    }

    // Create the file in the providen path
    // Customize how you want
    QRcode::png($codeContents,$filepath , QR_ECLEVEL_H, 12);

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

    // prueba de texto

    // Create Image From Existing File
    $jpg_image = imagecreatefrompng($filepath);
    $orig_width = imagesx($jpg_image);
    $orig_height = imagesy($jpg_image);


    // Create your canvas containing both image and text
    $canvas = imagecreatetruecolor($orig_width,  ($orig_height + 70));
    // Allocate A Color For The background
    $bcolor = imagecolorallocate($canvas, 255, 255, 255);
    // Add background colour into the canvas
    imagefilledrectangle( $canvas, 0, 0, $orig_width, ($orig_height + 70), $bcolor);

    // Save image to the new canvas
    imagecopyresampled ( $canvas , $jpg_image , 0 , 0 , 0 , 0, $orig_width , $orig_height , $orig_width , $orig_height );

    // Tidy up:
    imagedestroy($jpg_image);

    // Set Path to Font File
    $font_path = "arial.ttf";

    // Set Text to Be Printed On Image
    $txtnombre = $nombre . " " . $apellido;
    $txtdni = "DNI " . $dni;
    $txtcondicion = $condicion . " M" . $manzana . " C" . $casa;

    // Allocate A Color For The Text
    $color = imagecolorallocate($canvas, 0, 0, 0);      

    // Print Text On Image
    imagettftext($canvas,  16, 0, 45, $orig_height-20, $color, $font_path, $txtnombre);
    imagettftext($canvas,  14, 0, 45, $orig_height+10, $color, $font_path, $txtdni);
    imagettftext($canvas,  14, 0, 45, $orig_height+40, $color, $font_path, $txtcondicion);

    imagepng($canvas,$filepath);


    // End DRAWING LOGO IN QR CODE

    // Ouput image in the browser
    //echo '<img src="'.$filepath.'" />';
}
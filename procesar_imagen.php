<?php
/*
@Autor Eduardo Rivas
*/

function rgb_to_cmyk($rgb) { //convert RGB To CMYK
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;

    $c = 1 - ($r / 255);
    $m = 1 - ($g / 255);
    $y = 1 - ($b / 255);
    $k = min($c, $m, $y) * 100;

    if ($k >= 30) {
        $c = $m = $y = 0;
    } else {
        $c = ($c - $k) / (1 - $k);
        $m = ($m - $k) / (1 - $k);
        $y = ($y - $k) / (1 - $k);
    }

    return array('c' => $c * 100, 'm' => $m * 100, 'y' => $y * 100, 'k' => $k);
}

if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] == 0) {
    $target_dir = "uploads/"; //save data
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));


    $allowed_extensions = array("jpg", "jpeg", "png", "gif");//only file tyspe
    if(!in_array($imageFileType, $allowed_extensions)) {
        echo "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
        exit();
    }

    $image = imagecreatefromstring(file_get_contents($_FILES["fileToUpload"]["tmp_name"]));

    $width = imagesx($image); 
    $height = imagesy($image);

    $total_pixels = $width * $height;//total pixels 

    $white_pixels = 0;
    $green_pixels = 0;
    $gray_pixels = 0;

    for($x = 0; $x < $width; $x++) {
        for($y = 0; $y < $height; $y++) {
           
            $rgb = imagecolorat($image, $x, $y);
          
            $cmyk = rgb_to_cmyk($rgb);//convert
         
            if($cmyk['c'] + $cmyk['m'] + $cmyk['y'] == 0 && $cmyk['k'] < 50) { //white
                $white_pixels++;
            }

            elseif($cmyk['c'] < 50 && $cmyk['m'] == 0 && $cmyk['y'] < 50 && $cmyk['k'] < 50) { //green
                $green_pixels++;
            }
            elseif(abs($cmyk['c'] - $cmyk['m']) <= 10 && abs($cmyk['c'] - $cmyk['y']) <= 10 && abs($cmyk['m'] - $cmyk['y']) <= 10 && $cmyk['k'] < 50) { //gray
                $gray_pixels++;
            }
        }
    }

    //percentage
    $white_percentage = ($white_pixels / $total_pixels) * 100;
    $green_percentage = ($green_pixels / $total_pixels) * 100;
    $gray_percentage = ($gray_pixels / $total_pixels) * 100;

    echo "Porcentaje de blanco (CMYK): " . round($white_percentage, 2) . "%<br>";
    echo "Porcentaje de verde (CMYK): " . round($green_percentage, 2) . "%<br>";
    echo "Porcentaje de gris (CMYK): " . round($gray_percentage, 2) . "%<br>";
} else {
    echo "Error al procesar la imagen.";
}
?>

<?php
class BarcodeGenerator {
    const TYPE_CODE_128 = 'code128';
    const IMAGE_FORMAT_PNG = 'png';

    private function encode_code128($input) {
        // Code 128 character set B encoding table
        $table = array(
            '212222', '222122', '222221', '121223', '121322', '131222', '122213',
            '122312', '132212', '221213', '221312', '231212', '112232', '122132',
            '122231', '113222', '123122', '123221', '223211', '221132', '221231',
            '213212', '223112', '312131', '311222', '321122', '321221', '312212',
            '322112', '322211', '212123', '212321', '232121', '111323', '131123',
            '131321', '112313', '132113', '132311', '211313', '231113', '231311',
            '112133', '112331', '132131', '113123', '113321', '133121', '313121',
            '211331', '231131', '213113', '213311', '213131', '311123', '311321',
            '331121', '312113', '312311', '332111', '314111', '221411', '431111',
            '111224', '111422', '121124', '121421', '141122', '141221', '112214',
            '112412', '122114', '122411', '142112', '142211', '241211', '221114',
            '413111', '241112', '134111', '111242', '121142', '121241', '114212',
            '124112', '124211', '411212', '421112', '421211', '212141', '214121',
            '412121', '111143', '111341', '131141', '114113', '114311', '411113',
            '411311', '113141', '114131', '311141', '411131', '211412', '211214',
            '211232', '2331112'
        );

        // Start character
        $result = $table[105];

        // Checksum calculation
        $weight = 1;
        $checksum = 0;
        for ($i = 0; $i < strlen($input); $i++) {
            $char = ord(substr($input, $i, 1));
            if ($char < 32) $char += 64;
            else if ($char > 126) $char -= 64;
            $result .= $table[$char - 32];
            $checksum += $char * $weight;
            $weight++;
        }
        $checksum %= 103;
        $result .= $table[$checksum];

        // Stop character
        $result .= $table[106];

        return $result;
    }

    function barcodePNG($text, $file, $height = 60, $width = null) {
        // Generate barcode data string using code 128 encoding
        $data = $this->encode_code128($text);
    
        // Calculate width if not provided
        if ($width === null) {
            $width = strlen($data)*13+6;
        }
    
        // Create image object
        $barcode = imagecreate($width, $height+10);
    
        // Allocate colors for black and white
        $white = imagecolorallocate($barcode, 255, 255, 255);
        $black = imagecolorallocate($barcode, 0, 0, 0);
    
        // Fill background with white color
        imagefill($barcode, 0, 0, $white);
    
        // Draw bars
        $x = 3;
        for ($i = 0; $i < strlen($data); $i++) {
            $bar = substr($data, $i, 1);
            if ($bar == '1') {
                imageline($barcode, $x, 5, $x, $height+5, $black);
                $x += 1; // increase line spacing by 1 pixel
            } else {
                $x++;
            }
            $x++; // add 1 to X-coordinate after each line
        }
    
        // Save image to file
        imagepng($barcode, $file);
    
        // Destroy image object
        imagedestroy($barcode);
    }
    
    

    
}

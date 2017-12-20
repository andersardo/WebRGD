<?php
/****************
Simple and limited conversion of Gedcom-files to UTF-8
*************/

function convertGedcom2UTF8($file_in, $file_out, $dir, $logg = FALSE) {
    //$in = fopen($dir . $file_in,'rb');
    //$out = fopen($dir . $file_out,'wb');
    $str = file_get_contents($dir . $file_in);
    $enc_in = mb_detect_encoding($str);
    echo "Char encoding = $enc_in\n";
    if ($enc_in == FALSE) {
        echo "ERR: No encoding detected\n";
        return array(0, 0, 'Cant convert this character encoding', 0);
    }
    if ($enc_in == 'UTF-8') {  //just copy file
        file_put_contents($dir . $file_out, $str);
    }
    else {
        $out_str = mb_convert_encoding($str, "UTF-8", $enc_in);
        //if(substr($result,0,6) == '1 CHAR') $result = '1 CHAR UTF-8';
        file_put_contents($dir . $file_out,
                          preg_replace('/^1 CHAR .*$/m', '1 CHAR UTF-8', $out_str, 1) );
    }
    return array(0, 0, '', 0); //No error
    //fclose($in);
    //fclose($out);
}
?>

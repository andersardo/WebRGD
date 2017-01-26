<?php
/****************
* Exchange or delete of characters in DISBYT.SVE file to
* make result better readable for import to MySQL database
* and converting to utf8
* 2014-12-09
* 2015-11-11 ChangeChar only for DISBYT conversion
* 2015-11-30 changeCP general function for code conversion added
* 2015-11-30 change_ged_CP general funtion for GEDCOM-file to UTF-8 CP
* 2015-12-10 Extended ANSEL codes conversion
* 2015-12-10 Extended check and correction on unsufficient code page mark up and error messages
* @author Ulf Arfvidsson
*/
define ('UTF32_BIG_ENDIAN_BOM'   , chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define ('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define ('UTF16_BIG_ENDIAN_BOM'   , chr(0xFE) . chr(0xFF));
define ('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE)); 
define ('UTF8_BOM'               , chr(0xEF) . chr(0xBB) . chr(0xBF));

class exchange {
  private $ascii_a = NULL;
  private $ascii_u = NULL;
  private $ascii_ansel = NULL;
  // Significant words noridic languages in ANSEL CP1252
  private $ansel_pat = array('fèodd','dèod', ' êar ',' èar ' ,'fèorsamling',
   'vèast','èost','gêard','kèand','ocksêa','dèar',' pêa ','frêan','fèor');
  /***
  * The constructor 
  ***************/
  public function __construct(){
    }
  /********************
  * Read table charconvert on exchange rules
  * 2015-10-30 Type rules => 0 general, 1 UTF-8
  ***************************/
  private function getAscii(){  
    /***** Read char exchange rules from table ******************/
    $ql = 'SELECT * FROM kmq__charconvert ORDER BY AsciiIn, ChrBefore DESC, ChrBefore2 DESC';
    $res = DB::getInstance()->query($ql);
    foreach($res as $row){
      $Name = empty($row['Name']) ? 'All' : $row['Name'];
      $Before = empty($row['ChrBefore']) ? 'N1' : $row['ChrBefore']; 
      $Before2 = empty($row['ChrBefore2']) ? 'N2' : $row['ChrBefore2'];
      $Type = $row['Type'];
      switch ($Type){
        case 0:
        case 1:
          $ndx = $row['AsciiIn'] . '|' . $Name . '|' . $Before2 . '|' . $Before; 
          $this->ascii_a[$ndx] = $row['AsciiOut'] . '|' . $row['ExchQty'];
        case 1: // Only Utf8 exchange rules   
          $this->ascii_u[$ndx] = $row['AsciiOut'] . '|' . $row['ExchQty'];
          break;
      }  
    }
  }
  
  
  /********************
  * Read table charconvert on exchange rules for ANSEL
  * 2015-10-30 Type rules => 2 ANSEL
  ***************************/
  private function getAnsel(){  
    /***** Read char exchange rules from table ******************/
    $ql = 'SELECT * FROM kmq__charconvert WHERE Type = 2 ORDER BY AsciiIn, ChrBefore DESC, ChrBefore2 DESC';
    $res = DB::getInstance()->query($ql);
    foreach($res as $row){
      $Name = empty($row['Name']) ? 'All' : $row['Name'];
      $Before = empty($row['ChrBefore']) ? 'N1' : $row['ChrBefore']; 
      $Before2 = empty($row['ChrBefore2']) ? 'N2' : $row['ChrBefore2'];
      $ndx2 =  $row['AsciiIn'] . '|' . $Name . '|' . $Before2 . '|' . $Before; 
      $this->ascii_ansel[$ndx2] = array($row['AsciiOut'], $row['AsciiOut2']);  
    }
  }
  
  /********************************
  * Exchange selected chars and return utf8
  * @param mixed $elements string to convert
  * @param mixed $Name contributionID
  * @param int $logg save logg-file
  *************************************/
  public function changeChar($str,$Name,$logg=FALSE){
    if(!$this->ascii_a)
      $this->getAscii();
    // Check encoding not UTF8
    $is_code = mb_detect_encoding($str,'ASCII,UTF-8,CP850,ISO-8859-1,Windows-1252',TRUE);
    $str_part = str_split($str);
    $BeforeVal = 'N1'; // All -2 values
    $BeforeVal2 = 'N2';  // All -1 values 
    $Add_chr = FALSE ; // Character to concatenate in end of string
    $str_ut = ''; $step = 0; $ustep = 0;
    if($logg)fwrite($logg,$str . '==>' . $is_code . "\n");
    if($logg) fwrite($logg,"Tecken - antal - step - sträng\n");
    foreach($str_part as $key => $value){
      $Qty = 1; // Normally replace or keep chr
      $val = ord($value); $nx = 0; // $nx is test marker
      $ndx1 = $val . '|' . $Name . '|' . $BeforeVal2 . '|' . $BeforeVal;
      $ndx2 = $val . '|' . $Name . '|' . $BeforeVal2 . '|N1';
      $ndx3 = $val . '|' . $Name . '|N2|N1';
      $ndx4 = $val . '|All|' . $BeforeVal2 . '|' . $BeforeVal;
      $ndx5 = $val . '|All|' . $BeforeVal2 . '|N1';
      $ndx6 = $val . '|All|N2|N1';    
      if($step){
        // Skip next value -$step times
        $step--;
        $Add_chr = $BeforeVal;
        $BeforeVal = $BeforeVal2; 
        $BeforeVal2 = $val; }
      else {
        if($ustep){
        // Keep value as is UTF8 -$ustep times
        $ustep--;  }
        // String $ndx* of 3 ascii-values  + Name to compare with exchange rules
        if($is_code == 'UTF-8' AND array_key_exists($ndx4,$this->ascii_u)){
          // Sequence with UTF-8 3 steps and no change
          $ustep = 1; $nx = 14; }
        elseif($is_code == 'UTF-8' AND array_key_exists($ndx5,$this->ascii_u)){
          // Sequence with UTF-8 2 steps and no change
          $ustep = 0; $nx = 15; }      
        elseif(array_key_exists($ndx1,$this->ascii_a)){
          // Compare to this value, name and 2 chars before
          list($val, $Qty) = explode('|',$this->ascii_a[$ndx1]);
          $step = 2; $nx = 1; }
        elseif(array_key_exists($ndx2,$this->ascii_a)){
          // Compare to this value, name and 1 char before
          list($val, $Qty) = explode('|',$this->ascii_a[$ndx2]);     
          $step = 1;   $nx = 2; } 
        elseif(array_key_exists($ndx3,$this->ascii_a)){
          // Compare to this value and name
          list($val, $Qty) = explode('|',$this->ascii_a[$ndx3]);
          $step = 0;  $nx = 3 ;} 
        elseif(array_key_exists($ndx4,$this->ascii_a)){
          // Compare to this value and -2 and -1 char all names
          list($val, $Qty) = explode('|',$this->ascii_a[$ndx4]);
          $step = 2; $nx = 4; } 
        elseif(array_key_exists($ndx5,$this->ascii_a)){
          // Compare to this value, -1 char and all names
          list($val, $Qty) = explode('|',$this->ascii_a[$ndx5]);
          $step = 1;  $nx = 5;}      
        elseif(array_key_exists($ndx6,$this->ascii_a)){ 
          // Compare to this value and all names
          list($val, $Qty) = explode('|',$this->ascii_a[$ndx6]);
          $step = 0;  $nx = 6; }      
        // Step is number of chars to delete after $val in $str
        // Ustep is number of chars to keep as is in out string excluding $val
        if($Qty OR $step){
          // Concatenate one position to out string
          $str_ut .= is_numeric($Add_chr) ? chr($Add_chr):'';
          $Add_chr = $BeforeVal; 
          $BeforeVal = $BeforeVal2;
          $BeforeVal2 = $val;
        }
      }
      if($logg) fwrite($logg,$value . '-' . $Qty . '-' . $step . '-' . $str_ut . '= ' . $nx . ' ' . "\n");
    }
    // On remaining steps fulfil step over
    $val = '';
    while($step){
      $step--;
      $Add_chr = $BeforeVal;
      $BeforeVal = $BeforeVal2; 
      $BeforeVal2 = $val; }
    // Add the three last chars and convert to UTF-8
    $str_ut .= chr($Add_chr);
    if(is_numeric($BeforeVal)) $str_ut .= chr($BeforeVal);
    if(is_numeric($BeforeVal2)) $str_ut .= chr($BeforeVal2);
    $nu_code = mb_detect_encoding($str_ut,'UTF-8,CP850,ISO-8859-1,Windows-1252',TRUE);
    if($nu_code != 'UTF-8'){
      // Conversion to UTF8
      $ret = iconv($nu_code,"UTF-8//TRANSLIT",$str_ut);
      if($ret === FALSE){
        if($logg) fwrite($logg,'Ej UTF8: Bidrag: ' . $Name . '=>' . $str_ut . "\n");}
      else $str_ut = $ret;
    } 
    if($logg) fwrite($logg, $str_ut . '->' . $is_code . '->' . $nu_code . "\n");
    return $str_ut;  
  }
  
  /***************************************************
  * Code conversion to UTF-8 
  * @param mixed $str string to be converted
  * @param arr $acp code page, special code, error message, stop/go
  * @param mixed $logg logg file used in test
  ***********************************/
  public function changeCP($str,$acp,$logg = FALSE){  
    $nu_code = mb_detect_encoding($str,'ASCII,UTF-8,CP850,ISO-8859-1,Windows-1252',TRUE);
    $str= $this->mb_rtrim($str);
    $str = ltrim($str);
    $cp = strtoupper($acp[0]); $special_cp = $acp[1];  
    if($cp == 'UTF-8' OR $nu_code == 'UTF-8') $str_ut = $str;
    elseif($nu_code == 'ASCII' AND substr($cp,0,6) != 'UTF-16' AND substr($cp,0,6) != 'UTF-32')
      $str_ut = $str;
    elseif($cp == 'MIX' AND $nu_code != 'UTF-8') $str_ut = iconv('ISO-8859-1',"UTF-8//TRANSLIT",$str);  
    elseif($cp) $str_ut = iconv($cp,"UTF-8//TRANSLIT",$str);
    else $str_ut = iconv($nu_code,"UTF-8//TRANSLIT",$str);
    if($logg){
      $tmp = '';
      $str_part = str_split($str_ut);
      foreach($str_part as $value){
        $tmp .= ord($value) . '(' . $value . ')| ';
      }
      fwrite($logg, $tmp . "\n");
    } 
    if($special_cp == 'ANSEL'){
      // ANSEL special conversion rules in table
      if(!$this->ascii_ansel)
        $this->getAnsel();
      $str_part = str_split($str_ut);
      $BeforeVal = 'N1'; // All -2 values
      $BeforeVal2 = 'N2';  // All -1 values 
      $Add_chr = FALSE ; // Character to concatenate in end of string
      $str_ut = ''; $step = 0;
      if($logg)fwrite($logg,$str . '|CP=' . $nu_code . ' ==> UTF-8' . "\n");
      if($logg) fwrite($logg,"Tecken - antal - step - sträng\n");
      foreach($str_part as $value){
        $ut_arr = array();
        $val = ord($value); $nx = 0;
        $ndx4 = $val . '|All|' . $BeforeVal2 . '|' . $BeforeVal;
        $ndx5 = $val . '|All|' . $BeforeVal2 . '|N1';
        $ndx6 = $val . '|All|N2|N1';
        if($logg) fwrite($logg,"ndx4= " . $ndx4 . "step=" . $step . "\n");    
        if($step){
          // Skip next value -$step times - values in last key search
          $step--;
          $Add_chr = $BeforeVal;
          $BeforeVal = $BeforeVal2; 
          $BeforeVal2 = $val; }
        else {
          // String $ndx* of 3 ascii-values to compare with exchange rules 
          if(array_key_exists($ndx4,$this->ascii_ansel)){
            // Compare to this value and -2 and -1 char all names
            $ut_arr = $this->ascii_ansel[$ndx4];
            $step = 2; $nx = 24; } 
          elseif(array_key_exists($ndx5,$this->ascii_ansel)){
            // Compare to this value, -1 char and all names
            $ut_arr = $this->ascii_ansel[$ndx5];
            $step = 1;  $nx = 25;}   
          elseif(array_key_exists($ndx6,$this->ascii_ansel)){ 
            // Compare to this value and all names
            $ut_arr = $this->ascii_ansel[$ndx6];
            $step = 0;  $nx = 26; }
          /***** key search done ****/
          if($ut_arr){
            // key hit, add replacement values
            foreach($ut_arr as $vue){
              $str_ut .= is_numeric($vue) ? chr($vue):'';
            }
          } else{ 
            // Step index one pos. Add char to string
            $Add_chr = $BeforeVal; 
            $BeforeVal = $BeforeVal2;
            $BeforeVal2 = $val;
            // No key! Copy as is one char
            $str_ut .= is_numeric($Add_chr) ? chr($Add_chr):''; 
          }                 
        }
        if($logg) fwrite($logg,$value . '-' . count($ut_arr) . '-' . $step . '-' . $str_ut . '= ' . $nx . "\n");
      }
      while($step){
        // Skip next value -$step times - values in last key search
        $step--;
        $Add_chr = $BeforeVal;
        $BeforeVal = $BeforeVal2; 
        $BeforeVal2 = ''; }
      // Add last 2 chars in cache if no replacement made  
      if(empty($ut_arr)){
        $str_ut .= is_numeric($BeforeVal) ? chr($BeforeVal):''; 
        $str_ut .= is_numeric($BeforeVal2) ? chr($BeforeVal2):'';
      }
    }
    if($logg) fwrite($logg, $str_ut . "\n");
    return $str_ut;  
  }

  /********************************************
  * Detection of code page used in gedcom file 
  * BOM first choice check in supplied file
  * @param str $file first line with BOM
  * @param str $path path to file in document root
  * @return arr $acp detected code page, special codepage, error message, STOP/GO
  */
  function detect_encoding($file, $path = FALSE) {
    $err = array();
    $in = fopen($path . $file,'rb');
    $rows = 0; $cp = FALSE; $char = FALSE; $sour = FALSE; $cp_special = FALSE;
    while(($row = fgets($in,4096)) !== FALSE):
      if(strlen($row) > 600) {
        // Long row without proper line endings
        $row_a = explode(chr(13), $row);
        foreach($row_a as $rows => $value){
          $row = ltrim($value);
          $row = $this->mb_rtrim($row);
          if($rows == 0)
            $cp = $this->check_bom($row);
          else {
            if($cp) $row = iconv($cp,"UTF-8//TRANSLIT",$row);
            if(substr($row,0,6) == '1 SOUR'){
              $row = rtrim($row);
              list($d,$d1,$sour) = explode(' ',$row);
              if(!empty($char)) break 2;
            }
            if(substr($row,0,6) == '1 CHAR'){
              $char = rtrim($row);
              if(!empty($sour)) break 2;
            }
          }  
        }   
      } else {
        $row = ltrim($row);
        $row = $this->mb_rtrim($row);
        if($rows == 0){
          // Check BOM
          $cp = $this->check_bom($row);
        } elseif($rows == 1 AND $cp) {
          // Second test of cp
          $str_tst = pack('H*','E280');
          $row_BE = iconv("UTF-16BE","UTF-8//TRANSLIT",$row);
          $row_LE = iconv("UTF-16LE","UTF-8//TRANSLIT",$row);
          if($cp AND strpos($row_BE,$str_tst)) $cp = 'UTF-16LE';
          elseif($cp AND strpos($row_LE,$str_tst)) $cp = 'UTF-16BE';
        }
        if($cp) $row = iconv($cp,"UTF-8//TRANSLIT",$row);
        if(substr($row,0,6) == '1 SOUR'){
          $row = rtrim($row);
          list($d,$d1,$sour) = explode(' ',$row);
          if(!empty($char)) break;
        }
        if(substr($row,0,6) == '1 CHAR'){
          $char = strtoupper(rtrim($row));
          if(!empty($sour)) break;
        }
        $rows++;
        if($rows > 50){
          // Markup CHAR or SOUR not detected in first 50 rows
          $err[] = 'GEDCOM-filen är ofullständigt uppmärkt eller är detta inte en GEDCOM-fil. Resultatfilen kan vara felaktig!!';
          $cp = 'MIX';
          break;
        }
      } 
    endwhile;
    $cp_special = FALSE;
    switch($sour){
      case 'Anarkiv':
        $char_a = explode(' ',$char);
        if(empty($cp)){
          if(empty($char_a[2])) $cp = 'MIX';
          elseif($char_a[2] == 'Unicode') $cp = 'UTF-8' ;
          else $cp = $char_a[2]; }
        if(!empty($char_a[4])) $cp_special = $char_a[4];
        break;
      case 'GeneWeb':
        // 1 CHAR UTF-8 men ISO-8859-1 i testfilen?? tests per rad
        $cp = 'MIX';
        break;  
      default:
        if(!$cp) list($d1,$d2,$cp) = explode(' ',$char);
        if($cp == 'IBMPC' OR $cp == 'PCDOS' OR $cp == 'MSDOS' OR $cp == 'IBM WINDOWS' OR $cp == 'IBM-DOS' OR $cp == 'IBM' OR $cp == 'OEM') $cp = 'CP850';
        elseif($cp == 'ANSI') $cp = 'CP1252';
        elseif($cp == 'ANSEL'){$cp = 'CP1252' ; $cp_special = 'ANSEL';}
        else $cp = 'CP1252';
        break;
    }
    // Test of code page is in compliance with mark up.
    $other_cp = FALSE; $ansel = 0; $goon = TRUE; $rows = 0;
    while(($row = fgets($in,500)) !== FALSE):
      // Detect ansel codes
      $nu_code = mb_detect_encoding($row,'ASCII,UTF-8,CP850,ISO-8859-1,Windows-1252',TRUE);
      if($nu_code != 'UTF-8' AND $nu_code != 'ASCII'){
        $other_cp = TRUE;
        $row = iconv($nu_code,"UTF-8//TRANSLIT",strtolower($row));
        // CP other
        foreach ($this->ansel_pat as $value){          
          if(strpos($row,$value))
          $ansel++;
        }
      }
      elseif($nu_code == 'UTF-8'){
        foreach ($this->ansel_pat as $value){          
          if(strpos(strtolower($row),$value))
          $ansel++;
        }
      }
      if($sour == 'Anarkiv'){
        if(strpos($row,'+AEA-')){
          $err[] = 'Anarkivexport med felaktigt format. Konvertering avbryts!';
          $goon = FALSE;
          break;
        }
      }  
      $rows++;
      if($rows > 500) break;
    endwhile;
    fclose($in);
    // Conclusions from discovered status
    switch ($cp){
    case 'UTF-8':
      if(empty($ansel) AND $cp_special == 'ANSEL'){
        $err[] = 'ANSEL med teckentabell UTF-8 noterat i filen. Inga ANSEL-tecken detekterade.';}
      elseif($ansel AND $cp_special == 'ANSEL'){
        $err[] = 'ANSEL med teckentabell UTF-8 noterat i filen. Detta kan ge felaktigt resultat.';}  
      elseif($ansel){
        $err[] = 'ANSEL-koder finns i filen och teckentabell UTF-8 angiven. ANSEL-tecknen konverteras men detta kan ge felaktigt resultat';
        $cp_special = 'ANSEL';}
      break;
    case 'MIX':
      $err[] = 'Teckentabell ej angiven. Konvertering sker efter radvis detektering.';
      if($ansel AND empty($cp_special)){
        $err[] = 'ANSEL-koder detekterade i filen (' . $ansel . ' st) men har ej angivits. Detta kan ge felaktigt resultat';
        $cp_special = 'ANSEL';
      }          
      break;
    case 'CP850':
    case 'CP1252':
      if($ansel AND empty($cp_special)){
        $err[] = 'ANSEL-koder detekterade i filen (' . $ansel . ' st) men har ej angivits. Detta kan ge felaktigt resultat';
        $cp_special = 'ANSEL';
      }
      break;   
    default:
      if(empty($sour)){
        $err[] = 'Filen är inte i GEDCOM-format. Konvertering sker efter radvis detektering';
        $cp = 'MIX';
      }       
    }  
    
    $acp = array($cp,$cp_special,$err,$goon);
    return $acp; 
  }
  /****************************
  * Remove ending line feed and carrige return in row
  * @param mixed $str
  * @return str $str
  **************************/
  function mb_rtrim($str) { 
  return preg_replace("/(\r\n|\n|\r)$/i", "", $str); 
  }
  /*****************************
  * Remove BOM in file
  * @param mixed $text
  * @return str $text
  *************************/
  function remove_bom($text) {
    $bom8 = pack('H*','EFBBBF');
    $bom16be = pack('H*','FEFF');
    $bom16le = pack('H*', 'FFFE');
    $text = preg_replace("/^$bom8|^$bom16be|^$bom16le/", '', $text);
    return $text;
  }
  
  /****************************
  * Check if BOM exists on first line in file
  * @param str $row
  * @return str $cp detected code page or false
  *****************************/
  function check_bom($row){
    $first2 = substr($row, 0, 2);
    $first3 = substr($row, 0, 3);
    $first4 = substr($row, 0, 4);  
    if ($first3 == UTF8_BOM) $cp = 'UTF-8';
    elseif ($first4 == UTF32_BIG_ENDIAN_BOM) $cp = 'UTF-32BE';
    elseif ($first4 == UTF32_LITTLE_ENDIAN_BOM) $cp = 'UTF-32LE';
    elseif ($first2 == UTF16_BIG_ENDIAN_BOM) $cp = 'UTF-16BE';
    elseif ($first2 == UTF16_LITTLE_ENDIAN_BOM) $cp = 'UTF-16LE';
    else $cp = FALSE;       
    return $cp;
  }
  
  /***************************************
  * Convert GEDCOM file to code page UTF-8
  * @param str $file_in
  * @param str $file_out
  * @param str $dir
  * @param str [$logg]
  * @return bol TRUE or False
  *************************************/
  function change_ged_CP($file_in, $file_out, $dir, $logg = FALSE){
    $in = fopen($dir . $file_in,'rb');
    // Check which code page is used in GEDCOM file;
    $acp = $this->detect_encoding($file_in,$dir);
    if(!$acp[3])// FALSE to break process 
      return $acp; // Felkoder
    $out = fopen($dir . $file_out,'wb');
    $rows = 0;
    while(($row = fgets($in, 4096))!== FALSE):
      if(empty($rows)){
        $row = $this->remove_bom($row);
        if(strlen($row) > 100) $long_row = TRUE; // Reunion Apple file!
        else $long_row = FALSE; // Windows file etc... 
      }
      $rows++;
      if($long_row){
        $row_a = explode(chr(13),$row);
        foreach($row_a as $key => $value){
          if($key > 0) fwrite($out,"\n");
          /*** Conversion of code page to utf-8 ****/
          $result = $this->changeCP($value,$acp,$logg);
          if(substr($result,0,6) == '1 CHAR') $result = '1 CHAR UTF-8';
          fwrite($out,$result);
        }
      } else {
        /*** Conversion of code page to utf-8 ****/
        $result = $this->changeCP($row,$acp,$logg);  
        if(substr($result,0,6) == '1 CHAR') $result = '1 CHAR UTF-8';
        fwrite($out,$result . "\n");
      }
    endwhile;
    fclose($in);
    fclose($out);
    return $acp; // error messages
  }
}      
?>

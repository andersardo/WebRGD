<?php
/****************
* Exchange or delete of characters in DISBYT.SVE file to
* make result better readable for import to MySQL database
* and converting to utf8
* 2014-12-09
* 2015-11-11 function for changeAnsel convertion obsolete
* 2015-11-11 ChangeChar only for DISBYT conversion
* 2015-11-11 general function for code conversion added changeCP
* @author Ulf Arfvidsson
*/
class exchange {
  private $ascii_a = NULL;
  private $ascii_u = NULL;
  private $ascii_ansel = NULL;
  
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
      $ndx2 =  $row['AsciiIn'] . '|All|' . $Before2 . '|' . $Before; 
      $this->ascii_ansel[$ndx2] = $row['AsciiOut'] . '|' . $row['AsciiOut2'] . '|' . $row['ExchQty'];  
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
  * @param mixed $cp forced code page or FALSE
  * @param mixed $logg logg file used in test
  ***********************************/
  public function changeCP($str,$cp = FALSE,$logg = FALSE){  
    $nu_code = mb_detect_encoding($str,'UTF-8,CP850,ISO-8859-1,Windows-1252',TRUE);
    if($nu_code == 'UTF-8') $str_ut = $str;
    elseif($cp == 'CP850') 
      $str_ut = iconv($cp,"UTF-8//TRANSLIT",$str);
    else $str_ut = iconv($nu_code,"UTF-8//TRANSLIT",$str); 
    if($cp == 'ANSEL'){
      // ANSEL special conversion
      if(!$this->ascii_ansel)
      $this->getAnsel();
      $str_part = str_split($str_ut);
      $BeforeVal = 'N1'; // All -2 values
      $BeforeVal2 = 'N2';  // All -1 values 
      $Add_chr = FALSE ; // Character to concatenate in end of string
      $str_ut = ''; $step = 0;
      if($logg)fwrite($logg,$str . '|CP=' . $nu_code . ' ==> UTF-8' . "\n");
      if($logg) fwrite($logg,"Tecken - antal - step - sträng\n");
      foreach($str_part as $key => $value){
        $Qty = 1; // Normally replace or keep chr
        $val = ord($value); $nx = 0; $valx = FALSE;
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
          // String $ndx* of 3 ascii-values to compare with exchange rules 
          if(array_key_exists($ndx4,$this->ascii_ansel)){
            // Compare to this value and -2 and -1 char all names
            list($val2,$valx, $Qty) = explode('|',$this->ascii_ansel[$ndx4]);
            $step = 2; $nx = 24; } 
          elseif(array_key_exists($ndx5,$this->ascii_ansel)){
            // Compare to this value, -1 char and all names
            list($val2,$valx, $Qty) = explode('|',$this->ascii_ansel[$ndx5]);
            $step = 1;  $nx = 25;}   
          elseif(array_key_exists($ndx6,$this->ascii_ansel)){ 
            // Compare to this value and all names
            list($val2, $valx, $Qty) = explode('|',$this->ascii_ansel[$ndx6]);
            $step = 0;  $nx = 26; }     
          if($valx){ 
            // Insert 2 chars when value in $valx
            // Concatenate one position to out string
            $str_ut .= is_numeric($Add_chr) ? chr($Add_chr):'';
            $Add_chr = $BeforeVal; 
            $BeforeVal = $BeforeVal2;
            $BeforeVal2 = $val2;
            $val = $valx;
          }        
          // Step is number of chars to delete after $val in $str
          if($Qty OR $step){
            // Concatenate one position to out string
            $str_ut .= is_numeric($Add_chr) ? chr($Add_chr):'';
            $Add_chr = $BeforeVal; 
            $BeforeVal = $BeforeVal2;
            $BeforeVal2 = $val;
          }
        }
        if($logg) fwrite($logg,$value . '-' . $Qty . '-' . $step . '-' . $str_ut . '= ' . $nx . "\n");
      }
      // On remaining steps fulfil step over
      $val = '';
      while($step){
        if($logg )fwrite($logg, $step . ' Add=' . $Add_chr . ' Bef=' . $BeforeVal . ' Bef2=' . $BeforeVal2 . "\n");
        $step--;
        $Add_chr = $BeforeVal;
        $BeforeVal = $BeforeVal2; 
        $BeforeVal2 = $val;
        $val = '';}
      // Add the three last chars 
      $str_ut .= chr($Add_chr);
      if(is_numeric($BeforeVal)) $str_ut .= chr($BeforeVal);
      if(is_numeric($BeforeVal2)) $str_ut .= chr($BeforeVal2);
    }
    if($logg) fwrite($logg, $str_ut . "\n");
    return $str_ut;  
  }
}      
?>

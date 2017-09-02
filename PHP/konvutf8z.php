<?PHP
/*
Programmet sparas i UTF-8 !!!!!!!!!!!!!!

Konverterar ANSEL, ANSI eller IBMPC teckenrepresentation till UTF-8.

För enkelhetens skull har även andra funktioner lagts till.

2017-06 förbättrad information i meddelanden.

*/
require 'initbas.php';
require 'initdb.php';
//
include 'exchange.php';
include 'db.php';
//
$brytr = 0;
$larma = 0;
$larmant = 0;
$larmrub1 = 1;
$larmrub2 = 1;
$larmrub3 = 1;
/*
Programmet skall läsa alla rader för taggen CHIL, uppdatera 
dom i en tabell för att säkerställa att inga dubbletter skapas.

Troligen ett mycket sällsynt fel, men allvarligt om det förekommer.

Dessutom testas rader som saknar egen tagg, förhoppningsvis bara 
från noteringar.

*/
//
$larmx = 0;
//	Formell kontroll av IND/FAM och referenser
$filein=$directory . "RGD1.GED";
$handin=fopen($filein,"r");
$head = 'ON';	
//	Läs in indatafilen				
$lines = file($filein,FILE_IGNORE_NEW_LINES);
foreach($lines as $radnummer => $str)
{
//	Huvud börjar - läs tills första individ/relation
	if($head == 'ON') {
		$ztag = substr($str,0,3);
		if($ztag == '0 @') {
			$zlen = strlen($str);
			$zmax = 3;
			while($zmax <= $zlen) {
				$ztal = substr($str,$zmax,1);
				if($ztal == '@')  {
					if((substr($str,$zmax,5) == '@ IND') || (substr($str,$zmax,5) == '@ FAM')) {
						$head = 'OFF';
					}
					$zmax = $zlen; 
				}	
				$zmax++;
			}
		}	
	}
//	Första individ/relation börjar	
	if($head == 'OFF')
	{
//	hitta idnummer för individ/relation
		$ztag = substr($str,0,3);
		if($ztag == '0 @') {
			$ptyp = '';
			$isex = '';
			$fref = '';
//			
			$znum = '';
			$zlen = strlen($str);
			$zmax = 3;
			while($zmax <= $zlen) {
				$ztal = substr($str,$zmax,1);
				if($ztal != '@') {
					$znum = $znum.$ztal;
				}
				else {
					$ptyp = substr($str,($zmax+2),3);
					$zmax = $zlen;
					$zmax++;
				}
				$zmax++;
			}
		}
		$tagk = substr($str,0,5);
		$tagg = substr($str,0,6);
		$rlen = strlen($str);
		if($tagk == '1 SEX') {
			$isex = substr($str,6,1);
		}	
		if($tagg == '1 FAMS') {
			$fref = substr($str,7,$rlen);
			if($isex == 'F') {
//				$irad[] = '1 WIFE @'.$znum.'@'.$fref;
				$irad[] = '1 MAKE @'.$znum.'@'.$fref;
			}
			else {
//				$irad[] = '1 HUSB @'.$znum.'@'.$fref;
				$irad[] = '1 MAKE @'.$znum.'@'.$fref;
			}
		}	
		if($tagg == '1 FAMC') {
			$fref = substr($str,7,$rlen);
			$irad[] = '1 CHIL @'.$znum.'@'.$fref;
		}	
		if($tagg == '1 HUSB') {
//			$frad[] = $str.'@'.$znum.'@';
			$trad = $str.'@'.$znum.'@';
			$frad[] = '1 MAKE'.substr($trad,6,strlen($trad));
		}	
		if($tagg == '1 WIFE') {
//			$frad[] = $str.'@'.$znum.'@';
			$trad = $str.'@'.$znum.'@';
			$frad[] = '1 MAKE'.substr($trad,6,strlen($trad));
		}	
		if($tagg == '1 CHIL') {
			$frad[] = $str.'@'.$znum.'@';
		}	
	}
}	
fclose($handin);
//	
$anti = count($irad);
$antf = count($frad);
if($anti != $antf) {
	echo '<br/>';
	echo 'OBS! Felaktig GEDCOM fil, kan ej bearbetas korrekt '.$anti.' i/f '.$antf.' <br/>';
	if($antf > $anti) {
		echo 'Individ(er) saknas för referens(er) <br/>';
	}
	else {
		echo 'Referens(er) saknas för individ(er) <br/>';
	}
}
sort($irad);
sort($frad);
$s1 = 0;
$s2 = 0;
$ok = 'OK';
while($s1 < $anti && $s2 < $antf)
{
	if($irad[$s1] != $frad[$s2]) {
//	stoppa ej		$typtest = 'EJ';
		if($antf > $anti) {
			$larmx++;
			$lrmx[] =  'Formellt fel i GEDCOM filen: Referens saknas/felaktig, berörda identiteter '.substr($frad[$s2],6,strlen($frad[$s2]));
			echo 'Berörda identiteter  -  -  -  '.substr($frad[$s2],6,strlen($frad[$s2])).' <br/>';
			$s1--;
		}
		if($anti > $antf) {
			$larmx++;
			$lrmx[] =  'Formellt fel i GEDCOM filen: Referens saknas/felaktig, berörda identiteter '.substr($irad[$s1],6,strlen($irad[$s1]));
			echo 'Berörda identiteter  -  -  -  '.substr($irad[$s1],6,strlen($irad[$s1])).' <br/>';
			$s2--;
		}
		if($anti == $antf) {
			if($ok = 'OK') {
				echo '<br/>';
				echo 'OBS! Felaktig GEDCOM fil, kan ej bearbetas korrekt <br/>';
			}	
			$larmx++;
			$lrmx[] =  'Formellt fel i GEDCOM filen: Referens saknas/felaktig, berörda identiteter '
			.substr($irad[$s1],6,strlen($irad[$s1])).' och/eller '.substr($frad[$s2],6,strlen($frad[$s2]));
			echo 'Berörda identiteter  -  -  -  '.substr($irad[$s1],6,strlen($irad[$s1])).' och/eller '
			.substr($frad[$s2],6,strlen($frad[$s2])).' <br/>';
		}
		$ok = 'FEL';
	}
	$s1++;
	$s2++;
}
//
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDH.GED";
//
$result=rename($fileut,$filein);
if($result == false) {
	echo "OBS! Filkopieringen misslyckades. <br/>";
	echo "<br/>";
}
//
$typ = '';
$typx = '';
$typtest = '';
if(file_exists($filein)) {
	$handin=fopen($filein,"r");
	$handut=fopen($fileut,"w");
//
	echo "<br/>";
// AA0 lock table in a Web-anvironment
	$result = mysqli_query($link,"LOCK TABLES chil WRITE");
	if(!$result)
	{
		echo "LOCK av chil fungerande inte".mysqli_error($link);
	}
	// TRUNCATE fungerar inte tillsammans med LOCK
	$SQL="DELETE FROM chil";
	$result=mysqli_query($link,$SQL);
	if(!$result)
	{
		echo $SQL."Tömningen av chil fungerande inte".mysqli_error($link);
	}
	else
	{
		echo "Tabellen chil är tömd och klar <br/>";
	}
	echo "<br/>";
//	
	$key = '';
	$val = '';
	$oldstr = '';
	$oldtagg = '';
	$utfant = 0;
	$utftest = '';
//	Läs in indatafilen				
	$lines = file($filein,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str) {
//	Dolda tecken i början på filen, t.ex. BOM tecken
		$tstlen = strlen($str);
		$tsthead = substr($str,($tstlen-4),4);
		if(($tsthead == 'HEAD') && ($tstlen > 6)) {
			echo $tstlen.'/'.$str.' Felaktig HEAD tag rättad. <br/>';
			$str = '0 HEAD';
		}
//	Testa NAME						
		$tagg = substr($str,0,6);
		if($tagg == '1 NAME') {
//			$zlen = strlen($str);
//			$zant = 0;
//			$sant = 0;
//			while($zant < $zlen) {
//				$ztkn = substr($str,$zant,1);
//				if($ztkn == '/') {
//					$sant++;
//				}
//				$zant++;
//			}
			$sant = 0;
			$zant = count_chars($str,0);
			$sant = $zant[ord('/')];
			$str = preg_replace('/\//', ' /', $str, 1);
			$str = preg_replace('/  \//', ' /', $str, 1);
//			print "$str antal / =$sant\n";
//	Rätta formellt felaktiga						
			if($sant == 0) {
				$str = $str." //";
			}	
			if($sant == 1) {
				$str = $str."/";
				$sant++;
				$sant++;
			}
		}
//		
////	Fixa felaktigt avbrutna rader
		$textut = '';
		$nypos1 = substr($str,0,1);
		$nypos2 = substr($str,1,1);
		$nytagg = substr($str,0,7);
		if(($nypos1 >= '0') && ($nypos1 <= '9') && ($nypos2 == ' ')) {
			fwrite($handut,$str."\r\n");
			$oldstr = $str;
		}	
		else {
			if(($oldtagg == '1 SOUR ') || ($oldtagg == '1 NOTE ') 
			|| ($oldtagg == '2 CONT ') || ($oldtagg == '2 CONC ')){
				$textut = '2 CONC '.$str;
			}
			elseif(($oldtagg == '2 SOUR ') 
			|| ($oldtagg == '3 CONT ') || ($oldtagg == '3 CONC ')){
				$textut = '3 CONC '.$str;
			}
			else {
				if($str != '') {
					$textut = '2 CONC '.$str;
				}
				else {
					$textut = '';
					echo "Tom rad borttagen efter raden /".$oldstr."/ <br/>";
				}
			}
//
			if($textut != '') {
				fwrite($handut,$textut."\r\n");
				$oldstr = $textut;
			}	
		}
		$oldtagg = $nytagg;
//	
		$tagt = strlen($str);
		$tag3 = substr($str,0,3);
		$tag4 = substr($str,2,4);
		if($tag3 == '0 @') {
			$val = '';
			$tagp = 3;
			while($tagp < $tagt)
			{
				$tag1 = substr($str,$tagp,1);
				if($tag1 == '@') {
					$tagp = $tagt;
				}
				else {
					$val = $val.$tag1;
					$tagp++;
				}	
			}	
		}
		elseif($tag4 == 'CHIL') {
//	i tabellen används barnet som key, skall bara finnas en gång
//	och familjen som val, för att ge information.			
			$key = '';
			$tagp = 8;
			while($tagp < $tagt)
			{
				$tag1 = substr($str,$tagp,1);
				if($tag1 == '@') {
					$tagp = $tagt;
				}
				else {
					$key = $key.$tag1;
					$tagp++;
				}	
			}	
//	Nyuppdatera varje barn
// 	Insert
			$SQL="INSERT INTO chil(chil,fam) VALUES('$key','$val')";
			$result=mysqli_query($link,$SQL);
			if($result)
			{
//				echo "Uppdaterat barn ".$key."<br/n>";
			}
			else
			{
//	Select 
				$SQL="SELECT fam FROM chil WHERE chil='$key'";
				$result=mysqli_query($link,$SQL);
				if(!$result)
				{
					echo $SQL."fungerande inte".mysqli_error($link);
				}
				else
				{
					$row=mysqli_fetch_assoc($result);
					$fam=$row['fam'];
//	stoppa ej		$typtest = 'EJ';
					$larmx++;
					$lrmx[] = "Formellt fel i GEDCOM filen: Barn ".$key.
					", som finns i familj ".$fam.", finns även i familjen ".$val;
				}	
			}
		}
		else {
//			ointressant rad för barntest
		}
		
//	Testar teckenformatet	
		$char = substr($str,0,7);
		$trlr = substr($str,0,6);
		if($char == '1 CHAR ') {
			$typ = substr($str,7,(strlen($str) - 7));
			if(($typ == 'ANSEL') || ($typ == 'IBMPC') || ($typ == 'IBM WINDOWS') ) {
				echo "Teckenformat = ".$typ.". Filen konverteras. <br/>";
				echo "<br/>";
				$typtest = 'JA';
			}
			elseif($typ == 'UTF-8') {
				echo "Teckenformat = ".$typ.". Filen klar för bearbetning. <br/>";
				echo "<br/>";
				$typtest = 'JA';
				$utftest = '8';
			}
			elseif($typ == 'ANSI') {
//				echo "Teckenformat = ".$typ.". Filen måste konverteras med Notepad++. <br/>";
//				echo "Programmet avbryts därför. <br/>";
				echo "Teckenformat = ".$typ.". Filen konverteras. <br/>";
				echo "<br/>";
				$typtest = 'JA';
			}
			else {
				echo "Teckenformat = ".$typ." kunde ej tolkas, måste kollas. <br/>";
				echo "Filen omdöpt till RGD01.GED och den får ej användas för vidare bearbetning. <br/>";
				echo "<br/>";
				echo "Programmet avbryts därför. <br/>";
				$typtest = 'EJ';
				$typx = 'X';
			}
		}
		if(($trlr == '0 TRLR') && ($typ == '')) {
			echo "Taggen CHAR saknas, teckenformatat kan därför ej fastställas. <br/>";
			echo "Filen omdöpt till RGD01.GED och den får ej användas för vidare bearbetning. <br/>";
			echo "<br/>";
			echo "Programmet avbryts därför. <br/>";
			$typtest = 'EJ';
			$typx = 'Y';
		}
	}
	fclose($handut);
	
//	CHIL testen avslutad
	$result = mysqli_query($link,"UNLOCK TABLES");
	if(!$result)
	{
		echo "UNLOCK av chil fungerande inte".mysqli_error($link);
	}
//	
//
	if($typtest == 'EJ') {
		fclose($handin);
		$filez=$directory . "RGD01.GED";
		$result=rename($filein,$filez);
		if($result == false) {
			echo "OBS! Namnändringen till RGD01 misslyckades. <br/>";
			echo "<br/>";
			}
//	Larm
		$filelarm=$directory . "Check_lista.txt";
		$handlarm=fopen($filelarm,"a");
		if($larmrub1 == 1) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fwrite($handlarm,$larm."\r\n");
			$larm = "*** L A R M  (I) Teckenformat";
			fwrite($handlarm,$larm."\r\n");
			$larmrub1++;
		}
		if($typ == '') {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = "Teckenformatet (CHAR) saknas helt och filen kan då ej tolkas på rätt sätt.";
			fwrite($handlarm,$larm."\r\n");
			$larmant++;
		}
		if($typx == 'X') {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = "Okänt teckenformatet, CHAR ".$typ.", filen kunde ej tolkas på rätt sätt.";
			fwrite($handlarm,$larm."\r\n");
			$larmant++;
		}
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * Resultetet blir därför helt oförutsägbart.";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * Körningen har avbrutits * * *";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
//
}
else
{
	echo "Filen ".$filein." saknas, programmet avbrutet <br/>";
}
//
////////////////////
//
if($typtest == 'JA') {
	fclose($handin);
//
	$fileut=$directory . "RGD1.GED";
	$filein=$directory . "RGDX.GED";
//
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//
//
		$filein=$directory . "RGDX.GED";
		$fileut=$directory . "RGDR.GED";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//	Disbyt konvertering hel fil
		$byt = new exchange();
		$dir = $directory;
        $file_in = "RGDX.GED";
        $file_out = "RGDR.GED";
        $logg = FALSE;//$logg = fopen($dir . 'logg.txt','wb');//  
        // Start timer
        $secs = microtime(true);
        /*******************************
        * Change CP to UTF-8 in GEDCOM file
        * required classes class_db_disbyt.php and class_exchange_disbyt.php
        * $logg = FALSE as std or handle to file
        *********************************************/
        $acp = $byt->change_ged_CP($file_in,$file_out,$dir,$logg);        
      
        /************************************/
		if(!empty($acp[3])) { 
			$acp3 = array($acp[3]);
		   foreach($acp3 as $value){
			   echo '*** '.$value.'<br/>';
		   }
		}
		else {
			echo "UFT-8 test OK <br/>";
		}
        // Measure time
        $sece = microtime(true);
        $laptime = (int)($sece - $secs);  
		echo 'Time: '.$laptime.'<br/n>';	
//		
		fclose($handin);
		fclose($handut);
//
		$filein=$directory . "RGDR.GED";
		$fileut=$directory . "RGD1.GED";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
//
			$text = $str;
//	Kolla $str
			mb_internal_encoding("UTF-8");
			if(mb_check_encoding($str,"UTF-8") == false) 
			{
				$utfant++;
				echo "* ".$str." - UTF-8 fel <br/>";
//	Kolla tecken
				$text = '';
				$imax=0;
				$len=strlen($str);
				while($imax <= $len)
				{
					$std=substr($str,$imax,1);
//	kolla om $std är UTF-8
					mb_internal_encoding("UTF-8");
					If(mb_check_encoding($std,"UTF-8") == false) {
						$std = '#';
					}	
//			
					$text=$text.$std;
					$imax++;
				}
			}
//if($str != $text) {
//	echo $str." fixad till ".$text." <br/>";
//}
//					
			fwrite($handut,$text."\r\n");
			$text="";
		}
//		
		fclose($handin);
		fclose($handut);
//		
		if($utfant > 0) {
			echo "<br/n>";
			echo $utfant." Rader med okända UTF-8 tecken som ersatts med #-tecken  <br/>";
		}
		else {
			echo "UTF-8 koll OK <br/>";
		}
		echo "<br/n>";
		echo "Program konvutf8 avslutat <br/n>";
		echo "<br/n>";
		echo "Filen ".$fileut." har skapats <br/n>";
}
/*
Kollar och larmar för dubbla notiser för födelse, dop, död och begravd
då Disgen tillåter att dubbla notiser läggs in och också genereras till GEDCOM.
Även namn förekommer dubbelt i vissa GEDCOM filer.

Kommer troligen fler tester, som kunde underlätta.
Strukturen på GEDCOM filerna är inte standardiserade.
*/
$filename=$directory . "RGD1.GED";
//
$tant=0;
//
$nant=0;
$fant=0;
$cant=0;
$dant=0;
$bant=0;
$mant=0;
$aktf="";
$txtf="";
$aktd="";
$txtd="";
$aktm="";
$txtm="";
$dbmr="";
$xnum="";
//
if(file_exists($filename))
{
	$handle=fopen($filename,'r');
	$head = "ON";
//	Läs in indatafilen				
	$lines = file($filename,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str)
	{
//	Huvud börjar - läs tills första individ/relation
		if($head == "ON") {
			$ztag = substr($str,0,3);
			if($ztag == "0 @") {
				$zlen = strlen($str);
				$zmax = 3;
				while($zmax <= $zlen) {
					$ztal = substr($str,$zmax,1);
					if($ztal == "@") {
						if((substr($str,$zmax,5) == "@ IND") || (substr($str,$zmax,5) == "@ FAM")) 
						{
							$head = "OFF";
						}
						$zmax = $zlen; 
					}	
					$zmax++;
				}
			}	
		}
//	Första individ/relation börjar	
		if($head == "OFF")
		{
//	Testa brutna tecken i radslutet
			$len=strlen($str);
			if($len > 7) {
				$tkn99 = substr($str,($len-1),1);
				$asciivalue = ord($tkn99);
				if(($asciivalue == 194) || ($asciivalue == 195)) {
					echo "Varning: Raden slutar ej med UTF-8 /".$tkn99."/ ".$str."<br>";
				}	
			}	
//	hitta idnummer för individ/relation
			$ztag = substr($str,0,3);
			if($ztag == '0 @') {
				$znum = '';
				$zlen = strlen($str);
				$zmax = 3;
				while($zmax <= $zlen) {
					$ztal = substr($str,$zmax,1);
					if($ztal != '@') {
						$znum = $znum.$ztal;
					}
					else {
						$zmax = $zlen;
						$zmax++;
					}
					$zmax++;
				}
			}
//	
			$taggk=substr($str,2,4);
			$tagg3=substr($str,2,3);
			$ztag = substr($str,0,3);
			if($ztag == "0 @") {
				if($dbmr != '') {
					$larma++;
					$lrmr[] = $dbmr.' (Jfr. '.$xnum.')';
				}
				$nant=0;
				$fant=0;
				$cant=0;
				$dant=0;
				$bant=0;
				$mant=0;
				$aktf="";
				$txtf="";
				$aktd="";
				$txtd="";
				$aktm="";
				$txtm="";
				$dbmr="";
				$xnum="";
				$ntxt=' - ';
			}
			if($taggk == 'NAME')
			{
				$nlen = strlen($str);
				$ntxt = $ntxt.substr($str,7,($nlen-6));
				$nant++;
			}
			if($taggk == 'BIRT')
			{
				$aktf = 'JA';
				$fant++;
			}
			if($tagg3 == 'CHR')
			{
				$aktf = 'JA';
				$cant++;
			}
			if($taggk == 'DEAT')
			{
				$aktd = 'JA';
				$dant++;
			}
			if($taggk == 'BURI')
			{
				$aktd = 'JA';
				$bant++;
			}
			if($taggk == 'MARR')
			{
				$aktm = 'JA';
				$mant++;
			}
			if($taggk == 'DATE') {
				$lend = strlen($str);
				if(($aktf == 'JA') && ($txtf == '')) {
						$txtf = substr($str,7,$lend); }
				if(($aktd == 'JA') && ($txtd == '')) {
						$txtd = substr($str,7,$lend); }
				if(($aktm == 'JA') && ($txtm == '')) {
						$txtm = substr($str,7,$lend); }
				$aktf = '';
				$aktd = '';
				$aktm = '';
			}
			if(($taggk == 'HUSB') || ($taggk == 'WIFE') || ($taggk == 'CHIL')) {
				if($xnum == '') {
					$xlen = strlen($str);
					$xmax = 7;
					while($xmax <= $xlen) {
						$xtal = substr($str,$xmax,1);
						if($xtal != '@') {
							$xnum = $xnum.$xtal;
						}
						$xmax++;
					}					
				}
			}	
			if($taggk == 'NAME')
			{
				if($nant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla namnförekomster för individ - Id => '.$znum.$ntxt.' => '.$txtf.'-'.$txtd;
					$txtf = '';
					$txtd = '';
				}
			}
			if($taggk == 'BIRT')
			{
				if($fant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla födelsenotiser för individ - Id => '.$znum.$ntxt.' => '.$txtf.'-'.$txtd;
					$txtf = '';
					$txtd = '';
				}
			}
			if($tagg3 == 'CHR')
			{
				if($cant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla dopnotiser för individ - Id => '.$znum.$ntxt.' => '.$txtf.'-'.$txtd;
					$txtf = '';
					$txtd = '';
				}
			}
			if($taggk == 'DEAT')
			{
				if($dant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla dödsnotiser för individ - Id => '.$znum.$ntxt.' => '.$txtf.'-'.$txtd;
					$txtf = '';
					$txtd = '';
				}
			}
			if($taggk == 'BURI')
			{
				if($bant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla begravningsnotiser för individ - Id => '.$znum.$ntxt.' => '.$txtf.'-'.$txtd;
					$txtf = '';
					$txtd = '';
				}
			}
			if($taggk == 'MARR')
			{
				if($mant > 1)
				{
					$dbmr = 'Dubbla giftemålsnotiser för familj - Id => '.$znum.' => '.$txtm;
				}
			}
		}	
	}
	if($dbmr != '') {
		$larma++;
		$lrmr[] = $dbmr.' (Jfr. '.$xnum.')';
	}
	fclose($handle);
//
	if($larmx > 0)
	{
//	Larm
		$brytr = 0;
		$filelarm=$directory . "Check_lista.txt";
		$handlarm=fopen($filelarm,"a");
		if($larmrub2 == 1) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fwrite($handlarm,$larm."\r\n");
			$larm = "*** F E L  L I S T A  (II) Strukturfel";
			fwrite($handlarm,$larm."\r\n");
			$larmrub2++;
		}
		if($larmx > 0) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$lrmlistx=array_unique($lrmx);
			foreach($lrmlistx as $lrmradx) {
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}
				fwrite($handlarm,$lrmradx." \r\n");
				$larmant++;
			}
		}
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * GEDCOM filen skall inte användas innan formella felaktigheter är korrigerade.";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
//
	if($larma > 0)
	{
//	Larm
		$brytr = 0;
		$filelarm=$directory . "Check_lista.txt";
		$handlarm=fopen($filelarm,"a");
		if($larmrub3 == 1) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fwrite($handlarm,$larm."\r\n");
			$larm = "*** V A R N I N G  (III) Dubbla förekomster";
			fwrite($handlarm,$larm."\r\n");
			$larmrub3++;
		}
		if($larma > 0) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$lrmlista=array_unique($lrmr);
			foreach($lrmlista as $lrmrad) {
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}
				fwrite($handlarm,$lrmrad." \r\n");
				$larmant++;
			}
		}
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * Om endast en förekomst behandlas, blir resultetet slumpartat.";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
}
else
{
	echo $filename.' Saknas <br/n>';
}	
//
if($larmant > 0) {
	echo "<br/>";
	if($larmant == 1) {
		echo "* * * Check-listan utökad med ".$larmant." rad. <br/>";
	}
	else {
		echo "* * * Check-listan utökad med ".$larmant." rader. <br/>";
	}
	echo "<br/>";
}
echo 'Program klart <br/n>';
?>
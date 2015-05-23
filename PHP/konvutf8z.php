<?PHP
/*
Programmet sparas i UTF-8 !!!!!!!!!!!!!!

Konverterar ANSEL, ANSI eller IBMPC teckenrepresentation till UTF-8.

En ANSI fil kan med fördel konverteras med Notepad++

Programmet ändrar /å/ä/ö/Å/Ä/Ö/á/à/é/É/è/È/ü/Ü/§/
men inga andra specialtecken.

För enkelhetens skull har även andra funktioner lagts till.

*/
require 'initbas.php';
require 'initdb.php';
//
$brytr = 0;
$larma = 0;
$larmant = 0;
$larmrub1 = 1;
$larmrub2 = 1;
$larmrub3 = 1;
/*
Programmet skall läsa alla racer för taggen CHIL, uppdatera 
dom i en tabell för att säkerställa att inga dubbletter skapas.

Troligen ett mycket sällsynt fel, men allvarligt om det förekommer.

Dessutom testas rader som saknar egen tagg, förhoppningsvis bara 
från noteringar.

*/
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
$larmx = 0;
if(file_exists($filein)) {
	$handin=fopen($filein,"r");
	$handut=fopen($fileut,"w");
//
	echo "<br/>";
// AA0 lock table in a Web-anvironment
	$result = mysql_query("LOCK TABLES chil WRITE");
	if(!$result)
	{
		echo "LOCK av chil fungerande inte".mysql_error();
	}
	// TRUNCATE fungerar inte tillsammans med LOCK
	$SQL="DELETE FROM chil";
	$result=mysql_query($SQL);
	if(!$result)
	{
		echo $SQL."Tömningen av chil fungerande inte".mysql_error();
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
			$sant = 0;
			$zant = count_chars($str,0);
			$sant = $zant[ord('/')]; //hur många '/'?
//	Rätta formellt felaktiga						
//	Alltid en ' ' för första '/'
			$str = preg_replace('/\//', ' /', $str, 1);
			$str = preg_replace('/  \//', ' /', $str, 1);
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
			$result=mysql_query($SQL);
			if($result)
			{
//				echo "Uppdaterat barn ".$key."<br/n>";
			}
			else
			{
//	Select 
				$SQL="SELECT fam FROM chil WHERE chil='$key'";
				$result=mysql_query($SQL);
				if(!$result)
				{
					echo $SQL."fungerande inte".mysql_error();
				}
				else
				{
					$row=mysql_fetch_assoc($result);
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
	$result = mysql_query("UNLOCK TABLES");
	if(!$result)
	{
		echo "UNLOCK av chil fungerande inte".mysql_error();
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
	if(file_exists($fileut))
	{
		echo $fileut." finns redan, programmet avbryts<br/>";
	}
	else
	{
		if(file_exists($filein))
		{
			$handin=fopen($filein,"r");
			$handut=fopen($fileut,"w");
//
			$text="";
//		
			$lines = file($filein,FILE_IGNORE_NEW_LINES);
			foreach($lines as $radnummer => $str)
			{
//
			if($utftest == '8') {
//	skall kolla $str
				mb_internal_encoding("UTF-8");
				$text = $str;
				if(mb_check_encoding($text,'UTF-8') == false) {
					$text = '';
					$imax=0;
					$len=strlen($str);
					while($imax <= $len)
					{
						$std=substr($str,$imax,1);
//	kolla om $std är UTF-8
						mb_internal_encoding("UTF-8");
						If(mb_check_encoding($std,'UTF-8') == false) {
							$std = '#';
							$utfant++;
						}	
//					
						$text=$text.$std;
						$imax++;
					}
//					echo "Raden innehåller okända UTF-8 tecken, ersatta med # <br/>".$text." <br/>";
				}
				fwrite($handut,$text."\r\n");
				$text="";
			}
			else {
//			
				$char = substr($str,0,7);
				if($char == '1 CHAR ')
				{
					$str = $char.'UTF-8';
				}
				$imax=0;
				$len=strlen($str);
				while($imax <= $len)
				{
					$spc=substr($str,$imax,2);
					$std=substr($str,$imax,1);
//	ö			
					if($spc == '�o') {
						$text=$text."ö";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."ö";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."ö";
						$imax++;
					}
//	Ö			
					elseif($spc == '�O') {
						$text=$text."Ö";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Ö";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Ö";
						$imax++;
					}
//	ä
					elseif($spc == '�a') {
						$text=$text."ä";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."ä";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."ä";
						$imax++;
					}
//	Ä
					elseif($spc == '�A') {
						$text=$text."Ä";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Ä";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Ä";
						$imax++;
					}
//	å
					elseif($spc == '�a') {
						$text=$text."å";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."å";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."å";
						$imax++;
					}
//	Å
					elseif($spc == '�A') {
						$text=$text."Å";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Å";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Å";
						$imax++;
					}
//	á
					elseif($spc == '�a') {
						$text=$text."á";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."á";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."á";
						$imax++;
					}
//	à
					elseif($spc == '�a') {
						$text=$text."á";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."á";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."á";
						$imax++;
					}
//	é
					elseif($spc == '�e') {
						$text=$text."é";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."é";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."é";
						$imax++;
					}
//	É
					elseif($spc == '�E') {
						$text=$text."É";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."É";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."É";
						$imax++;
					}
//	è
					elseif($spc == '�e') {
						$text=$text."é";
						$imax++;
						$imax++;
					}
					elseif($spc == '�e') {
						$text=$text."é";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."é";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."é";
						$imax++;
					}
//	È
					elseif($spc == '�E') {
						$text=$text."É";
						$imax++;
						$imax++;
					}
					elseif($spc == '�E') {
						$text=$text."É";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."É";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."É";
						$imax++;
					}
//	ü
					elseif($spc == '�u') {
						$text=$text."ü";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."ü";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."ü";
						$imax++;
					}
//	Ü
					elseif($spc == '�U') {
						$text=$text."Ü";
						$imax++;
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Ü";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."Ü";
						$imax++;
					}
//	§
//						elseif($spc ==  '') {
//						$text=$text."§";
//						$imax++;
//						$imax++;
//					}
					elseif($std == '�') {
						$text=$text."§";
						$imax++;
					}
					elseif($std == '�') {
						$text=$text."§";
						$imax++;
					}
//	-
//						elseif($spc == '') {
//						$text=$text."-";
//						$imax++;
//						$imax++;
//					}
					elseif($std == '�') {
						$text=$text."-";
						$imax++;
					}
					else {
//	kolla om $std är UTF-8
						mb_internal_encoding("UTF-8");
						If(mb_check_encoding($std,'UTF-8') == false) {
							$std = '#';
							$utfant++;
						}	
//					
						$text=$text.$std;
						$imax++;
					}
				}
				fwrite($handut,$text."\r\n");
				$text="";
			}
			}
			fclose($handin);
			fclose($handut);
//
			if($utfant > 0) {
				echo "* * * ".$utfant." tecken som inte kunnat tolkas som UTF-8 har ersatts med # <br/>";
			}	
//
			echo "<br/n>";
			echo "Program konvutf8 avslutat <br/n>";
			echo "<br/n>";
			echo "Filen ".$fileut." har skapats <br/n>";
//
		}
		else
		{
			echo "Filen ".$filein." saknas, programmet avbrutet <br/>";
		}
	}
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
				$nant=0;
				$fant=0;
				$cant=0;
				$dant=0;
				$bant=0;
				$mant=0;
				$ntxt=' - ';
			}
			if($taggk == 'NAME')
			{
				$nlen = strlen($str);
				$ntxt = $ntxt.substr($str,7,($nlen-6));
				$nant++;
				if($nant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla namnförekomster för individ - '.$znum.$ntxt;
				}
			}
			if($taggk == 'BIRT')
			{
				$fant++;
				if($fant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla födelsenotiser för individ - '.$znum.$ntxt;
				}
			}
			if($tagg3 == 'CHR')
			{
				$cant++;
				if($cant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla dopnotiser för individ - '.$znum.$ntxt;
				}
			}
			if($taggk == 'DEAT')
			{
				$dant++;
				if($dant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla dödsnotiser för individ - '.$znum.$ntxt;
				}
			}
			if($taggk == 'BURI')
			{
				$bant++;
				if($bant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla begravningsnotiser för individ - '.$znum.$ntxt;
				}
			}
			if($taggk == 'MARR')
			{
				$mant++;
				if($mant > 1)
				{
					$larma++;
					$lrmr[] = 'Dubbla giftemålsnotiser för familj - '.$znum;
				}
			}
		}	
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
		$larm = "* * * Endast en av referenserna kan representera biologisk koppling.";
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
		$larm = "* * * Endast en förekomst behandlas, så resultetet blir slumpartat.";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
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
?>
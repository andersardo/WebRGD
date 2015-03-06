<?PHP
/*
Programmet är avsett för snabbkontroll av GEDCOM filer.
Består av ett antal program som körs i serie.

Säkerhetstest av teckenformat.

*/
require 'initbas.php';
require 'initdb.php';
//
//	ob start
//	if (ob_get_level() == 0) ob_start();
//
$larmant = 0;
$larmrub1 = 1;
$larmrub2 = 1;
$larmrub3 = 1;
$larmrub4 = 1;
//			
$fileix=$directory . "RGD1.GED";
//
echo "<br/>";
//
$typ = '';
$typtest = '';
if(file_exists($fileix)) {
	echo $fileix." finns<br/>";
	echo "$fileix har storleken ".filesize($fileix)."<br/>";
	$handix=fopen($fileix,"r");
	echo "<br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
	$lines = file($fileix,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str) {
		$char = substr($str,0,7);
		$trlr = substr($str,0,6);
		if($char == '1 CHAR ') {
			$typ = substr($str,7,(strlen($str) - 7));
			if(($typ == 'ANSEL') || ($typ == 'ANSI') ||($typ == 'IBMPC')) {
				echo "Teckenformat = ".$typ.". Filen måste konverteras. <br/>";
				echo "<br/>";
				echo "Av säkerhetsskäl har filen döpts om till RGD01.GED. <br/>";
				echo "Innan konverteringen kan köras skall RGD01.GED byta namn till RGD1.GED <br/>";
				echo "Kör sedan programmet konvutf8x innan snabbkörningen åter startas. <br/>";
				echo "<br/>";
				echo "Programmet avbryts därför. <br/>";
				}
			elseif($typ == 'UTF-8') {
				echo "Teckenformat = ".$typ.". Filen klar för bearbetning. <br/>";
//				echo "<br/>";
				$typtest = 'OK';
			}
			else {
				echo "Teckenformat = ".$typ." kunde ej tolkas, måste kollas. <br/>";
				echo "<br/>";
				echo "Filen får inte användas för vidare bearbetning. <br/>";
				echo "Av säkerhetsskäl har filen döpts om till RGD01.GED. <br/>";
				echo "<br/>";
				echo "Programmet avbryts därför. <br/>";
				$typtest = 'EJ';
			}	
		}
		if(($trlr == '0 TRLR') && ($typ == '')) {
			echo "Taggen CHAR saknas, teckenformatat kan därför ej fastställas. <br/>";
				echo "<br/>";
				echo "Filen får inte användas för vidare bearbetning. <br/>";
				echo "Av säkerhetsskäl har filen döpts om till RGD01.GED. <br/>";
			echo "<br/>";
			echo "Programmet avbryts därför. <br/>";
			$typtest = 'EJ';
		}
	}	
	fclose($handix);
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
}
else
{
	echo "Filen ".$fileix." saknas, programmet avbrutet <br/>";
}
//
////////////////////
//
if($typtest != 'OK') {
	$fileix=$directory . "RGD1.GED";
	if(file_exists($fileix)) {
		$fileux=$directory . "RGD01.GED";
		$result=rename($fileix,$fileux);
		if($result == false) {
			echo "OBS! Filkopieringen misslyckades. <br/>";
			echo "<br/>";
		}
	}
}	
//	Larm
if($typtest == 'EJ') {
	$larmant++;
	$filelarm=$directory . "LARM_lista.txt";
	$handlarm=fopen($filelarm,"a");
	if($larmrub1 == 1) {
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "L A R M  T Y P  I ";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larmrub1++;
	}
	$larm = " ";
	fwrite($handlarm,$larm."\r\n");
	if($typ == '') {
		$larm = "Teckenformatet saknas helt och filen kan då ej tolkas på rätt sätt.";
		fwrite($handlarm,$larm."\r\n");
	}	
	else {
		$larm = "Okänt teckenformatet CHAR ".$typ.", filen kunde ej tolkas på rätt sätt.";
		fwrite($handlarm,$larm."\r\n");
	}	
	$larm = "* * * Resultetet blir därför helt oförutsägbart.";
	fwrite($handlarm,$larm."\r\n");
	$larm = " ";
	fwrite($handlarm,$larm."\r\n");
	$larm = "* * * Filen får inte användas för vidare bearbetning.";
	fwrite($handlarm,$larm."\r\n");
	$larm = "* * * Av säkerhetsskäl har filen döpts om till RGD01.GED.";
	fwrite($handlarm,$larm."\r\n");
	$larm = " ";
	fwrite($handlarm,$larm."\r\n");
	fclose($handlarm);
}
/*
Första programmet består av tre delar som lagts samman i ett flöde.

Programmet är avsett för GEDCOM filer med strukturerade källor.
Källorna ligger då i slutet av filen medan respektive källa bara innehåller en referens.

Programmet delar infilen, från Disgen eller Minsläkt fil, till två indatafiler.
I enfil placeras identiteter och värden som skall uppdateras i databastabell.
I andra filen placeras alla resterande poster, dvs. de normala GEDCOM posterna.

Programmet konvtabi läser sen in alla indexerade identiteter och laddar tabellen sour,
programmet konvtabu tar in alla övriga poster och ersätter SOUR pekare
med texten från databastabellen och skapar en traditionell SOUR post i utfilen.

*/
$filei1=$directory . "RGD1.GED";
//
echo "<br/>";
echo "Program konvtabbx startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
//
$filezz=$directory . "RGD1.GED";
//
$filein=$directory . "RGDT.GED";
//
$fileu1=$directory . "RGDU.GED";
$fileu2=$directory . "RGDV.GED";
//
if(file_exists($filezz))
{
//
//	echo "<br/>";
	$filexx=$directory . "RGD1_bup.GED";
	$result=copy($filezz,$filexx);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//		else {
//			echo "Filkopiering gick bra. ".$filexx." har skapats <br/>";
//			echo "<br/>";
//		}	
//
//	echo "<br/>";
	$result=rename($filezz,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//	}	
//
	$filtest=0;
//
	$rad=0;
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handu1=fopen($fileu1,"w");
		$handu2=fopen($fileu2,"w");
		$fil = 0;
		$char = 'NEJ';
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
			$noll=substr($str,0,1);
			$tabb=substr($str,0,4);
			$tagg=substr($str,2,4);
			if($noll == "0") {
				$fil = 2;
			}
			if($tabb == "0 @S") {
				$fil = 1;
			}
			if($tabb == "0 @N") {
				$fil = 1;
			}
			if($tabb == "0 @X") {
				$fil = 1;
			}
			if($tabb == "0 @R") {
				$fil = 1;
			}
			if($tabb == "0 @M") {
				$fil = 1;
			}
			if($fil == 1)
			{
				if($tagg != '_UPD') {
					fwrite($handu1,$str."\r\n");
				}	
			}
			else
			{
				fwrite($handu2,$str."\r\n");
			}
		}
		fclose($handin);
		fclose($handu1);
		fclose($handu2);
//		echo "<br/>";
		echo "Program konvtabbx avslutad <br/>";
		echo "<br/>";
//		echo "Filerna ".$fileu2." och ".$fileu1." har skapats.<br/>";
//		echo "Nästa steg: Kör programmet konvtabi  <br/>";
//
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts <vr/>";
	}
}
else
{
	echo $filezz." saknas, programmet avbryts<br/>";
	echo "<br/>";
//	echo "Programmen konvtabi och konvtabu måste köras <br/>";
//	echo "innan detta program kan återstartas <br/>";
//	echo "<br/>";
//	echo "Om detta inte är lämpligt kan RGD1_bup.GED renameas <br/>";
}
?>
<?PHP
/*
Programmet skall läsa av strukturerade källor från Disgen eller Minsläkts 
GEDCOM fil och ladda data i tabell sour.

Innan detta program körs, skall programmet konvtabb körts. Det delar upp 
GEDCOM filen i 2 filer.
Filen från det programmet är indata till detta program.

OBS!
     Innan denna körning startas skall tabellen sour tömts på tidigare innehåll.  OBS!
OBS!

Tabellen skall sen i konvtabu användas för att uppdatera texterna på sin "rätta" plats.

Kan vara andvändbart på GEDCOM filer som år skapade av Disgen eller MinSläkt med
strukturerade källor.

*/
echo "<br/>";
echo "Program konvtabix startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//
$filename=$directory . "RGDU.GED";
//
$rad=0;
$len=0;
$txt='';
if(file_exists($filename))
{
//	echo $filename." finns<br/>";
//	echo "$filename har storleken ".filesize($filename)."<br/>";
	$handle=fopen($filename,"r");
//
//	echo "<br/>";
// AA0 lock table in Web-environment
	$result = mysql_query("LOCK TABLES sour WRITE");
	if(!$result)
	{
		echo "LOCK av sour fungerande inte".mysql_error();
	}
	// TRUNCATE fungerar inte tillsammans med LOCK
	//	$SQL="TRUNCATE sour"; 
	$SQL="DELETE FROM sour";
	$result=mysql_query($SQL);
	if(!$result)
	{
		echo $SQL."Tömningen av sour fungerande inte".mysql_error();
	}
//	else
//	{
//		echo "Tabellern sour är tömd och klar <br/>";
//	}
//	echo "<br/>";
//	
	$sekv=1;
	$tbas=0;
	$id='';
	$rad=0;
//	Läs in indatafilen				
	$lines = file($filename,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str)
	{
		$nytt = substr($str,0,3);
		if($nytt == "0 @")
		{
			$imax=3;
			$sekv=0;
			$just=0;
			$tgnr = '0';
			$id='';
			$tgtx='';
			$tgtp='';
			$temp='';
			$txt='';
			while($imax < 23)
			{
				$test=substr($str,$imax,1);
				$imax++;
				if($test == "@")
				{
					$tgtp=substr($str,$imax+1,4);
					$id=$tgtp.$temp;
					$tgtx = '';
					$len = strlen($str);
					if($len > ($imax+6)) {
						$tgtx=substr($str,($imax+6),($len-6));
					}
					$imax=23;
				}
				else
				{
					$temp=$temp.$test;
				}
				if($tgtx == '') {
//					if(($tgtp == 'SOUR') || ($tgtp == 'SUBM')) {
					if($tgtp == 'SOUR') {
						$tgnr = '';
						$tgtp = '';
					}
				}
			}
		}
		else
		{
			$tgtx = '';
			$len=strlen($str);
			$tgnr = substr($str,0,1);
			$tgtp = substr($str,2,4);
			$tgtx = substr($str,7,($len-7));
			if($sekv == 0)
			{
				if(($tgtp == 'ABBR') || ($tgtp == 'TYPE') || ($tgtp == 'TITL')) {
					$tgtp = 'SOUR';
					$just = $tgnr;
					$tgnr = '0';
				}
			}
		}
//			
		if($sekv > 0) {
			$tgnr = $tgnr - $just;
		}
		$txt=$tgnr.' '.$tgtp.' '.$tgtx;
		$rad++;
		if($sekv >= 9998) {
			echo "OBS! maxantal rader uppnått på ".$id." / ".$txt." <br/>";
			$txt = '';
		}
//
		$tlen = strlen($txt);
		if($tlen > 2)
		{
			$sekv++;
			$tmax = 0;
			$txty = '';
			$fixz = "\\";
			while($tmax < $tlen) {
				$txtx = substr($txt,$tmax,1);
				if($txtx == "'") {
					$txty = $txty.'"';
				}
				elseif($txtx == $fixz) {
					$txty = $txty."/";
				}
				else {
					$txty = $txty.$txtx;
				}
				$tmax++;
			}
			$txt = $txty;
//				
			$SQL="INSERT INTO sour(id,sekv,text) VALUES('$id',$sekv,'$txt')";
			$result=mysql_query($SQL);
			if(!$result)
			{	
				echo $SQL."fungerande inte".mysql_error()."<br/>";
			}
			$txt="";
		}	
	}
	fclose($handle);
//	if($rad > 0)
//	{
//		echo "<br/>";
		echo "Program konvtabix avslutad <br/>";
		echo "<br/>";
//		echo "Antal poster som uppdaterats i tabellen sour blev " .$rad." rader <br/n>";
//		echo "<br/>";
//		echo "Nästa steg: Kör programmet konvtabu <br/>";
//	}
//	else
//	{
//		echo "<br/>";
//		echo "OBS! Inga poster uppdaterades i tabellen, kolla varför! <br/>";
//	}
//
}
else
{
	echo "<br/>";
	echo "Filen ".$filename." saknas, programmet avbryts <br/>";
}
?>
<?PHP
/*
Programmet skall läsa av sour tabellen som laddats med program 
konvtabi och uppdatera texterna på sin "rätta" plats. 

Innan programmet körs skall programmen konvtabb och konvtabi vara körda.
*/
echo "<br/>";
echo "Program konvtabux startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
$fileut=$directory . "RGDV2.GED";
//
/*
if(file_exists($fileut))
{
	echo "<br/>";
	echo $fileut." finns redan, programmet avbruts<br/>";
}
else
{*/
	$filename=$directory . "RGDV.GED";
	$subloop = 'NEJ';
	$rad=0;
	if(file_exists($filename))
	{
//		echo $filename." finns<br/>";
//		echo "$filename har storleken ".filesize($filename)."<br/>";
		$handle=fopen($filename,"r");
		$handut=fopen($fileut,"w");
//	Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
//	Första individ/relation börjar	
//			if($head == 'OFF')
//			{
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
				$sekv = 0;
				$tgin=substr($str,0,1);
				$tagg=substr($str,0,8);
				if(($tagg == "2 SOUR @") || ($tagg == "1 NOTE @") || 
				($tagg == "1 SUBM @") || ($tagg == "1 REPO @") ||
				($tagg == "1 REPO @") || ($tagg == "3 REPO @") || 
				($tagg == "1 OBJE @") || ($tagg == "2 OBJE @") || 
				($tagg == "3 OBJE @"))
				{
					$taggxx = $tagg;
					$imax=8;
					$id='';
					$temp="";
					$textin="tom";
					while($imax < 28)
					{
						$test=substr($str,$imax,1);
						$imax++;
						if($test == "@")
						{
							$rad++;
							$test=substr($str,2,4);
							$id=$test.$temp;
							$tagg="tom";
							$imax=28;
						}
						else
						{
							$temp=$temp.$test;
						}
					}
					if($sekv == 0) {
						$sekv++;
						$SQL="SELECT text FROM sour WHERE(id='$id' AND sekv=$sekv)";
						$result=mysql_query($SQL);
//						echo "Result = ".$result,"<br/>";
						if($result)
						{
							$row=mysql_fetch_assoc($result);
							$textin=$row['text'];"<br/>";
						}	
						$tgtp = substr($textin,2,4);
						if(($taggxx == '2 SOUR @') && ($tgtp != 'SOUR')){
							$taggrad = '2 SOUR  ';
							fwrite($handut,$taggrad."\r\n");
						}
						if(($taggxx == '1 NOTE @') && ($tgtp != 'NOTE')){
							$taggrad = '1 NOTE  ';
							fwrite($handut,$taggrad."\r\n");
						}
						if(($taggxx == '1 SUBM @') && ($tgtp != 'SUBM')){
							$taggrad = '1 SUBM  ';
							fwrite($handut,$taggrad."\r\n");
//	Om SUBM inte skrivs		$tgin = 0;
						}
						$len=strlen($textin);
						$tgut = substr($textin,0,1);
						$tgnr = $tgin + $tgut;
						$tgtx = substr($textin,1,($len-1));
						$textin = $tgnr.$tgtx;
						$len=strlen($textin);
						if($len > 7) {
							fwrite($handut,$textin."\r\n");
						}	
					}
					while($sekv < 9999)
					{
						$sekv++;
						$SQL="SELECT text FROM sour WHERE(id='$id' AND sekv=$sekv)";
						$result=mysql_query($SQL);
//						echo "Result = ".$result,"<br/>";
						if($result)
						{
							$row=mysql_fetch_assoc($result);
							$textin=$row['text'];"<br/>";
							if($textin != "")
							{
								$len=strlen($textin);
								$tgut = substr($textin,0,1);
								$tgnr = $tgin + $tgut;
								$tgtx = substr($textin,1,($len-1));
								$textin = $tgnr.$tgtx;
								fwrite($handut,$textin."\r\n");
								$tsub=substr($textin,2,7);
								if(($tsub == "SOUR @S") || ($tsub == "NOTE @N") ||
								($tsub == "REPO @R") || ($tsub == "OBJE @M")) {
									if($subloop == 'NEJ') {
										echo "<br/>";
										echo "Substruktur hittad. <br/>";
										$subloop = 'JA';
									}	
								}
							}
							else
							{
								$sekv=9999;
							}	
						}
						else
						{
							$sekv=9999;
//							echo $SQL."fungerande inte".mysql_error();
						}
					}	
				}
				else
				{
//	Kolla om PHOTO alltid registreras dubbelt
					$foto=substr($str,2,6);
					if($foto == "_PHOTO") {
						$str = substr($str,0,8);
					}
					fwrite($handut,$str."\r\n");
				}
//			}
		}
		fclose($handle);
		fclose($handut);
////////		
		if($subloop == 'JA') {
		echo "Extra loop startad <br/>";
//	Nästlade källor fanns		
		$fileut=$directory . "RGD1.GED";
		$filename=$directory . "RGDV2.GED";
//
		$handle=fopen($filename,"r");
		$handut=fopen($fileut,"w");
//	Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
//	Första individ/relation börjar	
//			if($head == 'OFF')
//			{
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
				$sekv = 0;
				$tgin=substr($str,0,1);
				$tagg=substr($str,2,6);
				if(($tagg == "SOUR @") || ($tagg == "NOTE @") || 
				($tagg == "SUBM @") || ($tagg == "REPO @") || 
				($tagg == "OBJE @"))
				{
					$taggxx = $tagg;
					$imax=8;
					$id='';
					$temp="";
					$textin="tom";
					while($imax < 28)
					{
						$test=substr($str,$imax,1);
						$imax++;
						if($test == "@")
						{
							$rad++;
							$test=substr($str,2,4);
							$id=$test.$temp;
							$tagg="tom";
							$imax=28;
						}
						else
						{
							$temp=$temp.$test;
						}
					}
					if($sekv == 0) {
						$sekv++;
						$SQL="SELECT text FROM sour WHERE(id='$id' AND sekv=$sekv)";
						$result=mysql_query($SQL);
//						echo "Result = ".$result,"<br/>";
						if($result)
						{
							$row=mysql_fetch_assoc($result);
							$textin=$row['text'];"<br/>";
						}	
						$tgtp = substr($textin,2,4);
						if(($taggxx == 'SOUR @') && ($tgtp != 'SOUR')){
							$taggrad = $tgin.' SOUR  ';
							fwrite($handut,$taggrad."\r\n");
						}
						if(($taggxx == 'NOTE @') && ($tgtp != 'NOTE')){
							$taggrad = $tgin.' NOTE  ';
							fwrite($handut,$taggrad."\r\n");
						}
						$len=strlen($textin);
						$tgut = substr($textin,0,1);
						$tgnr = $tgin + $tgut;
						$tgtx = substr($textin,1,($len-1));
						$textin = $tgnr.$tgtx;
						fwrite($handut,$textin."\r\n");
					}
					while($sekv < 9999)
					{
						$sekv++;
						$SQL="SELECT text FROM sour WHERE(id='$id' AND sekv=$sekv)";
						$result=mysql_query($SQL);
//						echo "Result = ".$result,"<br/>";
						if($result)
						{
							$row=mysql_fetch_assoc($result);
							$textin=$row['text'];"<br/>";
							if($textin != "")
							{
								$len=strlen($textin);
								$tgut = substr($textin,0,1);
								$tgnr = $tgin + $tgut;
								$tgtx = substr($textin,1,($len-1));
								$textin = $tgnr.$tgtx;
								fwrite($handut,$textin."\r\n");
							}
							else
							{
								$sekv=9999;
							}	
						}
						else
						{
							$sekv=9999;
//							echo $SQL."fungerande inte".mysql_error();
						}
					}	
				}
				else
				{
					fwrite($handut,$str."\r\n");
				}
//			}
		}
		fclose($handle);
		fclose($handut);
//			
		}
		else {
			$fileut=$directory . "RGD1.GED";
			$filename=$directory . "RGDV2.GED";
			$result=copy($filename,$fileut);
			if($result == false) {
				echo "OBS! Namnbytet misslyckades. <br/>";
				echo "<br/>";
			}
		}
//		echo "<br/>";
		echo "Program konvtabux avslutad <br/>";
		echo "<br/>";
//		echo $rad." kompletterade poster i " .$fileut."<br/>";
//		echo "<br/>";
//		echo "Programmet klart <br/>";
//		echo "<br/>";
//		echo "Filen ".$fileut." har skapats.<br/>";
//		echo "Konverteringen från strukturerade källor är klar. <br/>";
//
		$result = mysql_query("UNLOCK TABLES");
		if(!$result)
		{
		  echo "UNLOCK av sour fungerande inte".mysql_error();
		}
//
	}
	else
	{
		"Filen ".$filename." saknas, programmet avbröts <br/>";
	}
//}
?>
<?PHP
/*
Programmet kan "rätta" till ordningsföljden för tex. Min Släkts GEDCOM fil.
Byter plats på SEX-NAME och MARR-HUSB/WIFE.

Läsprogrammet testordn visar om det finns poster i fel ordning.

*/
echo "<br/>";
echo "Program konvordnx startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDY.GED";
//
//echo "<br/>";
//$filexx=$directory . "RGD1_bup.GED";
//$result=copy($fileut,$filexx);
//if($result == false) {
//	echo "OBS! Filkopieringen misslyckades. <br/>";
//	echo "<br/>";
//}
//	else {
//		echo "Filkopiering gick bra. ".$filexx." har skapats <br/>";
//		echo "<br/>";
//	}	
//
//echo "<br/>";
if(file_exists($fileut))
{
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//	}	
}
//
$wrant=0;
$w1ant=0;
$w2ant=0;
$spar1='NEJ';
$spar2='NEJ';
$wname='';
$wwname='';
$wsex='';
$wpar='';
$wmar='';
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	$rad=0;
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
		$head="ON";
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
						if($ztal == '@') {
							if((substr($str,$zmax,5) == '@ IND') || (substr($str,$zmax,5) == '@ FAM')) {
								$head = 'OFF';
//								echo "Första individen/relationen = ".$str." <br/>";
							}
							$zmax = $zlen; 
						}	
						$zmax++;
					}
				}	
				if($head == 'ON') {
					fwrite($handut,$str."\r\n");
				}	
			}
//	Första individ/relation börjar	
			if($head == 'OFF')
			{
//	Töm eventuellt sparat data vid postbyte	
				$pos1 = substr($str,0,1);
				$tagg = substr($str,2,4);
				$tag6 = substr($str,0,6);
				if(($tag6 == '1 CHAN') || ($pos1 == '0'))
				{
					if(($wsex != '') && ($wname == '') && ($ztyp == 'IND'))
					{
//						echo "Namn saknas . . . . . . . . . . Id => ".$znum." <br/>";
						fwrite($handut,"1 NAME Saknas /Saknas/\r\n");
						$wsex = '';
					}
					if(($wname != '') && ($wsex == '') && ($ztyp == 'IND'))
					{
//						echo "Kön saknas, sök 1 SEX O (Ändras till M eller F om kön framgår) . . . . . Id => ".$znum." <br/>";
						fwrite($handut,"1 SEX O\r\n");
						$wname = '';
//	Larm
						$larmant++;
						$filelarm=$directory . "LARM_lista.txt";
						$handlarm=fopen($filelarm,"a");
						if($larmrub2 == 1) {
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larm = "L A R M  T Y P  II ";
							fwrite($handlarm,$larm."\r\n");
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larmrub2++;
						}
// sätt om möjligt id och namn
						$larmid = $znum;
						$larmnamn = $lnamn;
						$larm = " ";
						fwrite($handlarm,$larm."\r\n");
// och beskrivande feltext
						$larm = "Individen saknar angiven könstillhörighet - Id => "
						.$larmid." - ".$larmnamn;
						fwrite($handlarm,$larm."\r\n");
						$larm = "* * * Individen kommer därför inte att bearbetas korrekt.";
						fwrite($handlarm,$larm."\r\n");
						$larm = " ";
						fwrite($handlarm,$larm."\r\n");
						fclose($handlarm);
//
					}
				}
				if($pos1 == '0')
				{
//	Skriv sparade rader	
					if($wrant > 0) {
						for($i=0;$i<$wrant;$i++)
						{
							$wstr = $wwstr[$i];
							fwrite($handut,$wstr."\r\n");
						}	
					}
					$wrant = 0;
					if($w1ant > 0) {
						for($i=0;$i<$w1ant;$i++)
						{
							$wstr = $w1str[$i];
							fwrite($handut,$wstr."\r\n");
						}	
					}
					$w1ant = 0;
					if($w2ant > 0) {
						for($i=0;$i<$w2ant;$i++)
						{
							$wstr = $w2str[$i];
							fwrite($handut,$wstr."\r\n");
						}	
					}
					$w2ant = 0;
//					
					$spar1 = 'NEJ';
					$spar2 = 'NEJ';
				}
//	hitta idnummer för individ/relation
				$ztag = substr($str,0,3);
				if($ztag == '0 @') {
					$wrant=0;
					$wname='';
					$wwname='';
					$wsex='';
//
					$znum = '';
					$ztyp = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal != '@') {
							$znum = $znum.$ztal;
						}
						else {
							$ztyp = substr($str,$zmax+2,3);
							$zmax = $zlen;
							$zmax++;
						}
						$zmax++;
					}
				}
//	
				if(($ztyp == 'IND') || ($ztyp == 'FAM')) {
//	Om dubbla namnblock förekommer väljs det första förekomsten
					if(($tag6 == '1 NAME') && ($ztyp == 'IND'))
					{	
						$zlen = strlen($str);
						if($zlen <= 7) {
							$str = "1 NAME Saknas /Saknas/";
						}
						$zlen = strlen($str);
						$zant = 0;
						$sant = 0;
						while($zant < $zlen) {
							$ztkn = substr($str,$zant,1);
							if($ztkn == '/') {
								$sant++;
							}
							$zant++;
						}
//	Rätta formellt felaktiga						
						if($sant == 0) {
							$str = $str." //";
						}
						if($sant == 1) {
							$str = $str."/";
							$sant++;
							$sant++;
						}
/*	Inte rätta här 		if($sant > 2) {
							$zlen = strlen($str);
							$zant = ($zlen-1);
							$sant = 0;
							$ztmp = '';
							while($zant >= 0) {
								$ztkn = substr($str,$zant,1);
								$ztk2 = substr($str,($zant-1),1);
								if($ztkn == '/') {
									$sant++;
									if($sant == 1) {
										$ztmp = $ztkn.$ztmp;
									}
									elseif($sant == 2) {
										if($ztk2 != ' ') {
											$ztmp = ' '.$ztkn.$ztmp;
										}
										else {
											$ztmp = $ztkn.$ztmp;
										}
									}
									elseif($sant >= 3) {
//	Skippa / tecken
										if($ztk2 != ' ') {
											$ztmp = ' '.$ztmp;
										}
									}
									else {
										echo "Detta skall inte kunna inträffa <br/>";
										$ztmp = $ztkn.$ztmp;
									}
								}	
								else {
									$ztmp = $ztkn.$ztmp;
								}
								$zant--;
							}
							$str = $ztmp;
						}*/
						if($wwname == '') {
							$wname=$str;
							$wwname=$str;
							$llen = strlen($str);
							$lnamn = substr($str,6,$llen);
						}
					}
					elseif($tagg == 'SEX ')
					{	
						$wsex=$str;
						$wwsex=substr($wsex,6,1);
						if(($wwsex != 'M') && ($wwsex != 'F')) {
							echo 'OBS! Kön "'.$wwsex.'" felaktigt, sök SEX O och 
							försök rätta till M eller F  . . . . . Id => '.$znum.' <br/>';
							$str = '1 SEX O';
							$wsex = $str;
						}
					}
					elseif($tagg == 'MARR')
					{
						$spar1 = 'JA';
						$spar2 = 'NEJ';
					}
					elseif($tagg == 'MARB')
					{
						$spar1 = 'JA';
						$spar2 = 'NEJ';
					}
					elseif($tagg == 'HUSB')
					{
						$spar2 = 'JA';
						$spar1 = 'NEJ';
					}
					elseif($tagg == 'WIFE')
					{
						$spar2 = 'JA';
						$spar1 = 'NEJ';
					}
					elseif($tagg == 'CHIL')
					{
						$spar2 = 'JA';
						$spar1 = 'NEJ';
					}
					else
					{
//	ej aktivt				
					}
//	Bearbeta
					if(($wname != '') && ($wsex == ''))
					{
//	Spara rader	
						$wwstr[$wrant]=$str;
						$wrant++;
					}
//				
					elseif($spar1 == 'JA')
					{	
//	Spara rader	
						$w1str[$w1ant]=$str;
						$w1ant++;
					}	
//
					elseif($spar2 == 'JA')
					{	
//	Spara rader	
						$w2str[$w2ant]=$str;
						$w2ant++;
					}
//				
					elseif(($wname != '') && ($wsex != ''))
					{
//	Skriv aktuell rad	
						fwrite($handut,$str."\r\n");
//	Skriv sparade rader	
						if($wrant > 0) {
							for($i=0;$i<$wrant;$i++)
							{
								$wstr = $wwstr[$i];
								fwrite($handut,$wstr."\r\n");
							}	
						}
						$wrant = 0;
						$wsex = '';
						$wname= '';
					}
//					
					elseif($tag6 == '1 CHAN')
					{
//	Skriv sparade rader	
						if($wrant > 0) {
							for($i=0;$i<$wrant;$i++)
							{
								$wstr = $wwstr[$i];
								fwrite($handut,$wstr."\r\n");
							}	
						}
						$wrant = 0;
						if($w1ant > 0) {
							for($i=0;$i<$w1ant;$i++)
							{
								$wstr = $w1str[$i];
								fwrite($handut,$wstr."\r\n");
							}	
						}
						$w1ant = 0;
						if($w2ant > 0) {
							for($i=0;$i<$w2ant;$i++)
							{
								$wstr = $w2str[$i];
								fwrite($handut,$wstr."\r\n");
							}	
						}
						$w2ant = 0;
//
						$spar1 = 'NEJ';
						$spar2 = 'NEJ';
//	Skriv aktuell rad	
						fwrite($handut,$str."\r\n");
					}
//	ingen bearbetning
					else
					{
						fwrite($handut,$str."\r\n");
					}
				}	
//	inte IND/FAM				
				else
				{
					fwrite($handut,$str."\r\n");
				}
			}
		}
//		echo "<br/n>";
		echo "Program konvordnx avslutad <br/n>";
		echo "<br/n>";
//		echo $fileut." har skapats <br/n>";
		fclose($handin);
		fclose($handut);
//
	}
	else
	{
		echo "Filen ".$filein." saknades, programmet avbröts <br/>";
	}
}
?>
<?PHP
/*
Programmet justerar vid behov texten på taggen PLAC
för att underlätta maskinell tolkning av församlingar.

Teckenrättning:
Borttag av tecknen mellanslag, komma eller punkt i slutet av raden
Borttag av inledande mellanslag i början av raden
Borttag av mellanslag före komma eller punkt
Borttag av upprepade mellanslag i raden
Borttag av upprepade kommatecken i raden
Borttag av upprepare punkter i raden
Borttag av mellanslag inuti parenteser
Tillägg av mellanslag efter komma och punkt om det saknas
Tillägg av mellanslag före parentes och slash om det saknas

*/
echo "<br/>";
echo "Program konvtextx startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDJ.GED";
//
//echo "<br/>";
//$filexx=$directory . "RGD1_bup.GED";
//$result=copy($fileut,$filexx);
//if($result == false) {
//	echo "OBS! Filkopieringen misslyckades. <br/>";
//	echo "<br/>";
//}
//	else {
//		echo "Filkopiering gick bra. ".$filexx." har skapats <br/>";
//		echo "<br/>";
//	}	
//
//echo "<br/>";
$result=rename($fileut,$filein);
if($result == false) {
	echo "OBS! Filkopieringen misslyckades. <br/>";
	echo "<br/>";
}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//	}	
//
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
//		echo "<br/>";
		$handin=fopen($filein,"r");
  		$handut=fopen($fileut,"w");
		$qant=0;
		$fixant=0;
		$akt='NEJ';
		$head="ON";
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
//								echo "Första individen/relationen = ".$str." <br/>";
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
				$pos1=substr($str,0,1);
				$tagk=substr($str,0,5);
				$tagg=substr($str,0,6);
				if(($pos1 == '0') || ($pos1 == '1'))
				{
					$akt = 'NEJ';
				}
				if($tagg == '1 NAME')
				{
					$llen = strlen($str);
					$lnamn = substr($str,6,$llen);
				}
				if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
				{
					$akt = 'JA';
				}
				if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
				{
					$akt = 'JA';
				}
				if($tagg == '1 MARR')
				{
					$akt = 'JA';
				}
				if(($tagg == '2 PLAC') && ($akt == 'JA'))
				{
//	Sverige anses default 
					$len=strlen($str);
					$sist=substr($str,(strlen($str)-9),9);
					if($sist == ', Sverige')
					{
						$str=substr($str,0,(strlen($str)-9));
						$qant++;
					}
					$sist=substr($str,(strlen($str)-8),8);
					if(($sist == ' Sverige') || ($sist == ', Sweden'))
					{
						$str=substr($str,0,(strlen($str)-8));
						$qant++;
					}
					$sist=substr($str,(strlen($str)-7),7);
					if($sist == ' Sweden')
					{
						$str=substr($str,0,(strlen($str)-7));
						$qant++;
					}
//				
//	Rensa förstatecken och sistatecken på PLAC rader
//	OBS! Om $str används rensas också taggen PLAC när den återskrivs
					$len=strlen($str);
					$halv1 = substr($str,0,7);
					$halv2 = substr($str,7,($len-7));
					$amax=0;
					while($amax <= (strlen($halv2)))
					{
						$tom=substr($halv2,$amax,1);
						if(($tom == ' ') || ($tom == '.') || ($tom == ','))
						{
//							echo "* * * * * * * * * * /".$str."/ <br/>";
							$halv2=substr($halv2,1,(strlen($halv2)-1));
							$amax++;
							$qant++;
						}
						else
						{
							$amax=$len;
							$amax++;
						}
					}
					$str = $halv1.$halv2;
//	och slutet				
					$len=strlen($str);
					$amax=$len;
					while($amax > 0)
					{
						$sist=substr($str,(strlen($str)-1),1);
						if(($sist == ' ') || ($sist == '.') || ($sist == ','))
						{
//							echo "* * * * * * * * * * /".$str."/ <br/>";
							$str=substr($str,0,(strlen($str)-1));
							$amax--;
							$qant++;
						}
						else
						{
							$amax=0;
						}
					}
//	och sen mitten också				
					$len=strlen($str);
					$amax=0;
					$pant=0;
					$pcnt=0;
					$kant=0;
					$sist = '';
					$spar = '';
					$plus = '';
					while($amax <= $len)
					{
						if($pcnt > 0) {
							$pcnt++;
						}
						$tom=substr($str,$amax,1);
						if(($tom == ' ') || ($tom == '.') || ($tom == ',')
						|| ($tom == '(') || ($tom == ')') || ($tom == '/'))
						{
							if(($tom == '(') || ($tom == ')'))
							{
								$pant++;
							}
							if($tom == '(')
							{
								$pcnt++;
							}
							if($tom == ')')
							{
								if($pcnt <= 4)
								{
//	en vanlig församling eller land								
									$pcnt = 0;
								}	
							}
//	Borttag av , före (							
							if($tom == ',')
							{
								$pls1=substr($str,($amax+1),1);
								$pls2=substr($str,($amax+1),2);
								if($pls1 == '(') {
									$tom = ' ';
								}
								if($pls2 == ' (') {
									$tom = '';
								}
							}
//							if($tom == ',')
//							{
//Kanske fanns en tanke bakom?	if($kant > 1)
//								$kant++;
//								{
//									$tom = ' ';
//								}
//							}
//	Tillägg av mellanslag före parentes					
							if(($sist != ' ') && ($tom == '('))
							{
								$spar = $spar.' ';
								$sist = '';
							}
//	Tillägg av komma före slash				
/*							if(($sist != ' ') && ($tom == '/') && ($kant == 0))
							{
								$spar = $spar.',';
								$sist = '';
							}*/
//	Tillägg av mellanslag efter komma							
							if((($tom == ',')) && (substr($str,($amax+1),1) != ' '))
							{
								$plus = ' ';
							}
//	Tillägg av mellanslag efter punkt, undantag förkotningspunkt 						
							if((($tom == ',') && ($sist != '.')) && (substr($str,($amax+1),1) != ' '))
							{
								$plus = ' ';
							}
//	Tillägg av komma efter	parentes utom för två parenteser eller lång parentes				
							if(($tom == ')') && ($len > ($amax + 1)))
							{
								if(substr($str,($amax+1),1) != ',')
								{
									$plus = ',';
								}
								if(($plus == ',') && (substr($str,($amax+2),1) == '('))
								{
									$plus = '';
								}
								if(($plus == ',') && ($pcnt >= 5))
								{
									$plus = '';
								}
								if(($plus == ',') && (substr($str,($amax+1),1) != ' '))
								{
									$plus = ', ';
								}
							}
//	Borttag av mellanslag							
							if(($sist == ' ') && ($tom == ','))
							{
								$spar=substr($spar,0,(strlen($spar)-1));
								$sist = '';
							}
							if(($sist == ' ') && ($tom == '.'))
							{
								$spar=substr($spar,0,(strlen($spar)-1));
								$sist = '';
							}
//	Inget av dessa tecken skall vara dubblerade							
							if($sist != $tom)
							{
								$sist = $tom;
								$spar = $spar.$tom.$plus;
								$plus = '';
							}
						}
						else
						{
							$sist = $tom;
							$spar = $spar.$tom;
						}
						$amax++;
						$sist = $tom;
					}
					if(($pant == 1) || ($pant == 3) || ($pant == 5) || ($pant == 7))
					{
						echo "> > ".$str." - Udda antal parenteser . . . . . . . . . . Id => "
						.$znum." - ".$lnamn." <br/>"; 
						$pant = 0;
//	Larm
						$larmant++;
						$filelarm=$directory . "LARM_lista.txt";
						$handlarm=fopen($filelarm,"a");
						if($larmrub3 == 1) {
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larm = "L A R M  T Y P  III ";
							fwrite($handlarm,$larm."\r\n");
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larmrub3++;
						}
// sätt om möjligt id och namn
						$larmid = $znum;
						$larmnamn = $lnamn;
						$larm = " ";
						fwrite($handlarm,$larm."\r\n");
// och beskrivande feltext
						$larm = "Udda antal parenteser - ".$str.
						" - Id => ".$larmid." - ".$larmnamn;
						fwrite($handlarm,$larm."\r\n");
						$larm = "* * * Orten kan därför feltolkas i bearbetningen.";
						fwrite($handlarm,$larm."\r\n");
						$larm = " ";
						fwrite($handlarm,$larm."\r\n");
						fclose($handlarm);
//
					}
					if($str != $spar)
					{
						$str = $spar;
						$qant++;
					}	
//					
					$len=strlen($str);
					$bmax=$len;
					if($len < 8)
					{
//							echo "* * * * * * * * * * /".$str.
//							"/ saknade text . . . . . . . . . . Id => ".$znum." <br/>";
//							$qant++;
					}
				}
			}
			fwrite($handut,$str."\r\n");
		}
/*		if($qant == 0)
		{
			echo "<br/n>";
			echo "Program konvtext avslutat. <br/n>";
			echo "<br/n>";
			echo "Filen ".$fileut." har skapats. <br/n>";
			echo "<br/n>";
		}
		else
		{
			echo "<br/n>";
			echo "Program konvtext avslutat. <br/n>";
			echo "<br/n>";
			echo "Programmet har justerat tecken i ".$qant." rader av taggen PLAC. <br/n>";
			echo "<br/n>";
		}
		if($fixant > 0)
		{
			echo "<br/n>";
			echo "OBS! <br/n>";
			if($fixant > 1) {
				echo "OBS! ".$fixant." asterisk markerade rader måste åtgärdas i RGD1 <br/>";  
			}
			else {
				echo "OBS! ".$fixant." asterisk markerad rad måste åtgärdas i RGD1 <br/>";  
			}
			echo "OBS! <br/n>";
		}*/
		echo "Program konvtextx avslutad <br/n>";
		echo "<br/n>";
		fclose($handin);
		fclose($handut);
//
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts. <br/>";
	}
?>
<?PHP
/*
Programmet konverterar och byter ut län i klartext och numerisk länskod 
till länsbokstav inom parentes.
Efterföljande program klarar inte bådeock, så de ersätts helt.

Programmet förutsätter att numerisk länskod är inramad i parentes eller slash.
Programmet förutsätter att län i klartext avgränsas med komma och ordet län ingår.
Exempel: Madesjö, Kalmar län
Exempel: (08) eller /08/ OBS! att inledande nollor måste finnas med.

*/
//
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDI.GED";
//
echo "<br/>";
echo "Program konvlkodx startad <br/>";
//
//echo "<br/>";
if(file_exists($fileut))
{
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//}	
}
//
	$rads=0;
//	$radz=0;
//
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
//		echo "<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
			$txtu = '';
			$tkn4 = '';
			$spar = '';
			$lfix = '';
			$strt = 'NEJ';
			$posx = 0;
//
			$pos1=substr($str,0,1);
			$tagk=substr($str,0,5);
			$tagg=substr($str,0,6);
			if(($pos1 == '0') || ($pos1 == '1'))
			{
				$akt = 'NEJ';
			}
			if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
			{
				$akt = 'JA';
			}
			if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
			{
				$akt = 'JA';
			}
			if($tagg == '1 MARR')
			{
				$akt = 'JA';
			}
			if(($tagg == '2 PLAC') && ($akt == 'JA'))
			{
//	Testa numerisk länskod	
				$slen = strlen($str);
				$bmax = $slen-1;
				while($bmax > 0)
				{
					$tkn1=substr($str,$bmax,1);
					if(($tkn1 == ")") || ($tkn1 == "/")) {
						$tkn4 = substr($str,$bmax-3,1);
						$tkn3 = substr($str,$bmax-2,1);
						$tkn2 = substr($str,$bmax-1,1);
						if(($tkn4 == "(") || ($tkn4 == "/")) {
							if(($tkn3 >= '0') && ($tkn3 <= '9')
							&& ($tkn2 >= '0') && ($tkn2 <= '9')) {
								$spar = '('.$tkn3.$tkn2.')';
								$posx = $bmax - 3;
							}
						}
					}
					$bmax--;
				}
//				
				if($posx == 0) {	
//	Testa län i inramad textform
					$slen = strlen($str);
					$bmax = $slen;
					$strt = 'NEJ';
					$tant = 0;
					while($bmax > 0)
					{
						$tkn1=substr($str,$bmax-1,1);
						if(($tkn1 == ")") || ($tkn1 == "/")) {
							$strt = 'JA';
						}	
						if(($trt = 'JA') && (($tkn1 == "(") || ($tkn1 == "/"))) {
							$strt = 'NEJ';
							$spar = $tkn1.$spar;
							$posx = $bmax;
							if($tant < 5) {
								$posx = 0;
								$spar = '';
							}
						}	
						if($strt == 'JA') {
							$spar = $tkn1.$spar;
							$tant++;
						}
						$bmax--;
					}
				}
//				
				if($posx == 0) {	
//	Testa "län"			 
					$slen = strlen($str);
					$bmax = $slen-1;
					$strt = 'NEJ';
					while($bmax > 0)
					{
						$tkn1=substr($str,$bmax,1);
						if($tkn1 == " ") {
							$tkn4 = substr($str,$bmax,5);
							if(($tkn4 == " län") || ($tkn4 == " Län")) {
								$strt = 'JA';
								$spar = 'län';
							}
							else {
								$tkn2 = substr($str,($bmax-1),1);
								if($tkn2 == ',') {
									if($strt == 'JA') {
										$posx = $bmax-1;
									}	
									$strt = 'NEJ';
								}
							}
						}
						if($strt == 'JA') {
							$spar = $tkn1.$spar;
						}
						$bmax--;
					}
				}
//	Korta ner spar
				$orglen = strlen($spar);
				if($orglen > 4) {
					$slen = strlen($str);
					$xlen = strlen($spar);
					$bmax = 0;
					$stemp = '';
					while($bmax < $xlen)
					{
						$tkn1=substr($spar,$bmax,1);
						$tkn4=substr($spar,$bmax,5);
						$tkn5=substr($spar,$bmax,6);
						$tkn6=substr($spar,$bmax,7);
						if(($tkn6 == "s län)") || ($tkn6 == "s Län)")) {
							$bmax = $bmax+6;
						}	
						elseif(($tkn5 == "s län") || ($tkn5 == "s Län")) {
							$bmax = $bmax+5;
						}
						elseif(($tkn4 == " län") || ($tkn4 == " Län")) {
							$bmax = $bmax+4;
						}
						elseif(($tkn1 == ")") || ($tkn1 == "/")) {
//	Hoppa över
						}
						elseif($tkn1 == "(") {
							$posx--;
						}
						else {
							$stemp = $stemp.$tkn1;
						}
						$bmax++;
					}
					$spar = $stemp;
				}
//				
				if($posx > 0) {	
//				 
					if(($spar == 'Stockholm') || ($spar == '(01)')) {
						$lfix = '(AB)'; }
					elseif(($spar == 'Stockholm') || ($spar == '(02)')) {
						$lfix = '(AB)'; }
					elseif(($spar == 'Västerbotten') || ($spar == '(24)')) {
						$lfix = '(AC)'; }
					elseif(($spar == 'Norrbotten') || ($spar == '(25)')) {
						$lfix = '(BD)'; }
					elseif(($spar == 'Uppsala') || ($spar == '(03)')) {
						$lfix = '(C)'; }
					elseif(($spar == 'Södermanland') || ($spar == '(04)')) {
						$lfix = '(D)'; }
					elseif(($spar == 'Östergötland') || ($spar == '(05)')) {
						$lfix = '(E)'; }
					elseif(($spar == 'Jönköping') || ($spar == '(06)')) {
						$lfix = '(F)'; }
					elseif(($spar == 'Kronoberg') || ($spar == '(07)')) {
						$lfix = '(G)'; }
					elseif(($spar == 'Kalmar') || ($spar == '(08)')) {
						$lfix = '(H)'; }
					elseif($spar == 'Öland') {
						$lfix = '(H)'; }
					elseif(($spar == 'Gotland') || ($spar == '(09)')) {
						$lfix = '(I)'; }
					elseif(($spar == 'Blekinge') || ($spar == '(10)')) {
						$lfix = '(K)'; }
					elseif(($spar == 'Kristianstad') || ($spar == '(11)')) {
						$lfix = '(L)'; }
					elseif(($spar == 'Malmöhu') || ($spar == '(12)')) {
						$lfix = '(M)'; }
					elseif($spar == 'Skåne') {
						$lfix = '(M)'; }
					elseif(($spar == 'Halland') || ($spar == '(13)')) {
						$lfix = '(N)'; }
					elseif(($spar == 'Göteborgs och Bohu') || ($spar == '(14)')) {
						$lfix = '(O)'; }
					elseif($spar == 'Göteborgs- och Bohu') {
						$lfix = '(O)'; }
					elseif($spar == 'Göteborgs & Bohu') {
						$lfix = '(O)'; }
					elseif($spar == 'Västra Götaland') {
						$lfix = '(O)'; }
					elseif(($spar == 'Älvsborg') || ($spar == '(15)')) {
						$lfix = '(P)'; }
					elseif(($spar == 'Skaraborg') || ($spar == '(16)')) {
						$lfix = '(R)'; }
					elseif(($spar == 'Värmland') || ($spar == '(17)')) {
						$lfix = '(S)'; }
					elseif(($spar == 'Örebro') || ($spar == '(18)')) {
						$lfix = '(T)'; }
					elseif(($spar == 'Västmanland') || ($spar == '(19)')) {
						$lfix = '(U)'; }
					elseif(($spar == 'Kopparberg') || ($spar == '(20)')) {
						$lfix = '(W)'; }
					elseif($spar == 'Dalarna') {
						$lfix = '(W)'; }
					elseif(($spar == 'Gävleborg') || ($spar == '(21)')) {
						$lfix = '(X)'; }
					elseif(($spar == 'Västernorrland') || ($spar == '(22)')) {
						$lfix = '(Y)'; }
					elseif($spar == 'Medelpad') {
						$lfix = '(Y)'; }
					elseif(($spar == 'Jämtland') || ($spar == '(23)')) {
						$lfix = '(Z)'; }
					else {
						$lfix = '';
//						echo '*** Oidentifierat län = => ('.$spar.')<br/>';
//						$radz++;
					}
					$sparlen = strlen($spar);
				}	
//	Undvik både rätt länsbokstav och län i textform
				if($lfix != '') {
					$slen = strlen($str);
					$xlen = strlen($lfix);
					$zlen = strlen($spar);
					$bmax = 0;
					$dubb = '';
					while($bmax < $slen)
					{
						$tkn1=substr($str,$bmax,1);
						$tknx=substr($str,$bmax,$xlen);
						if($tknx == $lfix) {
							$dubb = 'JA';
							$bmax = $bmax + $xlen + 1;
						}
						else {
							$txtu = $txtu.$tkn1;
						}
						$bmax++;
					}
					if($dubb == '') {
						$txtu = '';
					}
					else {
//						echo '1 Ändrad från - - - '.$str.' - - - => 
//						till => - - - '.$txtu.'<br/>'; 
						$rads++;
						$str = $txtu;
						$txtu = '';
					}
				}	
//	Något av alternativen skall uppdateras
				if($lfix != '') {
					$slen = strlen($str);
					$xlen = strlen($spar);
					$bmax = 0;
					while($bmax < $slen)
					{
						$tkn1=substr($str,$bmax,1);
						if($tkn1 == ',') {
							if((($bmax+2) >= $posx) && ($xlen > 4)) {
								$txtu = $txtu.' '.$lfix;
								$posx = 999;
								$bmax = ($bmax + $orglen + 1);
//								$rads++;
							}
							else {
								$txtu = $txtu.$tkn1;
							}
						}	
						elseif(($tkn1 == '(') || ($tkn1 == '/')) {
							if((($bmax+1) >= $posx) && ($xlen > 4)){
								$txtu = $txtu.$lfix;
								$posx = 999;
								$bmax = (($bmax-1) + $orglen);
							}
							elseif((($bmax+1) >= $posx) && ($xlen == 4)){
								$txtu = $txtu.$lfix;
								$posx = 999;
								$bmax = (($bmax-1) + $xlen);
							}
							else {
								$txtu = $txtu.$tkn1;
							}
						}
						else {
							$txtu = $txtu.$tkn1;
						}
						$bmax++;
					}
					$rads++;
//					echo '2 Ändrad från - - - '.$str.' - - - => 
//					till => - - - '.$txtu.'<br/>'; 
				}
				else {
					if($txtu == '') {
						$txtu = $str;
					}	
				}	
//	
				fwrite($handut,$txtu."\r\n");
//
			}
			else {
				fwrite($handut,$str."\r\n");
			}	
		}
//	
		fclose($handin);
		fclose($handut);
//		
//		echo "<br/>";
//		echo $rads." justeringar gjorda. <br/>";
// 		echo "<br/>";
//		if($radz > 0) {
//			echo "<br/>";
//			echo $radz." oidentifierade texter, sök ***. <br/>";
//		}
		echo "Program konvlkodx klart <br/>";
		echo "<br/>";
//
	}
	else
	{
		echo $filein." saknas, programmet avslutas.<br/>";
	}
?>
<?PHP
/*
Programmet skall konvertera avvikande inramning (slash) av länskod.
/x/ konverteras till (x)
/x  konverteras till (x)
, x konverteras till (x)

Och flyttar ländkoden till första blocket i PLAC fältet
om kombinationen ger träff i församlingstabellen.

*/
echo "<br/>";
echo "Program konvpslsx startad <br/>";
//
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDM.GED";
//
//echo "<br/>";
if(file_exists($fileut))
{
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//}	
}
//
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	$rads=0;
//
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
//		echo "<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
	//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
//		
			$strx = '';
			$pos1=substr($str,0,1);
			$tagk=substr($str,0,5);
			$tagg=substr($str,0,6);
			if(($pos1 == '0') || ($pos1 == '1'))
			{
				$akt = 'NEJ';
			}
			if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
			{
				$akt = 'JA';
			}
			if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
			{
				$akt = 'JA';
			}
			if($tagg == '1 MARR')
			{
				$akt = 'JA';
			}
			if(($tagg == '2 PLAC') && ($akt == 'JA'))
			{
				$pant = 0;
				$sant = 0;
//	Kolla parenteser
				$len = strlen($str);
				$bmax = 0;
				while($bmax <= $len)
				{
					$tkn=substr($str,($bmax),1);
					if($tkn == '(') {
							$pant++;
					}
					if($tkn == ')') {
							$pant++;
					}
					$bmax++;
				}
//
				if($pant == 0) {
//	Byt slash mot parentes
					$len = strlen($str);
					$bmax = 0;
					while($bmax <= $len)
					{
						$tkn=substr($str,$bmax,1);
//	Inget komma dikt före en parentes
						$tk2=substr($str,($bmax),2);
						if(($tk2 == ',/') || ($tk2 == ',(')){
							$tkn = ' ';
									 }
						if($tkn == "/") {
							if($sant == 1) {
								$strx = $strx.')';
								$sant++;
								$rads++;
							}
							if($sant == 0) {
								$strx = $strx.'(';
								$sant++;
							}
						}	
						else {
							$strx = $strx.$tkn;
						}
						$bmax++;
					}
				}	
//
				if($strx != '') {
					$str = $strx;
					$strx='';
				}
//	Kolla att parentes är komplett
				if($pant == 1) {
					$len = strlen($str);
					$bmax = 0;
					while($bmax <= $len)
					{
						$tkn=substr($str,($bmax),1);
						if($tkn == '(') {
							$lst=substr($str,($bmax-1),1);
							if($lst != ' ') {
								$strx = $strx.' ';
							}
							$strx = $strx.$tkn;
							$tknx = substr($str,($bmax+1),1);
							if(($tknx >= 'A') && ($tknx <= 'Z')) {
								$strx = $strx.$tknx;
								$bmax++;
							}
							$tknx = substr($str,($bmax+1),1);
							if(($tknx >= 'A') && ($tknx <= 'Z')) {
								$strx = $strx.$tknx;
								$bmax++;
							}
							$strx = $strx.')';
							$pant++;
							$rads++;
						}	
						else {
							$strx = $strx.$tkn;
						}
						$bmax++;
					}
				}
//
				if($strx != '') {
					$str = $strx;
					$strx='';
				}
//	Om parentes saknas
				if($pant == 0) {
					$utxt = '';
					$len = strlen($str);
					$bmax = $len;
					while($bmax > 0)
					{
						$lkd=substr($str,($bmax-1),1);
						$tom=substr($str,($bmax-2),1);
						$lnd=substr($str,($bmax-3),2);
						$rst=substr($str,0,($bmax-3));
						if($tom == ' ')
						{
							if($lnd == ', ') {
								$utxt = $rst.' ('.$lkd.')';
							}	
						}	
						else
						{
							$lkd=substr($str,($bmax-2),2);
							$tom=substr($str,($bmax-3),1);
							$lnd=substr($str,($bmax-4),2);
							$rst=substr($str,0,($bmax-4));
							if($tom == ' ')
							{
								if($lnd == ', ') {
									$utxt = $rst.' ('.$lkd.')';
								}	
							}
						}
						$bmax = 0;
						
					}
	//
					if($utxt != '') {
						$str = $utxt;
						$utxt='';
						$rads++;
					}
				}	
//	Avsluta med ev. flytt av länskod
				$ptxt = '';
				$plan = '';
				$utxt = '';
				$len = strlen($str);
				$bmax = $len;
				while($bmax > 0)
				{
					$tkn=substr($str,($bmax-1),1);
					if(($tkn == '(') || ($tkn == ' '))
					{
						if($tkn == ' ') {
							if($ptxt == '') {
//								hoppa över tomma i slutet
							}
							else {
								$ptxt=$tkn.$ptxt;
							}
							$bmax--;
						}	
						if($tkn == '(') {
							$plan = ' ('.$ptxt;
							$ptxt = '';
							$bmax--;
						}	
					}	
					else
					{
						$ptxt=$tkn.$ptxt;
						$bmax--;
					}
				}
//
//
				$len = strlen($ptxt);
				$imax = 7;
				$ftst = '';
				while($imax <= $len)
				{
					$tkn=substr($ptxt,$imax,1);
					if($tkn == ',')
					{
						$ftst = $utxt.$plan;
						$utxt = $utxt.$plan.$tkn;
						$plan = '';
					}
					else
					{
						$utxt = $utxt.$tkn;
					}
					$imax++;
				}
//
				if($ftst != '') {
//
					$tmax = 0;
					$txty = '';
					$tlen = strlen($ftst);
					while($tmax < $tlen) {
						$txtx = substr($ftst,$tmax,1);
						if($txtx != "'") {
							$txty = $txty.$txtx;
						}
						$tmax++;
					}
					$ftst = $txty;
//				
					$noid=0;
					$SQL="SELECT noid FROM foskx WHERE fors='$ftst'";
//					$SQL="SELECT faar,taar,noid FROM foin WHERE fors='$ftst'";
//					$SQL="SELECT faar,taar,noid FROM foxx WHERE fors='$ftst'";
					$result=mysql_query($SQL);
					if(!$result)
					{
						echo $SQL."fungerande inte".mysql_error();
					}
					else
					{
						$row=mysql_fetch_assoc($result);
//						$faar=$row['faar'];
//						$taar=$row['taar'];
						$noid=$row['noid'];
					}	
					if($noid != 0) {
						$str = '2 PLAC '.$utxt.$plan;
						$rads++;
						}
				}	
//
			}
			fwrite($handut,$str."\r\n");
		}
//	
		fclose($handin);
		fclose($handut);
//		
//		echo $rads." justeringar gjorda.<br/>";
		echo "Program konvpslsx klart <br/>";
		echo "<br/>";
//
//
	}
	else
	{
		echo $filein." saknas, programmet avslutas.<br/>";
	}
}	
?>
<?PHP
/*
Programmet kontrollerar om församlingen färegås av by/gård eller
annan tilläggsinformation. Den text, som i så fall ligger före
församlingen läggs istället efter församlingen.

Ordningen skiftas endast för PLAC som följer efter BIRT, CHR, DEAT, BURI och MARR.
Övriga PLAC, tex efter RESI, påverkas inte.

Fungerar nog bra endast på programgenererad text, som tex från Disgen.
Avgörandet om detta program är lämpligt att köra avgörs manuellt.
*/
echo "<br/>";
echo "Program konvortfx startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
$filezz=$directory . "RGDS.GED";
//
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDW.GED";
//
//echo "<br/>";
//$filexx=$directory . "RGD1_bup.GED";
//$result=copy($fileut,$filexx);
//if($result == false) {
//	echo "OBS! Filkopieringen misslyckades. <br/>";
//	echo "<br/>";
//}
//	else {
//		echo "Filkopiering gick bra. ".$filexx." har skapats <br/>";
//		echo "<br/>";
//	}	
//
//echo "<br/>";
if(file_exists($fileut))
{
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//	echo "Filkopiering gick bra. <br/>";
//	echo "<br/>";
//}	
}
//
$len=0;
$znum='';
$text=' ';
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	$rad=0;
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//		echo "<br/>";
		$akt='NEJ';
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
//								echo "Första individen/relationen = ".$str." <br/>";
							}
							$zmax = $zlen; 
						}	
						$zmax++;
					}
				}	
				if($head == 'ON') {
					fwrite($handut,$str."\r\n");
				}	
			}
//	Första individ/relation börjar	
			if($head == 'OFF')
			{
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
				$fors="";
				$ort="";
				$land='';
				$lkod='';
				$len=strlen($str);
				$imax=$len;
				$pos1=substr($str,0,1);
				$tagk=substr($str,0,5);
				$tagg=substr($str,0,6);
				if(($pos1 == '0') || ($pos1 == '1'))
				{
					$akt = 'NEJ';
				}
				if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
				{
					$akt = 'JA';
				}
				if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
				{
					$akt = 'JA';
				}
				if($tagg == '1 MARR')
				{
					$akt = 'JA';
				}
				if(($tagg == '2 PLAC') && ($akt == 'JA'))
				{
//					echo "PLAC = ".$str."<br/>";
					$akt = 'NEJ';
					$tkn=substr($str,($imax-1),1);
					$koll = 'J';
					if($tkn == ')')
					{
						$koll = 'N';
					}
					while($imax > 0)
					{
						$tkn=substr($str,($imax-1),1);
						if(($tkn == ')') && (substr($str,($imax-4),1) == '('))
						{
							$land = substr($str,($imax-3),2);
							if(($land == 'AB') || ($land == 'AC') || ($land == 'BD'))
							{
								$lkod = $land;
								$land = '';
							}
						}
						if(($tkn == ')') && (substr($str,($imax-3),1) == '('))
						{
							$lkod = substr($str,($imax-2),1);
							if(($lkod < 'A') || ($lkod > 'Z'))
							{
								$lkod = '';
							}	
						}
						if($tkn == ',')
						{
							if(substr($fors,0,1) == ' ')	
							{
								$fors=substr($fors,1,(strlen($fors)));
//								echo "Fors = ".$fors." Längd ".(strlen($fors))."<br/>";
							}
							if($len > 0)
							{
								$ort=substr($str,7,($imax-8));
//								echo "Ort = ".$ort."<br/>";
							}
							$imax=0;
						}	
						else
						{
							$fors=$tkn.$fors;
						}
						$imax--;
					}
					if(($ort != '') && ($land == '') && ($lkod != '') && ($koll == 'N'))
					{
						fwrite($handut,"2 PLAC ".$fors.", ".$ort."\r\n");
//						echo $fors.", ".$ort." -  -  -  Åtgärdad . . . . . . . . . . Id => ".$znum." <br/>";
					}
					else // ingen kombination församling - ort
					{
						fwrite($handut,$str."\r\n");
						if($koll == 'J')
						{
//							echo "- - -  >  >  >  ? ? ? ".$ort.
//							", ".$fors." . . . . . . . . . . Id => ".$znum." <br/>";
						}
						else
						{
							if(($ort != '') && ($land == ''))
							{
//								echo "??? ".$ort.", ".$fors." . . . . . . . . . . Id => ".$znum." <br/>";
							}
							else
							{
//								echo "-  >  ".$fors." . . . . . . . . . . Id => ".$znum." <br/>";
							}
						}	
					}
				}
				else // ej PLAC
				{
					fwrite($handut,$str."\r\n");
				}
			}
		}
		fclose($handin);
		fclose($handut);
		//
		if(file_exists($filezz))
		{
			echo "<br/>";
			echo $filezz." fanns redan <br/>";
		}
		else
		{
//			echo "<br/>";
			$result=copy($fileut,$filezz);
			if($result == false) {
				echo "OBS! Filkopieringen misslyckades. <br/>";
				echo "<br/>";
			}
//			else {
//				echo "Filkopiering gick bra. ".$filezz." har skapats <br/>";
//				echo "<br/>";
//			}
		}		
//
//		echo "<br/n>";
		echo "Program konvortfx avslutad <br/n>";
		echo "<br/n>";			
//		echo "Filen ".$fileut." har skapats <br/>";
//
	}
	else
	{
		echo "<br/>";
		echo "Filen ".$filein." saknas, programmet avbröts <br/>";
	}
}
?>
<?PHP
/*
Samma som kompland programmet men kan köras tidigt för att
"få undan" godkända länder från bearbetningslistorna.

Programmet kontrollerar om länder finns i GEDCOM filen 
och läser normeringstabellen land.

Programmet gör uppdatering av RGDP tagg med landsnamnet
från normeringstabellen och normeringsidentiteten.

Programmet letar i första hand efter landets 2-ställiga
kod enligt ISO3166 angivet inom parenteser.
I andra hand landets namn förutsatt raden 
börjar eller slutar med namnet. 

OBS!
Om ingångsfilen manuellt behöver kompletteras blir det enklanst
att ange ISO koden inom parenteser. 
OBS!

*/
echo "<br/>";
echo "Program komplandx startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDL.GED";
//
//echo "<br/>";
//$filexx=$directory . "RGD1_bup.GED";
//$result=copy($fileut,$filexx);
//if($result == false) {
//	echo "OBS! Filkopieringen misslyckades. <br/>";
//	echo "<br/>";
//}
//	else {
//		echo "Filkopiering gick bra. ".$filexx." har skapats <br/>";
//		echo "<br/>";
//	}	
//
//echo "<br/>";
if(file_exists($fileut))
{
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//}	
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
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
//		echo "<br/>";
		$handin=fopen($filein,"r");
  		$handut=fopen($fileut,"w");
		$aaar=0;
		$stop=0;
		$land="";
		$landx="";
		$ltxt="";
		$ltxtx="";
		$akt='NEJ';
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
//								echo "Första individen/relationen = ".$str." <br/>";
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
				$len=strlen($str);
				$pos1=substr($str,0,1);
				$tagk=substr($str,0,5);
				$tagg=substr($str,0,6);
				if(($pos1 == '0') || ($pos1 == '1'))
				{
					$akt = 'NEJ';
				}
				if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
				{
					$akt = 'JA';
				}
				if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
				{
					$akt = 'JA';
				}
				if($tagg == '1 MARR')
				{
					$akt = 'JA';
				}
				if($tagg == '2 RGDP')                 
				{
					$akt = 'NEJ';
				}
				if(($tagg == '2 PLAC') && ($akt == 'JA'))
				{
					$ltxt="";
					$land="";
					$ltxtx="";
					$landx="";
					$imax=7;
					$bmax=$len;
					$akt='NEJ';
					while($bmax > 0)
					{
						$tkn=substr($str,($bmax-1),1);
						if(($tkn == ' ') || ($tkn == ','))
						{
							$ltxtx=$landx;
							$bmax=0;
						}	
						else
						{
							$landx=$tkn.$landx;
							$bmax--;
						}
					}
					while($imax <= $len)
					{
						$tkn=substr($str,$imax,1);
						if(($tkn == ' ') || ($tkn == ','))
						{
							if($ltxt == '')
							{
								$ltxt=$land;
							}	
						}
						if($tkn == '(')
						{
							$land='';
						}
						elseif($tkn == ')')
						{
							$imax=$len;
						}	
						else
						{
							$land=$land.$tkn;
						}
						$imax++;
					}
//	Sök landet i land via landkoden
					$noid="";
					if(($land != "") && (strlen($land) == 2))
					{
//
						$tmax = 0;
						$txty = '';
						$tlen = strlen($land);
						while($tmax < $tlen) {
							$txtx = substr($land,$tmax,1);
							if($txtx != "'") {
								$txty = $txty.$txtx;
							}
							$tmax++;
						}
						$land = $txty;
//				
						$isoland="";
						$SQL="SELECT isoland,noid FROM land WHERE iso2='$land'";
						$result=mysql_query($SQL);
						if(!$result)
						{
							echo $SQL."fungerande inte".mysql_error();
							
						}
						else
						{
							$row=mysql_fetch_assoc($result);
							$isoland=$row['isoland'];
							$noid=$row['noid'];
						}	
						if($noid == "")
						{
							if(($land == 'AB') || ($land == 'AC') || ($land == 'BD'))
							{
//	Tvåställinga länskoder från församling						
							}
							else
							{
//								echo "--->>>".$land.
//								" - saknas som ISO landkod, kolla . . . . . . . . . . Id => ".$znum." <br/>";
							}	
						}
						else
						{
							$rgdp="2 RGDP ".$noid;
//							echo $isoland." - uppdaterad från ISO-landkoden . . . . . . . . . . Id => ".$znum." <br/>";
  							fwrite($handut,$rgdp."\r\n");
						}
					}
					elseif(($land != "") && (strlen($land) == 1))
					{
// 	Församling, testas ej här.						
					}
					else
					{	
						if(($ltxt != "") && (strlen($ltxt) > 2))
						{
//
							$tmax = 0;
							$txty = '';
							$tlen = strlen($ltxt);
							while($tmax < $tlen) {
								$txtx = substr($ltxt,$tmax,1);
								if($txtx != "'") {
									$txty = $txty.$txtx;
								}
								$tmax++;
							}
							$ltxt = $txty;
//				
							$noid="";
							$isoland="";
							$SQL="SELECT isoland,noid FROM land WHERE land='$ltxt'";
							$result=mysql_query($SQL);
							if(!$result)
							{
								echo $SQL." - fungerande inte".mysql_error();
								
							}
							else
							{
								$row=mysql_fetch_assoc($result);
								$isoland=$row['isoland'];
								$noid=$row['noid'];
							}	
//							Nytt försök med land2							
							if($noid == "")
							{
								$isoland="";
								$SQL="SELECT isoland,noid FROM land WHERE land2='$ltxt'";
								$result=mysql_query($SQL);
								if(!$result)
								{
									echo $SQL." - fungerande inte".mysql_error();
									
								}
								else
								{
									$row=mysql_fetch_assoc($result);
									$isoland=$row['isoland'];
									$noid=$row['noid'];
								}	
							}
						}	
//						Nytt försök med ltxtx							
						if($noid == "")
						{
//
							$tmax = 0;
							$txty = '';
							$tlen = strlen($ltxtx);
							while($tmax < $tlen) {
								$txtx = substr($ltxtx,$tmax,1);
								if($txtx != "'") {
									$txty = $txty.$txtx;
								}
								$tmax++;
							}
							$ltxtx = $txty;
//				
							$isoland="";
							$SQL="SELECT isoland,noid FROM land WHERE land2='$ltxtx'";
							$result=mysql_query($SQL);
							if(!$result)
							{
								echo $SQL." - fungerande inte".mysql_error();
							}
							else
							{
								$row=mysql_fetch_assoc($result);
								$isoland=$row['isoland'];
								$noid=$row['noid'];
							}	
						}
//						Nytt försök med ltxtx och land2							
						if($noid == "")
						{
							$isoland="";
							$SQL="SELECT isoland,noid FROM land WHERE land2='$ltxtx'";
							$result=mysql_query($SQL);
							if(!$result)
							{
								echo $SQL." - fungerande inte".mysql_error();
							}
							else
							{
								$row=mysql_fetch_assoc($result);
								$isoland=$row['isoland'];
								$noid=$row['noid'];
							}	
						}
						if($noid == "")
						{
//							echo "- - - - - > > > > >".$str.
//							" - saknas som land, kolla . . . . . . . . . . Id => ".$znum." <br/>";
						}
						else
						{
							$rgdp="2 RGDP ".$noid;
//							echo $isoland." - uppdaterad från text . . . . . . . . . . Id => ".$znum." <br/>";
							fwrite($handut,$rgdp."\r\n");
						}
					}	
				}
			}
			fwrite($handut,$str."\r\n");
		}
//		echo "<br/n>";
		echo "Program komplandx avslutad <br/n>";
		echo "<br/n>";
//		echo "Filen ".$fileut." har skapats <br/n>";
		fclose($handin);
		fclose($handut);
//
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts. <br/>";
	}
}
?>
<?PHP
/*
Programmet skulle ändra GEDCOMs datumformat till mer naturligt format ÅÅÅÅMMDD.

Om speciella RGD-taggar kommer att användas, kan detta också då vara lämpligt data
att använda en specialtagg till i stället för att förändra originaldatumet.
I så fall sätts $date nedan till RGD-tagg RGDD och skrivs ut före en efterföljande skrivning
av originalraden via $str.

*/
echo "<br/>";
echo "Program kompdatex startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	
$fileut=$directory . "RGD1.GED";
//
$filein=$directory . "RGDF.GED";
//
//echo "<br/>";
//$filexx=$directory . "RGD1_bup.GED";
//$result=copy($fileut,$filexx);
//if($result == false) {
//	echo "OBS! Filkopieringen misslyckades. <br/>";
//	echo "<br/>";
//}
//	else {
//		echo "Filkopiering gick bra. ".$filexx." har skapats <br/>";
//		echo "<br/>";
//	}	
//
//echo "<br/>";
if(file_exists($fileut))
{
	$result=rename($fileut,$filein);
	if($result == false) {
		echo "OBS! Filkopieringen misslyckades. <br/>";
		echo "<br/>";
	}
//	else {
//		echo "Filkopiering gick bra. <br/>";
//		echo "<br/>";
//}	
}
//
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	$aarant=0;
	$datant=0;
	$ungant=0;
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
/*
Ladda array med kända värden.
*/
		$tst[]="JAN";
		$tst[]="FEB";
		$tst[]="MAR";
		$tst[]="APR";
		$tst[]="MAY";
		$tst[]="JUN";
		$tst[]="JUL";
		$tst[]="AUG";
		$tst[]="SEP";
		$tst[]="OCT";
		$tst[]="NOV";
		$tst[]="DEC";
		$tst[]="CAL";
		$tst[]="EST";
		$tst[]="ABT";
		$tst[]="INT";
		$tst[]="AFT";
		$tst[]="BEF";
		$tst[]="BET";
		$tst[]="AND";
//	
		$len=0;
		$stop=0;
		$llen = '';
		$lnamn = '';
		$akt = 'NEJ';
		$head= 'ON';
		$kant=0;
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
						if($ztal == '@') {
							if((substr($str,$zmax,5) == '@ IND') || (substr($str,$zmax,5) == '@ FAM')) {
								$head = 'OFF';
//								echo "Första individen/relationen = ".$str." <br/>";
							}
							$zmax = $zlen;
						}	
						$zmax++;
					}
				}	
			}
//		
//	Första individ/relation börjar	
			if($head == 'OFF')
			{
//	hitta idnummer för individ/relation
				$ztag = substr($str,0,3);
				if($ztag == '0 @') {
					$znum = '';
					$znamn = '';
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
				$pos1=substr($str,0,1);
				$tagk=substr($str,0,5);
				$tagg=substr($str,0,6);
				if(($pos1 == '0') || ($pos1 == '1'))
				{
					$akt = 'NEJ';
				}
				if($tagg == '1 NAME')
				{
					$llen = strlen($str);
					$lnamn = substr($str,6,$llen);
				}
				if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
				{
					$akt = 'JA';
				}
				if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
				{
					$akt = 'JA';
				}
				if(($tagg == '1 MARR') || ($tagg == '1 CHAN'))
				{
					$akt = 'JA';
					if($tagg == '1 MARR') {
						$lnamn = 'Vigseldatum familj';
					}
					if($tagg == '1 CHAN') {
						$lnamn = 'Ändringsdatum';
					}
				}
				if(($tagg == '2 DATE') && ($akt == 'JA'))
				{
					$akt='NEJ';
//  				$date=substr($str,0,7);   Om inte RGDD skall användas  
					$date=substr($str,0,2);
					$date=$date."RGDD ";
					$tdag='XX';
					$tman='XXX';
					$taar='XXXX';
					$ndag=0;
					$nman=0;
					$naar=0;
					$ber='JA';
					$cal='NEJ';
					$temp='';
					$tkn='';
					$imax=7;
					$fant=0;
					$len=strlen($str);
					$test = substr($str,7,3);
//					if($test == 'CAL') {
//						$qqq = (substr($str,0,7)).(substr($str,11,((strlen($str)) - 11)));
//						$str = $qqq;
//						$len=strlen($str);
//						}
					for($i=0;$i<count($tst);$i++)
					{
						$x = 20;
						if(($tst[$i]) == $test) {
//	Raden börjar inte med datum eller årtal utan någon text
							$x = $i;
//							if($i > 12)	{
//	Raden börjar inte med CAL eller godkänd månad	
//								$ber = 'NEJ';
//							}
						}
					}
					if(($ber == 'JA') || ($test == 'CAL'))
					{
//						if($len > 18) {
//							$len = 18;
//						}
						while($imax <= $len)
						{
							$tkn = substr($str,$imax,1);
							if(($tkn == ' ') || ($imax == $len))
							{
								if(strlen($temp) == 1) {
									if(($temp < '0') || ($temp > '9')) {
										$temp = '';
										$fant++;
									}	
								}
								if(strlen($temp) == 2) {
									$p1 = substr($temp,0,1);
									if(($p1 < '0') || ($p1 > '9')) {
										$p1 = '';
									}	
									$p2 = substr($temp,1,1);
									if(($p2 < '0') || ($p2 > '9')) {
										$p2 = '';
									}
									if(($p1 == '') || ($p2 == '')) {
										$temp = $p1.$p2;
										$fant++;
									}
								}
								if(strlen($temp) == 4) {
									$p1 = substr($temp,0,1);
									if(($p1 < '0') || ($p1 > '9')) {
										$p1 = '';
									}	
									$p2 = substr($temp,1,1);
									if(($p2 < '0') || ($p2 > '9')) {
										$p2 = '';
									}
									$p3 = substr($temp,2,1);
									if(($p3 < '0') || ($p3 > '9')) {
										$p3 = '';
									}	
									$p4 = substr($temp,3,1);
									if(($p4 < '0') || ($p4 > '9')) {
										$p4 = '';
									}
									if(($p1 == '') || ($p2 == '') || 
									($p3 == '') || ($p4 == '')) {
										$temp = '';
										$fant++;
									}
								}
								if(strlen($temp) == 1)
								{
									$tdag='0'.$temp;
									$temp='';
									$ndag = $tdag; 
								}
								elseif(strlen($temp) == 2)
								{
									$tdag=$temp;
									$temp='';
									$ndag = $tdag;
									if($ndag > 31) {
										$fant++; }
								}
								elseif(strlen($temp) == 3)
								{
//									if($temp != 'CAL') {
									if($x == 20) {
										for($i=0;$i<count($tst);$i++) {
											if(($tst[$i]) == $temp)	{
												$nman = ($i+1);
												$tman = $nman; 	}
											if(($nman < 1) || ($nman > 12)) {
												$nman = 0;
												$tman = ''; }	
											if(($nman == 2) && ($ndag > 28)) {
												$fant++; }
											if(($nman==4) || ($nman==6) || ($nman==9) || ($nman==11)) {
												if($ndag > 30){
													$fant++; }
											}
										}
									}	
									$temp='';
								}
								elseif(strlen($temp) == 4)
								{
									$taar=$temp;
									$naar=$taar;
									$qart = $naar / 4;	
									$rest = $naar - (4 *(int)$qart);
									$temp='';
									if(($rest == 0) && ($nman == 2) && ($ndag == 29))
									{
										$fant = 0;
									}
									if(($naar == 1712) && ($nman == 2) && ($ndag == 30))
									{
										$fant = 0;
									}
									if(($naar == 1753) && ($nman == 2))
									{
										if($ndag > 17) {
											$fant++;
										}
									}
									if(($naar == 1700) || ($naar == 1800) || ($naar == 1900))
									{
										if(($nman == 2) && ($ndag == 29)) {
											$fant++;
										}
									}
								}
								else
								{
//									Ej förväntad struktur.
									$imax = $len;
								}
							}
							else
							{
								$temp = $temp.$tkn;
							}
							$imax++;
						}
						if($fant > 0)
						{
							$kant++;
						}		
						if((strlen($tman)) == 1)
						{
							$tman = '0'.$tman;
						}
						if($naar >= 1000)
						{
							if($tdag == 'XX')
							{
//							Även om månad godkänts skrivs bara år när datum saknas	
								$tman = '';
								$tdag = '';
							}
							if($ndag == 0)
							{
								$tman = '';
								$tdag = '';
							}
							if($tman == 'XXX')
							{
//							Månad saknas blankas även dag 	
								$tman = '';
								$tdag = '';
							}
							if($nman == 0)
							{
								$tdag = '';
								$tman = '';
							}
//	Sätt samman befintliga bitar av datumet	om datum eller årtal är OK
							if($fant == 0) {
								$datut=$date.$taar.$tman.$tdag;
//	Underhåll räknare
								if($tdag == '')
								{
									$aarant++;
								}
								else
								{
									$datant++;
								}	
								fwrite($handut,$datut."\r\n");
							}	
						}
						else
						{
// konverteringen tar bara exakt / kalkulerat årtal / exakt datum, övriga återskrivs oförändrade
							$ungant++;
//							echo "<br/>";
//							echo "Ej kompletterat ".$str." . . . . . . . . . . Id => ".$znum." <br/>";
						}
						if($fant > 0)
						{
							echo "<br/>";
							echo " *  *  *  *  *  Ej korrekt kalenderdatum ".$str.
							" . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
//	Larm
							$larmant++;
							$filelarm=$directory . "LARM_lista.txt";
							$handlarm=fopen($filelarm,"a");
							if($larmrub4 == 1) {
								$larm = " ";
								fwrite($handlarm,$larm."\r\n");
								$larm = "L A R M  T Y P  IV ";
								fwrite($handlarm,$larm."\r\n");
								$larm = " ";
								fwrite($handlarm,$larm."\r\n");
								$larmrub4++;
							}
// sätt om möjligt id och namn
							$larmid = $znum;
							$larmnamn = $lnamn;
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
// och beskrivande feltext
							$larm = "Ej korrekt kalenderdatum ".$str.
							" - Id => ".$larmid." - ".$larmnamn;
							fwrite($handlarm,$larm."\r\n");
							$larm = "* * * Kan orsaka felaktiga beräkningar vid bearbetning.";
							fwrite($handlarm,$larm."\r\n");
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							fclose($handlarm);
//
						}	
					}
					else
					{
						$ungant++;
//						echo "<br/>";
//						echo "Ej kompletterat ".$str." . . . . . . . . . . Id => ".$znum." <br/>";
					}
				}	
				$ber='JA';
				$cal='NEJ';
			}
			fwrite($handut,$str."\r\n");
		}
//		echo "Antal kompletta datum   = ".$datant."<br/>";
//		echo "Antal med årtalgivelse  = ".$aarant."<br/>";
//		echo "Ej exakt datum eller år = ".$ungant."<br/>";
		if($kant > 0) {
			echo "<br/>";
			echo $kant." DATE post(er) med felaktigt kalenderdatum hittad, måste rättas. <br/>";
			echo "OBS! informera användaren om de identifierade felaktigheterna. <br/>";
			echo "<br/>";
		}	
//  		echo "<br/>";
//		echo "Kontrollera rimligheten för eventuella felsignaler.<br/>";
//		echo "<br/>";
//		echo "Filen ".$fileut." har skapats <br/>";
//		echo "<br/>";
		echo "Program kompdatex avslutad <br/>";
		echo "<br/>";
		fclose($handin);
		fclose($handut);
//
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts. <br/>";
	}
}
?>
<?PHP
/*
Programmet kontrollerar om församlingar i GEDCOM filen 
finns i tabellen från Skatteverket.

Programmet gör uppdatering av RGDP tagg med församlingsnamnet
från normeringstabellen och normeringsidentiteten.

OBS! 
Tabellen saknar helt tidsintervaller
Eventuellt skall årtal 0 ersättas med 1000 vid kontroll
Nu exkluderas bara år 0 från felsignal.
OBS!


*/
echo "<br/>";
echo "Program kompfnorx startad <br/>";
//	töm echo buffer
//	echo str_pad('',4096)."\n";    
//	ob_flush();
//	flush();
//	ob end
//	ob_end_flush();
//	
$fileut=$directory . "RGD8.GED";
//
$filein=$directory . "RGD1.GED";
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
//		echo "<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
		$aaar=0;
//	???	$aaar=1000;
		$stop=0;
		$akt = 'NEJ';
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
//								echo "Första individen/relationen = ".$str." <br/>";
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
				$imax=7;
				$fors="";
				$ort ="";
				$len=strlen($str);
				$pos1=substr($str,0,1);
				$tagk=substr($str,0,5);
				$tagg=substr($str,0,6);
				if(($pos1 == '0') || ($pos1 == '1'))
				{
					$aaar=0;
//	???				$aaar=1000;
					$akt = 'NEJ';
				}
				if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
				{
					$akt = 'JA';
				}
				if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
				{
					$akt = 'JA';
				}
				if($tagg == '1 MARR')
				{
					$akt = 'JA';
				}
				if($tagg == '2 RGDD')
				{
					$aaar=substr($str,7,4);
				}
				if($tagg == '2 RGDP')                 
				{
					$akt = 'NEJ';
				}
				if(($tagg == '2 PLAC') && ($akt == 'JA'))
				{
					$akt = 'NEJ';
					$lxx = '';
					while($imax <= $len)
					{
						$tkn=substr($str,$imax,1);
						$kfs=substr($str,($imax+1),3);
						$kfl=substr($str,($imax+1),4);
//	Fix för att rädda församlingar som är förkortade till "...förs" eller "lfs", "sfs" eller "dkf/s"				
						if($tkn == ' ')
						{
							if((substr($fors,($imax-12),5)) == 'förs')
							{
								$fors = $fors.'amling';
							}
						}
						if($tkn == ' ')
						{
							if(($kfs == 'lfs') || ($kfs == 'LFS') || ($kfs == 'Lfs'))
							{
								$fors = $fors.' landsförsamling';
								$imax++;
								$imax++;
								$imax++;
								$imax++;
							}
						}
						if($tkn == ' ')
						{
							if(($kfs == 'sfs') || ($kfs == 'SFS') || ($kfs == 'Sfs'))
							{
								$fors = $fors.' stadsförsamling';
								$imax++;
								$imax++;
								$imax++;
								$imax++;
							}
						}	
						if($tkn == ' ')
						{
							if(($kfl == 'dkfs') || ($kfl == 'DKFS') || ($kfl == 'Dkfs')) 
							{
								$fors = $fors.' domkyrkoförsamling';
								$imax++;
								$imax++;
								$imax++;
								$imax++;
								$imax++;
							}
							elseif(($kfs == 'dkf') || ($kfs == 'DKF') || ($kfs == 'Dkf')) 
							{
								$fors = $fors.' domkyrkoförsamling';
								$imax++;
								$imax++;
								$imax++;
								$imax++;
							}
						}
						if(($tkn == ',') || ($imax == $len))
						{
							if($ort == '')
							{
								$ort = $fors;
							}
						}
						if($tkn == '(')
						{
							$lxx = '';
						}
						if($tkn == ')')
						{
							$lxx = $lxx.$tkn;
							if($lxx == '(A)') {
								$lxx = '(AB)';
								$fors=$fors.'B';
							}
							$fors=$fors.$tkn;
							if(strlen($lxx) == 4)
							{
								if(($lxx == '(AB)') || ($lxx == '(AC)') || ($lxx == '(BD)'))
								{
//									Svensk församling						
								}
								else
								{
//									Land visas eller inte visas ?	
									$fors='';
								}
							}
							$imax=$len;
						}	
						else
						{
							$fors=$fors.$tkn;
							$lxx = $lxx.$tkn;
							
						}
						$imax++;
					}
//	Sök församlingen i foskx
					if($fors != "")
					{
						$pre2=substr($fors,0,2);
						if(($pre2 == 'i ') || ($pre2 == 'I '))
						{
							$fors = substr($fors,2,(strlen($fors)-2));
						}
						$pre4=substr($fors,0,4);
						if(($pre4 == 'på ') || ($pre4 == 'På '))
						{
							$fors = substr($fors,4,(strlen($fors)-3));
						}
//
						$tmax = 0;
						$txty = '';
						$tlen = strlen($fors);
						while($tmax < $tlen) {
							$txtx = substr($fors,$tmax,1);
							if($txtx != "'") {
								$txty = $txty.$txtx;
							}
							$tmax++;
						}
						$fors = $txty;
//				
						$noid=0;
						$SQL="SELECT noid FROM foskx WHERE fors='$fors'";
//						$SQL="SELECT faar,taar,noid FROM foin WHERE fors='$fors'";
//						$SQL="SELECT faar,taar,noid FROM foxx WHERE fors='$fors'";
						$result=mysql_query($SQL);
						if(!$result)
						{
							echo $SQL."fungerande inte".mysql_error();
							
						}
						else
						{
							$row=mysql_fetch_assoc($result);
//							$faar=$row['faar'];
//							$taar=$row['taar'];
							$noid=$row['noid'];
						}	
						if($noid == 0)
						{
//							echo $fors." saknas, i alla fall för år ".$aaar.
//							echo "<br/>";
//							echo $fors." saknas"
//							.", kolla . . . . . . . . . . Id => ".$znum." <br/>";
//	Sök alternativ
/*							
//
							$tmax = 0;
							$txty = '';
							$tlen = strlen($ort);
							while($tmax < $tlen) {
								$txtx = substr($ort,$tmax,1);
								if($txtx != "'") {
									$txty = $txty.$txtx;
								}
								$tmax++;
							}
							$ort = $txty;
//				
							$noid="";
							$lkod="";
							$info="";
							$fors2="";
							$SQL="SELECT lkod,noid,fors,info FROM fors WHERE fors='$ort'";
							$result=mysql_query($SQL);
							if(!$result)
							{
								echo $SQL."fungerande inte".mysql_error();
							
							}
							else
							{
								$row=mysql_fetch_assoc($result);
								$lkod=$row['lkod'];
								$noid=$row['noid'];
								$fors2=$row['fors'];
								$info=$row['info'];
							}	
							if($fors2 != "")
							{
								if($lkod == '')
								{
//									echo "- - - - - > ?? > ".$ort."  -  -  Ej unik, alternativa länskoder  -  -  "
//									.$info." . . . . . . . . . . Id => ".$znum." <br/>";
								}
							}*/
//	Fix för dubblettkontrollen							
							if($fors != '') {
								$rgdp="2 RGDP ".$fors;
							}
							else {
								if($ort != '') {
									$rgdp="2 RGDP ".$ort;
								}
								else {
									$rgdp="2 RGDP *****";
								}	
							}							
							fwrite($handut,$rgdp."\r\n");
//							
						}
						else
						{
//	Hit skall tidpunkt läggas in och även eventuell läsning mot fono för namnet på utpekad församling.						
//							echo $fors." ".$aaar." är OK <br/>";
							$rgdp="2 RGDP ".$noid;
							fwrite($handut,$rgdp."\r\n");
						}
/*	Tidpunkt används ej						
						else
						{
							if(($aaar <= $taar) && ($aaar >= $faar)) 
							{
//								echo $fors." ".$aaar." är OK <br/>";
								$rgdp="2 RGDP ".$noid;
								fwrite($handut,$rgdp."\r\n");
							}
							else
							{
//	Skall vi undanta saknade årtal?				
								if($aaar > 0)
								{
//									echo "? ? ? ".$fors." är OK bara om ".$faar."<".$aaar.">".$taar.
//									" . . . . . . . . . . Id => ".$znum." <br/>";	
								}
								else
								{
//									echo $fors." ".$aaar." är OK <br/>";
									$rgdp="2 RGDP ".$noid;
									fwrite($handut,$rgdp."\r\n");
								}
							}
						}*/
					}
				}
			}
		fwrite($handut,$str."\r\n");
		}
//		echo "<br/n>";
		echo "Program kompfnorx avslutad <br/n>";
		echo "<br/n>";
		echo "Filen ".$fileut." har skapats <br/n>";
		fclose($handin);
		fclose($handut);
//
	}
	else
	{
		echo "<br/n>";
		echo "Filen ".$filein." saknas, programmet avbryts. <br/>";
	}
}	
?>
<?PHP
/*
Programmet kontrollerar att individer och relationer innehåller 
de kopplingar som RGD kärver.

RELATION skall ha minst två kopplingar till PERSONER 
INDIVID skall ha minst en koppling till RELATION

*/
$filename=$directory . "RGD8.GED";
//
$snak = '@';
$snal = '0 @';
$name = '';
$post = '';
$cntind = 0;
$cntfam = 0;
$infoant = 0;
//
if(file_exists($filename))
{
//	echo $filename.' finns<br/>';
//	echo $filename.' har storleken '.filesize($filename).'<br/>';
	$handle=fopen($filename,'r');
	echo '<br/>';
	echo 'Program testantrx startad <br/>';
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
						if((substr($str,$zmax,5) == "@ IND") || (substr($str,$zmax,5) == "@ FAM")) {
							$head = "OFF";
//							echo "Första individen/relationen = ".$str." <br/>";
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
			$tagg1=substr($str,0,1);
			$taggk=substr($str,2,4);
			if($tagg1 == '0')
			{
				if(($post == 'FAM') && ($cntfam == 0)) {
//					echo 'Familj som helt saknar individ - Id '.$id.' - Familj <br/>'; 
					$fellista[] = '1Id '.$id.' - Familj';
					$infoant++;
				}
				if(($post == 'FAM') && ($cntfam == 1)) {
//					echo 'Familj med endast en individ - Id '.$id.' - Familj <br/>'; 
					$fellista[] = '2Id '.$id.' - Familj'; 
					$infoant++;
				}
				if(($post == 'IND') && ($cntind == 0)) {
//					echo 'Individ som saknar familjekoppling - Id '.$id.' - '.$name.'<br/>'; 
					$fellista[] = '3Id '.$id.' - '.$name; 
					$infoant++;
				}
//
				$imax=3;
				$id='';
				while($imax <= 20)
				{
					$sna=substr($str,$imax,1);
					if($sna == $snak)
					{
						$imax++;
						$imax++;
						$post=substr($str,$imax,3);
						$imax=20;	
						if($post == 'IND')
						{
							$cntind = 0;
							$name = '';
						}
						if($post == 'FAM')
						{
							$cntfam = 0;
							$name = '';
						}
					}
					else
					{
						$pos=substr($str,$imax,1);
						$id=$id.$pos;
						$imax++;
					}
				}
			}
//                   			
			if($taggk == 'NAME')
			{
				$nlen = strlen($str);
				$name = substr($str,6,($nlen-6));
			}
			if($taggk == 'HUSB')
			{
				$cntfam++;
			}
			if($taggk == 'WIFE')
			{
				$cntfam++;
			}
			if($taggk == 'CHIL')
			{
				$cntfam++;
			}
			if($taggk == 'FAMC')
			{
				$cntind++;
			}
			if($taggk == 'FAMS')
			{
				$cntind++;
			}
		}	
	}
	fclose($handle);
//	
	if($infoant == 0) {
//		fwrite($handux," \r\n");
//		fwrite($handux,"Inga avvikande uppgifter hittades. \r\n");
	}
	else {
		$fileux=$directory . "Info.txt";
		$handux=fopen($fileux,"w");
		fwrite($handux," \r\n");
		fwrite($handux,"INFORMATIONSLISTA MED SAKNADE RELATIONSKOPPLINGAR. \r\n");
//
		$brytr = 0;
		$oldid = '';
		$fellista=array_unique($fellista);
		sort($fellista);
		foreach($fellista as $felrad) {
//
			$block = '';
			$xlen = strlen($felrad);
			$rubid = substr($felrad,0,1);
			$block = substr($felrad,1,($xlen-1));
			if($rubid != $oldid) {
				fwrite($handux," \r\n");
				if($rubid == '1') {
					fwrite($handux," \r\n");
					fwrite($handux,"Familj som helt saknar individ \r\n");
				}
				if($rubid == '2') {
					fwrite($handux," \r\n");
					fwrite($handux,"Familj med endast en individ \r\n");
				}
				if($rubid == '3') {
					fwrite($handux," \r\n");
					fwrite($handux,"Individ som saknar familjekoppling \r\n");
				}
				$brytr = 0;
			}	
			$brytr++;
			if($brytr >= 4) {
				fwrite($handux," \r\n");
				$brytr = 1;
			}
			fwrite($handux,$block." \r\n");
			$oldid = $rubid;
		}
		fwrite($handux," \r\n");
		fwrite($handux,"Personer/Familjer som saknar korrekta kopplingar bör kontrolleras. \r\n");
		fwrite($handux," \r\n");
		fwrite($handux,"Orsaken kan vara felregistrering där uppgifterna inte blivit kompletta \r\n");
		fwrite($handux,"men kan också orsakats av urvalet till GEDCOM filen. \r\n");
		fclose($handux);
		echo '<br/>';
		echo '***** Lista Info.txt med okomplett data har skapats, informera användaren <br/>';
		echo '<br/>';
	}	
//
//	echo '<br/>';
	echo 'Program testantrx avslutat <br/>';
	echo '<br/>';
	echo "Program xprsstrt kört klart * * * * * <br/>";
	echo "<br/n>";
//
}
else
{
	echo $filename.' saknas, programmet avslutas.<br/>';
}
if($larmant > 0) {
	echo "<br/>";
	echo "***** Larmlista med ".$larmant." post(er) har skrivits ut, måste rättas. <br/>";
	echo "<br/>";
}
?>
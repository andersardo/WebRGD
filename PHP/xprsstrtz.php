<?PHP
/*
Programmet är avsett för snabbkontroll av GEDCOM filer.
Består av ett antal program som körs i serie.

Säkerhetstest av teckenformat.

*/
require 'initbas.php';
require 'initdb.php';
//
$brytr = 0;
$larmant = 0;
$larmrub4 = 1;
$larmrub5 = 1;
$larmrub6 = 1;
$larmrub7 = 1;
$larmrub8 = 1;
$larmrub9 = 1;
$larmrub10 = 1;
//			
$fileix=$directory . "RGD1.GED";
//
echo "<br/>";
//
$typ = '';
$typtest = '';
$larmi = 0;
$larmv = 0;
if(file_exists($fileix)) {
	echo "Program xprsstrtz startad * * * * * <br/>";
	echo "<br/n>";
	echo $fileix." finns<br/>";
	$handix=fopen($fileix,"r");
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
//	
}
//
////
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
	$filelarm=$directory . "Check_lista.txt";
	$handlarm=fopen($filelarm,"a");
	if($larmrub4 == 1) {
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		fwrite($handlarm,$larm."\r\n");
		$larm = "*** L A R M  (IV) Teckenformat";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larmrub4++;
	}
	if($typ == '') {
		$larm = "Teckenformatet (CHAR) saknas helt och filen kan då ej tolkas på rätt sätt.";
		fwrite($handlarm,$larm."\r\n");
	}	
	else {
		$larm = "Okänt teckenformatet CHAR ".$typ.", filen kunde ej tolkas på rätt sätt.";
		fwrite($handlarm,$larm."\r\n");
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
?>
<?PHP
/*
Första programmet består av tre delar som lagts samman i ett flöde.

Programmet är avsett för GEDCOM filer med strukturerade källor.
Källorna ligger då i slutet av filen medan respektive källa bara innehåller en referens.

Programmet delar infilen till två indatafiler.
I enfil placeras SOUR texter som skall uppdateras i databastabell.
I andra filen placeras poster som skall användas i vidare bearbetningar.

Programmet konvtabi läser sen in alla indexerade identiteter och laddar tabellen sour,
programmet konvtabu tar in alla övriga poster och uppdaterar RGDS taggen
med texten från databastabellen och skapar en rad SOUR information i utfilen.

*/
echo "<br/>";
echo "Program konvtabb startad <br/>";
//
$filein=$directory . "RGD1.GED";
$fileu1=$directory . "RGDU.GED";
$fileu2=$directory . "RGDV.GED";
//
if(file_exists($filein))
{
		$handin=fopen($filein,"r");
		$handu1=fopen($fileu1,"w");
		$handu2=fopen($fileu2,"w");
		$fil = 0;
		$uttxt = '';
		$chil = '';
		$char = 'NEJ';
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
			$tett=substr($str,0,1);
			$tabb=substr($str,0,4);
			$tagg=substr($str,2,4);
			$tagk=substr($str,2,3);
			if($tett == "0") {
				$fil = 2;
				$uttxt = '';
				$chil = '';
				$indi = '';
				$strlen = strlen($str);
				$strxxx = substr($str,($strlen-4),4);
				if(($strxxx == 'INDI') || ($strxxx == 'NDI ')) {
					$indi = 'JA';
				}
			}
			if(($tett == "1") && ($fil != 1)){
				if(($tagg == "NAME" ) && ($fil != 4)){
					$fil = 2;
				}
				elseif($tagk == "SEX" ){
					$fil = 2;
				}
				elseif($tagg == "BIRT" ){
					$fil = 2;
				}
				elseif($tagk == "CHR" ){
					$fil = 2;
				}
				elseif($tagg == "DEAT" ){
					$fil = 2;
				}
				elseif($tagg == "BURI" ){
					$fil = 2;
				}
				elseif($tagg == "ADOP" ){
					$fil = 2;
					echo "* Individ med icke-biologisk koppling finns - sök ".$str."<br/>";
				}
				elseif(($tagg == "MARR" ) && ($indi == '')){
					$fil = 2;
				}
//				elseif($tagg == "OCCU" ){
//					$fil = 2;
//				}
				elseif($tagg == "FAMC" ){
					$fil = 2;
				}
				elseif($tagg == "FAMS" ){
					$fil = 2;
				}
				elseif($tagg == "HUSB" ){
					$fil = 2;
				}
				elseif($tagg == "WIFE" ){
					$fil = 2;
				}
				elseif($tagg == "CHIL" ){
					$fil = 2;
					$chil=$str;
					}
				else {
					if($tagg == "OCCU" ){
						fwrite($handu2,$str."\r\n");
						$fil = 3;
					}
					else {
						$fil = 3;
					}	
				}
			}
			if($tabb == "0 @S") {
				$fil = 1;
			}
			if($tabb == "0 @N") {
				$fil = 4;
			}
			if($tabb == "0 @X") {
				$fil = 4;
			}
			if($tabb == "0 @R") {
				$fil = 4;
			}
			if($tabb == "0 @M") {
				$fil = 4;
			}
//			
			if($fil == 1)
			{
				$utlen = strlen($str);	
				if($tabb == "0 @S") {
					fwrite($handu1,$str."\r\n");
				}
				else {
					if($tagg == '_UPD') {
//	skip
					}
					elseif(($uttxt == '') && ($utlen > 7)) {
						$uttxt = '2 RGDS '.substr($str,7,($utlen-7));
						fwrite($handu1,$uttxt."\r\n");
					}
				}		
			}
//
			if($fil == 2)
			{
//	Skippa volymrader som ej är aktiva
				if(($tett == '3') || ($tett == '4') || ($tett == '5')) {
//	Skipp
				}
				elseif($tagg == 'NOTE') {
				}
				elseif($tagg == 'CONC') {
				}
				elseif($tagg == 'CONT') {
				}
				elseif($tagg == 'GIVN') {
				}
				elseif($tagg == 'SURN') {
				}
				elseif($tagg == '_MAR') {
				}
				elseif($tagk == 'AGE') {
				}
				elseif($tagg == 'CAUS') {
				}
				elseif($tagg == 'PAGE') {
				}
				elseif($tagg == 'OBJE') {
				}
				elseif($tagg == '_TOD') {
				}
				elseif($tagg == '_FRE') {
					$tagn=substr($str,8,7);
					if(($tagn != 'Natural') && ($tagn != 'Unknown')){
						echo "* Barn med icke-biologisk koppling finns - ".$chil." / ".$str."<br/>";
						fwrite($handu2,$str."\r\n");
					}	
				}
				elseif($tagg == '_MRE') {
					$tagn=substr($str,8,7);
					if(($tagn != 'Natural') && ($tagn != 'Unknown')) {
						echo "* Barn med icke-biologisk koppling finns - ".$chil." / ".$str."<br/>";
						fwrite($handu2,$str."\r\n");
					}	
				}
//	Faller ut under 3:an
				elseif($tagg == 'OCCU') {
				}
				else {
					fwrite($handu2,$str."\r\n");
				}	
			}
		}
//
		fclose($handin);
		fclose($handu1);
		fclose($handu2);
//
		echo "Program konvtabb avslutad <br/>";
		echo "<br/>";
}
else
{
	echo "Filen ".$filein." saknas, programmet avbryts <vr/>";
}
?>
<?PHP
/*
Programmet skall läsa av SOUR källor från GEDCOM fil och ladda data i tabell sour.

Tabellen skall sen i konvtabu användas för att uppdatera texten i RGDS taggen

*/
echo "<br/>";
echo "Program konvtabi startad <br/>";
//
$filename=$directory . "RGDU.GED";
//
if(file_exists($filename))
{
	$handle=fopen($filename,"r");
//
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
//	
	$len=0;
	$txt='';
	$sekv=1;
	$id='';
//	Läs in indatafilen				
	$lines = file($filename,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str)
	{
		$nytt = substr($str,0,3);
		if($nytt == "0 @")
		{
			$imax=3;
			$sekv=1;
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
			}
		}
		else
		{
			$tgtx = '';
			$len=strlen($str);
			$tgnr = substr($str,0,1);
			$tgtp = substr($str,2,4);
			$tgtx = substr($str,7,($len-7));
//			
			$txt=$tgnr.' '.$tgtp.' '.$tgtx;
//
			$tlen = strlen($txt);
			if($tlen > 2)
			{
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
	}
	fclose($handle);
	echo "Program konvtabi avslutad <br/>";
	echo "<br/>";
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
echo "Program konvtabu startad <br/>";
//	
$filein=$directory . "RGDV.GED";
$fileut=$directory . "RGDT.GED";
$filesou=$directory . "sour.dat";
//
$soucnt = 0;
$akt = 'NEJ';
//
if(file_exists($filein))
{
	$handin=fopen($filein,"r");
	$handut=fopen($fileut,"w");
	$handsou=fopen($filesou,"w");
//	Läs in indatafilen				
	$lines = file($filein,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str)
	{
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
		if(($tagg == '2 SOUR') && ($akt == 'JA'))
		{
			$akt = 'NEJ';
//	Array
			$soulen=strlen($str);
			$souorg=substr($str,7,$soulen);
//
			$tagt = substr($str,7,1);
			$tagi = substr($str,2,4);
			if($tagt == '@')
			{
//	hitta idnummer för individ/relation
				$znum = '';
				$zlen = strlen($str);
				$zmax = 8;
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
				$sekv = 1;
				$id = $tagi.$znum;
				$SQL="SELECT text FROM sour WHERE(id='$id' AND sekv=$sekv)";
				$result=mysql_query($SQL);
				if($result)
				{
					$row=mysql_fetch_assoc($result);
					$textin=$row['text'];"<br/>";
//					
					fwrite($handut,$textin."\r\n");
					fwrite($handut,$str."\r\n");
//	Array 
					$soulen=strlen($textin);
					$souut=substr($textin,7,$soulen);
					$sou[$souorg]=$souut;
					$soucnt++;
				}
				else
				{
					$souut = '2 RGDS '.$souorg;
					fwrite($handut,$souut."\r\n");
					fwrite($handut,$str."\r\n");
//	Array 
					$sou[$souorg]=$souorg;
					$soucnt++;
				}
//				
			}
			else
			{
				$souut = '2 RGDS '.$souorg;
				fwrite($handut,$souut."\r\n");
				fwrite($handut,$str."\r\n");
//	Array 
				$sou[$souorg]=$souorg;
				$soucnt++;
			}
		}
		else
		{
			fwrite($handut,$str."\r\n");
		}
	}
	fclose($handin);
	fclose($handut);
//	Array start
	if($soucnt > 0) {
		fwrite($handsou,json_encode($sou)."\r\n");
		fclose($handsou);
	}
	else {
		fwrite($handsou,"{}\r\n");
		fclose($handsou);
		echo "Källinformation saknas. <br/>";
	}	
//	Array slut
//			
	echo "Program konvtabu avslutad <br/>";
	echo "<br/>";
//	
	$result = mysql_query("UNLOCK TABLES");
	if(!$result)
	{
	  echo "UNLOCK av sour fungerande inte".mysql_error();
	}
}
else
{
	"Filen ".$filename." saknas, programmet avbryts <br/>";
}
?>
<?PHP
/*
Programmet kan "rätta" till ordningsföljden för tex. Min Släkts GEDCOM fil.
Byter plats på SEX-NAME och MARR-HUSB/WIFE.

Läsprogrammet testordn visar om det finns poster i fel ordning.

*/
echo "<br/>";
echo "Program konvordn startad <br/>";
//	
$filein=$directory . "RGDT.GED";
$fileut=$directory . "RGDY.GED";
//
$wrant=0;
$w1ant=0;
$w2ant=0;
$w3ant=0;
$spar1='NEJ';
$spar2='NEJ';
$spar3='NEJ';
$wid = '';
$wname='';
$wwname='';
$wsex='';
$wpar='';
$wmar='';
$ztyp='';
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	if(file_exists($filein))
	{
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
//	Töm sparat data vid postbyte	
				$pos1 = substr($str,0,1);
				$tagg = substr($str,2,4);
				$tag6 = substr($str,0,6);
				if($pos1 == '0')
				{
					if($wid != '') {
						fwrite($handut,$wid."\r\n");
					}	
					if(($wname != '') && ($wsex == '') && ($ztyp == 'IND'))
					{
						echo "Kön saknas, ändras till M eller F om kön framgår - Id => "
						.$znum." - ".$lnamn." <br/>";
						fwrite($handut,"1 SEX O\r\n");
						$wname = '';
//	Larm
						$larmant++;
						$filelarm=$directory . "Check_lista.txt";
						$handlarm=fopen($filelarm,"a");
						if($larmrub5 == 1) {
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							fwrite($handlarm,$larm."\r\n");
							$larm = "*** F E L  L I S T A  (V) Saknar könstillhörighet";
							fwrite($handlarm,$larm."\r\n");
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larmrub5++;
							$brytr = 0;
						}
// sätt om möjligt id och namn
						$larmid = $znum;
						if($lnamn == '') {
							$lnamn = 'Namn saknas helt';
						}	
						$larmnamn = $lnamn;
						$brytr++;
						if($brytr >= 4) {
							fwrite($handlarm," \r\n");
							$brytr = 1;
						}	
// och beskrivande feltext
						$larm = "Individen saknar angiven könstillhörighet - Id => "
						.$larmid." - ".$larmnamn;
						fwrite($handlarm,$larm."\r\n");
						fclose($handlarm);
//
					}
					elseif($wsex == '1 SEX O')
					{
						echo "Kön saknas, ändras till M eller F om kön framgår - Id => "
						.$znum." - ".$lnamn." <br/>";
						fwrite($handut,"1 SEX O\r\n");
						$wname = '';
//	Larm
						$larmant++;
						$filelarm=$directory . "Check_lista.txt";
						$handlarm=fopen($filelarm,"a");
						if($larmrub5 == 1) {
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							fwrite($handlarm,$larm."\r\n");
							$larm = "*** F E L  L I S T A  (V) Saknar könstillhörighet";
							fwrite($handlarm,$larm."\r\n");
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larmrub5++;
							$brytr = 0;
						}
// sätt om möjligt id och namn
						$larmid = $znum;
						if($lnamn == '') {
							$lnamn = 'Namn saknas helt';
						}	
						$larmnamn = $lnamn;
						$brytr++;
						if($brytr >= 4) {
							fwrite($handlarm," \r\n");
							$brytr = 1;
						}	
// och beskrivande feltext
						$larm = "Individen saknar angiven könstillhörighet - Id => "
						.$larmid." - ".$larmnamn;
						fwrite($handlarm,$larm."\r\n");
						fclose($handlarm);
//
					}
					else {
						if($wsex != '') {
							fwrite($handut,$wsex."\r\n");
						}	
					}
					if(($wsex != '') && ($wname == '') && ($ztyp == 'IND'))
					{
//						echo "Namn saknas . . . . . . . . . . Id => ".$znum." <br/>";
						fwrite($handut,"1 NAME Saknas /Saknas/\r\n");
						$wsex = '';
					}
					else {
						if($wname != '') {
							fwrite($handut,$wname."\r\n");
						}	
					}
					if($ztyp == 'FAM') {
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
						if($w3ant > 0) {
							for($i=0;$i<$w3ant;$i++)
							{
								$wstr = $w3str[$i];
								fwrite($handut,$wstr."\r\n");
							}	
						}
						$w3ant = 0;
					}
//	Skriv öriga sparade rader	
					if($wrant > 0) {
						for($i=0;$i<$wrant;$i++)
						{
							$wstr = $wwstr[$i];
							fwrite($handut,$wstr."\r\n");
						}	
					}
					$wrant = 0;
//	hitta idnummer för individ/relation
					$ztag = substr($str,0,3);
					if($ztag == '0 @') {
						$wid = $str;
						$wrant=0;
						$wname='';
						$wwname='';
						$wsex='';
						$spar1 = 'NEJ';
						$spar2 = 'NEJ';
						$spar3 = 'NEJ';
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
				}
				else
				{
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
							if($wwname == '') {
								$wname=$str;
								$wwname=$str;
								$llen = strlen($str);
								$lnamn = substr($str,6,$llen);
							}
						}
						elseif(($tagg == 'SEX ') && ($ztyp == 'IND'))
						{	
							$wsex=$str;
							$wwsex=substr($wsex,6,1);
							if(($wwsex != 'M') && ($wwsex != 'F')) {
//								echo 'OBS! Kön "'.$wwsex.'" felaktigt, sök SEX O och 
//								försök rätta till M eller F  . . . . . Id => '.$znum.' - '.$lnamn.' <br/>';
								$str = '1 SEX O';
								$wsex = $str;
							}
						}
						elseif(($tagg == 'MARR') && ($ztyp == 'FAM'))
						{
							$spar1 = 'JA';
							$spar2 = 'NEJ';
							$spar3 = 'NEJ';
							$w1str[$w1ant]=$str;
							$w1ant++;
						}
						elseif(($tagg == 'MARB') && ($ztyp == 'FAM'))
						{
							$spar1 = 'JA';
							$spar2 = 'NEJ';
							$spar3 = 'NEJ';
							$w1str[$w1ant]=$str;
							$w1ant++;
						}
						elseif(($tagg == 'HUSB') && ($ztyp == 'FAM'))
						{
							$spar1 = 'NEJ';
							$spar2 = 'JA';
							$spar3 = 'NEJ';
							$w2str[$w2ant]=$str;
							$w2ant++;
						}
						elseif(($tagg == 'WIFE') && ($ztyp == 'FAM'))
						{
							$spar1 = 'NEJ';
							$spar2 = 'JA';
							$spar3 = 'NEJ';
							$w2str[$w2ant]=$str;
							$w2ant++;
						}
						elseif(($tagg == 'CHIL') && ($ztyp == 'FAM'))
						{
							$spar1 = 'NEJ';
							$spar2 = 'JA';
							$spar3 = 'NEJ';
							$w2str[$w2ant]=$str;
							$w2ant++;
						}
						elseif($tagg == 'CHAN')
						{
							$spar1 = 'NEJ';
							$spar2 = 'NEJ';
							$spar3 = 'JA';
							$w3str[$w3ant]=$str;
							$w3ant++;
						}
//	Bearbeta fam
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
						elseif($spar3 == 'JA')
						{	
//	Spara rader	
							$w3str[$w3ant]=$str;
							$w3ant++;
						}
//	Resten				
						else
						{
//	Spara rader	
							$wwstr[$wrant]=$str;
							$wrant++;
						}
//				
					}		
//	inte IND/FAM				
					else
					{
						fwrite($handut,$str."\r\n");
					}
				}
			}
		}
// TRLR
		fwrite($handut,$str."\r\n");
//		
		if($larmrub5 > 1) {
			$filelarm=$directory . "Check_lista.txt";
			$handlarm=fopen($filelarm,"a");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = "* * * Personer måste ges könstillhörighet för att kunna bearbetas";
			fwrite($handlarm,$larm."\r\n");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fclose($handlarm);
		}
//		
		echo "Program konvordn avslutad <br/n>";
		echo "<br/n>";
		fclose($handin);
		fclose($handut);
	}
	else
	{
		echo "Filen ".$filein." saknades, programmet avbryts <br/>";
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
echo "Program konvtext startad <br/>";
//	
$filein=$directory . "RGDY.GED";
$fileut=$directory . "RGDJ.GED";
//
	if(file_exists($filein))
	{
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
//	Spara orginal
					$orglen = strlen($str);
					$orgtxt = substr($str,7,($orglen-7));
					$orgtxt = '2 PLAC '.$orgtxt;					
					
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
//	Tillägg av mellanslag före parentes					
							if(($sist != ' ') && ($tom == '('))
							{
								$spar = $spar.' ';
								$sist = '';
							}
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
							if(($sist == ' ') && ($tom == ')'))
							{
								$spar=substr($spar,0,(strlen($spar)-1));
								$sist = '';
							}
							if(($sist == '(') && ($tom == ' '))
							{
								$tom = '';
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
// Larm och beskrivande feltext
						$larmid = $znum;
						$larmnamn = $lnamn;
						$larmv++;
						$lrmv[] = "Udda antal parenteser - ".$str.
						" - Id => ".$larmid." - ".$larmnamn;
//
					}
					if($str != $spar)
					{
						$str = $spar;
						$qant++;
					}	
//	Extra tagg RGDX skapas
					$xlen = strlen($str);
					$xtxt = substr($str,7,($xlen-7));
					$xtxt = '2 RGDX '.$xtxt;					
					fwrite($handut,$xtxt."\r\n");
					fwrite($handut,$orgtxt."\r\n");
				}
				else {
					fwrite($handut,$str."\r\n");
				}
			}
		}	
		echo "Program konvtext avslutad <br/n>";
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
$filein=$directory . "RGDJ.GED";
$fileut=$directory . "RGDI.GED";
//
echo "<br/>";
echo "Program konvlkod startad <br/>";
//
	if(file_exists($filein))
	{
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
			if(($tagg == '2 RGDX') && ($akt == 'JA'))
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
		echo "Program konvlkod avslutad <br/>";
		echo "<br/>";
	}
	else
	{
		echo $filein." saknas, programmet avbryts.<br/>";
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
echo "Program konvpsls startad <br/>";
//
$filein=$directory . "RGDI.GED";
$fileut=$directory . "RGDM.GED";
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	if(file_exists($filein))
	{
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
			if(($tagg == '2 RGDX') && ($akt == 'JA'))
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
					$result=mysql_query($SQL);
					if(!$result)
					{
						echo $SQL."fungerande inte".mysql_error();
					}
					else
					{
						$row=mysql_fetch_assoc($result);
						$noid=$row['noid'];
					}	
					if($noid != 0) {
						$str = '2 RGDX '.$utxt.$plan;
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
		echo "Program konvpsls avslutad <br/>";
		echo "<br/>";
//
//
	}
	else
	{
		echo $filein." saknas, programmet avbryts.<br/>";
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
echo "Program konvortf startad <br/>";
//	
$filezz=$directory . "RGDS.GED";
$filein=$directory . "RGDM.GED";
$fileut=$directory . "RGDW.GED";
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
	if(file_exists($filein))
	{
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
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
				if(($tagg == '2 RGDX') && ($akt == 'JA'))
				{
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
							}
							if($len > 0)
							{
								$ort=substr($str,7,($imax-8));
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
						fwrite($handut,"2 RGDX ".$fors.", ".$ort."\r\n");
					}
					else // ingen kombination församling - ort
					{
						fwrite($handut,$str."\r\n");
					}
				}
				else
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
			$result=copy($fileut,$filezz);
			if($result == false) {
				echo "OBS! Filkopieringen misslyckades. <br/>";
				echo "<br/>";
			}
		}		
//
		echo "Program konvortf avslutad <br/n>";
		echo "<br/n>";			
//
	}
	else
	{
		echo "<br/>";
		echo "Filen ".$filein." saknas, programmet avbryts <br/>";
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
echo "Program kompland startad <br/>";
//	
$filein=$directory . "RGDW.GED";
$fileut=$directory . "RGDL.GED";
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
	if(file_exists($filein))
	{
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
				if(($tagg == '2 RGDX') && ($akt == 'JA'))
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
						$SQL="SELECT isoland,noid FROM lande WHERE iso2='$land'";
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
						}
						else
						{
							$rgdp="2 RGDP ".$noid;
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
							$SQL="SELECT isoland,noid FROM lande WHERE land='$ltxt'";
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
//	Nytt försök med land2							
							if($noid == "")
							{
								$isoland="";
								$SQL="SELECT isoland,noid FROM lande WHERE land2='$ltxt'";
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
//	Nytt försök med land3							
							if($noid == "")
							{
								$isoland="";
								$SQL="SELECT isoland,noid FROM lande WHERE land3='$ltxt'";
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
							$SQL="SELECT isoland,noid FROM lande WHERE land='$ltxtx'";
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
//	Nytt försök med ltxtx och land2							
						if($noid == "")
						{
							$isoland="";
							$SQL="SELECT isoland,noid FROM lande WHERE land2='$ltxtx'";
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
//	Nytt försök med ltxtx och land3							
						if($noid == "")
						{
							$isoland="";
							$SQL="SELECT isoland,noid FROM lande WHERE land3='$ltxtx'";
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
//	saknas som land
						}
						else
						{
							$rgdp="2 RGDP ".$noid;
							fwrite($handut,$rgdp."\r\n");
						}
					}	
				}
			}
			fwrite($handut,$str."\r\n");
		}
		echo "Program kompland avslutad <br/n>";
		echo "<br/n>";
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
echo "Program kompdate startad <br/>";
//	
$filein=$directory . "RGDL.GED";
$fileut=$directory . "RGDF.GED";
$filedat=$directory . "date.dat";
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
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
		$handdat=fopen($filedat,"w");
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
		$datcnt=0;
		$llen = '';
		$lnamn = '';
		$akt = 'NEJ';
		$head= 'ON';
		$kant=0;
		$larmd=0;
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
//	Array
					$datlen=strlen($str);
					$datorg=substr($str,7,$datlen);
//
					$akt='NEJ';
					$date=substr($str,0,2);
					$date=$date."RGDD ";
					$tdag='XX';
					$tman='XXX';
					$ttmp='XXX';
					$taar='XXXX';
					$ndag=0;
					$nman=0;
					$ntmp=0;
					$naar=0;
					$ber='JA';
					$cal='NEJ';
					$temp='';
					$tkn='';
					$imax=7;
					$fant=0;
					$len=strlen($str);
					$test = substr($str,7,3);
					for($i=0;$i<count($tst);$i++)
					{
						$x = 20;
						if(($tst[$i]) == $test) {
//	Raden börjar inte med datum eller årtal utan någon text
							$x = $i;
						}
					}
//	Beräkna alla poster och tolka ommöjligt årtal
					if(($ber == 'JA') || ($test == 'CAL'))
					{
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
//	Array 
								$datut = $taar.$tman.$tdag;
								$dat[$datorg]=$datut;
								$datcnt++;
							}	
						}
						else
						{
// konverteringen tar bara exakt / kalkulerat årtal / exakt datum, övriga återskrivs oförändrade
							$ungant++;
							$fant++;
						}	
					}
//
					for($i=0;$i<count($tst);$i++) {
						if(($tst[$i]) == $test)	{
							$ntmp = ($i+1);
							$ttmp = $ntmp;
						}
					}		
//	Särbehandling av ungefärliga tidpunkter
					if(($ntmp >= 13) && ($nman <= 19)) {
// Larm och beskrivande text
						$esttxt = "Ej definitiv tidsangivelse  ".$str;	
						$larmd++;
						$lrmd[] = $esttxt." - Id => ".$znum." - ".$lnamn;
						$fant = 0;
					}
//							
					if($fant > 0)
					{
						$kant++;
						echo "<br/>";
						echo " *  *  *  *  *  Ej korrekt kalenderdatum ".$str.
						" . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
//	Larm
						$larmant++;
						$filelarm=$directory . "Check_lista.txt";
						$handlarm=fopen($filelarm,"a");
						if($larmrub6 == 1) {
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							fwrite($handlarm,$larm."\r\n");
							$larm = "*** F E L  L I S T A  (VI) Datum, ej godkända";
							fwrite($handlarm,$larm."\r\n");
							$larm = " ";
							fwrite($handlarm,$larm."\r\n");
							$larmrub6++;
							$brytr = 0;
						}
// sätt om möjligt id och namn
						$larmid = $znum;
						$larmnamn = $lnamn;
						$brytr++;
						if($brytr >= 4) {
							fwrite($handlarm," \r\n");
							$brytr = 1;
						}	
// och beskrivande feltext
						$larm = "Ej korrekt kalenderdatum ".$str.
						" - Id => ".$larmid." - ".$larmnamn;
						fwrite($handlarm,$larm."\r\n");
						fclose($handlarm);
//
					}			
					else
					{
						$ungant++;
					}
				}	
				$ber='JA';
				$cal='NEJ';
			}
			fwrite($handut,$str."\r\n");
		}
//		
		if($larmrub6 > 1) {
			$filelarm=$directory . "Check_lista.txt";
			$handlarm=fopen($filelarm,"a");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = "* * * Dessa punkter i Check-listan bör kollas och om möjligt åtgärdas";
			fwrite($handlarm,$larm."\r\n");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fclose($handlarm);
		}
//		
		if($kant > 0) {
			echo "<br/>";
			echo $kant." DATE post(er) med felaktigt kalenderdatum hittad, måste rättas. <br/>";
			echo "OBS! informera användaren om de identifierade felaktigheterna. <br/>";
			echo "<br/>";
		}	
		fclose($handin);
		fclose($handut);
//	Array start
		if($datcnt > 0) {
			fwrite($handdat,json_encode($dat)."\r\n");
			fclose($handdat);
		}
		else {
			fwrite($handdat,"{}\r\n");
			fclose($handdat);
			echo "Datuminformation saknas. <br/>";
		}	
//	Array slut
		echo "Program kompdate avslutad <br/>";
		echo "<br/>";
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts. <br/>";
	}
}
?>
<?PHP
/*
OBS! Programmet kompdate måste körts först och skapat RGDD taggen

Tester på felaktig ordningsföld av datum för individ
Test om intervallen av årtal överstiger 110 år
Test om datum större än dd
*/
$filename=$directory . "RGDF.GED";
//
$imax=0;
$dat=0;
$aar=0;
$diff=0;
$faar=0;
$caar=0;
$daar=0;
$baar=0;
$fdat=0;
$cdat=0;
$ddat=0;
$bdat=0;
$dmax=0;
$fnum=0;
$cnum=0;
$dnum=0;
$bnum=0;
$fmin=9999;
$fflag="";
$cflag="";
$dflag="";
$bflag="";
$feltxt="";
//
if(file_exists($filename))
{
	echo "<br/>";
	echo "Program testdate startad <br/>";
	$handle=fopen($filename,"r");
//	Läs in indatafilen				
	$lines = file($filename,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str)
	{
		$pos1=substr($str,0,1);
		$nytt=substr($str,0,3);
		$tagg=substr($str,0,6);
		$tagk=substr($str,0,5);
		$aar=substr($str,7,4);
		$man=substr($str,11,2);
		$dat=substr($str,7,8);
		$dag=substr($str,13,2);
		$dlen = strlen($dat);
		if(($pos1 == '0') || ($pos1 == '1'))
		{
			$fflag="";
			$cflag="";
			$dflag="";
			$bflag="";
		}
		if($tagg == '1 NAME')
		{
			$llen = strlen($str);
			$lnamn = substr($str,6,$llen);
		}
		if($tagg == '1 BIRT')
		{
			$fflag="F";
			$cflag="";
			$dflag="";
			$bflag="";
		}
		if($tagk == '1 CHR')
		{
			$cflag="C";
			$fflag="";
			$dflag="";
			$bflag="";
		}
		if($tagg == '1 DEAT')
		{
			$dflag="D";
			$fflag="";
			$cflag="";
			$bflag="";
		}
		if($tagg == '1 BURI')
		{
			$bflag="B";
			$fflag="";
			$cflag="";
			$dflag="";
		}
		if($tagg == '2 RGDD')
		{
			if($fflag == "F")
			{
				$faar = $aar;
				if($dlen == 8) {
					$fdat = $dat;
					$fnum = 365*$aar + 30*$man + $dag;
				}
				else
				{
					$fnum = 0;
				}	
			}
			if($cflag == "C")
			{
				$caar = $aar;
				if($dlen == 8) {
					$cdat = $dat;
					$cnum = 365*$aar + 30*$man + $dag;
				}	
				else
				{
					$cnum = 0;
				}	
			}
			if($dflag == "D")
			{
				$daar = $aar;
				if($dlen == 8) {
					$ddat = $dat;
					$dnum = 365*$aar + 30*$man + $dag;
				}	
				else
				{
					$dnum = 0;
				}	
			}
			if($bflag == "B")
			{
				$baar = $aar;
				if($dlen == 8) {
					$bdat = $dat;
					$bnum = 365*$aar + 30*$man + $dag;
				}	
				else
				{
					$bnum = 0;
				}	
			}
			$feltxt = '';
			if($dat > date('Ymd')) {
				$feltxt = "Framtida datum ".$dat.", som måste åtgärdas";
//				echo "** Framtida datum ".$dat.", som måste åtgärdas  
//				. . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
			}	
			elseif($aar > date('Y')) {
				$feltxt = "Framtida årtal ".$aar.", som måste åtgärdas";
//				echo "** Framtida årtal ".$aar.", som måste åtgärdas  
//				. . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
			}
//	Larm
			if($feltxt != '') {
				$larmant++;
				$filelarm=$directory . "Check_lista.txt";
				$handlarm=fopen($filelarm,"a");
				if($larmrub7 == 1) {
					$larm = " ";
					fwrite($handlarm,$larm."\r\n");
					fwrite($handlarm,$larm."\r\n");
					$larm = "*** F E L  L I S T A  (VII) Datum, felaktiga";
					fwrite($handlarm,$larm."\r\n");
					$larm = " ";
					fwrite($handlarm,$larm."\r\n");
					$larmrub7++;
					$brytr = 0;
				}
// sätt om möjligt id och namn
				$larmid = $znum;
				$larmnamn = $lnamn;
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}	
// och beskrivande feltext
				$larm = $feltxt.
				" - Id => ".$larmid." - ".$larmnamn;
				fwrite($handlarm,$larm."\r\n");
				fclose($handlarm);
			}	
//
		}
		if($nytt == '0 @')
		{
//	Test intervall
			$inttxt = '';
			if(($cnum > 0) && ($fnum > 0)) {
				$dagtst = $cnum-$fnum;
				if($dagtst > 295) {
					$inttxt = $dagtst." dagars intervall - född ".$fdat." - döpt ".$cdat.", bör kollas ";
//					echo $dagtst." dagars intervall - född ".$fdat." - döpt ".$cdat.", 
//					bör kollas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
// Larm och beskrivande feltext
					$larmi++;
					$lrmi[] = $inttxt." - Id => ".$znum." - ".$lnamn;
//
				}	
			}	
			elseif(($caar > 0) && ($faar > 0)) {
				if($caar > $faar) {
					$dagtst = $caar-$faar;
					$inttxt = $dagtst." års intervall - född ".$faar." - döpt ".$caar.", bör kollas ";
//					echo $dagtst." års intervall - född ".$faar." - döpt ".$caar.", 
//					bör kollas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
// Larm och beskrivande feltext
					$larmi++;
					$lrmi[] = $inttxt." - Id => ".$znum." - ".$lnamn;
//
				}	
			}	
			if(($bnum > 0) && ($dnum > 0)) {
				$dagtst = $bnum-$dnum;
				if($dagtst > 295) {
					$inttxt = $dagtst." dagars intervall - död ".$ddat." -  begravd ".$bdat.", bör kollas ";
//					echo $dagtst." dagars intervall - död ".$ddat." - begravd ".$bdat.", 
//					bör kollas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
// Larm och beskrivande feltext
					$larmi++;
					$lrmi[] = $inttxt." - Id => ".$znum." - ".$lnamn;
//
				}	
			}
			elseif(($baar > 0) && ($daar > 0)) {
				if($baar > $daar) {
					$dagtst = $baar-$daar;
					$inttxt = $dagtst." års intervall - död ".$daar." - begravd ".$baar.", bör kollas ";
//					echo $dagtst." års intervall - död ".$daar." - begravd ".$baar.", 
//					bör kollas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
// Larm och beskrivande feltext
					$larmi++;
					$lrmi[] = $inttxt." - Id => ".$znum." - ".$lnamn;
//
				}	
			}	
//	0-ställ till nästa individ		
			$fnum = 0;
			$cnum = 0;
			$dnum = 0;
			$bnum = 0;
			$dagtst = 0;
//			
//	Test sekvensfel
			$feltxt = '';
			if(($fdat > 0) && ($bdat > 0))
			{
				if($fdat > $bdat)
				{
					$feltxt = "Begravd ".$bdat." före född ".$fdat;
//					echo "** Begravd ".$bdat." före född ".$fdat." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$ddat = 0;
					$cdat = 0;
					$fdat = 0;
				}
			}
			elseif(($faar > 0) && ($baar > 0))
			{
				if($faar > $baar)
				{
					$feltxt = "Begravd ".$baar." före född ".$faar;
//					echo "** Begravd ".$baar." före född ".$faar." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$daar = 0;
					$caar = 0;
					$faar = 0;
				}
			}
			if(($cdat > 0) && ($bdat > 0))
			{
				if($cdat > $bdat)
				{
					$feltxt = "Begravd ".$bdat." före döpt ".$cdat;
//					echo "** Begravd ".$bdat." före döpt ".$cdat." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$ddat = 0;
					$cdat = 0;
				}
			}
			elseif(($caar > 0) && ($baar > 0))
			{
				if($caar > $baar)
				{
					$feltxt = "Begravd ".$baar." före döpt ".$caar;
//					echo "** Begravd ".$baar." före döpt ".$caar." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$daar = 0;
					$caar = 0;
				}
			}
			if(($bdat > 0) && ($ddat > 0))
			{
				if($ddat > $bdat)
				{
					$feltxt = "Begravd ".$bdat." före död ".$ddat;
//					echo "** Begravd ".$bdat." före död ".$ddat." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$ddat = 0;
				}
			}
			elseif(($baar > 0) && ($daar > 0))
			{
				if($daar > $baar)
				{
					$feltxt = "Begravd ".$baar." före död ".$daar;
//					echo "** Begravd ".$baar." före död ".$daar." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$daar = 0;
				}
			}
			if(($fdat > 0) && ($ddat > 0))
			{
				if($fdat > $ddat)
				{
					$feltxt = "Död ".$ddat." före född ".$fdat;
//					echo "** Död ".$ddat." före född ".$fdat." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$cdat = 0;
					$fdat = 0;
				}
			}
			elseif(($faar > 0) && ($daar > 0))
			{
				if($faar > $daar)
				{
					$feltxt = "Död ".$daar." före född ".$faar;
//					echo "** Död ".$daar." före född ".$faar." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$caar = 0;
					$faar = 0;
				}
			}
			if(($cdat > 0) && ($ddat > 0))
			{
				if($cdat > $ddat)
				{
					$feltxt = "Död ".$ddat." före döpt ".$cdat;
//					echo "** Död ".$ddat." före döpt ".$cdat." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$cdat = 0;
				}
			}
			elseif(($caar > 0) && ($daar > 0))
			{
				if($caar > $daar)
				{
					$feltxt = "Död ".$daar." före döpt ".$caar;
//					echo "** Död ".$daar." före döpt ".$caar." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
					$caar = 0;
				}
			}
			if(($fdat > 0) && ($cdat > 0))
			{
				if($fdat > $cdat)
				{
					$feltxt = "Döpt ".$cdat." före född ".$fdat;
//					echo "** Döpt ".$cdat." före född ".$fdat." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
				}
			}
			elseif(($faar > 0) && ($caar > 0))
			{
				if($faar > $caar)
				{
					$feltxt = "Döpt ".$caar." före född ".$faar;
//					echo "** Döpt ".$caar." före född ".$faar." är omöjligt och det måste 
//					åtgärdas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
				}
			}
//	Larm
			if($feltxt != '') {
				$larmant++;
				$filelarm=$directory . "Check_lista.txt";
				$handlarm=fopen($filelarm,"a");
				if($larmrub7 == 1) {
					$larm = " ";
					fwrite($handlarm,$larm."\r\n");
					fwrite($handlarm,$larm."\r\n");
					$larm = "*** F E L  L I S T A  (VII) Datum, felaktiga";
					fwrite($handlarm,$larm."\r\n");
					$larm = " ";
					fwrite($handlarm,$larm."\r\n");
					$larmrub7++;
					$brytr = 0;
				}
// sätt om möjligt id och namn
				$larmid = $znum;
				$larmnamn = $lnamn;
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}	
// och beskrivande feltext
				$larm = $feltxt.
				" - Id => ".$larmid." - ".$larmnamn;
				fwrite($handlarm,$larm."\r\n");
				fclose($handlarm);
			}	
//
			$fmin=9999;
			$dmax=0;
			if(($faar > 0) && ($fmin > $faar)) {
				$fmin = $faar; }
			if(($caar > 0) && ($fmin > $caar)) {
				$fmin = $caar; }
			if(($daar > 0) && ($fmin > $daar)) {
				$fmin = $daar; }
			if(($baar > 0) && ($fmin > $baar)) {
				$fmin = $baar; }
//
			if($dmax < $faar) {
				$dmax = $faar; }
			if($dmax < $caar) {
				$dmax = $caar; }
			if($dmax < $daar) {
				$dmax = $daar; }
			if($dmax < $baar) {
				$dmax = $baar; }
//		
			$feltxt = '';
			if(($fmin > 0) && ($fmin < 9999) && ($dmax > 0)) {
				$diff=($dmax-$fmin);
				if($diff > 110)
				{
					$feltxt = "År ".$fmin." - ".$dmax." ger en diff av ".$diff." år";
//					echo "* År ".$dmax." minus ".$fmin." ger en diff på ".$diff.
//					" år, bör kollas . . . . . . . . . . Id => ".$znum." - ".$lnamn." <br/>";
				}
			}	
//	Larm
			if($feltxt != '') {
				$larmant++;
				$filelarm=$directory . "Check_lista.txt";
				$handlarm=fopen($filelarm,"a");
				if($larmrub7 == 1) {
					$larm = " ";
					fwrite($handlarm,$larm."\r\n");
					fwrite($handlarm,$larm."\r\n");
					$larm = "*** F E L  L I S T A  (VII) Datum, felaktiga";
					fwrite($handlarm,$larm."\r\n");
					$larm = " ";
					fwrite($handlarm,$larm."\r\n");
					$larmrub7++;
					$brytr = 0;
				}
// sätt om möjligt id och namn
				$larmid = $znum;
				$larmnamn = $lnamn;
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}	
// och beskrivande feltext
				$larm = $feltxt.
				" - Id => ".$larmid." - ".$larmnamn;
				fwrite($handlarm,$larm."\r\n");
				fclose($handlarm);
			}	
//
			$lnamn = "Familj";
			$fflag = "";
			$cflag = "";
			$dflag = "";
			$bflag = "";
			$faar = 0;
			$caar = 0;
			$daar = 0;
			$baar = 0;
			$fdat = 0;
			$cdat = 0;
			$ddat = 0;
			$bdat = 0;
//			$dmax = 0;
//			$fmin = 9999;
//
			$imax=3;
			$znum='';
			while($imax <= 20)
			{
				$sna=substr($str,$imax,1);
				if($sna == '@')
				{
					$imax++;
					$imax=20;	
				}
				else
				{
					$znum = $znum.$sna;
					$imax++;
				}
			}
		}
	}
	fclose($handle);
//
	if($larmrub7 > 1) {
		$filelarm=$directory . "Check_lista.txt";
		$handlarm=fopen($filelarm,"a");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * Dessa punkter i Check-listan bör kollas och åtgärdas";
		fwrite($handlarm,$larm."\r\n");
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
//
	echo "Program testdate avslutad <br/>";
	echo " <br/>";
}                                               
else
{
	echo $filename." saknas, programmet avslutas.<br/>";
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
echo "Program kompfnor startad <br/>";
//	
$filein=$directory . "RGDF.GED";
$fileut=$directory . "RGDE.GED";
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
		$aaar=0;
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
				if(($tagg == '2 RGDX') && ($akt == 'JA'))
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
						$result=mysql_query($SQL);
						if(!$result)
						{
							echo $SQL."fungerande inte".mysql_error();
							
						}
						else
						{
							$row=mysql_fetch_assoc($result);
							$noid=$row['noid'];
						}	
						if($noid == 0)
						{
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
							$rgdp="2 RGDP ".$noid;
							fwrite($handut,$rgdp."\r\n");
						}
					}
				}
			}
		fwrite($handut,$str."\r\n");
		}
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
//	Avslutande fix
//	
$filein=$directory . "RGDE.GED";
$fileut=$directory . "RGD8.GED";
$filepla=$directory . "plac.dat";
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
		$handpla=fopen($filepla,"w");
//
		$placnt = 0;
		$platxt = '';
		$plaorg = '';
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
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
					$platxt = substr($str,7,($len-7));
				}
				if(($tagg == '2 PLAC') && ($akt == 'JA'))
				{
					if($platxt != '') {
						$plaorg = substr($str,7,($len-7));
//	Array 
						$pla[$plaorg]=$platxt;
						$placnt++;
//
						$platxt = '';
						$plaorg = '';
					}
					else {
						echo "RGDP blank eller saknas för /".$str."/ <br/>";
					}
				}
				if($tagg == '2 RGDX')
				{
//	Skippa
				}
				else 
				{
					fwrite($handut,$str."\r\n");
				}
		}
		fclose($handin);
		fclose($handut);
//
//	Array start
		if($placnt > 0) {
			fwrite($handpla,json_encode($pla)."\r\n");
			fclose($handpla);
		}
		else {
			fwrite($handpla,"{}\r\n");
			fclose($handpla);
			echo "Församlingsinformation saknas. <br/>";
		}	
//	Array slut
//			
		echo "Program kompfnor avslutad <br/n>";
		echo "<br/n>";
		echo "Filen ".$fileut." har skapats <br/n>";
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
	$handle=fopen($filename,'r');
	echo '<br/>';
	echo 'Program testantr startad <br/>';
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
					$fellista[] = '1Id '.$id.' - Familj';
					$infoant++;
				}
				if(($post == 'FAM') && ($cntfam == 1)) {
					$fellista[] = '2Id '.$id.' - Familj'; 
					$infoant++;
				}
				if(($post == 'IND') && ($cntind == 0)) {
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
//	Varningslista ologiskt placerad för att få rätt sekvens vid utskrift
	if($larmi > 0)
	{
//	Larm
		$brytr = 0;
		$filelarm=$directory . "Check_lista.txt";
		$handlarm=fopen($filelarm,"a");
		if($larmrub8 == 1) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fwrite($handlarm,$larm."\r\n");
			$larm = "*** V A R N I N G  (VIII) Datum, intervall";
			fwrite($handlarm,$larm."\r\n");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larmrub8++;
		}
		if($larmi > 0) {
			$lrmlisti=array_unique($lrmi);
			foreach($lrmlisti as $lrmradi) {
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}
				fwrite($handlarm,$lrmradi." \r\n");
				$larmant++;
			}
		}
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * Dessa punkter i Check-listan bör kollas och vid behov åtgärdas";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
//
//	Varningslista ologiskt placerad för att få rätt sekvens vid utskrift
		if($larmd > 0)
		{
//	Larm
			$brytr = 0;
			$filelarm=$directory . "Check_lista.txt";
			$handlarm=fopen($filelarm,"a");
			if($larmrub9 == 1) {
				$larm = " ";
				fwrite($handlarm,$larm."\r\n");
				fwrite($handlarm,$larm."\r\n");
				$larm = "*** I N F O R M A T I O N  (IX) Datum, opreciserat";
				fwrite($handlarm,$larm."\r\n");
				$larm = " ";
				fwrite($handlarm,$larm."\r\n");
				$larmrub9++;
			}
			if($larmd > 0) {
				$lrmlistd=array_unique($lrmd);
				foreach($lrmlistd as $lrmradd) {
					$brytr++;
					if($brytr >= 4) {
						fwrite($handlarm," \r\n");
						$brytr = 1;
					}
					fwrite($handlarm,$lrmradd." \r\n");
					$larmant++;
				}
			}
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larm = "* * * Datum som bör justeras till exakt tidsangivelse, om möjlighet finns.";
			fwrite($handlarm,$larm."\r\n");
			fclose($handlarm);
		}
//	
//	Varningslista ologiskt placerad för att få rätt sekvens vid utskrift
	if($larmv > 0)
	{
//	Larm
		$brytr = 0;
		$filelarm=$directory . "Check_lista.txt";
		$handlarm=fopen($filelarm,"a");
		if($larmrub10 == 1) {
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			fwrite($handlarm,$larm."\r\n");
			$larm = "*** V A R N I N G  (X) Text";
			fwrite($handlarm,$larm."\r\n");
			$larm = " ";
			fwrite($handlarm,$larm."\r\n");
			$larmrub10++;
		}
		if($larmv > 0) {
			$lrmlistv=array_unique($lrmv);
			foreach($lrmlistv as $lrmradv) {
				$brytr++;
				if($brytr >= 4) {
					fwrite($handlarm," \r\n");
					$brytr = 1;
				}
				fwrite($handlarm,$lrmradv." \r\n");
				$larmant++;
			}
		}
		$larm = " ";
		fwrite($handlarm,$larm."\r\n");
		$larm = "* * * Texten kan därför feltolkas i bearbetningen.";
		fwrite($handlarm,$larm."\r\n");
		fclose($handlarm);
	}
//	
	if($infoant > 0) {
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
		echo '* * * Lista Info.txt med okomplett data har skapats. <br/>';
	}	
//
	echo 'Program testantr avslutad <br/>';
	echo '<br/>';
	echo "Program xprsstrtz avslutad * * * * * <br/>";
	echo "<br/n>";
//
}
else
{
	echo $filename.' saknas, programmet avbryts.<br/>';
}
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
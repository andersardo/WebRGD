<?PHP
/*
Programmet behandlar specialtaggen RGDS (I fixen används PLAC i stället för RGDX)

Programmet lägger samman PLAC och SOUR för att få med församling i källan
Årtal för händelsen tas också in för att kunna verifiera 1:sta hands källor korrekt.
Annars läggs en ren kopia av SOUR in så att det alltid skall finnas en RGDS.

*/
require 'initbas.php';
require 'initdb.php';
//
$filein=$directory . "RGD9X.GED";
$fileut=$directory . "RGD9Y.GED";
//
$akt='NEJ';
$typ='';
$bild='';
$sida='';
$tant=0;
$pant=0;
$sant=0;
$kant=0;
$gant=0;
$xant=0;
$zant=0;
$sok2=0;
$sok='';
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
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
			$pos1=substr($str,0,1);
			$tagk=substr($str,0,5);
			$tagg=substr($str,0,6);
			$tagl=substr($str,0,8);
 			if($pos1 == '0')
			{
				$ptmp="";
				$pmix="";
				$smix="";
				$skol="";
				$stst="";
				$bbok="";
				$brst="";
				$book='';
				$bmix='';
				$fors='';
				$sbok='';
			}	
 			if(($pos1 == '0') || ($pos1 == '1'))
			{
				$akt = 'NEJ';
				$typ = '';
				$aar = '';
				$id="";
				$sord="";
				$snum="";
//	Fler ??
			}
			if(($tagg == '1 BIRT' ) || ($tagk == '1 CHR'))
			{
				$akt = 'JA';
				$typ = 'F';
				$tant++;
			}
			if(($tagg == '1 DEAT') || ($tagg == '1 BURI'))
			{
				$akt = 'JA';
				$typ = 'D';
				$tant++;
			}
			if($tagg == '1 MARR')
			{
				$akt = 'JA';
				$typ = 'V';
				$tant++;
			}
			if(($tagg == '2 RGDD') && ($akt == 'JA'))
			{
				$aar = substr($str,7,4);
			}
//	Org.	if(($tagg == '2 RGDX') && ($akt == 'JA'))
			if(($tagg == '2 PLAC') && ($akt == 'JA'))
			{
				$pant++;
 				fwrite($handut,$str."\r\n");
//	Spara enbart församlingsnamnet utan länsbokstav	
				$plen=strlen($str);
				$pmax=7;
				$id="";
				$ptmp="";
				$pmix="";
				while($pmax <= $plen)
				{
					$ptkn = substr($str,$pmax,1);
					$ptk2 = substr($str,$pmax,2);
					if($ptkn == ")") {
						$id = $pmix.$ptkn;
						$pmax = $plen;
					}
					if($ptk2 == " (") {
						$ptmp = $pmix;
					}
					$pmix = $pmix.$ptkn;
					$pmax++;
				}	
			}
 			elseif(($tagl=='2 RGDS *') && ($akt == 'JA'))
			{
//	Återskriv redan behandlad rad
				fwrite($handut,$str."\r\n");
//	och bearbeta ej vidare
				$akt = 'NEJ';
				if(substr($str,8,1) == '6') {
					$kant++;
				}
				else {
					$xant++;
				}
				$sant++;
			}
 			elseif(($tagg=='2 RGDS') && ($akt == 'JA'))
			{
				$slen = strlen($str);
				$otkn = '';
				$strtmp = '';
				$smax = 0;
				while($smax <= $slen)
				{
//	skippa ev. dubbla mellanslag
					$stkn = substr($str,$smax,1);
					if($stkn == ' ') {
						if($stkn != $otkn) {
							$strtmp = $strtmp.$stkn;
						}	
					}
					else {
						$strtmp = $strtmp.$stkn;
					}	
					$otkn = $stkn;
					$smax++;
				}
				$str = $strtmp;
//				
				$sant++;
				$akt='NEJ';
				$sok = '';
				$sok2 = 0;
//	Hitta kyrkbok
				$slen=strlen($str);
				$smax=7;
				$smix="";
				$skol="";
				$stst="";
				$sord="";
				$snum=""; 
				$bbok="";
				$brst="";
				$oldx="";
				$sold="";
				while($smax <= $slen)
				{
					$stkn = substr($str,$smax,1); 
					$stkn2 = substr($str,$smax,2); 
					if(($stkn == ":") || ($stkn == " ") || ($stkn == ",") || 
					($stkn == "/") || ($stkn == ";")) 
					{
						if(($skol == '') && ($stkn == ' ')) {
							$oldx = $sord;
							$sord = '';
						}
						else {
							if($stkn == ':') {
								if(($sord == 'AID') || ($sord == 'GID') || ($sord == 'Bildid'))  {
//	Undantag
									$sord = '';
								}
								else {
									if($stkn2 != ' ') {
										$skol = 'JA';
										if($oldx == 'län') {
											$sold = 'XXX';
										}
										else {
											$sold = $oldx;
										}	
									}	
								}	
							}         
							if(($skol == 'JA') && (($stkn == ' ') || ($stkn == ',') || 
							($stkn == "/") || ($stkn == ";"))) {
								if($bbok == '') {
									$bbok = $sord;
									$smix = '';
								}
							}
							$sord=$sord.$stkn;
						}
//	Inkl. skiljetecken							
						$smix=$smix.$stkn;
					}
					else
					{
						$smix=$smix.$stkn;
						$sord=$sord.$stkn;
					}
//	Kyrkböcker slutar inte med kolon
					$olen=strlen($bbok);
					if($olen > 0) {
						$opos=substr($bbok,($olen-1),1);
						if($opos == ':') {
							$bbok='';
							$skol='';
						}	
					}	
//	texten slut men kolon finns 					
					if(($smax == $slen) && ($skol == 'JA'))
					{
						if($bbok == '') {
							$bbok = $smix;
						}
						else {
							$brst = $smix;
						}
					}
					$smax++;
				}
//	Sök ptmp strängen			
				$tlen=strlen($ptmp);
				$slen=strlen($str);
				$smax=7;
				while($smax <= $slen)
				{
					$ttxt=substr($str,$smax,$tlen);
					if($ptmp == $ttxt) {
						$sold=$ptmp;
						$smax=$slen;
					}	
					$smax++;
				}
				if($skol == 'JA') {
//	Sök sida och bild
					$sida = '';
					$bild = '';
					if((substr($brst,0,1)) == '/') {
						if(((substr($brst,2,1)) >= '0') && ((substr($brst,2,1)) <= '9')) {
							$sida = 'JA';
						}	
					}
					$biok = '';
					$siok = '';
					$snum = '';
					$xnum = '';
					$rmax = 0;
					$xmax = 0;
					$rlen = strlen($brst);
					while($rmax <= $rlen)
					{
						$rtkn1 = substr($brst,$rmax,1); 
						$rtkn3 = substr($brst,$rmax,3);
						$rtkn4 = substr($brst,$rmax,4);
						$rtkn5 = substr($brst,$rmax,5);
						if(($rtkn3 == ' s.') || ($rtkn3 == ' S.')){
							$sida = 'JA';
							$bild = '';
							$xmax = $rmax;
						}
						if(($rtkn3 == ' s ') || ($rtkn3 == ' S ')){
							$sida = 'JA';
							$bild = '';
							$xmax = $rmax;
						}
						if(($rtkn4 == ' sid') || ($rtkn4 == ' Sid')){
							$sida = 'JA';
							$bild = '';
							$xmax = $rmax;
						}
						if(($rtkn5 == ' bild') || ($rtkn5 == ' Bild')){
							if($sida == '') {
								$bild = 'JA';
								$xmax = $rmax;
							}	
						}
						if(($sida == 'JA') || ($bild == 'JA')){
							if(($rtkn1 >= '0') && ($rtkn1  <= '9') && ($rmax >= $xmax)) {
								$xnum = $xnum.$rtkn1;
							}
							if(($xnum != '') && 
							(($rtkn1  == ' ') || ($rtkn1  == '/') || 
							($rtkn1  == ';') || ($rtkn1  == '-') || 
							($rtkn1  == ',') || ($rmax == $rlen))) {
								if(($bild == 'JA') && ($sida == '') && ($biok == '')) {
									$snum = $xnum;
									$xnum = '';
									$biok = 'OK';
								}
//	Sida högre status							
								if(($sida == 'JA') && ($siok == '')){
									$snum = $xnum;
									$xnum = '';
									$siok = 'OK';
								}	
							}
						}
						$rmax++;
					}
//				
					$blen=strlen($bbok);
					$bmax=$blen-1;
					$book='';
					$bmix='';
					$fors='';
					while($bmax >= 0)
					{
						$btkn = substr($bbok,$bmax,1); 
						if(($btkn == ' ') && ($book == '')) 
						{
							$book = $bmix;
							$bmix = '';
							$btkn = '';
						}
						$bmix=$btkn.$bmix;
						$bmax--;
					}
					if($book == '') {
						$book = $bmix;
					}
					else {
						$fors = $bmix;
					}	
					$bmix = '';
//				
					$sbok = '';
					if($fors == '') {
						$sbok = $ptmp.' '.$book;
						$fors = $ptmp;
					}
					else {
						$sbok = $fors.' '.$book;
					}
				}
//	Select
				$fbok = '';
				$sok = '';
				$sok2 = 0;
				if($skol == 'JA' ) {
					$sok = '*';
					$sok2 = 6;
//					$kant++;
				}
				if(($id != '') && ($typ != '') && ($aar != '') && ($skol == 'JA')) {
					$SQL="SELECT fbok,bok,lkod,adid FROM adreg WHERE 
					id='$id' AND typ='$typ' AND fint <= '$aar' AND tint >= '$aar'";
					$result=mysqli_query($link,$SQL);
					if(!$result)
					{
						echo $SQL."fungerande inte".mysqli_error($link);
					}
					else
					{
						$rant = mysqli_num_rows($result);
						$rlop = 0;
						while($row=mysqli_fetch_assoc($result)) {
							$fbok=$row['fbok'];
							$bok=$row['bok'];
//							$lkod=$row['lkod'];
//							$adid=$row['adid'];
//
							if($fbok != "")
							{
								if($sbok == $fbok) {
									$sok2 = 3;
								}
							}
							$rlop++;
						}
//	stoppa if-satsen
					}
				}
				if($sok2 == 3) { 
					$gant++;
				}
				elseif($sok2 == 6) {
					$kant++;
				}
				else
				{	
					$zant++;
				}
//					
				if($sida == 'JA') {
					$sok2 = $sok2 - 2;
				}
				else {
					if($bild == 'JA') {
						$sok2 = $sok2 - 1;
					}
				}
//
				$umax = 7;
				$ulen = strlen($str);
				while($umax <= $ulen)
				{
					$utkn = substr($str,$umax,1);
					if($utkn != " ") {
						$utx = substr($str,$umax,$ulen-$umax);
						$umax = $ulen;
					}
					$umax++;
				}
				if($sok == '*') {
					$sok = $sok.$sok2.' ';
					if(($fors == '') && ($sold == '')) {
						$sold = $oldx;
					}
					if(($sold == '') || (($sok2 >= 1) && ($sok2 <= 3))){
						$stxt = $fors.' '.$book;
					}
					else {
						$stxt = $sold.' '.$book;
					}	
					$ssid = '';
					if(($snum != '') && ($bild == 'JA')) {
						$ssid = ', Bild '.$snum;
					}	
					if(($snum != '') && ($sida == 'JA')) {
						$ssid = ', Sidan '.$snum;
					}	
					fwrite($handut,"2 RGDS ".$sok.$stxt.$ssid." \r\n");
				}	
				else {
					fwrite($handut,"2 RGDS ".$utx."\r\n");
					$fellista[] = $utx;
				}
//
			}
			else
			{
				fwrite($handut,$str."\r\n");
			}
		}
		fclose($handin);
		fclose($handut);
/*		echo "<br/>";
		echo "Församlingar på: ".(int)($pant/$tant*100+0.5)."% av F-V-D händelser <br/>";
		echo "<br/>";
		echo "Med angivna källor: ".$sant." (".(int)($sant/$tant*100+0.5)."%) <br/>";
		echo "Identifierade originalkällor ".$gant." (".(int)($gant/$sant*100+0.5)."%) <br/>";
		echo "Övriga sekundära kyrkbokskällor ".$kant." (".(int)($kant/$sant*100+0.5)."%) <br/>";
		echo "Övriga sekundära noterade källor ".$xant." (".(int)($xant/$sant*100+0.5)."%) <br/>";
		echo "Övriga oidentifierade källor ".$zant." (".(int)($zant/$sant*100+0.5)."%) <br/>";
		echo "<br/>";
		echo "Filen ".$fileut." har skapats <br/>";
		echo "<br/>";
//
		if($zant > 0) {
			echo "<br/>";
			echo "Oidentifierade källor listas: <br/>";
			$radbr = 0;
			$fellista=array_unique($fellista);
			sort($fellista);
			echo "<br/>";
			foreach($fellista as $felrad) {
				echo $felrad."<br/>";
				$radbr++;
				if($radbr == 3) {
					echo "<br/>";
					$radbr = 0;
				}	
			}
			echo "<br/>";
		}*/
		echo "<br/>";
		echo "Program komprgdsz avslutad <br/>";
/*			
		$filelogg=$directory . "RGDlogg.txt";
		$handlog=fopen($filelogg,"a");
		$text = "Program komprgdsz avslutad ";
		fwrite($handlog,$text.date('Y-m-d')." / ".date('H:i:s')."\r\n");
		fclose($handlog);
*/
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts <br/>";
	}
}
?>
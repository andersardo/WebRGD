<?PHP
/*
Programmet behandlar specialtaggen RGDS

Programmet lägger samman PLAC och SOUR för att få med församling i källan
Årtal för händelsen tas också in för att kunna verifiera 1:sta hands källor korrekt.
Annars läggs en ren kopia av SOUR in så att det alltid skall finnas en RGDS.

*****
***** Fixa kända olikheter Disgen-Källa
*****

*/
require 'initbas.php';
require 'initdb.php';
//
$filein=$directory . "RGD9Y.GED";
$fileut=$directory . "RGD9Z.GED";
//
$rgds = '';
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
$spar='';
$xyz = '';
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
				$spar=$str;
//
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
				$xyz = '';
//
				$rgds = '';
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
//	DATE sista 4
//			if(($tagg == '2 RGDD') && ($akt == 'JA'))
			if(($tagg == '2 DATE') && ($akt == 'JA'))
			{
				$dlen = strlen($str);
				$aar = substr($str,$dlen-4,4);
//echo $aar.'<br/>';
			}
//	Bidrag	if(($tagg == '2 RGDX') && ($akt == 'JA'))
  			if(($tagg == '2 PLAC') && ($akt == 'JA'))
			{
				$pant++;
 				fwrite($handut,$str."\r\n");
//
//	Expandera förkortningar
					$len=strlen($str);
					$strorg = substr($str,7,$len);
					$strx = '';
					$lxx = '';
					$imax=7;
					$fors="";
					while($imax <= $len)
					{
						$tkn=substr($str,$imax,1);
						$kfs=substr($str,($imax+1),3);
						$kfs1=substr($str,($imax+4),1);
						$kfl=substr($str,($imax+1),4);
						$kfl1=substr($str,($imax+5),1);
//	Fix för att rädda församlingar som är förkortade till "...förs" eller "lfs", "sfs" eller "dkf/s"				
						if(($tkn == ' ') || ($tkn == '.') || (($imax+1) == $len))
						{
							if((substr($fors,($imax-12),5)) == 'förs')
							{
								if($tkn == '.') {
									$fors = $fors.'amlin';
									$tkn = 'g';
								}
								else {
									$fors = $fors.'amling';
								}
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
								if($kfs1 == '.') {
									$imax++;
								}	
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
								if($kfs1 == '.') {
									$imax++;
								}	
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
								if($kfl1 == '.') {
									$imax++;
								}	
							}
							elseif(($kfs == 'dkf') || ($kfs == 'DKF') || ($kfs == 'Dkf')) 
							{
								$fors = $fors.' domkyrkoförsamling';
								$imax++;
								$imax++;
								$imax++;
								$imax++;
								if($kfl1 == '.') {
									$imax++;
								}	
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
								$imax++;
							}
							$fors=$fors.$tkn;
						}	
						else
						{
							$fors=$fors.$tkn;
							$lxx = $lxx.$tkn;
						}
						$imax++;
					}
//	Ta bort prefix
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
//	Spara enbart församlingsnamnet med/utan länsbokstav	
				$plen=strlen($str);
				$pmax=7;
//				$id="";
				$pmix="";
				while($pmax <= $plen)
				{
					$ptkn = substr($str,$pmax,1);
					$ptk2 = substr($str,$pmax,2);
//	Skippa inledande text
					if($ptk2 == ", ") {
						$pmix = "";
						$ptkn = "";
						$pmax++;
					}
					if($ptkn == ")") {
						$id = $pmix.$ptkn;
						$pmax = $plen;
					}
					$pmix = $pmix.$ptkn;
					$pmax++;
				}	
//	***** Fixa kända olikheter Disgen-Källa
				if($id == "Kalmar domkyrkoförsamling (H)") {
					$id = "Kalmar stadsförsamling (H)";
				}
				if($id == 'Visby domkyrkoförsamling (I)') {
					$id = 'Visby stadsförsamling (I)';
				}
//	*****		
//	Skapa ptmp
				$plen=strlen($id);
				$pmax=0;
				$ptmp="";
				$pmix="";
				while($pmax <= $plen)
				{
					$ptkn = substr($id,$pmax,1);
					$ptk2 = substr($id,$pmax,2);
					if($ptk2 == " (") {
						$ptmp = $pmix;
						$pmax = $plen;
					}
					$pmix = $pmix.$ptkn;
					$pmax++;
				}	
			}
 			elseif(($tagl=='2 RGDS *') && ($akt == 'JA'))
 			{
//	Återskriv redan behandlad rad
				fwrite($handut,$str."\r\n");
				$rgds = 'J';
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
				$old2="";
				while($smax <= $slen)
				{
					$stkn = substr($str,$smax,1); 
					$stkn2 = substr($str,$smax,2); 
					if(($stkn == ":") || ($stkn == " ") || ($stkn == ",") || 
					($stkn == "/") || ($stkn == ";")) 
					{
						if(($skol == '') && ($stkn == ' ')) {
							$oldx = $sord;
							if($old2 == '') {
								$old2 = $sord;
							}
							else {
								
								$old2 = $old2.' '.$sord;
							}
							$sord = '';
						}
						else {
							$skip = 'N';
//	Skip S: normalt Sankt
							if($stkn == ':') {
								if($sord == 'S') {
									$skip = 'J';
								}
//	Skip Reg: normalt ej kyrkbok
								if(($sord == 'Reg') || ($sord == 'reg')) {
									$skip = 'J';
								}
							}
							if(($stkn == ':') && ($skip == 'N')){
								if($stkn2 != ' ') {
									$skol = 'JA';
									$sold = $old2;
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
						if(($rtkn3 == ' s.') || ($rtkn3 == ' S.') | ($rtkn3 == ' p.') || ($rtkn3 == ' P.')){
							$sida = 'JA';
							$bild = '';
							$xmax = $rmax;
						}
						if(($rtkn3 == ' s ') || ($rtkn3 == ' S ') || ($rtkn3 == ' p ') || ($rtkn3 == ' P ')){
							$sida = 'JA';
							$bild = '';
							$xmax = $rmax;
						}
						if(($rtkn4 == ' sid') || ($rtkn4 == ' Sid') || ($rtkn4 == ' pag') || ($rtkn4 == ' Pag')){
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
//	Alternativ test
					$altx = strlen($book);
					$alt1 = substr($book,0,1);
					$alt2 = substr($book,1,1);
					$altr = substr($book,1,$altx);
					if($alt2 == ':') {
						$balt = $alt1.'I'.$altr;
					}
					else {
						$balt = $book;
					}
//			
					$sbok = '';
					if($fors == '') {
						if($old2 == '') {
							$sbok = $ptmp.' '.$book;
							$salt = $ptmp.' '.$balt;
							$fors = $ptmp;
						}
						else {
							$sbok = $old2.' '.$book;
							$salt = $old2.' '.$balt;
							$fors = $ptmp;
						}
					}
					else {
						if($old2 == '') {
							$sbok = $fors.' '.$book;
							$salt = $fors.' '.$balt;
						}
						else {
							$sbok = $old2.' '.$book;
							$salt = $old2.' '.$balt;
						}
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
								else {
//	Testa alternativet
									if($salt == $fbok) {
										$book = $balt;
										$sok2 = 3;
									}
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
					if($old2 == '') {
						$old2 = $fors;
					}
					$stxt = $old2.' '.$book;
					if(($fors == '') && ($sold == '')) {
						$sold = $old2;
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
//	ej *
					if($rgds == '') {
						fwrite($handut,$str."\r\n");
						$utx = substr($str,7,strlen($str));
						$fellista[] = $utx;
					}
					else {
echo '***** Borde vara utskriven i tidigare skede <br/>';
//						fwrite($handut,$str."\r\n");
					}
				}
//
			}
			else
			{
//	akt ej JA
				fwrite($handut,$str."\r\n");
			}
		}
		fclose($handin);
		fclose($handut);
//
		echo "<br/>";
		echo "Församlingar på: ".(int)($pant/$tant*100+0.5)."% av F-V-D händelser <br/>";
		echo "<br/>";
		if($sant > 0) {
			echo "Med angivna källor: ".$sant." (".(int)($sant/$tant*100+0.5)."%) <br/>";
			echo "Identifierade originalkällor ".$gant." (".(int)($gant/$sant*100+0.5)."%) <br/>";
			echo "Övriga sekundära kyrkbokskällor ".$kant." (".(int)($kant/$sant*100+0.5)."%) <br/>";
			echo "Övriga sekundära noterade källor ".$xant." (".(int)($xant/$sant*100+0.5)."%) <br/>";
			echo "Övriga oidentifierade källor ".$zant." (".(int)($zant/$sant*100+0.5)."%) <br/>";
		}
		else {
			echo 'Källor saknas <br/>';
		}
		echo "<br/>";
		echo "Filen ".$fileut." har skapats <br/>";
		echo "<br/>";
//	zz
/*
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
		}
*/	
//	zz	
		echo "<br/>";
		echo "Program komprgdsz avslutad <br/>";
//
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
<?PHP
/*
Programmet skall bryta isär förnamn och efternamn på samma sätt som måste göras
före normering av namn.

Programmet går direkt mot DISNAMN via en REST funktion 
*******************
	OBS! 
	Programmet förutsätter att teckenrepresentationen för åäöÅÄÖ skall vara
	konverterade (programmet konvutf8 skall vara kört om teckenrepresentationen
	inte är UTF-8)
	Taggarna SEX och NAME måste komma i "rätt" ordning (testordn och ev.även konvordn).
	OBS!

Programmet tar också bort extratecken som registrerats, dock ej kolon.

I denna version tas även asterisken för markering av tilltalsnamn bort,
men denna funktion kanske skall finnas kvar i någon form.

I denna version bryter den också isär sammansatta namn,
hur detta skall hanteras, måste normeringsansvariga ta hänsyn till.

*/
require 'initbas.php';
//
$brytr = 0;
$larmant = 0;
$larmrub9 = 1;
//
$fileut=$directory . "RGD9.GED";
$fileux=$directory . "RGDN.txt";
$filenam=$directory . "name.dat";
//
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	$filein=$directory . "RGD8.GED";
	//
	if(file_exists($filein))
	{
		echo "Program dubbprepz startat<br/>";
		echo $filein." finns<br/>";
//
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
		$handux=fopen($fileux,"w");
		$handnam=fopen($filenam,"w");
//	
//	Beräkna checksum
	$namndbFil='fsndata.txt';
	$dbl=file_get_contents($namndbFil);
	$checksum=md5($dbl);
//	Anropa disnamnREST
	ini_set('default_socket_timeout',60);
//      Moved to dis4/rgd
//	$url = "http://dev.dis.se/disnamnREST.php?download=1&md5=".$checksum;
	$url = "https://rgd.dis.se/disnamnREST.php?download=1&md5=".$checksum;
	$db = file_get_contents($url);
//	Alternativ
	if(($db===FALSE)||($db=='NoChanges')) {
		echo 'Använder lokal namntabell';
//		Avkoda lokala data		
		$resp=json_decode($dbl,true);
//		var_dump($http_response_header);
		}else{
//		Lagra data		
		$fp=fopen($namndbFil,'w');
		fwrite($fp,$db);
		fclose($fp);
		echo 'Hämtat nytt data från DISNAMN';
//		Avkoda data
		$resp = json_decode($db,true);
	}	
//
	$text = "NAMNFEL ELLER NAMN SOM SAKNAS I NAMNDATABASEN, MEN FINNS MED AVVIKANDE KÖN:";
	fwrite($handux,$text."\r\n");
	$text = "";
	fwrite($handux,$text."\r\n");
//		
	$fn = $resp['fn'];
	$sn = $resp['sn'];
	echo "<br/>";
//
		$trec=0;
		$treh=0;
		$ftyp='';
		$fnamn='';
		$kok='EJ';
		$sex='X';
		$gsex='X';
		$ntyp='X-';
		$nrad='';
		$ant=0;
		$kant=0;
		$eant=0;
		$indant=0;
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
					if(($fnamn != '') && ($kok == 'EJ')) {
						$ftxt = 'Person ';
						$rtxt = '7';
						if($ftyp == 'M-') {
							$ftxt = 'Man ';
							$rtxt = '2';
						}	
						if($ftyp == 'K-') {
							$ftxt = 'Kvinna ';
							$rtxt = '1';
						}	
//	namn saknas men finns med avvikande kön
						$ktext = $rtxt."Id => ".$znum." - ".$nrad;
						$fellista[] = $ktext;
						$kant++;
					}
					$ftyp='';
					$fnamn='';
					$kok='EJ';
					$nrad='';
					$eant=0;
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
				$wnormf="";
				$wnorme="";
				$tnamn="";
				$nnamn="";
				$tagg=substr($str,0,5);
				if($tagg == '1 SEX')
				{
					$sex=substr($str,6,1);
					$indant++;
				}
				$tagg=substr($str,0,6);
				if($tagg == '1 NAME')
				{
//	Array
					$namlen=strlen($str);
					$namorg=substr($str,7,$namlen);
//	Namnsuffix flyttas
					$trel=strlen($str);
					$trem = $trel -1;
					$stk = '';
					$nyx = '';
					$sls = 0;
					$pos1 = substr($str,$trem,1);
					if($pos1 != '/') {
						$stk = $pos1;
						$trem--;
						while($trem >= 0)
						{
							$pos1 = substr($str,$trem,1);
							$pos2 = substr($str,($trem+1),1);
							if($pos1 == '/') {
								if($sls == 0) {
									if($pos2 != ' ') {
										$stk = ' '.$stk;
									}
									$nyx = $stk.$pos1;
									$sls++;
								}
								else {
									$nyx = $pos1.$nyx;
								}
							}
							else {
								if($sls == 0) {
									$stk = $pos1.$stk;
								}
								else {
									$nyx = $pos1.$nyx;
								}
							}
							$trem--;
						}
						if($str != $nyx) {
							$str = $nyx;
						}
					}	
//				
					$namn="";
					$nlen=strlen($str);
					$nrad=substr($str,7,($nlen - 7));
//	test 3 lika tecken i rad
					$trel=strlen($str);
					$trem=7;
					$trep='';
					while($trem <= $trel)
					{
						$tret=substr($str,$trem,1);
						$tren=substr($str,($trem+1),1);
						if($tret == $trep) {
							if($tret == $tren) {
//	Trippeltecken
								$ktext = "4Id => ".$znum." - ".$nrad."/".$trep.$tret.$tren."/";
								$fellista[] = $ktext;
								$kant++;
							}
						}
//	Dotter test
						$fixe = substr($str,$trem,6);
						if(($fixe == 'dotter') && ($sex == 'M')) {
							$ktext = "3Id => ".$znum." - ".$nrad;
							$fellista[] = $ktext;
							$kant++;
						}	
						if(($fixe == 'dottir') && ($sex == 'M')) {
							$ktext = "3Id => ".$znum." - ".$nrad;
							$fellista[] = $ktext;
							$kant++;
						}	
////
//	Varning för <>
						if(($tret == '<') || ($tret == '>')) {
							$treh++;
							if($treh == 1) {
								$ktext = "6Id => ".$znum." - ".$nrad;
								$fellista[] = $ktext;
								$kant++;
//	Larm								
								$larmant++;
								$filelarm=$directory . "Check_lista.txt";
								$handlarm=fopen($filelarm,"a");
								if($larmrub9 == 1) {
									$larm = " ";
									fwrite($handlarm,$larm."\r\n");
									fwrite($handlarm,$larm."\r\n");
									$larm = "*** V A R N I N G  (IX)  Text";
									fwrite($handlarm,$larm."\r\n");
									$larm = " ";
									fwrite($handlarm,$larm."\r\n");
									$larmrub9++;
									$brytr = 0;
								}
// sätt om möjligt id och namn
								$larmid = $znum;
								$larmnamn = $nrad;
								$brytr++;
								if($brytr >= 4) {
									fwrite($handlarm," \r\n");
									$brytr = 1;
								}	
// och beskrivande feltext
								$larm = "Olämpliga tecken i namnfältet - lt/gt (<>) - Id => "
								.$larmid." - ".$larmnamn;
								fwrite($handlarm,$larm."\r\n");
								fclose($handlarm);
							}	
//							
						}
						$trep=$tret;
						$trem++;
					}
//				
					if($sex == 'F')
					{
						$ntyp="K-";
					}
					else
					{
						$ntyp="M-";
					}
					$imax=7;
					while($imax <= $nlen)
					{
						$test=substr($str,$imax,1);
						if($test == '/')
						{
							if(($ntyp == 'E-') && ($imax < ($nlen-1)))
							{
								if($eant == 0) {
//	Felaktigt använda / i namnfältet
									$ktext = "5Id => ".$znum." - ".$nrad;
									$fellista[] = $ktext;
									$kant++;
									$eant++;
//	Larm
									$larmant++;
									$filelarm=$directory . "Check_lista.txt";
									$handlarm=fopen($filelarm,"a");
									if($larmrub9 == 1) {
										$larm = " ";
										fwrite($handlarm,$larm."\r\n");
										fwrite($handlarm,$larm."\r\n");
										$larm = "*** V A R N I N G  (IX)  Text";
										fwrite($handlarm,$larm."\r\n");
										$larm = " ";
										fwrite($handlarm,$larm."\r\n");
										$larmrub9++;
										$brytr = 0;
									}
// sätt om möjligt id och namn
									$larmid = $znum;
									$larmnamn = $nrad;
									$brytr++;
									if($brytr >= 4) {
										fwrite($handlarm," \r\n");
										$brytr = 1;
									}	
// och beskrivande feltext
									$larm = "Olämpligt tecken i namnfältet - slash (/) - Id => "
									.$larmid." - ".$larmnamn;
									fwrite($handlarm,$larm."\r\n");
									fclose($handlarm);
//
								}				
							}
							else
							{	
								$ntyp='E-';
							}	
						}
						if($test == "*")
						{
							$imax++;
						}
						elseif($test == ".")
						{
							$imax++;
						}
						elseif($test == ":")
						{
							$imax++;
						}
						elseif($test == ",")
						{
							$imax++;
						}
						elseif($test == ";")
						{
							$imax++;
						}
						elseif($test == "'")
						{
							$imax++;
						}
						elseif($test == '"')
						{
							$imax++;
						}
						elseif($test == "(")
						{
							$imax++;
						}
						elseif($test == ")")
						{
							$imax++;
						}
						elseif($test == "[")
						{
							$imax++;
						}
						elseif($test == "]")
						{
							$imax++;
						}
						elseif($test == "<")
						{
							$imax++;
						}
						elseif($test == ">")
						{
							$imax++;
						}
						elseif($test == "?")
						{
							$imax++;
						}
						elseif(($test == " ") || ($test == "/") || ($test == "-") || ($test == "_"))
						{
//
							mb_internal_encoding("UTF-8");
							$namn=mb_strtolower($namn);
//					
							if($ntyp == 'E-')
							{
								if(strlen($namn) > 1)
								{
// 	Nya anropet mot $sn Eftertnamn
									if(isset($sn[$namn]['id']) == false)
									{
//	saknas i DISNAMN
										$wnorme = $wnorme.$namn.",";
									}
									else
									{
										if(($sn[$namn]['id']) < 0)
										{
//	ICKE-NAMN i DISNAMN
										}
										elseif(($sn[$namn]['id']) > 0)
										{
											$wnorme = $wnorme.$sn[$namn]['id'].",";
											$nnamn = $nnamn.$sn[$namn]['grnamn'].",";
										}
										else
										{
											$wnorme = $wnorme.$namn.",";
										}
									}
								}	
							$namn="";
							$imax++;
							}	
							if($ntyp == 'M-')
							{
								if(strlen($namn) > 1)
								{
									$sex = substr($ntyp,0,1);
// 	Nya anropet mot $fn Förnamn
									$m = '';
									$k = '';
									$o = '';
									foreach(array('M','K','O') as $s) {
										if(isset($fn[$namn][$s]['id'])) {
//											print "$namn,$s ";
											if($s == 'M') {
											$m = '-M'; }                    
											if($s == 'K') {
											$k = '-K'; }                    
											if($s == 'O') {
											$o = '-O'; } }                   
									}
									if($m == '-M')
									{
										$kok='OK';
									}
									if($m != '-M')
									{
//	saknas i DISNAMN
										$wnormf = $wnormf.$namn.",";
										if($k == '-K')	
										{
//	Flera namn ger gemensam felrad
											$fk = 0;
											$fk = $fn[$namn]['K']['id']; 
											if($fk > 0) {								
												$ftyp = $ntyp;
												$fnamn = $fnamn.$namn.' ';
											}	
										}
									}
									else
									{
										if(($fn[$namn]['M']['id']) < 0)
										{
//	ICKE-NAMN i DISNAMN
										}
										elseif(($fn[$namn]['M']['id']) > 0)
										{
											if(($k == '-K') || ($o == '-O'))	
											{
//	FINNS ÄVEN MED KÖN ".$k.' '.$o;
											}
											$wnormf = $wnormf.$fn[$namn][$sex]['id'].",";
											$nnamn = $nnamn.$fn[$namn][$sex]['grnamn'].",";
										}
										else
										{
											$wnormf = $wnormf.$namn.",";
											if(($k == '-K') || ($o	== '-O'))	
											{
//	FINNS ÄVEN MED KÖN ".$k.' '.$o;
											}
										}
									}
								}
							$namn="";
							$imax++;
							}
							if($ntyp == 'K-')
							{
								if(strlen($namn) > 1)
								{
									$sex = substr($ntyp,0,1);
// 	Nya anropet mot $fn Förnamn
									$m = '';
									$k = '';
									$o = '';
									foreach(array('M','K','O') as $s) {
										if(isset($fn[$namn][$s]['id'])) {
//											print "$namn,$s ";
											if($s == 'M') {
											$m = '-M'; }                    
											if($s == 'K') {
											$k = '-K'; }                    
											if($s == 'O') {
											$o = '-O'; } }                   
									}
									if($k == '-K')
									{
										$kok = 'OK';
									}
									if($k != '-K')
									{
//	saknas i DISNAMN
										$wnormf = $wnormf.$namn.",";
										if($m == '-M')	
										{
//	Flera namn ger gemensam felrad
											$fm = 0;
											$fm = $fn[$namn]['M']['id']; 
											if($fm > 0) {								
												$ftyp = $ntyp;
												$fnamn = $fnamn.$namn.' ';
											}	
										}
									}
									else
									{
										if(($fn[$namn]['K']['id']) < 0)
										{
//	ICKE-NAMN i DISNAMN
										}
										elseif(($fn[$namn]['K']['id']) > 0)
										{
											if(($m == '-M') || ($o == '-O'))	
											{
//	FINNS ÄVEN MED KÖN ".$m.' '.$o;
											}
											$wnormf = $wnormf.$fn[$namn][$sex]['id'].",";
											$nnamn = $nnamn.$fn[$namn][$sex]['grnamn'].",";
										}
										else
										{
											$wnormf = $wnormf.$namn.",";
											if(($m == '-M') || ($o == '-O'))	
											{
//	FINNS ÄVEN MED KÖN ".$m.' '.$o;
											}
										}
									}
								}
							$namn="";
							$imax++;
							}
						}
						else
						{
//	fix för att neutralisera dotternamn
							if($test == 'd') {
								$dtr=substr($str,$imax,6);
								if(($dtr == 'dotter') || ($dtr == 'dottir')) {
									$namn = $namn.'son';
									$imax = $imax+6;
								}
								else {
									$namn=$namn.$test;
									$imax++;
								}
							}
							else {
//	fix hit							
							$namn=$namn.$test;
							$imax++;
							}
						}
					}
					$ulen = strlen($wnormf);
					$wnormf = substr($wnormf,0,($ulen - 1));
					if($ulen > 0) {
						$rgdn="1 RGDF ".$wnormf;
						fwrite($handut,$rgdn."\r\n");
//	Array
						$namtyp = 'F';
						$nam[$namorg][$namtyp]=$wnormf;
					}	
					$ulen = strlen($wnorme);
					$wnorme = substr($wnorme,0,($ulen - 1));
					if($ulen > 0) {
						$rgdn="1 RGDE ".$wnorme;
						fwrite($handut,$rgdn."\r\n");
//	Array
						$namtyp = 'E';
						$nam[$namorg][$namtyp]=$wnorme;
					}	
					$namtxt = '1 NAME '.$namorg;
					fwrite($handut,$namtxt."\r\n");
				}
				else {
					fwrite($handut,$str."\r\n");
				}
			}
		}
//
		if($kant == 0) {
			fwrite($handux," \r\n");
			fwrite($handux,"Inga avvikande uppgifter hittades. \r\n");
		}
		else {
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
						fwrite($handux,"Kvinnonamnet saknas men finns som mansnamn, kolla \r\n");
					}
					if($rubid == '2') {
						fwrite($handux,"Mansnamnet saknas men finns som kvinnonamn, kolla \r\n");
					}
					if($rubid == '3') {
						fwrite($handux,"Dotterpatronymikon i mansnamn, kolla \r\n");
					}
					if($rubid == '4') {
						fwrite($handux,"Trippeltecken, kontrollera och vid behov rätta \r\n");
					}
					if($rubid == '5') {
						fwrite($handux,"Slash (/) skall ej användas i namnfältet, åtgärda \r\n");
					}
					if($rubid == '6') {
						fwrite($handux,"Tecknen lt/gt (<>) är olämpliga i namnfältet \r\n");
					}
					if($rubid == '7') {
						fwrite($handux,"Person med okänt kön och bör kontrolleras manuellt \r\n");
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
//
			fwrite($handux," \r\n");
			fwrite($handux,"Listningen avslutad. \r\n");
		}
		echo "Program dubbprepz avslutat<br/>";
		echo "<br/>";
		echo "Filen ".$fileux." har skapats <br/n>";
//
		fclose($handin);
		fclose($handut);
		fclose($handux);
//
//	Array start	
		fwrite($handnam,json_encode($nam)."\r\n");
		fclose($handnam);
//	Array slut
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts.<br/>";
	}
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
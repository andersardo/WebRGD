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
$larmant = 0;
$larmrub5 = 1;
$larmrub6 = 1;
//
$fileut=$directory . "RGD9.GED";
$fileux=$directory . "RGDN.txt";
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
		echo $filein." finns<br/>";
		echo "$filein har storleken ".filesize($filein)."<br/>";
		echo "<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
		$handux=fopen($fileux,"w");
//	
//	Beräkna checksum
	$namndbFil='fsndata.txt';
	$dbl=file_get_contents($namndbFil);
	$checksum=md5($dbl);
//	Anropa disnamnREST
	ini_set('default_socket_timeout',60);
	$url = "http://dev.dis.se/disnamnREST.php?download=1&md5=".$checksum;
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
//						$ktext=$ftxt.$fnamn.", namn saknas men finns med avvikande kön ";
//						fwrite($handux,$ktext." . . . . . Id => "
//						.$znum." - ".$nrad." \r\n");
//						fwrite($handux," \r\n");
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
//								echo "<br/>";
//								echo "* Trippeltecken /".$trep.$tret.$tren.
//								"/ i namnfältet, kolla . . . . . Id => ".$znum." - ".$nrad." <br/>";
////
//								$ktext="Trippeltecken av /".$trep.$tret.$tren
//								."/ i namnfältet, kontrollera och vid behov rätta. ";
//								fwrite($handux,$ktext." . . . . . Id => "
//								.$znum." - ".$nrad." \r\n");
//								fwrite($handux," \r\n");
								$ktext = "4Id => ".$znum." - ".$nrad."/".$trep.$tret.$tren."/";
								$fellista[] = $ktext;
								$kant++;
							}
						}
//	Dotter test
						$fixe = substr($str,$trem,6);
						if(($fixe == 'dotter') && ($sex == 'M')) {
//							echo "** Man med dotter i namnet, bör kollas";
//							echo " . . . . . . . Id => ".$znum." - ".$norg." *** <br/>";;
////
//							$ktext="Dotterpatronymikon i mansnamn, kontrollera och vid behov rätta. ";
//							fwrite($handux,$ktext." . . . . . Id => "
//							.$znum." - ".$nrad." \r\n");
//							fwrite($handux," \r\n");
							$ktext = "3Id => ".$znum." - ".$nrad;
							$fellista[] = $ktext;
							$kant++;
						}	
						if(($fixe == 'dottir') && ($sex == 'M')) {
//							echo "** Man med dotter i namnet, bör kollas";
//							echo " . . . . . . . Id => ".$znum." - ".$norg." *** <br/>";;
////
//							$ktext="Dottirpatronymikon i mansnamn, kontrollera och vid behov rätta. ";
//							fwrite($handux,$ktext." . . . . . Id => "
//							.$znum." - ".$nrad." \r\n");
//							fwrite($handux," \r\n");
							$ktext = "3Id => ".$znum." - ".$nrad;
							$fellista[] = $ktext;
							$kant++;
						}	
////
//	Varning för <>
						if(($tret == '<') || ($tret == '>')) {
							$treh++;
							if($treh == 1) {
//								echo "<br/>";
//								echo "* * Tecknen lt/gt (<>) olämpliga att använda i 
//								namnfältet . . . . . Id => ".$znum." - ".$nrad." <br/>";
//								$trec++;
////
//								$ktext="Tecknen lt/gt (<>) olämpliga att använda i namnfältet. ";
//								fwrite($handux,$ktext." . . . . . Id => "
//								.$znum." - ".$nrad." \r\n");
//								fwrite($handux," \r\n");
								$ktext = "6Id => ".$znum." - ".$nrad;
								$fellista[] = $ktext;
								$kant++;
//	Larm								
								$larmant++;
								$filelarm=$directory . "LARM_lista.txt";
								$handlarm=fopen($filelarm,"a");
								if($larmrub6 == 1) {
									$larm = " ";
									fwrite($handlarm,$larm."\r\n");
									$larm = "L A R M  T Y P  VI ";
									fwrite($handlarm,$larm."\r\n");
									$larm = " ";
									fwrite($handlarm,$larm."\r\n");
									$larmrub6++;
								}
// sätt om möjligt id och namn
								$larmid = $znum;
								$larmnamn = $nrad;
								$larm = " ";
								fwrite($handlarm,$larm."\r\n");
// och beskrivande feltext
								$larm = "Varning för lt/gt (<>) i namnfältet . . . . . Id => ".$larmid." - ".$larmnamn;
								fwrite($handlarm,$larm."\r\n");
								$larm = "* *  Bör undvikas i namnfältet, de används ofta i helt olika syften.";
								fwrite($handlarm,$larm."\r\n");
								$larm = " ";
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
//									echo "<br/>";
//									echo "* * *  OBS! Felaktigt använda / i namnfältet, måste rättas i "
//									.$str." . . . . . Id => ".$znum." <br/>";
////
//									$ktext="OBS! Felaktigt använda slash (/) i namnfältet, måste rättas ";
//									fwrite($handux,$ktext." . . . . . Id => "
//									.$znum." - ".$nrad." \r\n");
//									fwrite($handux," \r\n");
									$ktext = "5Id => ".$znum." - ".$nrad;
									$fellista[] = $ktext;
									$kant++;
									$eant++;
//	Larm
									$larmant++;
									$filelarm=$directory . "LARM_lista.txt";
									$handlarm=fopen($filelarm,"a");
									if($larmrub5 == 1) {
										$larm = " ";
										fwrite($handlarm,$larm."\r\n");
										$larm = "L A R M  T Y P  V ";
										fwrite($handlarm,$larm."\r\n");
										$larm = " ";
										fwrite($handlarm,$larm."\r\n");
										$larmrub5++;
									}
// sätt om möjligt id och namn
									$larmid = $znum;
									$larmnamn = $nrad;
									$larm = " ";
									fwrite($handlarm,$larm."\r\n");
// och beskrivande feltext
									$larm = "Felaktigt använda slash (/) i namnfältet . . . . . Id => "
									.$larmid." - ".$larmnamn;
									fwrite($handlarm,$larm."\r\n");
									$larm = "* * *  Orsakar fel vid tolkning av efternamn i GEDCOM.";
									fwrite($handlarm,$larm."\r\n");
									$larm = " ";
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
										$wnorme = $wnorme.$namn.",";
//										echo ">  >  > ".$ntyp.$namn
//										." saknas i DISNAMN . . . . . . . . Id => ".$znum." <br/>";
//										$ant++;
									}
									else
									{
										if(($sn[$namn]['id']) < 0)
										{
//											echo "--------------- ICKE-NAMN i DISNAMN --------------- "
//											.$ntyp.$namn." . . . . . . . Id => ".$znum." <br/>";
										}
										elseif(($sn[$namn]['id']) > 0)
										{
//											echo $ntyp.$namn." fanns med id ".$sn[$namn]['id'];
//											echo " och gruppnamn ".$sn[$namn]['grnamn']."<br/>";
											$wnorme = $wnorme.$sn[$namn]['id'].",";
											$nnamn = $nnamn.$sn[$namn]['grnamn'].",";
										}
										else
										{
											$wnorme = $wnorme.$namn.",";
//											echo ">  ".$ntyp.$namn.
//											" fanns men är ej grupperad . . . . . . . Id => ".$znum." <br/>";
//											$ant++;
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
										$wnormf = $wnormf.$namn.",";
//										echo ">  >  >  ".$ntyp.$namn.
//										" saknas i DISNAMN";
//										$ant++;
//										if(($k == '-K') || ($o	== '-O'))	
										if($k == '-K')	
										{
//											echo ", >  >  >  > FINNS MED KÖN ".$k.' '.$o;
////	Flyttas till ny post				$ktext=$ntyp.$namn.' saknas men finns med kön '.$k.' '.$o;
//											fwrite($handux,$ktext." . . . . . Id => "
//											.$znum." - ".$nrad." \r\n");
//											$ktext = $ktext." . . . . . Id => ".$znum." - ".$nrad;
//											$fellista[] = $ktext;
////										$kant++;	
////	Flera namn ger gemensam felrad
											$fk = 0;
											$fk = $fn[$namn]['K']['id']; 
											if($fk > 0) {								
												$ftyp = $ntyp;
												$fnamn = $fnamn.$namn.' ';
											}	
										}
//										echo " . . . . . . . Id => ".$znum." <br/>";
									}
									else
									{
										if(($fn[$namn]['M']['id']) < 0)
										{
//											echo "--------------- ICKE-NAMN i DISNAMN --------------- "
//											.$ntyp.$namn." . . . . . . . Id => ".$znum." <br/>";
										}
										elseif(($fn[$namn]['M']['id']) > 0)
										{
											if(($k == '-K') || ($o == '-O'))	
											{
//												echo ">  >  >  >  >  >  > ".$ntyp.$namn.
//												", fanns > > FINNS ÄVEN MED KÖN ".$k.' '.$o;
//												echo " . . . . . . . Id => ".$znum." <br/>";
//	ej testat									$ktext=$ntyp.$namn.' fanns men finns även med kön '.$k.' '.$o;
//												fwrite($handux,$ktext." . . . . . Id => "
//												.$znum." - ".$nrad." \r\n");
//												$ktext = $ktext." . . . . . Id => ".$znum." - ".$nrad;
//												$fellista[] = $ktext;
//												$kant++;	
											}
//		 									echo $ntyp.$namn." fanns med id ".$fn[$namn][$sex]['id'];
//											echo " och gruppnamn ".$fn[$namn][$sex]['grnamn']."<br/>";
											$wnormf = $wnormf.$fn[$namn][$sex]['id'].",";
											$nnamn = $nnamn.$fn[$namn][$sex]['grnamn'].",";
										}
										else
										{
											$wnormf = $wnormf.$namn.",";
											if(($k == '-K') || ($o	== '-O'))	
											{
//												echo $ntyp.$namn.
//												" fanns ej grupperad,";
//												echo ", >  >  >  >  ÄVEN MED KÖN ".$k.' '.$o;
//												echo " . . . . . . . Id => ".$znum." <br/>";
//	ej testat									$ktext=$ntyp.$namn.' fanns men finns även med kön '.$k.' '.$o;
//												fwrite($handux,$ktext." . . . . . Id => "
//												.$znum." - ".$nrad." \r\n");
//												$ktext = $ktext." . . . . . Id => ".$znum." - ".$nrad;
//												$fellista[] = $ktext;
//												$kant++;	
											}
/*											else
											{
												echo $ntyp.$namn.
												" fanns ej grupperad,";
												echo " . . . . . . . . Id => ".$znum." <br/>";
											}
											$ant++;*/
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
										$wnormf = $wnormf.$namn.",";
//										echo ">  >  >  ".$ntyp.$namn.
//										" saknas i DISNAMN";
//										$ant++;
//										if(($m == '-M') || ($o == '-O'))	
										if($m == '-M')	
										{
//											echo ", >  >  >  > FINNS MED KÖN ".$m.' '.$o;
////	Flyttas till ny post				$ktext=$ntyp.$namn." saknas men finns med kön ".$m.' '.$o;
//											fwrite($handux,$ktext." . . . . . Id => "
//											.$znum." - ".$nrad." \r\n");
//											$ktext = $ktext." . . . . . Id => ".$znum." - ".$nrad;
//											$fellista[] = $ktext;
//											$kant++;
////	Flera namn ger gemensam felrad
											$fm = 0;
											$fm = $fn[$namn]['M']['id']; 
											if($fm > 0) {								
												$ftyp = $ntyp;
												$fnamn = $fnamn.$namn.' ';
											}	
										}
//										echo " . . . . . . . Id => ".$znum." <br/>";
									}
									else
									{
										if(($fn[$namn]['K']['id']) < 0)
										{
//											echo "--------------- ICKE-NAMN i DISNAMN --------------- "
//											.$ntyp.$namn." . . . . . . . Id => ".$znum." <br/>";
										}
										elseif(($fn[$namn]['K']['id']) > 0)
										{
											if(($m == '-M') || ($o == '-O'))	
											{
//												echo ">  >  >  >  >  >  > ".$ntyp.$namn.
//												", fanns > > FINNS ÄVEN MED KÖN ".$m.' '.$o;
//												echo " . . . . . . . Id => ".$znum." <br/>";
//	ej testat									$ktext=$ntyp.$namn." fanns men finns även med kön ".$m.' '.$o;
//												fwrite($handux,$ktext." . . . . . Id => "
//												.$znum." - ".$nrad." \r\n");
//												$ktext = $ktext." . . . . . Id => ".$znum." - ".$nrad;
//												$fellista[] = $ktext;
//												$kant++;	
											}
//	 										echo $ntyp.$namn." fanns med id ".$fn[$namn][$sex]['id'];
//											echo " och gruppnamn ".$fn[$namn][$sex]['grnamn']."<br/>";
											$wnormf = $wnormf.$fn[$namn][$sex]['id'].",";
											$nnamn = $nnamn.$fn[$namn][$sex]['grnamn'].",";
										}
										else
										{
											$wnormf = $wnormf.$namn.",";
											if(($m == '-M') || ($o == '-O'))	
											{
//												echo $ntyp.$namn.
//												" fanns ej grupperad,";
//												echo ", >  >  >  > FINNS ÄVEN MED KÖN ".$m.' '.$o;
//												echo " . . . . . . . Id => ".$znum." <br/>";
//	ej testat									$ktext=$ntyp.$namn." finns även med kön ".$m.' '.$o;
//												fwrite($handux,$ktext." . . . . . Id => "
//												.$znum." - ".$nrad." \r\n");
//												$ktext = $ktext." . . . . . Id => ".$znum." - ".$nrad;
//												$fellista[] = $ktext;
//												$kant++;	
											}
/*											else
											{
												echo $ntyp.$namn.
												" fanns ej grupperad,";
												echo " . . . . . . . . Id => ".$znum." <br/>";
											}
											$ant++;*/
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
//					$rgdn="1 RGDN F-".$wnormf."E-".$wnorme."N-".$nnamn;
					$ulen = strlen($wnormf);
					$wnormf = substr($wnormf,0,($ulen - 1));
					if($ulen > 0) {
						$rgdn="1 RGDF ".$wnormf;
						fwrite($handut,$rgdn."\r\n");
					}	
					$ulen = strlen($wnorme);
					$wnorme = substr($wnorme,0,($ulen - 1));
					if($ulen > 0) {
						$rgdn="1 RGDE ".$wnorme;
						fwrite($handut,$rgdn."\r\n");
					}	
				}
			}
			fwrite($handut,$str."\r\n");
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
		echo "<br/n>";
		echo "Program kompnnorx avslutat<br/>";
//		echo "Antalet saknade namn = ".$ant."<br/>";
		echo "<br/n>";
		echo "Filen ".$fileux." har skapats <br/n>";
		echo "<br/n>";
		echo "Filen ".$fileut." har skapats <br/n>";
		echo "Antalet individer i filen = ".$indant."<br/>";
//		if($ant > 0)
//		{
//			echo "men bör tas bort och kompnnorx körs om när tabellen kompletterats <br/>";
//		}
		fclose($handin);
		fclose($handut);
		fclose($handux);
//
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avslutas.<br/>";
	}
}	
if($larmant > 0) {
	echo "<br/>";
	echo "***** Larmlista med ".$larmant." post(er) har skrivits ut, måste rättas. <br/>";
	echo "<br/>";
}
?>
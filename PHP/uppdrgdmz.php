<?PHP
/*
Fixprogram för att skapa alternativ källinformation med källvalidering

*/
require 'initbas.php';
//
//$filedata=$directory . "data.dat";
$filesou2=$directory . "sou2.dat";
$sou2cnt = 0;
//
if(file_exists($filesou2))
{
	echo $filesou2." finns redan, programmet avbryts<br/>";
}
else
{
//
	$filein=$directory . "RGD9Z.GED";
//
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
//		$handdata=fopen($filedata,"w");
		$handsou2=fopen($filesou2,"w");
//	
		$sou2p = '';
		$typ = 'X X';
		$htyp = 'XXXX';
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
//		
//	Första individ/relation börjar	
			if($head == 'OFF')
			{
//	hitta idnummer för individ/relation
				$ztag = substr($str,0,3);
				if($ztag == '0 @') {
					$sant=0;
					$cant=0;
					$typ = 'X X';
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal != '@') {
							$znum = $znum.$ztal;
						}
						else {
							if((substr($str,$zmax,5) == '@ IND') || (substr($str,$zmax,5) == '@ FAM')) {
								$typ = substr($str,$zmax,5);
							}
							$zmax = $zlen;
							$zmax++;
						}
						$zmax++;
					}
					if($typ == '@ IND') {
						$ptyp = 'individ'; }
					elseif($typ == '@ FAM') {
						$ptyp = 'familj'; }
					else {
						$ptyp = 'X X';
					}	
				}
//				
//					$data[$ptyp]['@'.$znum.'@'][$htyp]['dtyp']=$text;
//
				$slen = strlen($str);
				$pos1 = substr($str,0,1);
				$tagk = substr($str,0,5);
				$tagg = substr($str,0,6);
				$sexx = substr($str,6,1);
				$text = substr($str,7,($slen-7));
				if(($pos1 == '0') || ($pos1 == '1')) {
					$htyp = 'XXXX';
					$sou2ut = 'XXXX';
					$sou2org = 'XXXX';
				}
				if($tagk == '1 SEX') {
					$data[$ptyp]['@'.$znum.'@']['SEX']=$sexx; }
				elseif($tagg == '1 RGDF') {
					$data[$ptyp]['@'.$znum.'@']['RGDF']=$text; }
				elseif($tagg == '1 RGDE') {
					$data[$ptyp]['@'.$znum.'@']['RGDE']=$text; }
				elseif($tagg == '1 RGDN') {
					$data[$ptyp]['@'.$znum.'@']['RGDN']=$text; }
				elseif($tagg == '1 BIRT') {
					$htyp = 'BIRT'; }
				elseif($tagk == '1 CHR') {
					$htyp = 'CHR'; }
				elseif($tagg == '1 DEAT') {
					$htyp = 'DEAT'; }
				elseif($tagg == '1 BURI') {
					$htyp = 'BURI'; }
				elseif($tagg == '1 MARR') {
					$htyp = 'MARR'; }
				elseif($tagg == '1 OCCU') {
					$data[$ptyp]['@'.$znum.'@']['OCCU']=$text; }
				elseif($tagg == '1 FAMS') {
					if($sant == 0) {
						$data[$ptyp]['@'.$znum.'@']['FAMS']=array(); 
						$sant++; }		
					$data[$ptyp]['@'.$znum.'@']['FAMS'][]=$text; }
				elseif($tagg == '1 FAMC') {
					$data[$ptyp]['@'.$znum.'@']['FAMC']=$text; }
				elseif($tagg == '1 HUSB') {
					$data[$ptyp]['@'.$znum.'@']['HUSB']=$text; }
				elseif($tagg == '1 WIFE') {
					$data[$ptyp]['@'.$znum.'@']['WIFE']=$text; }
				elseif($tagg == '1 CHIL') {
					if($cant == 0) {
						$data[$ptyp]['@'.$znum.'@']['CHIL']=array(); 
						$cant++; }		
					$data[$ptyp]['@'.$znum.'@']['CHIL'][]=$text; }
				elseif($tagg == '2 RGDD') {
					if($htyp != 'XXXX') {
						$data[$ptyp]['@'.$znum.'@'][$htyp]['RGDD']=$text; }
				}		
				elseif($tagg == '2 RGDP') {
					if($htyp != 'XXXX') {
						$data[$ptyp]['@'.$znum.'@'][$htyp]['RGDP']=$text; }
/*	Array2
						$soulen=strlen($str);
						$sou2p=substr($str,7,$soulen);*/
				}
				elseif($tagg == '2 RGDX') {
					if($htyp != 'XXXX') {
						$data[$ptyp]['@'.$znum.'@'][$htyp]['RGDX']=$text; }
				}
				elseif($tagg == '2 PLAC') {
//					if($htyp != 'XXXX') {
//	Array2
						$soulen=strlen($str);
						$sou2p=substr($str,7,$soulen);
//					}
				}
				elseif($tagg == '2 RGDS') {
					if($htyp != 'XXXX') {
						$data[$ptyp]['@'.$znum.'@'][$htyp]['RGDS']=$text;
//
//	Skip upprepade mellanslag och mellanslag i sista position
						$tlen = strlen($str);
						$otkn = ' ';
						$strtmp = '';
						$smax = ($tlen-1);
						while($smax >= 0)
						{
							$stkn = substr($str,$smax,1);
							if($stkn == ' ') {
								if($stkn != $otkn) {
									$strtmp = $stkn.$strtmp;
								}	
							}
							else {
								$strtmp = $stkn.$strtmp;
							}	
							$otkn = $stkn;
							$smax--;
						}
						$str = $strtmp;
//						if($sou2p == '') {
/*							$pos2 = substr($str,7,2);
							if(($pos2 == '*7') || ($pos2 == '*8') || ($pos2 == '*9')) $sou2p = '9999';
*/
//echo $znum.'***/***/'.$pos2.'>'.$sou2p.'/'.$str."/ <br/>";
//						}
//
//	Array2
						$soulen=strlen($str);
						$sou2ut=substr($str,7,$soulen);
					}
				}	
				elseif($tagg == '2 SOUR') {
//					if($htyp != 'XXXX') {
						$data[$ptyp]['@'.$znum.'@'][$htyp]['RGDS']=$text;
//	Array2
						$soulen=strlen($str);
						$sou2s=substr($str,7,$soulen);
//	Array2
						if($sou2p != '') {
							$sou2org=$sou2p.'-'.$sou2s;
							$sou2[$sou2org]=$sou2ut;
							$sou2cnt++;
							$sou2p = '';
//echo $znum."*/*".$sou2org."/".$sou2ut."/ <br/>";
						}
						else {
							$sou2org=$sou2s;
							$sou2[$sou2org]=$sou2ut;
							$sou2cnt++;
//echo $znum."#/#".$sou2org."/".$sou2ut."/ <br/>";
						}
//					}
				}	
				else {
//	Skipa resten
				}
			}
		}
/*	Array start
	fwrite($handdata,json_encode($data)."\r\n");
	fclose($handdata);
//	Array slut
//
		echo "<br/>";
		echo "Filen ".$filedata." har skapats <br/>";
*/
//	Array2 start
	if($sou2cnt > 0) {
		fwrite($handsou2,json_encode($sou2)."\r\n");
		fclose($handsou2);
	}
	else {
		fwrite($handsou2,"{}\r\n");
		fclose($handsou2);
		echo "Källinformation saknas. <br/>";
	}	
//	Array2 slut
//			
		echo "<br/>";
		echo "Program uppdrgdmz avslutad <br/>";
//		echo "<br/>";
//		
		fclose($handin);
/*
		$filelogg=$directory . "RGDlogg.txt";
		$handlog=fopen($filelogg,"a");
		$text = "Program uppdrgdmz avslutad ";
		fwrite($handlog,$text.date('Y-m-d')." / ".date('H:i:s')."\r\n");
		fclose($handlog);
*/
	}
	else
	{
		echo "Filen ".$filein." saknas, programmet avbryts. <br/>";
	}
}	
?>

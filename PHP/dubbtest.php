<?PHP
/*
Programmet skall genomsöka GEDCOM filen efter individer med lika
eller snarlika uppgifter för att hitta eventuella dubbletter eller
andra felregistreringar.
Programmet är uppdelat i 4 block för att korta körtiden då körtiden
ökar katastrofalt med antalet individer i filen.
Blocken är uppdelade efter födelseår.

Poängsättning och bonus på jämförda likheter
Avvikelser adderas i $neg, endast 1 avvikelse tillåten
Även familjekombinationen kan påverka resultatet.

Utdata, en sorterad kandidatlista avsedd för egenkontroll.

Antalsspärr, f.n. 100 000 inlagd

*/
require 'initbas.php';
//
$filename=$directory . "RGD9.GED";
//
$fileut=$directory . "RGDD.txt";
//
if(file_exists($fileut))
{
	echo $fileut	." finns redan, programmet avbruts<br/>";
}
else
{
//
	if(file_exists($filename))
	{
		echo $filename." finns<br/>";
		echo "$filename har storleken ".filesize($filename)."<br/>";
//
		echo "Program startar ".date('Y-m-d')." / ".date('H:i:s')."<br/>";
//****
//	Extra snurra för att kunna begränsa volymen
	$zlen = 0;
	$zind = 0;
	$zfam = 0;
	$ztst = '';
	$handle=fopen($filename,"r");
//	Läs in indatafilen				
	$lines = file($filename,FILE_IGNORE_NEW_LINES);
	foreach($lines as $radnummer => $str)
	{
		$zlen = strlen($str);
		$ztst = substr($str,$zlen-6,6);
		if($ztst == '@ INDI') {
			$zind++;
		}
		$ztst = substr($str,$zlen-5,5);
		if($ztst == '@ FAM') {
			$zfam++;
		}
	}
	fclose($handle);
//
	if($zind >= 100000) 
	{
		$handut=fopen($fileut,"w");
		fwrite($handut,"Dubblett Sökning \r\n");
		fwrite($handut," \r\n");
		fwrite($handut,"Antalet individer är för stort för dubblettkontrollen.  \r\n");
		fwrite($handut," \r\n");
		fwrite($handut,"Kontakta administratören som anges på inloggningssidan  \r\n");
		fwrite($handut,"för att få hjälp med dubblettkontrollen på annat sätt.   \r\n");
		fwrite($handut," \r\n");
		fwrite($handut,"Antal individer = ".$zind." \r\n");
		fwrite($handut,"Antal familjer  = ".$zfam." \r\n");
		fclose($handut);
		echo "<br/>";
		echo 'Antal individer = '.$zind.'<br/>';
		echo 'Antal familjer  = '.$zfam.'<br/>';
		echo "<br/>";
		echo 'Dubblettkontrollen inte körd.<br/>';
	}
	else
	{	
//****
		$handut=fopen($fileut,"w");
		echo "<br/>";
//
		echo 'Antal individer = '.$zind.'<br/>';
		echo 'Antal familjer  = '.$zfam.'<br/>';
//
		fwrite($handut,"Dubblett Sökning \r\n");
		fwrite($handut," \r\n");
		fwrite($handut,"Individer med lika eller snarlika uppgifter, som bör ");
		fwrite($handut,"kontrolleras avseende dubblett eller felregistrering.  \r\n");
		fwrite($handut," \r\n");
//	
		$n1 = 0;
		$min = 0;
		$max = 0;
		$kant = 0;
//	
//	Steg 1 startar	
//	
		$handle=fopen($filename,"r");
//	
		$n1 = $min;
		$nsist = 0;
		$znum = '';
		$kand = '';
		$birt = '';
		$deat = '';
		$ifnx = '';
		$ienx = '';
		$ifdx = '';
		$ifpx = '';
		$iddx = '';
		$idpx = '';
		$famc = '';
		$nradx = '';
		$dradx = '';
		$pradx = '';
		$dpref = '';
		$sexx = '';
		$fam = ''; 
		$ind = '';
		$head = 'ON';	
//	Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
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
//							echo "Första individen/relationen = ".$str." <br/>";
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
//	Ny post, ladda upp tidigare data
					$aar = 0;
					$faar = 0;
					$daar = 0;
					$faar = (int)substr($ifdx,0,4);
					$daar = (int)substr($iddx,0,4);
					if(($faar >= 1000) && ($faar <= 1799)) {
						$aar = $faar;
					}
					if(($daar >= 1000) && ($daar <= 1799)) {
						$aar = $daar;
					}
					if(($znum != '') && ($ind == 'J') && ($aar >= 1000) && ($aar <= 1799)) {
						$n1++;
						$num[$n1]=$znum;
						$ifn[$n1]=$ifnx;
						$ien[$n1]=$ienx;
						$ifd[$n1]=$ifdx;
						$ifp[$n1]=$ifpx;
						$idd[$n1]=$iddx;
						$idp[$n1]=$idpx;
						$fmc[$n1]=$famc;
						$nrad[$n1]=$nradx;
						$drad[$n1]=$dradx;
						$prad[$n1]=$pradx;
						$isex[$n1]=$sexx;
					}
					$ifnx = '';
					$ienx = '';
					$ifdx = '';
					$ifpx = '';
					$iddx = '';
					$idpx = '';
					$famc = '';
					$nradx = '';
					$dradx = '';
					$pradx = '';
					$dpref = '';
					$sexx = '';
//	Ny post, sök identitet
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal == '@') {
							if(substr($str,$zmax,5) == '@ IND') {
								$ind = 'J';
								$fam = '';
								}
							elseif(substr($str,$zmax,5) == '@ FAM') {
								$ind = '';
								$fam = 'J';
							}
							else {
								$ind = '';
								$fam = '';
								}
						}	
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
//	Fortsätt samla data
				$tag1 = substr($str,0,1);
				$tagk = substr($str,2,3);
				$tagl = substr($str,2,4);
				$tag7 = substr($str,6,1);
				$tlen = strlen($str);
				$str = substr($str,7,($tlen-7));
//
				if($tag1 == '1') {
					$birt = '';
					$deat = '';
				}
//
				if($tagk == 'SEX') {
					$sexx = $tag7;
				}
				elseif($tagl == 'BIRT') {
					$birt = 'J';
				}
				elseif($tagk == 'CHR') {
					if(($ifdx == '') && ($ifpx == '')) {
						$birt = 'J';
//						echo "Enbart döpt <br/>";
					}	
				}
				elseif($tagl == 'DEAT') {
					$deat = 'J';
				}
				elseif($tagl == 'BURI') {
					if(($iddx == '') && ($idpx == '')) {
						$deat = 'J';
//						echo "Enbart begravd <br/>";
					}	
				}
				elseif($tagl == 'RGDF') {
					$ifnx = $str;
				}
				elseif($tagl == 'RGDE') {
					$ienx = $str;
				}
				elseif(($tagl == 'RGDD') && ($birt == 'J')) {
					$ifdx = $str;
				}
				elseif(($tagl == 'RGDP') && ($birt == 'J')) {
					$ifpx = $str;
				}
				elseif(($tagl == 'RGDD') && ($deat == 'J')) {
					$iddx = $str;
				}
				elseif(($tagl == 'RGDP') && ($deat == 'J')) {
					$idpx = $str;
				}
				elseif($tagl == 'FAMC') {
					$famc = $str;
				}
				elseif($tagl == 'NAME') {
					$nradx = $str;
				}
				elseif(($tagl == 'DATE') && ($birt == 'J')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'f. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($birt == 'J')) {
					if($dpref == '') {
						$dpref = 'f. ';
						$pradx = $dpref.$str;
					}
					else {
						$pradx = $str;
					}
				}
				elseif(($tagl == 'DATE') && ($deat == 'J') && ($dpref == '')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'd. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($deat == 'J')) {
					if($dpref == '') {
						$pradx = 'd. '.$str;
					}	
					if($dpref == 'd. ') {
						$pradx = $str;
					}	
				}
				else {
//	ointressant rad
				}
			}		
		}
		fclose($handle);
//
		$max = $n1;
		$n1 = $min + 1;
//
		while($n1 <= $max) {
				$kp1[$n1]='';
				$kp2[$n1]='';
				if(($ifd[$n1] != '') && ($ifp[$n1] != '')); {
					$kp1[$n1]=$ifd[$n1].$ifp[$n1];
				}	
				if(($idd[$n1] != '') && ($idp[$n1] != '')); {
					$kp2[$n1]=$idd[$n1].$idp[$n1];
				}	
//						
				$flen = strlen($ifn[$n1]);		
				$ifn1[$n1] = '';
				$ifn2[$n1] = '';
				$ifn3[$n1] = '';
				$ifn4[$n1] = '';
				$ifn5[$n1] = '';
//			
				$fx = 0;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn1[$n1] = $ifn1[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn2[$n1] = $ifn2[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn3[$n1] = $ifn3[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn4[$n1] = $ifn4[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				if($fx <= $flen) {
					$ifn5[$n1] = substr($ifn[$n1],$fx,($flen-$fx));
				}
//						
				$elen = strlen($ien[$n1]);		
				$ien1[$n1] = '';
				$ien2[$n1] = '';
				$ien3[$n1] = '';
//
				$ex = 0;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien1[$n1] = $ien1[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien2[$n1] = $ien2[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				if($ex <= $elen) {
					$ien3[$n1] = substr($ien[$n1],$ex,($elen-$ex));
				}
//						
				$blen = strlen($ifd[$n1]);		
				$ifd1[$n1] = '';
				$ifd2[$n1] = '';
				$ifd3[$n1] = '';
//			
				$bx = 0;
				if(($bx < $blen) && (substr($ifd[$n1],$bx,4) != '')) {
					$ifd1[$n1] = substr($ifd[$n1],$bx,4);
				}
				$bx = 4;
				if(($bx <= $blen) && (substr($ifd[$n1],$bx,2) != '')) {
					$ifd2[$n1] = substr($ifd[$n1],$bx,2);
				}
				$bx = 6;
				if($bx <= $blen) {
					$ifd3[$n1] = substr($ifd[$n1],$bx,2);
				}	
//						
				$dlen = strlen($idd[$n1]);		
				$idd1[$n1] = '';
				$idd2[$n1] = '';
				$idd3[$n1] = '';
//			
				$dx = 0;
				if(($dx < $dlen) && (substr($idd[$n1],$dx,4) != '')) {
					$idd1[$n1] = substr($idd[$n1],$dx,4);
				}
				$dx = 4;
				if(($dx <= $dlen) && (substr($idd[$n1],$dx,2) != '')) {
					$idd2[$n1] = substr($idd[$n1],$dx,2);
				}
				$dx = 6;
				if($dx <= $dlen) {
					$idd3[$n1] = substr($idd[$n1],$dx,2);
				}	
				$kp3[$n1]='';
				$kp4[$n1]='';
				if(($ifd1[$n1] != '') && ($ifp[$n1] != '')); {
					$kp3[$n1]=$ifd1[$n1].$ifp[$n1];
				}	
				if(($idd1[$n1] != '') && ($idp[$n1] != '')); {
					$kp4[$n1]=$idd1[$n1].$idp[$n1];
				}	
				$kp5[$n1]='';
				$kp6[$n1]='';
				if(($ifd2[$n1] != '') && ($ifd3[$n1] != '') && ($ifp[$n1] != '')); {
					$kp5[$n1]=$ifd2[$n1].$ifd3[$n1].$ifp[$n1];
				}	
				if(($idd2[$n1] != '') && ($idd3[$n1] != '') && ($idp[$n1] != '')); {
					$kp6[$n1]=$idd2[$n1].$idd3[$n1].$idp[$n1];
				}
			$n1++;
		}			
//
//	Loop 1		
		$n1 = $min + 1;
		$min = $max;
		$nsist = $max - 1;
		while($n1 <= $nsist) {
//	Loop 2	
			$n2 = $n1 + 1;
			while($n2 <= $max) {
//	
				$bon = 0;
				$pos = 0;
				$neg = 0;
				$ant = 0;
				$halv = 0;
				$plus = ' ';
//	Individerna har olika kön					
				if($isex[$n1] != '') {
					if($isex[$n2] != '') {
						if($isex[$n1] != $isex[$n2]) {
							$neg++;
						}		
					}
				}
//	Båda har barnfamilj och dessutom samma familj (syskon)					
				if($fmc[$n1] != '') {
					if($fmc[$n2] != '') {
						if($fmc[$n1] == $fmc[$n2]) {
							$plus = '-';
							$neg++;
// ?						$halv++;
						}		
					}
				}
//	Boost					
				if(($ifd[$n1] != '') && (strlen($ifd[$n1]) == 8)) {
					if($ifd[$n2] != '') {
						if($ifd[$n1] == $ifd[$n2]) {
							$bon++;
						}
					}
				}
				if($ifd1[$n1] != '') {
					if($ifd1[$n2] != '') {
						$ant++;
						if($ifd1[$n1] == $ifd1[$n2]) {
							$pos++;
						}
						else {
							$neg++;
							if($ifd1[$n1] < $ifd1[$n2]) {
								if(($ifd1[$n1] + 10) < $ifd1[$n2]) {
									$neg++;
								}
							}	
							else {
								if(($ifd1[$n2] + 10) < $ifd1[$n1]) {
									$neg++;
								}
							}
						}
					}
				}
//	Bryt om ett dödår är mindre än det andra födelseåret
				if($ifd1[$n1] != '') {
					if($idd1[$n2] != '') {
						if($idd1[$n2] < $ifd1[$n1]) {
							$neg++;
							$neg++;
						}
					}
				}
				if($ifd1[$n2] != '') {
					if($idd1[$n1] != '') {
						if($idd1[$n1] < $ifd1[$n2]) {
							$neg++;
							$neg++;
						}
					}
				}
//			
				if($neg < 2){
//						
//	Boost					
					if(($idd[$n1] != '') && (strlen($idd[$n1]) == 8)) {
						if($idd[$n2] != '') {
							if($idd[$n1] == $idd[$n2]) {
								$bon++;
							}
						}
					}
//						
					if($idd1[$n1] != '') {
						if($idd1[$n2] != '') {
							$ant++;
							if($idd1[$n1] == $idd1[$n2]) {
								$pos++;
							}
							else {
								$neg++;
								if($idd1[$n1] < $idd1[$n2]) {
									if(($idd1[$n1] + 10) < $idd1[$n2]) {
										$neg++;
									}
								}	
								else {
									if(($idd1[$n2] + 10) < $idd1[$n1]) {
										$neg++;
									}
								}
							}
						}
					}
//			
					if($neg < 2){
//						
//	Boost		
						$ntest = 0;
						if($ifn[$n1] != '') {
							if($ifn[$n2] != '') {
								if($ifn[$n1] == $ifn[$n2]) {
									$bon++;
									if($ifn[$n1] != $ifn1[$n1]) {
										$bon++;
									}	
									$ntest++;
								}
							}
						}
//						
						if($ifn1[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn1[$n1] == $ifn1[$n2]) || ($ifn1[$n1] == $ifn2[$n2]) || ($ifn1[$n1] == $ifn3[$n2])
								 || ($ifn1[$n1] == $ifn4[$n2]) || ($ifn1[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}	
						}
//						
						if($ifn2[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn2[$n1] == $ifn1[$n2]) || ($ifn2[$n1] == $ifn2[$n2]) || ($ifn2[$n1] == $ifn3[$n2])
								 || ($ifn2[$n1] == $ifn4[$n2]) || ($ifn2[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn3[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn3[$n1] == $ifn1[$n2]) || ($ifn3[$n1] == $ifn2[$n2]) || ($ifn3[$n1] == $ifn3[$n2])
								 || ($ifn3[$n1] == $ifn4[$n2]) || ($ifn3[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn4[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn4[$n1] == $ifn1[$n2]) || ($ifn4[$n1] == $ifn2[$n2]) || ($ifn4[$n1] == $ifn3[$n2])
								 || ($ifn4[$n1] == $ifn4[$n2]) || ($ifn4[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn5[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn5[$n1] == $ifn1[$n2]) || ($ifn5[$n1] == $ifn2[$n2]) || ($ifn5[$n1] == $ifn3[$n2])
								 || ($ifn5[$n1] == $ifn4[$n2]) || ($ifn5[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
						if($ntest == 0) {
							$neg++;
						}
//			
						if($neg < 2) {
//						
							if($ifd2[$n1] != '') {
								if($ifd2[$n2] != '') {
									$ant++;
									if($ifd2[$n1] == $ifd2[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifd3[$n1] != '') {
								if($ifd3[$n2] != '') {
									$ant++;
									if($ifd3[$n1] == $ifd3[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifp[$n1] != '') {
								if($ifp[$n2] != '') {
									$ant++;
									if($ifp[$n1] == $ifp[$n2]) {
										$pos++;
									}
									else {
										$neg++;
//	???									$halv++;
									}
								}
							}
//			
							if($neg < 2) {
//						
								if($idd2[$n1] != '') {
									if($idd2[$n2] != '') {
										$ant++;
										if($idd2[$n1] == $idd2[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idd3[$n1] != '') {
									if($idd3[$n2] != '') {
										$ant++;
										if($idd3[$n1] == $idd3[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idp[$n1] != '') {
									if($idp[$n2] != '') {
										$ant++;
										if($idp[$n1] == $idp[$n2]) {
											$pos++;
										}
										else {
											$neg++;
//	???										$halv++;
										}
									}
								}
//			
								if($neg < 2) {
//	Boost					
									$ntest = 0;
									if($ien[$n1] != '') {
										if($ien[$n2] != '') {
											if($ien[$n1] == $ien[$n2]) {
												$bon++;
												if($ien[$n1] != $ien1[$n1]) {
													$bon++;
												}	
												$ntest++;
											}
										}
									}
//						
									if($ien1[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien1[$n1] == $ien1[$n2]) || ($ien1[$n1] == $ien2[$n2]) || ($ien1[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien2[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien2[$n1] == $ien1[$n2]) || ($ien2[$n1] == $ien2[$n2]) || ($ien2[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien3[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien3[$n1] == $ien1[$n2]) || ($ien3[$n1] == $ien2[$n2]) || ($ien3[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
									if($ntest == 0) {
										$halv++;
									}
//	Boost					
									if($kp1[$n1] != '') {
										if($kp1[$n2] != '') {
											if(($kp1[$n1] == $kp1[$n2]) && (strlen($ifd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp2[$n1] != '') {
										if($kp2[$n2] != '') {
											if(($kp2[$n1] == $kp2[$n2]) && (strlen($idd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp3[$n1] != '') {
										if($kp3[$n2] != '') {
											if($kp3[$n1] == $kp3[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp4[$n1] != '') {
										if($kp4[$n2] != '') {
											if($kp4[$n1] == $kp4[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp5[$n1] != '') {
										if($kp5[$n2] != '') {
											if($kp5[$n1] == $kp5[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp6[$n1] != '') {
										if($kp6[$n2] != '') {
											if($kp6[$n1] == $kp6[$n2]) {
												$bon++;
											}
										}
									}
//	Tolka betydelsen av familj FAMC 
//					
//	Båda har barmfamilj men olika familjer
									if($fmc[$n1] != '') {
										if($fmc[$n2] != '') {
											if($fmc[$n1] != $fmc[$n2]) {
												$plus = '?';
												$halv++;
											}
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] != '') {
										if($fmc[$n2] == '') {
											$plus = '+';
											$bon++;
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] == '') {
										if($fmc[$n2] != '') {
											$plus = '+';
											$bon++;
										}
									}
								}
							}
						}
					}	
				}
				if($halv >= 2) {
					$neg++;
				}
//	
				if(($bon > 2) && ($pos > 2) && ($pos > $neg) && ($neg < 2) && ($ant > 3)) {
					$tmp = $pos/$ant;
					if($tmp >= 0.7) {
						$diff = $ant - $pos;
						$totp = $bon + $pos - $diff - $neg;
						if($plus == '+') {
							$totp = $totp + 1;
						}	
						if($plus == '-') {
							$totp = $totp - 2;
						}	
						if($totp > 7) {
							if($totp > 15) {
								$tots = 9;
							}	
							else {
								$tots = $totp - 7;
							}
//
							$fellista[]="Poäng = ".$tots."(".$plus.") Jämför:"
							.$num[$n1].", ".$nrad[$n1].", ".$drad[$n1].", ".$prad[$n1]." med:"
							.$num[$n2].", ".$nrad[$n2].", ".$drad[$n2].", ".$prad[$n2];
							$kant++;
						}
					}	
				}
				$n2++;
			}
			$n1++;
		}
//	
//	steg 1 avslutat	
//	
//	Steg 2 startar	
//	
		$handle=fopen($filename,"r");
//	
		$n1 = $min;
		$nsist = 0;
		$znum = '';
		$kand = '';
		$birt = '';
		$deat = '';
		$ifnx = '';
		$ienx = '';
		$ifdx = '';
		$ifpx = '';
		$iddx = '';
		$idpx = '';
		$famc = '';
		$nradx = '';
		$dradx = '';
		$pradx = '';
		$dpref = '';
		$sexx = '';
		$fam = ''; 
		$ind = '';
		$head = 'ON';	
//	Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
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
//							echo "Första individen/relationen = ".$str." <br/>";
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
//	Ny post, ladda upp tidigare data
					$aar = 0;
					$faar = 0;
					$daar = 0;
					$faar = (int)substr($ifdx,0,4);
					$daar = (int)substr($iddx,0,4);
					if(($faar >= 1800) && ($faar <= 1849)) {
						$aar = $faar;
					}
					if(($daar >= 1800) && ($daar <= 1849)) {
						$aar = $daar;
					}
					if(($znum != '') && ($ind == 'J') && ($aar >= 1800) && ($aar <= 1849)) {
						$n1++;
						$num[$n1]=$znum;
						$ifn[$n1]=$ifnx;
						$ien[$n1]=$ienx;
						$ifd[$n1]=$ifdx;
						$ifp[$n1]=$ifpx;
						$idd[$n1]=$iddx;
						$idp[$n1]=$idpx;
						$fmc[$n1]=$famc;
						$nrad[$n1]=$nradx;
						$drad[$n1]=$dradx;
						$prad[$n1]=$pradx;
						$isex[$n1]=$sexx;
					}
					$ifnx = '';
					$ienx = '';
					$ifdx = '';
					$ifpx = '';
					$iddx = '';
					$idpx = '';
					$famc = '';
					$nradx = '';
					$dradx = '';
					$pradx = '';
					$dpref = '';
					$sexx = '';
//	Ny post, sök identitet
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal == '@') {
							if(substr($str,$zmax,5) == '@ IND') {
								$ind = 'J';
								$fam = '';
								}
							elseif(substr($str,$zmax,5) == '@ FAM') {
								$ind = '';
								$fam = 'J';
							}
							else {
								$ind = '';
								$fam = '';
								}
						}	
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
//	Fortsätt samla data
				$tag1 = substr($str,0,1);
				$tagk = substr($str,2,3);
				$tagl = substr($str,2,4);
				$tag7 = substr($str,6,1);
				$tlen = strlen($str);
				$str = substr($str,7,($tlen-7));
//
				if($tag1 == '1') {
					$birt = '';
					$deat = '';
				}
//
				if($tagk == 'SEX') {
					$sexx = $tag7;
				}
				elseif($tagl == 'BIRT') {
					$birt = 'J';
				}
				elseif($tagk == 'CHR') {
					if(($ifdx == '') && ($ifpx == '')) {
						$birt = 'J';
//						echo "Enbart döpt <br/>";
					}	
				}
				elseif($tagl == 'DEAT') {
					$deat = 'J';
				}
				elseif($tagl == 'BURI') {
					if(($iddx == '') && ($idpx == '')) {
						$deat = 'J';
//						echo "Enbart begravd <br/>";
					}	
				}
				elseif($tagl == 'RGDF') {
					$ifnx = $str;
				}
				elseif($tagl == 'RGDE') {
					$ienx = $str;
				}
				elseif(($tagl == 'RGDD') && ($birt == 'J')) {
					$ifdx = $str;
				}
				elseif(($tagl == 'RGDP') && ($birt == 'J')) {
					$ifpx = $str;
				}
				elseif(($tagl == 'RGDD') && ($deat == 'J')) {
					$iddx = $str;
				}
				elseif(($tagl == 'RGDP') && ($deat == 'J')) {
					$idpx = $str;
				}
				elseif($tagl == 'FAMC') {
					$famc = $str;
				}
				elseif($tagl == 'NAME') {
					$nradx = $str;
				}
				elseif(($tagl == 'DATE') && ($birt == 'J')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'f. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($birt == 'J')) {
					if($dpref == '') {
						$dpref = 'f. ';
						$pradx = $dpref.$str;
					}
					else {
						$pradx = $str;
					}
				}
				elseif(($tagl == 'DATE') && ($deat == 'J') && ($dpref == '')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'd. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($deat == 'J')) {
					if($dpref == '') {
						$pradx = 'd. '.$str;
					}	
					if($dpref == 'd. ') {
						$pradx = $str;
					}	
				}
				else {
//	ointressant rad
				}
			}		
		}
		fclose($handle);
//
		$max = $n1;
		$n1 = $min + 1;
//
		while($n1 <= $max) {
				$kp1[$n1]='';
				$kp2[$n1]='';
				if(($ifd[$n1] != '') && ($ifp[$n1] != '')); {
					$kp1[$n1]=$ifd[$n1].$ifp[$n1];
				}	
				if(($idd[$n1] != '') && ($idp[$n1] != '')); {
					$kp2[$n1]=$idd[$n1].$idp[$n1];
				}	
//						
				$flen = strlen($ifn[$n1]);		
				$ifn1[$n1] = '';
				$ifn2[$n1] = '';
				$ifn3[$n1] = '';
				$ifn4[$n1] = '';
				$ifn5[$n1] = '';
//			
				$fx = 0;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn1[$n1] = $ifn1[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn2[$n1] = $ifn2[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn3[$n1] = $ifn3[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn4[$n1] = $ifn4[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				if($fx <= $flen) {
					$ifn5[$n1] = substr($ifn[$n1],$fx,($flen-$fx));
				}
//						
				$elen = strlen($ien[$n1]);		
				$ien1[$n1] = '';
				$ien2[$n1] = '';
				$ien3[$n1] = '';
//
				$ex = 0;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien1[$n1] = $ien1[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien2[$n1] = $ien2[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				if($ex <= $elen) {
					$ien3[$n1] = substr($ien[$n1],$ex,($elen-$ex));
				}
//						
				$blen = strlen($ifd[$n1]);		
				$ifd1[$n1] = '';
				$ifd2[$n1] = '';
				$ifd3[$n1] = '';
//			
				$bx = 0;
				if(($bx < $blen) && (substr($ifd[$n1],$bx,4) != '')) {
					$ifd1[$n1] = substr($ifd[$n1],$bx,4);
				}
				$bx = 4;
				if(($bx <= $blen) && (substr($ifd[$n1],$bx,2) != '')) {
					$ifd2[$n1] = substr($ifd[$n1],$bx,2);
				}
				$bx = 6;
				if($bx <= $blen) {
					$ifd3[$n1] = substr($ifd[$n1],$bx,2);
				}	
//						
				$dlen = strlen($idd[$n1]);		
				$idd1[$n1] = '';
				$idd2[$n1] = '';
				$idd3[$n1] = '';
//			
				$dx = 0;
				if(($dx < $dlen) && (substr($idd[$n1],$dx,4) != '')) {
					$idd1[$n1] = substr($idd[$n1],$dx,4);
				}
				$dx = 4;
				if(($dx <= $dlen) && (substr($idd[$n1],$dx,2) != '')) {
					$idd2[$n1] = substr($idd[$n1],$dx,2);
				}
				$dx = 6;
				if($dx <= $dlen) {
					$idd3[$n1] = substr($idd[$n1],$dx,2);
				}	
				$kp3[$n1]='';
				$kp4[$n1]='';
				if(($ifd1[$n1] != '') && ($ifp[$n1] != '')); {
					$kp3[$n1]=$ifd1[$n1].$ifp[$n1];
				}	
				if(($idd1[$n1] != '') && ($idp[$n1] != '')); {
					$kp4[$n1]=$idd1[$n1].$idp[$n1];
				}	
				$kp5[$n1]='';
				$kp6[$n1]='';
				if(($ifd2[$n1] != '') && ($ifd3[$n1] != '') && ($ifp[$n1] != '')); {
					$kp5[$n1]=$ifd2[$n1].$ifd3[$n1].$ifp[$n1];
				}	
				if(($idd2[$n1] != '') && ($idd3[$n1] != '') && ($idp[$n1] != '')); {
					$kp6[$n1]=$idd2[$n1].$idd3[$n1].$idp[$n1];
				}
			$n1++;
		}			
//
//	Loop 1		
		$n1 = $min + 1;
		$min = $max;
		$nsist = $max - 1;
		while($n1 <= $nsist) {
//	Loop 2	
			$n2 = $n1 + 1;
			while($n2 <= $max) {
//	
				$bon = 0;
				$pos = 0;
				$neg = 0;
				$ant = 0;
				$halv = 0;
				$plus = ' ';
//	Individerna har olika kön					
				if($isex[$n1] != '') {
					if($isex[$n2] != '') {
						if($isex[$n1] != $isex[$n2]) {
							$neg++;
						}		
					}
				}
//	Båda har barnfamilj och dessutom samma familj (syskon)					
				if($fmc[$n1] != '') {
					if($fmc[$n2] != '') {
						if($fmc[$n1] == $fmc[$n2]) {
							$plus = '-';
							$neg++;
						}		
					}
				}
//	Boost					
				if(($ifd[$n1] != '') && (strlen($ifd[$n1]) == 8)) {
					if($ifd[$n2] != '') {
						if($ifd[$n1] == $ifd[$n2]) {
							$bon++;
						}
					}
				}
				if($ifd1[$n1] != '') {
					if($ifd1[$n2] != '') {
						$ant++;
						if($ifd1[$n1] == $ifd1[$n2]) {
							$pos++;
						}
						else {
							$neg++;
							if($ifd1[$n1] < $ifd1[$n2]) {
								if(($ifd1[$n1] + 10) < $ifd1[$n2]) {
									$neg++;
								}
							}	
							else {
								if(($ifd1[$n2] + 10) < $ifd1[$n1]) {
									$neg++;
								}
							}
						}
					}
				}
//	Bryt om ett dödår är mindre än det andra födelseåret
				if($ifd1[$n1] != '') {
					if($idd1[$n2] != '') {
						if($idd1[$n2] < $ifd1[$n1]) {
							$neg++;
							$neg++;
						}
					}
				}
				if($ifd1[$n2] != '') {
					if($idd1[$n1] != '') {
						if($idd1[$n1] < $ifd1[$n2]) {
							$neg++;
							$neg++;
						}
					}
				}
//			
				if($neg < 2){
//						
//	Boost					
					if(($idd[$n1] != '') && (strlen($idd[$n1]) == 8)) {
						if($idd[$n2] != '') {
							if($idd[$n1] == $idd[$n2]) {
								$bon++;
							}
						}
					}
//						
					if($idd1[$n1] != '') {
						if($idd1[$n2] != '') {
							$ant++;
							if($idd1[$n1] == $idd1[$n2]) {
								$pos++;
							}
							else {
								$neg++;
								if($idd1[$n1] < $idd1[$n2]) {
									if(($idd1[$n1] + 10) < $idd1[$n2]) {
										$neg++;
									}
								}	
								else {
									if(($idd1[$n2] + 10) < $idd1[$n1]) {
										$neg++;
									}
								}
							}
						}
					}
//			
					if($neg < 2){
//						
//	Boost		
						$ntest = 0;
						if($ifn[$n1] != '') {
							if($ifn[$n2] != '') {
								if($ifn[$n1] == $ifn[$n2]) {
									$bon++;
									if($ifn[$n1] != $ifn1[$n1]) {
										$bon++;
									}	
									$ntest++;
								}
							}
						}
//						
						if($ifn1[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn1[$n1] == $ifn1[$n2]) || ($ifn1[$n1] == $ifn2[$n2]) || ($ifn1[$n1] == $ifn3[$n2])
								 || ($ifn1[$n1] == $ifn4[$n2]) || ($ifn1[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}	
						}
//						
						if($ifn2[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn2[$n1] == $ifn1[$n2]) || ($ifn2[$n1] == $ifn2[$n2]) || ($ifn2[$n1] == $ifn3[$n2])
								 || ($ifn2[$n1] == $ifn4[$n2]) || ($ifn2[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn3[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn3[$n1] == $ifn1[$n2]) || ($ifn3[$n1] == $ifn2[$n2]) || ($ifn3[$n1] == $ifn3[$n2])
								 || ($ifn3[$n1] == $ifn4[$n2]) || ($ifn3[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn4[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn4[$n1] == $ifn1[$n2]) || ($ifn4[$n1] == $ifn2[$n2]) || ($ifn4[$n1] == $ifn3[$n2])
								 || ($ifn4[$n1] == $ifn4[$n2]) || ($ifn4[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn5[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn5[$n1] == $ifn1[$n2]) || ($ifn5[$n1] == $ifn2[$n2]) || ($ifn5[$n1] == $ifn3[$n2])
								 || ($ifn5[$n1] == $ifn4[$n2]) || ($ifn5[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
						if($ntest == 0) {
							$neg++;
						}
//			
						if($neg < 2) {
//						
							if($ifd2[$n1] != '') {
								if($ifd2[$n2] != '') {
									$ant++;
									if($ifd2[$n1] == $ifd2[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifd3[$n1] != '') {
								if($ifd3[$n2] != '') {
									$ant++;
									if($ifd3[$n1] == $ifd3[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifp[$n1] != '') {
								if($ifp[$n2] != '') {
									$ant++;
									if($ifp[$n1] == $ifp[$n2]) {
										$pos++;
									}
									else {
										$neg++;
//	???									$halv++;
									}
								}
							}
//			
							if($neg < 2) {
//						
								if($idd2[$n1] != '') {
									if($idd2[$n2] != '') {
										$ant++;
										if($idd2[$n1] == $idd2[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idd3[$n1] != '') {
									if($idd3[$n2] != '') {
										$ant++;
										if($idd3[$n1] == $idd3[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idp[$n1] != '') {
									if($idp[$n2] != '') {
										$ant++;
										if($idp[$n1] == $idp[$n2]) {
											$pos++;
										}
										else {
											$neg++;
//	???										$halv++;
										}
									}
								}
//			
								if($neg < 2) {
//	Boost					
									$ntest = 0;
									if($ien[$n1] != '') {
										if($ien[$n2] != '') {
											if($ien[$n1] == $ien[$n2]) {
												$bon++;
												if($ien[$n1] != $ien1[$n1]) {
													$bon++;
												}	
												$ntest++;
											}
										}
									}
//						
									if($ien1[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien1[$n1] == $ien1[$n2]) || ($ien1[$n1] == $ien2[$n2]) || ($ien1[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien2[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien2[$n1] == $ien1[$n2]) || ($ien2[$n1] == $ien2[$n2]) || ($ien2[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien3[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien3[$n1] == $ien1[$n2]) || ($ien3[$n1] == $ien2[$n2]) || ($ien3[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
									if($ntest == 0) {
										$halv++;
									}
//	Boost					
									if($kp1[$n1] != '') {
										if($kp1[$n2] != '') {
											if(($kp1[$n1] == $kp1[$n2]) && (strlen($ifd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp2[$n1] != '') {
										if($kp2[$n2] != '') {
											if(($kp2[$n1] == $kp2[$n2]) && (strlen($idd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp3[$n1] != '') {
										if($kp3[$n2] != '') {
											if($kp3[$n1] == $kp3[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp4[$n1] != '') {
										if($kp4[$n2] != '') {
											if($kp4[$n1] == $kp4[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp5[$n1] != '') {
										if($kp5[$n2] != '') {
											if($kp5[$n1] == $kp5[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp6[$n1] != '') {
										if($kp6[$n2] != '') {
											if($kp6[$n1] == $kp6[$n2]) {
												$bon++;
											}
										}
									}
//	Tolka betydelsen av familj FAMC 
//					
//	Båda har barmfamilj men olika familjer
									if($fmc[$n1] != '') {
										if($fmc[$n2] != '') {
											if($fmc[$n1] != $fmc[$n2]) {
												$plus = '?';
												$halv++;
											}
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] != '') {
										if($fmc[$n2] == '') {
											$plus = '+';
											$bon++;
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] == '') {
										if($fmc[$n2] != '') {
											$plus = '+';
											$bon++;
										}
									}
								}
							}
						}
					}	
				}
				if($halv >= 2) {
					$neg++;
				}
//	
				if(($bon > 2) && ($pos > 2) && ($pos > $neg) && ($neg < 2) && ($ant > 3)) {
					$tmp = $pos/$ant;
					if($tmp >= 0.7) {
						$diff = $ant - $pos;
						$totp = $bon + $pos - $diff - $neg;
						if($plus == '+') {
							$totp = $totp + 1;
						}	
						if($plus == '-') {
							$totp = $totp - 2;
						}	
						if($totp > 7) {
							if($totp > 15) {
								$tots = 9;
							}	
							else {
								$tots = $totp - 7;
							}
//
							$fellista[]="Poäng = ".$tots."(".$plus.") Jämför:"
							.$num[$n1].", ".$nrad[$n1].", ".$drad[$n1].", ".$prad[$n1]." med:"
							.$num[$n2].", ".$nrad[$n2].", ".$drad[$n2].", ".$prad[$n2];
							$kant++;
						}
					}	
				}
				$n2++;
			}
			$n1++;
		}
//	
//	steg 2 avslutat	
//
//	Steg 3 startar	
//	
		$handle=fopen($filename,"r");
//	
		$n1 = $min;
		$nsist = 0;
		$znum = '';
		$kand = '';
		$birt = '';
		$deat = '';
		$ifnx = '';
		$ienx = '';
		$ifdx = '';
		$ifpx = '';
		$iddx = '';
		$idpx = '';
		$famc = '';
		$nradx = '';
		$dradx = '';
		$pradx = '';
		$dpref = '';
		$sexx = '';
		$fam = ''; 
		$ind = '';
		$head = 'ON';	
//	Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
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
//							echo "Första individen/relationen = ".$str." <br/>";
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
//	Ny post, ladda upp tidigare data
					$aar = 0;
					$faar = 0;
					$daar = 0;
					$faar = (int)substr($ifdx,0,4);
					$daar = (int)substr($iddx,0,4);
					if(($faar >= 1850) && ($faar <= 1899)) {
						$aar = $faar;
					}
					if(($daar >= 1850) && ($daar <= 1899)) {
						$aar = $daar;
					}
					if(($znum != '') && ($ind == 'J') && ($aar >= 1850) && ($aar <= 1899)) {
						$n1++;
						$num[$n1]=$znum;
						$ifn[$n1]=$ifnx;
						$ien[$n1]=$ienx;
						$ifd[$n1]=$ifdx;
						$ifp[$n1]=$ifpx;
						$idd[$n1]=$iddx;
						$idp[$n1]=$idpx;
						$fmc[$n1]=$famc;
						$nrad[$n1]=$nradx;
						$drad[$n1]=$dradx;
						$prad[$n1]=$pradx;
						$isex[$n1]=$sexx;
					}
					$ifnx = '';
					$ienx = '';
					$ifdx = '';
					$ifpx = '';
					$iddx = '';
					$idpx = '';
					$famc = '';
					$nradx = '';
					$dradx = '';
					$pradx = '';
					$dpref = '';
					$sexx = '';
//	Ny post, sök identitet
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal == '@') {
							if(substr($str,$zmax,5) == '@ IND') {
								$ind = 'J';
								$fam = '';
							}
							elseif(substr($str,$zmax,5) == '@ FAM') {
								$ind = '';
								$fam = 'J';
							}
							else {
								$ind = '';
								$fam = '';
								}
						}	
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
//	Fortsätt samla data
				$tag1 = substr($str,0,1);
				$tagk = substr($str,2,3);
				$tagl = substr($str,2,4);
				$tag7 = substr($str,6,1);
				$tlen = strlen($str);
				$str = substr($str,7,($tlen-7));
//
				if($tag1 == '1') {
					$birt = '';
					$deat = '';
				}
//
				if($tagk == 'SEX') {
					$sexx = $tag7;
				}
				elseif($tagl == 'BIRT') {
					$birt = 'J';
				}
				elseif($tagk == 'CHR') {
					if(($ifdx == '') && ($ifpx == '')) {
						$birt = 'J';
//						echo "Enbart döpt <br/>";
					}	
				}
				elseif($tagl == 'DEAT') {
					$deat = 'J';
				}
				elseif($tagl == 'BURI') {
					if(($iddx == '') && ($idpx == '')) {
						$deat = 'J';
//						echo "Enbart begravd <br/>";
					}	
				}
				elseif($tagl == 'RGDF') {
					$ifnx = $str;
				}
				elseif($tagl == 'RGDE') {
					$ienx = $str;
				}
				elseif(($tagl == 'RGDD') && ($birt == 'J')) {
					$ifdx = $str;
				}
				elseif(($tagl == 'RGDP') && ($birt == 'J')) {
					$ifpx = $str;
				}
				elseif(($tagl == 'RGDD') && ($deat == 'J')) {
					$iddx = $str;
				}
				elseif(($tagl == 'RGDP') && ($deat == 'J')) {
					$idpx = $str;
				}
				elseif($tagl == 'FAMC') {
					$famc = $str;
				}
				elseif($tagl == 'NAME') {
					$nradx = $str;
				}
				elseif(($tagl == 'DATE') && ($birt == 'J')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'f. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($birt == 'J')) {
					if($dpref == '') {
						$dpref = 'f. ';
						$pradx = $dpref.$str;
					}
					else {
						$pradx = $str;
					}
				}
				elseif(($tagl == 'DATE') && ($deat == 'J') && ($dpref == '')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'd. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($deat == 'J')) {
					if($dpref == '') {
						$pradx = 'd. '.$str;
					}	
					if($dpref == 'd. ') {
						$pradx = $str;
					}	
				}
				else {
//	ointressant rad
				}
			}		
		}
		fclose($handle);
//
		$max = $n1;
		$n1 = $min + 1;
//
		while($n1 <= $max) {
				$kp1[$n1]='';
				$kp2[$n1]='';
				if(($ifd[$n1] != '') && ($ifp[$n1] != '')); {
					$kp1[$n1]=$ifd[$n1].$ifp[$n1];
				}	
				if(($idd[$n1] != '') && ($idp[$n1] != '')); {
					$kp2[$n1]=$idd[$n1].$idp[$n1];
				}	
//						
				$flen = strlen($ifn[$n1]);		
				$ifn1[$n1] = '';
				$ifn2[$n1] = '';
				$ifn3[$n1] = '';
				$ifn4[$n1] = '';
				$ifn5[$n1] = '';
//			
				$fx = 0;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn1[$n1] = $ifn1[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn2[$n1] = $ifn2[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn3[$n1] = $ifn3[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn4[$n1] = $ifn4[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				if($fx <= $flen) {
					$ifn5[$n1] = substr($ifn[$n1],$fx,($flen-$fx));
				}
//						
				$elen = strlen($ien[$n1]);		
				$ien1[$n1] = '';
				$ien2[$n1] = '';
				$ien3[$n1] = '';
//
				$ex = 0;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien1[$n1] = $ien1[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien2[$n1] = $ien2[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				if($ex <= $elen) {
					$ien3[$n1] = substr($ien[$n1],$ex,($elen-$ex));
				}
//						
				$blen = strlen($ifd[$n1]);		
				$ifd1[$n1] = '';
				$ifd2[$n1] = '';
				$ifd3[$n1] = '';
//			
				$bx = 0;
				if(($bx < $blen) && (substr($ifd[$n1],$bx,4) != '')) {
					$ifd1[$n1] = substr($ifd[$n1],$bx,4);
				}
				$bx = 4;
				if(($bx <= $blen) && (substr($ifd[$n1],$bx,2) != '')) {
					$ifd2[$n1] = substr($ifd[$n1],$bx,2);
				}
				$bx = 6;
				if($bx <= $blen) {
					$ifd3[$n1] = substr($ifd[$n1],$bx,2);
				}	
//						
				$dlen = strlen($idd[$n1]);		
				$idd1[$n1] = '';
				$idd2[$n1] = '';
				$idd3[$n1] = '';
//			
				$dx = 0;
				if(($dx < $dlen) && (substr($idd[$n1],$dx,4) != '')) {
					$idd1[$n1] = substr($idd[$n1],$dx,4);
				}
				$dx = 4;
				if(($dx <= $dlen) && (substr($idd[$n1],$dx,2) != '')) {
					$idd2[$n1] = substr($idd[$n1],$dx,2);
				}
				$dx = 6;
				if($dx <= $dlen) {
					$idd3[$n1] = substr($idd[$n1],$dx,2);
				}	
				$kp3[$n1]='';
				$kp4[$n1]='';
				if(($ifd1[$n1] != '') && ($ifp[$n1] != '')); {
					$kp3[$n1]=$ifd1[$n1].$ifp[$n1];
				}	
				if(($idd1[$n1] != '') && ($idp[$n1] != '')); {
					$kp4[$n1]=$idd1[$n1].$idp[$n1];
				}	
				$kp5[$n1]='';
				$kp6[$n1]='';
				if(($ifd2[$n1] != '') && ($ifd3[$n1] != '') && ($ifp[$n1] != '')); {
					$kp5[$n1]=$ifd2[$n1].$ifd3[$n1].$ifp[$n1];
				}	
				if(($idd2[$n1] != '') && ($idd3[$n1] != '') && ($idp[$n1] != '')); {
					$kp6[$n1]=$idd2[$n1].$idd3[$n1].$idp[$n1];
				}
			$n1++;
		}			
//
//	Loop 1		
		$n1 = $min + 1;
		$min = $max;
		$nsist = $max - 1;
		while($n1 <= $nsist) {
//	Loop 2	
			$n2 = $n1 + 1;
			while($n2 <= $max) {
//	
				$bon = 0;
				$pos = 0;
				$neg = 0;
				$ant = 0;
				$halv = 0;
				$plus = ' ';
//	Individerna har olika kön					
				if($isex[$n1] != '') {
					if($isex[$n2] != '') {
						if($isex[$n1] != $isex[$n2]) {
							$neg++;
						}		
					}
				}
//	Båda har barnfamilj och dessutom samma familj (syskon)					
				if($fmc[$n1] != '') {
					if($fmc[$n2] != '') {
						if($fmc[$n1] == $fmc[$n2]) {
							$plus = '-';
							$neg++;
						}		
					}
				}
//	Boost					
				if(($ifd[$n1] != '') && (strlen($ifd[$n1]) == 8)) {
					if($ifd[$n2] != '') {
						if($ifd[$n1] == $ifd[$n2]) {
							$bon++;
						}
					}
				}
				if($ifd1[$n1] != '') {
					if($ifd1[$n2] != '') {
						$ant++;
						if($ifd1[$n1] == $ifd1[$n2]) {
							$pos++;
						}
						else {
							$neg++;
							if($ifd1[$n1] < $ifd1[$n2]) {
								if(($ifd1[$n1] + 10) < $ifd1[$n2]) {
									$neg++;
								}
							}	
							else {
								if(($ifd1[$n2] + 10) < $ifd1[$n1]) {
									$neg++;
								}
							}
						}
					}
				}
//	Bryt om ett dödår är mindre än det andra födelseåret
				if($ifd1[$n1] != '') {
					if($idd1[$n2] != '') {
						if($idd1[$n2] < $ifd1[$n1]) {
							$neg++;
							$neg++;
						}
					}
				}
				if($ifd1[$n2] != '') {
					if($idd1[$n1] != '') {
						if($idd1[$n1] < $ifd1[$n2]) {
							$neg++;
							$neg++;
						}
					}
				}
//			
				if($neg < 2){
//						
//	Boost					
					if(($idd[$n1] != '') && (strlen($idd[$n1]) == 8)) {
						if($idd[$n2] != '') {
							if($idd[$n1] == $idd[$n2]) {
								$bon++;
							}
						}
					}
//						
					if($idd1[$n1] != '') {
						if($idd1[$n2] != '') {
							$ant++;
							if($idd1[$n1] == $idd1[$n2]) {
								$pos++;
							}
							else {
								$neg++;
								if($idd1[$n1] < $idd1[$n2]) {
									if(($idd1[$n1] + 10) < $idd1[$n2]) {
										$neg++;
									}
								}	
								else {
									if(($idd1[$n2] + 10) < $idd1[$n1]) {
										$neg++;
									}
								}
							}
						}
					}
//			
					if($neg < 2){
//						
//	Boost		
						$ntest = 0;
						if($ifn[$n1] != '') {
							if($ifn[$n2] != '') {
								if($ifn[$n1] == $ifn[$n2]) {
									$bon++;
									if($ifn[$n1] != $ifn1[$n1]) {
										$bon++;
									}	
									$ntest++;
								}
							}
						}
//						
						if($ifn1[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn1[$n1] == $ifn1[$n2]) || ($ifn1[$n1] == $ifn2[$n2]) || ($ifn1[$n1] == $ifn3[$n2])
								 || ($ifn1[$n1] == $ifn4[$n2]) || ($ifn1[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}	
						}
//						
						if($ifn2[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn2[$n1] == $ifn1[$n2]) || ($ifn2[$n1] == $ifn2[$n2]) || ($ifn2[$n1] == $ifn3[$n2])
								 || ($ifn2[$n1] == $ifn4[$n2]) || ($ifn2[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn3[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn3[$n1] == $ifn1[$n2]) || ($ifn3[$n1] == $ifn2[$n2]) || ($ifn3[$n1] == $ifn3[$n2])
								 || ($ifn3[$n1] == $ifn4[$n2]) || ($ifn3[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn4[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn4[$n1] == $ifn1[$n2]) || ($ifn4[$n1] == $ifn2[$n2]) || ($ifn4[$n1] == $ifn3[$n2])
								 || ($ifn4[$n1] == $ifn4[$n2]) || ($ifn4[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn5[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn5[$n1] == $ifn1[$n2]) || ($ifn5[$n1] == $ifn2[$n2]) || ($ifn5[$n1] == $ifn3[$n2])
								 || ($ifn5[$n1] == $ifn4[$n2]) || ($ifn5[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
						if($ntest == 0) {
							$neg++;
						}
//			
						if($neg < 2) {
//						
							if($ifd2[$n1] != '') {
								if($ifd2[$n2] != '') {
									$ant++;
									if($ifd2[$n1] == $ifd2[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifd3[$n1] != '') {
								if($ifd3[$n2] != '') {
									$ant++;
									if($ifd3[$n1] == $ifd3[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifp[$n1] != '') {
								if($ifp[$n2] != '') {
									$ant++;
									if($ifp[$n1] == $ifp[$n2]) {
										$pos++;
									}
									else {
										$neg++;
//	???									$halv++;
									}
								}
							}
//			
							if($neg < 2) {
//						
								if($idd2[$n1] != '') {
									if($idd2[$n2] != '') {
										$ant++;
										if($idd2[$n1] == $idd2[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idd3[$n1] != '') {
									if($idd3[$n2] != '') {
										$ant++;
										if($idd3[$n1] == $idd3[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idp[$n1] != '') {
									if($idp[$n2] != '') {
										$ant++;
										if($idp[$n1] == $idp[$n2]) {
											$pos++;
										}
										else {
											$neg++;
//	???										$halv++;
										}
									}
								}
//			
								if($neg < 2) {
//	Boost					
									$ntest = 0;
									if($ien[$n1] != '') {
										if($ien[$n2] != '') {
											if($ien[$n1] == $ien[$n2]) {
												$bon++;
												if($ien[$n1] != $ien1[$n1]) {
													$bon++;
												}	
												$ntest++;
											}
										}
									}
//						
									if($ien1[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien1[$n1] == $ien1[$n2]) || ($ien1[$n1] == $ien2[$n2]) || ($ien1[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien2[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien2[$n1] == $ien1[$n2]) || ($ien2[$n1] == $ien2[$n2]) || ($ien2[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien3[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien3[$n1] == $ien1[$n2]) || ($ien3[$n1] == $ien2[$n2]) || ($ien3[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
									if($ntest == 0) {
										$halv++;
									}
//	Boost					
									if($kp1[$n1] != '') {
										if($kp1[$n2] != '') {
											if(($kp1[$n1] == $kp1[$n2]) && (strlen($ifd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp2[$n1] != '') {
										if($kp2[$n2] != '') {
											if(($kp2[$n1] == $kp2[$n2]) && (strlen($idd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp3[$n1] != '') {
										if($kp3[$n2] != '') {
											if($kp3[$n1] == $kp3[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp4[$n1] != '') {
										if($kp4[$n2] != '') {
											if($kp4[$n1] == $kp4[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp5[$n1] != '') {
										if($kp5[$n2] != '') {
											if($kp5[$n1] == $kp5[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp6[$n1] != '') {
										if($kp6[$n2] != '') {
											if($kp6[$n1] == $kp6[$n2]) {
												$bon++;
											}
										}
									}
//	Tolka betydelsen av familj FAMC 
//					
//	Båda har barmfamilj men olika familjer
									if($fmc[$n1] != '') {
										if($fmc[$n2] != '') {
											if($fmc[$n1] != $fmc[$n2]) {
												$plus = '?';
												$halv++;
											}
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] != '') {
										if($fmc[$n2] == '') {
											$plus = '+';
											$bon++;
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] == '') {
										if($fmc[$n2] != '') {
											$plus = '+';
											$bon++;
										}
									}
								}
							}
						}
					}	
				}
				if($halv >= 2) {
					$neg++;
				}
//	
				if(($bon > 2) && ($pos > 2) && ($pos > $neg) && ($neg < 2) && ($ant > 3)) {
					$tmp = $pos/$ant;
					if($tmp >= 0.7) {
						$diff = $ant - $pos;
						$totp = $bon + $pos - $diff - $neg;
						if($plus == '+') {
							$totp = $totp + 1;
						}	
						if($plus == '-') {
							$totp = $totp - 2;
						}	
						if($totp > 7) {
							if($totp > 15) {
								$tots = 9;
							}	
							else {
								$tots = $totp - 7;
							}
//
							$fellista[]="Poäng = ".$tots."(".$plus.") Jämför:"
							.$num[$n1].", ".$nrad[$n1].", ".$drad[$n1].", ".$prad[$n1]." med:"
							.$num[$n2].", ".$nrad[$n2].", ".$drad[$n2].", ".$prad[$n2];
							$kant++;
						}
					}	
				}
				$n2++;
			}
			$n1++;
		}
//	
//	steg 3 avslutat	
//
//	Steg 4 startar	
//	
		$handle=fopen($filename,"r");
//	
		$n1 = $min;
		$nsist = 0;
		$znum = '';
		$kand = '';
		$birt = '';
		$deat = '';
		$ifnx = '';
		$ienx = '';
		$ifdx = '';
		$ifpx = '';
		$iddx = '';
		$idpx = '';
		$famc = '';
		$nradx = '';
		$dradx = '';
		$pradx = '';
		$dpref = '';
		$sexx = '';
		$fam = ''; 
		$ind = '';
		$head = 'ON';	
//	Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
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
//							echo "Första individen/relationen = ".$str." <br/>";
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
//	Ny post, ladda upp tidigare data
					$aar = 0;
					$faar = 0;
					$daar = 0;
					$faar = (int)substr($ifdx,0,4);
					$daar = (int)substr($iddx,0,4);
					if($faar >= 1900) {
						$aar = $faar;
					}
					if($daar >= 1900) {
						$aar = $daar;
					}
					if(($znum != '') && ($ind == 'J') && ($aar >= 1900)) {
						$n1++;
						$num[$n1]=$znum;
						$ifn[$n1]=$ifnx;
						$ien[$n1]=$ienx;
						$ifd[$n1]=$ifdx;
						$ifp[$n1]=$ifpx;
						$idd[$n1]=$iddx;
						$idp[$n1]=$idpx;
						$fmc[$n1]=$famc;
						$nrad[$n1]=$nradx;
						$drad[$n1]=$dradx;
						$prad[$n1]=$pradx;
						$isex[$n1]=$sexx;
					}
					$ifnx = '';
					$ienx = '';
					$ifdx = '';
					$ifpx = '';
					$iddx = '';
					$idpx = '';
					$famc = '';
					$nradx = '';
					$dradx = '';
					$pradx = '';
					$dpref = '';
					$sexx = '';
//	Ny post, sök identitet
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal == '@') {
							if(substr($str,$zmax,5) == '@ IND') {
								$ind = 'J';
								$fam = '';
							}
							elseif(substr($str,$zmax,5) == '@ FAM') {
								$ind = '';
								$fam = 'J';
							}
							else {
								$ind = '';
								$fam = '';
								}
						}	
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
//	Fortsätt samla data
				$tag1 = substr($str,0,1);
				$tagk = substr($str,2,3);
				$tagl = substr($str,2,4);
				$tag7 = substr($str,6,1);
				$tlen = strlen($str);
				$str = substr($str,7,($tlen-7));
//
				if($tag1 == '1') {
					$birt = '';
					$deat = '';
				}
//
				if($tagk == 'SEX') {
					$sexx = $tag7;
				}
				elseif($tagl == 'BIRT') {
					$birt = 'J';
				}
				elseif($tagk == 'CHR') {
					if(($ifdx == '') && ($ifpx == '')) {
						$birt = 'J';
//						echo "Enbart döpt <br/>";
					}	
				}
				elseif($tagl == 'DEAT') {
					$deat = 'J';
				}
				elseif($tagl == 'BURI') {
					if(($iddx == '') && ($idpx == '')) {
						$deat = 'J';
//						echo "Enbart begravd <br/>";
					}	
				}
				elseif($tagl == 'RGDF') {
					$ifnx = $str;
				}
				elseif($tagl == 'RGDE') {
					$ienx = $str;
				}
				elseif(($tagl == 'RGDD') && ($birt == 'J')) {
					$ifdx = $str;
				}
				elseif(($tagl == 'RGDP') && ($birt == 'J')) {
					$ifpx = $str;
				}
				elseif(($tagl == 'RGDD') && ($deat == 'J')) {
					$iddx = $str;
				}
				elseif(($tagl == 'RGDP') && ($deat == 'J')) {
					$idpx = $str;
				}
				elseif($tagl == 'FAMC') {
					$famc = $str;
				}
				elseif($tagl == 'NAME') {
					$nradx = $str;
				}
				elseif(($tagl == 'DATE') && ($birt == 'J')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'f. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($birt == 'J')) {
					if($dpref == '') {
						$dpref = 'f. ';
						$pradx = $dpref.$str;
					}
					else {
						$pradx = $str;
					}
				}
				elseif(($tagl == 'DATE') && ($deat == 'J') && ($dpref == '')) {
					$tstx = $str; // fimpa ()
					$lend = strlen($str);
					$tstp = substr($str,0,1);
					if($tstp == '(') {
						$tstx = substr($str,1,$lend-2); }
					$dpref = 'd. ';
					$dradx = $dpref.$tstx;
				}
				elseif(($tagl == 'PLAC') && ($deat == 'J')) {
					if($dpref == '') {
						$pradx = 'd. '.$str;
					}	
					if($dpref == 'd. ') {
						$pradx = $str;
					}	
				}
				else {
//	ointressant rad
				}
			}		
		}
		fclose($handle);
//
		$max = $n1;
		$n1 = $min + 1;
//
		while($n1 <= $max) {
				$kp1[$n1]='';
				$kp2[$n1]='';
				if(($ifd[$n1] != '') && ($ifp[$n1] != '')); {
					$kp1[$n1]=$ifd[$n1].$ifp[$n1];
				}	
				if(($idd[$n1] != '') && ($idp[$n1] != '')); {
					$kp2[$n1]=$idd[$n1].$idp[$n1];
				}	
//						
				$flen = strlen($ifn[$n1]);		
				$ifn1[$n1] = '';
				$ifn2[$n1] = '';
				$ifn3[$n1] = '';
				$ifn4[$n1] = '';
				$ifn5[$n1] = '';
//			
				$fx = 0;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn1[$n1] = $ifn1[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn2[$n1] = $ifn2[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn3[$n1] = $ifn3[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				while(($fx <= $flen) && (substr($ifn[$n1],$fx,1) != ',')) {
					$ifn4[$n1] = $ifn4[$n1].substr($ifn[$n1],$fx,1);
					$fx++;
				}
				$fx++;
				if($fx <= $flen) {
					$ifn5[$n1] = substr($ifn[$n1],$fx,($flen-$fx));
				}
//						
				$elen = strlen($ien[$n1]);		
				$ien1[$n1] = '';
				$ien2[$n1] = '';
				$ien3[$n1] = '';
//
				$ex = 0;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien1[$n1] = $ien1[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				while(($ex <= $elen) && (substr($ien[$n1],$ex,1) != ',')) {
					$ien2[$n1] = $ien2[$n1].substr($ien[$n1],$ex,1);
					$ex++;
				}
				$ex++;
				if($ex <= $elen) {
					$ien3[$n1] = substr($ien[$n1],$ex,($elen-$ex));
				}
//						
				$blen = strlen($ifd[$n1]);		
				$ifd1[$n1] = '';
				$ifd2[$n1] = '';
				$ifd3[$n1] = '';
//			
				$bx = 0;
				if(($bx < $blen) && (substr($ifd[$n1],$bx,4) != '')) {
					$ifd1[$n1] = substr($ifd[$n1],$bx,4);
				}
				$bx = 4;
				if(($bx <= $blen) && (substr($ifd[$n1],$bx,2) != '')) {
					$ifd2[$n1] = substr($ifd[$n1],$bx,2);
				}
				$bx = 6;
				if($bx <= $blen) {
					$ifd3[$n1] = substr($ifd[$n1],$bx,2);
				}	
//						
				$dlen = strlen($idd[$n1]);		
				$idd1[$n1] = '';
				$idd2[$n1] = '';
				$idd3[$n1] = '';
//			
				$dx = 0;
				if(($dx < $dlen) && (substr($idd[$n1],$dx,4) != '')) {
					$idd1[$n1] = substr($idd[$n1],$dx,4);
				}
				$dx = 4;
				if(($dx <= $dlen) && (substr($idd[$n1],$dx,2) != '')) {
					$idd2[$n1] = substr($idd[$n1],$dx,2);
				}
				$dx = 6;
				if($dx <= $dlen) {
					$idd3[$n1] = substr($idd[$n1],$dx,2);
				}	
				$kp3[$n1]='';
				$kp4[$n1]='';
				if(($ifd1[$n1] != '') && ($ifp[$n1] != '')); {
					$kp3[$n1]=$ifd1[$n1].$ifp[$n1];
				}	
				if(($idd1[$n1] != '') && ($idp[$n1] != '')); {
					$kp4[$n1]=$idd1[$n1].$idp[$n1];
				}	
				$kp5[$n1]='';
				$kp6[$n1]='';
				if(($ifd2[$n1] != '') && ($ifd3[$n1] != '') && ($ifp[$n1] != '')); {
					$kp5[$n1]=$ifd2[$n1].$ifd3[$n1].$ifp[$n1];
				}	
				if(($idd2[$n1] != '') && ($idd3[$n1] != '') && ($idp[$n1] != '')); {
					$kp6[$n1]=$idd2[$n1].$idd3[$n1].$idp[$n1];
				}
			$n1++;
		}			
//
//	Loop 1		
		$n1 = $min + 1;
		$min = $max;
		$nsist = $max - 1;
		while($n1 <= $nsist) {
//	Loop 2	
			$n2 = $n1 + 1;
			while($n2 <= $max) {
//	
				$bon = 0;
				$pos = 0;
				$neg = 0;
				$ant = 0;
				$halv = 0;
				$plus = ' ';
//	Individerna har olika kön					
				if($isex[$n1] != '') {
					if($isex[$n2] != '') {
						if($isex[$n1] != $isex[$n2]) {
							$neg++;
						}		
					}
				}
//	Båda har barnfamilj och dessutom samma familj (syskon)					
				if($fmc[$n1] != '') {
					if($fmc[$n2] != '') {
						if($fmc[$n1] == $fmc[$n2]) {
							$plus = '-';
							$neg++;
						}		
					}
				}
//	Boost					
				if(($ifd[$n1] != '') && (strlen($ifd[$n1]) == 8)) {
					if($ifd[$n2] != '') {
						if($ifd[$n1] == $ifd[$n2]) {
							$bon++;
						}
					}
				}
				if($ifd1[$n1] != '') {
					if($ifd1[$n2] != '') {
						$ant++;
						if($ifd1[$n1] == $ifd1[$n2]) {
							$pos++;
						}
						else {
							$neg++;
							if($ifd1[$n1] < $ifd1[$n2]) {
								if(($ifd1[$n1] + 10) < $ifd1[$n2]) {
									$neg++;
								}
							}	
							else {
								if(($ifd1[$n2] + 10) < $ifd1[$n1]) {
									$neg++;
								}
							}
						}
					}
				}
//	Bryt om ett dödår är mindre än det andra födelseåret
				if($ifd1[$n1] != '') {
					if($idd1[$n2] != '') {
						if($idd1[$n2] < $ifd1[$n1]) {
							$neg++;
							$neg++;
						}
					}
				}
				if($ifd1[$n2] != '') {
					if($idd1[$n1] != '') {
						if($idd1[$n1] < $ifd1[$n2]) {
							$neg++;
							$neg++;
						}
					}
				}
//			
				if($neg < 2){
//						
//	Boost					
					if(($idd[$n1] != '') && (strlen($idd[$n1]) == 8)) {
						if($idd[$n2] != '') {
							if($idd[$n1] == $idd[$n2]) {
								$bon++;
							}
						}
					}
//						
					if($idd1[$n1] != '') {
						if($idd1[$n2] != '') {
							$ant++;
							if($idd1[$n1] == $idd1[$n2]) {
								$pos++;
							}
							else {
								$neg++;
								if($idd1[$n1] < $idd1[$n2]) {
									if(($idd1[$n1] + 10) < $idd1[$n2]) {
										$neg++;
									}
								}	
								else {
									if(($idd1[$n2] + 10) < $idd1[$n1]) {
										$neg++;
									}
								}
							}
						}
					}
//			
					if($neg < 2){
//						
//	Boost		
						$ntest = 0;
						if($ifn[$n1] != '') {
							if($ifn[$n2] != '') {
								if($ifn[$n1] == $ifn[$n2]) {
									$bon++;
									if($ifn[$n1] != $ifn1[$n1]) {
										$bon++;
									}	
									$ntest++;
								}
							}
						}
//						
						if($ifn1[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn1[$n1] == $ifn1[$n2]) || ($ifn1[$n1] == $ifn2[$n2]) || ($ifn1[$n1] == $ifn3[$n2])
								 || ($ifn1[$n1] == $ifn4[$n2]) || ($ifn1[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}	
						}
//						
						if($ifn2[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn2[$n1] == $ifn1[$n2]) || ($ifn2[$n1] == $ifn2[$n2]) || ($ifn2[$n1] == $ifn3[$n2])
								 || ($ifn2[$n1] == $ifn4[$n2]) || ($ifn2[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn3[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn3[$n1] == $ifn1[$n2]) || ($ifn3[$n1] == $ifn2[$n2]) || ($ifn3[$n1] == $ifn3[$n2])
								 || ($ifn3[$n1] == $ifn4[$n2]) || ($ifn3[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn4[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn4[$n1] == $ifn1[$n2]) || ($ifn4[$n1] == $ifn2[$n2]) || ($ifn4[$n1] == $ifn3[$n2])
								 || ($ifn4[$n1] == $ifn4[$n2]) || ($ifn4[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
//					
						if($ifn5[$n1] != '') {
							if($ifn1[$n2] != '') {
								$ant++;
								if(($ifn5[$n1] == $ifn1[$n2]) || ($ifn5[$n1] == $ifn2[$n2]) || ($ifn5[$n1] == $ifn3[$n2])
								 || ($ifn5[$n1] == $ifn4[$n2]) || ($ifn5[$n1] == $ifn5[$n2]) ) {
									$pos++;
									$ntest++;
								}
							}
						}
						if($ntest == 0) {
							$neg++;
						}
//			
						if($neg < 2) {
//						
							if($ifd2[$n1] != '') {
								if($ifd2[$n2] != '') {
									$ant++;
									if($ifd2[$n1] == $ifd2[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifd3[$n1] != '') {
								if($ifd3[$n2] != '') {
									$ant++;
									if($ifd3[$n1] == $ifd3[$n2]) {
										$pos++;
									}
									else {
										$neg++;
									}
								}
							}
//						
							if($ifp[$n1] != '') {
								if($ifp[$n2] != '') {
									$ant++;
									if($ifp[$n1] == $ifp[$n2]) {
										$pos++;
									}
									else {
										$neg++;
//	???									$halv++;
									}
								}
							}
//			
							if($neg < 2) {
//						
								if($idd2[$n1] != '') {
									if($idd2[$n2] != '') {
										$ant++;
										if($idd2[$n1] == $idd2[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idd3[$n1] != '') {
									if($idd3[$n2] != '') {
										$ant++;
										if($idd3[$n1] == $idd3[$n2]) {
											$pos++;
										}
										else {
											$neg++;
										}
									}
								}
//						
								if($idp[$n1] != '') {
									if($idp[$n2] != '') {
										$ant++;
										if($idp[$n1] == $idp[$n2]) {
											$pos++;
										}
										else {
											$neg++;
//	???										$halv++;
										}
									}
								}
//			
								if($neg < 2) {
//	Boost				
									$ntest = 0;
									if($ien[$n1] != '') {
										if($ien[$n2] != '') {
											if($ien[$n1] == $ien[$n2]) {
												$bon++;
												if($ien[$n1] != $ien1[$n1]) {
													$bon++;
												}	
												$ntest++;
											}
										}
									}
//						
									if($ien1[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien1[$n1] == $ien1[$n2]) || ($ien1[$n1] == $ien2[$n2]) || ($ien1[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien2[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien2[$n1] == $ien1[$n2]) || ($ien2[$n1] == $ien2[$n2]) || ($ien2[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
//						
									if($ien3[$n1] != '') {
										if($ien1[$n2] != '') {
											$ant++;
											if(($ien3[$n1] == $ien1[$n2]) || ($ien3[$n1] == $ien2[$n2]) || ($ien3[$n1] == $ien3[$n2])) {
												$pos++;
											}
											$ntest++;
										}
									}
									if($ntest == 0) {
										$halv++;
									}
//	Boost					
									if($kp1[$n1] != '') {
										if($kp1[$n2] != '') {
											if(($kp1[$n1] == $kp1[$n2]) && (strlen($ifd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp2[$n1] != '') {
										if($kp2[$n2] != '') {
											if(($kp2[$n1] == $kp2[$n2]) && (strlen($idd[$n1]) == 8)) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp3[$n1] != '') {
										if($kp3[$n2] != '') {
											if($kp3[$n1] == $kp3[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp4[$n1] != '') {
										if($kp4[$n2] != '') {
											if($kp4[$n1] == $kp4[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp5[$n1] != '') {
										if($kp5[$n2] != '') {
											if($kp5[$n1] == $kp5[$n2]) {
												$bon++;
											}
										}
									}
//	Boost					
									if($kp6[$n1] != '') {
										if($kp6[$n2] != '') {
											if($kp6[$n1] == $kp6[$n2]) {
												$bon++;
											}
										}
									}
//	Tolka betydelsen av familj FAMC 
//					
//	Båda har barmfamilj men olika familjer
									if($fmc[$n1] != '') {
										if($fmc[$n2] != '') {
											if($fmc[$n1] != $fmc[$n2]) {
												$plus = '?';
												$halv++;
											}
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] != '') {
										if($fmc[$n2] == '') {
											$plus = '+';
											$bon++;
										}
									}
//	En saknar barnfamilj (mor/dotter - far/som)					
									if($fmc[$n1] == '') {
										if($fmc[$n2] != '') {
											$plus = '+';
											$bon++;
										}
									}
								}
							}
						}
					}	
				}
				if($halv >= 2) {
					$neg++;
				}
//	
				if(($bon > 2) && ($pos > 2) && ($pos > $neg) && ($neg < 2) && ($ant > 3)) {
					$tmp = $pos/$ant;
					if($tmp >= 0.7) {
						$diff = $ant - $pos;
						$totp = $bon + $pos - $diff - $neg;
						if($plus == '+') {
							$totp = $totp + 1;
						}	
						if($plus == '-') {
							$totp = $totp - 2;
						}	
						if($totp > 7) {
							if($totp > 15) {
								$tots = 9;
							}	
							else {
								$tots = $totp - 7;
							}
//
							$fellista[]="Poäng = ".$tots."(".$plus.") Jämför:"
							.$num[$n1].", ".$nrad[$n1].", ".$drad[$n1].", ".$prad[$n1]." med:"
							.$num[$n2].", ".$nrad[$n2].", ".$drad[$n2].", ".$prad[$n2];
							$kant++;
						}
					}	
				}
				$n2++;
			}
			$n1++;
		}
//	
//	steg 4 avslutat	
//
		if($kant > 0) {
//
			$fellista=array_unique($fellista);
			rsort($fellista);
			$kant = 0;
			fwrite($handut," \r\n");
			foreach($fellista as $felrad) {
				$kant++;
				$xlen = 0;
				$xant = 0;
				$xblock = '';
				$xlen = strlen($felrad);
				while($xant <= $xlen) {
					$xtkn = substr($felrad,$xant,1);
					if($xtkn == ':') {
						$xblock = $xblock.$xtkn;
//						echo $xblock."<br/>";
						fwrite($handut,$xblock." \r\n");
						$xblock = '';
					}
					else {
						$xblock = $xblock.$xtkn;
					}
					$xant++;
				}
//				echo $xblock."<br/>";
//				echo "<br/>";
				fwrite($handut,$xblock." \r\n");
				fwrite($handut," \r\n");
				$xblock = '';
			}
			echo "<br/>";
			if($kant > 1) {
				echo "Bearbetningen sökte fram ".$kant." dubblettkandidater <br/>";
			}
			else {
				echo "Bearbetningen sökte fram ".$kant." dubblettkandidat <br/>";
			}	
			echo "Ta hand om den skapade filen RGDD.txt<br/>";
			fwrite($handut," \r\n");
			fwrite($handut,"Bearbetningen sökte fram ".$kant." dubblettkandidater. \r\n");
//		
			fwrite($handut," \r\n");
			fwrite($handut,"Tolkning av poäng: \r\n");
			fwrite($handut," \r\n");
			fwrite($handut,"Hög poäng = många uppgifter som har jämförts, ");
			fwrite($handut,"men inte nödvändigtvis säkrare dubblett. \r\n");
//			fwrite($handut," \r\n");
			fwrite($handut,"Plustecken anger förhållande Mor/Dotter respektive Far/Son, ");
			fwrite($handut,"en vanlig kombination vid dubbletter. \r\n");
//			fwrite($handut," \r\n");
			fwrite($handut,"Frågetecken anger osäkrare dubblett, ");
			fwrite($handut,"men i så fall kan även föräldrar vara felaltiga.  \r\n");
//			fwrite($handut," \r\n");
			fwrite($handut,"Minustecken kan vara tvillingar men även syskon, ");
			fwrite($handut,"dessa är dock oftast inte dubbletter. \r\n");
//			fwrite($handut," \r\n");
//			fwrite($handut," \r\n");
			fwrite($handut,"Olika efternamn förekommer ofta på äkta dubbletter. \r\n");
//			fwrite($handut," \r\n");
			fwrite($handut,"Förutom dubbletter, kan stora likheter även vara en ");
			fwrite($handut,"larmsignal för andra typer av felaktigheter. \r\n");
			fwrite($handut," \r\n");

		}
		else {
			echo "<br/>";
			echo "Inga kandidater hittade, sökningen avslutad. <br/>";
			echo "Filen RGDD.txt skapad men innehåller bara rubriken.<br/>";
			fwrite($handut," \r\n");
			fwrite($handut,"Inga kandidater hittade, sökningen avslutad. \r\n");
		}
	fclose($handut);
//
	}
//**** extra fixen
	}
	else
	{
		echo "<br/>";
		echo "Filen ".$filename." saknas, programmet avbryts <br/>";
	}
	echo "<br/>";
	echo "Program klart ".date('Y-m-d')." / ".date('H:i:s')."<br/>";
}
?>

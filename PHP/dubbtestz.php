<?PHP
/*
Uppdelning i män och kvinnor om filstorlekarna fortfarande blir för stora med dubbsplit.
*/
require 'initbas.php';
//
$filein=$directory . "RGD9.GED";
//
$filem1=$directory . "RGD91.GED";
$filek1=$directory . "RGD92.GED";
//
if(file_exists($filem1))
{
	echo $filem1." finns redan, programmet avbryts<br/>";
}
else
{
//
	if(file_exists($filein))
	{
		$handin=fopen($filein,"r");
		$handm1=fopen($filem1,"w");
		$handk1=fopen($filek1,"w");
//
		$zant = 0;
		$mant = 0;
		$kant = 0;
		$zind = '';
		$ztag[] = '';
//	Läs in indatafilen				
		$linein = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($linein as $radnummer => $str)
		{
//	hitta idnummer för individ/relation
			$ztag = substr($str,0,3);
			$tagg = substr($str,2,4);
			if($ztag == '0 @') 
			{
				if(($ztag == '0 @') && ($zind = 'JA')) 
				{
//	Skriv person
//
					if($zant > 0) {
						for($i=1;$i<=$zant;$i++)
						{
							$ztxt = $zlista[$i];
							if($zsex == 'M') {
								fwrite($handm1,$ztxt."\r\n"); }
							else {
								fwrite($handk1,$ztxt."\r\n"); }
//echo $ztxt.'<br/>';
						}
						for($i=1;$i<=$zant;$i++)
						{
							unset($zlista[$i]);
						}
						if($zsex == 'M') {
							$mant++; }
						else {
							$kant++; }
//
						$zant = 0;
//echo '<br/>';
					}	
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
							if(substr($str,$zmax,5) == '@ IND') {
								$zind = 'JA';
							}
							if(substr($str,$zmax,5) == '@ FAM') {
								$zind = 'NEJ';
							}
							$zmax = $zlen; 
						}
						$zmax++;
					}
				}
			}
//
			if($tagg == 'SEX ') {
				$zsex = substr($str,6,1); 
			}
//	Skippa taggar som ej används i dubblettesten	
			$ztst = '';
			if($tagg == 'OCCU') {
			}
			elseif($tagg == 'RGDS') {
			}
			elseif($tagg == 'SOUR') {
			}
			elseif($tagg == 'FAMS') {
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
			elseif($tagg == 'AGE ') {
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
			}
			elseif($tagg == '_MRE') {
			}
			elseif($tagg == 'HEAD') {
			}
			elseif($tagg == 'TRLR') {
			}
			else {
				$ztst = 'JA';
			}	
//
//	Individ med aktiva taggar	
			if(($zind == 'JA') && ($ztst == 'JA')) {
				$zant++;
				$zlista[$zant] = $str;
//
			}
//
		}
//	Skriv avslutande person
//
		if($zant > 0) 
		{
			for($i=1;$i<=$zant;$i++)
			{
				$ztxt = $zlista[$i];
				if($zsex == 'M') {
					fwrite($handm1,$ztxt."\r\n"); }
				else {
					fwrite($handk1,$ztxt."\r\n"); }
//echo $ztxt.'<br/>';
			}
			for($i=1;$i<=$zant;$i++)
			{
				unset($zlista[$i]);
			}
			if($zsex == 'M') {
				$mant++; }
			else {
				$kant++; }
//
			$zant = 0;
//echo '<br/>';
		}	
//
		fclose($handin);
		fclose($handm1);
		fclose($handk1);
//
			echo "<br/>";
			echo "Antal Personer och Män respektive Kvinnor: <br/>";
			echo 'P = '.($mant+$kant).'<br/>';
			echo "<br/>";
			echo 'M = '.$mant.'<br/>';
			echo 'K = '.$kant.'<br/>';
			echo "<br/>";
//			echo "Program dubbsplitmk avslutad <br/>";
//			echo "<br/>";
	}
	else
	{
		echo "<br/>";
		echo "Fil ".$filein." saknas, programmet avbryts <br/>";
	}
}
?>
<?PHP
/*
Urval inför splittad dubblettkontroll.
*/
require 'initbas.php';
//
$filein=$directory . "RGD92.GED";
//
$filek0=$directory . "RGD920.GED";
$filek1=$directory . "RGD921.GED";
$filek2=$directory . "RGD922.GED";
$filek3=$directory . "RGD923.GED";
$filek4=$directory . "RGD924.GED";
$filek5=$directory . "RGD925.GED";
$filek6=$directory . "RGD926.GED";
$filek7=$directory . "RGD927.GED";
$filek8=$directory . "RGD928.GED";
$filek9=$directory . "RGD929.GED";
//
if(file_exists($filek1))
{
	echo $filek1." finns redan, programmet avbryts<br/>";
}
else
{
//
	if(file_exists($filein))
	{
		$handin=fopen($filein,"r");
		$handk0=fopen($filek0,"w");
		$handk1=fopen($filek1,"w");
		$handk2=fopen($filek2,"w");
		$handk3=fopen($filek3,"w");
		$handk4=fopen($filek4,"w");
		$handk5=fopen($filek5,"w");
		$handk6=fopen($filek6,"w");
		$handk7=fopen($filek7,"w");
		$handk8=fopen($filek8,"w");
		$handk9=fopen($filek9,"w");
//
		$i = 0;
		$zant = 0;
		$zind = '';
		$chr = '';
		$birt = '';
		$buri = '';
		$deat = '';
		$zaar = '';
		$faar = '';
		$daar = '';
		$zdec = '';
		$zsex = '';
		$fgrp = 99;
		$dgrp = 99;
		$zpls = 0;
		$zmin = 0;
		$znum = '';
//	
		$pant = 0;
		$xant0 = 0;
		$xant1 = 0;
		$xant2 = 0;
		$xant3 = 0;
		$xant4 = 0;
		$xant5 = 0;
		$xant6 = 0;
		$xant7 = 0;
		$xant8 = 0;
		$xant9 = 0;
		$gant0 = 0;
		$gant1 = 0;
		$gant2 = 0;
		$gant3 = 0;
		$gant4 = 0;
		$gant5 = 0;
		$gant6 = 0;
		$gant7 = 0;
		$gant8 = 0;
		$gant9 = 0;
//	Läs in indatafilen				
		$linein = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($linein as $radnummer => $str)
		{
//	hitta idnummer för individ/relation
			$ztag = substr($str,0,3);
			$tagg = substr($str,2,4);
//	Ny individ
			if($ztag == '0 @') 
			{
				if(($ztag == '0 @') && ($zind == 'JA')) 
				{
//	Sätt räknare föregående person
					if (($fgrp == 0) || ($dgrp == 0)) {
						if(($fgrp == 0) && ($dgrp == 0)) {
							$xant0++; }
						else {
							if($fgrp == 0) {
								$xant0++;
								if($zpls == 1) {
									$xant1++; } }
							if($dgrp == 0) {
								$xant0++; }
						}		
					}	
					if (($fgrp == 1) || ($dgrp == 1)) {
						if(($fgrp == 1) && ($dgrp == 1)) {
							$xant1++; }
						else {
							if($fgrp == 1) {
								$xant1++; 
								if($zpls == 1) {
									$xant2++; } }
							if($dgrp == 1) {
								$xant1++;
								if($zmin == 1) {
									$xant0++; } }
						}		
					}
					if (($fgrp == 2) || ($dgrp == 2)) {
						if(($fgrp == 2) && ($dgrp == 2)) {
							$xant2++; }
						else {
							if($fgrp == 2) {
								$xant2++; 
								if($zpls == 1) {
									$xant3++; } }
							if($dgrp == 2) {
								$xant2++;
								if($zmin == 1) {
									$xant1++; } }
						}		
					}
					if (($fgrp == 3) || ($dgrp == 3)) {
						if(($fgrp == 3) && ($dgrp == 3)) {
							$xant3++; }
						else {
							if($fgrp == 3) {
								$xant3++; 
								if($zpls == 1) {
									$xant4++; } }
							if($dgrp == 1) {
								$xant3++;
								if($zmin == 1) {
									$xant2++; } }
						}		
					}
					if (($fgrp == 4) || ($dgrp == 4)) {
						if(($fgrp == 4) && ($dgrp == 4)) {
							$xant4++; }
						else {
							if($fgrp == 4) {
								$xant4++; 
								if($zpls == 1) {
									$xant5++; } }
							if($dgrp == 4) {
								$xant4++;
								if($zmin == 1) {
									$xant3++; } }
						}		
					}
					if (($fgrp == 5) || ($dgrp == 5)) {
						if(($fgrp == 5) && ($dgrp == 5)) {
							$xant5++; }
						else {
							if($fgrp == 5) {
								$xant5++; 
								if($zpls == 1) {
									$xant6++; } }
							if($dgrp == 5) {
								$xant5++;
								if($zmin == 1) {
									$xant4++; } }
						}		
					}
					if (($fgrp == 6) || ($dgrp == 6)) {
						if(($fgrp == 6) && ($dgrp == 6)) {
							$xant6++; }
						else {
							if($fgrp == 6) {
								$xant6++; 
								if($zpls == 1) {
									$xant7++; } }
							if($dgrp == 6) {
								$xant6++;
								if($zmin == 1) {
									$xant5++; } }
						}		
					}
					if (($fgrp == 7) || ($dgrp == 7)) {
						if(($fgrp == 7) && ($dgrp == 7)) {
							$xant7++; }
						else {
							if($fgrp == 7) {
								$xant7++; 
								if($zpls == 1) {
									$xant8++; } }
							if($dgrp == 7) {
								$xant7++;
								if($zmin == 1) {
									$xant6++; } }
						}		
					}
					if (($fgrp == 8) || ($dgrp == 8)) {
						if(($fgrp == 8) && ($dgrp == 8)) {
							$xant8++; }
						else {
							if($fgrp == 8) {
								$xant8++; 
								if($zpls == 1) {
									$xant9++; } }
							if($dgrp == 8) {
								$xant8++;
								if($zmin == 1) {
									$xant7++; } }
						}		
					}
					if (($fgrp == 9) || ($dgrp == 9)) {
						if(($fgrp == 9) && ($dgrp == 9)) {
							$xant9++; }
						else {
							if($fgrp == 9) {
								$xant9++; }
							if($dgrp == 9) {
								$xant9++;
								if($zmin == 1) {
									$xant8++; } }
						}	
					}
//
//	Skriv föregående person
					if($zant > 0) 
					{
						for($i=1;$i<=$zant;$i++) 
						{
							$ztxt = $zlista[$i];
							if($xant0 > 0) {
								fwrite($handk0,$ztxt."\r\n"); }
							if($xant1 > 0) {
								fwrite($handk1,$ztxt."\r\n"); }
							if($xant2 > 0) {
								fwrite($handk2,$ztxt."\r\n"); }
							if($xant3 > 0) {
								fwrite($handk3,$ztxt."\r\n"); }
							if($xant4 > 0) {
								fwrite($handk4,$ztxt."\r\n"); }
							if($xant5 > 0) {
								fwrite($handk5,$ztxt."\r\n"); }
							if($xant6 > 0) {
								fwrite($handk6,$ztxt."\r\n"); }
							if($xant7 > 0) {
								fwrite($handk7,$ztxt."\r\n"); }
							if($xant8 > 0) {
								fwrite($handk8,$ztxt."\r\n"); }
							if($xant9 > 0) {
								fwrite($handk9,$ztxt."\r\n"); }
						}
//	Töm array
						for($i=1;$i<=$zant;$i++)
						{
							unset($zlista[$i]);
						}
						$zant = 0;
					}
				}
//	0-ställ					
					if($xant0 > 0) {
						$gant0++; }
					if($xant1 > 0) {
						$gant1++; }
					if($xant2 > 0) {
						$gant2++; }
					if($xant3 > 0) {
						$gant3++; }
					if($xant4 > 0) {
						$gant4++; }
					if($xant5 > 0) {
						$gant5++; }
					if($xant6 > 0) {
						$gant6++; }
					if($xant7 > 0) {
						$gant7++; }
					if($xant8 > 0) {
						$gant8++; }
					if($xant9 > 0) {
						$gant9++; }
					$xant0 = 0;
					$xant1 = 0;
					$xant2 = 0;
					$xant3 = 0;
					$xant4 = 0;
					$xant5 = 0;
					$xant6 = 0;
					$xant7 = 0;
					$xant8 = 0;
					$xant9 = 0;
	//				
					$zant = 0;
					$chr = '';
					$birt = '';
					$buri = '';
					$deat = '';
					$zaar = '';
					$faar = '';
					$daar = '';
					$zdec = '';
					$fgrp = 99;
					$dgrp = 99;
					$zpls = 0;
					$zmin = 0;
	//
					$zind = '';
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal != '@') {
							$znum = $znum.$ztal;
						}
						else {
							if(substr($str,$zmax,5) == '@ IND') {
								$zind = 'JA';
								$pant++;
							}
							$zmax = $zlen; 
						}
						$zmax++;
					}
			}
//	Ta hand om övriga taggar
//	Skippa taggar som ej används i dubblettesten	
			$ztst = '';
			if($tagg == 'OCCU') {
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
			elseif($tagg == 'AGE ') {
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
			}
			elseif($tagg == '_MRE') {
			}
			else {
				$ztst = 'JA';
			}	
//
//	Individ med aktiva taggar	
			if(($zind == 'JA') && ($ztst == 'JA')) {
				$zant++;
				$zlista[$zant] = $str;
//
//	Årtalsintervall	
				if($tagg == 'SEX ') {
					$zsex = substr($str,6,1); }
				if($tagg == 'RGDD') {
					$zaar = substr($str,7,4); }
				if($ztag == 'CHR') {
					$chr = 'JA';
					$birt = '';
					$buri = '';
					$deat = '';
					$zaar = ''; }
				if($tagg == 'BIRT') {
					$birt = 'JA';
					$chr = '';
					$buri = '';
					$deat = '';
					$zaar = ''; }
				if($tagg == 'BURI') {
					$buri = 'JA';
					$chr = '';
					$birt = '';
					$deat = '';
					$zaar = ''; }
				if($tagg == 'DEAT') {
					$deat = 'JA';
					$chr = '';
					$birt = '';
					$buri = '';
					$zaar = ''; }
				if(($faar == '')  && ($birt == 'JA')){
					$faar = $zaar; }
				if(($faar == '')  && ($chr == 'JA')){
					$faar = $zaar; }
				if(($daar == '')  && ($deat == 'JA')){
					$daar = $zaar; }
				if(($daar == '')  && ($buri == 'JA')){
					$daar = $zaar; }
//
//	Gruppering
				if(($faar >= '1000') && ($faar <= '1599')) {
					$fgrp = 0; }
				if(($daar >= '1000') && ($faar <= '1599')) {
					$dgrp = 0; }
				if(($faar >= '1600') && ($faar <= '1649')) {
					$fgrp = 1;  }
				if(($daar >= '1600') && ($daar <= '1649')) {
					$dgrp = 1; }
				if(($faar >= '1650') && ($faar <= '1699')) {
					$fgrp = 2; }
				if(($daar >= '1650') && ($daar <= '1699')) {
					$dgrp = 2; }
				if(($faar >= '1700') && ($faar <= '1749')) {
					$fgrp = 3;  }
				if(($daar >= '1700') && ($daar <= '1749')) {
					$dgrp = 3; }
				if(($faar >= '1750') && ($faar <= '1799')) {
					$fgrp = 4; }
				if(($daar >= '1750') && ($daar <= '1799')) {
					$dgrp = 4; }
				if(($faar >= '1800') && ($faar <= '1849')) {
					$fgrp = 5;  }
				if(($daar >= '1800') && ($daar <= '1849')) {
					$dgrp = 5; }
				if(($faar >= '1850') && ($faar <= '1899')) {
					$fgrp = 6; }
				if(($daar >= '1850') && ($daar <= '1899')) {
					$dgrp = 6; }
				if(($faar >= '1900') && ($faar <= '1949')) {
					$fgrp = 7;  }
				if(($daar >= '1900') && ($daar <= '1949')) {
					$dgrp = 7; }
				if(($faar >= '1950') && ($faar <= '1999')) {
					$fgrp = 8; }
				if(($daar >= '1950') && ($daar <= '1999')) {
					$dgrp = 8; }
				if($faar >= '2000') {
					$fgrp = 9; }
				if($daar >= '2000') {
					$dgrp = 9; }
/*				if(($faar == '') && ($daar != '')) {
					$fgrp = ($dgrp - 1); 
					$zdec = substr($daar,2,2);
					if($zdec <= '25') {
						$zmin = 1; } }
				if(($daar == '') && ($faar != '')) {
					$dgrp = ($fgrp + 1);
					$zdec = substr($faar,2,2);
					if($zdec >= '75') {
						$zpls = 1; } }
				if(($daar == '') && ($faar == '')) {
					$dgrp = 0; 
					$fgrp = 0; }*/
//
			}
//
		}
//	Skriv avslutande person
		if($zant > 0) 
		{
			for($i=1;$i<=$zant;$i++) 
			{
				$ztxt = $zlista[$i];
				if($xant0 > 0) {
					fwrite($handk0,$ztxt."\r\n"); }
				if($xant1 > 0) {
					fwrite($handk1,$ztxt."\r\n"); }
				if($xant2 > 0) {
					fwrite($handk2,$ztxt."\r\n"); }
				if($xant3 > 0) {
					fwrite($handk3,$ztxt."\r\n"); }
				if($xant4 > 0) {
					fwrite($handk4,$ztxt."\r\n"); }
				if($xant5 > 0) {
					fwrite($handk5,$ztxt."\r\n"); }
				if($xant6 > 0) {
					fwrite($handk6,$ztxt."\r\n"); }
				if($xant7 > 0) {
					fwrite($handk7,$ztxt."\r\n"); }
				if($xant8 > 0) {
					fwrite($handk8,$ztxt."\r\n"); }
				if($xant9 > 0) {
					fwrite($handk9,$ztxt."\r\n"); }
			}
//	Töm array
			for($i=1;$i<=$zant;$i++)
			{
				unset($zlista[$i]);
			}
			$zant = 0;
		}	
//
		fclose($handin);
		fclose($handk0);
		fclose($handk1);
		fclose($handk2);
		fclose($handk3);
		fclose($handk4);
		fclose($handk5);
		fclose($handk6);
		fclose($handk7);
		fclose($handk8);
		fclose($handk9);
//
/*		echo '<br/>';
		echo 'P = '.$pant.'<br/>';
		echo '<br/>';
		echo '0 = '.$gant0.'<br/>';
		echo '1 = '.$gant1.'<br/>';
		echo '2 = '.$gant2.'<br/>';
		echo '3 = '.$gant3.'<br/>';
		echo '4 = '.$gant4.'<br/>';
		echo '5 = '.$gant5.'<br/>';
		echo '6 = '.$gant6.'<br/>';
		echo '7 = '.$gant7.'<br/>';
		echo '8 = '.$gant8.'<br/>';
		echo '9 = '.$gant9.'<br/>';
		echo '<br/>';
		$gant99 = $gant0+$gant1+$gant2+$gant3+$gant4+$gant5+$gant6+$gant7+$gant8+$gant9;
		echo 'Tot '.$gant99.'<br/>';
		$gant999 = $gant99 / $pant;
		echo 'Fac '.$gant999.'<br/>';
//	
		echo "<br/>";
		echo "Program dubbsplitk avslutad <br/>";
		echo "<br/>";*/
	}
	else
	{
		echo "<br/>";
		echo "Fil ".$filein." saknas, programmet avbryts <br/>";
	}
}
?>
<?PHP
/*
Urval inför splittad dubblettkontroll.
*/
require 'initbas.php';
//
$filein=$directory . "RGD91.GED";
//
$filem0=$directory . "RGD910.GED";
$filem1=$directory . "RGD911.GED";
$filem2=$directory . "RGD912.GED";
$filem3=$directory . "RGD913.GED";
$filem4=$directory . "RGD914.GED";
$filem5=$directory . "RGD915.GED";
$filem6=$directory . "RGD916.GED";
$filem7=$directory . "RGD917.GED";
$filem8=$directory . "RGD918.GED";
$filem9=$directory . "RGD919.GED";
//
if(file_exists($filem1))
{
	echo $filem1." finns redan, programmet avbryts<br/>";
}
else
{
//
	if(file_exists($filein))
	{
		$handin=fopen($filein,"r");
		$handm0=fopen($filem0,"w");
		$handm1=fopen($filem1,"w");
		$handm2=fopen($filem2,"w");
		$handm3=fopen($filem3,"w");
		$handm4=fopen($filem4,"w");
		$handm5=fopen($filem5,"w");
		$handm6=fopen($filem6,"w");
		$handm7=fopen($filem7,"w");
		$handm8=fopen($filem8,"w");
		$handm9=fopen($filem9,"w");
//
		$i = 0;
		$zant = 0;
		$zind = '';
		$chr = '';
		$birt = '';
		$buri = '';
		$deat = '';
		$zaar = '';
		$faar = '';
		$daar = '';
		$zdec = '';
		$zsex = '';
		$fgrp = 99;
		$dgrp = 99;
		$zpls = 0;
		$zmin = 0;
		$znum = '';
//	
		$pant = 0;
		$xant0 = 0;
		$xant1 = 0;
		$xant2 = 0;
		$xant3 = 0;
		$xant4 = 0;
		$xant5 = 0;
		$xant6 = 0;
		$xant7 = 0;
		$xant8 = 0;
		$xant9 = 0;
		$gant0 = 0;
		$gant1 = 0;
		$gant2 = 0;
		$gant3 = 0;
		$gant4 = 0;
		$gant5 = 0;
		$gant6 = 0;
		$gant7 = 0;
		$gant8 = 0;
		$gant9 = 0;
//	Läs in indatafilen				
		$linein = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($linein as $radnummer => $str)
		{
//	hitta idnummer för individ/relation
			$ztag = substr($str,0,3);
			$tagg = substr($str,2,4);
//	Ny individ
			if($ztag == '0 @') 
			{
				if(($ztag == '0 @') && ($zind == 'JA')) 
				{
//	Sätt räknare föregående person
					if (($fgrp == 0) || ($dgrp == 0)) {
						if(($fgrp == 0) && ($dgrp == 0)) {
							$xant0++; }
						else {
							if($fgrp == 0) {
								$xant0++;
								if($zpls == 1) {
									$xant1++; } }
							if($dgrp == 0) {
								$xant0++; }
						}		
					}	
					if (($fgrp == 1) || ($dgrp == 1)) {
						if(($fgrp == 1) && ($dgrp == 1)) {
							$xant1++; }
						else {
							if($fgrp == 1) {
								$xant1++; 
								if($zpls == 1) {
									$xant2++; } }
							if($dgrp == 1) {
								$xant1++;
								if($zmin == 1) {
									$xant0++; } }
						}		
					}
					if (($fgrp == 2) || ($dgrp == 2)) {
						if(($fgrp == 2) && ($dgrp == 2)) {
							$xant2++; }
						else {
							if($fgrp == 2) {
								$xant2++; 
								if($zpls == 1) {
									$xant3++; } }
							if($dgrp == 2) {
								$xant2++;
								if($zmin == 1) {
									$xant1++; } }
						}		
					}
					if (($fgrp == 3) || ($dgrp == 3)) {
						if(($fgrp == 3) && ($dgrp == 3)) {
							$xant3++; }
						else {
							if($fgrp == 3) {
								$xant3++; 
								if($zpls == 1) {
									$xant4++; } }
							if($dgrp == 1) {
								$xant3++;
								if($zmin == 1) {
									$xant2++; } }
						}		
					}
					if (($fgrp == 4) || ($dgrp == 4)) {
						if(($fgrp == 4) && ($dgrp == 4)) {
							$xant4++; }
						else {
							if($fgrp == 4) {
								$xant4++; 
								if($zpls == 1) {
									$xant5++; } }
							if($dgrp == 4) {
								$xant4++;
								if($zmin == 1) {
									$xant3++; } }
						}		
					}
					if (($fgrp == 5) || ($dgrp == 5)) {
						if(($fgrp == 5) && ($dgrp == 5)) {
							$xant5++; }
						else {
							if($fgrp == 5) {
								$xant5++; 
								if($zpls == 1) {
									$xant6++; } }
							if($dgrp == 5) {
								$xant5++;
								if($zmin == 1) {
									$xant4++; } }
						}		
					}
					if (($fgrp == 6) || ($dgrp == 6)) {
						if(($fgrp == 6) && ($dgrp == 6)) {
							$xant6++; }
						else {
							if($fgrp == 6) {
								$xant6++; 
								if($zpls == 1) {
									$xant7++; } }
							if($dgrp == 6) {
								$xant6++;
								if($zmin == 1) {
									$xant5++; } }
						}		
					}
					if (($fgrp == 7) || ($dgrp == 7)) {
						if(($fgrp == 7) && ($dgrp == 7)) {
							$xant7++; }
						else {
							if($fgrp == 7) {
								$xant7++; 
								if($zpls == 1) {
									$xant8++; } }
							if($dgrp == 7) {
								$xant7++;
								if($zmin == 1) {
									$xant6++; } }
						}		
					}
					if (($fgrp == 8) || ($dgrp == 8)) {
						if(($fgrp == 8) && ($dgrp == 8)) {
							$xant8++; }
						else {
							if($fgrp == 8) {
								$xant8++; 
								if($zpls == 1) {
									$xant9++; } }
							if($dgrp == 8) {
								$xant8++;
								if($zmin == 1) {
									$xant7++; } }
						}		
					}
					if (($fgrp == 9) || ($dgrp == 9)) {
						if(($fgrp == 9) && ($dgrp == 9)) {
							$xant9++; }
						else {
							if($fgrp == 9) {
								$xant9++; }
							if($dgrp == 9) {
								$xant9++;
								if($zmin == 1) {
									$xant8++; } }
						}	
					}
//
//	Skriv föregående person
					if($zant > 0) 
					{
						for($i=1;$i<=$zant;$i++) 
						{
							$ztxt = $zlista[$i];
							if($xant0 > 0) {
								fwrite($handm0,$ztxt."\r\n"); }
							if($xant1 > 0) {
								fwrite($handm1,$ztxt."\r\n"); }
							if($xant2 > 0) {
								fwrite($handm2,$ztxt."\r\n"); }
							if($xant3 > 0) {
								fwrite($handm3,$ztxt."\r\n"); }
							if($xant4 > 0) {
								fwrite($handm4,$ztxt."\r\n"); }
							if($xant5 > 0) {
								fwrite($handm5,$ztxt."\r\n"); }
							if($xant6 > 0) {
								fwrite($handm6,$ztxt."\r\n"); }
							if($xant7 > 0) {
								fwrite($handm7,$ztxt."\r\n"); }
							if($xant8 > 0) {
								fwrite($handm8,$ztxt."\r\n"); }
							if($xant9 > 0) {
								fwrite($handm9,$ztxt."\r\n"); }
						}
//	Töm array
						for($i=1;$i<=$zant;$i++)
						{
							unset($zlista[$i]);
						}
						$zant = 0;
					}
				}
//	0-ställ					
					if($xant0 > 0) {
						$gant0++; }
					if($xant1 > 0) {
						$gant1++; }
					if($xant2 > 0) {
						$gant2++; }
					if($xant3 > 0) {
						$gant3++; }
					if($xant4 > 0) {
						$gant4++; }
					if($xant5 > 0) {
						$gant5++; }
					if($xant6 > 0) {
						$gant6++; }
					if($xant7 > 0) {
						$gant7++; }
					if($xant8 > 0) {
						$gant8++; }
					if($xant9 > 0) {
						$gant9++; }
					$xant0 = 0;
					$xant1 = 0;
					$xant2 = 0;
					$xant3 = 0;
					$xant4 = 0;
					$xant5 = 0;
					$xant6 = 0;
					$xant7 = 0;
					$xant8 = 0;
					$xant9 = 0;
	//				
					$zant = 0;
					$chr = '';
					$birt = '';
					$buri = '';
					$deat = '';
					$zaar = '';
					$faar = '';
					$daar = '';
					$zdec = '';
					$fgrp = 99;
					$dgrp = 99;
					$zpls = 0;
					$zmin = 0;
	//
					$zind = '';
					$znum = '';
					$zlen = strlen($str);
					$zmax = 3;
					while($zmax <= $zlen) {
						$ztal = substr($str,$zmax,1);
						if($ztal != '@') {
							$znum = $znum.$ztal;
						}
						else {
							if(substr($str,$zmax,5) == '@ IND') {
								$zind = 'JA';
								$pant++;
							}
							$zmax = $zlen; 
						}
						$zmax++;
					}
			}
//	Ta hand om övriga taggar
//	Skippa taggar som ej används i dubblettesten	
			$ztst = '';
			if($tagg == 'OCCU') {
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
			elseif($tagg == 'AGE ') {
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
			}
			elseif($tagg == '_MRE') {
			}
			else {
				$ztst = 'JA';
			}	
//
//	Individ med aktiva taggar	
			if(($zind == 'JA') && ($ztst == 'JA')) {
				$zant++;
				$zlista[$zant] = $str;
//
//	Årtalsintervall	
				if($tagg == 'SEX ') {
					$zsex = substr($str,6,1); }
				if($tagg == 'RGDD') {
					$zaar = substr($str,7,4); }
				if($ztag == 'CHR') {
					$chr = 'JA';
					$birt = '';
					$buri = '';
					$deat = '';
					$zaar = ''; }
				if($tagg == 'BIRT') {
					$birt = 'JA';
					$chr = '';
					$buri = '';
					$deat = '';
					$zaar = ''; }
				if($tagg == 'BURI') {
					$buri = 'JA';
					$chr = '';
					$birt = '';
					$deat = '';
					$zaar = ''; }
				if($tagg == 'DEAT') {
					$deat = 'JA';
					$chr = '';
					$birt = '';
					$buri = '';
					$zaar = ''; }
				if(($faar == '')  && ($birt == 'JA')){
					$faar = $zaar; }
				if(($faar == '')  && ($chr == 'JA')){
					$faar = $zaar; }
				if(($daar == '')  && ($deat == 'JA')){
					$daar = $zaar; }
				if(($daar == '')  && ($buri == 'JA')){
					$daar = $zaar; }
//
//	Gruppering
				if(($faar >= '1000') && ($faar <= '1599')) {
					$fgrp = 0; }
				if(($daar >= '1000') && ($faar <= '1599')) {
					$dgrp = 0; }
				if(($faar >= '1600') && ($faar <= '1649')) {
					$fgrp = 1;  }
				if(($daar >= '1600') && ($daar <= '1649')) {
					$dgrp = 1; }
				if(($faar >= '1650') && ($faar <= '1699')) {
					$fgrp = 2; }
				if(($daar >= '1650') && ($daar <= '1699')) {
					$dgrp = 2; }
				if(($faar >= '1700') && ($faar <= '1749')) {
					$fgrp = 3;  }
				if(($daar >= '1700') && ($daar <= '1749')) {
					$dgrp = 3; }
				if(($faar >= '1750') && ($faar <= '1799')) {
					$fgrp = 4; }
				if(($daar >= '1750') && ($daar <= '1799')) {
					$dgrp = 4; }
				if(($faar >= '1800') && ($faar <= '1849')) {
					$fgrp = 5;  }
				if(($daar >= '1800') && ($daar <= '1849')) {
					$dgrp = 5; }
				if(($faar >= '1850') && ($faar <= '1899')) {
					$fgrp = 6; }
				if(($daar >= '1850') && ($daar <= '1899')) {
					$dgrp = 6; }
				if(($faar >= '1900') && ($faar <= '1949')) {
					$fgrp = 7;  }
				if(($daar >= '1900') && ($daar <= '1949')) {
					$dgrp = 7; }
				if(($faar >= '1950') && ($faar <= '1999')) {
					$fgrp = 8; }
				if(($daar >= '1950') && ($daar <= '1999')) {
					$dgrp = 8; }
				if($faar >= '2000') {
					$fgrp = 9; }
				if($daar >= '2000') {
					$dgrp = 9; }
/*				if(($faar == '') && ($daar != '')) {
					$fgrp = ($dgrp - 1); 
					$zdec = substr($daar,2,2);
					if($zdec <= '25') {
						$zmin = 1; } }
				if(($daar == '') && ($faar != '')) {
					$dgrp = ($fgrp + 1);
					$zdec = substr($faar,2,2);
					if($zdec >= '75') {
						$zpls = 1; } }
				if(($daar == '') && ($faar == '')) {
					$dgrp = 0; 
					$fgrp = 0; }*/
//
			}
//
		}
//	Skriv avslutande person
		if($zant > 0) 
		{
			for($i=1;$i<=$zant;$i++) 
			{
				$ztxt = $zlista[$i];
				if($xant0 > 0) {
					fwrite($handk0,$ztxt."\r\n"); }
				if($xant1 > 0) {
					fwrite($handk1,$ztxt."\r\n"); }
				if($xant2 > 0) {
					fwrite($handk2,$ztxt."\r\n"); }
				if($xant3 > 0) {
					fwrite($handk3,$ztxt."\r\n"); }
				if($xant4 > 0) {
					fwrite($handk4,$ztxt."\r\n"); }
				if($xant5 > 0) {
					fwrite($handk5,$ztxt."\r\n"); }
				if($xant6 > 0) {
					fwrite($handk6,$ztxt."\r\n"); }
				if($xant7 > 0) {
					fwrite($handk7,$ztxt."\r\n"); }
				if($xant8 > 0) {
					fwrite($handk8,$ztxt."\r\n"); }
				if($xant9 > 0) {
					fwrite($handk9,$ztxt."\r\n"); }
			}
//	Töm array
			for($i=1;$i<=$zant;$i++)
			{
				unset($zlista[$i]);
			}
			$zant = 0;
		}	
//
		fclose($handin);
		fclose($handm0);
		fclose($handm1);
		fclose($handm2);
		fclose($handm3);
		fclose($handm4);
		fclose($handm5);
		fclose($handm6);
		fclose($handm7);
		fclose($handm8);
		fclose($handm9);
//
/*		echo '<br/>';
		echo 'P = '.$pant.'<br/>';
		echo '<br/>';
		echo '0 = '.$gant0.'<br/>';
		echo '1 = '.$gant1.'<br/>';
		echo '2 = '.$gant2.'<br/>';
		echo '3 = '.$gant3.'<br/>';
		echo '4 = '.$gant4.'<br/>';
		echo '5 = '.$gant5.'<br/>';
		echo '6 = '.$gant6.'<br/>';
		echo '7 = '.$gant7.'<br/>';
		echo '8 = '.$gant8.'<br/>';
		echo '9 = '.$gant9.'<br/>';
		echo '<br/>';
		$gant99 = $gant0+$gant1+$gant2+$gant3+$gant4+$gant5+$gant6+$gant7+$gant8+$gant9;
		echo 'Tot '.$gant99.'<br/>';
		$gant999 = $gant99 / $pant;
		echo 'Fac '.$gant999.'<br/>';
//	
		echo "<br/>";
		echo "Program dubbsplitm avslutad <br/>";
		echo "<br/>";*/
	}
	else
	{
		echo "<br/>";
		echo "Fil ".$filein." saknas, programmet avbryts <br/>";
	}
}
?>
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
*/
require 'initbas.php';
//
$filename=$directory . "RGD910.GED";
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
		$handut=fopen($fileut,"w");
//
		echo "<br/>";
//
		echo "Dubblettkontroll startad ".date('Y-m-d')." / ".date('H:i:s')."<br/>";
//		echo "<br/>";
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
//	Steg 0 startar	
//	
		$filename=$directory . "RGD910.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 0 avslutat	
//
//	Steg 1 startar	
//	
		$filename=$directory . "RGD911.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 1 avslutat	
//	
//	Steg 2 startar	
//	
		$filename=$directory . "RGD912.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 2 avslutat	
//
//	Steg 3 startar	
//	
		$filename=$directory . "RGD913.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 3 avslutat	
//
//	Steg 4 startar	
//	
		$filename=$directory . "RGD914.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 4 avslutat	
//	
//	Steg 5 startar	
//	
		$filename=$directory . "RGD915.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 5 avslutat	
//
//	Steg 6 startar	
//	
		$filename=$directory . "RGD916.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 6 avslutat	
//
//	Steg 7 startar	
//	
		$filename=$directory . "RGD917.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 7 avslutat	
//	
//	Steg 8 startar	
//	
		$filename=$directory . "RGD918.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 8 avslutat	
//
//	Steg 9 startar	
//	
		$filename=$directory . "RGD919.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 9 avslutat	
//
//	Steg 10 startar	
//	
		$filename=$directory . "RGD920.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 10 avslutat	
//
//	Steg 11 startar	
//	
		$filename=$directory . "RGD921.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 11 avslutat	
//	
//	Steg 12 startar	
//	
		$filename=$directory . "RGD922.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 12 avslutat	
//
//	Steg 13 startar	
//	
		$filename=$directory . "RGD923.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 13 avslutat	
//
//	Steg 14 startar	
//	
		$filename=$directory . "RGD924.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 14 avslutat	
//	
//	Steg 15 startar	
//	
		$filename=$directory . "RGD925.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 15 avslutat	
//
//	Steg 16 startar	
//	
		$filename=$directory . "RGD926.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 16 avslutat	
//
//	Steg 17 startar	
//	
		$filename=$directory . "RGD927.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 17 avslutat	
//	
//	Steg 18 startar	
//	
		$filename=$directory . "RGD928.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 18 avslutat	
//
//	Steg 19 startar	
//	
		$filename=$directory . "RGD929.GED";
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
					$aar = (int)substr($ifdx,0,4);
					if(($znum != '') && ($ind == 'J')) {
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
					$dpref = 'f. ';
					$dradx = $dpref.$str;
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
					$dpref = 'd. ';
					$dradx = $dpref.$str;
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
//////////////////////////////////
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
///////////////////////////
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
//			
				$n2++;
			}
			$n1++;
		}
//	
//	steg 19 avslutat	
//
//	avslut
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
			fwrite($handut,"Plustecken anger förhållande Mor/Dotter respektive Far/Son, ");
			fwrite($handut,"en vanlig kombination vid dubbletter. \r\n");
			fwrite($handut,"Frågetecken anger osäkrare dubblett, ");
			fwrite($handut,"men i så fall kan även föräldrar vara felaltiga.  \r\n");
			fwrite($handut,"Minustecken kan vara tvillingar men även syskon, ");
			fwrite($handut,"dessa är dock oftast inte dubbletter. \r\n");
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
	else
	{
		echo "<br/>";
		echo "Filen ".$filename." saknas, programmet avbryts <br/>";
	}
	echo "<br/>";
	echo "Dubblettkontroll avslutad ".date('Y-m-d')." / ".date('H:i:s')."<br/>";
}
?>

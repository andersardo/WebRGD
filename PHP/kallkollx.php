<?PHP
/*
Testa källor, listar Saknas när källa saknas.
 
*/
require 'initbas.php';
//
$fileut=$directory . "RGDK.CSV";
//
$namn='';
$fnamn='';
$enamn='';
$typ='#';
$kalla='';
$txtrad='';
$rads=0;
$radp=0;
$radt=0;
$radx=0;
$radf=0;
$radl=0;
$imax=0;
$lmax=0;
$lidn='';
$znum=0;
$len=0;
$brytr = 0;
$snak="@";
$snal="0 @";
if(file_exists($fileut))
{
	echo "<br/>";
	echo $fileut." finns redan, programmet avbruts<br/>";
}
else
{
	$filename=$directory . "RGD9.GED";
//	
	if(file_exists($filename))
	{
		$handle=fopen($filename,"r");
		$handut=fopen($fileut,"w");
		$akt = 'NEJ';
		echo "<br/>";
//		Läs in indatafilen				
		$lines = file($filename,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
			$len=strlen($str);
			if($len > $lmax)
			{
				$lmax = $len;
				$lidn = $znum;
			}
			$pos1=substr($str,0,1);
			$tagk=substr($str,0,5);
			$tagg=substr($str,0,6);
			$tagl=substr($str,0,8);
			if(($pos1 == '0') || ($pos1 == '1'))
			{
//	Utskrift					
				if($kalla == 'Saknas') {
					$radl++;
					$txtrad=$znum.';'.$enamn.';'.$fnamn.';'.$typ.';'.$ort.';'.$dat.';'.$kalla;
					fwrite($handut,$txtrad."\r\n");
				}
				$kalla = '';
				$ort = '';
				$dat = '';
				$typ = '#';
				$akt = 'NEJ';
			}
			if($tagg == '1 NAME' )
			{
				$fnamn='';
				$enamn='';
				$namn = substr($str,7,(strlen($str)));
				$nlen = strlen($namn);
				$nmax = 0;
				while($nmax < ($nlen-1)) {
					$ntkn = substr($namn,$nmax,1);
					if($ntkn == '/') {
						if($fnamn == '') {
							$fnamn = $enamn;
							$enamn = '';
						}
						else {
							$enamn = $enamn.$ntkn;
						}	
					}
					else {
						$enamn = $enamn.$ntkn;
					}
					$nmax++;
				}
			}
			if($tagg == '1 BIRT' )
			{
				$akt = 'JA';
				$typ = 'född';
			}
			if($tagk == '1 CHR')
			{
				$akt = 'JA';
				$typ = 'döpt';
			}
			if($tagg == '1 DEAT')
			{
				$akt = 'JA';
				$typ = 'död';
			}
			if($tagg == '1 BURI')
			{
				$akt = 'JA';
				$typ = 'begravd';
			}
			if($tagg == '1 MARR')
			{
				$akt = 'JA';
				$typ = 'gift';
			}
			if(($tagg == '2 DATE') && ($akt == 'JA'))
			{
				$dat = substr($str,7,(strlen($str)));
				$kalla = 'Saknas';
			}
			if(($tagg == '2 PLAC') && ($akt == 'JA'))
			{
				$ort = substr($str,7,(strlen($str)));
				$kalla = 'Saknas';
			}
			if(($tagg == '2 SOUR') && ($akt == 'JA'))
			{
				if($tagl == '2 SOUR @')
				{	
					$radx++;
				}
				else
				{
					$rads++;
//
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
//				
					$kalla = substr($strtmp,7,(strlen($strtmp)));
					if($kalla == '') {
						$kalla = 'Saknas';
					}
//	
//	Utskrift
					if($kalla == 'Saknas') {
						$radl++;
						$txtrad=$znum.';'.$enamn.';'.$fnamn.';'.$typ.';'.$ort.';'.$dat.';'.$kalla;
						fwrite($handut,$txtrad."\r\n");
					}
				}
				$kalla = '';
				$radt++;
			}
			else
			{
				$radt++;
				$post=substr($str,0,3);
				if($post == $snal)
				{
					$imax=3;
					$znum='';
					while($imax <= 20)
					{
						$sna=substr($str,$imax,1);
						if($sna == $snak)
						{
							$imax++;
							$test=substr($str,$imax,4);
							$imax=20;	
							if($test == ' IND')
							{
								$radp++;
							}
							if($test == ' FAM')
							{
								$namn = 'Familj';
								$fnamn = 'Familj';
								$enamn = 'Familj';
								$radf++;
							}
						}
						else
						{
							$znum=$znum.$sna;
							$imax++;
						}
					}
				}
			}
		}
		fclose($handle);
		fclose($handut);
//		
		if($radl > 0) {
			echo $radl. " händelser saknar källa, RGDK.CSV har skapats. <br/>";
		}
		else {
			echo "Inga källor saknas, filen RGDK.CSV är tom.  <br/>";
		}
//		
	}
	else
	{
		echo $filename." saknas, programmet avslutas.<br/>";
	}
}
?>
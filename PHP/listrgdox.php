<?PHP
/*
Speciell variant för web-versionen där RGD9 används i stället för RGD8.
Medför att RGDP taggen måste tolkas för att ge rätt resultat i RGDO listan.

Programmet avser att lista taggen PLAC när RGDP taggen inte skapats, 
dvs. programmet har inte lyckats identifiera platsen som en församling eller ett land.
*/
require 'initbas.php';
require 'initdb.php';
//
echo "<br/>";
echo "Program listrgdox startad <br/>";
//
$fileut=$directory . "RGDO.txt";
//
$slutkoll = '';
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
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//		echo "<br/>";
		$ant = 0;
		$grp3= 0;
		$miss = 'JA';
		$ny = 'N';
		$plac = '';	
		$namn = '';
		$head = 'ON';	
//
		$text = "Ortnamn / Platser som ej kunnat identifieras som församlingar i GEDCOM filen:";
		fwrite($handut,$text."\r\n");
		$text = "";
		fwrite($handut,$text."\r\n");
		$text = "* anger rad med oidentifierad församling";
		fwrite($handut,$text."\r\n");
		$text = "- - anger möjligt alternativ enligt församlingstabellen";
		fwrite($handut,$text."\r\n");
		$text = "";
		fwrite($handut,$text."\r\n");
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
//				if($head == 'ON') {
//					fwrite($handut,$str."\r\n");
//				}
			}
//	Första individ/relation börjar	
			if($head == 'OFF')
			{
//	hitta idnummer för individ/relation
				$ztag = substr($str,0,3);
				if($ztag == '0 @') {
					$znum = '';
					$ny = 'J';
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
////////
				$ztag = substr($str,0,3);
				$tagg = substr($str,0,6);
//						
				if($tagg == '1 NAME')	{
					$namn = substr($str,7,((strlen($str) - 7)));
				}
//
				if(($ztag == '0 @') || ($tagg == '0 TRLR')) {
					$miss = 'JA';
					$plac = '';
				}
				else
				{
///////////		
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
///////////
					if(($tagg == "2 PLAC") && ($miss == 'JA') && ($akt == 'JA'))
					{
						$akt = 'NEJ';
						if($ny == 'J') {
							if($namn == '') {
								$namn = "Familj";
							}
//	ej aktiv längre							
							$ny = 'N';
						}
						$plac = substr($str,7,(strlen($str)-7));
//	Spara bara församling
						$omax = 0;	
						$ort = "";
						$olen=strlen($plac);
						while($omax < $olen) {
							$tkn=substr($plac,$omax,1);
							if($tkn == ')')
							{
								$omax = $olen;
								$ort = $ort.$tkn;
							}
							else
							{
								$ort = $ort.$tkn;
							}	
							$omax++;
						}
						$plac = $ort;
//	Spara bara ortsnamnet
						$omax = 0;	
						$ort = "";
						$olen=strlen($plac);
						while($omax < $olen) {
							$tkn=substr($plac,$omax,1);
							if(($tkn == '(') || ($tkn == ','))
							{
								$omax = $olen;
							}
							else
							{
								$ort = $ort.$tkn;
							}	
							$omax++;
						}
//	ta bort ev. mellanslag i slutet
						if((substr($ort,(strlen($ort)-1),1)) == ' ') {
							$ort=substr($ort,0,(strlen($ort)-1));
						}
						$fellista[]=$plac.":".$ort;
						$ort = '';
						$plac = '';
						$namn = '';
						$utinfo = '';
						$ant++;
					}
					else
					{
						$miss = 'JA';
					}
					if($tagg == "2 RGDP")
					{
						if(strlen($str) >= 8) {
							$p1 = substr($str,7,1);
							$p2 = substr($str,8,1);
							if(($p1 < '0') || ($p1 > '9')) {
								if($p1 == 'L') {
									if(($p2 < '0') || ($p2 > '9')) {
//	ej identifierad								
//										echo $p1."/".$p2."/".$miss."<br/>";
									}
									else {
//	landid								
										$miss='NEJ';
									}
								}
								else {
//	ej identifierad								
//									echo $p1."/".$p2."/".$miss."<br/>";
								}	
							}
							else {
//	församlingsid						
								$miss='NEJ';
							}
						}	
					}
				}	
			}	
		}
//
		if($ant > 0) {
//
			$fellista=array_unique($fellista);
			sort($fellista);
//			echo "<br/>";
			fwrite($handut," \r\n");
			foreach($fellista as $felrad) {
				$xlen = 0;
				$xant = 0;
				$xblock = '';
				$xlen = strlen($felrad);
				while($xant < $xlen) {
					$xtkn = substr($felrad,$xant,1);
					if($xtkn == ':') {
						$fplac = $xblock;
						$xblock = '';
					}
					else {
						$xblock = $xblock.$xtkn;
					}
					$xant++;
				}
				$fort = $xblock;
				if(strlen($fort) >=5) {
					$fort = substr($xblock,0,(strlen($xblock) -1));
				}
				$xblock = '';
//
				$noid="";
				$lkod="";
				$info="";
				$fors="";
				$ort=$fort."%";
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
				$SQL="SELECT fors FROM foskx WHERE fors LIKE '$ort'";
				$result=mysql_query($SQL);
				if(!$result)
				{
					echo $SQL."fungerande inte".mysql_error();
				}
				else
				{
					$mstopp = mysql_num_rows($result);
					$nstopp = 0;
					if($mstopp <= 15) {
						while($row=mysql_fetch_assoc($result)) {
//							$lkod=$row['lkod'];
							$fors=$row['fors'];
//							$info=$row['info'];
							if($fors != "")
							{
								if($nstopp == 0) {
									if(($grp3 <= 3) && ($grp3 >= 1)) {
										fwrite($handut,"\r\n");
									}	
									$utinfo = " - - alternativ: ";
									fwrite($handut,"* ".$fplac.$utinfo."\r\n");
								}
								fwrite($handut,"  - - ".$fors."\r\n");
							}
							$nstopp++;
							$grp3=3;
						}
					}
					if($nstopp == 0) {
						fwrite($handut,"* ".$fplac."\r\n");
						$grp3++;
					}
					if($grp3 >= 3) {
						fwrite($handut,"\r\n");
						$grp3=0;
					}	
					$nstopp = 0;
				}	
			}
		}	
//
		if($ant == 0) {
			$text = "";
			fwrite($handut,$text."\r\n");
			$text = "Inga oidentifierade orter.";
			fwrite($handut,$text."\r\n");
		}
		else
		{
			$text = "";
			fwrite($handut,$text."\r\n");
			$text = "Listan avslutad.";
			fwrite($handut,$text."\r\n");
			$text = "";
			fwrite($handut,$text."\r\n");
			$text = "Listan är komprimerad, samma ort förekommer bara en gång";
			fwrite($handut,$text."\r\n");
			$text = "men kan däremot förekomma flera gånger i GEDCOM filen.";
			fwrite($handut,$text."\r\n");
		}
//
		fclose($handin);
		fclose($handut);
//
//		echo "<br/>";
		echo "Filen ".$fileut." har skapats <br/>";
		echo "Program listrgdox avslutad <br/>";
		echo "<br/>";
//
		$slutkoll='OK';
//
	}
	else
	{
		echo "<br/>";
		echo "Filen ".$filein." saknas, programmet avbröts <br/>";
//
		$slutkoll='XX';
//
	}
}
//
echo "<br/>";
echo "*********************************************<br/>";
if($slutkoll == 'OK') {
//
	echo "Program listrgdox avslutad <br/>";
	echo "Viktigt att kolla om några felsignaler skrivits!<br/>";
	echo "<br/>";
	echo "Ta hand om den skapade filen RGDO.txt.<br/>";
}
else {
	echo "Program listrgdox avslutades inte på förväntad sätt, kolla upp varför !!! <br/>";
	echo "<br/>";
	echo "Körningen kan startas om efter borttag av RGDO <br/>";
	echo "om den skapats eller ej är borttagen sen tidigare körning. <br/>";
}
echo "*********************************************<br/>";
?>
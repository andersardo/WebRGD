<?PHP
/*
Programmet läser kända källor för att konvertera till ett önskat format (Del av fixen).

Programmet kan behöva anpassas inför varje körning om man inte
begränsar det till de vanligast förekommande förkortningarna. 

Kan också användas för att fixa temporära konverteringar,
kopiera i så fall ett elseif block och ändra enligt önskemål
använd echo för att kolla resultatet.

Nu finns SB/SBF/SVBEF, DB/SDB, BIS, SCB, VKBR
Alt. också SV

Komplettera konverteringarna efterhand eller inaktivera felaktiga.

*/
require 'initbas.php';
//
$fileut=$directory . "RGD9X.GED";
//
$len=0;
$text=' ';
if(file_exists($fileut))
{
	echo $fileut." finns redan, programmet avbryts<br/>";
}
else
{
//
	$filein=$directory . "RGD9.GED";
//
	$rad=0;
	if(file_exists($filein))
	{
//		echo $filein." finns<br/>";
//		echo "$filein har storleken ".filesize($filein)."<br/>";
		$handin=fopen($filein,"r");
		$handut=fopen($fileut,"w");
//		echo "<br/>";
		$head = 'ON';	
//	Läs in indatafilen				
		$lines = file($filein,FILE_IGNORE_NEW_LINES);
		foreach($lines as $radnummer => $str)
		{
			$strlen = strlen($str);
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
				$tagg=substr($str,0,7);
				if($tagg == "2 RGDS ")
				{
					$exp="";
					$exp1="";
//	Kopiera EJ detta block!	
//	längd 27		
					if((substr($str,7,27) == 'Statistiska Central Byrån') || (substr($str,7,27) == 'Statistiska central byrån')) {
					if($strlen > 33) {
							$exp=(Substr($str,34,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Statisktiska Central Byrån";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 19		
					elseif(substr($str,7,19) == 'Sveriges Befolkning') {
						if($strlen > 25) {
							$exp=(Substr($str,26,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 Sveriges Befolkning";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 18		
					elseif(substr($str,7,18) == 'Begravda i Sverige') {
						if($strlen > 24) {
							$exp=(Substr($str,25,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Begravda i Sverige";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 17		
					elseif((substr($str,7,17) == 'Sveriges Dödbok') || (substr($str,7,17) == 'Sveriges dödbok')) {
						if($strlen > 23) {
							$exp=(Substr($str,24,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Sveriges Dödbok";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 12		
					elseif(substr($str,7,12) == 'Vallonskivan') {
						if($strlen > 18) {
							$exp=(Substr($str,19,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Vallonskivan";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 10		
					elseif(substr($str,7,10) == 'Smedskivan') {
						if($strlen > 16) {
							$exp=(Substr($str,17,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Smedskivan";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 10		
					elseif(substr($str,7,10) == 'Rotemannen') {
						if($strlen > 16) {
							$exp=(Substr($str,17,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Rotemannen";
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 8		
					elseif((substr($str,7,8) == 'KRÅKEN') || (substr($str,7,8) == 'kråken') || (substr($str,7,8) == 'Kråken')) {
						if($strlen > 14) {
							$exp=(Substr($str,15,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Kråken".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'DISBYT') || (substr($str,7,6) == 'disbyt') || (substr($str,7,6) == 'Disbyt')) {
						if($strlen > 12) {
							$exp=(Substr($str,13,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 DIS Disbyt databas".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 5		
					elseif((substr($str,7,5) == 'SVBEF') || (substr($str,7,5) == 'svbef') || (substr($str,7,5) == 'Svbef')) {
						if($strlen > 11) {
							$exp=(Substr($str,12,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 Sveriges befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 4				
					elseif((substr($str,7,4) == 'VKBR') || (substr($str,7,4) == 'vkbr') || (substr($str,7,4) == 'Vkbr')) {
						if($strlen > 10) {
							$exp=(Substr($str,11,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Värmlands kyrkoboksregister".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 4				
					elseif((substr($str,7,4) == 'SBEF') || (substr($str,7,4) == 'sbef') || (substr($str,7,4) == 'Sbef')) {
						if($strlen > 10) {
							$exp=(Substr($str,11,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 4				
					elseif((substr($str,7,4) == 'SVDB') || (substr($str,7,4) == 'svdb') || (substr($str,7,4) == 'Svdb')) {
						if($strlen > 10) {
							$exp=(Substr($str,11,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Sveriges dödbok".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 4				
					elseif((substr($str,7,4) == 'DDSS') || (substr($str,7,4) == 'ddss') || (substr($str,7,4) == 'Ddss')) {
						if($strlen > 10) {
							$exp=(Substr($str,11,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Demogrfisk Databas Södra Sverige".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'RGD') || (substr($str,7,3) == 'rgd') || (substr($str,7,3) == 'Rgd')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 DIS RGD databas".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'SCB') || (substr($str,7,3) == 'scb') || (substr($str,7,3) == 'Scb')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Statistiska Central Byrån".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'BIS') || (substr($str,7,3) == 'bis') || (substr($str,7,3) == 'Bis')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Begravda i Sverige".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'SDB') || (substr($str,7,3) == 'sdb') || (substr($str,7,3) == 'Sdb')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Sveriges dödbok".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'SBF') || (substr($str,7,3) == 'sbf') || (substr($str,7,3) == 'Sbf')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'SVB') || (substr($str,7,3) == 'svb') || (substr($str,7,3) == 'Svb')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'KGF') || (substr($str,7,3) == 'kgf') || (substr($str,7,3) == 'Kgf')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Kronobergs Genealogiska Förening".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'NAD') || (substr($str,7,3) == 'nad') || (substr($str,7,3) == 'Nad')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Nationell ArkivDatabas".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'PLF') || (substr($str,7,3) == 'plf') || (substr($str,7,3) == 'Plf')) {
						if($strlen > 9) {
							$exp=(Substr($str,10,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Person- och Lokalhistoriskt Forskarcentrum".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	Längd 2
					elseif((substr($str,7,2) == 'SB') || (substr($str,7,2) == 'sb') || (substr($str,7,2) == 'Sb')) {
						if($strlen > 8) {
							$exp=(Substr($str,9,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
						}
// 	längd 2				
					elseif((substr($str,7,2) == 'DB') || (substr($str,7,2) == 'db') || (substr($str,7,2) == 'Db')) {
						if($strlen > 8) {
							$exp=(Substr($str,9,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*7 Sveriges Dödbok".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 2				
					elseif((substr($str,7,2) == 'CD') || (substr($str,7,2) == 'cd') || (substr($str,7,2) == 'Cd')) {
						if($strlen > 8) {
							$exp=(Substr($str,9,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 CD".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//					
// 	längd 2	temp Passar ibland oftast inte			
/*					elseif((substr($str,7,2) == 'SV') || (substr($str,7,2) == 'sv') || (substr($str,7,2) == 'Sv')) {
						if($strlen > 8) {
							$exp=(Substr($str,9,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."Sveriges".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}*/
//					
					else {
						fwrite($handut,$str."\r\n");
						$ejlista[] = substr($str,7,$strlen);
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
/*	Unika radändringar listas
		$radbr = 0;
		$utlista=array_unique($utlista);
		sort($utlista);
		echo "<br/>";
		foreach($utlista as $utrad) {
			echo $utrad."<br/>";
			$radbr++;
			if($radbr == 3) {
				echo "<br/>";
				$radbr = 0;
			}	
		}
		echo "<br/>";
		echo "Kontrollera om önskat resultat uppnåtts <br/>";
		echo "OBS! En ändring kan behöva ändras på flera RGDS rader i ".$fileut." <br/>";
//
		echo "<br/>";
		echo "<br/>";
		echo "Listade källor som ej konverterats:<br/>";
//	Unika källor som ej konverterats
		$radbr = 0;
		$ejlista=array_unique($ejlista);
		sort($ejlista);
//		echo "<br/>";
		foreach($ejlista as $ejrad) {
			echo $ejrad."<br/>";
			$radbr++;
			if($radbr == 3) {
				echo "<br/>";
				$radbr = 0;
			}	
		}
//
		echo "<br/>";
		echo "Filen ".$fileut." har skapats <br/>";
*/
		echo "<br/>";
		echo "Program konvktxtz avslutad <br/>";
/*
		$filelogg=$directory . "RGDlogg.txt";
		$handlog=fopen($filelogg,"a");
		$text = "Program konvktxtz avslutad ";
		fwrite($handlog,$text.date('Y-m-d')." / ".date('H:i:s')."\r\n");
		fclose($handlog);
*/
	}
	else
	{
		echo "<br/>";
		echo "Filen ".$filein." saknas, programmet avbröts <br/>";
	}
}
?>
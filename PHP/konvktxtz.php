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
$fileut=$directory . "RGD9Y.GED";
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
// 	längd 28				
					if((substr($str,7,28) == 'Sveriges Biografiska Lexikon') || (substr($str,7,28) == 'sveriges biografiska lexikon') || (substr($str,7,28) == 'Sveriges biografiska lexikon')) {
						if($strlen > 34) {
							$exp=(Substr($str,35,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenskt Biografiskt Lexikon".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 27		
					elseif((substr($str,7,27) == 'Statistiska Central Byrån') || (substr($str,7,27) == 'statistiska central byrån') || (substr($str,7,27) == 'Statistiska central byrån')) {
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
						$text = $tagg."*7 Statisktiska Central Byrån".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 27				
					elseif((substr($str,7,27) == 'Svenskt Biografiskt Lexikon') || (substr($str,7,27) == 'svenskt biografiskt lexikon') || (substr($str,7,27) == 'Svenskt biografiskt lexikon') || (substr($str,7,27) == 'Svenskt Biografiskt lexikon')) {
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
						$text = $tagg."*9 Svenskt Biografiskt Lexikon".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 22				
					elseif((substr($str,7,22) == 'Sv Biografiskt Lexikon') || (substr($str,7,22) == 'sv biografiskt lexikon') || (substr($str,7,22) == 'Sv biografiskt lexikon') || (substr($str,7,22) == 'Sv Biografiskt lexikon')) {
						if($strlen > 28) {
							$exp=(Substr($str,29,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenskt Biografiskt Lexikon".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 22	V	
					elseif((substr($str,7,22) == 'Sveriges Adelskalendern') || (substr($str,7,22) == 'sveriges adelskalender') || (substr($str,7,22) == 'Sveriges adelskalender')) {
						if($strlen > 28) {
							$exp=(Substr($str,29,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Sveriges Adelskalender".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 20 V	
					elseif((substr($str,7,20) == 'Svenska SmedSläkter') || (substr($str,7,20) == 'Svenska Smedsläkter') || (substr($str,7,20) == 'svenska smedsläkter') || (substr($str,7,20) == 'Svenska smedsläkter')){
						if($strlen > 26) {
							$exp=(Substr($str,27,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenska Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 19		
					elseif((substr($str,7,19) == 'Sveriges Befolkning') || (substr($str,7,19) == 'Sveriges befolkning')){
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
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 18		
					elseif((substr($str,7,18) == 'Begravda i Sverige') || (substr($str,7,18) == 'begravda i sverige') || (substr($str,7,18) == 'begravda i Sverige')){
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
						$text = $tagg."*7 Begravda i Sverige".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 16		
					elseif((substr($str,7,16) == 'Sveriges Dödbok') || (substr($str,7,16) == 'sveriges dödbok') || (substr($str,7,16) == 'Sveriges dödbok')) {
						if($strlen > 22) {
							$exp=(Substr($str,23,(Strlen($str))));
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
//	Längd 15 V
					elseif(substr($str,7,15) == 'Kjell Lindbloms'){
						if($strlen > 21) {
							$exp=(Substr($str,22,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Kjell Lindbloms".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
						}
//	längd 15 V	
					elseif((substr($str,7,15) == 'Sv SmedSläkter') || (substr($str,7,15) == 'Sv Smedsläkter') || (substr($str,7,15) == 'sv smedsläkter') || (substr($str,7,15) == 'Sv smedsläkter')){
						if($strlen > 21) {
							$exp=(Substr($str,22,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenska Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 15 V	
					elseif((substr($str,7,15) == 'Rotemansarkivet') || (substr($str,7,15) == 'rotemansarkivet')){
						if($strlen > 21) {
							$exp=(Substr($str,22,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Rotemannen".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 15				
					elseif((substr($str,7,15) == 'Sv. Biogr. Lex.') || (substr($str,7,15) == 'sv. biogr. lex.') || (substr($str,7,15) == 'Sv. biogr. lex.') || (substr($str,7,15) == 'Sv. Biogr. lex.')) {
						if($strlen > 21) {
							$exp=(Substr($str,22,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenskt Biografiskt Lexikon".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 14	V	
					elseif((substr($str,7,14) == 'Adelskalendern') || (substr($str,7,14) == 'adelskalendern')) {
						if($strlen > 20) {
							$exp=(Substr($str,21,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Sveriges Adelskalender".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 14 V		
					elseif((substr($str,7,14) == 'Begr i Sverige') || (substr($str,7,14) == 'begr i sverige') || (substr($str,7,14) == 'begr i Sverige')){
						if($strlen > 20) {
							$exp=(Substr($str,21,(Strlen($str))));
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
//	Längd 14 V
					elseif((substr($str,7,14) == 'Kjell Lindblom') || (substr($str,7,14) == 'Lindblom Kjell')){
						if($strlen > 20) {
							$exp=(Substr($str,21,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Kjell Lindblom".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
						}
//	längd 13 V		
					elseif((substr($str,7,13) == 'Begravda i Sv') || (substr($str,7,13) == 'begravda i sv') || (substr($str,7,13) == 'begravda i Sv')){
						if($strlen > 19) {
							$exp=(Substr($str,20,(Strlen($str))));
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
// 	längd 13				
					elseif((substr($str,7,13) == 'Bouppteckning') || (substr($str,7,13) == 'bouppteckning')) {
						if($strlen > 19) {
							$exp=(Substr($str,20,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*6 Bouppteckning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 13		
					elseif((substr($str,7,13) == 'Sv Befolkning') || (substr($str,7,13) == 'Sv befolkning')){
						if($strlen > 19) {
							$exp=(Substr($str,20,(Strlen($str))));
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
/*	längd 13		
					elseif((substr($str,7,13) == 'Söderskivan') || (substr($str,7,13) == 'söderskivan')){
						if($strlen > 19) {
							$exp=(Substr($str,20,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Söderskivan".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}*/
//	längd 12 V		
					elseif((substr($str,7,12) == 'Folkräkning') || (substr($str,7,12) == 'folkräkning')){
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
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 12 V	
					elseif((substr($str,7,12) == 'Rotemansark.') || (substr($str,7,12) == 'rotemansark.')){
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
						$text = $tagg."*9 Rotemannen".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 12 V	
					elseif((substr($str,7,12) == 'Vallonskivan') || (substr($str,7,12) == 'vallonskivan')){
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
						$text = $tagg."*9 Vallonskivan".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 12				
					elseif((substr($str,7,12) == 'Sv Biogr Lex') || (substr($str,7,12) == 'sv biogr lex') || (substr($str,7,12) == 'Sv biogr lex') || (substr($str,7,12) == 'Sv Biogr lex')) {
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
						$text = $tagg."*9 Svenskt Biografiskt Lexikon".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	Längd 11 V
					elseif(substr($str,7,11) == 'K, Lindblom'){
						if($strlen > 17) {
							$exp=(Substr($str,18,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Kjell Lindblom".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
						}
//	längd 10 V		
					elseif((substr($str,7,10) == 'Sv Dödbok') || (substr($str,7,10) == 'sv dödbok') || (substr($str,7,10) == 'Sv dödbok')) {
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
						$text = $tagg."*7 Sveriges Dödbok".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 10		
					elseif((substr($str,7,10) == 'Smedskivan') || (substr($str,7,10) == 'smedskivan')){
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
						$text = $tagg."*9 Smedskivan".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 10		
					elseif((substr($str,7,10) == 'Rotemannen') || (substr($str,7,10) == 'rotemannen')) {
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
						$text = $tagg."*9 Rotemannen".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 10	V	
					elseif((substr($str,7,10) == 'Rotem.ark.') || (substr($str,7,10) == 'rotem.ark.')){
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
						$text = $tagg."*9 Rotemannen".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 10 V		
					elseif((substr($str,7,10) == 'Folkräkn.') || (substr($str,7,10) == 'folkräkn.')){
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
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	Längd 10 V
					elseif((substr($str,7,10) == 'K Lindblom') || (substr($str,7,10) == 'K.Lindblom')){
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
						$text = $tagg."*9 Kjell Lindblom".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
						}
/*	längd 9 V		
					elseif((substr($str,7,9) == 'Söder CD') || (substr($str,7,9) == 'söder cd')){
						if($strlen > 15) {
							$exp=(Substr($str,16,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Söderskivan".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 9 V		
					elseif((substr($str,7,9) == 'Folkräkn') || (substr($str,7,9) == 'folkräkn')){
						if($strlen > 15) {
							$exp=(Substr($str,16,(Strlen($str))));
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
					}*/
//	längd 9	V	
					elseif((substr($str,7,9) == 'Sv SmedSl') || (substr($str,7,9) == 'Sv Smedsl') || (substr($str,7,9) == 'sv smedsl') || (substr($str,7,9) == 'Sv smedsl')){
						if($strlen > 15) {
							$exp=(Substr($str,16,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenska Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 9	V	
					elseif((substr($str,7,9) == 'Adelskal.') || (substr($str,7,9) == 'adelskal.')) {
						if($strlen > 15) {
							$exp=(Substr($str,16,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Sveriges Adelskalender".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 9	V	
					elseif((substr($str,7,9) == 'Begr i Sv') || (substr($str,7,9) == 'begr i Sv') || (substr($str,7,9) == 'begr i sv')){
						if($strlen > 15) {
							$exp=(Substr($str,16,(Strlen($str))));
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
//	längd 8		
					elseif((substr($str,7,8) == 'Vallonä') || (substr($str,7,8) == 'vallonä')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	Längd 8 V
					elseif(substr($str,7,8) == 'Lindblom'){
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
						$text = $tagg."*9 Kjell Lindblom".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
						}
//	längd 8	V	
					elseif((substr($str,7,8) == 'SvSmedSl') || (substr($str,7,8) == 'SvSmedsl') || (substr($str,7,8) == 'svsmedsl') || (substr($str,7,8) == 'Svsmedsl')){
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
						$text = $tagg."*9 Svenska Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 8	V	
					elseif((substr($str,7,8) == 'Adelskal') || (substr($str,7,8) == 'adelskal')) {
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
						$text = $tagg."*9 Sveriges Adelskalender".$exp;
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
//	längd 7		
					elseif((substr($str,7,7) == 'Vallons') || (substr($str,7,7) == 'vallons')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 7		
					elseif((substr($str,7,7) == 'Vallone') || (substr($str,7,7) == 'vallone')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 7	V	
					elseif((substr($str,7,7) == 'Sv SmSl') || (substr($str,7,7) == 'Sv Smsl') || (substr($str,7,7) == 'sv smsl') || (substr($str,7,7) == 'Sv smsl')){
						if($strlen > 13) {
							$exp=(Substr($str,14,(Strlen($str))));
							$exp1=(substr($exp,0,1));
							if(($exp1 == ',') || ($exp1 == '.') || ($exp1 == '-') || ($exp1 == ':') || ($exp1 == ';')) {
//	Skiljetecken OK
							}
							elseif(($exp1) != " ") {
								$exp = " ".$exp;
							}
						}
						$text = $tagg."*9 Svenska Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Smedda') || (substr($str,7,6) == 'smedda')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Smeder') || (substr($str,7,6) == 'smeder')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Smedss') || (substr($str,7,6) == 'smedss')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Smedsl') || (substr($str,7,6) == 'smedsl')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Smedsf') || (substr($str,7,6) == 'smedsf')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Smeds ') || (substr($str,7,6) == 'smeds ')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'Bishop') || (substr($str,7,6) == 'Biskop')) {
//	Skippas
						fwrite($handut,$str."\r\n");
					}
//	längd 6	V	
					elseif((substr($str,7,6) == 'Rotem.') || (substr($str,7,6) == 'rotem.')){
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
						$text = $tagg."*9 Rotemannen".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 6 V		
					elseif((substr($str,7,6) == 'Vallon') || (substr($str,7,6) == 'vallon')){
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
						$text = $tagg."*9 Vallonskivan".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 6	V	
					elseif((substr($str,7,6) == 'SvSmSl') || (substr($str,7,6) == 'SvSmsl') || (substr($str,7,6) == 'svsmsl') || (substr($str,7,6) == 'Svsmsl')){
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
						$text = $tagg."*9 Svenska Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 6	V	
					elseif((substr($str,7,6) == 'Folkr.') || (substr($str,7,6) == 'folkr.')){
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
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 6		
					elseif((substr($str,7,6) == 'SV BEF') || (substr($str,7,6) == 'sv bef') || (substr($str,7,6) == 'Sv bef') || (substr($str,7,6) == 'Sv Bef')) {
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
						$text = $tagg."*8 Sveriges Befolkning".$exp;
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
//	längd 5	V	
					elseif((substr($str,7,5) == 'Rotem') || (substr($str,7,5) == 'rotem')){
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
						$text = $tagg."*9 Rotemannen".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
//	längd 5		
					elseif((substr($str,7,5) == 'SVBEF') || (substr($str,7,5) == 'svbef') || (substr($str,7,5) == 'Svbef') || (substr($str,7,5) == 'SvBef')) {
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
						$text = $tagg."*8 Sveriges Befolkning".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 4	V			
					elseif((substr($str,7,4) == 'Smed') || (substr($str,7,4) == 'smed')){
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
						$text = $tagg."*9 Smedskivan".$exp;
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
						$text = $tagg."*7 Sveriges Dödbok".$exp;
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
					elseif((substr($str,7,3) == 'SBL') || (substr($str,7,3) == 'sbl') || (substr($str,7,3) == 'Sbl')) {
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
						$text = $tagg."*9 Svenskt Biografiskt Lexikon".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'SSS') || (substr($str,7,3) == 'sss') || (substr($str,7,3) == 'Sss')) {
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
						$text = $tagg."*9 Svensks Smedsläkter".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'HFL') || (substr($str,7,3) == 'hfl') || (substr($str,7,3) == 'Hfl')) {
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
						$text = $tagg."*6 Husförhörslängd".$exp;
						$utlen = strlen($text);
						$utlista[] = substr($text,7,$utlen).
						' -----> ersätter ----->> '.substr($str,7,$strlen);
						fwrite($handut,$text."\r\n");
					}
// 	längd 3				
					elseif((substr($str,7,3) == 'BOU') || (substr($str,7,3) == 'bou') || (substr($str,7,3) == 'Bou') || (substr($str,7,3) == 'BoU')) {
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
						$text = $tagg."*6 Bouppteckning".$exp;
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
					elseif((substr($str,7,3) == 'SCB') || (substr($str,7,3) == 'scb') || (substr($str,7,3) == 'Scb') || (substr($str,7,3) == 'SCb')) {
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
						$text = $tagg."*7 Sveriges Dödbok".$exp;
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
						$text = $tagg."*6 Nationell ArkivDatabas".$exp;
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
//	Längd 2 V
					elseif(substr($str,7,2) == 'KL') {
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
						$text = $tagg."*9 Kjell Lindblom".$exp;
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
//	Unika radändringar listas
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
//
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
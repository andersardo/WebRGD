<html>
<head>
 <script type="text/javascript">
function load(url, where) {
//Adopted from ex 20-2 p 486 Javascript book
//calls url and sets inneHTML of element where to the result asynchronously
  var request = new XMLHttpRequest();
  request.onreadystatechange = function () {
    if (request.readyState == 4 && request.status == 200)
       where.innerHTML = request.responseText;
//FIX!! Test on return ERR => alert
  }
  request.open("GET", url);
  request.send(null);
}
</script>
</head>

<body>
%if message:
  <h1>Resultat</h1>
  {{!message}}
  <hr>
%end
<h1>Startsida - arbetsflöde</h1>
<a href="/logout">Logga ut</a>
<h2>1-3 Ladda upp - indatavalidering/egenkontroll - import</h2>
<div style="float: right; padding: 0ex; margin: 0px 0px 0px 0px;">
<img src="/img/wfOP_1_3.png" width="397"
onmouseover="this.width='794'"
onmouseout="this.width='397'" />
</div>
En GEDCOM fil läses in, bearbetas (förbereds för matchning) och lämnar
som resultat ett antal textfiler (kan skickas per mail).
Följande steg körs:
<ol>
<li> Ladda upp en GEDCOM fil
<li> GEDCOM fil bearbetning: <br> (indatavalidering/egenkontroll).
Ger som resultat valideringslistor (textfiler - per mail om så önskas).
<ul>
<li>Lista oregistrerade namn - <a href="#Namn">Läs mera</a></li>
<li>Ortlista med oidentifierade församlingar  - <a href="#Församling">Läs mera</a></li>
<li>Lista möjliga dubblettindivider  - <a href="#Dubblett1">Läs mera</a></li>
</ul>
<li> Skapa temporär databas för matchning
</ol>
<form enctype="multipart/form-data" action="/workflow/combined" method="POST">
Ange var GEDCOM filen finns genom att anväda bläddra-funktionen:
<input name="gedcomfile" type="file" />

<P>
Ange om resultat-listan/listorna skall skickas med email
(OBS endast för registrerade användare!):
<input type="checkbox" name="resmail" />
</P>

<P>
Kryssa i om du vill ha lista oregistrerade namn (Namnkontroll)
 <input type="checkbox" name="namn" checked ><BR>
Kryssa i om du vill ha ortlista (Församlingskontroll)
 <input type="checkbox" name="ort" checked ><BR>
Kryssa i om du vill ha dubblettlista (Dubblettkontroll)
<input type="checkbox" name="dubl" checked >
</p>

<p>

Därefter klicka på <input type="submit" value="Starta bearbetning" />
 för att starta bearbetningen	
</p>
</form>
<P>
Bearbetningen kan ta olika lång tid beroende på 
	filens storlek och andra parallella bearbetningar.<br>
(Vissa browsers (Firefox, Chrome, m.fl.) visar att bearbetning pågår.)
 </P>
<!--Under tiden bearbetningen pågår kommer log-filen att löpande visas.<br>-->
När bearbetningen är klar indikeras detta och resultat-listan/listorna
blir tillgängliga.<br>
Samtidigt skickas dessa till dig, per email, om du har indikerat så ovan.

<table style="width: 100%"><tr><td style="vertical-align: top;">
<h3>2A Alternativ dubblettkontrol - <a href="#Dubblett2">Läs mera</a></h3>
Ytterligare dubblettkontroll, som
använder matchningsteknik (en matchning av databasen mot sig själv)
för att finna likheter i indidivernas uppgifter.
<p/><br/>
<form action="/listDubl" method="GET" name="listDubl style="vertical-align: middle;"">
Databas 
<select name="workDB">
<option value="">Välj databas I</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
<p><input type="submit" value="Alternativ dubblettkontrol" />
<div id="db2listDubl"></div>

</form>
</td>
<td style="float: right;">
<img src="/img/wfOP_2A.png" width="397"
onmouseover="this.width='794'; this.style='position: absolute;right: 0px;'"
onmouseout="this.width='397'; this.style='position: relative;right: 0px;'" />
</td></tr>
</table>

%if role == 'editor' or role == 'admin':
<h3>2B Alternativ sammanslagen dubblettkontrol - experimentel</h3>

<form action="/listDublExp" method="GET" name="listDublExp style="vertical-align: middle;"">
Databas 
<select name="workDB">
<option value="">Välj databas I</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
<p><input type="submit" value="Alternativ sammanslagen dubblettkontrol" />
</form>
%end

<table style="width: 100%"><tr><td style="vertical-align: top;">
<h2>4. Maskinell Matchning - <a href="#Match1">Läs mera</a></h2>
Matchning av två databaser innebär att programmet försöker
att identifiera personer, som finns i båda databaserna.
<br>
(Fintrimmning av matchningsfunktionen återstår att göra.)
<p><br>
<form action="/runProg/match" method="GET">
Databas
<select name="workDB">
<option value="">Välj databas I</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
att matchas mot
<select name="matchDB">
<option value="">Välj databas II</option>

%for db in dbs:
    <option>{{db}}</option>'
%end
<!--  <option>RGDmaster</option> -->
</select>
<p><input type="submit" value="Matcha!" />
</form>
</td>
<td style="float: right;"><img src="/img/wfOP_4.png" width="397"
onmouseover="this.width='794'; this.style='position: absolute;right: 0px;'"
onmouseout="this.width='397'; this.style='position: reslative;right: 0px;'" />
</td></tr>
</table>

<table style="width: 100%"><tr><td style="vertical-align: top;">
<h2>5. Manuell Matchning - <a href="#Match2">Läs mera</a></h2>
Den maskinella matchningen kan inte med tillräcklig säkerhet
matcha alla personer mot varandra, utan lämnar tveksamma
matchningar till manuell bedömning.
<p><br>
<h4><em>Detaljerad dokumentation (visas i nytt fönster):
  <a href="/static/Matchinfo.pdf" target="_blank">Matchinfo.pdf</a></em></h4>

<form action="/list/families" method="GET" name='manualMatch'>
Databas
<select name="workDB"
onchange='load("/databases/manualMatch/"+document.manualMatch.workDB.options[document.manualMatch.workDB.selectedIndex].value, document.getElementById("db2manualMatch"));'>

<option value="">Välj databas I</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
att matchas mot
<div id="db2manualMatch"><i>&lt;möjliga databas II val&gt;</i></div>
<!--
<select name="matchDB">
<option value="">Välj databas II</option>

%for db in dbs:
    <option>{{db}}</option>'
%end
</select>
<p><input type="submit" value="DoManualMatch" />
-->
</form>

</td>
<td style="float: right;"><img src="/img/wfOP_5.png" width="397"
onmouseover="this.width='794'; this.style='position: absolute;right: 0px;'"
onmouseout="this.width='397'; this.style='position: reslative;right: 0px;'" />
</td></tr>
</table>
<!-- TMP
<h2>?. Rimlighetskontroll av matchningsresultat (DEBUG)
<table style="width: 100%"><tr><td>

<form action="/runProg/sanity" method="GET" name="sanity">
Databas
<select name="workDB"
onchange='load("/databases/sanity/"+document.sanity.workDB.options[document.sanity.workDB.selectedIndex].value, document.getElementById("db2sanity"));'>

<option value="">Välj databas I</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
matchad mot
<div id="db2sanity"><i>&lt;möjliga databas II val&gt;</i></div>
</form>
</td>
<td></td></tr>
</table>
-->
<table style="width: 100%"><tr><td style="vertical-align: top;">
<h2>6. Visa skillnader  - <a href="#Skillnad">Läs mera</a></h2>
Visar detaljskillnader mellan matchade personer.
<p><br><br>
<form action="/listSkillnad/persons" method="GET" name="listSkillnad">
Skillnad mellan
<select name="workDB"
onchange='load("/databases/listSkillnad/"+document.listSkillnad.workDB.options[document.listSkillnad.workDB.selectedIndex].value,
	document.getElementById("db2listSkillnad"));'>
<option value="">Välj matchad databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
och
<div id="db2listSkillnad"><i>&lt;möjliga jämförd databas val&gt;<i></div>
<!--
<select name="matchDB">
<option value="">Select compared database</option>

%for db in dbs:
    <option>{{db}}</option>'
%end
</select>
<p><input type="submit" value="Skillnad" />
-->
</form>
</td><td style="float: right;">
<img src="/img/wfOP_6.png" width="397"
onmouseover="this.width='794'; this.style='position: absolute;right: 0px;'"
onmouseout="this.width='397'; this.style='position: reslative;right: 0px;'" />
</td></tr>
</table>

<table style="width: 100%"><tr><td style="vertical-align: top;">
<h2>7. Sammanslagning - <a href="#Merge">Läs mera</a></h2>
Sammanslagning av två matchade databaser till en gemensam databas.
<p><br><br>
<form action="/runProg/merge" method="GET" name="merge">
Databas
<select name="workDB"
onchange='load("/databases/merge/"+document.merge.workDB.options[document.merge.workDB.selectedIndex].value, document.getElementById("db2merge"));'>
<option value="">Välj databas I</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
att slå samman med
<div id="db2merge"><i>&lt;möjliga  databas II val&gt;</i></div>
<!--
<select name="matchDB">
<option value="">Välj databas II</option>

%for db in dbs:
    <option>{{db}}</option>'
%end
</select>
<p><input type="submit" value="DoMerge" />
-->
</form>
</td><td style="float: right;">
<img src="/img/wfOP_7.png" width="397"
onmouseover="this.width='794'; this.style='position: absolute;right: 0px;'"
onmouseout="this.width='397'; this.style='position: reslative;right: 0px;'" />
</td></tr>
</table>

<table style="width: 100%"><tr><td style="vertical-align: top;">
<h2>8. Skapa GEDCOM fil - <a href="#Skapa">Läs mera</a></h2>
Skapar GEDCOM fil från en utpekad databas (inkluderar NOTE mm från
orginal GEDCOM filen/filerna).
<p><br><br>
<form action="/workflow/download" method="GET">
Databas
<select name="workDB">
<option value="">Välj databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
att bli nerladdad som GEDCOM file.
<p><input type="submit" value="Ladda ner" />
</form>
</td><td style="float: right;">
<img src="/img/wfOP_8.png" width="397"
onmouseover="this.width='794'; this.style='position: absolute;right: 0px;'"
onmouseout="this.width='397'; this.style='position: reslative;right: 0px;'" />
</td></tr>
</table>

%if role == 'admin':
  <hr>
  <a href="/admin">Admin page</a>
%end

<hr>
<h2><a href="/DBadmin">Visa lagrad information</a> - <a href="#Admin">Läs mera</a></h2>
Visa aktuella log-filer. Underhållsfunktion för skapade
databaser. Inställningar lösenord.
<!--
<h2>Database browse</h2>
<form action="/DBbrowse" method="GET">
Database 
<select name="workDB">
<option value="">Välj databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
<p><input type="submit" value="Browse" />
</form>
-->
<hr>
<a href="/logout">Logga ut</a>

<hr>
<hr>
<h1>Mer information</h1>
<TABLE WIDTH=944 BORDER=1 BORDERCOLOR="#000000" CELLPADDING=4 CELLSPACING=0>
	<COL WIDTH=72>
	<COL WIDTH=400>
	<COL WIDTH=400>
	<COL WIDTH=72>
	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><a name=Länk><strong>Länk</a></strong></br></P>
		</TD>
		<TD WIDTH=400>
			<P><strong>Rubrik</strong></P>
		</TD>
		<TD WIDTH=400>
			<P><strong>Information</strong></P>
		</TD>
		<TD WIDTH=72>
			<P><strong>Lista</strong></P>
		</TD>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Namn><strong>Namn</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Namnkontroll</B></FONT>
			<BR><BR>Kontrolerar namnfältet efter formella felaktigheter och
			kontrollerar namnen mot Dis namndatabas.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Programmet söker formella felaktigheter och listar dessa.
			<BR>Därefter kontrollerar varje ingående namn mot Dis namndatabas.
			<BR>Programmet listar namn som saknas med personens angivna kön
			men som finns med motsatt kön.
			<BR>Dessa personer kan vara registrerade med fel kön.<BR>
			<BR>K angivet före namnet anger att personen är registrerad som kvinna.
			<BR>M angivet före namnet anger att personen är registrerad som man.<BR>
			<BR><B>Tips:</B>
			<BR>Felaktigheter i namnfältet är ofta att slash (/) använts i
			namnfältet. Det skapar problem i GEDCOM filer då dessa som standard
			använder slash för att avgränsa efternamnet.<BR>
			<BR>Att personer registreras med fel kön är inte ovanligt. Ibland
			kan detta då avslöjas genom namnkontrollen.
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B>RGDN</B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Församling><strong>Församling</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Församlingskontroll</B></FONT>
			<BR><BR>Ortkontrollen görs för att identifiera svenska
			församlingar respektive länder.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Programmet läser den angivna orten för händelserna född, död och
vigd.
			Därefter jämför programmet detta mot generella tabeller för svenska
			församlingar och länder.
			<BR>De orter som då inte får träff listas tillsammans med ett urval av
			möjliga alternativa församlingar.<BR>
			<BR>Även länder kontrolleras mot en tabell med svensk stavning enligt
			ISO-standard.<BR>
			<BR><B>Notera att det inte är något fel med orter</B>, t.ex. Stockholm,
			Göteborg, de kommer ut på listan med anledning av att de inte är
			svenska församlingar alternativt länder.<BR>
			<BR><B>Tips:</B>
			<BR>Svenska församlingar skall anges med församlingsnamn samt länskod
			inom parentes. Exempel Berg (G).<BR>
			<BR>Länder kan även identifieras med landkod enligt ISO-2 standard
			angiven inom parentes. Exempel Tyskland (DE).
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B>RGDO</B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Dubblett1><strong>Dubblett1</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Dubblettkontroll</B></FONT>
			<BR><BR>Dubblettkontrollen avser att finna lika eller snarlika
			personer, som möjligen kan vara dubbletter.</P>
			<BR><BR><B>Viktigt:</B> Att eliminera dubbletter i släktforskningsdata	är
			en viktig och kvalitetshöjande åtgärd som bör prioriteras högt.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Programmet jämför alla personer i GEDCOM filen med varandra.
			Försöker att hitta och värdera likheter i indidivernas uppgifter.
			<BR>De personer, som har liknande uppgifter listas parvis.<BR>
			<BR><B>Tips:</B>
			<BR>Om man avser att upprepa dubblettesterna vid senare tillfälle,
			kan det vara bra att spara uppgiften om vilka par som inte var
			dubbletter.<BR> När programmet körs nästa gång återkommer samma
			kandidater igen, då kan det vara praktiskt att kunna jämföra med
			den tidigare listan.
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B>RGDD</B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Dubblett2><strong>Dubblett2</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Alternativ Dubblettkontroll</B></FONT>
			<BR><BR>Ytterligare dubblettkontroll, som använder matchningsteknik
			för att finna likheter i indidivernas uppgifter.</P>
			<BR><BR><B>Viktigt:</B> Att eliminera dubbletter i släktforskningsdata	är
			en viktig och kvalitetshöjande åtgärd som bör prioriteras högt.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Programmet jämför matchningsresultatet från en intern matchning
			av en databas, för att söka efter möjliga dubbletter i databasen.
			<BR>Förutom en annan teknik än den föregående, är den även mera
detaljerad.<BR>
			<BR>De personer, som har liknande uppgifter listas parvis.<BR>
			<BR><B>Tips:</B>
			<BR>Om man avser att upprepa dubblettesterna vid senare tillfälle,
			kan det vara bra att spara uppgiften om vilka par som inte var
			dubbletter.<BR> När programmet körs nästa gång återkommer samma
			kandidater igen, då kan det vara praktiskt att kunna jämföra med
			den tidigare listan.
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Match1><strong>Match1</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Maskinell matchning av två databaser</B></FONT>
			<BR><BR>Matchning av två databaser innebär att programmet försöker
			att identifiera personer, som finns i båda databaserna.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Det är här möjligt att maskinellt matcha en databas mot en annan.
			<BR>Databasen man matchar mot kan antingen vara en egen databas eller kan
			man matcha sin databas mot ”RGD” databasen.<BR>
			<BR><B>Tips:</B>
			<BR>Matchar man en databas, skapad från någon annan släktforskares GEDCOM
fil,
			med en egen databas så hittar man de gemensamma släktgrenarna.<BR>
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Match2><strong>Match2</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Manuell matchning av två databaser</B></FONT>
			<BR><BR>Den maskinella matchningen kan inte med tillräcklig säkerhet
			matcha alla personer mot varandra, utan lämnar tveksamma matchningar
			till manuell bedömning.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Här har man då möjlighet att familjevis hitta likheter och
			skillnader mellan de två maskinellt matchade databaserna.<BR>
			OBS! Skall bearbetningen fortsättas måste också den manuella matchningen
			genomföras, tills alla familjer och personer med ”manuell” status
			bearbetats.<BR>
			<BR><B>Tips:</B>
			<BR>Dokumentet Matchinfo.pdf hjälper till med steg-för-steg
			instruktioner för att underlätta detta manuella moment.<BR>
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Skillnad><strong>Skillnad</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Visa skillnader i matchade databaser</B></FONT>
			<BR><BR>Visar detaljskillnader mellan matchade personer.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>De nu matchade databaserna innehåller troligen skillnader
			i data för enskilda matchade personer.<BR>
			Skillnader på så sätt att data finns i båda, men där uppgifterna
			avviker från varandra.<BR>
			Personer där differenser finns listas och skillnaderna synliggörs.<BR>
			<BR><B>Tips:</B>
			<BR>Om matchningen gjorts mot RGD databasen och felaktigheter i RGD
			upptäcks skall dessa felaktigheter rapporteras till RGD
administrationen.<BR>
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Merge><strong>Merge</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Sammanslagning av två matchade
databaser</B></FONT>
			<BR><BR>Sammanslagning av två matchade databaser till en gemensam
			databas.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Efter att manuell matchning har genomförts, kan två databaser
			slås samman enligt resultatet från matchningen (dock ej med RGD basen).
			<BR>Syftet med matchningen och sammanslagningen är att personer som
			identifierats i båda databaserna läggs samman utan att skapa dubbletter.
			<BR>Sammanslagning kan användas om man fått GEDCOM data från annan
			släktforskare och vill inkluderas den i sin egen.<BR>
			<BR><B>Tips:</B>
			<BR>Två släktforskare kan på detta sätt skapa sig en gemensam databas.
<BR>
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Skapa><strong>Skapa</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Skapa GEDCOM fil från databas</B></FONT>
			<BR><BR>Skapar GEDCOM fil från en utpekad databas.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>Den databas, som bearbetats från de ursprungliga GEDCOM filerna,
			kan användas för att generera en ny GEDCOM fil (dock ej RGD basen).<BR>
			<BR><B>Tips:</B>
			<BR>Efter sammanslagning av två databaser, kan resultatet tas ut som
			GEDCOM fil för att sedan importeras till ett släktforskningsprogram. <BR>
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	<TR VALIGN=TOP>
		<TD WIDTH=72 HEIGHT=18>
			<P><BR><a name=Admin><strong>Admin</a></strong></P>
		</TD>
		<TD WIDTH=400>
			<P><BR><FONT SIZE=4><B>Administrera databas</B></FONT>
			<BR><BR>Underhållsfunktion för skapade databaser.</P>
		</TD>
		<TD WIDTH=400>
			<P><BR>När flera databaser skapats, kan det finnas behov att
			städa bort sånt man inte längre avser att jobba vidare med.<BR>
			Delete tar bort vald databas<BR><BR>
			Ta bort all match-data, förstör eller påverkar inte databasen, men tar
			bort data från matchningar, som gjorts mot andra databaser.<BR>
			Visa information, ger antal personer och familjer samt
			information om eventuella matchningar.<BR><BR>
			<BR><B>Tips:</B>
			<BR>Att underhålla sin personliga area i detta system är precis
			lika viktigt som att underhålla sitt släktforskningsdata.<BR>
			</P>
		</TD>
		<TD WIDTH=72>
			<P><BR><B></B></P>
		</TD>
	</TR>

	</TABLE>
</body></html>


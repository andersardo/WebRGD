<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta content="text/html; charset=utf-8" http-equiv="content-type">
  <title>TEST openRGD</title>
</head>
<body>
<div id="hbox">
  <div class="box">
      <h2>Logga in</h2>
      <p>Ange användarnamn/lösenord:</p>
      <form action="login" method="post" name="login">
          <input type="text" name="username" />
          <input type="password" name="password" />

          <br/><br/>
          <button type="submit" > OK </button>
          <button type="button" class="close"> Cancel </button>
      </form>
      <br />
  </div>
<div class="info">
<p>&nbsp;
<b>TEST openRGD version 2.0</b><p>&nbsp;<br>
Detta är openRGDs Web-tjänster - logga in som "guest" utan lösenord för att prova
  systemet. OBS filer för "guest" sparas inte mellan sessioner.
<br><br>
För att skapa ett personligt konto, kontakta <a href="mailto:carl-johan.gustafsson@telia.com">Carl-Johan Gustavsson</a> och glöm inte att skicka med ditt medlemsnummer i DIS.
<br>
Notera att varken GEDCOM fil eller resultat i openRGD är synligt för någon annan
än ni själva. Är ni inloggade som guest tas alla uppgifter bort automatiskt,
efter att ni loggat ut. Har ni personligt konto sköter ni borttaget själva i
funktionen “Visa lagrad information”.

<p><hr><p>
Note - the UI is in Swedish.
This is the top openRGD page - login as "guest" with no password if you dont
  have an account.
Files for guests are not saved.
</div>
</div>
<br style="clear: left;" />
<h4>Nyheter/Händelser/Meddelanden</h4>
<ul>
  <li> 2018-01-29: Nya verktyg: relations-editor inklusive enkel hantering av dubbletter; generell sökning med grafisk visning.
  <li> 2018-01-05: Ny databas-design, förbättrad matchning och sammanslagning,
    bättre tolerans för relationsfel
  <li> 2018-01-05: <b>Version 2.0</b><br>
  <li> 2017-05-21: Ny funktionalitet - export av alla matchade familjer
  <li> 2017-02: Förbättrad GEDCOM export
  <li> 2016-03-15: Det ser ut som openRGD nu har stabiliserats.
  <li> 2016-02-13: <b>VARNING:</b> Än så länge kör vi tester för att få openRGD att fungera så bra som möjligt i den nya server-miljön.
Det betyder att openRGD kommer att vara under de tider vi modifierar programvaran. Och vi kan inte garantera att det eventuella jobb ni gör kommer att sparas :-(
  <li> 2016-02-05: Flyttad till en ny kraftfullare server - <b>rapportera alla
      problem!</b> <br>
     <b>OBS</b> inga tidigare skapade databaser flyttas över på grund
    av att databasen samtidigt är uppdaterats till en senare
    version. <br>
    Förändringar:
    <ul>
      <li> Förbättrad prestanda </li>
      <li> Mer diskutrymme </li>
      <li> Nyare versioner av bl.a. databas-program </li>
      <li> Smärre ändringar i programkoden </li>
    </ul>
  <li> 2015-06-22: Optimeringar och ny funktionalitet:
    <ul>
     <li> Tiden för maskinell matchning har minskats
     <li> Nya funktioner för dublettkontrol har tillförts (Dubblettkontroll XL
          respektive Alternativ dubblettkontroll)<br>
          Den största utmaningen för ett kommande RGD är kravet att databasen inte
          skall innehålla dubbletter, samma person skall bara finnas en gång. Att
          minimera dubbletter blir därmed en kvalitetsstämpel. Därför har mycket av
          kontrollfunktionerna i Släkttrim koncentrerats just till att söka dubbletter.<br>
          <i>Om du har kört "2A Alternativ dubblettkontroll" tidigare måste du ladda in
          GEDCOM-filen på nytt och köra om dubblettkontrollen</i> 

    </ul>
  <li> 2015-05-21: Resultatet av det avslutade delprojektet openRGD är
        nu tillgängligt - <a href="/static/openRGD.pdf">Informationsbroschyr</a>.
  <li> 2015-05-21: Matchnings-delen är nu tillgänglig igen (viss fintrimmning återstår)
  <li> 2015-05-13: Matchnings-delen är avstängd tills vidare medan vi undersöker ett problem.
  <li> 2015-05-12: Nu borde allt gå att använda som vanligt - kom ihåg
 att inga databaser har flyttats med från den tidigare test-servern -
 ni får ladda upp de GEDCOM-filer som ni vill använda på nytt.
  <li> 2015-05-12: Systemunderhåll hela dagen - vilket medför en ostabil service!
  <li> 2015-05-05: Du använder nu DIS RGD-server. 
  <li> 2015-04-23: Artikel "Släkttrim Trimma din släktforskning", Rolf Carlsson, i
  Diskulogen nr 108, 2015-04, sid 36 -- 38.
<li> 2015-03-15: Programkoden tillgänglig som OpenSource via GitHub <a
href="https://github.com/andersardo/gedMerge">https://github.com/andersardo/gedMerge</a>
<li> 2015-02-26: DIS projektledare Christer Gustavsson ger en
  statusrapport om arbetet med projektet RGD
  i <a href="http://www.cognatus.se/podcastgen/?name=2015-02-26_cognatus_slaktforskningspod_1.mp3">Cognatus
    Släkforskningpod</a>. 
<li> 2015-02-12: I den GEDCOM-fil som skapas via punkt "8. Skapa
 GEDCOM fil" ska nu all information (NOTE m.m.) från de tidigare inladdade
GEDCOM-filerna finnas med. (<b>OBS</b> för att detta ska fungera måste
 filerna vara uppladdade ("1-3 Ladda upp -
 indatavalidering/egenkontroll - import") efter 2015-02-12, dvs med
 openRGD version 0.5 eller högre.)
</ul>

<h4>Funktionalitet</h4>
Servicen ska fortfarande ses som ett ej färdigt system under utveckling.
Erfarenheterna från arbetet tar vi med oss till den fortsatta
utvecklingen med RGD med Släkttrim.
<ul>
<li>Indatavalidering / egenkontroll av GEDCOM-filer
<ul>
<li>Namn kontroll, listar formella fel och möjliga felregistreringar av kön
<li>Ort kontroll, listar angivna platser som inte är svensk församling eller land
<li>Dubblett kontroll, listar möjliga dubblettkandidater
</ul>
<li>Matchning av två GEDCOM filer<br>
Släktforskare, med viss del av forskningen gemensam, kan analysera likheter/skillnader
<li>Avvikelser i matchat data<br>
  Forskare som hittat gemensamma anor kan hitta detaljskillnader hos matchade personer
<li>Utdrag av matchningsresultatet<br>
Export av sammandrag från matchningen i Excel format .xlsx eller i generellt .csv format.
<li>Sammanslagning av matchat släktforskningsdata<br>
Två matchade GEDCOM filer kan sammanföras till en gemensam fil utan dubbletter
<li>Skapa GEDCOM fil av sammanslaget släktforskningsdata<br>
Resultatet från sammanslagningen kan laddas in i ett släktforskningsprogram
</ul>
GEDCOM-filerna som bearbetas kommer inte att kopieras
och användas i något annat syfte, varken DIS, RGDs produktions-version
eller i något kommersiellt sammanhang.<br>

<p><b><a href="/static/Lathund.pdf">Lathund för användare</a></b>
(utkast Rolf Carlsson)</p>
<hr style="clear: left;" />

<b>Utveckling/Roadmap</b>
<ul>
<li> Alpha-release slutet Oktober 2014
<li> Beta-release slutet November 2014
<li> Version 0.5 slutet Januari 2015
<li> Version 1.0 Maj 2015 - openRGD
<li> ?
</ul>

I projektet används metoder från områden som
Machine Learning och Information Retrieval. Verktyg/metoder som används är
Python, PHP, MongoDB, Lucene, och SVM.
<p>
<b>Vill du medverka i projektet?</b><br>
Du kan bidra med teknisk kunskap i produktutformning (inkluderar programutveckling, Web
användargränssnitt, layout, testning, mm), administrativ erfarenhet vid behandling
av inkommande datafiler liksom att rapportera in
egna forskningsresultat.
<p>
Hör av Dig till oss!
<br>


Christer Gustavsson (DIS), christer.gustavsson@dis.se<br>


<h4>Mer information</h4>
Rikstäckande Genealogisk Databas (RGD) är ett projekt som drivs av
<a href="http://www.dis.se/">föreningen DIS</a> med syfte att på ett strukturerat
sätt sammanställa Sveriges historiska befolkning med dess
släktrelationer i en kvalitetsgranskad databas bestående av unika individer.
<ul>
<li> <a href="https://www.dis.se/rgd-projektet">DIS
    projektet</a>
</ul>

Vissa Web-tjänster tas fram i ett delprojekt, (i ett samarbete mellan
 <a href="http://www.dis.se/">DIS</a> och <a href="http://www.lu.se/">Lunds
Universitet</a>, <a href="http://www.eit.lth.se/">EIT</a>) som avser att väsentligt
bredda och förenkla möjligheterna för allmänheten att använda de
hjälpdatabaser och verktyg som tas fram i huvudprojektet. 
<ul>
<li><a href="http://www.eit.lth.se/project/rgd">projektbeskrivning
från EIT, Lunds Universitet</a>
<li>Delfinansierat av
  <a href="http://www.internetfonden.se/rikstackande-genealogisk-databas">Internetfonden
  .SE</a>
</ul>

<!--
  <div class="box">
      <h2>Signup</h2>
      <p>Please insert your credentials:</p>
      <form action="register" method="post" name="signup">
          <input type="text" name="username" value="username"/>
          <input type="password" name="password" />
          <input type="text" name="email_address" value="email address"/>

          <br/><br/>
          <button type="submit" > OK </button>
          <button type="button" class="close"> Cancel </button>
      </form>
      <br />
  </div>
  <div class="box">
      <h2>Password reset</h2>
      <p>Please insert your credentials:</p>
      <form action="reset_password" method="post" name="password_reset">
          <input type="text" name="username" value="username"/>
          <input type="text" name="email_address" value="email address"/>

          <br/><br/>
          <button type="submit" > OK </button>
          <button type="button" class="close"> Cancel </button>
      </form>
      <br />
  </div>
  <br style="clear: left;" />
</div>
--!>
<style>
div {
    color: #777;
    margin: auto;
    width: 20em;
    text-align: center;
}
div#hbox {width: 100%;}
div#hbox div.box {float: left; width: 33%;}
div#hbox div.info { text-align: left; vertical-align: middle; width: 100%;}

input {
    background: #f8f8f8;
    border: 1px solid #777;
    margin: auto;
}
input:hover { background: #fefefe}
</style>
</body>
</html>

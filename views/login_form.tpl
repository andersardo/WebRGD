<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="content-type">
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
<b>openRGD version 0.5 för tester!</b><p>&nbsp;<br>
Detta är openRGDs Web-tjänster - logga in som "guest" utan lösenord för att prova
  systemet.
OBS filer för "guest" sparas inte mellan sessioner.

<p><hr><p>
Note - the UI is in Swedish.<br>
This is the top openRGD page - login as "guest" with no password if you dont
  have an account.
Files for guests are not saved.
</div>
</div>
<br style="clear: left;" />
<h4>Nyheter/Händelser/Meddelanden</h4>
<ul>
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
<li>Sammanslagning av matchat släktforskningsdata<br>
Två matchade GEDCOM filer kan sammanföras till en gemensam fil utan dubbletter
<li>Skapa GEDCOM fil av sammanslaget släktforskningsdata<br>
Resultatet från sammanslagningen kan laddas in i ett släktforskningsprogram
</ul>
GEDCOM-filerna, som bearbetas i alpha- och beta-versionerna kommer inte att kopieras
och användas i något annat syfte, varken DIS, RGDs produktions-version
eller i något kommersiellt sammanhang.<br>
<b>Däremot kan de GEDCOM-filer som bearbetas i version 0.5 komma att
  användas för interna tester av programvaran (som t.ex. sammanslagning med andra
  filer) under utvecklingstiden fram till version 1.0.  Filer som
  skapas av eller används under dessa tester sparas inte.
</b>

<p><b><a href="/static/Lathund.pdf">Lathund för användare</a></b>
(utkast Rolf Carlsson)</p>
<hr style="clear: left;" />

<b>Utveckling/Roadmap</b>
<ul>
<li> Alpha-release slutet Oktober 2014
<li> Beta-release slutet November 2014
<li> Version 0.5 slutet Januari 2015
<li> Version 1.0 Maj 2015
</ul>

I projektet används metoder från områden som
Machine Learning och Information Retrieval. Verktyg/metoder som används är
Python, MongoDB, Lucene, och SVM.
<p>
<b>Vill du medverka i projektet?</b><br>
Du kan bidra med teknisk kunskap i produktutformning (inkluderar programutveckling, Web
användargränssnitt, layout, testning, mm), administrativ erfarenhet vid behandling
av inkommande datafiler liksom att rapportera in
egna forskningsresultat.
<p>
Hör av Dig till någon av oss!
<br>
Christer Gustavsson (DIS), christer.gustavsson@dis.se<br>
<a href="http://www.eit.lth.se/staff/Anders.Ardo">Anders Ardö</a> (EIT, Lunds universitet)


<h4>Mer information</h4>
Rikstäckande Genealogisk Databas (RGD) är ett projekt som drivs av
<a href="http://www.dis.se/">föreningen DIS</a> med syfte att på ett strukturerat
sätt sammanställa Sveriges historiska befolkning med dess
släktrelationer i en kvalitetsgranskad databas bestående av unika individer.
<ul>
<li> <a href="http://www.dis.se/sv/projekt/genealogisk-databas.html">DIS
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

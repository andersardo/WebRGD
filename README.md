WebRGD
======

RGD is a project with the aim to collect Sweden's historical population with its family relationships in a quality-controlled database consisting of unique individuals.

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
eller i något kommersiellt sammanhang.
<hr style="clear: left;" />

<b>Utveckling/Roadmap</b>
<ul>
<li> Alpha-release slutet Oktober 2014
<li> Beta-release slutet November 2014
<li> System 0.5 slutet Januari 2015
<li> System 1.0 Maj 2015
</ul>

I projektet används metoder från områden som
Machine Learning och Information Retrieval. Verktyg/metoder som används är
Python, MongoDB, Lucene, och SVM.

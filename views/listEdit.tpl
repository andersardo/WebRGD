<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="UTF-8" />
<title>RGD Match {{title}}</title>
 
<script language="JavaScript">
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
function syncload(url, where) {
//Adopted from ex 20-2 p 486 Javascript book
//calls url and sets inneHTML of element where to the result synchronously
  var request = new XMLHttpRequest();
  request.open("GET", url, false);
  request.send(null);
  if (request.status === 200) {
       where.innerHTML = request.responseText;
//FIX!! Test on return ERR => alert
  }
}
function doAction(args) {
//FIX better handling of args
    var person = args.person;
    var family = args.family;
    var id1 = '&id1=' + (args.id1 || '')
    var id2 = '&id2=' + (args.id2 || '')
    var typ  = args.typ;
    if (args.where == 'visa') {
      document.getElementById('verif').innerHTML = ''
      document.getElementById('res').innerHTML = ''
    }
    if (args.what.indexOf('/actions') == 0) {
      syncload(args.what + '?m'+id1+id2,document.getElementById(args.where));
      window.location.reload(true);
    } else {
      load(args.what + '?person='+person+'&family='+family+'&typ='+typ,document.getElementById(args.where));
    }
}
</script>
</head>

<body>
<h1>RGD {{title}}</h1>
<h3>  <a href="/relationsEditor/child">Barn i mer än 2 familjer</a>
  ||  <a href="/relationsEditor/family">Familjer med mer än 1 HUSB/WIFE</a>
  ||  <a href="/relationsEditor/relation">Personer/Familjer utan relationer</a>
  <br>
  ||  <a href="/relationsEditor/dubblett">Dubbletter</a>
  ||  <a href="/relationsEditor/dubblettFind">Generera dubbletter</a>
  <br>
  ||  <a href="/">Tillbaks till startsida</a>
</h3>

<div id="visa"></div>

%if len(childErrs)>1:
<H3>Barn i mer än 2 familjer</H3>
<table border=1 id="childerr">
%for r in childErrs:
  <tr>
    %for cell in r:
      <td>{{!cell}}</td>
    %end
  </tr>
%end
</table> 
%end

%if len(famErrs)>1:
<H3>Familjer med mer än 1 HUSB/WIFE</H3>
<table border=1 id="famerr">
%for r in famErrs:
  <tr>
    %for cell in r:
      <td>{{!cell}}</td>
    %end
  </tr>
%end
</table> 
%end

%if len(dubbletter)>1:
<H3>Dubbletter</H3>
<table border=1 id="relerr">
%for r in dubbletter:
  <tr>
    %for cell in r:
      <td>{{!cell}}</td>
    %end
  </tr>
%end
</table> 
%end


<div id="verif"></div>
<div id="res"></div>
<div id="graph"></div>
</body>
</html>

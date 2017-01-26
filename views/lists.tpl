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
    var mid = args.mid || ''
    var fid = args.fid || ''
    var role = args.role || ''
    var fltext = args.fltext || ''
    if (args.where == 'visa') {
      document.getElementById('verif').innerHTML = ''
      document.getElementById('res').innerHTML = ''
    }
    if (args.what.indexOf('/actions') == 0) {
      syncload(args.what + '?wid='+args.wid+'&mid='+mid+'&fid='+fid+'&role='+role+'&fltext='+fltext,document.getElementById(args.where));
      window.location.reload(true);
    } else {
      load(args.what + '?wid='+args.wid+'&mid='+mid+'&fid='+fid+'&role='+role,document.getElementById(args.where));
    }
}
</script>
</head>

<body>
<h1>RGD {{title}}</h1>
<h3>  <a href="/list/families">Byt till familj</a>
  ||  <a href="/list/persons">Byt till person</a>
  ||  <a href="/viewNext">Åtgärdslista</a>
  ||  <a href="/">Tillbaks till startsida</a>
  ||  <a href="/logout">Logga ut</a>
<!--  ||  <a href="/list/fixDB">Fix databas (visa familj)</a> -->
</h3>

<form action="/list/{{typ}}" method="get">
<b>MultiMatch:</b>
%for (f,v) in multi.iteritems():
  {{f}}<input type="radio" name="multi" {{v}} value="{{f}}"
	      onclick="this.form.submit()" />;
%end
<p>
<b>Inkludera: Grupper</b>
%for (f,v) in filtergroup.iteritems():
  {{f}}<input type="checkbox" name="filtergroups" {{v}} value="{{f}}"
	      onclick="this.form.submit()" />;
%end
<br>
<b>Status:</b>
%for (f,v) in filter.iteritems():
  {{f}}<input type="checkbox" name="filters" {{v}} value="{{f}}"
	      onclick="this.form.submit()" />;
%end
<input type="hidden" name="pageNo" value="{{page}}"/>
<div align="center">
%if (len(prow)>1 or page>1) and (not page==1):
Förra # {{(page-2)*10+1}} till {{(page-2)*10+10}}:<input type="checkbox" name="page" value="prev"
	      onclick="this.form.submit()" />;
%end
%if len(prow)==11:
&nbsp; | | &nbsp; &nbsp;  # {{(page-1)*10+1}} till {{(page-1)*10+10}}  &nbsp; &nbsp; | | &nbsp; 
Nästa # {{page*10+1}} till {{page*10+10}} :<input type="checkbox" name="page" value="next"
	      onclick="this.form.submit()" />;
%end
&nbsp; &nbsp; | | &nbsp; &nbsp; Totalt: {{tot}}
</div><hr>
</form>
<form action="/search" method = "get">
Sök efter Gedcom ID: <input type="text" name="gedid">
i <select name="databas">
<option value="work">Databas I</option>
<option value="match">Databas II</option>
</select>
<button type="submit" > Sök </button>
</form><br>

%if len(prow)>1:
<table border=1 id="match">
%for r in prow:
  %if r[1] in ('Match', 'OK', 'rOK'):
    <tr bgcolor="#50FF50">
  %elif r[1] in ('EjMatch', 'EjOK', 'rEjOK'):
    <tr bgcolor="#FF5050">
  %elif r[1] in ('Manuell', 'rManuell'):
    <tr bgcolor="#FFFF50">
  %else:
    <tr>
  %end
    %for cell in r:
      <td>{{!cell}}</td>
    %end
  </tr>
%end
</table> 
%end

<div id="verif"></div>
<div id="res"></div>
<div id="visa"></div>
<div id="graph"></div>
</body>
</html>

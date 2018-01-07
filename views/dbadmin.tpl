<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtm
l1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="UTF-8" />
<title>Visa lagrad information</title>
</head>

<body>
%if message:
  <h2>Resultat</h2>
  {{!message}}
  <hr>
%end
<p>
<a href="/">Tillbaka till startsida</a>
<h2>Visa aktuella log-filer</h2>
<form action="/oldLogs" method="GET">
Databas:
<select name="workDB">
<option value="">Välj databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end
<input type="submit" value="GO" />
</form>

<h2>Databas administration</h2>
<p>
<form action="/DBaction" method="GET">
Med databas
<select name="workDB">
<option value="">Välj databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
gör <br/>
Delete<input type="radio" name="action" value="del" />; <br/>
Ta bort all match-data<input type="radio" name="action" value="rmMatch" />; <br/>
Visa information<input type="radio" name="action" value="info" checked="checked" />; <br/>

<p><input type="submit" value="GO" />
</form>

<!--
<h2>Databas debug</h2>
<p>
<form action="/DBdebug" method="GET">
DatabasI
<select name="workDB">
<option value="">Välj databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
DatabasII
<select name="matchDBDB">
<option value="">Välj databas</option>

%for db in dbs:
    <option>{{db}}</option>'
%end

</select>
<br/>
Visa dbI familj:
<input type="text" name="workFam" value=""/>
dbII familj:
<input type="text" name="matchFam" value=""/>
<p><input type="submit" value="GO" />
</form>

%if role == 'admin' or role == 'user':
<h2>Inställningar</h2>
<ul>
<li>Ändra lösenord
      <form action="reset_password" method="post" name="password_reset">
          <!--Användarnamn: <input type="text" name="username"
          value=""/>-->
	  <input type="hidden" name="username" value="{{user}}" />
          Email: <input type="text" name="email_address" value=""/>

          <br/><br/>
          <button type="submit" > OK </button>
          <button type="button" class="close"> Cancel </button>
      </form>

</ul>
%end
-->

</body>
</html>

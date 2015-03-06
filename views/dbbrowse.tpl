<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtm
l1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="UTF-8" />
<title>RGD database browse</title>
</head>

<body>
%if message:
  <h2>Action result</h2>
  {{!message}}
  <hr>
%end

<h2>Database browse</h2>
<a href="/">Back to workflow</a>
<p>
<form action="/DBaction" method="GET">
With collection
<select name="coll">
<option value="">Select collection</option>

%for coll in collections:
    <option>{{coll}}</option>'
%end

</select>
do <br/>
Find all<input type="radio" name="action" value="findall" />; <br/>
Find one<input type="radio" name="action" value="findone" />; <br/>
 <br/>
Field:
<select name="field">
<option value="">Select field</option>
    <option>ID</option>'
    <option>name</option>'
    <option>refId</option>'
    <option>Birth date</option>'
</select>
<input type='text' name='val' value='' />

<p><input type="submit" value="GO" />
</form>
</body>
</html>

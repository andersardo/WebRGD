<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta charset="UTF-8" />
<title>Waiting for action to complete</title>
 
<script language="JavaScript">
function load(url, where) {
//Adopted from ex 20-2 p 486 Javascript book
//calls url and sets innerHTML of element where to the result asynchronously
  var request = new XMLHttpRequest();
  request.onreadystatechange = function () {
    if (request.readyState == 4 && request.status == 200)
       where.innerHTML = request.responseText;
  }
  request.open("GET", url);
  request.send(null);
}
function syncload(url, where) {
//Adopted from ex 20-2 p 486 Javascript book
//calls url and sets innerHTML of element where to the result synchronously
  var request = new XMLHttpRequest();
  request.open("GET", url, false);
  request.send(null);
  if (request.status === 200) {
       where.innerHTML = request.responseText;
       return request.responseText.indexOf("ALLT KLART"); /* -1 if not found */
  } else { return -1; }
}
function waitFor() {
  if ( syncload("prLog", 'Log') == -1 ) {
      ProgressMeter.innerHTML += ".";
      setTimeout("waitFor();", 5000 ); /*timeout in 5 sec */
  } else {
      ProgressMeter.innerHTML = "BEARBETNING KLAR + link back to workflow";
FIX resultlinks
  }
}

</script>
</head>

<body onLoad="waitFor();">
<h1>Waiting for action to complete</h1>
<span id="ProgressMeter">Bearbetar </span>
<h4>Log</h4>
<span id="Log"></span>

</body>
</html>

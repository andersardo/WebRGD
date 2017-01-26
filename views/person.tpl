%if prow:
  <h2>Person</h2>
  <table border=1>
  %if prow[1] in ('Match', 'OK', 'rOK'):
    <tr bgcolor="#50FF50">
  %elif prow[1] in ('EjMatch', 'EjOK', 'rEjOK'):
    <tr bgcolor="#FF5050">
  %elif prow[1] in ('Manuell', 'rManuell'):
    <tr bgcolor="#FFFF50">
  %else:
    <tr>
  %end
  %for cell in prow:
    <td>{{!cell}}</td>
  %end
</tr></table>
  %if buttons:
<div align="center">
<button onclick="doAction({where: 'verif', what: '/actions/setOK/person', wid: '{{wid}}', mid: '{{mid}}'})">Personen OK</button>
&nbsp; | | &nbsp; &nbsp; | | &nbsp; &nbsp; | | &nbsp; 
<button onclick="doAction({where: 'verif', what: '/actions/setEjOK/person', wid: '{{wid}}', mid: '{{mid}}'})">Personen <b>_INTE_</b> OK</button>
</div>
  %end
<hr>
%end

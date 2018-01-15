%if prow:
  <h2>Person</h2>
  <table border=1>
  %if ('EjMatch' in prow[4]) or ('EjOK' in prow[4]):
    <tr bgcolor="#FF5050">
  %elif ('Match' in prow[4]) or ('OK' in prow[4]):
    <tr bgcolor="#50FF50">
  %elif 'Manuell' in prow[4]:
    <tr bgcolor="#FFFF50">
  %else:
    <tr>
  %end
  %for cell in prow:
       %if type(cell) is str and cell.find('Ignorerad')>=0:
          <td bgcolor="#FF5050">{{!cell}}</td>
       %else:
          <td>{{!cell}}</td>
       %end
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

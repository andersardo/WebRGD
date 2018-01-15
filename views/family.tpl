%if rows:
<hr>
<table border=1>
%for r in rows:
  %if ('Ignorerad' in r[4]):
    <tr bgcolor="#FF5050">
  %elif ('EjMatch' in r[4]) or ('EjOK' in r[4]):
    <tr bgcolor="#FF5050">
  %elif 'notMatched' in r[4]:
    <tr bgcolor="#FFFFFF">
  %elif ('Match' in r[4]) or ('OK' in r[4]):
    <tr bgcolor="#50FF50">
  %elif 'Manuell' in r[4]:
    <tr bgcolor="#FFFF50">
  %else:
    <tr>
  %end
  %for cell in r:
    %if type(cell) is str and cell.find('Ignorerad')>=0:
       <td bgcolor="#FF5050">{{!cell}}</td>
    %else:
       <td>{{!cell}}</td>
    %end
  %end
  </tr>
%end
</table>
%if buttons:
<div align="center">
<button onclick="doAction({where: 'verif', what: '/actions/setOK/family', wid: '{{wfid}}', mid: '{{mfid}}'})"><b>Hela</b> familjen OK</button>
&nbsp; | | &nbsp; &nbsp; | | &nbsp; &nbsp; | | &nbsp; 
<button onclick="doAction({where: 'verif', what: '/actions/setEjOK/family', wid: '{{wfid}}', mid: '{{mfid}}'})">Familjen <b>_INTE_</b> OK</button>
</div>
%end
<hr>

%end

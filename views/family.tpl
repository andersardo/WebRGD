%if rows:
<hr>
<table border=1>
%for r in rows:
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
%if buttons:
<div align="center">
<button onclick="doAction({where: 'verif', what: '/actions/setOK/family', wid: '{{wfid}}', mid: '{{mfid}}'})"><b>Hela</b> familjen OK</button>
&nbsp; | | &nbsp; &nbsp; | | &nbsp; &nbsp; | | &nbsp; 
<button onclick="doAction({where: 'verif', what: '/actions/setEjOK/family', wid: '{{wfid}}', mid: '{{mfid}}'})">Familjen <b>_INTE_</b> OK</button>
</div>
%end
<hr>

%end

<h4>Flags</h4>
<table border=1>
%for r in flagList:
  <tr>
    %for cell in r:
      <td>{{!cell}}</td>
    %end
  </tr>
%end
</table>

<div align='center'>
<input type="text" name="fltext" id="fltext"/>

<button onclick="doAction({'what':'/actions/addFlag', 'where':'res', 'wid':'{{personId}}', 'fid':'{{famId}}', 'fltext': document.getElementById('fltext').value})">addFlag</button>

</div>

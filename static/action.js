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
    var mid = '&mid=' + args.mid || ''
    var fid = '&fid=' + args.fid || ''
    var role = '&role=' + args.role || ''
    var fltext = '&fltext=' + args.fltext || ''
    var buttons = '&buttons=' + args.buttons || ''
    if (args.where == 'visa') {
      document.getElementById('verif').innerHTML = ''
      document.getElementById('res').innerHTML = ''
    }
    if (args.what.indexOf('/actions') == 0) {
//      syncload(args.what + '?wid='+args.wid+'&mid='+mid+'&fid='+fid+'&role='+role+'&fltext='+fltext,document.getElementById(args.where));
      syncload(args.what + '?wid='+args.wid+mid+fid+role+fltext+buttons,document.getElementById(args.where));
      window.location.reload(true);
    } else {
//      load(args.what + '?wid='+args.wid+'&mid='+mid+'&fid='+fid+'&role='+role,document.getElementById(args.where));
      load(args.what + '?wid='+args.wid+mid+fid+role,document.getElementById(args.where));
    }
}

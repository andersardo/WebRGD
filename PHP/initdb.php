<?PHP
//	Initiering databas
$db_server = "localhost";
//$db_user = "root";
$db_user = "rgd";
//$db_password = "";
$db_password = "pilot";
$db="RGDindatavalid";
$link = mysql_connect($db_server, $db_user, $db_password);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
echo 'Connected successfully to '.$link."<br/>";
// make RGDindatavalid the current db
$db_selected = mysql_select_db('RGDindatavalid', $link);
if (!$db_selected) {
    die ('Can\'t use RGDindatavalid : ' . mysql_error());
}
echo 'connected successfully to '.$db_selected."<br/>";
?>

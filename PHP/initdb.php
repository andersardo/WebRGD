<?PHP
//	Initiering databas
$db_server = "localhost";
$db_user = "rgd";
$db_password = "openRGD";
$db="RGDindatavalid";
$link = mysqli_connect($db_server, $db_user, $db_password);
if (!$link) {
    die('Could not connect: ' . mysqli_error($link));
}
echo 'Connected successfully <br/>';
// make RGDindatavalid the current db
$db_selected = mysqli_select_db($link, $db);
if (!$db_selected) {
    die ("Can\'t use $db : " . mysqli_error($link));
}
echo "Connected successfully to $db ($db_selected) <br/>";

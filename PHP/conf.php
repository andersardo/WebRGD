<?PHP
$db_server = "localhost";
//	$db = "test";
//kalle $db_user = "root";
 $db_user = "rgd";
//kalle $db_password = "";
$db_password = "pilot";
//kalle $directory = "C:Bzon/";
$directory = "./";
//	Minnesarean
ini_set('memory_limit','2048M');
//	ini_set('memory_limit','4096M'); // dubblad area
//
//	Tidslimit
//	set_time_limit(6); // vid looprisk
//	set_time_limit(60); // för test
//kalle set_time_limit(6000); // 1t 40 min
set_time_limit(18000); // 5 timmar
//	set_time_limit(36000); // 10 timmar

ini_set('auto_detect_line_endings',true);
?>
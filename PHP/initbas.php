<?PHP
//	Anger datamapp och initieringsparemetrar
//$directory = "C:Bzon/";
$directory = "./";
ini_set('auto_detect_line_endings',true);
//	Minnesarean
ini_set('memory_limit','3096M');
//	ini_set('memory_limit','4096M'); // dubblad area
//
//	Tidslimit
//	set_time_limit(6); // vid looprisk
//	set_time_limit(60); // för test
//	set_time_limit(6000); // 1t 40 min
//set_time_limit(18000); // 5 timmar
	set_time_limit(36000); // 10 timmar
?>
<?PHP
//	Logga in
$username = 'disbyttest';
$password = 'sallad80';
//
$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode("$username:$password")
    )
));
//
$namndev = 'disbyt-dev.dis.se';
?>

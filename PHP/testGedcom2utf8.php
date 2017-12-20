<?PHP
include 'gedcom2utf8.php';

$res = convertGedcom2UTF8('t1.ged', 't2.ged', './');
if(!empty($res[3])) {
    echo "Error ";
}
?>

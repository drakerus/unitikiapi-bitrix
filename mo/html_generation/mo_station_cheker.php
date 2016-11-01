<?
/*
error_reporting(E_ALL | E_NOTICE); 
set_time_limit(0);
date_default_timezne_set("Europe/Moscow");
*/
ini_set("display_errors", 0); 
error_reporting(0); 
	
$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define("MAIN_PATH", '/home/bitrix/www/mods/bus/town/');
$direction=array(0=>'f',1=>'b');
?>
<?
//Читаем курсор города
//Вычисляем ID города по курсору
$sql='SELECT * FROM `cursor` WHERE id_cursor=1';
$result=mysql_query($sql);
if($row = mysql_fetch_array($result)){
define('TOWN_ID', $row['id_town']);
}

//Формируем массив про все автобусы
$sql="SELECT * FROM `station` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$chek_array[]=html_entity_decode($row['station_name']);
}
echo '<pre>';
print_r($chek_array);
?>
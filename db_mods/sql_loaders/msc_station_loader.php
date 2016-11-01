<?
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/moscow/array/');
define('TOWN_ID', 1);

//Читаем массив
$array_src=SAVE_PATH.'/stations.data';
$super_array=unserialize(file_get_contents($array_src));
$super_array=array_unique($super_array);

//Чистим старые элмементы
//перед загрузкой автобусов Москвы - стираем старые записи
mysql_query("DELETE FROM `station` WHERE `id_town` = '".TOWN_ID."';");
//mysql_query("TRUNCATE TABLE station");

//Добавляем в инфоблок новые элменты
foreach($super_array as $key=>$station){
	$station_name=mb_convert_encoding($station, 'utf8', 'cp1251');						
    $alias = transletiration(mb_convert_encoding($station, 'utf8', 'cp1251'));//Алиас
 		
$sql = "INSERT INTO station SET station_name='".$station_name."', alias='".$alias."', id_town='".TOWN_ID."'";
mysql_query($sql);

}
echo 'OK';
?>

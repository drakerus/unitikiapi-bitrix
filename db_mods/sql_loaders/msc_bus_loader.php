<?
//set_time_limit(0);
//date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/moscow/array/');
define('TOWN_ID', 1);

//Читаем массив
$array_src=SAVE_PATH.'/routes.data';
$super_array=unserialize(file_get_contents($array_src));

//Чистим старые элмементы текущего города
//Ни в коем случае нельзя стирать иные города!!! это приведёт к потере данных о алисах и сайт перестанет работать
mysql_query("DELETE FROM `bus` WHERE `id_town` = '".TOWN_ID."';");

//Добавляем в инфоблок новые элменты
foreach($super_array as $route_number=>$ways_array){
            $alias = transletiration(mb_convert_encoding($route_number, 'utf8', 'cp1251'));//Алиас
			$f_way = mb_convert_encoding($ways_array['AB'], 'utf8', 'cp1251');//Название направления - прямое
            $b_way = ((isset($ways_array['BA'])) ? mb_convert_encoding($ways_array['BA'], 'utf8', 'cp1251') : '');			
			$bus_name=mb_convert_encoding($route_number, 'utf8', 'cp1251');
			
$sql = "INSERT INTO bus SET bus_name='".$bus_name."', id_bus_type='1', f_way='".$f_way."', b_way='".$b_way."', alias='".$alias."', id_town='".TOWN_ID."'";
mysql_query($sql);

//INSERT INTO `mybuses`.`bus_type` (`id_bus_type`, `bus_type_name`) VALUES (NULL, 'междугородные автобусы'), (NULL, 'автобусы до Москвы');		
}
echo 'DONE';
?>
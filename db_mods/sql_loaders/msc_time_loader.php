<?
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/moscow/array/');
define('TOWN_ID', 1);

//Чистим старые элмементы
//перед загрузкой времен Москвы - стираем старые записи
//mysql_query("DELETE FROM `time` WHERE `id_town` = '".TOWN_ID."';");
mysql_query("TRUNCATE TABLE time");


//Собираем массив существующих уже остановок в Москве
$sql="SELECT * FROM `station` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$stations_db_array[html_entity_decode($row['station_name'])]=$row['id_station'];
}

//Собираем массив существующих уже автобусов в Москве
$sql="SELECT * FROM `bus` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$bus_db_array[html_entity_decode($row['alias'])]=$row['id_bus'];
}

//Собираем массив существующих типов рейсов
$sql="SELECT * FROM `race_type`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$_schedules[html_entity_decode($row['mgs_value'])]=$row['id_race_type'];
}

//echo '<pre>'; print_r($_schedules); echo '<hr/>';

//Обходим все автобусы
foreach($bus_db_array as $bus_alias=>$bus_id){

//Читаем массив
$array_src=SAVE_PATH.$bus_alias.'.data';
$super_array=unserialize(file_get_contents($array_src));

//Добавляем в инфоблок новые элменты

	foreach($super_array as $shedule=>$direction_array){//По всем графикам. Пока их 2 будни ? выходные
	$shedule=mb_convert_encoding($shedule, 'utf8', 'cp1251');
	
		foreach($direction_array as $direction=>$station_array){
		$direction=mb_convert_encoding($direction, 'utf8', 'cp1251');
		
			foreach($station_array as $station=>$times_array){
			$station=mb_convert_encoding($station, 'utf8', 'cp1251');
			
				foreach($times_array as $race_number=>$time){
				$race_number=mb_convert_encoding($race_number, 'utf8', 'cp1251');
				$time=mb_convert_encoding($time, 'utf8', 'cp1251');								
				
				$id_station = $stations_db_array[$station];//ID Остановки
				$id_race_type = $_schedules[$shedule];//ID графика	
				
				$race_direction = (($direction=='AB') ? 0 : 1);//Направление рейса;
				
				//payment_type=0 льготный маршрут
				$sql = "INSERT INTO time SET time='".$time."', id_town='".TOWN_ID."', id_bus='".$bus_id."', id_station='".$id_station."', id_race_type='".$id_race_type."', payment_type='0', race_number='".$race_number."', race_direction='".$race_direction."';";
				if((!isset($bus_id))OR(!isset($id_station))OR(!isset($id_race_type))OR(!isset($race_direction))){
				echo $shedule.' sql: '.$sql;
				echo '<br/>STATION= #'.$station.'#';
				echo '<br/>SRC= #'.$array_src.'#';
				
				/*echo '<pre>'; print_r($super_array);
				echo '<pre>'; print_r($super_array);*/
				die(' FATAL ERROR');
				}
				
				mysql_query($sql);				
				
				}//все времена
			
			}//все остановки
		
		}//все направления
		
	}//все графики

}//все автобусы
?>

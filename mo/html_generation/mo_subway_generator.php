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

//Получаем данные о городе
$sql="SELECT * FROM `town` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
if($row = mysql_fetch_array($result)){
$town=$row['town_alias'];
$town_name=$row['town_name'];
}


//Формируем массив про все автобусы
$sql="SELECT * FROM `bus` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$bus_info_array[$row['id_bus']]['alias']=html_entity_decode($row['alias']);
$bus_info_array[$row['id_bus']]['f_way']=html_entity_decode($row['f_way']);
$bus_info_array[$row['id_bus']]['bus_name']=html_entity_decode($row['bus_name']);
}

//Проверяем на существование директорию
$dir1=MAIN_PATH.$town.'/';

if(!is_dir($dir1)){ //Если нет папки "/mods/bus/town/".$town.'/'
echo 'Folder does not exists: '.$dir1; die();
}

//Директория для статики
$dir2=MAIN_PATH.$town.'/html/';
if(!is_dir($dir2)){ mkdir($dir2, 0777); chmod($dir2, 0777); }

//Поддиретория для статики - остановки
$dir3=MAIN_PATH.$town.'/html/station/';
if(!is_dir($dir3)){ mkdir($dir3, 0777); chmod($dir3, 0777); }

//Собираем массив остановок Москвы
$sql="SELECT * FROM `station` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$prop_station_array=array(
"station_name"=>$row['station_name'],
"station_alias"=>$row['alias'],
);
$all_stations_array[$row['id_station']]=$prop_station_array;
}

//echo '<pre>'; print_r($all_stations_array); die();
$z=0;
foreach($all_stations_array as $station_id=>$prop_array){

//Получаем данные об остановке
$station_alias=$prop_array['station_alias'];
$station_name=$prop_array['station_name'];

//Бежим по 2 направлениям - вперед/назад
	foreach($direction as $direction_id=>$direction_value){

	$super_array=array();
	//Собираем все останвки автобуса в данном городе:
	
	$sql="SELECT * FROM `time` WHERE time<>'#' AND id_town='".TOWN_ID."' AND id_station='".$station_id."' AND race_direction='".$direction_id."';";		
	$result=mysql_query($sql);
	//echo $sql; die();
	$check=mysql_num_rows($result);
	while($row = mysql_fetch_array($result)){
	
	//Формирование конечного массива
	$super_array[$row['id_bus']][]=array(
	"TIME"=>$row['time'],	
	"BUS"=>$row['id_bus'],
	"SCHEDULE"=>$row['id_race_type'],
	"PAYMENT_TYPE"=>$row['payment_type'],
	"RACE_NUMBER"=>$row['race_number'],
	"STATION"=>$row['id_station'],	
	);

	}

//echo '<pre>'; print_r($super_array); die();

	//формирование x-координат
	$x_coord=array();	
	$max_arr=array();
	foreach($super_array as $bus=>$races){
	$x_coord[]=$bus;
	$max_arr[]=count($races);
	}

//echo '<pre>'; print_r($super_array); die();

	//Формирование y-координат
	$y_coord=array();
	$races=end($super_array);
	$y_coord=array(-1=>"График / Автобус");
	foreach($races as $race){	
	$y_coord[]='@';
	}
	$max_tr='';
	$max_tr=max($max_arr);
/*
	echo '<pre>'; print_r($y_coord);
	echo '<pre>'; print_r($x_coord); 
	echo '<pre>'; print_r($bus_info_array); 
	die();

*/
if(($check>0)&&(count($super_array)>0)){//Если по данному направлению есть автобусы

ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
	
	//Выводим заголовок
	if($direction_id==0){
		?><h2>Расписание автобусов на остановке <?=$station_name;?> <small>(от вокзала) <a href="#br" title='<?=$station_name;?> - к вокзалу'><span class="glyphicon glyphicon-arrow-right"></span> (к вокзалу)</a></small></h2><a name="fr"></a><?
	}
	else{
		?><h2>Расписание автобусов на остановке <?=$station_name;?> <small>(к вокзалу) <a href="#fr" title='<?=$station_name;?> - от вокзала'><span class="glyphicon glyphicon-arrow-right"></span> (от вокзала)</a></small></h2><a name="br"></a><?
	}

	//отрисовываем таблицу
	?><table  class="table table-bordered"><?

	for($ykey=-1; $ykey<$max_tr; $ykey++){
	
		if($ykey==-1){ ?><thead><? }
	?><tr><?
	
		foreach($x_coord as $xkey=>$xvalue){	
		$hak=$x_coord[$xkey];		
		$td_class='';
		
		//Собираем данные об автобусе				
		$bus_f_way=$bus_info_array[$xvalue]['f_way'];
		$bus_alias=$bus_info_array[$xvalue]['alias'];
		$bus_str_name=$bus_info_array[$xvalue]['bus_name'];		
		if(!empty($super_array[$hak][$ykey]['SCHEDULE'])){
		$td_class='sh'.$super_array[$hak][$ykey]['SCHEDULE'];
			if($super_array[$hak][$ykey]['SCHEDULE']==1){$td_class=$td_class.' '.'pay';}
		}
				
			if($ykey==-1){ ?><th><? } //Отрисовываем шапку
			else{ if(!empty($td_class)){?><td class="<?=$td_class?>"><?} else {?><td><?} }

			if($xkey!=-1){			
				if($ykey==-1){ //Выводим x-координаты (остановки)								
				?><a href="/<?=$town?>/bus/<?=$bus_alias?>/" class="stations_bus_href" title='Расписание автобуса № <?=$bus_str_name.' - '.$bus_f_way?>'><?=$bus_str_name;?></a><?
				}	
			}
		if(!empty($super_array[$xvalue][$ykey]['TIME'])){
			echo $super_array[$xvalue][$ykey]['TIME'];	//Выводим время
		}
			if($ykey==-1){?></th><?}//Отрисовываем шапку
			else{?></td><?}		
		}
	?></tr><?
	if($ykey==-1){ ?></thead><? }
	}
	?></table><?

}
	
	//Поддиретория статики - направление движения
	$dir4=MAIN_PATH.$town.'/html/station/'.$direction_value.'/';
	if(!is_dir($dir4)){ mkdir($dir4, 0777); chmod($dir4, 0777);}

	$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
	ob_end_clean(); 
	
	$file_src=MAIN_PATH.$town.'/html/station/'.$direction_value.'/'.$station_alias.'.html';
	$file_src=fopen($file_src, "w+" );
	fwrite($file_src, $buffer);
	fclose($file_src);
	}//Конец обхода по всем направлениям туда-сюда
//echo $z.'|'; 
$z++;
}//Бежим по всем остановкам
echo 'FINISHED';
?>
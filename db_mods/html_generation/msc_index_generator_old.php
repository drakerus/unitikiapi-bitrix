<?php 
error_reporting(E_ALL, E_NOTICE); 
ini_set("display_errors", 1); 
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('TOWN_ID', 1);
define('NUM_ROWS', 4);//количество столбцов со списком автобусов
define("MAIN_PATH", '/home/bitrix/www/mods/bus/town/');
$direction=array(0=>'f',1=>'b');
/*  */

//Получаем данные о городе
$sql="SELECT * FROM `town` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
if($row = mysql_fetch_array($result)){
$town=$row['town_alias'];
$town_name=$row['town_name'];
}

$super_bus_array=array();

//Собираем массив существующих уже автобусов в Москве
$sql="SELECT * FROM `bus` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$full_name='<b>'.$row['bus_name'].'</b> - '.$row['f_way'];
$full_name=str_replace(array('"','&quot;'), '`', $full_name);
$super_bus_array[$row['alias']]=$full_name;
}

//Проверяем на существование директорию
$dir1=MAIN_PATH.$town.'/';
if(!is_dir($dir1)){ //Если нет папки "/mods/bus/town/".$town.'/'
echo 'Folder does not exists: '.$dir1; die();
}
//Директория для статики
$dir2=MAIN_PATH.$town.'/html/';
if(!is_dir($dir2)){ mkdir($dir2); chmod($dir2, 0777); }
//Поддиретория для статики - остановки
$dir3=MAIN_PATH.$town.'/html/index/';
if(!is_dir($dir3)){ mkdir($dir3); chmod($dir3, 0777); }

ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)


?>
<a name="bs"></a>
<div class="row">
	<div class="col-md-12">
		<div class="input-group">
		<span class="input-group-btn"><button class="btn btn-info" type="button" OnClick="go_to_bus()"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
		<input type="text" id="bus" class="form-control" onkeydown="if(event.keyCode==13){go_to_bus();}" placeholder="Автобус" data-provide="typeahead" data-source='[
		<?
		foreach($super_bus_array as $key=>$value){
		echo '"'.$value.'"';
		if($value!=end($super_bus_array)){echo ', ';}
		}
		?>
		]'/>
		</div>
	</div>
</div>
<br/>
<?
$super_bus_array_f=array_chunk($super_bus_array, ceil(count($super_bus_array)/NUM_ROWS), TRUE);
//echo '<pre>'; print_r($super_bus_array_f); die();
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-info">
			<div class="panel-heading"><h3>Все автобусы г.<?=$town_name?> | <small><a href="#st" title="Расписание автобусов по всем остановкам г.<?=$town_name?>"><span class="glyphicon glyphicon-arrow-right"></span> Все остановки</a></small></h3></div>
			 <div class="panel-body">
						<?
						foreach($super_bus_array_f as $key=>$super_bus_array_small){
						?><div class="col-md-3"><div class="list-group"><?
							foreach($super_bus_array_small as $alias=>$name){
								if(!empty($name)){
								?><a href='/<?=$town;?>/bus/<?=$alias;?>/'  class='list-group-item' title='Расписание автобуса №<?=$name;?>'><?=$name;?></a><?
								}
							}
						?></div></div><?
						}
						?>
			</div>
		</div>
	</div>
</div>
<?
$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 
$file_src=MAIN_PATH.$town.'/html/index/index_top.html';
//echo $file_src;
$file_src=fopen($file_src, "w+" );
if(fwrite($file_src, $buffer)){
fclose($file_src);
}
?>
<?
ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?>
<?
$station_array=array();

//Собираем массив остановок Москвы
$sql="SELECT * FROM `station` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$station_array[html_entity_decode($row['alias'])]=html_entity_decode(str_replace(array('"','&quot;'), '`', $row['station_name']));
}
//echo '<pre>'; print_r($station_array); die();
?>
<a name="st"></a>
<div class="row">
	<div class="col-md-12">
		<div class="input-group">
		<span class="input-group-btn"><button class="btn btn-info" type="button" OnClick="go_to_station()"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
		<input type="text" id="station" class="form-control" onkeydown="if(event.keyCode==13){go_to_station();}" placeholder="Остановка" data-provide="typeahead" data-source='[
		<?
		foreach($station_array as $key=>$value){
		echo '"'.$value.'"';
		if($value!=end($station_array)){echo ', ';}
		}
		?>]'/></div>
	</div>
</div>
<br/>
<?
$station_array_f=array_chunk($station_array, ceil(count($station_array)/NUM_ROWS), TRUE);
//echo '<pre>'; print_r($station_array_f); die();
?>
<div class="row visible-lg-block">
	<div class="col-md-12">
		<div class="panel panel-info">	
			<div class="panel-heading"><h3>Все остановки в г.<?=$town_name?> | <small><a href="#bs" title="Расписание автобусов г.<?=$town_name?>" ><span class="glyphicon glyphicon-arrow-right"></span> Все автобусы</a></small></h3></div>				
			 <div class="panel-body">					
					<?foreach($station_array_f as $key=>$station_array_f_small){?>
					<div class="col-md-3"><div class="list-group"><?
						foreach($station_array_f_small as $alias=>$name){
							if(!empty($name)){
							?><a href='/<?=$town;?>/station/<?=$alias;?>/' class='list-group-item' title='Расписание автобусов на остановке <?=$name;?>'><?=$name;?></a><?	
							}
						}
					?></div></div><?}?>
			</div>				
		</div>
	</div>
</div>
<?
$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 

$file_src=MAIN_PATH.$town.'/html/index/index_bottom.html';
//echo $file_src;
$file_src=fopen($file_src, "w+" );
if(fwrite($file_src, $buffer)){
fclose($file_src);
echo 'INDEX GENETATED';
}
?>
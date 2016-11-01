<?
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1); 
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");

define('TOWN_ID', 1);
define('NUM_ROWS', 4);//количество столбцов со списком автобусов
define("MAIN_PATH", $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/');
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
		<h4>Поиск по автобусам</h4>
		<div class="alert alert-bus alert-block alert-danger"></div>    
		<div class="input-group">
		<span class="input-group-btn"><button class="btn btn-info" type="button"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
		     <input id="bus_typeahead" type="text" class="col-md-12 form-control" placeholder="Автобус.." autocomplete="off" />
		</div>	
	</div>		
	<div class="col-md-12">
		<div class="search_description">
		<span class="glyphicon glyphicon-info-sign"></span>&nbsp;Воспользуйтесь быстрым поиском по автобусам: начните вводить интересующий маршрут и просто выберите его из списка
		</div>
	</div>
</div>
<?
$super_bus_array_f=array_chunk($super_bus_array, ceil(count($super_bus_array)/NUM_ROWS), TRUE);
//echo '<pre>'; print_r($super_bus_array_f); die();
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-info">
			<div class="panel-heading"><h2>Городские автобусы г.<?=$town_name?> | <small><a href="#rbs" title="Расписание автобусов по всем остановкам г.<?=$town_name?>"><span class="glyphicon glyphicon-arrow-right"></span> Междугородние автобусы</a></small></h3></div>
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
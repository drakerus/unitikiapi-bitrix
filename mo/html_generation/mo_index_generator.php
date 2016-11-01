<? 
//error_reporting(E_ALL, E_NOTICE); 
//ini_set("display_errors", 1); 
session_start();
if(($_SESSION["parse"]['step_6']!='OK')OR(empty($_SESSION["parse"]['town_alias']))OR(empty($_SESSION["parse"]['id_town']))){  $_SESSION["fail_step"]='step_6'; }

set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
//define('TOWN_ID', 1);
define('NUM_ROWS', 4);//количество столбцов со списком автобусов
define("MAIN_PATH", $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/');
$direction=array(0=>'f',1=>'b');
/*  */
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

$sql='SELECT * FROM `bus_type`';
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$bus_type_array[$row['id_bus_type']]=$row['bus_type_name'];
}




$super_bus_array=array();

//Собираем массив существующих уже автобусов в Москве
$sql="SELECT * FROM `bus` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$full_name=$row['bus_name'].' - '.$row['f_way'];
$full_name=str_replace(array('"','&quot;'), '`', $full_name);
$super_bus_array[$row['alias']]=$full_name;
$super_bus_array_grouped[$row['id_bus_type']][]=$full_name.'###'.$row['alias'];
}

$NUM_ROWS=12/count($super_bus_array_grouped);

//Проверяем на существование директорию
$dir1=MAIN_PATH.$town.'/';
if(!is_dir($dir1)){ //Если нет папки "/mods/bus/town/".$town.'/'
echo 'Folder does not exists: '.$dir1; die();
}
//Директория для статики
$dir2=MAIN_PATH.$town.'/html/';
if(!is_dir($dir2)){ mkdir($dir2, 0777); chmod($dir2, 0777); }
//Поддиретория для статики - остановки
$dir3=MAIN_PATH.$town.'/html/index/';
if(!is_dir($dir3)){ mkdir($dir3, 0777); chmod($dir3, 0777); }


//echo '<pre>'; print_r($super_bus_array_grouped); die();


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
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-info">
				<div class="panel-heading"><h3>Все автобусы г.<?=$town_name?> | <small><a href="#st" title="Расписание автобусов по всем остановкам г.<?=$town_name?>"><span class="glyphicon glyphicon-arrow-right"></span> Все остановки</a></small></h3></div>
		</div>
	</div>
</div>
<div class="row"><?
	foreach($super_bus_array_grouped as $id_bus_type=>$super_bus_array_small){
	?><div class="col-md-<?=$NUM_ROWS;?>">
	<div class="panel panel-info"><div class="panel-heading capitalize"><h2><?=$bus_type_array[$id_bus_type];?></h2></div><div class="panel-body"><div class="list-group">
	<?
		foreach($super_bus_array_small as $key=>$barray){
		$b_array=explode('###',$barray); $name=$b_array['0']; $alias=$b_array['1'];
			if(!empty($name)){
			?><a  href='/<?=$town;?>/bus/<?=$alias;?>/' class="list-group-item" title='Расписание автобуса №<?=$name;?>'><?=$name;?></a><?
			}
		}
	?></div></div></div></div><?
	}
	?></div>
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
<?
$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 
$file_src=MAIN_PATH.$town.'/html/index/index_top.html';
//echo $file_src;
$file_src=fopen($file_src, "w+" );
if(fwrite($file_src, $buffer)){
fclose($file_src);
//echo 'INDEX GENETATED';
}
?>
<?
ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?>
<div class="row">		
	<div class="col-md-12">
		<h4>Поиск по остановкам</h4>			
        <div class="alert alert-station alert-block alert-danger"></div>    
		<div class="input-group">
			<span class="input-group-btn"><button class="btn btn-info" type="button"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
			<input id="station_typehead" type="text" class="col-md-12 form-control" placeholder="Остановка..." autocomplete="off" />
		</div>	
	</div>		
	<div class="col-md-12">
		<div class="search_description">
			<span class="glyphicon glyphicon-info-sign"></span>&nbsp;Воспользуйтесь быстрым поиском по остановкам: начните вводить название интересующей остановки и просто выберите её из списка	
		</div>
	</div>
</div>
<div class="row visible-lg-block">
	<div class="col-md-12">
		<div class="panel panel-info">
				<div class="panel-heading"><h3>Все остановки в г.<?=$town_name?> | <small><a href="#bs" title="Расписание автобусов г.<?=$town_name?>" ><span class="glyphicon glyphicon-arrow-right"></span> Все автобусы</a></small></h3></div>
				<div class="panel-body">
				
				
				<?
$station_array_f=array_chunk($station_array, ceil(count($station_array)/NUM_ROWS), TRUE);
//echo '<pre>'; print_r($station_array_f); die();
?><div class="row"><?
foreach($station_array_f as $key=>$station_array_f_small){
?><div class="col-md-3"><div class="list-group"><?
	foreach($station_array_f_small as $alias=>$name){
		if(!empty($name)){
		?><a href='/<?=$town;?>/station/<?=$alias;?>/' class="list-group-item" title='Расписание автобусов на остановке <?=$name;?>'><?=$name;?></a><?	
		}
	}
?></div></div><?
}
?></div>
				
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
//echo 'INDEX GENETATED';
}
?>
<?
$_SESSION["parse"]["step_7"]="OK";
$_SESSION["parse"]["current_step"]="step_7";
//echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?>
<?//if(!isset($_SESSION["parse"]["debug"])){?>
<script language="javascript">window.location.href = '<?=$target_href;?>';</script>
<?//}?>
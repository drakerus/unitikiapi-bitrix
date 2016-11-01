<?php 
error_reporting(E_ALL, E_NOTICE); 
ini_set("display_errors", 1);
session_start();
if(($_SESSION["parse"]['step_7']!='OK')OR(empty($_SESSION["parse"]['town_alias']))OR(empty($_SESSION["parse"]['id_town']))){ $_SESSION["fail_step"]='step_7'; }

set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
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
$super_bus_array=array();

//Собираем массив существующих уже автобусов в Москве
$sql="SELECT * FROM `bus` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$full_name=$row['bus_name'].' - '.$row['f_way'];
$full_name=str_replace(array('"','&quot;'), '`', $full_name);
$super_bus_array[$row['alias']]=$full_name;
}
?>
<?
$station_array=array();
//Собираем массив остановок Москвы
$sql="SELECT * FROM `station` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$station_array[html_entity_decode($row['alias'])]=html_entity_decode(str_replace(array('"','&quot;'), '`', $row['station_name']));
}


$town_array=array();
//Собираем массив городов
$sql="SELECT * FROM `town`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$town_array[html_entity_decode($row['town_alias'])]=html_entity_decode(str_replace(array('"','&quot;'), '`', $row['town_name']));
}
//echo '<pre>'; print_r($station_array); die();

//Проверяем на существование директорию
$dir1=MAIN_PATH.$town.'/';
if(!is_dir($dir1)){ //Если нет папки "/mods/bus/town/".$town.'/'
echo 'Folder does not exists: '.$dir1; die();
}
//Директория для статики
$dir2=MAIN_PATH.$town.'/html/';
if(!is_dir($dir2)){ mkdir($dir2); chmod($dir2, 777); }
//Поддиретория для статики - остановки
$dir3=MAIN_PATH.$town.'/html/index/';
if(!is_dir($dir3)){ mkdir($dir3); chmod($dir3, 777); }

ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?>
<script>
$(function(){
     function displayResultStation(item) {
					if(item.text!='Result not Found'){
					var prefix='http://mybuses.ru/';
					var postfix='/station/';			
					var postfixxx='/';
					var town='<?=$town;?>';
					var src=prefix+town+postfix+item.value+postfixxx;
					window.open(src, "_self");					
					}
					else{
					$('.alert-station').show().html('Увы ничего не найдено, попытайтесь снова');
					}
                }
				
				   function displayResultBus(item) {
                   if(item.text!='Result not Found'){					
					var prefix='http://mybuses.ru/';
					var postfix='/bus/';					
					var postfixxx='/';
					var town='<?=$town;?>';
					var src=prefix+town+postfix+item.value+postfixxx;
					window.open(src, "_self");				
					}
					else{
					$('.alert-bus').show().html('Увы ничего не найдено, попытайтесь снова');
					}
                }

                $('#station_typehead').typeahead({
                    source: [					
					<?foreach($station_array as $key=>$value){?>
					 {id: '<?=$key;?>', name: '<?=$value;?>'},
					<?}?>                       
                    ],					
                    onSelect: displayResultStation
                });
				
				$('#bus_typeahead').typeahead({
                    source: [
					<? foreach($super_bus_array as $key=>$value){?>
					 {id: '<?=$key;?>', name: '<?=$value;?>'},
					<?}?> 
                    ],
                    onSelect: displayResultBus
                });
                
        });
</script>
<?
$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 
$file_srcxx=MAIN_PATH.$town.'/html/index/search_script.html';
//echo $file_srcxx;
$file_srcxx=fopen($file_srcxx, "w+" );
if(fwrite($file_srcxx, $buffer)){
fclose($file_srcxx);
//echo 'INDEX GENETATED';
}
?>


<?
ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?>
<script type="text/javascript">
function go_to_town(){
var town_array = new Array();
<? foreach($town_array as $key=>$value){?>
town_array["<?=$value?>"]='<?=$key?>';
<? } ?>
var town_alias = town_array[$('#town').val()];
var prefix='http://mybuses.ru/';
var postfixxx='/';
var src=prefix+town_alias+postfixxx;
window.location.href = src;
}
</script>
<?
$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 

$file_src=MAIN_PATH.$town.'/html/index/sst.js';
//echo $file_src;
$file_src=fopen($file_src, "w+" );
if(fwrite($file_src, $buffer)){
fclose($file_src);
//echo 'INDEX GENETATED';
}
?>
<?
$_SESSION["parse"]["step_8"]="OK";
$_SESSION["parse"]["current_step"]="step_8";
echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?><?//if(!isset($_SESSION["parse"]["debug"])){?>
<script language="javascript">window.location.href = '<?=$target_href;?>';</script><?//}?>
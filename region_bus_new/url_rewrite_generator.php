<?
$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
?>
<?//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?// 
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/unitikiapi.php");
define('DB_PREFIX', 'ph382841_mybuses');
define('MAIN_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/');
define('NUM_ROWS', 4);

//Список всех городов России с интересной популяцией имеющие вокзалы ( WHERE `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 )
$sql_0='SELECT `reg_town`.`town_name`, `reg_town`.`town_alias` FROM `reg_town` JOIN `reg_town_rating` ON `reg_town`.`town_code`=`reg_town_rating`.`town_code` JOIN `reg_station` ON `reg_station`.`id_town`=`reg_town`.`id_town` JOIN `reg_stoppoint` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` WHERE `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 GROUP BY  `reg_town`.`id_town` ORDER BY `reg_town`.`town_name`  ASC;';
$result=mysql_query($sql_0);
while($row = mysql_fetch_array($result)){ $town_array[$row['town_alias']]=$row['town_name']; }

//echo '<pre>'; print_r($town_array); die();
ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
foreach($town_array as $town_alias=>$town_name){
?>array(
    "CONDITION" => "#^/<?=$town_alias;?>/way/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=<?=$town_alias;?>&page=way&way_alias=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/<?=$town_alias;?>/terminus/([\w\)\(\".,_-]*)/#",
	"RULE" => "alias=<?=$town_alias;?>&page=terminus&terminus_alias=$1",
    "PATH" => "/town/index.php",
	),
	array(
    "CONDITION" => "#^/<?=$town_alias;?>/#",
	"RULE" => "alias=<?=$town_alias;?>&page=index",
    "PATH" => "/town/index.php",
	),
	<?	
}
		$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
		ob_end_clean(); 
		$file_src=$_SERVER["DOCUMENT_ROOT"].'/addonurlrewrite.php';
		$file_src=fopen($file_src, "w+" );
		fwrite($file_src, $buffer);
		fclose($file_src);
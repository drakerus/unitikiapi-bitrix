<?
error_reporting(E_ALL ^ E_NOTICE); 
ini_set("display_errors", 1);
//подключаемся к БД
session_start();
//unset($_SESSION['parse']);
/*
unset($_SESSION['parse']); $_SESSION['parse']['current_step']="step_7";
unset($_SESSION['parse']);
*/

$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/mo/log/');
?>
<?
$sql='SELECT id_town FROM  `town` WHERE id_town <>1 ORDER BY id_town;';
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$town_array[]=$row['id_town'];
}

?>
<?
$target_href[1]='http://mybuses.ru/mods/bus/mo/getters/bus_loader.php';
$target_href[2]='http://mybuses.ru/mods/bus/mo/getters/station_loader.php';
$target_href[3]='http://mybuses.ru/mods/bus/mo/getters/htm_geter.php';
$target_href[4]='http://mybuses.ru/mods/bus/mo/getters/time_loader.php';
$target_href[5]='http://mybuses.ru/mods/bus/mo/html_generation/mo_bus_generator.php';
$target_href[6]='http://mybuses.ru/mods/bus/mo/html_generation/mo_station_generator.php';
$target_href[7]='http://mybuses.ru/mods/bus/mo/html_generation/mo_index_generator.php';
$target_href[8]='http://mybuses.ru/mods/bus/mo/html_generation/mo_index_script_generator.php';
$target_href[10]='http://mybuses.ru/mods/bus/sitemap_generator.php';
$target_href[99]='http://mybuses.ru/mods/bus/mo/conctructor.php';

if(!isset($_SESSION['parse'])){
?><script language="javascript">window.location.href = '<?=$target_href[1];?>';</script><?	
}

if($_SESSION['parse']["current_step"]=="step_8"){//После последнего успешно выполненного скрипта меняем город
//die('OK');	
	$new_id_town=$town_array[array_search($_SESSION["parse"]['id_town'], $town_array)+1];
	if($new_id_town<(end($town_array)+1)){
		
			 
		$sql='UPDATE  `ph382841_mybuses`.`cursor` SET  `id_town` =  '.$new_id_town.' WHERE  `cursor`.`id_cursor` =1;';		
		//echo $sql; die();
		mysql_query($sql);
		$str_town=json_encode($_SESSION["parse"]);		
		$file_src2=SAVE_PATH.$_SESSION["parse"]["town_alias"].'.json';
		$file_src2=fopen($file_src2, "w+" );
		fwrite($file_src2, $str_town);
		fclose($file_src2);
		unset($_SESSION['parse']);
		//echo '<pre>'; print_r($_SESSION);
		?><script language="javascript">window.location.href = '<?=$target_href[99];?>';</script><?	
	}
	else{
	die('constructor - OK');	
	}
		
}
		
$i=str_replace('step_', '',$_SESSION['parse']["current_step"])+1;
if($_SESSION["parse"][$_SESSION['parse']["current_step"]]=='OK') {?>
<script language="javascript">window.location.href = '<?=$target_href[$i];?>';</script>	
<?}?>

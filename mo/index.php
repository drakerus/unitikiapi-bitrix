<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
session_start();
$_SESSION["parse"]["debug"]="TRUE";
//ini_set('error_reporting', E_ALL ^ E_NOTICE);
//error_reporting(E_ALL ^ E_NOTICE);
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
?>
<?
if(isset($_POST['town_change'])){
$sql='UPDATE `ph382841_mybuses`.`cursor` SET `id_town` =  "'.$_POST['town_id_change'].'"	WHERE `cursor`.`id_cursor` =1;';
echo $sql;
mysql_query($sql);
}
?>
<?
$sql='SELECT town.town_name, town.id_town, town.town_alias FROM `town` LEFT JOIN `cursor` ON town.id_town=cursor.id_town WHERE id_cursor=1';
$result = mysql_query($sql);
if($row=mysql_fetch_array($result)){
	$selected_town_id=$row['id_town'];
	$selected_town_name=$row['town_name'];
	$selected_town_alias=$row['town_alias'];
}
//$sql='SELECT `town_name`, `id_town` FROM  `town` WHERE active=1 ORDER BY `town_name`';
$sql='SELECT `town_name`, `id_town` FROM  `town` WHERE active=1 ORDER BY `town_name`';
$result = mysql_query($sql);
$i=0;
while($row=mysql_fetch_array($result)){
	$all_towns_array[$row['id_town']]=$row['town_name'];
}	
?>
<h1>Текущий город: <a href="/<?=$selected_town_alias;?>/" target="_blank" ><?=$selected_town_name;?></a></h1>
<form method="POST">
<select name="town_id_change">
	<?foreach($all_towns_array as $id_town=>$town_name){
		?><option value="<?=$id_town;?>" <?if($id_town==$selected_town_id){echo ' selected';}?> ><?=$town_name;?></option><?
	}	
	?>
<select>
<input class="btn btn-default" type="submit" value="Сменить" name="town_change">
</form>

<ol style="margin:30px;">
<li><a href="/mods/bus/mo/getters/bus_loader.php" target="_blank">Создание базы автобусов города</a></li>
<li><a href="/mods/bus/mo/getters/station_loader.php" target="_blank">Создание базы остановок города</a></li>
<li><a href="/mods/bus/mo/getters/htm_geter.php" target="_blank">Скачивание исходных страниц на сервер</a></li>
<li><a href="/mods/bus/mo/getters/time_loader.php" target="_blank">Создание базы времён</a></li>
<li><a href="/mods/bus/mo/html_generation/mo_bus_generator.php" target="_blank">Создание HTML страниц автобусов</a></li>
<li><a href="/mods/bus/mo/html_generation/mo_station_generator.php" target="_blank">Создание HTML страниц остановок</a></li>
<li><a href="/mods/bus/mo/html_generation/mo_index_generator.php" target="_blank">Создание HTML страницы города</a></li>
<li><a href="/mods/bus/mo/html_generation/mo_index_script_generator.php" target="_blank">Создание скриптов страницы города</a></li>
<li><a href="/mods/bus/sitemap_generator.php" target="_blank">Создание карты сайта</a></li>
</ol>
<ul>
<li><a href="/mods/bus/mo/html_generation/mo_station_cheker.php" target="_blank">Проверка базы остановок - для новых городов</a></li>
<li><a href="/mods/bus/index_index_generator.php" target="_blank">Создание главной страницы сайта</a></li>
</ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
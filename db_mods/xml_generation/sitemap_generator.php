<?php 
error_reporting(E_ALL, E_NOTICE); 
ini_set("display_errors", 1); 
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define("MAIN_PATH", '/home/bitrix/www/sitemaps/');
$direction=array(0=>'f',1=>'b');

$town_array=array();
//Собираем массив городов
$sql="SELECT * FROM `town`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$town_array[$row['id_town']]=html_entity_decode($row['town_alias']);
}

foreach($town_array as $id_town=>$town_alias){

	$super_bus_array=array();
	//Собираем массив существующих уже автобусов
	$sql="SELECT * FROM `bus` WHERE id_town='".$id_town."';";
	$result=mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
	$super_bus_array[]=$row['alias'];
	}
	?>
	<?
	$station_array=array();
	//Собираем массив остановок
	$sql="SELECT * FROM `station` WHERE id_town='".$id_town."';";
	$result=mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
	$station_array[]=html_entity_decode($row['alias']);
	}
	//echo '<pre>'; print_r($station_array); die();

	ob_start();                     // Включаем буферизацию вывода
	ob_clean();                     // Чистим буфер (не обязательно)

echo '<?xml version="1.0" encoding="UTF-8"?>';	
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <url>
      <loc>http://mybuses.ru/<?=$town_alias;?>/</loc>
      <lastmod><?=date('Y-m-d');?></lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.8</priority>
   </url>
<?foreach($super_bus_array as $key=>$bus_alias){?>
<url>
		<loc>http://mybuses.ru/<?=$town_alias;?>/bus/<?=$bus_alias;?>/</loc>
		<changefreq>weekly</changefreq>
		<lastmod><?=date('Y-m-d');?></lastmod>
</url>
<?}?>
<? foreach($station_array as $key=>$station_alias){?>
<url>
		<loc>http://mybuses.ru/<?=$town_alias;?>/station/<?=$station_alias;?>/</loc>
		<changefreq>weekly</changefreq>
		<lastmod><?=date('Y-m-d');?></lastmod>
</url>
<?}?>

</urlset>

	<?
	$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
	ob_end_clean(); 

	$file_src=MAIN_PATH.$town_alias.'_sitemap.xml';
	//echo $file_src;
	$file_src=fopen($file_src, "w+" );
	if(fwrite($file_src, $buffer)){
	fclose($file_src);
	//echo 'INDEX GENETATED';
	}

}
?>
<?
	ob_start();                     // Включаем буферизацию вывода
	ob_clean();                     // Чистим буфер (не обязательно)
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?foreach($town_array as $id_town=>$town_alias){?>
   <sitemap>
      <loc>http://mybuses.ru/sitemaps/<?=$town_alias;?>_sitemap.xml</loc>
      <lastmod><?=date('Y-m-d');?></lastmod>
   </sitemap>
<?}?>
</sitemapindex>	
<?
	$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
	ob_end_clean(); 

	$file_src=$_SERVER["DOCUMENT_ROOT"].'sitemap.xml';
	//echo $file_src;
	$file_src=fopen($file_src, "w+" );
	if(fwrite($file_src, $buffer)){
	fclose($file_src);
	//echo 'INDEX GENETATED';
	}
?>
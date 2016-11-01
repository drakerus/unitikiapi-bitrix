<?
session_start();
if(($_SESSION["parse"]['step_1']!='OK')OR(empty($_SESSION["parse"]['town_alias']))OR(empty($_SESSION["parse"]['id_town']))){ $_SESSION["fail_step"]='step_1'; }

/*
echo '<pre>';
print_r($_SESSION);
die();
*/

ini_set('error_reporting', E_ALL ^ E_NOTICE);
error_reporting(E_ALL ^ E_NOTICE);

require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/moscow/array/');
require ($_SERVER["DOCUMENT_ROOT"].'/mods/phpQuery/phpQuery.php');
?>
<?
//Вычисляем ID города по курсору
$sql='SELECT * FROM `cursor` WHERE id_cursor=1';
$result=mysql_query($sql);
if($row = mysql_fetch_array($result)){
define('TOWN_ID', $row['id_town']);
}

//Читаем данные города
$sql="SELECT * FROM `town` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
if($row = mysql_fetch_array($result)){
define('TOWN_ALIAS', $row['town_alias']);
define('REMOTE_SRC', $row['mta_href']);
}

//перед загрузкой автобусов города - стираем старые записи
mysql_query("DELETE FROM `station` WHERE `id_town` = '".TOWN_ID."';");


//Собираем массив алиасов ощибочных останов с целью не добавления их в базу
$alias_db_array=array();
//Читаем данные города
$sql="SELECT * FROM `alias` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$alias_db_array[$row['id_alias']]=$row['bad_value'];
}


//Бежим по всем автобусам:

$sql="SELECT * FROM `bus` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
	$sourse_href = 'http://navi.mostransavto.ru/'.$row['href'];
	$results_page = get_xml_page($sourse_href);
	$results = phpQuery::newDocument($results_page);	
	$tables = $results->find('td.stops');
	$info = array();

		foreach($tables as $table){//Бежим по всем таблицам расписания
		$way=pq($table)->text();
					$way=trim($way);					
					$way = mb_convert_encoding($way, 'cp1252', 'utf-8' );
					$way = mb_convert_encoding($way, 'utf-8', 'cp1251' );					
		$station_name_array[]=$way;		
		}
		
}

$station_name_array=array_unique($station_name_array);
sort($station_name_array);
reset($station_name_array);

$result_station_array=array_diff($station_name_array,$alias_db_array);

//echo count($result_station_array).'='.count($station_name_array).'-'.count($alias_db_array).'<br/>';
//echo '<pre>'; print_r($station_name_array); print_r($alias_db_array); print_r($result_station_array); die();

foreach($result_station_array as $station_name){
	$alias=transletiration($station_name);			
	$sql = "INSERT INTO station SET station_name='".$station_name."', alias='".$alias."', id_town='".TOWN_ID."'";
	mysql_query($sql);	
}
//die();

function get_xml_page($url) {
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
$page = curl_exec($ch);
curl_close($ch);
return $page;
}
?>
<?
$_SESSION["parse"]["step_2"]="OK";
$_SESSION["parse"]["current_step"]="step_2";
echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?>
<script language="javascript">window.location.href = '<?=$target_href;?>';</script>
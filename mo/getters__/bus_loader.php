<?
session_start();
ini_set('error_reporting', E_ALL ^ E_NOTICE);
error_reporting(E_ALL ^ E_NOTICE);

//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/moscow/array/');
require ($_SERVER["DOCUMENT_ROOT"].'/mods/phpQuery/phpQuery.php');
?>
<?
$arr_bus_type=array(
		"городские"=>"1",
		"пригородные"=>"2",			
		"междугородные"=>"3",		
		"Автобусы до Москвы"=>"4",	
		);
?>
<?//Вычисляем ID города по курсору
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
mysql_query("DELETE FROM `bus` WHERE `id_town` = '".TOWN_ID."';");


//Парсим источник
$results_page = get_xml_page(REMOTE_SRC);
$results = phpQuery::newDocument($results_page);
$table = $results->find('table[width="95%"]');

$info = array();
$n=0;
$z=0;
$tds=$table->find('td');

foreach($tds as $td){
$z=$n+4;
	if($n>7){
		$way=pq($td)->text();
		$way=trim($way);
		
		$way = mb_convert_encoding($way, 'cp1252', 'utf-8' );
		$way = mb_convert_encoding($way, 'utf-8', 'cp1251' );
				
		if (($z+1) % 7 == 0){
		$info['way'][]=$way;
		$info['src'][]=pq($td)->find('a')->attr('href');	
		}
		
		if (($z+2) % 7 == 0){
		$info['bus_nuber'][]=$way;		
		}
		
		if (($z+3) % 7 == 0){
		$info['subway'][]=$way;	
		}		
				
		if (($z+6) % 7 == 0){
		$info['type'][]=$way;	
		}		
	}
$n++;
}

//echo '<pre>'; print_r($info); die();
//print_r($info['subway']);


//Добавляем все новые станции метро в справочник метро:

//Cобираем станции метро
$sql="SELECT * FROM `subway`;";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
//$subway_db_array[$row['id_subway']]=$row['subway_name'];
$subway_db_array[]=$row['subway_name'];
}

//Собираем типы автобусов
$sql="SELECT * FROM `bus_type`";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$bus_type_array[$row['id_bus_type']]=$row['bus_type_name'];
}


$clear_subway_insert_array=array_unique($info['subway']);

//echo '<pre>'; print_r($subway_db_array); die();


foreach($clear_subway_insert_array as $subway){
	if(strlen($subway)>4){
			if (!in_array($subway, $subway_db_array)) {//Если такой станции метро ещё нет в базе данных, ее необходимо добавить
			$sql = "INSERT INTO subway SET subway_name='".$subway."';";
			mysql_query($sql);
			echo '<br/>Добавляем станицю:'.$subway;
			}
	}
}

$trans_info=array_flip($info['src']);

//echo '<pre>'; print_r($trans_info);
$alias_array=array();


foreach($trans_info as $src=>$j){
//Вычисляем тип автобуса
if(strlen($info['subway'][$j])>4){//Автобус идёт до метро
	$bus_type=4;
	//Вычисляем id метро для закрепления за автобусом
	$sql="SELECT * FROM `subway` WHERE subway_name='".$info['subway'][$j]."';";
	$result=mysql_query($sql);
	if($row = mysql_fetch_array($result)){
	//$subway_db_array[$row['id_subway']]=$row['subway_name'];
	$subway_db_array[]=$row['subway_name'];
	$id_subway=$row['id_subway'];
	}
	else{ echo $info['subway'][$j]; die(); }

}
else{
	$bus_type=$arr_bus_type[$info['type'][$j]];
	if(empty($bus_type)){echo $info['type'][$j]; echo '<pre>'; print_r($arr_bus_type); die();}
	$id_subway=0;
}

	//Если автобус с таким номером уже существует, делаем ему уникальный alias
	if(in_array($info['bus_nuber'][$j], $alias_array)){
	$alias=$info['bus_nuber'][$j].'_';
	}
	else{
	$alias=$info['bus_nuber'][$j];
	}	
	$alias_array[]=$alias;

$href=$info['src'][$j];
$bus_name=$info['bus_nuber'][$j];
$f_way=$info['way'][$j];
$way_arr=explode('–',$f_way);
$b_way=$way_arr['1'].'-'.$way_arr['0'];
		
$sql = "INSERT INTO bus SET bus_name='".$bus_name."', id_bus_type='".$bus_type."', id_subway='".$id_subway."', f_way='".$f_way."', b_way='".$b_way."', alias='".$alias."', href='".$href."', id_town='".TOWN_ID."'";
mysql_query($sql);		
}

//Добавляем автобусы текущего города в справочник 
$total_bus_amount=count($info);

function get_xml_page($url){
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
$_SESSION["parse"]["id_town"]=TOWN_ID;
$_SESSION["parse"]["town_alias"]=TOWN_ALIAS;
$_SESSION["parse"]["step_1"]="OK";
$_SESSION["parse"]["current_step"]="step_1";
echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?>
<script language="javascript">window.location.href = '<?=$target_href;?>';</script>
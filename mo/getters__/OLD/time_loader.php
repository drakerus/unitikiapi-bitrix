<?
ini_set('error_reporting', E_ALL ^ E_NOTICE);
error_reporting(E_ALL ^ E_NOTICE);

$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('SAVE_PATH', '/home/bitrix/www/mods/bus/town/moscow/array/');
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

<?
//Читаем курсор города
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

//Собираем массив существующих уже остановок в этом городе


//Собираем массив существующих уже остановок
$sql="SELECT * FROM `station` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$stations_db_array[html_entity_decode($row['station_name'])]=$row['id_station'];
}

//Собираем массив алиасов
$sql="SELECT * FROM `alias` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$alias_db_array[html_entity_decode($row['bad_value'])]=$row['good_value'];
}


//Собираем массив существующих уже автобусов в Москве
$sql="SELECT * FROM `bus` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$bus_db_array[html_entity_decode($row['alias'])]=$row['id_bus'];
$all_buses_array[]=$row['id_bus'];
}

//Собираем массив существующих типов рейсов
$sql="SELECT * FROM `race_type`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$_schedules[html_entity_decode($row['mgs_value'])]=$row['id_race_type'];
$shedule_type_array[html_entity_decode($row['mta_value'])]=$row['id_race_type'];
$schedule_chek_array[$row['id_race_type']]=$row['human_value'];
}


$payment_type_array=array(
"соц"=>0,
"дог"=>1,
);


$direction_array=array(
0=>"Прямой рейс",
1=>"Обратный рейс",
);

$add_on_array=array();
if((count($alias_db_array))>0){//Существую города без ошибочных остановок!
//Добавляем алиасы
foreach($alias_db_array as $failname=>$realname){
	if(!empty($stations_db_array[$realname])){
	$add_on_array[$failname]=html_entity_decode($stations_db_array[$realname]);
	}
	else{//Попались HTML сучности
		if(!empty($stations_db_array[html_entity_decode($realname)])){
		$add_on_array[$failname]=$stations_db_array[html_entity_decode($realname)];
		}
		else{//Проблема глубже		
		echo '<br><b>ПРОБЛЕМА</b>  плохое имя - хорошее имя'.$failname.'=>'.$realname;	
		
		}
	}
}

	if(count($add_on_array)>0){
	$stations_db_array=$stations_db_array+$add_on_array; //Добавляем в массив остановок - ошибочные останвоки с целью склейки в единую базу
	}
}

if(isset($_GET['num'])){$num=$_GET['num'];}
else{$num=0;}
$next=$num+1;
$bus_id=$all_buses_array[$num];
$src = 'http://mybuses.ru/mods/bus/town/'.TOWN_ALIAS.'/source/'.$bus_id.'.html';

//echo $src; die(); 
	
	$results_page = get_xml_page($src);
	$results = phpQuery::newDocument($results_page);
	$c_ways=$results->find('p.routecap')->length();

	if($c_ways>1){//Если вхождений больше 1 - следовательно есть и прямые и обратные рейсы	
	$c_fulltable=$results->find('table[width="95%"]')->htmlOuter();
	$c_fulltable=encoding_converter($c_fulltable);	
	$tarr=explode('Обратные рейсы',$c_fulltable);
	$tar=pq($tarr[0]);
	$direct_way_race_amount=$tar->find('table.shedule')->length();
	}
	else{
	$direct_way_race_amount=$results->find('table.shedule')->length();
	}	
	
	//Получаем все таблицы
	$tables = $results->find('table.shedule');	
	$q=0;

		$race_type_arr=array();
		$bus_type_arr=array();
		$stops_name_arr=array();
		$times_array=array();
		
		foreach($tables as $table){//Бежим по всем таблицам расписания				
		
			$table = pq($table);
		
			//перебираем все типы маршрутов			
			$trs = $table->find('tr.stops td');
			foreach($trs as $tr){								
			$tt=pq($tr)->text();
			$tt=encoding_converter($tt);	
			$race_type_arr[$q][]=$tt;
			}
			
			$fonts = $table->find('font.days');
			foreach($fonts as $font){						
			$tt=pq($font)->text();
			$tt=encoding_converter($tt);
			$bus_type_arr[$q][]=$tt;
			}
			
			//перебираем все остановки
			$tds = $table->find('tr td.stops');			
			$n=0;			
			foreach($tds as $td){
			$n++;
			$tt=pq($td)->text();
			$tt=encoding_converter($tt);
			$stops_name_arr[$q][]=$tt;
			}			
			
			$trz=$table->find('tr');			
			$a=-4;
			foreach($trz as $tz){//Бежим по всем строкам таблицы
			$tz=pq($tz);		
			$a++;
			
				$tdimes = $tz->find('td.time');
				$b=-1;				
				foreach($tdimes as $tdime){		
				$b++;
				$tt=pq($tdime)->text();
				$tt=encoding_converter($tt);
				$times_array[$q][$a][$b]=$tt;
				}
					
			}
		$q++;	
		}
		
//Добавляем все времена в базу Bitrix
$cursor='1';
$duplication_array=array();
foreach($times_array as $key=>$table){
//$key - номер таблицы	
//Определяем направление движения по номеру таблицы
if($key<$direct_way_race_amount){//прямой рейс
$direction=0;
}
else{//
$direction=1;
}

//echo '<pre>'; print_r($stations_db_array); die();

	foreach($table as $key_tr=>$tr){	
	//$key_tr - номер строки		
		foreach($tr as $race=>$time){
		
		if(!preg_match('/[0-9]/', $time)){$time='#';}
		
				$id_station = $stations_db_array[($stops_name_arr[$key][$key_tr])];//Остановка
				if($race_type_arr[$key][$race]=='_______'){$race_type_arr[$key][$race]='пвсчпсв';}
				$id_race_type = $shedule_type_array[$race_type_arr[$key][$race]];//Тип рейса							
				$id_payment_type =  $payment_type_array[$bus_type_arr[$key][$race]];//Тип рейса	
				$unq=$time.'#'.TOWN_ID.'#'.$bus_id.'#'.$id_station.'#'.$id_race_type.'#'.$id_payment_type.'#'.$direction;
				
				if(!in_array($unq,$duplication_array)OR($time=='#')){
				
					$sql = "INSERT INTO time SET time='".$time."', id_town='".TOWN_ID."', id_bus='".$bus_id."', id_station='".$id_station."', id_race_type='".$id_race_type."', payment_type='".$id_payment_type."', race_number='".$cursor."', race_direction='".$direction."';";
					if((!isset($bus_id))OR(!isset($id_station))OR(!isset($id_race_type))OR(!isset($direction))){
					echo $shedule.' sql: '.$sql;
					echo '<br/> SHEDULE: '.$race_type_arr[$key][$race];
					echo '<br/> STATION: '.$stops_name_arr[$key][$key_tr];
					echo '<pre>'; print_r($super_array);
					die(' FATAL ERROR');
					}				
					mysql_query($sql);			
				}
				else{
				echo '<br/>.DUPLICATE#'.$unq;
				}
		$duplication_array[]=$unq;
		}
	$cursor++;
	}
}

function get_xml_page($url) {
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$page = curl_exec($ch);
curl_close($ch);
return $page;
}

function encoding_converter($way){
$way=trim($way);					
$way = mb_convert_encoding($way, 'cp1252', 'utf-8' );
$way = mb_convert_encoding($way, 'utf-8', 'cp1251' );
return $way;
}
?>
<?
if($num>0){usleep(250);}
$mnum=count($all_buses_array)-1;
if($num>=$mnum){
die('SUCCESS');
}
$target_href='http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?num='.$next;
?>
<script language="javascript">
window.location.href = '<? echo $target_href; ?>';
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/unitikiapi.php");
define('DB_PREFIX', 'ph382841_mybuses');
define('NUM_ROWS', 4);

$town_name='Москва';
$town='moscow';
/*$sql="SELECT * FROM  `reg_town` WHERE  `population_rating` <>0 ORDER BY  `reg_town`.`town_name` ASC";
$result=mysql_query($sql);*/

//короче запарил, я заказываю пуэр


$sql='
SELECT `reg_thread`.`thread_name`, `reg_town`.`town_name` 
FROM `reg_thread` 
JOIN `reg_stoppoint` ON `reg_thread`.`id_thread`=`reg_stoppoint`.`id_thread` 
JOIN `reg_station` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` 
JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town` 
WHERE `reg_town`.`id_town`=61 AND `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0;';

$sql='SELECT `reg_station`.`station_name`, `reg_station`.`id_station`, `reg_town`.`id_town` FROM `reg_stoppoint` JOIN `reg_station` ON  `reg_stoppoint`.`id_station`=`reg_station`.`id_station`  JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town` WHERE `reg_town`.`id_town`=43 AND `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0;';


//все вокзалы города terminus
$sql='SELECT `reg_station`.`station_name`, `reg_station`.`id_station` FROM `reg_stoppoint` JOIN `reg_station` ON  `reg_stoppoint`.`id_station`=`reg_station`.`id_station`  JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town` LEFT JOIN `reg_station_rating` ON `reg_station`.`station_code`=`reg_station_rating`.`station_code` WHERE `reg_town`.`id_town`=20 AND `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 GROUP BY `reg_station`.`id_station` ORDER BY `reg_station_rating`.`rating_value` DESC;';
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){$terminus_array[$row['id_station']]=$row['station_name'];}
//echo '<pre>'; print_r($terminus_array);

	$terminus_array_w=array_chunk($terminus_array, ceil(count($terminus_array)/NUM_ROWS), TRUE);
//echo '<pre>'; print_r($terminus_array_w); die();	
	//if(count($terminus_array>3)){}else{}
	
foreach( $terminus_array_w	as $terminus_array_small){
	?><div class="row"><?
	foreach($terminus_array_small as $id_terminus=>$terminus_name){
		$terminus_alias=transletiration($terminus_name);
		?><div class="col-md-4">
			<div class="panel panel-info">
				<div class="panel-heading"><h3><a href="/<?=$town;?>/terminus/<?=$terminus_alias?>/" title="Расписание автобусов <?=$town_name;?> автостанции <?=$terminus_name;?>">Автостанция <?=$terminus_name;?></a></h3></div>
				<div class="panel-body">
					<div class="list-group">
					<?
					//все направления вокзала ways
					$sql='SELECT `reg_thread`.`thread_name`, `reg_thread`.`id_thread`, `reg_thread`.`thread_alias`  FROM `reg_thread` JOIN `reg_stoppoint` ON `reg_thread`.`id_thread`=`reg_stoppoint`.`id_thread` JOIN `reg_station` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` WHERE `reg_station`.`id_station`='.$id_terminus.'  AND `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 GROUP BY `reg_thread`.`thread_name`;';		
					$result=mysql_query($sql);
					while($row = mysql_fetch_array($result)){
						//$thread_array[$row['id_thread']]=$row['thread_name'];
						?><a href="/<?=$town;?>/way/<?=$row['thread_alias'];?>/"  class='list-group-item'  title="расписание автобусов <?=threadNameCorrector($row['thread_name'], $town_name);?>" ><?=threadNameCorrector($row['thread_name'], $town_name);?></a><?
					}
					?>				
					</div>
				</div>	
			</div>	
		
		</div><?	
	}
	?></div><?
}


//print_r($thread_array);



$way_name='Москва - Ереван';
//Все нитки направления
$sql="SELECT * FROM  `reg_thread` WHERE  `thread_name` =  '".$way_name."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){$bus_array[$row['id_thread']]=$row['thread_name'];}
//print_r($bus_array);

//Выбор информации по конкретному направлению: Теплый Стан АС - Кагул' 
$header_array=array();
$date_array=array();
$super_array=array();

$i=0;
foreach($bus_array as $id_thread=>$thread_name){//Обход всех нитей направления

//Выбираем данные - туда
/*
$sql_1='SELECT `reg_town`.`town_name`, `reg_station`.`station_name`, `district`.`district_name`, `region`.`region_name`, `country`.`country_name` FROM `reg_thread` JOIN `reg_stoppoint` ON `reg_thread`.`id_thread`=`reg_stoppoint`.`id_thread` JOIN `reg_station` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town` LEFT JOIN `district` ON `reg_town`.`id_district`=`district`.`id_district` JOIN `region`  ON `reg_town`.`id_region`=`region`.`id_region` JOIN `country` ON `reg_town`.`id_country`=`region`.`id_country` WHERE `reg_thread`.`id_thread`='.$id_thread.' AND `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0;';
*/
//Выбираем данные - оттуда

$header_array["way_name"]=$way_name;

$sql_1='SELECT `reg_town`.`town_name`, `reg_town`.`town_alias`,  `reg_station`.`station_name`, `reg_station`.`station_code`, `reg_station`.`station_alias`, `region`.`region_name`, `country`.`country_name`, `district`.`district_name`, `reg_stoppoint`.`a_shift` FROM `reg_thread` JOIN `reg_stoppoint` ON `reg_thread`.`id_thread`=`reg_stoppoint`.`id_thread` JOIN `reg_station` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town` LEFT JOIN `district` ON `reg_town`.`id_district`=`district`.`id_district` LEFT JOIN `region`  ON `reg_town`.`id_region`=`region`.`id_region` LEFT JOIN `country` ON `reg_town`.`id_country`=`country`.`id_country`  WHERE `reg_thread`.`id_thread`='.$id_thread.' AND `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0;';

$result=mysql_query($sql_1);

while($row = mysql_fetch_array($result)){
 $header_array["country_from"]=$row['country_name'];
 $header_array["region_from"]=$row['region_name'];
 $header_array["district_from"]=$row['district_name'];
 $header_array["town_alias_from"]=$row['town_alias'];
 $header_array["town_from"]=$row['town_name'];
 $header_array["station_from"]=$row['station_name'];
 $header_array["station_code_from"]=$row['station_code'];
 $header_array["station_alias_from"]=$row['station_alias'];
}

$sql_2=str_replace('`a_shift`=0','`a_shift`!=0',$sql_1);
//echo $sql_2; die();

$result=mysql_query($sql_2);

while($row = mysql_fetch_array($result)){
 $header_array["country_to"]=$row['country_name'];
 $header_array["region_to"]=$row['region_name'];
 $header_array["district_to"]=$row['district_name'];
 $header_array["town_to"]=$row['town_name'];
 $header_array["town_alias_to"]=$row['town_alias'];
 $header_array["station_to"]=$row['station_name'];
 $header_array["station_code_to"]=$row['station_code'];
 $header_array["station_alias_to"]=$row['station_alias'];
 $a_shift=$row['a_shift'];
}

//Конечная цена билета
$sql_3='SELECT `reg_price`.`price`, `currency`.`currency_name` FROM  `reg_thread` JOIN `reg_fare` ON `reg_fare`.`id_fare`=`reg_thread`.`id_fare` JOIN `reg_price` ON `reg_price`.`id_fare`=`reg_fare`.`id_fare` JOIN `currency` ON `reg_price`.`id_currency`=`currency`.`id_currency` WHERE `reg_thread`.`id_thread`='.$id_thread.';';
$result=mysql_query($sql_3);
if($row = mysql_fetch_array($result)){
$price=$row['price'];
$currency=$row['currency_name'];
}

//Графики движения нитки
$sql_4='SELECT `reg_shedule`.`days`, `reg_shedule`.`times`, `reg_shedule`.`days`, `reg_shedule`.`shedule_end_date`, `reg_shedule`.`shedule_start_date` FROM `reg_thread` JOIN `reg_shedule` ON  `reg_thread`.`id_thread`=`reg_shedule`.`id_thread` WHERE `reg_thread`.`id_thread`='.$id_thread.';';
$result=mysql_query($sql_4);

while($row = mysql_fetch_array($result)){
$days_str=dateArrayBuilder(date("Y-m-d"), $row['shedule_end_date'], $row['days']);
$days_str=explode('#',$days_str);
$times_array=explode(':',$row['times']);

		foreach($days_str as $date){
			
			$dates_array=explode('-',$date);
			$start_date_unix=mktime($times_array[0], $times_array[1], 0, $dates_array[1], $dates_array[2], $dates_array[0]); 
			$finish_date=explode('#',date("Y-m-d#H:i", $start_date_unix+$a_shift));		
			$a_shift_string='';
						
			$daysz = floor($a_shift/24/3600);
			if($daysz>0){
					if($daysz==1){$daysz=$daysz.' сутки';}
					if($daysz>1){$daysz=$daysz.' суток';}
			$a_shift_string=$daysz;
			}
			 
			$r2=''; $r1='';
			$hoursz = floor($a_shift/3600)-$daysz*24;
			if($hoursz>0){
				$hoursz_array=str_split($hoursz);
				if(count($hoursz_array)>1){//2 разряда					
				$r2=$hoursz_array[0]; $r1=$hoursz_array[1];
				}else{//разряд					
				$r1=$hoursz_array[0];
				}
				
				if(!empty($r2)&&($r2==1)){	
				$hoursz=$hoursz.' часов';
				}else{//Если это не 11,12,..19
					if($r1==1){ $hoursz=$hoursz.' час'; }
					elseif(($r1==2)OR($r1==3)OR($r1==4)){ $hoursz=$hoursz.' часа'; }
					else{ $hoursz=$hoursz.' часов'; }
				}
			$a_shift_string=$a_shift_string.' '.$hoursz;
			}
			
			$minutesz=floor($a_shift/60-$hoursz*60-$daysz*24*60);			
			$r2=''; $r1='';
			if($minutesz>0){
				$minutesz_array=str_split($minutesz);
				if(count($minutesz_array)>1){//2 разряда					
				$r2=$minutesz_array[0]; $r1=$minutesz_array[1];
				}else{//разряд					
				$r1=$minutesz_array[0];
				}
				
				if(!empty($r2)&&($r2==1)){
				$minutesz=$minutesz.' минут';
				}else{//Если это не 11,12,..19
					if($r1==1){ $minutesz=$minutesz.' минута'; }
					elseif(($r1==2)OR($r1==3)OR($r1==4)){ $minutesz=$minutesz.' минуты'; }
					else{ $minutesz=$minutesz.' минут'; }
				}
			$a_shift_string=$a_shift_string.' '.$minutesz;				
			}
			
			
			
			$tr_array=array(
			"time"=>$row['times'],
			"time_shift"=>$a_shift_string,			
			"date_arrive"=>$finish_date[0],
			"time_arrive"=>$finish_date[1],
			"price"=>$price,
			"curency"=>$currency,
			);
			
		$date_array[$date]=$tr_array;
		
		}
		
		
//echo $days_str; die();

//$row['times'];
}
ksort($date_array);

}

//Список всех городов России с интересной популяцией
$sql_0='SELECT `reg_town`.`town_name`, `reg_town`.`town_alias` FROM `reg_town` JOIN `reg_town_rating` ON `reg_town`.`town_code`=`reg_town_rating`.`town_code` ORDER BY `reg_town`.`town_name` ASC;';
$result=mysql_query($sql_0);
while($row = mysql_fetch_array($result)){
/*$price=$row['price'];
$currency=$row['currency_name'];*/
$town_array[$row['town_alias']]=$row['town_name'];
}

//echo '<pre>'; print_r($town_array); die();
$town_array_w=array_chunk($town_array, ceil(count($town_array)/NUM_ROWS), TRUE);
?><div class="row"><?
foreach($town_array_w as $town_array_small){
	?>
	<div class="col-md-3">		
		<?		
		foreach($town_array_small as $town_alias=>$town_name){
			if(($town_alias!='moscow')&&($town_alias!='moscow_1')){
					?>
					<h5><span class="glyphicon glyphicon-map-marker"></span> <a href="/<?=$town_alias;?>/"  title="<?=$town_name;?> расписание автобусов"><?=$town_name;?></a></h5>
					<?
			}
		}
		?>		
	</div>
	<?
}
?></div><?
/*print_r($header_array); print_r($date_array);*/


/*
$sql='SELECT `reg_thread`.`thread_name`, `reg_town`.`town_name` FROM `reg_thread` JOIN `reg_stoppoint` ON `reg_thread`.`id_thread`=`reg_stoppoint`.`id_thread` JOIN `reg_station` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town` 
JOIN `reg_fare` ON `reg_thread`.`id_fare`=`reg_fare`.`id_fare` WHERE `reg_town`.`id_town`=44 AND `reg_stoppoint`.`d_shift`=0;';
*/

if(($header_array['country_from']==$header_array['country_to'])||(empty($header_array['country_from']))||(empty($header_array['country_to']))){$show_country='F';}else{$show_country='T';}
?>
<style>
.table-striped th{text-align:left;}
</style>
<table class="table table-striped">
	<thead>
	<tr>
		<th><u>Отправление</u><br/>
		<h3><a href="/<?=$header_array['town_alias_from'];?>/terminus/<?=$header_array['station_alias_from'];?>/" title="Расписание автобусов автостанция <?=$header_array['station_from'];?>"><?=$header_array['station_from'];?></a></h3>
		<h4><a href="/<?=$header_array['town_alias_from'];?>/"><?=$header_array['town_from'];?></a></h4>
		<?if(!empty($header_array['district_from'])){?><span class="hidden-xs"><?=$header_array['district_from'];?></span><?}?>
		<?if(!empty($header_array['region_from'])){?><span class="hidden-xs"><?=$header_array['region_from'];?></span><?}?>
		<?if($show_country=='T'){echo $header_array['country_from'].'<br/>';}?>
		</th>
		<th>В пути</th>
		<th><u>Прибытие</u><br/>
	<? /*	<h3><a href="/<?=$header_array['town_alias_to'];?>/terminus/<?=$header_array['station_alias_to'];?>/" title="Расписание автобусов автостанция <?=$header_array['station_from'];?>"><?=$header_array['station_to'];?></a></h3> */?>
		<h3><?=$header_array['station_to'];?></h3>
		<h4>
		<?if(array_key_exists($header_array['town_alias_to'], $town_array)){?>
		<a href="/<?=$header_array['town_alias_to'];?>/" title="Расписание автобусов <?=$header_array['town_to'];?>"><?=$header_array['town_to'];?></a><?}else{?><?=$header_array['town_to'];?><?}?></h4>
		<?if(!empty($header_array['district_to'])){?><span class="hidden-xs"><?=$header_array['district_to'];?></span><?}?>
		<?if(!empty($header_array['region_to'])){?><span class="hidden-xs"><?=$header_array['region_to'];?></span><?}?>
		<?if($show_country=='T'){echo $header_array['country_to'].'<br/>';}?>		
		</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
		<?foreach($date_array as $date=>$data_array){
		//вычичление ссылки на билет

		
		$unitiki_api = new Unitikiapi();
		$ride_list = $unitiki_api->ride_list(array(
		'station_id_start' => $header_array['station_code_from'],
		'station_id_end'  => $header_array['station_code_to'],
		'show_similar' => 0,
		));
		
		//echo '<pre>@'; print_r($ride_list); die();
		
		?><tr>
		<td><span class="lead"><?=$data_array['time'];?></span><br/><small><?=dateTransformer($date);?></small></td>
		<td><?=$data_array['time_shift'];?></td>
		<td><span class="lead"><?=$data_array['time_arrive'];?></span><br/><small><?=dateTransformer($data_array['date_arrive']);?></small></td>
		<td><a href="#" class="btn btn-primary btn-default" role="button">Купить билет<br/><?=$data_array['price'];?><?=$data_array['curency'];?></a></td>			
		</tr><?}?>		
	</tbody>
</table>
<?
function dateArrayBuilder($start,$stop,$day){
$day=str_replace('7','0',$day);
$days_array=str_split($day);

	$dateStart = explode('-',$start);										//парсим данные начала
	$dateStop = explode('-',$stop);											//и окончания

	$start = strtotime($dateStart[2].'-'.$dateStart[1].'-'.$dateStart[0]);	//UNIX формат начала
	$stop = strtotime($dateStop[2].'-'.$dateStop[1].'-'.$dateStop[0]);		//и окончания

	$dp = $stop-$start; 													//разница в секундах
	$dp /= 86400; 															//разница в количестве дней
	$arraystr=array();
	for ($i = 0; $i<=$dp; $i++){
		
		//if (date("w",$start+$i*86400) == $day){
		$needle=date("w",$start+$i*86400);
		if(in_array($needle, $days_array)){					
			//$arraystr .= date('Y-m-d', $start+$i*86400).'#'; 				//и в строчку
			$arraystr[]=date('Y-m-d', $start+$i*86400);
		}
		
	}
	 $arraystr=implode('#',$arraystr);
	return $arraystr;														//выдаем строку

}

function dateTransformer($date){
$moth_list=array(
"01"=>"января",
"02"=>"февраля",
"03"=>"марта",
"04"=>"апреля",
"05"=>"мая",
"06"=>"июня",
"07"=>"июля",
"08"=>"августа",
"09"=>"сентября",
"10"=>"октября",
"11"=>"ноября",
"12"=>"декабря"
);
$date_array=explode('-',$date);
$result_string=$date_array[2].' '.$moth_list[$date_array[1]].' '.$date_array[0];
return $result_string;
}

function threadNameCorrector($thread_name,$town_name){
//echo '22';
$str_name=explode(' - ',$thread_name);
//echo '<pre>23'; print_r($str_name); die();

	if($str_name[0]!=$town_name){
	//$thread_name=$town_name.' <small>('.$str_name[0].')</small> - '.$str_name[1];
	$thread_name=$town_name.' - '.$str_name[1];
	}
return $thread_name;
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
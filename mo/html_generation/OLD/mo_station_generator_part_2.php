<?
ini_set("display_errors", 1); 
error_reporting(E_ALL ^ E_NOTICE); 

session_start();
if(($_SESSION["parse"]['step_5']!='OK')OR(empty($_SESSION["parse"]['town_alias']))OR(empty($_SESSION["parse"]['id_town']))){   $_SESSION["fail_step"]='step_5'; }
	
$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define("MAIN_PATH", '/home/bitrix/www/mods/bus/town/');
$direction=array(0=>'f',1=>'b');
?>
<?
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
//Формируем массив про все автобусы
$sql="SELECT * FROM `bus` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$bus_info_array[$row['id_bus']]['alias']=html_entity_decode($row['alias']);
$bus_info_array[$row['id_bus']]['f_way']=html_entity_decode($row['f_way']);
$bus_info_array[$row['id_bus']]['bus_name']=html_entity_decode($row['bus_name']);
}

//Собираем массив всех существующих рейсов
$sql="SELECT * FROM `race_type`;";
$result=mysql_query($sql);
$all_race_type_array=array();
while ($row = mysql_fetch_array($result)){
$all_race_type_array[$row['id_race_type']]=$row['human_value'];
}

//Проверяем на существование директорию
$dir1=MAIN_PATH.$town.'/';

if(!is_dir($dir1)){ //Если нет папки "/mods/bus/town/".$town.'/'
echo 'Folder does not exists: '.$dir1; die();
}

//Директория для статики
$dir2=MAIN_PATH.$town.'/html/';
if(!is_dir($dir2)){ mkdir($dir2, 0777); chmod($dir2, 0777); }

//Поддиретория для статики - остановки
$dir3=MAIN_PATH.$town.'/html/station/';
if(!is_dir($dir3)){ mkdir($dir3, 0777); chmod($dir3, 0777); }

//Собираем массив остановок Москвы
$sql="SELECT * FROM `station` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$prop_station_array=array(
"station_name"=>$row['station_name'],
"station_alias"=>$row['alias'],
);
$all_stations_array[$row['id_station']]=$prop_station_array;
}

//echo '<pre>'; print_r($all_stations_array); die();
$z=0;
foreach($all_stations_array as $station_id=>$prop_array){

//Получаем данные об остановке
$station_alias=$prop_array['station_alias'];
$station_name=$prop_array['station_name'];

//Бежим по 2 направлениям - вперед/назад
	foreach($direction as $direction_id=>$direction_value){
	$table_id=$direction_id+1;
	$race_types_array=array();
	$super_array=array();
	//Собираем все останвки автобуса в данном городе:
	
	$sql="SELECT * FROM `time` WHERE time<>'#' AND id_town='".TOWN_ID."' AND id_station='".$station_id."' AND race_direction='".$direction_id."';";		
	$result=mysql_query($sql);
	//echo $sql; die();
	$check=mysql_num_rows($result);
	while($row = mysql_fetch_array($result)){	
	//Формирование конечного массива
	$super_array[$row['id_bus']][]=array(
	"TIME"=>$row['time'],	
	"BUS"=>$row['id_bus'],
	"SCHEDULE"=>$row['id_race_type'],
	"PAYMENT_TYPE"=>$row['payment_type'],
	"RACE_NUMBER"=>$row['race_number'],
	"STATION"=>$row['id_station'],	
	);
	$race_types_array[]=$row['id_race_type'];
	}

//echo '<pre>'; print_r($super_array); die();

	//формирование x-координат
	$x_coord=array();	
	//$max_arr=array();
	foreach($super_array as $bus=>$races){
	$x_coord[]=$bus;
	//$max_arr[]=count($races);
	}

//echo '<pre>'; print_r($super_array); die();

	//Формирование y-координат
	$y_coord=array();
	$races=end($super_array);
	$y_coord=array(-1=>"График / Автобус");
	foreach($races as $race){	
	$y_coord[]='@';
	}
	//$max_tr='';
	//$max_tr=max($max_arr);
	$max_tr=1;
	

/*
	echo '<pre>'; print_r($y_coord);
	echo '<pre>'; print_r($x_coord); 
	echo '<pre>'; print_r($bus_info_array); 
	die();

*/
//echo '<pre>'; print_r($x_coord); die();


/*

if(($check>0)&&(count($super_array)>0)){//Если по данному направлению есть автобусы


ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
	
	
	//Выводим заголовок
	if($direction_id==0){
		?><h2>Расписание автобусов на остановке <?=$station_name;?> <small>(от вокзала) <a href="#br" title='<?=$station_name;?> - к вокзалу'><span class="glyphicon glyphicon-arrow-right"></span> (к вокзалу)</a></small></h2><a name="fr"></a><?
	}
	else{
		?><h2>Расписание автобусов на остановке <?=$station_name;?> <small>(к вокзалу) <a href="#fr" title='<?=$station_name;?> - от вокзала'><span class="glyphicon glyphicon-arrow-right"></span> (от вокзала)</a></small></h2><a name="br"></a><?
	}

	//отрисовываем таблицу
	?><table  class="table table-bordered" id="table<?=$table_id;?>"><?

	for($ykey=-1; $ykey<$max_tr; $ykey++){
	
		if($ykey==-1){ ?><thead><? }
	?><tr><?
	
		foreach($x_coord as $xkey=>$xvalue){	
		$hak=$x_coord[$xkey];		
		$td_class='';
		
		//Собираем данные об автобусе				
		$bus_f_way=$bus_info_array[$xvalue]['f_way'];
		$bus_alias=$bus_info_array[$xvalue]['alias'];
		$bus_str_name=$bus_info_array[$xvalue]['bus_name'];		

				
			if($ykey==-1){ ?><th class="sh_th_<?=$bus_alias?>"><? } //Отрисовываем шапку
			else{?><td><?}
			
			
			

			if($xkey!=-1){			
				if($ykey==-1){ //Выводим x-координаты (остановки)								
				?><a href="/<?=$town?>/bus/<?=$bus_alias?>/" class="stations_bus_href" title='Расписание автобуса № <?=$bus_str_name.' - '.$bus_f_way?>'><?=$bus_str_name;?></a><?
				}	
			}
			
			if($ykey==0){
			?><ul class="list-unstyled sh_ul"><?
				foreach($super_array[$xvalue] as $y_key=>$time_array){
						
						if(!empty($super_array[$hak][$y_key]['SCHEDULE'])){
							$li_class='sh'.$super_array[$hak][$y_key]['SCHEDULE'];
							if($super_array[$hak][$y_key]['PAYMENT_TYPE']==1){$li_class=$li_class.' '.'pay';}
						}
						if(!empty($li_class)){?><li class="<?=$li_class;?>"><?} else {?><li><?}
						echo $super_array[$hak][$y_key]['TIME'];
				
				
				
			   ?></li><?
				}?>
			</ul>
			<?
			}
				
			if($ykey==-1){?></th><?}//Отрисовываем шапку
			else{?></td><?}		
		}
	?></tr><?
	if($ykey==-1){ ?></thead><? }
	}
	?></table><?

}

*/


//if($z==5){	die('####');  }
	
	//Поддиретория статики - направление движения
	$dir4=MAIN_PATH.$town.'/html/station/'.$direction_value.'/';
	if(!is_dir($dir4)){ mkdir($dir4, 0777); chmod($dir4, 0777);}

	/*
	$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
	ob_end_clean(); 
	
	$file_src=MAIN_PATH.$town.'/html/station/'.$direction_value.'/'.$station_alias.'.html';
	$file_src=fopen($file_src, "w+" );
	fwrite($file_src, $buffer);
	fclose($file_src);
	
	*/

	//Генерим панельку для селектора

	
if(($check>0)&&(count($super_array)>0)&&(count($x_coord)>1)){//Если по данному направлению есть автобусы

ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?><button type="button" class="btn btn-success show-all-columns" data-targettable="table<?=$table_id;?>">Показать все</button><?
	foreach($x_coord as $bus_number){
		?><button type="button" class="btn btn-default show-hide-column" data-toggle="button" data-targettable="table<?=$table_id;?>" data-busnumber="<?=$bus_info_array[$bus_number]['alias'];?>"><?=$bus_info_array[$bus_number]['bus_name'];?></button><?	
	}
$buffer2=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 
$file_src2=MAIN_PATH.$town.'/html/station/'.$direction_value.'/'.$station_alias.'_sh_selector.html';
$file_src2=fopen($file_src2, "w+" );
fwrite($file_src2, $buffer2);
fclose($file_src2);
}




$race_types_array=array_unique($race_types_array);

	if( (count($race_types_array)>0) && ( (count($race_types_array)!=1) || (!in_array('1',$race_types_array)) ) ){
	ob_start();                     // Включаем буферизацию вывода
	ob_clean();                     // Чистим буфер (не обязательно)

		
			foreach($race_types_array as $id_type){
			?><span class="label label-sh sh<?=$id_type;?>"><?=$all_race_type_array[$id_type];?></span><?
			}
	$buffer3=ob_get_contents();      // Пишем в переменную содержимое буфера
	ob_end_clean(); 
	$file_src3=MAIN_PATH.$town.'/html/station/'.$direction_value.'/'.$station_alias.'_sh_legend.html';
	$file_src3=fopen($file_src3, "w+" );
	fwrite($file_src3, $buffer3);
	fclose($file_src3);
	}


	}//Конец обхода по всем направлениям туда-сюда
//echo $z.'|'; 
$z++;
}//Бежим по всем остановкам
?>
<?die('TXT');?>
<?
$_SESSION["parse"]["step_7"]="OK";
$_SESSION["parse"]["current_step"]="step_7";
$_SESSION["parse"]["id_town"]=TOWN_ID;
$_SESSION["parse"]["town_alias"]=$town;
echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?>
<!-- #  # -->
 <script language="javascript">window.location.href = '<?=$target_href;?>';</script>
<!---->
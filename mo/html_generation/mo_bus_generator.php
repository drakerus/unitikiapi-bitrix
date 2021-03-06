<?
session_start();
if(($_SESSION["parse"]['step_4']!='OK')OR(empty($_SESSION["parse"]['town_alias']))OR(empty($_SESSION["parse"]['id_town']))){  $_SESSION["fail_step"]='step_4'; }

set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
///define('TOWN_ID', 1);
define("MAIN_PATH", $_SERVER["DOCUMENT_ROOT"].'/mods/bus/town/');
$direction=array(0=>'f',1=>'b');
define('TABLE', 'station');
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

//Собираем массив существующих уже автобусов в Москве
$sql="SELECT * FROM `bus` WHERE id_town='".TOWN_ID."';";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
//$bus_db_array[html_entity_decode($row['alias'])]=$row['id_bus'];
$prop_bus_array=array(
"bus_number"=>$row['bus_name'],
"bus_alias"=>$row['alias'],
"bus_way"=>$row['f_way'],
"bus_backway"=>$row['b_way'],
);
$all_buses_array[$row['id_bus']]=$prop_bus_array;

}

//Собираем массив всех существующих рейсов
$sql="SELECT * FROM `race_type`;";
$result=mysql_query($sql);
$race_type_array=array();
$race_type_array=array(-1=>"График / Остановка");
while ($row = mysql_fetch_array($result)){
$race_type_array[$row['id_race_type']]=$row['human_value'];
}

foreach($all_buses_array as $bus_id=>$bus_prop_array){
	
	$bus_number=$bus_prop_array['bus_number'];
	$bus_alias=$bus_prop_array['bus_alias'];	
	$bus_way=$bus_prop_array['bus_way'];
	$bus_backway=$bus_prop_array['bus_backway'];

	//Бежим по 2 направлениям - вперед/назад

		foreach($direction as $direction_id=>$direction_value){

		$super_array=array();
		$table_id=$direction_id+1;
		
		//Собираем все останвки автобуса в данном городе:	
		$sql="SELECT * FROM `time` WHERE id_town='".TOWN_ID."' AND id_bus='".$bus_id."' AND race_direction='".$direction_id."'  ORDER BY id ASC;";
		
		/*
		echo $sql;
		die();
			*/	
		$result=mysql_query($sql);
		$chek=0;
		
		while($row=mysql_fetch_array($result)){		
			$super_array[$row['id_station']][]=array(
			"TIME"=>$row['time'],
			"BUS"=>$row['id_bus'],
			"SCHEDULE"=>$row['id_race_type'],
			"PAYMENT_TYPE"=>$row['payment_type'],
			"RACE_NUMBER"=>$row['race_number'],
			);
			$chek++;		
		}		
		//echo '<pre>'; print_r($super_array); die();
		
		if($chek>0){

		//формирование x-координат
		$x_coord=array(-1=>"x_start");
		foreach($super_array as $station=>$races){
		$x_coord[]=$station;
		}

		//Формирование y-координат
		$races=end($super_array);
		$y_coord=array(-1=>-1);
		foreach($races as $race){		
		$y_coord[]=$race['SCHEDULE'];
		$payment_arr[]=$race['PAYMENT_TYPE'];
		}
			
		/*
		echo '<pre>'; print_r($y_coord);		
		echo '<pre>'; print_r($x_coord);
		die();		
		*/

		//Выводим заголовок
		ob_start();                     // Включаем буферизацию вывода
		ob_clean();                     // Чистим буфер (не обязательно)


		if($direction_id==0){//рейс прямой
		?><h2>Расписание автобуса №<?=$bus_number;?>: <?=$bus_way;?> <small>(от вокзала) <?if(!empty($bus_backway)){?><a href="#br" title='<?=$bus_backway;?>'><span class="glyphicon glyphicon-arrow-right"></span> (к вокзалу)</a><?}?></small></h2><a name="fr"></a><?
		} 
		else{//к вокзалу
		?><h2>Расписание автобуса №<?=$bus_number;?>: <?=$bus_backway;?> <small>(к вокзалу) <?if(!empty($bus_way)){?><a href="#fr" title='<?=$bus_way;?>'><span class="glyphicon glyphicon-arrow-right"></span> (от вокзала)</a><?}?></small></h2><a name="br"></a><?		
		}

		//отрисовываем таблицу
		?><table  class="table table-bordered" id="table<?=$table_id;?>"><?
		
		//echo '<pre>'; print_r($y_coord);die();
		foreach($y_coord as $ykey=>$yvalue){

		if($ykey==-1){ ?><thead><? }		
		
		$tr_class='';
		if(!empty($yvalue)){
		$tr_class='sh'.$yvalue;		
			if($payment_arr[$ykey]==1){$tr_class=$tr_class.' '.'pay';}
		}
		$tr_class=$tr_class.' columnIsVisible';
		
/*		?><tr class="<?=$tr_class?>"><? */
		if($ykey==-1){?><tr><?}
		else{?><tr class="<?=$tr_class?>"><? }
		
			foreach($x_coord as $xkey=>$xvalue){
			
			
				if($ykey==-1){
				$station=html_entity_decode(get_name_by_id($xvalue));
				$alias=transletiration($station);
				$th_class='sh_th_'.$alias;
				?><th class="<?=$th_class;?>"><? } //Отрисовываем шапку
				else{ 	?><td><? }
			
				if($xkey==-1){//Выводим y-координаты (рейсы)		
				?><noindex><?=$race_type_array[$yvalue];?></noindex><?
				}
				else{					
					if($ykey==-1){ //Выводим x-координаты (остановки)					
					?><a href='/<?=$town;?>/station/<?=$alias;?>/' title='Расписание автобусов на остановке <?=$station;?>'><?=$station;?></a><?					
					}				
				}			
				if(!empty($super_array[$xvalue][$ykey]['TIME'])){ $super_time=str_replace('#', '&nbsp;', $super_array[$xvalue][$ykey]['TIME']); echo $super_time; }	//Выводим время			
				if($ykey==-1){ ?></th><? }//Отрисовываем шапку
				else{	?></td><?	}			
			}
		?></tr><?
		if($ykey==-1){?></thead><?}
		}
		?></table><?
		
		
		//Проверяем на существование директорию
		$dir1=MAIN_PATH.$town.'/';

		if(!is_dir($dir1)){ //Если нет папки "/mods/bus/new/town/".$town.'/'
		echo 'Folder does not exists: '.$dir1; die();
		}

		//Директория для статики
		$dir2=MAIN_PATH.$town.'/html/';
		if(!is_dir($dir2)){ mkdir($dir2, 0777, true); chmod($dir2, 0777); }

		//Поддиретория для статики
		$dir3=MAIN_PATH.$town.'/html/bus/';
		if(!is_dir($dir3)){ mkdir($dir3, 0777, true); chmod($dir3, 0777); }

		//Поддиретория статики - направление движения
		$dir4=MAIN_PATH.$town.'/html/bus/'.$direction_value.'/';
		if(!is_dir($dir4)){ mkdir($dir4, 0777, true); chmod($dir4, 0777); }
		/*
		else{ //Если такая папка уже была - неплохо бы ее почистить!
			if($handle = opendir($dir3)){
				while(false !== ($file = readdir($handle)))
						if($file != "." && $file != "..") unlink($dir3.$file);
				closedir($handle);
			}
		}
		*/

		$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
		ob_end_clean();   

		$file_src=MAIN_PATH.$town.'/html/bus/'.$direction_value.'/'.$bus_alias.'.html';
		$file_src=fopen($file_src, "w+" );
		fwrite($file_src, $buffer);
		fclose($file_src);


		}//Если нет обратного направления

		
		
		//Генерим панельку для селектора

		if((count($super_array)>0)&&(count($x_coord)>1)){//Если по данному направлению есть автобусы

//		die('DI');
		
		ob_start();                     // Включаем буферизацию вывода
		ob_clean();                     // Чистим буфер (не обязательно)
		?><button type="button" class="btn btn-success show-all-columns" data-targettable="table<?=$table_id;?>">Показать все</button><?
			foreach($x_coord as $xxx_key=>$station_name){
				if($xxx_key>=0){
				$station=html_entity_decode(get_name_by_id($station_name));
				$alias=transletiration($station);
				?><button type="button" class="btn btn-default show-hide-column" data-toggle="button" data-targettable="table<?=$table_id;?>" data-busnumber="<?=$alias;?>"><?=$station;?></button><?	
				}
			}
		$buffer2=ob_get_contents();      // Пишем в переменную содержимое буфера
		ob_end_clean(); 
		$file_src2=MAIN_PATH.$town.'/html/bus/'.$direction_value.'/'.$bus_alias.'_sh_selector.html';
		$file_src2=fopen($file_src2, "w+" );
		fwrite($file_src2, $buffer2);
		fclose($file_src2);
		}
	
		
		}//Конец обхода по всем направлениям туда-сюда

}
?>
<?
function get_name_by_id($id){
	$sql='SELECT * FROM `station` WHERE id_station="'.$id.'";';
	$result=mysql_query($sql);
	if($row = mysql_fetch_array($result)){
	return $row['station_name'];
	}
}
?>
<?
$_SESSION["parse"]["step_5"]="OK";
$_SESSION["parse"]["current_step"]="step_5";
echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?>
<script language="javascript">window.location.href = '<?=$target_href;?>';</script>

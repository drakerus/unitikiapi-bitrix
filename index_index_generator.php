<?
error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 1); 
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define("MAIN_PATH", '/home/bitrix/www/sitemaps/');
$direction=array(0=>'f',1=>'b');

$town_array=array();
//Собираем массив городов
$sql="SELECT * FROM `town` WHERE `active`=1 ORDER BY `town_name` ASC;";//Все активные города кроме Москвы
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
	$town_array[$row['town_alias']]=html_entity_decode($row['town_name']);
}


//Список всех городов России с интересной популяцией имеющие вокзалы ( WHERE `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 )
$sql_0='SELECT `reg_town`.`town_name`, `reg_town`.`town_alias` FROM `reg_town` JOIN `reg_town_rating` ON `reg_town`.`town_code`=`reg_town_rating`.`town_code` JOIN `reg_station` ON `reg_station`.`id_town`=`reg_town`.`id_town` JOIN `reg_stoppoint` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station` WHERE `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 GROUP BY  `reg_town`.`id_town` ORDER BY `reg_town`.`town_name`  ASC;';
$result=mysql_query($sql_0);
while($row = mysql_fetch_array($result)){ $town_region_array[$row['town_alias']]=$row['town_name']; }

$all_towns_array=array_merge($town_array,$town_region_array);
//echo '<pre>'; print_r($town_array); die();
?>
<?
ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?>
<div class="row">		
	<div class="col-md-12">
		<h4>Поиск по городам</h4>			
        <div class="alert alert-station alert-block alert-danger"></div>    
		<div class="input-group">
			<span class="input-group-btn"><button class="btn btn-info" type="button"><span class="glyphicon glyphicon-arrow-right"></span></button></span>
			<input id="town_typehead" type="text" class="col-md-12 form-control" placeholder="Город..." autocomplete="off" />
		</div>	
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="search_description">
			<span class="glyphicon glyphicon-info-sign"></span>&nbsp;Воспользуйтесь быстрым поиском по городам: начните вводить название интересующего города и просто выберите его из выподающего списка	
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="moscow text-center">
			<h3><span class="glyphicon glyphicon-map-marker"></span><a href="/moscow/" title="расписание автобусов Москвы">Москва</a></h3>
		</div>
	</div>
</div>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
   <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingmobus">
      <h4>
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#mobus" aria-expanded="false" aria-controls="mobus"><span class="glyphicon glyphicon-arrow-down"></span> Расписание городских маршрутов Московской Области: <span class="glyphicon glyphicon-arrow-down"></span></a>
      </h4>
    </div>
    <div id="mobus" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingmobus">
      <div class="panel-body">
		  <div class="row">
			<?
			$town_array_w=array_chunk($town_array, ceil(count($town_array)/4), TRUE);
			foreach($town_array_w as $small_array){
			?><div class="col-md-3"><?
				foreach($small_array as $town_alias=>$town_name){
					if(($town_alias!='moscow')&&($town_alias!='perm')){			
					?><h6><span class="glyphicon glyphicon-map-marker"></span><a href="/<?=$town_alias;?>/" title="<?=$town_name;?> расписание автобусов"><?=$town_name;?></a></h6><?			
					}
				}
			?></div><?
			}
			?>
		</div>
      </div>
    </div>
  </div>
</div>
<?
	$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
	ob_end_clean(); 
	$file_src=$_SERVER["DOCUMENT_ROOT"].'/index_body.php';
	$chmod_src=$file_src;
	//echo $file_src; die();
	$file_src=fopen($file_src, "w+" );
	if(fwrite($file_src, $buffer)){	fclose($file_src);	chmod($chmod_src,0777);
	}
?>
<?
ob_start();                     // Включаем буферизацию вывода
ob_clean();                     // Чистим буфер (не обязательно)
?>
<script>
$(function(){
     function displayResultTown(item){
					if(item.text!='Result not Found'){
					var prefix='http://mybuses.ru/';							
					var postfixxx='/';					
					var src=prefix+item.value+postfixxx;
					window.open(src, "_self");					
					}
					else{
					$('.alert-station').show().html('Увы ничего не найдено, попытайтесь снова');
					}
                }
				$('#town_typehead').typeahead({
                    source: [					
					<?foreach($all_towns_array as $key=>$value){?>
					 {id: '<?=$key;?>', name: '<?=$value;?>'},
					<?}?>                       
                    ],					
                    onSelect: displayResultTown
                });              
        });
</script>
<?
$buffer=ob_get_contents();      // Пишем в переменную содержимое буфера
ob_end_clean(); 
$file_srcxx=$_SERVER["DOCUMENT_ROOT"].'/search_script.html';
//echo $file_srcxx;
$file_srcxx=fopen($file_srcxx, "w+" );
if(fwrite($file_srcxx, $buffer)){
fclose($file_srcxx);
//echo 'INDEX GENETATED';
}
?>
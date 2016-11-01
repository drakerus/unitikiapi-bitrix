<?
ini_set('error_reporting', E_ALL ^ E_NOTICE);
error_reporting(E_ALL ^ E_NOTICE);

session_start();
if(($_SESSION["parse"]['step_2']!='OK')OR(empty($_SESSION["parse"]['town_alias']))OR(empty($_SESSION["parse"]['id_town']))){  $_SESSION["fail_step"]='step_2'; }

//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
//define('SAVE_PATH', '/home/bitrix/www/mods/bus/town/moscow/array/');
//require ($_SERVER["DOCUMENT_ROOT"].'/mods/phpQuery/phpQuery.php');
$dr=$_SERVER['DOCUMENT_ROOT'];
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

//Создаем папки для размещения скачанного HTML

//Папка города
$dir1=$dr."/mods/bus/town/".TOWN_ALIAS.'/';

if(!is_dir($dir1)){ //Если нет папки "/mods/bus/new/town/".$town.'/'
if(!mkdir($dir1)){ die('Не удалось создать папку '.$dir1); }
}

//Папка для хранения источных html
$dir2=$dr."/mods/bus/town/".TOWN_ALIAS."/source/";

if(!is_dir($dir2)){ //Если нет папки /mods/bus/new/town/".$town."/source/"
mkdir($dir2);
}
else{//Если она есть - чистим!
	if($handle = opendir($dir2)){
        while(false !== ($file = readdir($handle)))
                if($file != "." && $file != "..") unlink($dir2.$file);
        closedir($handle);
	}
}


//Бежим по всем автобусам:
/*
$arFilter = Array("IBLOCK_ID"=>14, "PROPERTY_TOWN" => $town_id );	
	$res = CIBlockElement::GetList(Array("SORT"=>"ASC"), $arFilter);
	$p=0;

while($ar_fields = $res->GetNext()){

*/

//Бежим по всем автобусам:

$sql="SELECT * FROM `bus` WHERE `id_town` = '".TOWN_ID."';";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
	//Вычисляем ссылку на источник	
	/*$res_prop = CIBlockElement::GetProperty(14, $ar_fields['ID'], "sort", "asc", array("CODE" => "URL"));
	if($ob_prop = $res_prop->GetNext()){	$sourse_href = 'http://navi.mostransavto.ru/'.$ob_prop['VALUE']; }*/
	$url_list[$row['id_bus']]='http://navi.mostransavto.ru/'.$row['href'];
}


//echo '<pre>'; print_r($url_list); die();


foreach($url_list as $file_name=>$src_bus_mostransauto){
usleep(500);

	$curl = curl_init();
    curl_setopt($curl,CURLOPT_URL, $src_bus_mostransauto);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    $contents = curl_exec($curl);
    curl_close($curl);

ini_set('pcre.backtrack_limit', '5000000');
if (preg_match('|<h4>(.*)</h4>|sei', $contents, $arr)){ $title = $arr[1];   $q++; }
$title = explode('<i>', $title);  
preg_match_all("/[0-9]{1,}/ ", $title[0], $result);
//echo $result[0][0].'<br/>';

$contents = str_replace("</td></tr><br>", "</td></tr></table><br>", $contents) ;
$src_src=$dr.'/mods/bus/town/'.TOWN_ALIAS.'/source/'.$file_name.'.html';

	if ( !$handle = fopen($src_src, 'w+') ) {       echo "Не могу открыть файл ($src_src)";        exit;   }
		if (fwrite($handle, $contents) === FALSE) {
				echo "Не могу произвести запись в файл ($src_src)";
				exit;
	}
}

$_SESSION["parse"]["step_3"]="OK";
$_SESSION["parse"]["current_step"]="step_3";
echo 'OK';
$target_href='http://mybuses.ru/mods/bus/mo/conctructor.php';
?>
<script language="javascript">window.location.href = '<?=$target_href;?>';</script>
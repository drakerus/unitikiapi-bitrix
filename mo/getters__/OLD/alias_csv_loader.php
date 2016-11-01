<?
$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");

$sql="SELECT * FROM `town`;";
$result=mysql_query($sql);
while($row = mysql_fetch_array($result)){
$town_array[$row['town_name']]=$row['id_town'];
}

//echo '<pre>'; print_r($town_array)

if (($handle = fopen("alias.csv", "r")) !== FALSE) {
$i=0;
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
	$bad_name=$data[0];
	$good_name=$data[1];	
	$town_name=substr($data[2], 0,  strrpos($data[2], ' '));
		if(!empty($town_array[$town_name])){
		$id_town=$town_array[$town_name];
		//echo $bad_name.'-'.$good_name.'@'.$town_name.'<br/>';
		
		//$super_array[$town_name][]=
		
		$sql = "INSERT INTO alias SET bad_value='".$bad_name."', good_value='".$good_name."', id_town='".$id_town."'";
		mysql_query($sql);			
		echo '#';
		}
		else{
		echo $town_name.'@'; die();
		}
	}
}

/*
if (($handle = fopen("towns.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
	$town_name=$data[0];
	$town_alias=$data[1];
	$town_src=$data[2];	
	$sql = "INSERT INTO town SET town_name='".$town_name."', town_alias='".$town_alias."', mta_href='".$town_src."'";
	mysql_query($sql);			
	echo '#';	
	
	}
}
*/
?>
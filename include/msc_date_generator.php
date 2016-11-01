<?
$_SERVER["DOCUMENT_ROOT"]='/var/www/ph382841/data/www/mybuses.ru';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");
define('TOWN_ID', 1);
define("MAIN_PATH", $_SERVER["DOCUMENT_ROOT"].'/mods/bus/include/');
$buffer=date('Y-m-d',time());
$file_src=MAIN_PATH.'msc_date.html';
$file_src=fopen($file_src, "w+" );
fwrite($file_src, $buffer);
fclose($file_src);
?>
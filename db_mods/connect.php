<?php
if(!$myConnect = mysql_connect( $DBHost, $DBLogin, $DBPassword, true )){
    die('Не удалось подключиться к серверу БД: '.mysql_error());
}
mysql_query("SET NAMES utf8");
mysql_select_db($DBName,$myConnect);
?>
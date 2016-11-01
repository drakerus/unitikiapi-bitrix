<?php
error_reporting(E_ALL ^ E_DEPRECATED);
set_time_limit(0);
ignore_user_abort( true );
date_default_timezone_set("Europe/Moscow");

define('ROOT_PATH', '/var/www/html/mybuses/');
define('MODS', ROOT_PATH.'mods/');
define('LIB', MODS.'bus/');

require(LIB.'db_mods/dbconn.php');
require(LIB.'lib.php');
require(LIB.'/region_bus/XMLParserClass.php');

try {
    $cache = new XMLParserClass( $myConnect );
    //$cache->parseXML( ROOT_PATH.'common.xml' );
    $cache->parseXML( 'https://mybusesru:tVinskLRgA@unitiki.com/xml/common.xml' );
} catch (Exception $e){
    die($e->getMessage());
}

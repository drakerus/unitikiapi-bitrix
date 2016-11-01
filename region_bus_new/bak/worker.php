<?php
if(!stristr(php_sapi_name(), 'cli')) die();
error_reporting(E_ALL ^ E_DEPRECATED);
set_time_limit(0);
ignore_user_abort( true );
date_default_timezone_set("Europe/Moscow");
ini_set('short_open_tags', 'on');

define('ROOT_PATH', '/var/www/html/mybuses/');
define('MODS', ROOT_PATH.'mods/');
define('LIB', MODS.'bus/');
define('CACHE_DIR', LIB.'/town/region_cache/');
define('TARGET_DIR', LIB.'/town/region/');

define('TPL', LIB.'region_bus/templates/');
define('COLS_NUM', 4);

require(LIB.'db_mods/dbconn.php');
require(LIB.'lib.php');
require(LIB.'/region_bus/XMLParserClass.php');
require(LIB.'/region_bus/CacheClass.php');

try {
    $parser = new XMLParserClass( $myConnect );
    //$cache->parseXML( ROOT_PATH.'common.xml' );
    $parser->parseXML( 'https://mybusesru:tVinskLRgA@unitiki.com/xml/common.xml' );
    $cache = new CacheClass($myConnect);
    $cache->generateCache();
} catch (Exception $e){
    die($e->getMessage());
}

require(LIB.'/sitemap_generator.php');
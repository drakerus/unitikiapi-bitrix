<?php
error_reporting(E_ALL ^ E_DEPRECATED);
set_time_limit(0);
ignore_user_abort( true );
date_default_timezone_set("Europe/Moscow");

define('ROOT_PATH', '/home/admin/web/mybuses.ru/public_html/');
define('MODS', ROOT_PATH.'mods/');
define('LIB', MODS.'bus/');
define('TPL', LIB.'region_bus/templates/');
define('COLS_NUM', 4);
define('CACHE_DIR', LIB.'/town/region_cache/');
define('TARGET_DIR', LIB.'/town/region/');

require(LIB.'db_mods/dbconn.php');
require(LIB.'lib.php');
require(LIB.'/region_bus/CacheClass.php');
require(LIB.'/blablacars.php');

try {
    $cache = new CacheClass($myConnect);
    $cache->generateCache();
} catch (Exception $e){
    die($e->getMessage());
}
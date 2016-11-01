<?

error_reporting(E_ALL);
define('ROOT_PATH', '/var/www/html/mybuses/');
define('MODS', ROOT_PATH.'mods/');
define('LIB', MODS.'bus/');
define('CACHE_DIR', LIB.'/town/region_cache/');
define('TARGET_DIR', LIB.'/town/region/');

exec('chown -R apache.apache '.CACHE_DIR.'*');
exec('rm -rf '.TARGET_DIR.'*; mv -f '.CACHE_DIR.'* '.TARGET_DIR);

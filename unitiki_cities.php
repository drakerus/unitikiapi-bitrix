<?php
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

if(!stristr(php_sapi_name(), 'cli')) die();

define('ROOT_PATH', '/home/admin/web/mybuses.ru/public_html/');

require_once('unitikiapi.php');
$api = new Unitikiapi();
$cities_from = $api->city_list_from();
exec('rm -f '.ROOT_PATH.'search/js/*');

foreach($cities_from as $city){
    $cities_to = $api->city_list_to( array('city_id_start' => $city->city_id) );
    if(empty($cities_to)) continue;
    ob_start();
    include('include/search_list_to_script.js.tpl.php');
    $to = ob_get_clean();
    file_put_contents(ROOT_PATH.'search/js/'.$city->city_id.'.search_list_to_script.js', $to);
    unset($cities_to);
}

ob_start();
require_once('include/search_list_from_script.js.tpl.php');
$from = ob_get_clean();
file_put_contents(ROOT_PATH.'search/js/search_list_from_script.js', $from);

exec('chown apache.apache '.ROOT_PATH.'search/js/*');

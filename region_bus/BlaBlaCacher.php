<?php
    date_default_timezone_set("Europe/Moscow");

    define('ROOT_PATH', '/home/admin/web/mybuses.ru/public_html/');
    define('MODS', ROOT_PATH.'mods/');
    define('LIB', MODS.'bus/');
    define('TPL', LIB.'region_bus/templates/');
    define('TARGET_DIR', LIB.'town/region/');

    define('BBC_TEMPLATE', 'bla_bla');

    // время жизни кеша в секундах
    define('CACHE_LIFETIME', 3600 * 24 * 3);

    foreach (array('from', 'to', 'alias', 'from_alias') as $parameter){
        if(!isset($_POST[$parameter]) || trim($_POST[$parameter]) == '') die();
    }

    if(isset($_POST['date']) && preg_match('#^[\d]{2}\-[\d]{2}\-[\d]{4}$#', $_POST['date'])){
        $_POST['from_alias'] = $_POST['from_alias'].'_'.$_POST['date'];
    } else{
        $_POST['date'] = null;
    }

    $way_dir = TARGET_DIR.$_POST['from_alias'].'/way/';

    // пробуем защититься от ddos-подобных запросов - проверяем наличие кеша для пути
    if(!file_exists($way_dir.$_POST['alias'].'.html'))  die();

    $cache_file = $way_dir.'blabla/'.$_POST['alias'].'.html';
    if(file_exists($cache_file) && time()-filemtime($cache_file) < CACHE_LIFETIME ){
        echo file_get_contents($cache_file);
        die();
    }
    @unlink($cache_file);

    require(LIB.'lib.php');
    require(LIB.'/blablacars.php');
    try {
        MBUtils::_checkDir($way_dir.'blabla/');
        $blablacars = blablacars::getInstance();
        $blabla_data = $blablacars->loadFull($_POST['from'], $_POST['to'], $_POST['date']);
        if (null === @$blabla_data->pager->total || @$blabla_data->pager->total < 1){
            file_put_contents('', $cache_file);
            die();
        }
        MBUtils::_generateFile(BBC_TEMPLATE, array(
            'total' => $blabla_data->pager->total . MBUtils::_timings($blabla_data->pager->total, 'offer'),
            'price' => $blabla_data->trips[0]->price->value,
            'duration' => MBUtils::_getTravelTime(@$blabla_data->duration),
            'town_to' => $_POST['to'],
            'town_from' => $_POST['from']
        ), $cache_file);
        echo file_get_contents($cache_file);
        die();
        
    } catch (Exception $e){
        #die('<!-- '.$e->getMessage().' -->');
        die();
    }
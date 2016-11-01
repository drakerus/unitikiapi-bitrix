<?php

/**
 * Kazan Bus Parser
 * 
 * Парсинг автобусов с сайта http://navi.kazantransport.ru/main.php#schedule
 * 
 * @version 0.2 (2016/10)
 * @author Мастер Клавы <masterklavi.ru>
 * 
 * @see заказчик: Красиков Алексей, skype: aleksey_shifaka
 */

define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'admin_mybuses2');
define('MYSQL_PASSWORD', 'VOy8TOwT2p');
define('MYSQL_DATABASE', 'admin_mybuses2');

define('CACHE_DIR', 'cache');

chdir(__DIR__);
mb_internal_encoding('utf8');
date_default_timezone_set('Etc/GMT+3');
error_reporting(-1);
ini_set('memory_limit', '1500M');
require './functions.php';


// Запросы по автобусам

print 'Автобусы'.PHP_EOL;

if ($argc !== 2)
{
    $cached = process_routes_list();
}
else
{
    $cached = cache_read($argv[1]);
}

print 'memory peak usage: '.round(memory_get_peak_usage(true)/1024/1024).'M'.PHP_EOL;


// Обработка полученных данных

$city_id = 84;

$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if ($db->connect_error)
{
    trigger_error("connect_error: {$db->connect_error}");
    exit;
}
$db->set_charset('utf8');

$time = microtime(true);

// Чистка
$result = $db->query("DELETE FROM time WHERE id_town = {$city_id}");
if (!$result)
{
    trigger_error("delete error: $db->error");
    exit;
}

$result = $db->query("DELETE FROM station WHERE id_town = {$city_id}");
if (!$result)
{
    trigger_error("delete error: $db->error");
    exit;
}

$result = $db->query("DELETE FROM bus WHERE id_town = {$city_id}");
if (!$result)
{
    trigger_error("delete error: $db->error");
    exit;
}

print 'clearing time: '.(microtime(true) - $time).PHP_EOL;
$time = microtime(true);

$errors = 0;

foreach ($cached as $route_cache)
{
    $route = cache_read($route_cache);

    if (!$route->dates)
    {
        continue;
    }

    $d = current($route->dates);
    
    $result = $db->query(sprintf(
        "INSERT INTO bus (bus_name, id_bus_type, f_way, b_way, alias, id_town, id_subway) VALUES ('%s', %d, '%s', '%s', '%s', %d, %d)",
        $db->real_escape_string($route->mr_num),
        1,
        $db->real_escape_string($d['A']->name),
        $db->real_escape_string($d['B']->name),
        $db->real_escape_string(transletiration($route->mr_num)),
        $city_id,
        0
    ));

    if (!$result)
    {
        trigger_error("mysql error: {$db->error}");
        $errors++;
        continue;
    }
    
    $id_bus = (int)$db->insert_id;
    if ($id_bus <= 0)
    {
        trigger_error("insert_id <= 0");
        $errors++;
        continue;
    }

    $uniq = array();

    foreach ($route->dates as $date => $directions)
    {
        foreach (array('A', 'B') as $dir => $key)
        {
            foreach ($directions[$key]->stations as $station)
            {
                $station_name = $db->real_escape_string($station->title);

                $id_station = db_fetch_value("SELECT id_station FROM station WHERE station_name = '{$station_name}' AND id_town = {$city_id}");
                if ($id_station <= 0)
                {
                    $result = $db->query(sprintf(
                        "INSERT INTO station (station_name, alias, id_town) VALUES ('%s', '%s', %d)",
                        $station_name,
                        $db->real_escape_string(transletiration($station->title)),
                        $city_id
                    ));
                    if (!$result)
                    {
                        trigger_error("mysql error: {$db->error}");
                        $errors++;
                        continue;
                    }
                    $id_station = (int)$db->insert_id;
                    if ($id_station <= 0)
                    {
                        trigger_error("insert_id <= 0");
                        $errors++;
                        continue;
                    }
                }

                $mask = sprintf('%07b', $station->table->dow);
                $mask = substr($mask, 2).substr($mask, 0, 2);
                
                $id_race_type = get_race_type($mask);
                if (!$id_race_type)
                {
                    trigger_error("race_type error, mask: {$mask}");
                    $errors++;
                    continue;
                }

                $values = array();

                foreach ($station->table->schedule as $schedule)
                {
                    $race_number = 1;
                    foreach ($schedule->planning as $hour => $minutes)
                    {
                        foreach ($minutes as $minute)
                        {
                            $uniq_key = sprintf('%d %d %s %d:%d', $dir, $id_station, $mask, $hour, $minute[0]);

                            if (isset($uniq[$uniq_key]))
                            {
                                continue;
                            }
                            else
                            {
                                $uniq[$uniq_key] = 1;
                            }

                            $values[] = sprintf(
                                    "('%d:%02d',%d,%d,%d,%d,%d,%d,%d)",
                                    $hour, $minute[0],
                                    $city_id,
                                    $id_bus,
                                    $id_station,
                                    $id_race_type,
                                    0,
                                    $race_number++,
                                    $dir
                            );
                        }
                    }
                }

                $sql = 'INSERT INTO time (time, id_town, id_bus, id_station, id_race_type, payment_type, race_number, race_direction) VALUES ';
                foreach (array_chunk($values, 100) as $chunk)
                {
                    $result = $db->query($sql.implode(',', $chunk));
                    if (!$result)
                    {
                        trigger_error("mysql error: {$db->error}");
                        $errors++;
                        continue;
                    }
                }

                unset($values, $station, $schedule);
            }
        }
    }

    unset($uniq, $directions, $route);
    
    //cache_delete($route_cache);
}

print 'query time: '.(microtime(true) - $time).PHP_EOL;
$db->close();
print 'total time: '.(microtime(true) - $time).PHP_EOL;
print 'memory peak usage: '.round(memory_get_peak_usage(true)/1024/1024).'M'.PHP_EOL;

print 'clearing old cache files'.PHP_EOL;
$time = strtotime('-14 days');
foreach (glob(CACHE_DIR.'/*') as $filename)
{
    if (filemtime($filename) < $time)
    {
        unlink($filename);
    }
}
print 'done'.PHP_EOL;

if ($errors === 0 && count($cached) > 10)
{
    print 'status: ok'.PHP_EOL;
}

exit;



function process_routes_list()
{
    $url = 'http://navi.kazantransport.ru/api/browser/timetables.php?action=routes&type=1&okato=all&route-type=all';
    $routes = json_decode(addSomeSugar(request($url)));
    if (!$routes)
    {
        trigger_error('request error');
        exit;
    }

    $today = strtotime('today');
    
    $urls = array();
    foreach ($routes as $k => $route)
    {
        for ($d = 0; $d < 6; $d++)
        {
            $date = date('Y-m-d', $today + $d*24*3600);
            $urls[$k.'|'.$date] = "http://navi.kazantransport.ru/api/browser/timetables.php?action=directions&mr_id={$route->mr_id}&date={$date}";
        }
    }

    $results = array();
    foreach (request_multi($urls) as $key => $result)
    {
        list($k, $date) = explode('|', $key);

        if (!isset($results[$k]))
        {
            $results[$k] = array();
        }
        
        $results[$k][$date] = $result;
    }

    $total = count($routes);
    $done = 0;

    $cached = array();

    foreach ($routes as $k => $route)
    {
        if (!isset($results[$k]))
        {
            trigger_error('no result');
            $done++;
            continue;
        }

        $route->dates = array();

        foreach ($results[$k] as $date => $result)
        {
            $directions = json_decode(addSomeSugar($result), true);

            if (!$directions)
            {
                continue;
            }

            process_route($route, $directions, $date);
        }

        $cached[$k] = cache_write('route', $route);
        unset($route);

        $done++;
        print "routes: {$done} of {$total}".PHP_EOL;
    }

    cache_write('data', $cached);

    return $cached;
}

function process_route($route, $directions, $date)
{
    $urls = array();

    foreach ($directions as $direction_letter => $direction_name)
    {
        $urls[$direction_letter] = "http://navi.kazantransport.ru/api/browser/timetables.php?action=stops&mr_id={$route->mr_id}&direction={$direction_letter}&date={$date}";
    }

    $direction_results = request_multi($urls);

    $route->dates[$date] = array();

    foreach ($directions as $direction_letter => $direction_name)
    {
        if (!isset($direction_results[$direction_letter]))
        {
            trigger_error('no result');
            continue;
        }
        
        $stations = json_decode(addSomeSugar($direction_results[$direction_letter]));

        $urls = array();

        foreach ($stations as $k => $station)
        {
            $urls[$k] = "http://navi.kazantransport.ru/api/browser/timetables.php?action=timetable&mr_id={$route->mr_id}&direction={$direction_letter}&date={$date}&st_id={$station->id}";
        }

        $results = request_multi($urls);

        foreach ($stations as $k => $station)
        {
            if (!isset($results[$k]))
            {
                trigger_error('no result');
                continue;
            }

            $station->table = json_decode(addSomeSugar($results[$k]));
        }

        $route->dates[$date][$direction_letter] = (object)array(
            'name' => $direction_name,
            'stations' => $stations,
        );
    }
}

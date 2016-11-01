<?php

/**
 * Novosibirsk Bus Parser
 * 
 * Парсинг автобусов с сайта http://nskgortrans.ru/site/rasp
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

if ($argc !== 2)
{
    print 'Сбор маршрутов'.PHP_EOL;
    $routes = collect_routes();

    print 'Автобусы'.PHP_EOL;
    process_routes_list($routes['автобус']);

    print 'Маршрутное такси'.PHP_EOL;
    process_routes_list($routes['маршрутное такси']);

    cache_write('data', $routes);

    print 'memory peak usage: '.round(memory_get_peak_usage(true)/1024/1024).'M'.PHP_EOL;
}
else
{
    $routes = cache_read($argv[1]);
}


// Обработка полученных данных

$city_id = 85;

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

foreach ($routes as $list)
{
    foreach ($list['routes'] as $route)
    {
        $directions = cache_read($route['directions']);

        $f_way = '';
        $b_way = '';
        foreach ($directions as $direction)
        {
            if ($direction['id'] === 'A')
            {
                $f_way = $direction['title'];
            }
            elseif ($direction['id'] === 'B')
            {
                $b_way = $direction['title'];
            }
        }

        $result = $db->query(sprintf(
            "INSERT INTO bus (bus_name, id_bus_type, f_way, b_way, alias, id_town, id_subway) VALUES ('%s', %d, '%s', '%s', '%s', %d, %d)",
            $db->real_escape_string($route['name']),
            $list['id_bus_type'],
            $db->real_escape_string($f_way),
            $db->real_escape_string($b_way),
            $db->real_escape_string(transletiration($route['name'])),
            $city_id,
            0
        ));

        unset($f_way, $b_way);

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

        foreach ($directions as $direction)
        {
            foreach ($direction ['schedules'] as $schedule)
            {
                $id_race_type = get_race_type_by_string($schedule['title']);
                if (!$id_race_type)
                {
                    trigger_error("race_type error, title: {$schedule['title']}");
                    $errors++;
                    continue;
                }

                foreach ($schedule['stations'] as $station)
                {
                    $station_name = $db->real_escape_string($station['title']);
                    
                    $id_station = db_fetch_value("SELECT id_station FROM station WHERE station_name = '{$station_name}' AND id_town = {$city_id}");
                    if ($id_station <= 0)
                    {
                        $result = $db->query(sprintf(
                            "INSERT INTO station (station_name, alias, id_town) VALUES ('%s', '%s', %d)",
                            $station_name,
                            $db->real_escape_string(transletiration($station['title'])),
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

                    $values = array();

                    $race_number = 1;
                    foreach ($station['table'] as $hour => $minutes)
                    {
                        foreach ($minutes as $minute)
                        {
                            $uniq_key = sprintf('%d %d %d %d:%d', $direction['dir'], $id_station, $race_number, $hour, $minute);

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
                                    $hour, $minute,
                                    $city_id,
                                    $id_bus,
                                    $id_station,
                                    $id_race_type,
                                    $list['payment_type'],
                                    $race_number++,
                                    $direction['dir']
                            );
                        }
                        unset($uniq_key);
                    }
                    unset($minute, $minutes, $hour, $race_number, $id_station, $station_name);

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
                    
                    unset($sql, $chunk, $values);
                }

                unset($id_race_type, $station);
            }

            unset($schedule);
        }

        unset($direction, $directions);
    }
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

if ($errors === 0)
{
    print 'status: ok'.PHP_EOL;
}

exit;


function collect_routes()
{
    $types = array('автобус','троллейбус','трамвай','','','','','маршрутное такси');

    $urls = array();
    for ($i = 0; $i < 9; $i++)
    {
        $urls[] = 'http://nskgortrans.ru/site/rasp?q='.$i;
    }
    $results = request_multi($urls);
    $rows = array();
    foreach (array_keys($urls) as $k)
    {
        if (!isset($results[$k]))
        {
            trigger_error('request error');
            return false;
        }

        foreach (parse_response($results[$k]) as $row)
        {
            $rows[$row[2].'-'.$row[1]] = $row;
        }
    }

    $routes = array(
        'автобус' => array(
            'payment_type' => 0,
            'id_bus_type' => 1,
            'route_type' => 1,
            'routes' => array(),
        ),
        'маршрутное такси' => array(
            'payment_type' => 1,
            'id_bus_type' => 1,
            'route_type' => 8,
            'routes' => array(),
        ),
    );
    
    foreach ($rows as $row)
    {
        $type = $row[2] > 3 ? $types[7] : $types[$row[2]-1];
        if (!isset($routes[$type]))
        {
            //print "skipped {$type}".PHP_EOL;
            continue;
        }
        $id = iconv("utf8", "cp1251", $row[3]);
        $routes[$type]['routes'][$id] = array(
            'id' => $id,
            'encoded' => urlencode($row[3]),
            'name' => iconv("utf8", "cp1251", $row[1]),
        );
    }

    return $routes;
}


function process_routes_list(&$list)
{
    $directions = get_directions($list);
    parse_schedules($list, $directions);
    
    $total = count($list['routes']);
    $done = 0;

    foreach ($list['routes'] as $id => &$route)
    {
        if (!isset($directions[$id]))
        {
            trigger_error('value error');
            $done++;
            continue;
        }

        $dirs = $directions[$id];
        parse_stations($list['route_type'], $route['encoded'], $dirs);
        
        foreach ($dirs as &$direction)
        {
            parse_table($list['route_type'], $route['id'], $direction);
        }

        $route['directions'] = cache_write('route_', $dirs);
        unset($direction, $dirs);

        $done++;
        print "{$done} of {$total}".PHP_EOL;
    }
}

function get_directions($list)
{
    $directions = array();
    $urls = array();
    foreach ($list['routes'] as $id => $route)
    {
        $urls[$id] = "http://nskgortrans.ru/site/rasp?m={$route['encoded']}&t={$list['route_type']}";
    }
    $results = request_multi($urls);
    foreach (array_keys($urls) as $id)
    {
        if (!isset($results[$id]))
        {
            trigger_error('request error');
            continue;
        }

        $d = simplexml_load_string($results[$id]);
        $directions[$id] = array();
        foreach ($d->route as $v)
        {
            $dir_id = $v->attributes()->id->__toString();
            $directions[$id][] = array(
                'id' => $dir_id,
                'dir' => $dir_id === 'A' ? 0 : ($dir_id === 'B' ? 1 : 2),
                'title' => $v->attributes()->title->__toString(),
            );
        }
        unset($dir_id, $v, $d);
    }
    unset($id, $name, $results, $urls);
    return $directions;
}

function parse_schedules($list, &$directions)
{
    $urls = array();
    foreach ($list['routes'] as $id => $route)
    {
        foreach ($directions[$id] as $k => $direction)
        {
            $urls["{$id}/{$k}"] = "http://nskgortrans.ru/site/rasp?m={$route['encoded']}&t={$list['route_type']}&z={$direction['id']}";
        }
    }
    $results = request_multi($urls);
    foreach ($list['routes'] as $id => $route)
    {
        foreach ($directions[$id] as $k => &$direction)
        {
            if (!isset($results["{$id}/{$k}"]))
            {
                trigger_error('request error');
                continue;
            }
            $direction['schedules'] = array();
            $d = simplexml_load_string($results["{$id}/{$k}"]);
            foreach ($d->schedule as $schedule)
            {
                $attributes = $schedule->attributes();
                $schedule_id = $attributes->id->__toString();
                $direction['schedules'][$schedule_id] = array(
                    'id' => $schedule_id,
                    'title' => $attributes->title->__toString(),
                );
            }
            unset($schedule_id, $attributes, $schedule, $d);
        }
    }
    unset($id, $name, $results, $urls);
}

function parse_stations($route_type, $id_encoded, &$dirs)
{
    $urls = array();
    foreach ($dirs as $k => $direction)
    {
        foreach ($direction['schedules'] as $s => $schedule)
        {
            $urls["{$k}/{$s}"] =
                    "http://nskgortrans.ru/site/rasp?m={$id_encoded}&t={$route_type}&z={$direction['id']}&sch={$schedule['id']}";
        }
    }
    $results = request_multi($urls);
    foreach ($dirs as $k => &$direction)
    {
        foreach ($direction['schedules'] as $s => &$schedule)
        {
            if (!isset($results["{$k}/{$s}"]))
            {
                trigger_error('request error');
                continue;
            }
            $schedule['stations'] = array();
            $d = simplexml_load_string($results["{$k}/{$s}"]);
            foreach ($d->stop as $stop)
            {
                $attributes = $stop->attributes();
                $full_id = $attributes->id->__toString();
                $row = explode('|', $full_id);
                $id = $row[0];
                $schedule['stations'][$full_id] = array(
                    'full_id' => $full_id,
                    'id' => $id,
                    'title' => $attributes->title->__toString(),
                );
            }
            unset($id, $full_id, $row, $attributes, $stop, $d);
        }
    }
    unset($s, $k, $results, $urls);
}

function parse_table($route_type, $id, &$direction)
{
    $re1 = '#<td class="td_plan_h" bgcolor="[^"]+" >\s+<span>(\d+)</span>\s+</td>\s+'
            . '<td class="td_plan_m" bgcolor="[^"]+" >(.+?)</td>#s';
    $re2 = '#<div>(\d+)</div>#';
    
    $urls = array();
    foreach ($direction['schedules'] as $s => $schedule)
    {
        foreach ($schedule['stations'] as $st => $station)
        {
            $full_id = urlencode($station['full_id']);
            $id_encoded = urlencode($id);
            $urls["{$s}/{$st}"] =
                    "http://maps.nskgortrans.ru/components/com_planrasp/helpers/grasp.php"
                    . "?tv=mr&m={$id_encoded}&t={$route_type}&r={$direction['id']}&sch={$schedule['id']}&s={$full_id}&v=0";
        }
    }
    unset($id_encoded, $full_id);
    $results = request_multi($urls);
    foreach ($direction['schedules'] as $s => &$schedule)
    {
        foreach ($schedule['stations'] as $st => &$station)
        {
            if (!isset($results["{$s}/{$st}"]))
            {
                trigger_error('request error');
                continue;
            }

            if (!preg_match_all($re1, $results["{$s}/{$st}"], $matches, PREG_SET_ORDER))
            {
                trigger_error('parse error, url: '.$urls["{$s}/{$st}"]);
                continue;
            }

            $station['table'] = array();
            foreach ($matches as $set)
            {
                $hour = $set[1];
                $station['table'][$hour] = array();
                if (!preg_match_all($re2, $set[2], $matches2))
                {
                    continue;
                }
                foreach ($matches2[1] as $minute)
                {
                    $station['table'][$hour][] = $minute;
                }
                unset($minute, $hour, $matches2);
            }
            unset($set, $matches);
        }
    }
    unset($s, $st, $results, $urls, $re1, $re2);
}

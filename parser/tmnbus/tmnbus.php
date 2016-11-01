<?php

/**
 * Tumen Bus Parser
 * 
 * Парсинг автобусов с сайта http://tgt72.ru/schedule/
 * 
 * @version 0.1 (2016/09)
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
    print 'Автобусы'.PHP_EOL;
    $routes = process_routes();

    print 'memory peak usage: '.round(memory_get_peak_usage(true)/1024/1024).'M'.PHP_EOL;
}
else
{
    $routes = cache_read($argv[1]);
}


// Обработка полученных данных

$city_id = 86;

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

foreach ($routes as $cache)
{
    $route = cache_read($cache);

    if (!$route->directions)
    {
        trigger_error("skipped {$route->id}");
        continue;
    }

    foreach ($route->directions as $direction)
    {
        if ($direction->dir === 0)
        {
            $f_way = $direction->name;
        }
        elseif ($direction->dir === 1)
        {
            $b_way = $direction->name;
        }
    }
    unset($direction);

    $result = $db->query(sprintf(
        "INSERT INTO bus (bus_name, id_bus_type, f_way, b_way, alias, id_town, id_subway) VALUES ('%s', %d, '%s', '%s', '%s', %d, %d)",
        $db->real_escape_string($route->name),
        1,
        $db->real_escape_string($f_way),
        $db->real_escape_string($b_way),
        $db->real_escape_string(transletiration($route->name)),
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

    foreach ($route->directions as $direction)
    {
        foreach ($direction->stations as $station)
        {
            $station_name = $db->real_escape_string($station->name);

            $id_station = db_fetch_value("SELECT id_station FROM station WHERE station_name = '{$station_name}' AND id_town = {$city_id}");
            if ($id_station <= 0)
            {
                $result = $db->query(sprintf(
                    "INSERT INTO station (station_name, alias, id_town) VALUES ('%s', '%s', %d)",
                    $station_name,
                    $db->real_escape_string(transletiration($station->name)),
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
            unset($station_name);


            $values = array();

            foreach ($station->times as $t => $m)
            {
                $id_race_type = get_race_type($m->mask);
                if (!$id_race_type)
                {
                    trigger_error("race_type error, mask: {$m->mask}");
                    $errors++;
                    continue;
                }

                $uniq_key = sprintf('%d %d %d %s', $direction->dir, $id_station, $m->race, $t);
                if (isset($uniq[$uniq_key]))
                {
                    continue;
                }
                else
                {
                    $uniq[$uniq_key] = 1;
                }

                $values[] = sprintf(
                        "('%s',%d,%d,%d,%d,%d,%d,%d)",
                        $t,
                        $city_id,
                        $id_bus,
                        $id_station,
                        $id_race_type,
                        0,
                        $m->race,
                        $direction->dir
                );
                unset($uniq_key);
            }
            unset($t, $m, $id_race_type, $id_station);

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
        unset($station);
    }

    unset($direction, $uniq, $route);
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


function process_routes()
{
    $routes = get_routes();

    $checkpoints = get_checkpoints($routes);
    $paths = get_paths($routes);

    prepare_directions($routes, $checkpoints, $paths);
    unset($checkpoints, $paths);

    $cached = array();

    $total = count($routes);
    $done = 0;

    foreach ($routes as $route)
    {
        process_dates($route);
        process_times($route);

        foreach ($route->directions as $direction)
        {
            $stations = array();
            foreach ($direction->stations as $station)
            {
                $times = array();
                foreach ($station->dates as $date)
                {
                    foreach ($date->times as $time)
                    {
                        if (!isset($times[$time]))
                        {
                            $times[$time] = (object)array(
                                'mask' => '0000000',
                                'race' => 0,
                            );
                        }
                        $times[$time]->mask[$date->order-1] = 1;
                    }
                    unset($time, $date->times);
                }
                unset($date, $station->dates);

                $station->times = $times;
                unset($times);

                $stations[] = $station;
            }

            foreach ($stations as $s => $station)
            {
                $race_number = 1;
                foreach ($station->times as $t => $m)
                {
                    $m->race = $race_number++;
                }
            }
            
            unset($s, $station, $stations);
        }

        $cached[] = cache_write('route', $route);
        unset($station, $direction, $route);

        $done++;
        print "{$done} of {$total}".PHP_EOL;
    }

    cache_write('data', $cached);

    return $cached;
}

function get_routes()
{
    $result = json_decode(request('http://api.tgt72.ru/api/v5/routesforsearch'));
    if (!$result->success)
    {
        trigger_error('request error');
        exit;
    }
    $routes = array();
    foreach ($result->objects as $object)
    {
        $routes[$object->id] = $object;
    }
    unset($object, $result);
    return $routes;
}

function get_checkpoints($routes)
{
    $urls = array();
    foreach ($routes as $id => $route)
    {
        $urls[$id] = "http://api.tgt72.ru/api/v5/checkpointsforsearch?route_id={$route->id}&query_text=";
    }
    $results = request_multi($urls);
    unset($urls);
    $checkpoints = array();
    foreach ($routes as $id => $route)
    {
        if (!isset($results[$id]))
        {
            trigger_error('request error');
            continue;
        }

        $result = json_decode($results[$id]);
        if (!$result->success)
        {
            trigger_error('request error');
            continue;
        }
        $checkpoints[$id] = array();
        foreach ($result->objects as $object)
        {
            $checkpoints[$id][$object->id] = (object)array(
                'id' => $object->id,
                'name' => $object->name,
            );
        }
        unset($object, $result);
    }
    unset($id, $route, $results);
    return $checkpoints;
}

function get_paths($routes)
{
    $urls = array();
    foreach ($routes as $id => $route)
    {
        $urls[$id] = "http://api.tgt72.ru/api/v5/times/?show_intervals=1&route_id={$route->id}";
    }
    $results = request_multi($urls);
    unset($urls);
    $paths = array();
    foreach ($routes as $id => $route)
    {
        if (!isset($results[$id]))
        {
            trigger_error('request error');
            continue;
        }

        $result = json_decode($results[$id]);
        if (!isset($result->objects))
        {
            trigger_error('request error');
            continue;
        }
        $paths[$id] = array();
        foreach ($result->objects as $object)
        {
            $paths[$id][] = (object)array(
                'checkpoint_id' => $object->checkpoint->id,
                'is_forward_direction' => $object->is_forward,
                'order' => $object->order,
            );
        }
        unset($object, $result);
    }
    unset($id, $route, $results);
    return $paths;
}

function _sort_paths($a, $b)
{
    return $a->order - $b->order;
}

function prepare_directions($routes, $checkpoints, $paths)
{
    foreach ($routes as $id => $route)
    {
        if (!isset($paths[$id]))
        {
            trigger_error('value error');
            continue;
        }

        usort($paths[$id], '_sort_paths');
        $checkpoint = $checkpoints[$id];
        $route->directions = array();

        if (!$paths[$id])
        {
            continue;
        }

        
        // forward

        $stations = array();
        $start = array('order' => PHP_INT_MAX, 'checkpoint_id' => null);
        $finish = array('order' => 0, 'checkpoint_id' => null);

        foreach ($paths[$id] as $path)
        {
            if (!$path->is_forward_direction)
            {
                continue;
            }

            if (!isset($checkpoint[$path->checkpoint_id]))
            {
                trigger_error("value error, route_id={$route->id} checkpoint_id={$path->checkpoint_id}");
                continue;
            }

            if ($path->order <= $start['order'])
            {
                $start['order'] = $path->order;
                $start['checkpoint_id'] = $path->checkpoint_id;
            }

            if ($path->order >= $finish['order'])
            {
                $finish['order'] = $path->order;
                $finish['checkpoint_id'] = $path->checkpoint_id;
            }

            $stations[$path->checkpoint_id] = clone $checkpoint[$path->checkpoint_id];
        }

        if ($start['order'] === PHP_INT_MAX || $finish['order'] === 0)
        {
            trigger_error("unknown order, route_id={$route->id}");
            continue;
        }

        $route->directions[0] = (object)array(
            'dir' => 0,
            'name' => $checkpoint[$start['checkpoint_id']]->name.' — '.$checkpoint[$finish['checkpoint_id']]->name,
            'stations' => $stations,
        );

        unset($path, $start, $finish, $stations);


        // backward

        $stations = array();
        $start = array('order' => PHP_INT_MAX, 'checkpoint_id' => null);
        $finish = array('order' => 0, 'checkpoint_id' => null);

        foreach ($paths[$id] as $path)
        {
            if ($path->is_forward_direction)
            {
                continue;
            }

            if (!isset($checkpoint[$path->checkpoint_id]))
            {
                trigger_error("value error, route_id={$route->id} checkpoint_id={$path->checkpoint_id}");
                continue;
            }

            if ($path->order <= $start['order'])
            {
                $start['order'] = $path->order;
                $start['checkpoint_id'] = $path->checkpoint_id;
            }

            if ($path->order >= $finish['order'])
            {
                $finish['order'] = $path->order;
                $finish['checkpoint_id'] = $path->checkpoint_id;
            }

            $stations[$path->checkpoint_id] = $checkpoint[$path->checkpoint_id];
        }

        if ($start['order'] === PHP_INT_MAX || $finish['order'] === 0)
        {
            trigger_error('unknown order');
            continue;
        }

        $route->directions[1] = (object)array(
            'dir' => 1,
            'name' => $checkpoint[$start['checkpoint_id']]->name.' — '.$checkpoint[$finish['checkpoint_id']]->name,
            'stations' => $stations,
        );

        unset($path, $start, $finish, $stations);
    }

    unset($route);
}

function process_dates($route)
{
    $urls = array();
    foreach ($route->directions as $k => $direction)
    {
        foreach ($direction->stations as $s => $station)
        {
            $urls["{$k}/{$s}"] = "http://api.tgt72.ru/api/v5/dates?route_id={$route->id}&checkpoint_id={$station->id}";
        }
    }
    $results = request_multi($urls);
    unset($urls);
    foreach ($route->directions as $k => $direction)
    {
        foreach ($direction->stations as $s => $station)
        {
            if (!isset($results["{$k}/{$s}"]))
            {
                trigger_error('request error');
                continue;
            }

            $result = json_decode($results["{$k}/{$s}"]);
            if (!$result->success)
            {
                trigger_error('request error');
                continue;
            }

            $station->dates = array();

            if (!$result->objects)
            {
                continue;
            }

            $dates = array();
            foreach ($result->objects as $object)
            {
                $dates[$object->date] = $object;
            }
            unset($object, $result);

            if (count($dates) < 7)
            {
                trigger_error("no dates, route_id={$route->id} checkpoint_id={$station->id}");
                continue;
            }

            krsort($dates);
            foreach (array_slice($dates, 0, 7, true) as $date)
            {
                $date->order = (int)date('N', strtotime($date->date));
                $station->dates[] = $date;
            }
            unset($dates);
        }

        unset($s, $station);
    }
    unset($k, $direction, $results);
}

function process_times($route)
{
    $urls = array();
    foreach ($route->directions as $k => $direction)
    {
        foreach ($direction->stations as $s => $station)
        {
            foreach ($station->dates as $d => $date)
            {
                $urls["{$k}/{$s}/{$d}"] =    "http://api.tgt72.ru/api/v5/times/?date={$date->date}&show_intervals=1"
                                        . "&route_id={$route->id}&checkpoint_id={$station->id}";
            }
        }
    }
    $results = request_multi($urls);
    unset($urls);
    foreach ($route->directions as $k => $direction)
    {
        foreach ($direction->stations as $s => $station)
        {
            foreach ($station->dates as $d => $date)
            {
                if (!isset($results["{$k}/{$s}/{$d}"]))
                {
                    trigger_error('request error');
                    continue;
                }

                $result = json_decode($results["{$k}/{$s}/{$d}"]);
                if (!$result->success)
                {
                    trigger_error('request error');
                    continue;
                }

                if ($result->objects)
                {
                    $date->times = $result->objects[0]->times;
                }
                else
                {
                    $date->times = array();
                }
                unset($result);
            }
            unset($d, $date);
        }
        unset($s, $station);
    }
    unset($k, $direction, $results);
}

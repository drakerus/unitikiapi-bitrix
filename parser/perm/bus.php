<?php

/**
 * MasterKlavi Bus Parser
 *
 * Парсинг автобусов с сайта http://m.gortransperm.ru/
 *
 * @version 0.5 (2015/10)
 * @author Мастер Клавы (masterklavi.ru)
 *
 * @see https://www.fl.ru/tu/order/281591/
 * @see https://www.fl.ru/projects/2480672/php-parser-grafika-dvijeniya-avtobusov-g-permi.html
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
require './functions.php';

if (empty($argv[1])) {

    $data = array();

    // Запросы по маршрутным такси

    echo "Маршрутные такси\n";
    $routes = process_routes_list(3);
    if ($routes) {
        $data[] = array(
                'routes' => $routes,
                'payment_type'  => 1,
                'id_bus_type'   => 1,
        );
    }


    // Запросы по пригородным автобусам

    echo "Пригородные автобусы\n";
    $routes = process_routes_list(4);
    if ($routes) {
        $data[] = array(
                'routes' => $routes,
                'payment_type'  => 0,
                'id_bus_type'   => 2,
        );
    }


    // Запросы по автобусам

    echo "Автобусы\n";
    $routes = process_routes_list(0);
    if ($routes) {
        $data[] = array(
                'routes' => $routes,
                'payment_type'  => 0,
                'id_bus_type'   => 1,
        );
    }

    $cache_name = cache_write('data', $data);
    echo "cache_name = $cache_name\n";

} else {
    $data = cache_read($argv[1]);
    if (!$data) {
        trigger_error("data == false");
        exit;
    }
}

echo "memory peak usage: ", round(memory_get_peak_usage(true)/1024/1024), "M\n";


// Обработка полученных данных

$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if ($db->connect_error) {
    trigger_error("connect_error: $db->connect_error");
    return;
}
$db->set_charset('utf8');

$time = microtime(true);

// Чистка
$result = $db->query("DELETE FROM time WHERE id_town = 81");
if (!$result) {
    trigger_error("delete error: $db->error");
    exit;
}
$result = $db->query("DELETE FROM station WHERE id_town = 81");
if (!$result) {
    trigger_error("delete error: $db->error");
    exit;
}
$result = $db->query("DELETE FROM bus WHERE id_town = 81");
if (!$result) {
    trigger_error("delete error: $db->error");
    exit;
}

echo "clearing time: ", microtime(true) - $time, "\n";
$time = microtime(true);

$errors = 0;

foreach ($data as $d) {

    foreach ($d['routes'] as $route) {
        $result = $db->query(sprintf(
                "INSERT INTO bus (bus_name, id_bus_type, f_way, b_way, href, alias, id_town, id_subway) VALUES ('%s', %d, '%s', '%s', '', '%s', 81, 0)",
                $db->real_escape_string($route['bus_name']),
                $d['id_bus_type'],
                $db->real_escape_string($route['route_data']['f_way']),
                $db->real_escape_string($route['route_data']['b_way']),
                $db->real_escape_string(transletiration($route['bus_name']))
        ));
        if (!$result) {
            trigger_error("result == false ($db->error)");
            $errors++;
            continue;
        }
        $id_bus = (int)$db->insert_id;
        if ($id_bus <= 0) {
            trigger_error("insert_id <= 0");
            $errors++;
            continue;
        }
        foreach (array('f_stations', 'b_stations') as $dir => $key) {
            $cache_key = $route['route_data'][$key];
            foreach (cache_read($cache_key) as $station) {
                $station_name = $db->real_escape_string($station['station_name']);
                $id_station = db_fetch_value("SELECT id_station FROM station WHERE station_name = '$station_name' AND id_town = 81");
                if ($id_station <= 0) {
                    $result = $db->query(sprintf(
                            "INSERT INTO station (station_name, alias, id_town) VALUES ('%s', '%s', 81)",
                            $station_name,
                            $db->real_escape_string(transletiration($station['station_name']))
                    ));
                    if (!$result) {
                        trigger_error("result == false ($db->error)");
                        $errors++;
                        continue;
                    }
                    $id_station = (int)$db->insert_id;
                    if ($id_station <= 0) {
                        trigger_error("insert_id <= 0");
                        $errors++;
                        continue;
                    }
                }
                $query_parts = array();
                foreach ($station['times'] as $t) {
                    $id_race_type = get_race_type($t['mask']);
                    if (!$id_race_type) {
                        trigger_error("id_race_type == false ({$t['mask']})");
                        $errors++;
                        continue;
                    }
                    $query_parts[] = sprintf(
                            "('%s',81,%d,%d,%d,%d,%d,%d)",
                            $t['time'],
                            $id_bus,
                            $id_station,
                            $id_race_type,
                            $d['payment_type'],
                            $t['race_number'],
                            $dir
                    );
                    if (count($query_parts) >= 50) {
                        $result = $db->query("INSERT INTO time (time, id_town, id_bus, id_station, id_race_type, payment_type, race_number, race_direction) VALUES ".implode(',', $query_parts));
                        if (!$result) {
                            trigger_error("result == false ($db->error)");
                            $errors++;
                            continue;
                        }
                        $query_parts = array();
                    }
                }
                if (count($query_parts) > 0) {
                    $result = $db->query("INSERT INTO time (time, id_town, id_bus, id_station, id_race_type, payment_type, race_number, race_direction) VALUES ".implode(',', $query_parts));
                    if (!$result) {
                        trigger_error("result == false ($db->error)");
                        $errors++;
                        continue;
                    }
                    unset($query_parts);
                }
            }
            //cache_delete($cache_key);
        }
    }
}

echo "query time: ", microtime(true) - $time, "\n";
$db->close();
echo "memory peak usage: ", round(memory_get_peak_usage(true)/1024/1024), "M\n";

print 'clearing old cache files'.PHP_EOL;
$time = strtotime('-14 days');
foreach (glob(CACHE_DIR.'/*') as $filename)
{
    if (filemtime($filename) < $time)
    {
        unlink($filename);
    }
}
echo "done\n";

if ($errors === 0)
{
    print 'status: ok'.PHP_EOL;
}

exit;






/**
 * Парсинг списка маршрутов
 *
 * @see http://m.gortransperm.ru/routes-list/3/
 * @param int   list_id
 * @return array {bus_name, route_data}
 */
function process_routes_list($list_id) {

    // Запрос списка маршрутов
    $result = request("http://m.gortransperm.ru/routes-list/$list_id/");
    if (!$result) {
        trigger_error("result == false");
        exit;
    }
    if (!preg_match_all('#<a href="(/route/\d+/)">\s*(\d+[^,]*),[^<]+</a>#s', $result, $matches)) {
        trigger_error("matches == false");
        return false;
    }
    unset($result);
	$urls = array();
    foreach ($matches[1] as $k => $route_url) {
		$urls[$k] = "http://m.gortransperm.ru$route_url";
	}
    $results = request_multi($urls);
	$total = count($results);
    $done = 1;
    $routes = array();
    foreach ($results as $k => $result) {
        echo "$done of $total routes (".$urls[$k]."):\n";
		// EDITED
		/*if ($urls[$k] !== 'http://m.gortransperm.ru/route/222/') {
			continue;
		}*/
        // обработка данного автобуса (маршрута)
        $route_data = process_route($result);
        if (!$route_data) {
            trigger_error('route_data == false');
            continue;
        }
        $bus_name = $matches[2][$k];
        $routes[] = array('bus_name' => $bus_name, 'route_data' => $route_data);

        $done++;
    }
    return $routes;
}

/**
 * Парсинг автобуса (маршрута)
 *
 * @param string $route_url
 * @return array f_way, f_stations, b_way, b_stations
 */
function process_route($result) {

    if (!preg_match_all('#<h3>([^<]+)</h3>#s', $result, $matches)) {
        trigger_error("matches == false");
        return false;
    }
    if (count($matches[1]) != 2) {
        trigger_error("count(matches[1]) != 2");
        return false;
    }

    // получение списков остановок
    $p = strrpos($result, '<ul data-role="listview">');
    if ($p === false) {
        trigger_error("p == false");
        return false;
    }

    echo "forward\n";

    // прямое направление
    $stations = process_stations(substr($result, 0, $p));
    if (!$stations) {
        trigger_error("stations == false");
        return false;
    }
    $f_stations = cache_write('stations', $stations);
    unset($stations);

    echo "backward\n";

    // обратное направление
    $stations = process_stations(substr($result, $p));
    if (!$stations) {
        trigger_error("stations == false");
        return false;
    }
    $b_stations = cache_write('stations', $stations);
    unset($stations);

    unset($result, $p);

    $f_way = html_entity_decode(trim($matches[1][0]));
    $b_way = html_entity_decode(trim($matches[1][1]));

    return array('f_way' => $f_way, 'f_stations' => $f_stations, 'b_way' => $b_way, 'b_stations' => $b_stations);
}

/**
 * Получение списка остановок
 *
 * @param string $html
 * @return array {station_name, times}
 */
function process_stations($html) {
    if (!preg_match_all('#<a href="(/time-table/[^"]+)">([^<]+)</a>#s', $html, $matches)) {
        trigger_error("matches == false");
        return false;
    }
    unset($html);
    $total = count($matches[1]);
    $done = 0;
    $stations = array();
    foreach ($matches[1] as $k => $table_url) {

        echo "\r",$done++/$total*100,"%    ";

        // обработка временной таблицы одной станции
        $masks = process_table($table_url);

        $station_name = html_entity_decode(trim($matches[2][$k]));
        $stations[] = array('station_name' => $station_name, 'masks' => $masks);
    }

	$days_of_week = array('1111000' => 'monday', '0000010' => 'saturday', '0000001' => 'sunday', '0000100' => 'friday');
	$lines_by_mask = array();
	foreach (array_keys($days_of_week) as $mask) {
		$lines = array();
		foreach ($stations as $station) {
			if (!isset($station['masks'][$mask])) {
				continue;
			}
			foreach ($station['masks'][$mask] as $n => $time) {
				if (!isset($lines[$n])) {
					$lines[$n] = '';
				}
				$lines[$n] .= $time;
			}
		}
		$lines_by_mask[$mask] = $lines;
	}

	// группируем пн-чт + пт = будни
	group_stations($stations, $lines_by_mask, '1111000', '0000100', '1111100');

	// группируем сб + вс = выходные
	group_stations($stations, $lines_by_mask, '0000010', '0000001', '0000011');

	// группируем пт + выходные
	group_stations($stations, $lines_by_mask, '0000100', '0000011', '0000111');

	// группируем будни + выходные = ежедневно
	group_stations($stations, $lines_by_mask, '1111100', '0000011', '1111111');

	unset($lines_by_mask, $days_of_week);

	// собираем данные
	foreach ($stations as &$station) {
		$rows = array();
		foreach ($station['masks'] as $mask => $times) {
			$race_number = 1;
			foreach ($times as $time) {
				$rows[] = array('time' => $time, 'mask' => $mask, 'race_number' => $race_number++);
			}
		}
		unset($station['masks']);
		$station['times'] = $rows;
	}

    echo "\r",  str_repeat(' ', 20), "\r";
    return $stations;
}

/**
 * Парсинг временной таблицы
 *
 * @param string $table_url
 * @return array {mask => [time1, time2, ..], ..}
 */
function process_table($table_url) {

    $days_of_week = array('1111000' => 'monday', '0000010' => 'saturday', '0000001' => 'sunday', '0000100' => 'friday');
	$urls = array();
    foreach ($days_of_week as $mask => $day) {
        $date = date('d.m.Y', strtotime("this $day"));
		$urls[$mask] = "http://m.gortransperm.ru$table_url?date=$date";
	}
	$filter = function($response) {
		$times = array();
		if (!preg_match_all('#<li>\s*<span[^>]*>\s*(\d+)\s*</span>(.+?)</li>#s', $response, $matches)) {
            // в некоторые дни нет движения
            return null;
        }
		// часы
        foreach ($matches[1] as $k => $hour) {
            $minutes_html = $matches[2][$k];
            if (!preg_match_all('#<sup[^>]*>\s*(\d+)\**\s*</sup>#s', $minutes_html, $matches2)) {
                trigger_error("preg_match_all == false");
                continue;
            }
            // минуты
            foreach ($matches2[1] as $minute) {
                $times[] = $hour.':'.$minute;
            }
        }
		return $times;
	};
    return request_multi($urls, $filter);
}

function group_stations(&$stations, &$lines_by_mask, $from1, $from2, $to) {
	if (!isset($lines_by_mask[$from1], $lines_by_mask[$from2])) {
		return;
	}
	if (!isset($lines_by_mask[$to])) {
		$lines_by_mask[$to] = array();
	}
	$from1_numbers = array();
	$from2_numbers = array();
	foreach ($lines_by_mask[$from1] as $n1 => $line) {
		$n2 = array_search($line, $lines_by_mask[$from2]);
		if ($n2 !== false) {
			$from1_numbers[] = $n1;
			$from2_numbers[] = $n2;
			$lines_by_mask[$to][] = $line;
			unset($lines_by_mask[$from1][$n1]);
			unset($lines_by_mask[$from2][$n2]);
		}
	}
	foreach ($stations as &$station) {
		if (!isset($station['masks'][$to])) {
			$station['masks'][$to] = array();
		}
		foreach ($from1_numbers as $n1) {
			if (!isset($station['masks'][$from1][$n1])) {
				//trigger_error("{$station['station_name']} $from1 $n1");
				continue;
			}
			unset($station['masks'][$from1][$n1]);
		}
		foreach ($from2_numbers as $n2) {
			if (!isset($station['masks'][$from2][$n2])) {
				//trigger_error("{$station['station_name']} $from2 $n2");
				continue;
			}
			$station['masks'][$to][] = $station['masks'][$from2][$n2];
			unset($station['masks'][$from2][$n2]);
		}
	}
}

/**
 * Получение id от race_type по маске
 *
 * @global mysqli $db
 * @staticvar array $race_types
 * @param string $mask
 */
function get_race_type($mask) {
    static $race_types = array();
    if (!$race_types) {
        global $db;
        $result = $db->query("SELECT id_race_type, mgs_value FROM race_type");
        if (!$result) {
            trigger_error("result == false ($db->error)");
            $db->close();
            exit;
        }
        if ($result->num_rows <= 0) {
            trigger_error("num_rows == 0");
            $result->close();
            $db->close();
            exit;
        }
        while ($row = $result->fetch_row()) {
            $race_types[$row[1]] = $row[0];
        }
        $result->close();
    }
    if (!isset($race_types[$mask])) {
        $id = add_race_type($mask);
        if (!$id) {
            trigger_error("id == false ($mask)");
            return false;
        }
        $race_types[$mask] = $id;
        return $id;
    }
    return $race_types[$mask];
}

/**
 * Добавление нового race_type для маски
 *
 * @global mysqli $db
 * @param string $mask
 * @return int
 */
function add_race_type($mask) {
    $letters = array('п', 'в', 'с', 'ч', 'п', 'с', 'в');
    $ip = array('понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье');
    $rp = array('понедельника', 'вторника', 'среды', 'четверга', 'пятницы', 'субботы', 'воскресенья');
    $exist = array();
    $noexist = array();
    $mta = '';
    foreach ($letters as $m => $letter) {
        if ($mask{$m} == '1') {
            $exist[] = $ip[$m];
            $mta .= $letter;
        } else {
            $noexist[] = $rp[$m];
            $mta .= '_';
        }
    }
    $e = count($exist);
    if ($e <= 4) {  // четыре и меньше - перечисляем
        $human = implode(', ', $exist);

    } elseif ($e == 5) {   // пять дней - пишем, что кроме двух дней
        $human = "кроме $noexist[0] и $noexist[1]";

    } elseif ($e == 6) {   // шесть дней - пишем, что кроме одного дня
        $human = "кроме $noexist[0]";

    } else {    // иначе ошибка
        trigger_error("add_race_type error ($mask)");
        return false;
    }
    global $db;
    $result = $db->query("INSERT INTO race_type VALUES (NULL, '$human', '$mta', '$mask')");
    echo "INSERT INTO race_type VALUES (NULL, '$human', '$mta', '$mask')\n";
    if (!$result) {
        trigger_error("result == false ($db->error)");
        return false;
    }
    $id = (int)$db->insert_id;
    if ($id <= 0) {
        trigger_error("insert_id <= 0 ($mask)");
        return false;
    }
    return $id;
}

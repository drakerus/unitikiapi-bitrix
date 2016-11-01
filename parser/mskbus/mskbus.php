<?php

/**
 * MasterKlavi MSK Bus Parser
 *
 * Парсинг автобусов с сайта http://www.mosgortrans.ru/
 *
 * @version 0.3 (2016/10)
 * @author Мастер Клавы <masterklavi@gmail.com>
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
require './functions.php';
if (!is_dir(CACHE_DIR)) {
	mkdir(CACHE_DIR);
}

if (empty($argv[1])) {
	$routes = process_routes_list('avto');
	$name = cache_write('routes', $routes);
	echo "cache data: $name\n";
} else {
	$routes = cache_read($argv[1]);
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
$result = $db->query("SELECT MAX(id) FROM time");
if (!$result) {
	trigger_error("delete error: $db->error");
	exit;
}
$row = $result->fetch_row();
$max = (int)$row[0];
$result->close();

/*
if ($max <= 0) {
	trigger_error("max <= 0");
	exit;
}
*/
echo "max = $max\n";

if ($max > 0) {
    for ($i = 1; $i <= $max; $i += 300000) {
        $result = $db->query("DELETE FROM time WHERE id BETWEEN $i AND ".($i+300000)." AND id_town = 1");
        if (!$result) {
            trigger_error("delete error: $db->error");
            exit;
        }
    }
}
$result = $db->query("DELETE FROM station WHERE id_town = 1");
if (!$result) {
    trigger_error("delete error: $db->error");
    exit;
}
$result = $db->query("DELETE FROM bus WHERE id_town = 1");
if (!$result) {
    trigger_error("delete error: $db->error");
    exit;
}

echo "clearing time: ", microtime(true) - $time, "\n";
$total = count($routes);
$done = 0;
$_time = microtime(true);

$errors = 0;

foreach ($routes as $route) {
	echo $done++." of $total\n";
	$masks = cache_read($route['masks']);
	if (!$masks) {
		trigger_error("masks == false");
                $errors++;
		continue;
	}
	$mask = current($masks);
	$result = $db->query(sprintf(
			"INSERT INTO bus VALUES (NULL, '%s', 1, '%s', '%s', '', '%s', 1, 0)",
			$db->real_escape_string($route['bus_name']),
			$db->real_escape_string($mask['f_way']),
			$db->real_escape_string($mask['b_way']),
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
	foreach ($masks as $mask => $mask_data) {
		$id_race_type = get_race_type($mask);
		if (!$id_race_type) {
			trigger_error("id_race_type == false ($mask)");
                        $errors++;
			continue;
		}
		if (!$mask_data) {
			trigger_error("mask_data == false");
                        $errors++;
			continue;
		}
		foreach (array('f_times', 'b_times') as $dir => $key) {
			foreach ($mask_data[$key] as $station => $times) {
				if (!$times) {
					trigger_error("times == false");
                                        $errors++;
					continue;
				}
				$station_name = $db->real_escape_string($station);
				$id_station = db_fetch_value("SELECT id_station FROM station WHERE station_name = '$station_name' AND id_town = 1");
				if ($id_station <= 0) {
					$result = $db->query(sprintf(
							"INSERT INTO station (station_name, alias, id_town) VALUES ('%s', '%s', 1)",
							$station_name,
							$db->real_escape_string(transletiration($station))
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
				$race_number = 1;
				foreach ($times as $time) {
					$query_parts[] = sprintf(
							"(NULL,'%s',1,%d,%d,%d,%d,%d,%d)",
							$time,
							$id_bus,
							$id_station,
							$id_race_type,
							0,
							$race_number++,
							$dir
					);
					if (count($query_parts) >= 100) {
						$result = $db->query("INSERT INTO time VALUES ".implode(',', $query_parts));
						if (!$result) {
							trigger_error("result == false ($db->error)");
                                                        $errors++;
							continue;
						}
						$query_parts = array();
					}
				}
				if (count($query_parts) > 0) {
					$result = $db->query("INSERT INTO time VALUES ".implode(',', $query_parts));
					if (!$result) {
						trigger_error("result == false ($db->error)");
                                                $errors++;
						continue;
					}
					unset($query_parts);
				}
			}
		}
	}
}

echo "query time: ", microtime(true) - $_time, "\n";
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

if ($errors === 0 && count($routes) > 10)
{
    print 'status: ok'.PHP_EOL;
}

exit;



/**
 * Парсинг списка маршрутов
 *
 * @see http://www.mosgortrans.org/pass3/request.ajax.php?list=ways&type=avto
 *
 * @param	string	$type
 */
function process_routes_list($type) {

    // Запрос списка маршрутов
    $result = request("http://www.mosgortrans.org/pass3/request.ajax.php?list=ways&type=$type");
    if (!$result) {
        trigger_error("result == false");
        exit;
    }
	$rows = explode("\n", $result);
	$urls = array();
    foreach ($rows as $route) {
		if ($route === "") {
			continue;
		}
        if (in_array($route, array('route', 'stations', 'streets'))) {
            continue;
        }
		$urls[$route] = "http://www.mosgortrans.org/pass3/request.ajax.php?list=days&type=$type&way=".urlencode(iconv('utf8', 'cp1251', $route));
	}
	$urls_parts = array_chunk($urls, 100, true);
	if (empty($urls_parts)) {
		trigger_error("urls_parts == false");
		return false;
	}
	unset($rows, $result);
	$total_parts = count($urls_parts);
	$routes = array();
	echo "$type: $total_parts parts\n";
	foreach ($urls_parts as $part => $urls) {
		$part++;
		$results = request_multi($urls);
		$total = count($results);
		$done = 1;
		foreach ($results as $route => $result) {
			echo "$part/$total_parts part; $done/$total routes ($route)\n";
			// обработка данного автобуса (маршрута)
			$masks = process_route($type, urlencode(iconv('utf8', 'cp1251', $route)), $result);
			if (!$masks) {
				trigger_error('masks == false');
				continue;
			}
			$routes[] = array('bus_name' => $route, 'masks' => cache_write('masks', $masks));
			unset($masks);
			$done++;
		}
		unset($result, $results);
	}
    return $routes;
}

/**
 * Парсинг автобуса (маршрута)
 *
 * @param	string	$type
 * @param	string	$route
 * @param	string	$result
 */
function process_route($type, $route, $result) {

    $rows = explode("\n", $result);
	$urls = array();
	foreach ($rows as $mask) {
		if (strlen($mask) !== 7) {
			continue;
		}
		$urls[$mask] = "http://www.mosgortrans.org/pass3/request.ajax.php?list=directions&type=$type&way=$route&date=$mask";
	}
	if (!$urls) {
		return false;
	}
	unset($result);

	$results = request_multi($urls);
	$masks = array();
	foreach ($results as $mask => $result) {
		$mask_data = process_mask($type, $route, $mask, $result);
		if (!$mask_data) {
			trigger_error('mask_data == false');
			continue;
		}
		$masks[$mask] = $mask_data;
	}
	return $masks;
}

/**
 * Парсинг направлений в данном расписании
 *
 * @param	string	$type
 * @param	string	$route
 * @param	string	$mask
 * @param	string	$result
 * @return	array
 */
function process_mask($type, $route, $mask, $result) {
	$rows = explode("\n", $result);
	if (count($rows) < 2) {
		trigger_error("count(rows) < 2");
		return false;
	}
	$f_way = $rows[0];
	$b_way = $rows[1];
	unset($result, $rows);
	$urls = array(
		"http://www.mosgortrans.org/pass3/shedule.php?type=$type&way=$route&date=$mask&direction=AB&waypoint=all",
		"http://www.mosgortrans.org/pass3/shedule.php?type=$type&way=$route&date=$mask&direction=BA&waypoint=all",
	);
	$results = request_multi($urls);

	$f_times = process_stations($results[0]);
	$b_times = process_stations($results[1]);
	unset($results);

	if (!$f_times || !$b_times) {
		trigger_error("times == false");
	}

    return array('f_way' => $f_way, 'f_times' => $f_times, 'b_way' => $b_way, 'b_times' => $b_times);
}

/**
 * Получение списка остановок
 *
 * @param	string	$result
 * @return	array	{ station_name: ['06:00', '06:35', ...], ...}
 */
function process_stations($result) {

	$times = array();
	if (!preg_match_all('#<tr><td><h2>([^<]+)</h2></td></tr><tr><td[^>]+><table.*?</table>#s', $result, $matches)) {
		trigger_error("preg_match_all == false");
		return false;
	}
	$regex = '#<td[^>]+>\s+<span[^>]*class="hour"[^>]*>([^<]+)</span></td>\s+<td[^>]+>(.+?)</td>#s';
	foreach($matches[0] as $k => $table){
		$station = trim($matches[1][$k]);
		$times[$station] = array();
		if (!preg_match_all($regex, $table, $hours)) {
			trigger_error("preg_match_all == false");
			return false;
		}
		foreach($hours[1] as $k => $hour){
			if(!preg_match_all('#<span[^>]*class="minutes"[^>]*>([^<]+)<#s', $hours[2][$k], $minutes)){
				trigger_error("preg_match_all == false");
				return false;
			}
			foreach($minutes[1] as $minute){
				$times[$station][] = $hour.':'.$minute;
			}
		}
	}
	return $times;
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

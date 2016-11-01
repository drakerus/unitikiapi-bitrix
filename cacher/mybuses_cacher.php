<?php

/**
 * MasterKlavi Mybuses Cacher
 *
 * Генерация кэша городского расписания на сайте mybuses.ru
 *
 * @version 0.3 (2016/10)
 * @author Мастер Клавы (masterklavi.ru)
 *
 * @see https://www.fl.ru/tu/order/292322/
 * @see заказчик: Красиков Алексей, skype: aleksey_shifaka
 */


define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'admin_mybuses2');
define('MYSQL_PASSWORD', 'VOy8TOwT2p');
define('MYSQL_DATABASE', 'admin_mybuses2');


/**
 * Путь к директории, которая содержит прототипные файлы шаблона
 */
define('PROTOTYPE_DIR', 'templates');

/**
 * Путь к директории, в которую будет проведена запись результата
 */
//define('RESULT_DIR', 'result');
define('RESULT_DIR', '/home/admin//web/mybuses.ru/public_html/mods/bus/town/');


$total_time = microtime(true);
chdir(__DIR__);
mb_internal_encoding('utf8');
date_default_timezone_set('Etc/GMT+3');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
require './functions.php';

if (!isset($argv[1]) || ($argv[1] <= 0 && $argv[1] !== 'all')) {
	echo "Use an ID of town or 'all' as argument. Examples:\nphp5 mybuses_cacher.php all\nphp5 mybuses_cacher.php 23\n";
	exit;
}
if (!is_dir(PROTOTYPE_DIR)) {
	trigger_error('is_dir('.PROTOTYPE_DIR.') == false');
	exit;
}
if (!is_dir(RESULT_DIR)) {
	mkdir(RESULT_DIR);
}

$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if ($db->connect_error) {
    trigger_error("connect_error: $db->connect_error");
    return;
}
$db->set_charset('utf8');

if ($argv[1] !== 'all') {
	$town_ids = array((int)$argv[1]);
} else {
	$town_ids = db_fetch_int_col("SELECT id_town WHERE active = 1");
	if (!$town_ids) {
		trigger_error("town_ids == false");
		exit;
	}
}

$bus_types = db_fetch_assoc_array("SELECT id_bus_type, bus_type_name FROM bus_type");
if (!$bus_types) {
	trigger_error("bus_types == false");
	exit;
}
$race_types = db_fetch_assoc_array("SELECT id_race_type, human_value FROM race_type");
if (!$race_types) {
	trigger_error("race_types == false");
	exit;
}

foreach ($town_ids as $id_town) {

	// 1. Страница города

	echo "1. Index\n";
	$time = microtime(true);

	$town = db_fetch_object("SELECT town_name, town_alias FROM town WHERE id_town = $id_town");
	if (!$town) {
		trigger_error("town == false");
		continue;
	}
	echo $town->town_alias, "\n";
	$town->town_name_escaped =  str_replace('"', '`', $town->town_name);
	$town->is_reg_town = db_fetch_value("SELECT COUNT(*) as cnt FROM reg_town WHERE town_alias = '{$town->town_alias}'") > 0;

	$town_dir = RESULT_DIR."/$town->town_alias";
	if (!is_dir($town_dir)) {
		mkdir($town_dir);
	}
	$html_dir = "$town_dir/html";
	if (!is_dir($html_dir)) {
		mkdir($html_dir);
	}
	$index_dir = "$town_dir/html/index";
	if (!is_dir($index_dir)) {
		mkdir($index_dir);
	}

	$result = $db->query("SELECT id_bus, bus_name, id_bus_type, f_way, b_way, alias FROM bus WHERE id_town = $id_town ORDER BY id_bus");
	if (!$result) {
		trigger_error("result == false: $db->error");
		continue;
	}
	if ($result->num_rows <= 0) {
		$result->close();
		continue;
	}
	$buses_by_type = array();
	$buses_arrays = array();
	$buses_by_id = array();
	while ($obj = $result->fetch_object()) {
		$name = "$obj->bus_name - $obj->f_way";
		$obj->name = html_entity_decode($name);
		$obj->name_escaped = str_replace('"', '`', $obj->name);
		$obj->id_bus = (int)$obj->id_bus;
		if (!$obj->name) {
			trigger_error("bus_name == false: $obj->id_bus");
			continue;
		}
		if (!isset($bus_types[$obj->id_bus_type])) {
			trigger_error("unknown bus type: $obj->id_bus_type");
			continue;
		}
		$obj->bus_type = $bus_types[$obj->id_bus_type];
		if (!isset($buses_by_type[$obj->bus_type])) {
			$buses_by_type[$obj->bus_type] = array();
		}
		$buses_by_type[$obj->bus_type][] = $obj;
		$buses_arrays[] = array('id' => $obj->alias, 'name' => $name);
		$buses_by_id[$obj->id_bus] = $obj;
	}
	$result->close();

        foreach ($buses_by_type as &$buses_part)
        {
            usort($buses_part, '_buses_part_sort');
        }

	$n = 12 / count($buses_by_type);

	$result = $db->query("SELECT id_station, station_name, alias FROM station WHERE id_town = $id_town");
	if (!$result) {
		trigger_error("result == false: $db->error");
		continue;
	}
	if ($result->num_rows <= 0) {
		$result->close();
		continue;
	}
	$stations_by_id = array();
	$stations_arrays = array();
	while ($obj = $result->fetch_object()) {
		$obj->alias = html_entity_decode($obj->alias);
		$obj->name = html_entity_decode($obj->station_name);
		$obj->name_escaped = str_replace('"', '`', $obj->name);
		if (!$obj->name) {
			trigger_error("station_name == false: $obj->id_station");
			continue;
		}
		$stations_by_id[$obj->id_station] = $obj;
		$stations_arrays[] = array('id' => $obj->alias, 'name' => $obj->station_name);
	}
	$result->close();

        uasort($stations_by_id, '_buses_part_sort');

	$stations_by_col = array_chunk($stations_by_id, ceil(count($stations_by_id)/4));

	$date = date('d ').get_month_name(date('m')).date(' Y');

	// 1.1. Списки автобусов города

	ob_start();
	require PROTOTYPE_DIR."/index/index_top.php";
	file_put_contents("$index_dir/index_top.html", ob_get_clean());

	// 1.2. Списки остановок города

	ob_start();
	require PROTOTYPE_DIR."/index/index_bottom.php";
	file_put_contents("$index_dir/index_bottom.html", ob_get_clean());

	// 1.3. JS массив для скрипта поиска по автобусам

	ob_start();
	require PROTOTYPE_DIR."/index/ssb.php";
	file_put_contents("$index_dir/ssb.js", ob_get_clean());

	// 1.4. JS массив для скрипта поиска по остановкам

	ob_start();
	require PROTOTYPE_DIR."/index/sss.php";
	file_put_contents("$index_dir/sss.js", ob_get_clean());

	// 1.5. Дата генерации города

	ob_start();
	require PROTOTYPE_DIR."/modification_date.php";
	file_put_contents("$html_dir/modification_date.html", ob_get_clean());

	echo microtime(true) - $time, " seconds\n";

	unset($buses_by_type, $buses_arrays, $stations_arrays, $stations_by_col);

	// 2. Страница автобусов

	echo "2. Bus\n";
	$time = microtime(true);

	$bus_dir = "$town_dir/html/bus";
	if (!is_dir($bus_dir))
        {
            mkdir($bus_dir);
	}
	if (!is_dir($bus_dir.'/m'))
        {
            mkdir($bus_dir.'/m');
	}
	$bus_f_dir = "$town_dir/html/bus/f";
	if (!is_dir($bus_f_dir))
        {
            mkdir($bus_f_dir);
	}
	$bus_b_dir = "$town_dir/html/bus/b";
	if (!is_dir($bus_b_dir))
        {
            mkdir($bus_b_dir);
	}

        foreach ($buses_by_id as $bus)
        {
            $stations_and_times = array(0 => array(), 1 => array());
            $stations_all = array(0 => array(), 1 => array());

            foreach (array(0 => $bus_f_dir, 1 => $bus_b_dir) as $race_direction => $dir)
            {
                $id_bus = (int)$bus->id_bus;

                $result = $db->query("SELECT id, time, id_station, id_race_type, payment_type, race_number FROM time WHERE id_town = $id_town AND id_bus = $id_bus AND race_direction = $race_direction ORDER BY id");
                if (!$result)
                {
                    trigger_error("result == false: $db->error");
                    continue;
                }
                if ($result->num_rows <= 0)
                {
                    $result->close();
                    continue;
                }
                $times = array();
                $stations_ids_by_type = array();
                $rows = array();
                $counts = array();
                $first = array();
                while ($obj = $result->fetch_object())
                {
                    if (strlen($obj->time) === 4)
                    {
                        $obj->time = '0'.$obj->time;
                    }

                    if (!isset($stations_and_times[$race_direction][$obj->id_station]))
                    {
                        $stations_and_times[$race_direction][$obj->id_station] = array();
                    }
                    $stations_and_times[$race_direction][$obj->id_station][$obj->time] = $obj;

                    $obj->race_number = (int)$obj->race_number;
                    $obj->id_station = (int)$obj->id_station;
                    if (!isset($stations_ids_by_type[$obj->id_race_type]))
                    {
                        $stations_ids_by_type[$obj->id_race_type] = array($obj->id_station);
                    }
                    else
                    {
                        $stations_ids_by_type[$obj->id_race_type][] = $obj->id_station;
                    }

                    $k1 = $obj->id_station.'-'.$obj->race_number.'-'.$obj->id_race_type;
                    if (isset($counts[$k1]))
                    {
                        $counts[$k1]++;
                    }
                    else
                    {
                        $counts[$k1] = 1;
                    }

                    $k20 = sprintf('%02d-%04d-%04d', $obj->race_number, $obj->id_race_type, $counts[$k1]);
                    if (!isset($first[$k20]))
                    {
                        $first[$k20] = $obj->time;
                    }
                    $k2 = sprintf('%d-%05s-%s', $first[$k20] < 4, $first[$k20], $k20);
                    if (!isset($rows[$k2]))
                    {
                        $rows[$k2] = (object)array(
                            'id_race_type' => $obj->id_race_type,
                            'payment_type' => $obj->payment_type,
                            'race_human_value' => $race_types[$obj->id_race_type],
                            'first' => $obj->time,
                        );
                    }

                    if (!isset($times[$k2]))
                    {
                        $times[$k2] = array();
                    }
                    $times[$k2][$obj->id_station] = $obj->time;
                }
                $result->close();
                unset($counts);

                $stations_ids = array();
                $stations_ids_count = 0;
                if (isset($stations_ids_by_type[2], $stations_ids_by_type[3]))
                {
                    unset($stations_ids_by_type[1]);
                }
                foreach ($stations_ids_by_type as &$ids)
                {
                    $ids = array_values(array_unique($ids));
                }
                foreach ($stations_ids_by_type as $ids)
                {
                    if (count($ids) > $stations_ids_count)
                    {
                        $stations_ids_count = count($ids);
                    }
                }
                for ($i = 0; $i < $stations_ids_count; $i++)
                {
                    foreach ($stations_ids_by_type as $ids)
                    {
                        if (!isset($ids[$i]))
                        {
                            continue;
                        }
                        if (!in_array($ids[$i], $stations_ids))
                        {
                            $stations_ids[] = $ids[$i];
                        }
                    }
                }
                unset($stations_ids_by_type);

                $stations_all[$race_direction] = $stations_ids;

                // 2.1. Фильтр по остановкам
                // 2.2. Таблица графика движения маршрута

                ksort($rows);
                $stations = array();
                foreach ($stations_ids as $id)
                {
                    $stations[] = $stations_by_id[$id];
                }

                ob_start();
                require PROTOTYPE_DIR."/bus/bus_alias_sh_selector.php";
                file_put_contents("{$dir}/{$bus->alias}_sh_selector.html", ob_get_clean());

                ob_start();
                require PROTOTYPE_DIR."/bus/bus_alias.php";
                file_put_contents("{$dir}/{$bus->alias}.html", ob_get_clean());
            }

            list($begin_station, $end_station) = explode(' – ', $bus->f_way);

            $stations = array();
            
            reset($stations_all[0]);
            $stations_all[1] = array_reverse($stations_all[1]);
            reset($stations_all[1]);

            for ($i = 0; $i < 1000; $i++)
            {
                $s1 = current($stations_all[0]);
                $s2 = current($stations_all[1]);

                if ($s1)
                {
                    $stations[] = $s1;
                }
                if ($s2)
                {
                    $stations[] = $s2;
                }

                if (!$s1 && !$s2)
                {
                    break;
                }

                next($stations_all[0]);
                next($stations_all[1]);
            }

            $stations = array_unique($stations);

            ob_start();
            require PROTOTYPE_DIR."/m/bus.php";
            file_put_contents("{$bus_dir}/m/{$bus->alias}.html", ob_get_clean());
	}

	unset($times, $stations_ids, $rows, $stations, $stations_all, $stations_and_times);

	echo microtime(true) - $time, " seconds\n";

	// 3. Страница остановок

	echo "3. Station\n";
	$time = microtime(true);

	$station_dir = "$town_dir/html/station";
	if (!is_dir($station_dir))
        {
            mkdir($station_dir);
	}
	if (!is_dir($station_dir.'/m'))
        {
            mkdir($station_dir.'/m');
	}
	$station_f_dir = "$town_dir/html/station/f";
	if (!is_dir($station_f_dir))
        {
            mkdir($station_f_dir);
	}
	$station_b_dir = "$town_dir/html/station/b";
	if (!is_dir($station_b_dir))
        {
            mkdir($station_b_dir);
	}

        foreach ($stations_by_id as $station)
        {
            $buses_and_times = array(0 => array(), 1 => array());
            $buses_all = array(0 => array(), 1 => array());

            foreach (array($station_f_dir, $station_b_dir) as $race_direction => $dir)
            {
                $id_station = (int)$station->id_station;

                $result = $db->query("SELECT id, time, id_bus, id_race_type, race_number FROM time WHERE id_town = $id_town AND id_station = $id_station AND race_direction = $race_direction ORDER BY id");
                if (!$result)
                {
                    trigger_error("result == false: $db->error");
                    continue;
                }
                if ($result->num_rows <= 0)
                {
                    $result->close();
                    continue;
                }
                $station_race_types = array();
                $times = array();
                $buses_ids = array();
                while ($obj = $result->fetch_object())
                {
                    if (strlen($obj->time) === 4)
                    {
                        $obj->time = '0'.$obj->time;
                    }
                    
                    if (!isset($buses_and_times[$race_direction][$obj->id_bus]))
                    {
                        $buses_and_times[$race_direction][$obj->id_bus] = array();
                    }
                    $buses_and_times[$race_direction][$obj->id_bus][$obj->time] = $obj;

                    $obj->race_number = (int)$obj->race_number;
                    $obj->id_bus = (int)$obj->id_bus;
                    $station_race_types[$obj->id_race_type] = $race_types[$obj->id_race_type];
                    if (!in_array($obj->id_bus, $buses_ids))
                    {
                        $buses_ids[] = $obj->id_bus;
                    }

                    if (!isset($times[$obj->id_bus]))
                    {
                        $times[$obj->id_bus] = array();
                    }
                    $times[$obj->id_bus][sprintf('%d_%s', $obj->time{0} == '0' && $obj->time{1} < 4, $obj->time)] = $obj;
                }
                $result->close();

                $buses_all[$race_direction] = $buses_ids;

                ksort($station_race_types);
                $buses = array();
                foreach ($buses_ids as $id)
                {
                    $buses[] = $buses_by_id[$id];
                }
                foreach ($times as &$t)
                {
                    ksort($t);
                }

                ob_start();
                require PROTOTYPE_DIR."/station/station_alias_sh_legend.php";
                file_put_contents("{$dir}/{$station->alias}_sh_legend.html", ob_get_clean());

                ob_start();
                require PROTOTYPE_DIR."/station/station_alias_sh_selector.php";
                file_put_contents("{$dir}/{$station->alias}_sh_selector.html", ob_get_clean());

                ob_start();
                require PROTOTYPE_DIR."/station/station_alias.php";
                file_put_contents("{$dir}/{$station->alias}.html", ob_get_clean());

                unset($buses_ids, $buses);
            }

            $buses = array();
            foreach ($buses_all[0] as $bus_id)
            {
                $buses[$bus_id] = $buses_by_id[$bus_id];
            }
            foreach ($buses_all[1] as $bus_id)
            {
                $buses[$bus_id] = $buses_by_id[$bus_id];
            }

            ob_start();
            require PROTOTYPE_DIR."/m/station.php";
            file_put_contents("{$station_dir}/m/{$station->alias}.html", ob_get_clean());
        }

	unset($buses_by_id, $stations_by_id, $race_types);

	echo microtime(true) - $time, " seconds\n";
}

$db->close();

echo "\nTotal time: ", microtime(true) - $total_time, " seconds\n";
echo "Mem usage: ", memory_get_peak_usage(true)/1024/1024, " Mb\n";

print 'status: ok'.PHP_EOL;

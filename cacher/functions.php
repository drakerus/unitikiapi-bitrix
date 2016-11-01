<?php

function db_fetch_object($sql) {
	global $db;
    $result = $db->query($sql);
    if (!$result) {
        trigger_error("result == false: $db->error");
        return false;
    }
    if ($result->num_rows <= 0) {
        $result->close();
        return false;
    }
    $obj = $result->fetch_object();
    $result->close();
    return $obj;
}

function db_fetch_int_col($sql) {
	global $db;
    $result = $db->query($sql);
    if (!$result) {
        trigger_error("result == false: $db->error");
        return false;
    }
    if ($result->num_rows <= 0) {
        $result->close();
        return false;
    }
	$col = array();
	while ($row = $result->fetch_row()) {
		$col[] = (int)$row[0];
	}
    $result->close();
    return $col;
}

function db_fetch_value($sql) {
	global $db;
    $result = $db->query($sql);
    if (!$result) {
        trigger_error("result == false: $db->error");
        return false;
    }
    if ($result->num_rows <= 0) {
        $result->close();
        return false;
    }
    $row = $result->fetch_row();
    $result->close();
    return $row[0];
}

function db_fetch_assoc_array($sql) {
	global $db;
    $result = $db->query($sql);
    if (!$result) {
        trigger_error("result == false: $db->error");
        return false;
    }
    if ($result->num_rows <= 0) {
        $result->close();
        return false;
    }
	$array = array();
    while ($row = $result->fetch_row()) {
		$array[$row[0]] = $row[1];
	}
    $result->close();
    return $array;
}
/*
function db_escape($var) {
	global $db;
	return $db->real_escape_string($var);
}*/

function get_month_name($m) {
	switch ((int)$m) {
		case 1: return 'января';
		case 2: return 'февраля';
		case 3: return 'марта';
		case 4: return 'апреля';
		case 5: return 'мая';
		case 6: return 'июня';
		case 7: return 'июля';
		case 8: return 'августа';
		case 9: return 'сентября';
		case 10: return 'октября';
		case 11: return 'ноября';
		case 12: return 'декабря';
		default: return false;
	}
}

function json_encode_unicode($data) {
	if (defined('JSON_UNESCAPED_UNICODE')) {
		return json_encode($data, JSON_UNESCAPED_UNICODE);
	}
	return preg_replace_callback(
			'/(?<!\\\\)\\\\u([0-9a-f]{4})/i',
			function ($m) {
				$d = pack("H*", $m[1]);
				$r = mb_convert_encoding($d, "UTF8", "UTF-16BE");
				return $r!=="?" && $r!=="" ? $r : $m[0];
			},
			json_encode($data)
	);
}

function _buses_part_sort($a, $b)
{
    return strnatcasecmp($a->name, $b->name);
}

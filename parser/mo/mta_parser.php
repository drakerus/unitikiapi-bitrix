<?php
if(!stristr(php_sapi_name(), 'cli')) die();
error_reporting(E_ALL ^ E_DEPRECATED);
set_time_limit(0);
ignore_user_abort( true );
date_default_timezone_set("Europe/Moscow");
ini_set('short_open_tags', 'on');

define('ROOT_PATH', '/home/admin/web/mybuses.ru/public_html/');
define('MODS', ROOT_PATH.'mods/');
define('LIB', MODS.'bus/');
require(LIB.'db_mods/dbconn.php');
require(LIB.'lib.php');

try{
   $parser = new MtaParser($myConnect);
   $cities = array();
   if(!empty($argv[1])) $cities = explode(',', $argv[1]);
   $parser->doJob( $cities );
   $errors = $parser->getErrors();
   if(empty($errors)){
	//echo 'OK';
        print 'status: ok'.PHP_EOL;
   } else{
	echo 'Errors:'.PHP_EOL;
	foreach($errors as $error){
		echo $error.PHP_EOL;
	}
   }
} catch (Exception $e){
    die($e->getMessage().PHP_EOL);
}

class MtaParser{

    private $_link;
    private $_towns = array();
    private $_bus_types = array();
    private $_race_types = array();
    private $_errors = array();
    private $_proxy = array();
    private  $UA = array();

    private static $RETRIES = 5;


    public function __construct( $dbLink = null ){
        if( false === @mysql_ping( $dbLink )  ){
            throw new Exception('Invalid MySQL link!');
        }
        $this->_link = $dbLink;
        $this->_query("SET SQL_BIG_SELECTS=1");
        $this->UA = array_map('trim', file(__DIR__ .'/ua.txt'));
        $this->_loadTypes();
        $this->_loadRaceTypes();
    }

    public function getErrors(){
        return $this->_errors;
    }

    private function _loadTypes(){
        $this->_bus_types =  array(
            'городские' => 1,
            'пригородные' => 2,
            'междугородные' => 3,
            'Автобусы до Москвы' => 4,
        );
       /*$types = $this->_fetchAssoc("SELECT * FROM `bus_type`");
        foreach($types as $type){
            $this->_bus_types[$type['bus_type_name']] = $type['id_bus_type'];
        }*/
    }

    private function _loadRaceTypes(){
        $types = $this->_fetchAssoc("SELECT `id_race_type`, `mgs_value` FROM `race_type`");
        foreach($types as $type){
            $this->_race_types[$type['mgs_value']] = $type['id_race_type'];
        }
    }

    public function doJob( array $cities ){
        $where = '';
        if(!empty($cities)){
            $where = " AND `id_town` IN(".implode(',', array_map('intval', $cities)).")";
        }
        $this->_towns = $this->_fetchAssoc("SELECT `id_town`, `mta_href`, `town_name` FROM `town`
                                                WHERE `active` = 1 AND `mta_href` != ''".$where);
        //$this->_query("TRUNCATE TABLE `time`");
	#$this->_query("TRUNCATE TABLE `station`");
        foreach($this->_towns as $town){
            $this->_query("DELETE FROM `time` WHERE `id_town` = ".intval($town['id_town']));
            $this->_query("DELETE FROM `station` WHERE `id_town` = ".intval($town['id_town']));
	    $page = $this->_getResponse($town['mta_href']);
            if(!preg_match('#<main.*?<table[^>]*>(.*?)</table#is', $page, $table)){
		$this->_setError('Расписание не подпапдает под шаблон: '.$town['mta_href']);
		continue;
	    }

            if(!preg_match_all('#<tr[^>]*>[^<]*<td[^>]*>(.*?)</td[^<]*<td[^>]*>(.*?)</td[^<]*<td[^>]*>(.*?)</td[^<]*<td[^>]*>(.*?)</td[^<]*<td[^>]*>(.*?)</td[^<]*<td[^>]*>(.*?)</td[^<]*<td[^>]*>(.*?)</td[^<]*</tr#is', $table[1], $out)){
                $this->_setError('Расписание не подпапдает под шаблон: '.$town['mta_href']);
                continue;
	    }
            #preg_match_all('#<tr[^>]*>([^<]*<td[^>]*>(.*?)</td){7}[^<]*</tr#is', $table[1], $out);

            $buses = array();
            $aliases = array();
            $hrefs = array();
            foreach($out[1] as $key => $bus_number){
                if($key == 0) continue;

                preg_match('#href=[\'|"](.*?)[\'|"]#is', $out[2][$key], $href);
                if(empty($href[1])) continue;
                if(in_array($href[1], $hrefs)) continue;
                $hrefs[] = $href[1];
                $is_moscow = strip_tags(str_replace('&nbsp;', '', $out[7][$key]));
                if($is_moscow != ''){
                    $bus_type = 4;
                } else{
                    $bus_type = trim(strip_tags($out[4][$key]));
                    if(!array_key_exists($bus_type, $this->_bus_types)){
                        throw new Exception('Несуществующий тип автобуса: '.$bus_type);
                    }
                    $bus_type = $this->_bus_types[$bus_type];
                }
                $alias = transletiration(strip_tags($bus_number));
                while( in_array($alias, $aliases) ){
                    $alias .= '_';
                }
                $aliases[] = $alias;
                if( false === $races = $this->_getRaces($href[1], $town['id_town'])){
                    $this->_setError('Bad bus: town_name='.$town['town_name'].', town_id='.$town['id_town'].', bus '.strip_tags($bus_number));
                    continue; //skip bus
                }
                $buses[] = array(
                    'bus_name' => strip_tags($bus_number),
                    'id_bus_type' => $bus_type,
                    'f_way' => strip_tags($out[2][$key]),
                    'b_way' => implode( ' – ', array_reverse(explode(' – ', strip_tags($out[2][$key]))) ),
                    'href' => '',
                    'alias' => $alias,
                    'id_subway' => $this->_getSubwayId($is_moscow),
                    'races' => $races
                );
            }
            $this->_query("DELETE FROM `bus` WHERE `id_town` = ".intval($town['id_town']));
            foreach($buses as $bus){
                $this->_query("INSERT INTO `bus`(`bus_name`, `id_bus_type`, `f_way`, `b_way`, `href`, `alias`, `id_town`, `id_subway`)
                                VALUES(
                                '".mysql_real_escape_string($bus['bus_name'])."',
                                ".intval($bus['id_bus_type']).",
                                '".mysql_real_escape_string($bus['f_way'])."',
                                '".mysql_real_escape_string($bus['b_way'])."',
                                '',
                                '".mysql_real_escape_string($bus['alias'])."',
                                ".intval($town['id_town']).",
                                ".intval($bus['id_subway'])."
                                )");
                $bus_id = mysql_insert_id($this->_link);

                foreach($bus['races'] as $direction => $schedule){
                    foreach($schedule as $race_number => $_race){
                        foreach($_race['stations'] as $station_id => $time){
                            $this->_query("INSERT INTO `time`(`time`,`id_town`,`id_bus`,`id_station`,`id_race_type`,`payment_type`,`race_number`,`race_direction`)
                                            VALUES(
                                              '".mysql_real_escape_string($time)."',
                                              ".intval($town['id_town']).",
                                              ".intval($bus_id).",
                                              ".intval($station_id).",
                                              ".intval($_race['race_type']).",
                                              ".intval($_race['payment_type']).",
                                              ".intval($race_number+1).",
                                              ".intval($direction)."
                                            )");
                        }
                    }
                }
            }
        }
    }

    private function _getRaces($href, $town_id){
        $page = $this->_getResponse('http://www.mostransavto.ru/passengers/routes/raspisaniya/'.$href);
        $directions = explode('Обратные рейсы', $page);
        preg_match_all('#<tr[^>]*class=[\'|"]stops[\'|"][^>]*>(.*?)<[^>]*table[^>]*>#is', $directions[0], $forward_tables);
        if( false === $to = $this->_parseRaces($forward_tables[1], $town_id)){
            $this->_setError('Bad forward races: http://www.mostransavto.ru/passengers/routes/raspisaniya/'.$href);
            return false;
        }

        $from = array();
        if(sizeof($directions) == 2){
            preg_match_all('#<tr[^>]*class=[\'|"]stops[\'|"][^>]*>(.*?)<[^>]*table[^>]*>#is', $directions[1], $backward_tables);
            if( false === $from = $this->_parseRaces($backward_tables[1], $town_id)){
                $this->_setError('Bad backward races: http://www.mostransavto.ru/passengers/routes/raspisaniya/'.$href);
                return false;
            }
        }

        return array(
            0 => $to,
            1 => $from
        );

    }

    private function _parseRaces( $tables,  $town_id  ){
        $races = array();
        foreach($tables as $schedule){
            $schedule = preg_replace('#<tr[^>]*>#is', '<tr>', $schedule);
            $schedule = preg_replace('#<td[^>]*>#is', '<td>', $schedule);
            $rows = explode('<tr>', $schedule);
            $mta = explode('<td>', $rows[0]);
            $pay = explode('<td>', $rows[1]);
            unset($rows[0], $rows[1], $mta[0], $pay[0]);
            $schedule = $this->_getSchedule($rows, $town_id);
            foreach($mta as $key => $race_type){
                if(false === $pt = $this->_getPaymentType($pay[$key])){
                    return false;
                }
                if(false === $rt = $this->_getRaceType(strip_tags($race_type))){
                    return false;
                }
                $races[] = array(
                    'race_type' => $rt,
                    'payment_type' => $pt,
                    'stations' => $schedule[$key]
                );
            }
        }
        return $races;
    }

    private function _getSchedule($rows, $town_id){
        $schedule = array();
        foreach($rows as $station){
            $data = explode('</td>', $station);
            $data = array_map(function($a){return str_replace('&nbsp;', '', strip_tags($a));}, $data);
            $s_name = trim($data[0]);
            unset($data[0], $data[max(array_keys($data))]);
            foreach($data as $key => $time){
                $station_id = $this->_getStationId($s_name, $town_id);
                if (empty($schedule[$key][$station_id])) {
                    $schedule[$key][$station_id] = $time;
                }
            }
        }
        return $schedule;
    }

    private function _getRaceType( $string ){
        $mgs_value = preg_replace(array('#[^_]#u', '#[_]#u'), array('1', '0'), $string);
        if($mgs_value == '0000000') $mgs_value = '1111111';
        if(isset($this->_race_types[$mgs_value])) return $this->_race_types[$mgs_value];
        $ip = array('понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье');
        $rp = array('понедельника', 'вторника', 'среды', 'четверга', 'пятницы', 'субботы', 'воскресенья');
        $pointers = array_map('intval', str_split($mgs_value));
        if(array_sum($pointers) <= 4){
            $human_value = implode(', ', array_intersect_key($ip, array_flip(array_keys($pointers, 1))));
        } elseif(array_sum($pointers) <= 6){
            $human_value = 'кроме ' . implode(' и ', array_intersect_key($rp, array_flip(array_keys($pointers, 1))));
        } elseif(array_sum($pointers) == 7){
            $human_value = 'ежедневно';
        } else{
            $this->_setError('Bad mta-mask: '. $string.' ('.$mgs_value.')');
            return false;
        }
        $this->_query("INSERT INTO `race_type`(`human_value`,`mta_value`,`mgs_value`) VALUES(
                            '".mysql_real_escape_string($human_value)."',
                            '".mysql_real_escape_string($string)."',
                            '".mysql_real_escape_string($mgs_value)."'
                      )");
        $this->_race_types[$mgs_value] = mysql_insert_id($this->_link);
        return $this->_race_types[$mgs_value];
    }

    private function _getStationId($s_name, $town_id){
        $s_name = str_replace('﻿', '', $s_name);
        $result = $this->_fetchAssoc("SELECT IF(`alias_to` IS NOT NULL, `alias_to`, `id_station`) AS `station_id`
                                          FROM `station` WHERE `station_name` = '".mysql_real_escape_string($s_name)."' COLLATE utf8_bin
                                          AND `id_town` = ".intval($town_id));
        if(empty($result)){
            $this->_query("INSERT INTO `station`(`station_name`, `alias`, `id_town`)
                            VALUES('".mysql_real_escape_string($s_name)."', '".mysql_real_escape_string(transletiration($s_name))."', ".intval($town_id).")");
            return mysql_insert_id($this->_link);
        }
        return $result[0]['station_id'];
    }


    private function _getPaymentType($string){
        $types = array(
            'соц' => 0,
            'дог' => 1
        );
        $string = trim(strip_tags($string));

        if(!array_key_exists($string, $types)){
            $this->_setError('Unknown payment type: '.$string);
            return false;
        }

        return $types[$string];
    }

    private function _getSubwayId( $name ){
        if(empty($name)) return '';
        $sw = $this->_fetchAssoc("SELECT `id_subway` FROM `subway` WHERE `subway_name` = '".mysql_real_escape_string($name)."'");
        if(empty($sw)){
            $this->_query("INSERT INTO `subway`(`subway_name`) VALUES('".mysql_real_escape_string($name)."')");
            return mysql_insert_id($this->_link);
        }
        return $sw[0]['id_subway'];
    }

    private function _getResponse($url, $parameters = '', $post = false, $retry = 0){
        $this->_getProxy();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if($post === true){
            curl_setopt($curl, CURLOPT_POST, true);
            if(!empty($parameters)) curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        } else{
            curl_setopt($curl, CURLOPT_POST, false);
            if(!empty($parameters)) curl_setopt($curl, CURLOPT_URL, $url.'?'.$parameters);;
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->UA[ mt_rand(0, sizeof($this->UA) - 1) ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        curl_setopt($curl, CURLOPT_PROXYPORT, $this->_proxy['port']);
        curl_setopt($curl, CURLOPT_PROXYTYPE, 'HTTP');
        curl_setopt($curl, CURLOPT_PROXY, $this->_proxy['ip']);
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->_proxy['username'].':'.$this->_proxy['password']);

        if( false === $content = curl_exec($curl)){
            // throw new Exception('Ошибка получения страницы "'.$url.'"'."\r\n".''.curl_error($curl), 2);
            $this->_markBanned( $this->_proxy['proxy_id'], curl_error($curl) );
            return $this->_getResponse($url, $parameters, $post, $retry);
        }
        $curl_info = @curl_getinfo($curl);
        if($curl_info['http_code']!=200 && $curl_info['http_code']!=301 && $curl_info['http_code']!=302){
            if( $retry > self::$RETRIES - 1){
			       throw new Exception('Ошибка получения страницы "'.$url.'."'."\r\n".' Превышено количество попыток: '.self::$RETRIES."\r\n".'Код ответа сервера отличается от ожидаемого:'.$curl_info['http_code'], 3);
		    }
		    $retry+=1;
            $this->_markBanned( $this->_proxy['proxy_id'], 'Response '.$curl_info['http_code'] );
		    return $this->_getResponse($url, $parameters, $post, $retry);
        }
        curl_close($curl);
        return $this->_cleanContent($content);
    }

    private function _cleanContent($content){
        return trim(str_replace( array( "\r", "\n" ), '', preg_replace('#<\!\-\-.*?\-\->#is', '', $content) ));
    }


    // proxy

    private function _markBanned( $proxy_id, $cause = '' ){
        $this->_query("UPDATE `mb_proxy`
                        SET `banned` = 1,
                              `ban_time` = NOW(),
                              `ban_cause` = '".mysql_real_escape_string($cause)."'
                         WHERE `proxy_id` = ".intval($proxy_id));
    }

    private function _getProxy(){
        $this->_query("UPDATE `mb_proxy` SET `banned` = 0, `ban_cause` = '' WHERE `ban_time` < NOW() - 3600 AND `ban_cause` != 'Bad IP'");
        $proxy = $this->_fetchAssoc("SELECT `proxy_id`, `ip`, `port`, `username`, `password` FROM `mb_proxy`
                                                    WHERE `banned` = 0
                                                        ORDER BY `last_used_time` ASC
                                                            LIMIT 1");
        if(empty($proxy)){
            throw new Exception('Нет доступных прокси');
        }
        $proxy = $proxy[0];
        if( false === ip2long($proxy['ip']) ){
            $this->_markBanned($proxy['proxy_id'], 'Bad IP');
            $this->_getProxy();
        }
        $this->_proxy = $proxy;
    }


    //------------------------- DB helper methods -------------------------------

    private function _fetchAssoc( $query ){
        $result = $this->_query( $query );
        $rows = array();
        while( $row = mysql_fetch_assoc( $result ) ){
            $rows[] = $row;
        }
        return $rows;
    }

    private function _query( $query ){
        if (!mysql_ping($this->_link))
        {
            require(LIB.'db_mods/dbconn.php');
            $this->_link = $myConnect;
            if( false === $result = mysql_query( "SET SQL_BIG_SELECTS=1", $this->_link ) ){
                throw new Exception('MySQL-query error: '.mysql_error( $this->_link ).PHP_EOL.print_r("SET SQL_BIG_SELECTS=1", 1));
            }
        }
        if( false === $result = mysql_query( $query, $this->_link ) ){
            throw new Exception('MySQL-query error: '.mysql_error( $this->_link ).PHP_EOL.print_r($query, 1));
        }
        return $result;
    }

    private function _setError($error){
        $this->_errors[] = $error;
    }
}




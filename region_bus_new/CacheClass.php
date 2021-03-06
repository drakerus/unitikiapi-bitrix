<?php

class CacheClass{

    private static $towns_template = 'towns';
    private static $town_terminus_template = 'town_terminus';
    private static $terminus_template = 'terminus';
    private static $way_template = 'way';
    private static $date_selector_template = 'date';
    private static $terminus_selector_template = 'terminus_selector';
    private static $js_search_template = 'js_search';
    private static $bla_bla_template = 'bla_bla';
    
    private static $blablaCar = null;

    private $_link = null;
    private $_towns = array();
    private $_terminus = array();
    private $_hook_town_name = '';

    public function __construct( $dbLink = null ){
        if( false === @mysql_ping( $dbLink )  ){
            throw new Exception('Invalid MySQL link!');
        }
        $this->_link = $dbLink;
        $this->_query("SET SQL_BIG_SELECTS=1");
        #self::$blablaCar = blablacars::getInstance();
    }

    public function generateCache(){
        $tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Moscow');
        exec('rm -rf '.CACHE_DIR.'*');
        $this->_getTowns();
        $this->_generateTowns();
        $this->_getTerminus();
        $this->_processTerminus();
        date_default_timezone_set( $tz );
        exec('chown -R apache.apache '.CACHE_DIR.'*');
        exec('rm -rf '.TARGET_DIR.'*; mv -f '.CACHE_DIR.'* '.TARGET_DIR);

    }

    private function _processTerminus(){
        foreach( $this->_terminus as $town_id => $terminus ){
            $town_dir = CACHE_DIR.$this->_towns[$town_id]['town_alias'].'/';
            self::_checkDir( $town_dir );
            $way_dir = $town_dir.'way/';
            self::_checkDir( $way_dir );
            #self::_checkDir( $way_dir.'blabla/' );
            $terminus_dir = $town_dir.'terminus/';
            self::_checkDir( $terminus_dir );

            // все вокзалы города
            self::_generateFile(
                self::$town_terminus_template,
                array(
                    'terminus' => $terminus,
                    'town' => $this->_towns[$town_id],
                    'num_cols' => (  (  12 / sizeof($terminus) > 4 ) ? ( 12 / sizeof($terminus) ) : 4 )
                ),
                $town_dir.'all_terminus.html'
            );

            // JS-скрипт поиска
            self::_generateFile(
                self::$js_search_template,
                array(
                    'town' => $this->_towns[$town_id],
                    'stations' => $this->_getThreadStations($town_id)
                ),
                $town_dir.'reg_town_search.js'
            );

            // каждый вокзал отдельно
            foreach($terminus as $t){
                self::_generateFile(
                    self::$terminus_template,
                    array(
                        'terminus' => $t,
                        'town' => $this->_towns[$town_id]
                    ),
                    $terminus_dir.$t['alias'].'.html'
                );

                self::_generateFile(
                    self::$js_search_template,
                    array(
                        'town' => $this->_towns[$town_id],
                        'stations' => $this->_getTerminusStations($t['id_station'], $town_id)
                    ),
                    $terminus_dir.$t['alias'].'_reg_town_search.js'
                );

                foreach($t['ways'] as $way) {
                    $all_threads = $this->_getAllThreadsByName($way['thread_name']);
                    $this->_processWay($all_threads, $way['thread_name'], $way['thread_alias'], $way_dir, $town_id);
                }


            }
        }
    }

    private function _processWay( $all_threads, $thread_name, $thread_alias, $way_dir, $town_id, $shift = 0 ){

            foreach($all_threads as $k => $thread){

                /* $blabla_data = self::$blablaCar->loadFull($thread['from_town'], $thread['to_town']);
                if( null !== @$blabla_data->pager->total && @$blabla_data->pager->total > 0){
                    self::_generateFile(self::$bla_bla_template, array(
                        'total' => $blabla_data->pager->total . $this->_timings($blabla_data->pager->total, 'offer'),
                        'price' => $blabla_data->trips[0]->price->value,
                        'duration' => $this->_getTravelTime(@$blabla_data->duration),
                        'town_to' => $thread['to_town'],
                        'town_from' => $thread['from_town']
                    ), $way_dir . 'blabla/' . $thread_alias . '.html');
                } */

                $all_threads[$k]['thread_name'] = $thread_name;
                $all_threads[$k]['travel_time'] = $this->_getTravelTime( $thread['to_a_shift'] );
                $all_threads[$k]['schedules'] = $this->_getSchedules( $thread['id_thread'], $thread['to_a_shift'],
                    array(
                        'date' => '',
                        'station_id_start' => $thread['from_station_code'],
                        'station_id_end' => $thread['to_station_code'],
                        'time' => ''
                    ),
                    $thread['time_zone_id']
                    );
                if( empty($all_threads[$k]['schedules']) ){
                    unset($all_threads[$k]);
                    continue;
                }

                $all_threads[$k]['prices'] = explode('|', $thread['price']);
            }

            // если нитей нет или для всех нитей расписание пустое
            if( empty($all_threads) ){
                $this->_generateFile(   self::$way_template,
                    array(
                        'backlink' => $this->_towns[$town_id]['town_alias'],
                        'way_name' => $thread_name
                    ),
                    $way_dir.$thread_alias.'.html');
                return;
            }

        foreach($all_threads as $k => $tt) {
            $dates = array();
            foreach ($all_threads[$k]['schedules'] as $schedule) {
                foreach ($schedule as $date => $data) {
                    $timestamp = strtotime($date);
                    $date_s = date('dmY', $timestamp);
                    if (isset($dates[$date_s])) continue;
                    $dates[$date_s] = array(
                        'date' => $timestamp,
                        'month' => $data['start_month'],
                        'day' => $data['start_day']
                    );
                }
            }

            // генерация пути

            $stations = $this->_getWayStations($all_threads[$k]['id_thread'], $all_threads[$k]['to_station_code'], $town_id, $shift);

            $this->_generateFile(self::$way_template, array('threads' => $all_threads, 'stations' => self::_prepareStations($stations)), $way_dir . $thread_alias . '.html');

            // генерация селектора вокзалов
            if (sizeof($all_threads) > 1) {
                $this->_generateFile(self::$terminus_selector_template, array('terminus' => self::_fetchTerminusFromThreads($all_threads), 'thread_alias' => $thread_alias), $way_dir . $thread_alias . '_terminus_selector.html');
            }

            // генерация дат для селектора
            $this->_generateFile(self::$date_selector_template, array('dates' => $dates), $way_dir . $thread_alias . '_sh_selector.html');

            if ($shift == 0) {
                // генерация для промежуточных станций
                foreach ($stations as $station) {
                    $shift_threads = $this->_getAllThreadsByStations($all_threads[$k]['from_station_code'], $station['station_code']);
                    $this->_processWay($shift_threads, $station['thread_name'], $station['thread_alias'] . '_shift', $way_dir, $town_id, $station['d_shift']);
                }
            }
        }
    }

    private static function _prepareStations( Array $stations){
        if( empty($stations) ) return $stations;
        $r_stations = array();
        foreach($stations as $station){
            if(isset( $r_stations[$station['town_name']]  )) continue;
            $r_stations[$station['town_name']] = array(
                'thread_alias' => $station['thread_alias'],
                'thread_name' => $station['thread_name'],
            );
        }
        return $r_stations;
    }
    
    private static function _fetchTerminusFromThreads( Array $threads ){
        $terminus = array('from' => array(), 'to' => array());
        if(empty($threads)) return $terminus;
        foreach($threads as $thread){
            if(!isset($terminus['from'][$thread['from_station_code']])){
                $terminus['from'][$thread['from_station_code']] = array(
                    'from_town_alias' => $thread['from_town_alias'],
                    'from_station_alias' => $thread['from_station_alias'],
                    'from_station' => $thread['from_station'],
                    'from_town' => $thread['from_town']
                );
            }

            if(!isset($terminus['to'][$thread['to_station_code']])){
                $terminus['to'][$thread['to_station_code']] = array(
                    'to_town_alias' => $thread['to_town_alias'],
                    'to_station_alias' => $thread['to_station_alias'],
                    'to_station' => $thread['to_station'],
                    'to_town' => $thread['to_town']
                );
            }
        }
        return $terminus;
    }


    private function _getSchedules( $thread_id, $shift, $data_link, $timezone_id ){
        $schedules =  $this->_fetchAssoc("
            SELECT *
                FROM `reg_shedule`
            WHERE `id_thread` IN (".$thread_id.")
                AND `shedule_end_date` > NOW()
        ");
        $current_time = time();
        $return_schedule = array( );
        foreach($schedules as $k => $schedule){
            if( strtotime( $schedule['shedule_start_date'] ) < $current_time ){
                $schedules[$k]['shedule_start_date'] = date('Y-m-d', $current_time);
            }
            $schedules[$k]['dates'] = array();
            $days = str_split( $schedule['days'] );
            for( $day = strtotime( $schedules[$k]['shedule_start_date'] ); $day <= strtotime( $schedules[$k]['shedule_end_date'] ); $day = strtotime('+1 day', $day)  ){
                if( in_array( date('N', $day), $days ) ){
                    $start_time = date('Y-m-d H:i:s', strtotime( date('Y-m-d', $day).' '.$schedule['times'].':00'));
                    // check timezone
                    if(!empty($timezone_id)){
                        $date = new DateTime($start_time, new DateTimeZone($timezone_id));
                        $date->setTimezone(new DateTimeZone('Europe/Moscow'));
                        $local_date = new DateTime('now', new DateTimeZone("Europe/Moscow"));
                        if( $date->format('YmdHi') < $local_date->format('YmdHi') ){
                            continue;
                        }
                        unset($date, $local_date);
                    }
                    //
                    $schedules[$k]['dates'][$start_time]['stop_time'] = date('Y-m-d H:i:s', strtotime('+'.$shift.' seconds', strtotime($start_time)));
                    $schedules[$k]['dates'][$start_time]['start_month'] = $this->_translateMonth( date('n', strtotime($start_time)) );
                    $schedules[$k]['dates'][$start_time]['stop_month'] = $this->_translateMonth( date('n', strtotime($schedules[$k]['dates'][$start_time]['stop_time'])) );
                    $schedules[$k]['dates'][$start_time]['start_day'] = $this->_translateDay( date('w', strtotime($start_time)) );
                    $schedules[$k]['dates'][$start_time]['stop_day'] = $this->_translateDay( date('w', strtotime($schedules[$k]['dates'][$start_time]['stop_time'])) );
                    $data_link['date'] = date('Y-m-d', $day);
                    $data_link['time'] = $schedule['times'].':00';
                    $schedules[$k]['dates'][$start_time]['link_data'] =  base64_encode( serialize($data_link) );
                }
            }
            if(empty( $schedules[$k]['dates'] )){
                unset($schedules[$k]);
                continue;
            }
            $return_schedule = array_merge( $return_schedule, $schedules[$k]['dates']);
        }
        ksort($return_schedule, SORT_STRING);
        return ( (empty($return_schedule)) ? array() : array($return_schedule) );
    }

    private function _getAllThreadsByName( $thread_name ){
        return $this->_fetchAssoc("
            SELECT DISTINCT
                t1.`town_name` AS `from_town`,
                t1.`town_alias` AS `from_town_alias`,
                r1.`station_name` AS `from_station`,
                r1.`station_code` AS `from_station_code`,
                r1.`station_alias` AS `from_station_alias`,
                r1.`time_zone_id` AS `time_zone_id`,
                reg1.`region_name` AS `from_region`,
                c1.`country_name` AS `from_country`,
                d1.`district_name` AS `from_district`,
                sp1.`a_shift` AS `from_a_shift`,
                t2.`town_name` AS `to_town`,
                t2.`town_alias` AS `to_town_alias`,
                r2.`station_name` AS `to_station`,
                r2.`station_code` AS `to_station_code`,
                r2.`station_alias` AS `to_station_alias`,
                reg2.`region_name` AS `to_region`,
                c2.`country_name` AS `to_country`,
                d2.`district_name` AS `to_district`,
                sp2.`a_shift` AS `to_a_shift`,
                GROUP_CONCAT( DISTINCT `reg_thread`.`id_thread` SEPARATOR ',') AS `id_thread`,
                GROUP_CONCAT( DISTINCT `reg_price`.`price`, `currency`.`currency_name`, '.' ORDER BY  `reg_price`.`price` ASC SEPARATOR '|') AS `price`
            FROM (SELECT `id_thread` FROM  `reg_thread` FORCE INDEX(`thread_name`) WHERE  `thread_name` =  '".mysql_real_escape_string($thread_name)."') AS T
            LEFT JOIN `reg_thread` USING(`id_thread`)
            JOIN `reg_stoppoint` sp1 ON sp1.`id_thread` = `reg_thread`.`id_thread` AND sp1.`a_shift` = 0
            JOIN `reg_stoppoint` sp2 ON sp2.`id_thread` = `reg_thread`.`id_thread` AND sp2.`a_shift` != 0
            JOIN `reg_station` r1 ON r1.`id_station` = sp1.`id_station`
            JOIN `reg_station` r2 ON r2.`id_station` = sp2.`id_station`
            JOIN `reg_town` t1 ON t1.`id_town` = r1.`id_town`
            JOIN `reg_town` t2 ON t2.`id_town` = r2.`id_town`
            LEFT JOIN `district` d1 ON t1.`id_district` = d1.`id_district`
            LEFT JOIN `district` d2 ON t2.`id_district` = d2.`id_district`
            LEFT JOIN `region` reg1  ON t1.`id_region`  = reg1.`id_region`
            LEFT JOIN `region` reg2  ON t2.`id_region`  = reg2.`id_region`
            LEFT JOIN `country` c1 ON t1.`id_country` = c1.`id_country`
            LEFT JOIN `country` c2 ON t2.`id_country` = c2.`id_country`
            JOIN `reg_fare` ON `reg_fare`.`id_fare`=`reg_thread`.`id_fare`
            JOIN `reg_price` FORCE INDEX(from_to_fare) ON `reg_price`.`id_fare`=`reg_fare`.`id_fare` AND ( `reg_price`.`station_from_id` = sp1.`id_station` AND `reg_price`.`station_to_id` = sp2.`id_station` )
            LEFT JOIN `currency` FORCE INDEX(PRIMARY)  ON `reg_price`.`id_currency`=`currency`.`id_currency`
            WHERE sp1.`d_shift`=0
                AND sp2.`d_shift`=0
            GROUP BY `from_station_code`, `to_station_code`, `to_a_shift`
        ");
    }

    private function _getAllThreadsByStations( $from_station_code, $to_station_code ){

        return $this->_fetchAssoc("
            SELECT DISTINCT
                t1.`town_name` AS `from_town`,
                t1.`town_alias` AS `from_town_alias`,
                r1.`station_name` AS `from_station`,
                r1.`station_code` AS `from_station_code`,
                r1.`station_alias` AS `from_station_alias`,
                r1.`time_zone_id` AS `time_zone_id`,
                reg1.`region_name` AS `from_region`,
                c1.`country_name` AS `from_country`,
                d1.`district_name` AS `from_district`,
                sp1.`a_shift` AS `from_a_shift`,
                t2.`town_name` AS `to_town`,
                t2.`town_alias` AS `to_town_alias`,
                r2.`station_name` AS `to_station`,
                r2.`station_code` AS `to_station_code`,
                r2.`station_alias` AS `to_station_alias`,
                reg2.`region_name` AS `to_region`,
                c2.`country_name` AS `to_country`,
                d2.`district_name` AS `to_district`,
                sp2.`a_shift` AS `to_a_shift`,
                GROUP_CONCAT( DISTINCT `reg_thread`.`id_thread` SEPARATOR ',') AS `id_thread`,
                GROUP_CONCAT( DISTINCT `reg_price`.`price`, `currency`.`currency_name`, '.' ORDER BY  `reg_price`.`price` ASC SEPARATOR '|') AS `price`
            FROM (
                  SELECT start_points.id_thread
                    FROM `reg_stoppoint` start_points
                      LEFT JOIN `reg_stoppoint` stop_points ON stop_points.id_thread = start_points.id_thread
                      WHERE
                        ( start_points.id_station IN (SELECT DISTINCT r2.id_station FROM `reg_station` r
                            LEFT JOIN reg_station r2 USING(id_town)
                            WHERE r.station_code = ".intval($from_station_code).")
                              AND start_points.d_shift = 0 AND start_points.a_shift = 0
                         )
                          AND (
                            stop_points.id_station IN (SELECT DISTINCT r2.id_station FROM `reg_station` r
                            LEFT JOIN reg_station r2 USING(id_town)
                            WHERE r.station_code = ".intval($to_station_code).")
                          AND stop_points.a_shift != 0
                    )
            ) AS T
            LEFT JOIN `reg_thread` USING(`id_thread`)
            JOIN `reg_stoppoint` sp1 ON sp1.`id_thread` = `reg_thread`.`id_thread` AND sp1.`a_shift` = 0
            JOIN `reg_stoppoint` sp2 ON sp2.`id_thread` = `reg_thread`.`id_thread` AND sp2.`a_shift` != 0
            JOIN `reg_station` r1 ON r1.`id_station` = sp1.`id_station` AND r1.id_town = ( SELECT `id_town` FROM `reg_station` WHERE station_code = ".intval($from_station_code)." )
            JOIN `reg_station` r2 ON r2.`id_station` = sp2.`id_station` AND r2.id_town = ( SELECT `id_town` FROM `reg_station` WHERE station_code = ".intval($to_station_code)." )
            JOIN `reg_town` t1 ON t1.`id_town` = r1.`id_town`
            JOIN `reg_town` t2 ON t2.`id_town` = r2.`id_town`
            LEFT JOIN `district` d1 ON t1.`id_district` = d1.`id_district`
            LEFT JOIN `district` d2 ON t2.`id_district` = d2.`id_district`
            LEFT JOIN `region` reg1  ON t1.`id_region`  = reg1.`id_region`
            LEFT JOIN `region` reg2  ON t2.`id_region`  = reg2.`id_region`
            LEFT JOIN `country` c1 ON t1.`id_country` = c1.`id_country`
            LEFT JOIN `country` c2 ON t2.`id_country` = c2.`id_country`
            JOIN `reg_fare`  ON `reg_fare`.`id_fare`=`reg_thread`.`id_fare`
            JOIN `reg_price` FORCE INDEX(from_to_fare) ON `reg_price`.`id_fare`=`reg_fare`.`id_fare` AND ( `reg_price`.`station_from_id` = sp1.`id_station` AND `reg_price`.`station_to_id` = sp2.`id_station` )
            LEFT JOIN `currency` FORCE INDEX(PRIMARY)  ON `reg_price`.`id_currency`=`currency`.`id_currency`
            WHERE sp1.`d_shift`=0 AND sp1.a_shift = 0
                AND sp2.`d_shift` != 0
            GROUP BY `from_station_code`, `to_station_code`, `to_a_shift`
        ");
    }

    private function _getTerminus(){
        $terminus = $this->_fetchAssoc(
            "SELECT `reg_town`.`id_town`, `reg_station`.`station_name`, `reg_station`.`id_station`
                    FROM `reg_town`
                    LEFT JOIN `reg_station` USING(`id_town`)
                    LEFT JOIN `reg_stoppoint` USING(`id_station`)
                    LEFT JOIN `reg_station_rating` USING(`station_code`)
                WHERE  `reg_town`.`id_town` IN (".implode( ',', array_map( 'intval', array_keys($this->_towns) ) ).")
                    AND `reg_stoppoint`.`d_shift`=0
                    AND `reg_stoppoint`.`a_shift`=0
                GROUP BY `id_town`, `reg_station`.`id_station`
                ORDER BY `id_town` ASC,`reg_station_rating`.`rating_value` DESC"
        );
        foreach($terminus as $t){
            if( !isset($this->_terminus[$t['id_town']]) ){
                $this->_terminus[$t['id_town']] = array();
            }
            $this->_hook_town_name = $this->_towns[$t['id_town']]['town_name'];
            $t['ways'] = array_map( array( $this, 'correctThreadName' ), $this->_getTerminusWays( $t['id_station'] ) );
            //$t['ways'] = $this->_getTerminusWays( $t['id_station'] );
            $t['alias'] = transletiration( $t['station_name'] );
            $this->_terminus[$t['id_town']][] = $t;
        }
    }

    private function _getTerminusWays( $t_id ){
        return $this->_fetchAssoc("
            SELECT `reg_thread`.`thread_name`, `reg_thread`.`id_thread`, `reg_thread`.`thread_alias`
              FROM `reg_thread`
              JOIN `reg_stoppoint` ON `reg_thread`.`id_thread`=`reg_stoppoint`.`id_thread`
              JOIN `reg_station` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station`
            WHERE `reg_station`.`id_station`= ".intval( $t_id )."
              AND `reg_stoppoint`.`d_shift`=0
              AND `reg_stoppoint`.`a_shift`=0
            GROUP BY `reg_thread`.`thread_name`
        ");
    }

    private function _getTowns(){
        $towns = $this->_fetchAssoc(
            "SELECT `reg_town`.`id_town`, `reg_town`.`town_name`, `reg_town`.`town_alias`
                FROM `reg_town`
                JOIN `reg_town_rating` USING(`town_code`)
                JOIN `reg_station` USING(`id_town`)
                JOIN `reg_stoppoint` FORCE INDEX(`common`) USING(`id_station`)
            WHERE `reg_stoppoint`.`d_shift`= 0
                AND `reg_stoppoint`.`a_shift`= 0
            GROUP BY  `reg_town`.`id_town`
            ORDER BY `reg_town`.`town_name` ASC"
        );
        foreach($towns as $t){
            $this->_towns[$t['id_town']] = $t ;
        }
    }

    private function _getThreadStations( $town_id ){
        return $this->_fetchAssoc( "SELECT STRAIGHT_JOIN DISTINCT `station_code`, CONCAT('".mysql_real_escape_string($this->_towns[$town_id]['town_alias'])."', '_-_', `town_alias`) AS `search_alias`, CONCAT(`town_name`, ' ', `station_name`) AS `search_name`
                                        FROM(
                                            SELECT DISTINCT `id_thread`
                                            FROM `reg_stoppoint`
                                            LEFT JOIN `reg_station` USING(`id_station`)
                                            WHERE `d_shift` = 0 AND `a_shift` = 0 AND `id_town` = ".intval($town_id)."
                                        ) T
                                        LEFT JOIN `reg_stoppoint` USING(`id_thread`)
                                        LEFT JOIN `reg_station` USING(`id_station`)
                                        LEFT JOIN `reg_town` USING(`id_town`)
                                        WHERE `a_shift` != 0" );

    }

    private function _getTerminusStations( $station_id, $town_id ){

        return $this->_fetchAssoc( "SELECT STRAIGHT_JOIN DISTINCT `station_code`, CONCAT('".mysql_real_escape_string($this->_towns[$town_id]['town_alias'])."', '_-_', `town_alias`) AS `search_alias`, CONCAT(`town_name`, ' ', `station_name`) AS `search_name`
                                        FROM(
                                            SELECT DISTINCT `id_thread`
                                            FROM `reg_stoppoint`
                                            LEFT JOIN `reg_station` USING(`id_station`)
                                            WHERE `distance` = 0 AND `a_shift` = 0 AND `id_station` = ".intval($station_id)."
                                        ) T
                                        LEFT JOIN `reg_stoppoint` USING(`id_thread`)
                                        LEFT JOIN `reg_station` USING(`id_station`)
                                        LEFT JOIN `reg_town` USING(`id_town`)
                                        WHERE a_shift != 0" );

    }

    private function _getWayStations( $thread_ids, $to_station_code, $town_id, $shift = 0 ){
        return $this->_fetchAssoc( "SELECT STRAIGHT_JOIN DISTINCT
                                            `id_thread`,
                                            `station_code`,
                                            MAX(`a_shift`) AS `a_shift`,
                                            MAX(`d_shift`) AS `d_shift`,
                                            `town_alias`,
                                            `town_name`,
                                            CONCAT('".mysql_real_escape_string($this->_towns[$town_id]['town_name'])." - ', `town_name`) AS `thread_name`,
                                            CONCAT('".mysql_real_escape_string($this->_towns[$town_id]['town_alias'])."_-_', `town_alias`) AS `thread_alias`
                                        FROM `reg_stoppoint`
                                        LEFT JOIN `reg_station` ON `reg_station`.`id_station` = `reg_stoppoint`.`id_station`
                                        LEFT JOIN `reg_town` USING(`id_town`)
                                        LEFT JOIN `reg_thread` USING(`id_thread`)
                                        WHERE `id_thread` IN  ( ".mysql_real_escape_string($thread_ids)." )
                                            AND d_shift != 0 ".( ($shift > 0) ? "AND d_shift < ".intval($shift) : "" )."
                                            AND `reg_station`.`id_town` != (SELECT `id_town` FROM `reg_station` WHERE `station_code` = ".intval($to_station_code)." )
                                            AND `reg_station`.`id_town` != ".intval($town_id)."
                                            GROUP BY `station_code`" );
    }

    private function _generateTowns(){
        $filtered_towns = array();
        foreach($this->_towns as $town){
            if($town['town_alias'] == 'moscow' || $town['town_alias'] == 'moscow_1') continue;
            $filtered_towns[] = $town;
        }
        self::_generateFile(
            self::$towns_template,
            array(
                'towns' => $filtered_towns,
                'per_col' => ceil( sizeof($filtered_towns) / COLS_NUM ) ),
            ROOT_PATH.'index_regions.html'
        );
    }

    //------------------------- Generator ---------------------------------------
    private static function _generateFile( $template, $data, $file ){
        $template = TPL.$template.'.tpl.php';
        if( !file_exists( $template ) || !is_file( $template ) || !is_readable( $template ) ){
            throw new Exception('Template reading error: '.$template);
        }
        extract($data);
        ob_start();
        require($template);
        if( false === @file_put_contents( $file, ob_get_clean() ) ){
            throw new Exception('Can\'t create cache-file: '.$file);
        }
    }

    private static function _checkDir( $dir ){
        if( !file_exists( $dir )
            || !is_dir( $dir ) ){
            if( false === mkdir( $dir, 0755, true) ){
                throw new Exception('Can\'t create dir: '.$dir);
            }
        } else{
            $files = glob( $dir . '*');
            foreach($files as $file){
                if(is_file($file))
                    unlink($file);
            }
        }
    }

    private function correctThreadName( $thread ){
        $str_name=explode(' - ', $thread['thread_name'], 2);

        if(!stristr( $str_name[0], $this->_hook_town_name )){
            $str_name[0] = $this->_hook_town_name;
        }
        $thread['seo_thread_name'] = implode(' - ', $str_name);

        # old
        /*if($str_name[0] != $this->_hook_town_name && isset($str_name[1])){
            $thread['thread_name'] = $this->_hook_town_name.' - '.$str_name[1];
        } elseif( $str_name[0] != $this->_hook_town_name && !isset($str_name[1]) ){
            $thread['thread_name'] = $this->_hook_town_name.' - '.$str_name[0];
        }*/
        return $thread;
    }

    private function _getTravelTime( $seconds ){
        if( !function_exists( 'gmp_div_qr' ) || !is_callable( 'gmp_div_qr' ) ){
            $function = array($this, '_div_qr');
        } else{
            $function = array($this, '_gmp_div_qr');
        }
        $interval_string = '';
        $interval = call_user_func( $function, $seconds, 60 * 60 * 24  );
        if( $interval[0] > 0 ){
            $interval_string =  $interval[0].$this->_timings( $interval[0], 'day');
        }
        $interval = call_user_func( $function, $interval[1], 60 * 60 );
        if( $interval[0] > 0 ){
            $interval_string .=  ' '.$interval[0].$this->_timings( $interval[0], 'hour');
        } elseif( $interval[0] == 0 && strlen($interval_string) > 0 ){
            $interval_string .=  ' 0 часов';
        }
        $interval = call_user_func( $function, $interval[1], 60 );
        if( $interval[0] > 0 ) {
            $interval_string .=  ' '.$interval[0].$this->_timings( $interval[0], 'minute');
        }
        return trim($interval_string);
    }

    private function _div_qr( $n, $q ){
        return array( floor( $n / $q ), $n % $q  );
    }

    private function _gmp_div_qr( $n, $q ){
        $result = gmp_div_qr($n, $q);
        return array( gmp_strval($result[0]), gmp_strval($result[1]) );
    }

    private function _timings( $n, $case ){
        $cases = array(
            'day' => array(' сутки', ' суток', ' суток'),
            'hour' => array(' час', ' часа', ' часов'),
            'minute' => array(' минута', ' минуты', ' минут'),
            'offer' => array(' предложение', ' предложения', ' предложений')
        );
        $k = floor( ($n % 100) / 10 );
        $m = $n % 10;
        switch( $m ){
            case 1:
                return ( $k == 1 ) ? $cases[$case][2] : $cases[$case][0];
                break;
            case 2:
                return ( $k == 1 ) ? $cases[$case][2] : $cases[$case][1];
                break;
            case 3:
                return ( $k == 1 ) ? $cases[$case][2] : $cases[$case][1];
                break;
            case 4:
                return ( $k == 1 ) ? $cases[$case][2] : $cases[$case][1];
                break;
            default:
                return $cases[$case][2];
        }
    }

    private function _translateMonth( $month_num ){
        $months = array(
            1 => 'января',
            2 => 'февраля',
            3 => 'мфрта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря',
        );
        return @$months[intval($month_num)];
    }

    private function _translateDay( $day ){
        $days = array(
            0 => 'воскресенье',
            1 => 'понедельник',
            2 => 'вторник',
            3 => 'среда',
            4 => 'четверг',
            5 => 'пятница',
            6 => 'суббота'
        );
        return @$days[intval($day)];
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
        if( false === $result = mysql_query( $query, $this->_link ) ){
            throw new Exception('MySQL-query error: '.mysql_error( $this->_link ).PHP_EOL.print_r($query, 1));
        }
        return $result;
    }


}



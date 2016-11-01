<?php
/**
 * Author: oleg.igoshin
 * Date: 12.07.2015
 * Time: 16:30
 */

class XMLParserClass{

    private $_link = null;
    private $_countries = array();
    private $_regions = array();
    private $_districts = array();
    private $_stations = array();
    private $_towns = array();
    private $_currency = array();


    public function __construct( $dbLink  ){
        if( false === @mysql_ping( $dbLink )  ){
            throw new Exception('Invalid MySQL link!');
        }
        $this->_link = $dbLink;
        $this->_query("SET SQL_BIG_SELECTS=1");
        $this->_loadGeo();

    }

    public function parseXML( $path ){
        $context = null;
        if( preg_match('#^http#i', $path) ){
            $context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
        }
        if( false === $xml = file_get_contents($path, false, $context) ){
            throw new Exception('Can\'t load file: '.$path.PHP_EOL.print_r(error_get_last(), 1));
        }
        libxml_use_internal_errors(true);
        if( false === $xml = simplexml_load_string( $xml ) ){
            throw new Exception('Can\'t load XML: '.$path.PHP_EOL.print_r(libxml_get_errors(), 1));
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $this->_loadGeo();
        $this->_loadCurrency();
        $this->_cleanTable('reg_fare');
        $this->_cleanTable('reg_price');
        $this->_cleanTable('reg_thread');
        $this->_cleanTable('reg_stoppoint');
        $this->_cleanTable('reg_shedule');
        $this->_cleanTable('reg_seo_way');
        foreach($xml->group as $group){
            $this->_getStations($group->stations);
            $this->_getVehicles($group->vehicles);
            $this->_getCarriers($group->carriers);
            $this->_getFares($group->fares);
            $this->_getThreads($group->threads);
        }
    }

    private function _getThreads( SimpleXMLElement $threads ){
        foreach($threads->thread as $thread){
            $attributes = $this->_toArray( $thread->attributes() );
            $this->_query("INSERT INTO `reg_thread`(`thread_name`,`thread_alias`,`id_fare`,`id_carrier`,`id_vehicle`,`type`)
                            VALUES(
                                '',
                                '',
                                COALESCE((SELECT `id_fare` FROM `reg_fare` WHERE `fare_code` = '".mysql_real_escape_string( $attributes['fare_code'] )."'),0),
                                COALESCE((SELECT `id_carrier` FROM `reg_carrier` WHERE `carrier_code` = '".mysql_real_escape_string( $attributes['carrier_code'] )."'), 0),
                                COALESCE((SELECT `id_vehicle` FROM `reg_vehicle` WHERE `vehicle_code` = '".mysql_real_escape_string( @$attributes['vehicle_code'] )."'), 0),
                                '".mysql_real_escape_string( transletiration($attributes['t_type']) )."'
                            )");
            $thread_id = $this->_lastID();

            $start_stoppoint = 0;
            $end_stoppoint = 0;
            $stop_points = array();
            foreach($thread->stoppoints->stoppoint as $idx => $stoppoint){
                $attributes = $this->_toArray( $stoppoint->attributes() );
                if( intval(@$attributes['distance']) == 0 && intval(@$attributes['arrival_shift']) == 0 ){
                    $start_stoppoint = $attributes['station_code'];
                } else{
                    $stop_points[] = $attributes['station_code'];
                }
                $this->_query("INSERT INTO `reg_stoppoint`(`id_station`,`id_thread`,`distance`,`d_shift`,`a_shift`)
                                VALUES(
                                      ".intval($this->_stations[$attributes['station_code']]).",
                                      ".intval($thread_id).",
                                      ".intval(@$attributes['distance']).",
                                      ".intval(@$attributes['departure_shift']).",
                                      ".intval(@$attributes['arrival_shift'])."
                                )");
            }
            $end_stoppoint = $attributes['station_code'];

            # new thread name and alias
            $t_name = $this->_getValue("SELECT CONCAT_WS(' - ',
                                                (SELECT `town_name` FROM `reg_town` WHERE `id_town` = (SELECT `id_town` FROM `reg_station` WHERE `station_code` = ".intval($start_stoppoint)." )),
                                                (SELECT `town_name` FROM `reg_town` WHERE `id_town` = (SELECT `id_town` FROM `reg_station` WHERE `station_code` = ".intval($end_stoppoint)." ))
                                                )");

            $t_alias = mysql_real_escape_string( transletiration($t_name) );
            $this->_query("UPDATE `reg_thread` SET
                              `thread_name` = '".mysql_real_escape_string($t_name)."',
                              `thread_alias` = '".$t_alias."'
                              WHERE `id_thread` = ".intval($thread_id));
            $this->_query("INSERT IGNORE INTO `reg_seo_way` VALUES('".$t_alias."', '".mysql_real_escape_string($t_name)."')");

            foreach($stop_points as $sp){
                if($sp == $start_stoppoint || $sp == $end_stoppoint) continue;
                $t_name = $this->_getValue("SELECT CONCAT_WS(' - ',
                                                (SELECT `town_name` FROM `reg_town` WHERE `id_town` = (SELECT `id_town` FROM `reg_station` WHERE `station_code` = ".intval($start_stoppoint)." )),
                                                (SELECT `town_name` FROM `reg_town` WHERE `id_town` = (SELECT `id_town` FROM `reg_station` WHERE `station_code` = ".intval($sp)." ))
                                                )");
                $this->_query("INSERT IGNORE INTO `reg_seo_way` VALUES('".mysql_real_escape_string( transletiration($t_name) )."', '".mysql_real_escape_string($t_name)."')");
            }


			
            foreach($thread->schedules->schedule as $schedule){
                $attributes = $this->_toArray( $schedule->attributes() );
                $this->_query("INSERT INTO `reg_shedule`(`days`,`shedule_start_date`,`shedule_end_date`,`id_thread`,`times`)
                                  VALUES(
                                    ".intval($attributes['days']).",
                                    '".mysql_real_escape_string($attributes['period_start_date'])."',
                                    '".mysql_real_escape_string($attributes['period_end_date'])."',
                                    ".intval($thread_id).",
                                    '".mysql_real_escape_string($attributes['times'])."'
                                  )");
            }
        }
    }

    private function _getFares( SimpleXMLElement $fares ){
        foreach($fares->fare as $fare){
            $attributes = $this->_toArray( $fare->attributes() );
            $fare_id = $this->_addFare( $attributes['code'] );
            foreach($fare->price as $price){
                $this->_addPrice($price, $fare_id);
            }

        }
    }

    private function _addPrice(SimpleXMLElement $price, $fare_id){
        $attributes = $this->_toArray( $price->attributes() );
        if( !isset($this->_currency[$attributes['currency']]) ){
            $this->_addCurrency($attributes['currency']);
        }
        $this->_query("INSERT INTO `reg_price`(`id_fare`,`id_currency`,`station_from_id`,`station_to_id`,`price`)
                        VALUES(
                          ".intval($fare_id).",
                          ".intval($this->_currency[$attributes['currency']]).",
                          ".intval($this->_stations[$price->stop_from['station_code']->__toString()]).",
                          ".intval($this->_stations[$price->stop_to['station_code']->__toString()]).",
                          ".intval($attributes['price'])."
                        )");
    }

    private function _addCurrency( $currency_code ){
        $this->_query("INSERT INTO `currency`(`currency_code`) VALUES('".mysql_real_escape_string($currency_code)."')");
        $this->_currency[$currency_code] = $this->_lastID();
    }

    private function _addFare( $fare_code ){
        $this->_query("INSERT IGNORE INTO `reg_fare`(`fare_code`) VALUES('".mysql_real_escape_string($fare_code)."')");
        return $this->_lastID();
    }

    private function _getStations( SimpleXMLElement $stations ){
        foreach($stations->station as $station){
            $station = $this->_toArray( $station->attributes() );
            if( !isset($this->_stations[$station['code']]) ){
                $this->_addStation($station);
            }
        }
    }

    private function _getVehicles( SimpleXMLElement $vehicles  ){
        // просто заносим в БД, нигде далее не используется
        foreach( $vehicles->vehicle as $vehicle ){
            if( empty($vehicle) ) continue;
            $vehicle = $this->_toArray( $vehicle->attributes() );
            $this->_query("INSERT IGNORE INTO `reg_vehicle`(`vehicle_name`,`vehicle_code`)
                              VALUES(
                                '".mysql_real_escape_string( $vehicle['title'] )."',
                                '".mysql_real_escape_string( $vehicle['code'] )."'
                              )");
        }
    }

    private function _getCarriers( SimpleXMLElement $carriers  ){
        // просто заносим в БД, нигде далее не используется
        foreach( $carriers->carrier as $carrier ){
            if( empty($carrier) ) continue;
            $carrier = $this->_toArray( $carrier->attributes() );
            $this->_query("INSERT IGNORE INTO `reg_carrier`(`carrier_name`,`carrier_code`)
                              VALUES(
                                '".mysql_real_escape_string( $carrier['title'] )."',
                                '".mysql_real_escape_string( $carrier['code'] )."'
                              )");
        }
    }

    private function _addStation(Array $attributes){
        if( !isset( $this->_countries[$attributes['country_code']] ) ){
            $this->_addCountry($attributes['country_code'], $attributes['country_title']);
        }
        if( !empty($attributes['region_title'])
                && !isset( $this->_regions[ $this->_countries[$attributes['country_code']] ][$attributes['region_title']] ) ){
            $this->_addRegion( $this->_countries[$attributes['country_code']], $attributes['region_title'] );
        }
        if( !empty($attributes['district_title']) &&
                !isset( $this->_districts[(int)@$this->_regions[$this->_countries[$attributes['country_code']]][$attributes['region_title']]][$attributes['district_title']] ) ){
            $this->_addDistrict( $this->_countries[$attributes['country_code']],
                                (int)@$this->_regions[$this->_countries[$attributes['country_code']]][$attributes['region_title']],
                                $attributes['district_title']
            );
        }
        if( !isset($this->_towns[$attributes['city_id']]) ){
            $this->_addCity(
                $attributes['city_id'],
                $attributes['city_title'],
                $this->_countries[$attributes['country_code']],
                (int)@$this->_regions[$this->_countries[$attributes['country_code']]][$attributes['region_title']],
                (int)@$this->_districts[ (int)@$this->_regions[$this->_countries[$attributes['country_code']]][$attributes['region_title']] ][$attributes['district_title']]
            );
        }
        $xml = simplexml_load_string(file_get_contents('http://api.geonames.org/timezone?lat='.$attributes['lat'].'&lng='.$attributes['lon'].'&username=shifaka'));
        $this->_query("INSERT INTO `reg_station`(`station_name`,`station_alias`,`station_code`,`id_country`,`id_region`,`id_district`,`id_town`,`lat`,`lon`,`time_zone_id`)
                        VALUES(
                                '".mysql_real_escape_string( $attributes['title'] )."',
                                '".mysql_real_escape_string( transletiration($attributes['title']) )."',
                                 '".intval( $attributes['code'] )."',
                                 '".intval( $this->_countries[$attributes['country_code']] )."',
                                 '".intval( (int)@$this->_regions[$this->_countries[$attributes['country_code']]][$attributes['region_title']] )."',
                                 '".intval( (int)@$this->_districts[ (int)@$this->_regions[$this->_countries[$attributes['country_code']]][$attributes['region_title']] ][$attributes['district_title']] )."',
                                 '".intval( $this->_towns[$attributes['city_id']] )."',
                                 '".mysql_real_escape_string( $attributes['lat'] )."',
                                 '".mysql_real_escape_string( $attributes['lon'] )."',
                                 '".mysql_real_escape_string( (string)$xml->timezone->timezoneId )."'
                        )");
        $this->_stations[$attributes['code']] = $this->_lastID();
    }

    private function _addCountry( $country_code, $country_title ){
        $this->_query("INSERT INTO `country`(`country_code`, `country_name`)
                          VALUES(
                                '".mysql_real_escape_string( $country_code )."',
                                '".mysql_real_escape_string( $country_title )."'
                                )
            ");
        $this->_countries[$country_code] = $this->_lastID();
        $this->_regions[$this->_countries[$country_code]] = array();
        return $this->_countries[$country_code];
    }

    private function _addRegion( $country_id, $region_title ){
        $this->_query("INSERT INTO `region`(`region_name`, `id_country`)
                          VALUES(
                                '".mysql_real_escape_string( $region_title )."',
                                '".intval( $country_id )."'
                                )
            ");
        $this->_regions[$country_id][$region_title] = $this->_lastID();
        $this->_districts[$this->_regions[$country_id][$region_title]] = array();
        return $this->_regions[$country_id][$region_title];
    }

    private function _addDistrict( $country_id, $region_id, $district_title ){
        $this->_query("INSERT INTO `district`(`district_name`, `id_region`, `id_country`)
                          VALUES(
                                '".mysql_real_escape_string( $district_title )."',
                                '".intval( $region_id )."',
                                '".intval( $country_id )."'
                                )
            ");
        $this->_districts[$region_id][$district_title] = $this->_lastID();
        return $this->_districts[$region_id][$district_title];
    }

    private function _addCity($city_code, $city_name, $country_id, $region_id, $district_id){
        $this->_query("INSERT INTO `reg_town`(`town_name`,`town_alias`,`id_region`,`id_country`,`town_code`,`id_district`)
                          VALUES(
                                '".mysql_real_escape_string( $city_name )."',
                                '".mysql_real_escape_string( transletiration($city_name) )."',
                                '".intval( $region_id )."',
                                '".intval( $country_id )."',
                                '".intval( $city_code )."',
                                '".intval( $district_id )."'
                                )
            ");
        $this->_towns[$city_code] = $this->_lastID();
    }

    private function _loadGeo(){
        $result = $this->_fetchAssoc("SELECT `id_country`, `country_code` FROM `country`");
        $this->_countries = array();
        foreach( $result  as $country ){
            $this->_countries[$country['country_code']] = $country['id_country'];
            $this->_regions[$country['id_country']] = array();
        }
        $result = $this->_fetchAssoc("SELECT `id_region`, `region_name`, `id_country` FROM `region`");
        foreach($result as $region){
            $this->_regions[$region['id_country']][$region['region_name']] = $region['id_region'];
            $this->_districts[$region['id_region']] = array();
        }
        $result = $this->_fetchAssoc("SELECT `id_region`, `district_name`, `id_district` FROM `district`");
        foreach($result as $district){
            $this->_districts[$district['id_region']][$district['district_name']] = $district['id_district'];
        }
        $result = $this->_fetchAssoc("SELECT `id_station`, `station_code` FROM `reg_station`");
        foreach($result as $station){
            $this->_stations[$station['station_code']] = $station['id_station'];
        }
        $result = $this->_fetchAssoc("SELECT `id_town`, `town_code` FROM `reg_town`");
        foreach($result as $town){
            $this->_towns[$town['town_code']] = $town['id_town'];
        }
    }

    private function _loadCurrency(){
        $result = $this->_fetchAssoc("SELECT `id_currency`, `currency_code` FROM `currency`");
        foreach($result as $currency){
            $this->_currency[$currency['currency_code']] = $currency['id_currency'];
        }
    }

    private function _toArray(SimpleXMLElement $element ){
        $array = array();
        foreach( $element as $key => $val ){
            $array[$key] = (string)$val;
        }
        return $array;
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

    private function _getValue( $query ){
        $result = $this->_query( $query );
        $row = mysql_fetch_row( $result );
        return $row[0];
    }

    private function _query( $query ){
        if( false === $result = mysql_query( $query, $this->_link ) ){
            throw new Exception('MySQL-query error: '.mysql_error( $this->_link ).PHP_EOL.print_r($query, 1));
        }
        return $result;
    }

    private function _lastID(){
        return mysql_insert_id( $this->_link );
    }

    private function _cleanTable( $table ){
        $this->_query("TRUNCATE TABLE `".mysql_real_escape_string($table)."`");
    }

}
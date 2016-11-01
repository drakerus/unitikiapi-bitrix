<?php
    error_reporting(E_ALL);
    ignore_user_abort(1);
    set_time_limit(0);
    date_default_timezone_set("Europe/Moscow");
    setlocale(LC_ALL, "ru_RU.UTF8");

    try{
        $parser = new MGSParser();
        $parser->setType('avto');
        $parser->collectData();
        
        foreach($$parser->_timetable as $route_number => $route_data){
            foreach($route_data as $day => $day_data){
                foreach($routes[$route_number] as $direction_code => $direction_name){
                    foreach($day_data[$direction_code] as $station_name => $race_times){
                        foreach($race_times as $race_index => $race_time){
                            /* Здесь код загрузки в Битрикс
                            
                            Параметры:
                            $route_number - номер маршрута
                            $day - дни в формате донора (1111100, 0000011, 1000000 и т.д.)
                            MGSParser::getHumanDays($day) - дни в читаемом формате (Будни, Выходные, Понедельник и т.д.)
                            $direction_code - направление рейса в формате донора (AB - прямое, BA - обратное)
                            $direction_name - название маршрута
                            $station_name - название остановки
                            $race_time - время
                            ($race_index + 1) - порядковый номер рейса
                            
                            */
                            
                            // Визуальная проверка
                            
                            echo 'Номер маршрута: '.$route_number."\r\n";
                            echo 'Дни: '.MGSParser::getHumanDays($day)."\r\n";
                            echo 'Направление рейса: '.(($direction_code == 'AB') ? 'прямое' : 'обратное')."\r\n";
                            echo 'Название направления: '.$direction_name."\r\n";
                            echo 'Остановка: '.$station_name."\r\n";
                            echo 'Время: '.$race_time."\r\n";  
                            echo 'Номер рейса: '.($race_index + 1)."\r\n";
                            
                            var_dump($parser->routes);
                            var_dump($parser->_stations);
                            die();    
                        }
                    }
                }
            }
        }
    } catch(MGSException $e){
        var_dump($e);
        die();
    } catch(Exception $e){
        var_dump($e);
        die();
    }
    
    class MGSException extends Exception{
        public function __construct($error_message, $error_code = null){
                parent::__construct('Ошибка парсера: '."\r\n".$error_message, $error_code);
        }
    } 
    
    class MGSParser{
        
        private $_types = array('avto', 'trol', 'tram');
        private $_type = '';
        public $_routes = array();
        public $_timetable = array();
        private $_raw_timetable = '';
        public $_stations = array();
        
        static private $_dayTable = array('1111100' => 'Будни',
                                   '0000011' => 'Выходные',
                                   '1111111' => 'Ежедневно' );
        static private $_weekDays = array('Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
        
        public function __construct(){
            
        }
                
        public function getRoutes(){
            return $this->_routes;
        }
        
        public function getStations(){
            return $this->_stations;
        }
        
        public function getTimeTable($route = false){
            if($route !== false){
                if(!key_exists($route, $this->_timetable)){
                    throw new MGSException('Указан неизвестный маршрут!', 6);    
                }
                return $this->_timetable[$route];
            }
            return $this->_timetable;
        }
        
        public function setType($type){
            if(!in_array($type, $this->_types) || empty($type)){
                throw new MGSException('Указан неверный тип транспорта!', 1);
            }
            $this->_type = $type;
        }
        
        public function collectData(){
            if(empty($this->_type)){
                throw new MGSException('Не указан тип транспорта!', 4);
            }
            $routes = array_map('urldecode', explode("\n", $this->_getResponse('http://www.mosgortrans.org/pass3/request.ajax.php', 'list=ways&type='.$this->_type)));
            
            foreach($routes as $route){
                if(empty($route)) continue;
                $days = array_map('trim', explode("\n", $this->_getResponse('http://www.mosgortrans.org/pass3/request.ajax.php', 'list=days&type='.$this->_type.'&way='.urlencode($route))));
                foreach($days as $day){
                    if(!preg_match('#^\d{7}$#', $day)) continue;
                    $direstions = array_map(function($a){return trim(mb_convert_encoding($a, 'UTF8', 'CP1251'));}, explode("\n", $this->_getResponse('http://www.mosgortrans.org/pass3/request.ajax.php', 'list=directions&type='.$this->_type.'&way='.urlencode($route).'&date='.$day)));
                    if(preg_replace('#[^А-Я0-9]#i', '', $direstions[0]) == '') continue;
                    $this->_routes[$route]['AB'] = $direstions[0];
                    if(preg_replace('#[^А-Я0-9]#i', '', $direstions[1]) != ''){
                         $this->_routes[$route]['BA'] = $direstions[1];
                    }
                    foreach($this->_routes[$route] as $direction => $value){
                        $this->_raw_timetable = $this->_prepareString( trim( $this->_getResponse('http://www.mosgortrans.org/pass3/shedule.php', 'type='.$this->_type.'&way='.urlencode($route).'&date='.$day.'&direction='.$direction.'&waypoint=all')) );
                        if( false === $this->_timetable[$route][$day][$direction] = $this->_parseTimetable()){
                            throw new MGSException('Ошибка парсинга расписания: '."\r\n".'Тип: '.$this->_type."\r\n".'Маршрут: '.$route."\r\n".'Дни: '.self::getHumanDays($day)."\r\n".'Направление: '.$value, 5);
                        }
                    }
                }
            }
        }
        
        private function _parseTimetable(){
            if(empty($this->_raw_timetable)){
                return false;
            }
            $times = array();
            if(!preg_match_all('#<h2[^>]*>(.*?)</h2.*?<table.*?</table#is', substr($this->_raw_timetable, 0, stripos($this->_raw_timetable, '<h3>Легенда')), $matches)){
                return false;
            }
            foreach($matches[0] as $st_index => $tt){
                $station = trim($matches[1][$st_index]);
                if(!in_array($station, $this->_stations)){
                    $this->_stations[] = $station;
                }
                $times[$station] = array();
                if(!preg_match_all('#<td[^>]*align="right"[^>]*><span[^>]*class="hour"[^>]*>(\d{1,2})</span.*?<td[^>]*align="left"[^>]*>(.*?)</td#is', $tt, $hours)){
                    return false;
                }
                foreach($hours[1] as $index => $hour){
                    if(!preg_match_all('#<span[^>]*class="minutes"[^>]*>(\d{1,2})<#is', $hours[2][$index], $minutes)){
                        return false;
                    }
                    foreach($minutes[1] as $minute){
                        $times[$station][] = $hour.':'.$minute;    
                    }
                }
            }
            return $times;
        }
        
        public static function getHumanDays($day){
            if(isset(self::$_dayTable[$day])) return self::$_dayTable[$day];
            $week = str_split($day);
            $human_days = array();
            foreach($week as $order => $day){
                if($day == 1){
                    $human_days[] = self::$_weekDays($order);
                }
            }
            return implode(', ', $human_days);
        }
        
        private function _getResponse($url, $parameters = '', $post = false){
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
                curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13 ( .NET CLR 3.5.30729)');
                curl_setopt($curl, CURLOPT_ENCODING, 'deflate');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_AUTOREFERER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 3);  
                curl_setopt($curl, CURLOPT_TIMEOUT, 15); 
                if( false === $content = curl_exec($curl)){
                    throw new MGSException('Ошибка получения страницы "'.$url.'"'."\r\n".''.curl_error($curl), 2);
                }
                $curl_info = @curl_getinfo($curl);
                if($curl_info['http_code']!=200 && $curl_info['http_code']!=301 && $curl_info['http_code']!=302){
                    throw new MGSException('Ошибка получения страницы "'.$url.'"'."\r\n".'Код ответа сервера отличается от ожидаемого:'.$curl_info['http_code'], 3);
                }
                curl_close($curl);
                return $content;
        }
        
        private function _prepareString($string){
    	   return  preg_replace('/>(\s)+</','><',str_replace("\r","",str_replace("\n","", mb_convert_encoding($string, 'UTF8', 'CP1251') )));
        }
    }

?>

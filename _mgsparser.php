<?php
    error_reporting(E_ALL);
    ignore_user_abort(1);
    set_time_limit(0);
    date_default_timezone_set("Europe/Moscow");
    //setlocale(LC_ALL, "ru_RU.UTF8");

    try{
        #$_SERVER["DOCUMENT_ROOT"] = '/home/bitrix/www';
        #require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
        #CModule::IncludeModule("iblock");
        $parser = new MGSParser(483, 1, 2, 3); // Москва
        $parser->setType('avto'); // Автобусы
        $parser->collectData();
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
        
        public $_routes = array();
        public $_stations = array();
        
        private $_town_id = 0;
        private $_timetable_INFO_ID = 0;
        private $_stations_INFO_ID = 0;
        private $_buses_INFO_ID = 0;
        
        private $_CBIE = null;
        
        private $_types = array('avto', 'trol', 'tram');
        private $_type = '';
        
        private $_raw_timetable = '';
        
        static private $_dayTable = array('1111100' => 'Будни',
                                   '0000011' => 'Выходные',
                                   '1111111' => 'Ежедневно' );
        static private $_weekDays = array('Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
        
        static private $_schedules = array(
                                        '1111111' =>"1",
                                        '1111100' =>"2",
                                        '0000011' =>"3",
                                        '1111110' =>"4",
                                        '0000001' =>"5",
                                        '1111101' =>"6",
                                        '0000010' =>"7",
                                        '0000100' =>"8",
                                        '1111011' =>"9",
                                        '1111000' =>"10",
                                        '0000111' =>"11",
                                        '0000101' =>"12",
                                        '1111010' =>"13",
                                        '1111001' =>"14",
                                        '0000110' =>"15",
                                        '1000000' =>"16",
                                        '0111111' =>"17",
                                        '0010011' =>"18",
                                        '1101100' =>"19",
                                        '0100100' =>"20",
                                        '1011011' =>"21",
                                        '1000100' =>"22",
                                        '0111011' =>"23",
                                        '0111000' =>"24",
                                        '1000100' =>"25",
                                        '1000001' =>"26",
                                        '0111110' =>"27",
                                        '1000010' =>"28",
                                        '0111101' =>"29",
                                        '0111100' =>"30",
                                        '1000011' =>"31"
                                        );
        
        public function __construct($town_id = 0, $timetable_id = 0, $stations_id = 0, $buses_id = 0){
            if(intval($town_id) < 1){
                throw new MGSException('Указан неизвестный код города!', 7);    
            }
            if(intval($timetable_id) < 1){
                throw new MGSException('Указан некорректный номер инфоблока для расписания!', 7);    
            }
            if(intval($stations_id) < 1){
                throw new MGSException('Указан некорректный номер инфоблока для остановок!', 8);    
            }
            if(intval($buses_id) < 1){
                throw new MGSException('Указан некорректный номер инфоблока для автобусов!', 9);    
            }
            $this->_town_id = intval($town_id);
            $this->_timetable_INFO_ID = intval($timetable_id);
            $this->_stations_INFO_ID = intval($stations_id);
            $this->_buses_INFO_ID = intval($buses_id);
        }
        
        public function setType($type){
            if(!in_array($type, $this->_types) || empty($type)){
                throw new MGSException('Указан неверный тип транспорта!', 1);
            }
            $this->_type = $type;
        }
        
        public function collectData(){
            #$this->_cleanBlocks();
            #$this->_CBIE = new CIBlockElement();
            
            if(empty($this->_type)){
                throw new MGSException('Не указан тип транспорта!', 4);
            }
            $routes = array_map('urldecode', explode("\n", $this->_getResponse('http://www.mosgortrans.org/pass3/request.ajax.php', 'list=ways&type='.$this->_type)));
            foreach($routes as $route){
                $this->_dropBus($route);
                $days = array_map('trim', explode("\n", $this->_getResponse('http://www.mosgortrans.org/pass3/request.ajax.php', 'list=days&type='.$this->_type.'&way='.urlencode($route))));
                foreach($days as $day){
                    if(!preg_match('#^\d{7}$#', $day)) continue;
                    $direstions = array_map(function($a){return trim($a);}, explode("\n", $this->_getResponse('http://www.mosgortrans.org/pass3/request.ajax.php', 'list=directions&type='.$this->_type.'&way='.urlencode($route).'&date='.$day)));
                    $real_directions = array();
                    if(preg_replace('#[^А-Я0-9]#i', '', $direstions[0]) == '') continue;
                    $real_directions['AB'] = $direstions[0];
                    if(preg_replace('#[^А-Я0-9]#i', '', $direstions[1]) != ''){
                         $real_directions['BA'] = $direstions[1];
                    }
                    foreach($real_directions as $direction => $value){
                        $this->_raw_timetable = $this->_prepareString( trim( $this->_getResponse('http://www.mosgortrans.org/pass3/shedule.php', 'type='.$this->_type.'&way='.urlencode($route).'&date='.$day.'&direction='.$direction.'&waypoint=all')) );
                        if( false !== $timetable = $this->_parseTimetable()){
                            $this->_dropTimetable($route, $direction, $day, $timetable);    
                        } else {
                            throw new MGSException('Ошибка парсинга расписания: '."\r\n".'Тип: '.$this->_type."\r\n".'Маршрут: '.$route."\r\n".'Дни: '.self::getHumanDays($day)."\r\n".'Направление: '.$value, 5);
                        }
                    }
                }
                $this->_updateBus($route, $real_directions);
            }
            
            file_put_contents('routes.data', serialize($this->_routes));
            file_put_contents('stations.data', serialize($this->_stations));
        }
        
        private function _dropTimetable($route, $direction, $day, &$timetable){
            $bus = array();
            foreach($timetable as $station => $races){
                foreach($races as $race_index => $race_time){
                    $bus[$race_time][$route][$station][$direction][$day] = $race_index + 1;
                }
            }
            file_put_contents($route.'.data', serialize($bus));
            /*
            $PROP = array();
			$PROP[1] = $this->_town_id;//Город				
			$PROP[2] = $this->_routes[$route]; //Автобус - id автобуса - берется из справочника автобусов $all_buses_array['Название автобуса']
            foreach($timetable as $station => $races){
                foreach($races as $race_index => $race_time){
                    if(self::$_schedules[$day]==NULL){
                        die($day);
                    }
                    $PROP[3] = $this->_stations[$station]; //Остановка - id остановки - берется из справочника остановок	
        			//$PROP[4] = $src;//Источник урл 			
        			$PROP[5] = self::$_schedules[$day];//График движения - будни/выходные и т.п тоже из справочника...
        			//$PROP[6] = $payment_type_array[$bus_type_arr[$key][$race]];//Тип автобуса				
        			$PROP[7] = ($race_index + 1);//Номер рейса			
        			$PROP[8] = (($direction=='AB') ? 34 : 35);//Направление рейса			
        			$arLoadProductArray = Array(
        				  "IBLOCK_SECTION_ID" => false,
        				  "IBLOCK_ID"      => $this->_timetable_INFO_ID,
        				  "PROPERTY_VALUES"=> $PROP,
        				  "NAME"           => $race_time, // время
        				  "ACTIVE"         => "Y",            // активен			  
        				  );
        				 
        				if(false === $PRODUCT_ID = $this->_CBIE->Add($arLoadProductArray)){
        				    throw new MGSException('Ошибка добавления расписания: '.$this->_CBIE->LAST_ERROR."\r\n".print_r($arLoadProductArray, 1), 10);
        				}    
                }
            }
			*/
        }
        
        private function _dropBus($route_number){
            return;
            /*
            $PROP = array();
            $PROP[11] = $this->_town_id;//Город
            $PROP[12] = 36; //Тип
            $PROP[16] = $this->_transletiration($route_number);//Алиас имени автобуса (если все автобусы в городе имеют уникальный номер - алисас совпадает с номером автобуса)
		
            $arLoadProductArray = Array(
              "IBLOCK_SECTION_ID" => false,         
              "IBLOCK_ID"      => $this->_buses_INFO_ID,
              "PROPERTY_VALUES"=> $PROP,
              "NAME"           => mb_convert_encoding($route_number, 'utf8', 'cp1251'),
              "ACTIVE"         => "Y"            // активен			  
            );
            if(false !== $PRODUCT_ID = $this->_CBIE->Add($arLoadProductArray)){  
                $this->_routes[$route_number] = $PRODUCT_ID;   
            } else{  
                throw new MGSException('Ошибка добавления маршрута '.$route_number.': '.$this->_CBIE->LAST_ERROR, 10);   
            }
            */	
        }
        
        private function _updateBus($route_number, $directions){
            $this->_routes[$route_number] = $directions;
            return;
            /* 
            $PROP = array();
            $PROP[11] = $this->_town_id;//Город
            $PROP[12] = 36; //Тип
            $PROP[16] = $this->_transletiration($route_number);
            $PROP[13] = mb_convert_encoding($directions['AB'], 'utf8', 'cp1251');//Название направления - прямое
            $PROP[19] = ((isset($directions['BA'])) ? mb_convert_encoding($directions['BA'], 'utf8', 'cp1251') : '');//Название направления - обратное
            
            $arLoadProductArray = Array(
              "IBLOCK_SECTION_ID" => false,         
              "IBLOCK_ID"      => $this->_buses_INFO_ID,
              "PROPERTY_VALUES"=> $PROP,
              "NAME"           => mb_convert_encoding($route_number, 'utf8', 'cp1251'),
              "ACTIVE"         => "Y"            // активен			  
            );
            if(false === $PRODUCT_ID = $this->_CBIE->Update($this->_routes[$route_number], $arLoadProductArray)){  
                 throw new MGSException('Ошибка добавления маршрута '.$route_number.': '.$this->_CBIE->LAST_ERROR, 10);   
            }
            */
        }
        
        private function _dropStation($station){
            $this->_stations[] = $station;
            /*$PROP = array();
            $PROP[9] = $this->_town_id;//Город							
            $PROP[10] = $this->_transletiration($station);//Алиас
            
            $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,         
            "IBLOCK_ID"      => $this->_stations_INFO_ID,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => mb_convert_encoding($station, 'utf8', 'cp1251'),
            "ACTIVE"         => "Y",            // активен			  
            );				 
            if(false !== $PRODUCT_ID = $this->_CBIE->Add($arLoadProductArray)){ 
                $this->_stations[$station] = $PRODUCT_ID;
            }
            else{  
                throw new MGSException('Ошибка добавления остановки '.$station.': '.$this->_CBIE->LAST_ERROR, 11);
            }*/	
        }
        
        private function _parseTimetable(){
            if(empty($this->_raw_timetable)){
                return false;
            }
            $times = array();
            if(!preg_match_all('#<h2[^>]*>(.*?)</h2.*?<table.*?</table#is', $this->_raw_timetable, $matches)){
                return false;
            }
            foreach($matches[0] as $st_index => $tt){
                $station = trim($matches[1][$st_index]);
                if(!key_exists($station, $this->_stations)){
                    $this->_dropStation($station);
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
        
        private function _cleanBlocks(){
            //Стираем массив существующих уже расписаний в этом городе
            $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$this->_timetable_INFO_ID, "PROPERTY_TOWN" => $this->_town_id ));
            while($ar_fields = $res->GetNext()){
                CIBlockElement::Delete($ar_fields['ID']);
            }

            //Стираем массив существующих уже остановок в этом городе
            $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$this->_stations_INFO_ID, "PROPERTY_TOWN" => $this->_town_id ));
            while($ar_fields = $res->GetNext()){
                CIBlockElement::Delete($ar_fields['ID']);
            }
            
            //Стираем массив существующих уже автобусов в этом городе
            $res = CIBlockElement::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$this->_buses_INFO_ID, "PROPERTY_TOWN" => $this->_town_id ));
            while($ar_fields = $res->GetNext()){
                CIBlockElement::Delete($ar_fields['ID']);
            }
        }
        
        private static function getHumanDays($day){
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
    	   return  preg_replace('/>(\s)+</','><',str_replace("\r","",str_replace("\n","", $string )));
        }
        
        private function _transletiration($station){
            $trans = array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e', 'ё'=>'yo','ж'=>'j','з'=>'z','и'=>'i','й'=>'i','к'=>'k','л'=>'l', 'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t', 'у'=>'y','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch', 'ш'=>'sh','щ'=>'sh','ы'=>'i','э'=>'e','ю'=>'u','я'=>'ya',
            	'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E', 'Ё'=>'Yo','Ж'=>'J','З'=>'Z','И'=>'I','Й'=>'I','К'=>'K', 'Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P', 'Р'=>'R','С'=>'S','Т'=>'T','У'=>'Y','Ф'=>'F', 'Х'=>'H','Ц'=>'C','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sh', 'Ы'=>'I','Э'=>'E','Ю'=>'U','Я'=>'Ya',
              'ь'=>'','Ь'=>'','ъ'=>'','Ъ'=>'', ' '=>'_', '/'=>'_');
              
              return strtolower(str_replace(array_keys($trans), array_values($trans), $station));
        }
    }

?>
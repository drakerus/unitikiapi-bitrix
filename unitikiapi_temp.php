<?php
class Unitikiapi {
	private $api_url = 'https://api.unitiki.com';
	private $agent_id = '108';
	private $secret_key = '******'; 
	
    function __construct() {
	
	}

	/**
	Возврат билета
	@ticket_id ид билета
	@operation_id ид операции
	*/
    function return_ticet($params = array()){
        return $this->request_post('/ticket/refund', array(), $params);
    }




	/**
	Расчет суммы возврата билета
	@ticket_id ид билета
	@operation_id ид операции
	*/
    function sum_return($params = array()){
        return $this->request_get('/ticket/refund/calc', $params);
    }


    /**
     * Получение городов прибытия
     * @param array $params
     * @return bool|mixed
     */
    function city_list_to($params = array()){
        $response =  $this->request_get('/city/list/to', $this->option_fields(array(
            'query', 'city_id_start',
        ), $params));
        if(!$response || !isset($response->data->city_list)){
            return false;
        }
        return $response->data->city_list;
    }

    /**
     * Получение городов отправления
     * @param array $params
     * @return bool|mixed
     */
    function city_list_from($params = array()){
        $response = $this->request_get('/city/list/from', $this->option_fields(array(
            'query', 'city_id_end',
        ), $params));
        if(!$response || !isset($response->data->city_list)){
            return false;
        }
        return $response->data->city_list;
    }

    /**
     * Получение рейсов
     * @param array $params
     * @return bool|object
     */
    function ride_list($params = array()){
        return $this->request_get('/ride/list', $this->option_fields(array(
           'city_id_start', 'city_id_end', 'date', 'show_similar', 'partner_id', 'station_id_start', 'station_id_end', 'time',
        ), $params));
    }

    /**
     * Получение списка рейсов по стацниям
     * @param array $params
     * @return bool
     */
    function ride_list_station($params){
        $response = $this->request_get('/ride/list/station', $this->option_fields(array(
            'station_id_start', 'station_id_end',
            'date', 'time',
        ), $params));

        if(!$response || !isset($response->data->ride_list)){
            return false;
        }

        return $response->data->ride_list;
    }

    /**
     * Получение рейса
     * @param integer $ride_segment_id
     * @return object
     */
    function ride($ride_segment_id){
        return $this->request_get('/ride', array('ride_segment_id' => $ride_segment_id));
    }

    /**
     * Получение свободных мест на рейсе
     * @param integer $ride_segment_id
     * @return array
     */
    function ride_position_free($ride_segment_id){
        return $this->request_get('/ride/position/free', array('ride_segment_id' => $ride_segment_id));
    }
	/**
		получение документов для рейса
     * @param integer $ride_segment_id
     * @return array
	*/
    function card_identity_list($ride_segment_id){
        return $this->request_get('/card_identity/list', array('ride_segment_id' => $ride_segment_id));
    }

	/**
		получение гражданств
     * @param integer $ride_segment_id
     * @return array
	*/
    function citizenship_list(){
        return $this->request_get('/citizenship/list', array());
    }


    /**
     * Получение программ лояльности
     * @param integer $ride_segment_id
     * @return array
     */
    function loyalty_list($ride_segment_id){
        $response = $this->request_get('/loyalty/list', array('ride_segment_id' => $ride_segment_id));
        if(!$response || !isset($response->data->loyalty_list)){
            return false;
        }

        return $response->data->loyalty_list;
    }

    /**
     * Получение списка валют
     * @return array
     */
    function currency_list(){
        $response = $this->request_get('/currency/list');
        if(!$response || !isset($response->data->currency_list)){
            return false;
        }

        return $response->data->currency_list;
    }

    /**
     * Получение операции
     * @param integer $operation_id
     * @return object
     */
    function operation($operation_id){
        return $this->request_get('/operation', array(
            'operation_id' => $operation_id,
        ));
    }

    /**
     * Отмена операции
     * @param integer $operation_id
     * @return object
     */
    function operation_cancel($operation_id){
        return $this->request_post('/operation/cancel', array(), array(
            'operation_id' => $operation_id,
        ));
    }

    /**
     * Временное бронирование билетов
     * @param integer $ride_segment_id
     * @param array $ticket_data
     * @return object
     */
    function operation_booking_tmp($ride_segment_id, $ticket_data, $reference = null){
        return $this->request_post('/operation/booking/tmp', array(
            'reference' => $reference,
        ), array(
            'ride_segment_id' => $ride_segment_id,
            'ticket_data' => json_encode ($ticket_data),

        ));
    }


    /**
     * Обновление данных о билете
     * @param integer $ticket_id ID билета
     * @param array $ticket_data Данны для обновления билета
     * @return object
     */
    function ticket_update($ticket_id, $ticket_data){
        $response = $this->request_post('/ticket/update', array(), array(
            'ticket_id' => $ticket_id,
            'ticket_data' => json_encode($ticket_data),
        ));
        if(!$response || !isset($response->data->operation)){
            return false;
        }

        return $response->data->operation;
    }

    /**
     * Покупка билетов
     * @param integer $operation_id
     * @param array $tickets_price
     * @return bool
     */
    function operation_buy($operation_id, array $tickets_price){
        $response = $this->request_post('/operation/buy', array(), array(
			'operation_id' => $operation_id,
			'tickets_price' => json_encode($tickets_price)
        ));
return $response;
        if(!$response || !isset($response->data->operation)){
            return false;
        }

        return $response->data->operation;
    }

    /**
     * Получение pdf документа билетов
     * @param integer $operation_id
     * @param string $operation_hash
     * @return string
     */
    function operation_pdf($operation_id, $operation_hash){
        $params_get = array();
        $params_get['agent_id'] = $this->agent_id;
        $params_get['operation_id'] = $operation_id;
        $params_get['operation_hash'] = $operation_hash;
        $uri = $this->api_url . '/operation/pdf' . '?' . http_build_query($params_get);
        return $this->http_query($uri, 'get');

    }

    /**
     * Отправка GET запроса
     * @param $url
     * @param array $params_get
     * @return bool|mixed
     */
    private function request_get($url, $params_get = array()){
        $hash = $this->generate_hash($params_get);
        $params_get['agent_id'] = $this->agent_id;
        $params_get['hash'] = $hash;
        $uri = $this->api_url . $url . '?' . http_build_query($params_get);
        $response = $this->http_query($uri, 'get');

        $result = json_decode($response);

        if(json_last_error() !== JSON_ERROR_NONE){
            return false;
        }
        return $result;
    }

    /**
     * Отправка POST запроса
     * @param $url
     * @param array $params_get
     * @param array $params_post
     * @return bool|mixed
     */
    private function request_post($url, $params_get = array(), $params_post = array()){
        $hash = $this->generate_hash($params_post);
        $params_get['agent_id'] = $this->agent_id;
        $params_get['hash'] = $hash;
        $uri = $this->api_url . $url . '?' . http_build_query($params_get);
        $response = $this->http_query($uri, 'post', $params_post);

        $result = json_decode($response);
        if(json_last_error() !== JSON_ERROR_NONE){
            return false;
        }
        return $result;
    }

    /**
     * Генерация хэша
     * @param array $params Параметры для генерации хэша
     * @return string
     */
    private function generate_hash($params){
        $hash_str = 'agent_id=' . $this->agent_id;
        foreach($params as $key => $val){
            $hash_str .= $key . '=' . $val;
        }
        $hash_str .= 'secret_key=' . $this->secret_key;
        return md5($hash_str);
    }

    /**
     * Отправка HTTP запроса
     * @param $url
     * @param string $method
     * @param array $params
     * @param int $CONNECTTIMEOUT
     * @return mixed
     */
    private function http_query($url, $method = 'GET', $params = array(), $CONNECTTIMEOUT = 90) {
        //echo $url;
        //traverse array and prepare data for posting (key1=value1)
        if (count($params)) {
            foreach ($params as $key => $value) {
                $post_items[] = $key . '=' . urlencode($value);
            }
            //create the final string to be posted using implode()
            $post_string = implode('&', $post_items);
        } else {
            $post_string = '';
        }

        //echo $url;
        //create cURL connection
        $curl_connection = curl_init($url);
        // Выбираем тип запроса
        switch ($method) {
            case 'POST':
                curl_setopt($curl_connection, CURLOPT_POST, true);
                break;
            case 'GET':
                curl_setopt($curl_connection, CURLOPT_POST, false);
                break;
            case 'DELETE':
                curl_setopt($curl_connection, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }
        //set options
        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, $CONNECTTIMEOUT);
        curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_connection, CURLOPT_URL, $url);

        //set data to be posted
        if ($post_string != '') {
            curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
        }

        //perform our request
        $result = curl_exec($curl_connection);

        //close the connection
        curl_close($curl_connection);

        return $result;
    }

    /**
     * Получение необязательных полей
     * @param $fields
     * @param $opts
     * @return array
     */
    private function option_fields($fields, $opts){
        $params = array();
        foreach($fields as $field){
            if(isset($opts[$field])){
                $params[$field] = $opts[$field];
            }
        }
        return $params;
    }
	
  	public static function getPrice( $ride )
	{
		 return ( ($ride->price_unitiki < 99) ? ceil($ride->price_unitiki) : ceil( min( ( $ride->price_unitiki + $ride->price_unitiki *7/100) , $ride->price_agent_max ) ) );
		//return ceil( min( ( $ride->price_unitiki + $ride->price_unitiki *7/100) , $ride->price_agent_max ) );

    }
}

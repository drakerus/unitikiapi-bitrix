<?

$required_fields = array('date', 'time', 'station_id_start', 'station_id_end');

if( !isset($_REQUEST['data']) ){
    header('Location: /', 301);
    die();
}

if( false === $data = @unserialize( base64_decode($_REQUEST['data']) ) ){
    header('Location: /', 301);
    die();
}

if( !is_array($data) ){
    header('Location: /', 301);
    die();
}

if( sizeof( array_intersect( $required_fields, array_keys($data) ) ) != sizeof($required_fields) ){
    header('Location: /', 301);
    die();
}

require('unitikiapi.php');
$t = new Unitikiapi();
$r = $t->ride_list(
    array(
        'date' => $data['date'],
        'show_similar' => 1,
        'station_id_start' => intval($data['station_id_start']),
        'station_id_end' => intval($data['station_id_end']),
        'time' => $data['time']
    )
);
/*
if(empty($r->data->ride_list[0]->ride_segment_id)){
    if(empty($r->data->ride_list_similar[0]->ride_segment_id)){
        $location = '/';
    } else{
        $location = '/order/passfill/'.$r->data->ride_list_similar[0]->ride_segment_id.'/';
    }
} else{
    $location = '/order/passfill/'.$r->data->ride_list[0]->ride_segment_id.'/';
}

header('Location: https://unitiki.com'.$location.'?reference=mybuses.ru', 302);
*/
if(isset($_GET['oleg'])){
var_dump($r);
die();
}
if(empty($r->data->ride_list[0]->ride_segment_id)){
    if(empty($r->data->ride_list_similar[0]->ride_segment_id)){
        $location = '/';
    } else{
        $location = '/booking/order_make.php?ride_segment_id='.$r->data->ride_list_similar[0]->ride_segment_id;
    }
} else{
    $location = '/booking/order_make.php?ride_segment_id='.$r->data->ride_list[0]->ride_segment_id;
}

header('Location: https://mybuses.ru'.$location, 302);

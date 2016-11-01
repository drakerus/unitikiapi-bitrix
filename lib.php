<?php
function transletiration($station){
            $trans = array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e', 'ё'=>'yo','ж'=>'j','з'=>'z','и'=>'i','й'=>'i','к'=>'k','л'=>'l', 'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t', 'у'=>'y','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch', 'ш'=>'sh','щ'=>'sh','ы'=>'i','э'=>'e','ю'=>'u','я'=>'ya',
            	'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E', 'Ё'=>'Yo','Ж'=>'J','З'=>'Z','И'=>'I','Й'=>'I','К'=>'K', 'Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P', 'Р'=>'R','С'=>'S','Т'=>'T','У'=>'Y','Ф'=>'F', 'Х'=>'H','Ц'=>'C','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sh', 'Ы'=>'I','Э'=>'E','Ю'=>'U','Я'=>'Ya',
              'ь'=>'','Ь'=>'','ъ'=>'','Ъ'=>'', ' '=>'_', '/'=>'_', '%'=>'', '"'=>'', '.'=>'', '('=>'',  ')'=>'', ','=>'',  '№'=>'', ':' => '');
              
              return str_replace('moskva', 'moscow', umlauts( strtolower(str_replace(array_keys($trans), array_values($trans), $station)) ) );
        }

function umlauts($string){
    $pattern = str_split('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ');
    $replacement = str_split('SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    return str_replace( $pattern, $replacement, $string );
}

class MBUtils{
    //TODO: move these functions from CacheClass
    
    static function _timings( $n, $case ){
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

    static function _getTravelTime( $seconds ){
        if(empty($seconds) || !is_numeric($seconds)) $seconds = 0;
        if( !function_exists( 'gmp_div_qr' ) || !is_callable( 'gmp_div_qr' ) ){
            $function = 'MBUtils::_div_qr';
        } else{
            $function = 'MBUtils::_gmp_div_qr';
        }
        $interval_string = '';
        $interval = call_user_func( $function, $seconds, 60 * 60 * 24  );
        if( $interval[0] > 0 ){
            $interval_string =  $interval[0].self::_timings( $interval[0], 'day');
        }
        $interval = call_user_func( $function, $interval[1], 60 * 60 );
        if( $interval[0] > 0 ){
            $interval_string .=  ' '.$interval[0].self::_timings( $interval[0], 'hour');
        } elseif( $interval[0] == 0 && strlen($interval_string) > 0 ){
            $interval_string .=  ' 0 часов';
        }
        $interval = call_user_func( $function, $interval[1], 60 );
        if( $interval[0] > 0 ) {
            $interval_string .=  ' '.$interval[0].self::_timings( $interval[0], 'minute');
        }
        return trim($interval_string);
    }

    static function _div_qr( $n, $q ){
        return array( floor( $n / $q ), $n % $q  );
    }

    static function _gmp_div_qr( $n, $q ){
        $result = gmp_div_qr($n, $q);
        return array( gmp_strval($result[0]), gmp_strval($result[1]) );
    }

    static function _generateFile( $template, $data, $file ){
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

    static function _checkDir( $dir, $chmod = 755 ){
        if( !file_exists( $dir )
            || !is_dir( $dir ) ){
            if( false === mkdir( $dir, $chmod, true) ){
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
}
?>
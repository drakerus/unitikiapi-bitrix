<?php

function addSomeSugar($a)
{
    if (!$a)
    {
        return "-";
    }
    
    if ($a[strlen($a) - 1].$a[strlen($a) - 2].$a[strlen($a) - 3] == "key")
    {
        $b = str_split(base64_decode("MEFiQ2RFZkdoSmtMbU9wcVJzVHVWVw=="));
        $c = str_split(base64_decode("ITw+QCcjLiQlP14mKigpXy1dW317fA=="));
        $d = str_split($a);
        $d = array_slice($d, 0, -3);
        
        for ($e = count($d); $e--; )
        {
            for ($f = count($b); $f--; )
            {
                $d[$e] == $c[$f] && ($d[$e] = $b[$f]);
            }
        }

        return base64_decode(implode("", $d));
    }

    return $a;
}

function request($url, $referer = 'http://navi.kazantransport.ru/main.php')
{
    $options = array(
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => get_user_agent(),
        CURLOPT_REFERER => $referer,
        CURLOPT_HTTPHEADER => array(
            'Accept:text/plain, */*; q=0.01',
            'Accept-Encoding: gzip, deflate, sdch',
            'X-Requested-With: XMLHttpRequest',
            'Cookie: PHPSESSID=ik8k1126vogd2oldbqc8t1lrh7; uid=1450440739; lang=ru; map=%D0%A1%D0%BF%D1%83%D1%82%D0%BD%D0%B8%D0%BA.%D0%9A%D0%B0%D1%80%D1%82%D1%8B',
        ),
    );

    for ($i = 0; $i < 20; $i++)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error)
        {
            trigger_error("url: {$url}, attempt #{$i}, curl: {$error}");
            continue;
        }
        
        if ($http_code != 200)
        {
            trigger_error("url: {$url}, attempt #{$i}, code: {$http_code}");
            continue;
        }

        if (!$response)
        {
            trigger_error("url: {$url}, attempt #{$i}, empty response");
            continue;
        }

        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        if (strpos($header, 'Content-Encoding: gzip') !== false)
        {
            $body = gzdecode($body);

            if ($body === false)
            {
                trigger_error("url: {$url}, attempt #{$i}, bad gzip format");
                continue;
            }
        }

        return $body;
    }
    
    return false;
}

function request_multi($urls, $referer = 'http://navi.kazantransport.ru/main.php')
{
    $options = array(
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => get_user_agent(),
        CURLOPT_REFERER => $referer,
        CURLOPT_HTTPHEADER => array(
            'Accept:text/plain, */*; q=0.01',
            'Accept-Encoding: gzip, deflate, sdch',
            'X-Requested-With: XMLHttpRequest',
            'Cookie: PHPSESSID=ik8k1126vogd2oldbqc8t1lrh7; uid=1450440739; lang=ru; map=%D0%A1%D0%BF%D1%83%D1%82%D0%BD%D0%B8%D0%BA.%D0%9A%D0%B0%D1%80%D1%82%D1%8B',
        ),
    );

    $results = array();

    foreach (array_chunk($urls, 100, true) as $chunk)
    {
        $errors = array();
        $u = $chunk;

        for ($i = 0; $i < 20; $i++)
        {
            $mh = curl_multi_init();
            $chs = [];
            foreach ($u as $k => $url)
            {
                $ch = curl_init($url);
                curl_setopt_array($ch, $options);
                $chs[$k] = $ch;
                curl_multi_add_handle($mh, $ch);
            }

            do
            {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            }
            while ($running > 0);

            foreach ($chs as $k => $ch)
            {
                $response = curl_multi_getcontent($ch);
                $error = curl_error($ch);
                $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($mh, $ch);

                if ($error)
                {
                    trigger_error("url: {$urls[$k]}, attempt #{$i}, curl: {$error}");
                    $errors[$k] = $urls[$k];
                    continue;
                }

                if ($http_code != 200)
                {
                    trigger_error("url: {$urls[$k]}, attempt #{$i}, code: {$http_code}");
                    $errors[$k] = $urls[$k];
                    continue;
                }

                if (!$response)
                {
                    trigger_error("url: {$urls[$k]}, attempt #{$i}, empty response");
                    $errors[$k] = $urls[$k];
                    continue;
                }

                $header = substr($response, 0, $header_size);
                $body = substr($response, $header_size);

                if (strpos($header, 'Content-Encoding: gzip') !== false)
                {
                    $body = gzdecode($body);

                    if ($body === false)
                    {
                        trigger_error("url: {$urls[$k]}, attempt #{$i}, bad gzip format");
                        $errors[$k] = $urls[$k];
                        continue;
                    }
                }

                $results[$k] = $body;
            }

            curl_multi_close($mh);

            if ($errors)
            {
                $u = $errors;
                $errors = array();
            }
            else
            {
                break;
            }
        }
    }
    
    return $results;
}

function get_user_agent() {
    $agents = array(
        'Mozilla/5.0 (X11; Ubuntu; Linux; rv:24.0) Gecko/20100101 Firefox/24.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.17 (KHTML, like Gecko) Chrome/36.0.2032.50 Safari/536.17',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/35.0.2043.94 Safari/537.13',
        'Mozilla/5.0 (X11; NetBSD amd64; rv:24.0) Gecko/20100101 Firefox/24.0',
        'Mozilla/5.0 (Windows NT 8.1; WOW64) AppleWebKit/536.25 (KHTML, like Gecko) Chrome/34.0.2037.89 Safari/536.25',
        'Mozilla/5.0 (Windows NT 7.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/35.0.2005.27 Safari/537.11',
        'Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.15 (KHTML, like Gecko) Chrome/32.0.2025.43 Safari/537.15',
        'Mozilla/5.0 (Windows NT 8.0; Win64; x64) AppleWebKit/537.35 (KHTML, like Gecko) Chrome/32.0.2038.30 Safari/537.35',
        'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/36.0.2042.36 Safari/537.13',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/536.36 (KHTML, like Gecko) Chrome/37.0.2033.98 Safari/536.36',
    );
    return $agents[array_rand($agents)];
}

function cache_write($prefix, $data)
{
    $tmp_name = uniqid($prefix);
    file_put_contents(CACHE_DIR.'/'.$tmp_name, serialize($data));
    return $tmp_name;
}

function cache_read($name)
{
    if (!file_exists(CACHE_DIR.'/'.$name))
    {
        trigger_error("cache read error ($name)");
        exit;
    }
    $data = unserialize(file_get_contents(CACHE_DIR.'/'.$name));
    if (!$data)
    {
        trigger_error("cache read error ($name)");
        exit;
    }
    return $data;
}

function cache_delete($name)
{
    if (!file_exists(CACHE_DIR.'/'.$name))
    {
        trigger_error("cache delete error ($name)");
        exit;
    }
    $result = unlink(CACHE_DIR.'/'.$name);
    if (!$result)
    {
        trigger_error("cache unlink error ($name)");
        exit;
    }
}

function transletiration($station)
{
    $trans = array(
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e', 'ё'=>'yo','ж'=>'j','з'=>'z','и'=>'i','й'=>'i','к'=>'k','л'=>'l', 'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t', 'у'=>'y','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch', 'ш'=>'sh','щ'=>'sh','ы'=>'i','э'=>'e','ю'=>'u','я'=>'ya',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E', 'Ё'=>'Yo','Ж'=>'J','З'=>'Z','И'=>'I','Й'=>'I','К'=>'K', 'Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P', 'Р'=>'R','С'=>'S','Т'=>'T','У'=>'Y','Ф'=>'F', 'Х'=>'H','Ц'=>'C','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sh', 'Ы'=>'I','Э'=>'E','Ю'=>'U','Я'=>'Ya',
        'ь'=>'','Ь'=>'','ъ'=>'','Ъ'=>'', ' '=>'_', '/'=>'_', '%'=>'', '"'=>'', '.'=>'', '('=>'',  ')'=>'', ','=>'',  '№'=>'', ':' => ''
    );

    return mb_strtolower(umlauts(strtr($station, $trans)));
}

function umlauts($string)
{
    $pattern = str_split('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ');
    $replacement = str_split('SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    return str_replace($pattern, $replacement, $string);
}

function db_fetch_value($sql)
{
    global $db;
    $result = $db->query($sql);
    if (!$result)
    {
        trigger_error("result == false IN db_fetch_value: $db->error");
        return false;
    }
    if ($result->num_rows <= 0)
    {
        $result->close();
        return false;
    }
    $row = $result->fetch_row();
    $result->close();
    return $row[0];
}

/**
 * Получение id от race_type по маске
 *
 * @global mysqli $db
 * @staticvar array $race_types
 * @param string $mask
 */
function get_race_type($mask)
{
    static $race_types = array();
    if (!$race_types)
    {
        global $db;
        $result = $db->query("SELECT id_race_type, mgs_value FROM race_type");
        if (!$result)
        {
            trigger_error("result == false ($db->error)");
            $db->close();
            exit;
        }
        if ($result->num_rows <= 0)
        {
            trigger_error("num_rows == 0");
            $result->close();
            $db->close();
            exit;
        }
        while ($row = $result->fetch_row())
        {
            $race_types[$row[1]] = $row[0];
        }
        $result->close();
    }
    if (!isset($race_types[$mask]))
    {
        $id = add_race_type($mask);
        if (!$id)
        {
            trigger_error("id == false ($mask)");
            return false;
        }
        $race_types[$mask] = $id;
        return $id;
    }
    return $race_types[$mask];
}

/**
 * Добавление нового race_type для маски
 *
 * @global mysqli $db
 * @param string $mask
 * @return int
 */
function add_race_type($mask)
{
    $letters = array('п', 'в', 'с', 'ч', 'п', 'с', 'в');
    $ip = array('понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье');
    $rp = array('понедельника', 'вторника', 'среды', 'четверга', 'пятницы', 'субботы', 'воскресенья');
    $exist = array();
    $noexist = array();
    $mta = '';
    foreach ($letters as $m => $letter)
    {
        if ($mask{$m} == '1')
        {
            $exist[] = $ip[$m];
            $mta .= $letter;
        }
        else
        {
            $noexist[] = $rp[$m];
            $mta .= '_';
        }
    }
    $e = count($exist);
    if ($e <= 4) // четыре и меньше - перечисляем
    {  
        $human = implode(', ', $exist);
    } 
    elseif ($e == 5) // пять дней - пишем, что кроме двух дней
    {
        $human = "кроме $noexist[0] и $noexist[1]";
    }
    elseif ($e == 6) // шесть дней - пишем, что кроме одного дня
    {
        $human = "кроме $noexist[0]";
    }
    else // иначе ошибка
    {
        trigger_error("add_race_type error ($mask)");
        return false;
    }
    
    global $db;
    $result = $db->query("INSERT INTO race_type VALUES (NULL, '$human', '$mta', '$mask')");
    echo "INSERT INTO race_type VALUES (NULL, '$human', '$mta', '$mask')\n";
    if (!$result)
    {
        trigger_error("result == false ($db->error)");
        return false;
    }
    $id = (int)$db->insert_id;
    if ($id <= 0)
    {
        trigger_error("insert_id <= 0 ($mask)");
        return false;
    }
    return $id;
}


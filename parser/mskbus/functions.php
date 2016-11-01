<?php

function request($url) {
    $success = false;
    for ($i = 0; $i < 20; $i++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => get_user_agent(),
        ));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($error) {
            trigger_error("request $url curl error: $error");
            if ($i >= 3) {
                echo "sleep 180\n";
                sleep(180);
            } elseif ($i >= 10) {
                echo "sleep 300\n";
                sleep(300);
            } elseif ($i >= 15) {
                echo "sleep 600\n";
                sleep(600);
            }
            continue;
        }
        if ($code != 200) {
            trigger_error("code != 200 IN request");
            echo "sleep 300\n";
            sleep(300);
            continue;
        }
        if (!$result) {
            trigger_error("result == false IN request");
            continue;
        }
        $success = true;
        break;
    }
    if (!$success) {
        return false;
    }
    return iconv('cp1251', 'utf8', $result);
}

function request_multi($urls, $filter = null) {
    $results = array();
	for ($i = 0; $i < 5; $i++) {
		if ($i > 0) {
			echo "TRY $i\n";
            if (count($urls) > 5) {
                sleep(10);
            }
		}
		$mh = curl_multi_init();
		$chs = array();
		foreach ($urls as $k => $url) {
			$ch = curl_init($url);
			curl_setopt_array($ch, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERAGENT => get_user_agent(),
			));
			$chs[$k] = $ch;
			curl_multi_add_handle($mh, $ch);
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) == -1) {
				usleep(100);
			}
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
		$errors = array();
		foreach ($chs as $k => $ch) {
			$response = curl_multi_getcontent($ch);
			$error = curl_error($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_multi_remove_handle($mh, $ch);
			if ($error) {
				trigger_error("curl error for ".$urls[$k].": $error");
				$errors[$k] = $urls[$k];
			} elseif ($code != 200) {
				trigger_error("code = $code for ".$urls[$k]);
				$errors[$k] = $urls[$k];
			} elseif (!$response) {
				trigger_error("response == false for ".$urls[$k]);
				$errors[$k] = $urls[$k];
			} elseif (is_callable($filter)) {
				$result = $filter($response);
				if ($result === false) {
					$errors[$k] = $urls[$k];
				} elseif (!is_null($result)) {
					$results[$k] = $result;
				}
			} else {
				$results[$k] = iconv('cp1251', 'utf8', $response);
				continue;
			}
			unset($response);
		}
		curl_multi_close($mh);
		if ($errors) {
			$urls = $errors;
		} else {
			break;
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

function transletiration($station){
    $trans = array(
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e', 'ё'=>'yo','ж'=>'j','з'=>'z','и'=>'i','й'=>'i','к'=>'k','л'=>'l', 'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t', 'у'=>'y','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch', 'ш'=>'sh','щ'=>'sh','ы'=>'i','э'=>'e','ю'=>'u','я'=>'ya',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E', 'Ё'=>'Yo','Ж'=>'J','З'=>'Z','И'=>'I','Й'=>'I','К'=>'K', 'Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P', 'Р'=>'R','С'=>'S','Т'=>'T','У'=>'Y','Ф'=>'F', 'Х'=>'H','Ц'=>'C','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sh', 'Ы'=>'I','Э'=>'E','Ю'=>'U','Я'=>'Ya',
        'ь'=>'','Ь'=>'','ъ'=>'','Ъ'=>'', ' '=>'_', '/'=>'_', '%'=>'', '"'=>'', '.'=>'', '('=>'',  ')'=>'', ','=>'',  '№'=>'', ':' => ''
    );
              
    return mb_strtolower(umlauts(strtr($station, $trans)));
}

function umlauts($string){
    $pattern = str_split('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ');
    $replacement = str_split('SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
    return str_replace($pattern, $replacement, $string);
}

function db_fetch_value($sql) {
    global $db;
    $result = $db->query($sql);
    if (!$result) {
        trigger_error("result == false IN db_fetch_value: $db->error");
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

function cache_write($prefix, $data) {
    $tmp_name = uniqid($prefix);
    file_put_contents(CACHE_DIR.'/'.$tmp_name, serialize($data));
    return $tmp_name;
}

function cache_read($name) {
    if (!file_exists(CACHE_DIR.'/'.$name)) {
        trigger_error("cache read error ($name)");
        exit;
    }
    $data = unserialize(file_get_contents(CACHE_DIR.'/'.$name));
    if (!$data) {
        trigger_error("cache read error ($name)");
        exit;
    }
    return $data;
}

function cache_delete($name) {
    if (!file_exists(CACHE_DIR.'/'.$name)) {
        trigger_error("cache delete error ($name)");
        exit;
    }
    $result = unlink(CACHE_DIR.'/'.$name);
    if (!$result) {
        trigger_error("cache unlink error ($name)");
        exit;
    }
}
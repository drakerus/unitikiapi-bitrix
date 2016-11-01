<?
$_SERVER["DOCUMENT_ROOT"]='/home/admin/web/mybuses.ru/public_html/';
define("MAIN_PATH", $_SERVER["DOCUMENT_ROOT"].'sitemaps/');
define("DATE_MOD_PATH", $_SERVER["DOCUMENT_ROOT"].'include/mo_modification_date.html');

/*Читаем даты генерации:*/


$file_src=$_SERVER["DOCUMENT_ROOT"].'/mods/bus/include/msc_date.html';
$msc_date = file_get_contents($file_src);
$sitemap = $_SERVER["DOCUMENT_ROOT"].'sitemap.xml';
$lm_date = date('Y-m-d');



$direction=array(0=>'f',1=>'b');
$town_array=array();
$monthes_array=array(
	"01"=>"января",
	"02"=>"февраля",
	"03"=>"марта",
	"04"=>"апреля",
	"05"=>"мая",
	"06"=>"июня",
	"07"=>"июля",
	"08"=>"августа",
	"09"=>"сентября",
	"10"=>"октября",
	"11"=>"ноября",
	"12"=>"декабря"
);

exec('rm -f '.MAIN_PATH.'*.xml');
@unlink($sitemap);
file_put_contents($sitemap, '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL);

//Собираем массив городов
$result=mysql_query("SELECT `id_town`, `town_alias` FROM `town` WHERE `active`=1");
while ($row = mysql_fetch_assoc($result)) {
	extract($row);
	$town_alias = html_entity_decode($town_alias);
	$f_hand = fopen(MAIN_PATH.$town_alias . '_sitemap.xml', 'w+');


	fputs($f_hand,
		'<?xml version="1.0" encoding="UTF-8"?>
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		<url>
			<loc>https://mybuses.ru/' . $town_alias . '/</loc>
			<lastmod>' . $msc_date . '</lastmod>
			<changefreq>weekly</changefreq>
			<priority>0.8</priority>
		</url>
		');

	//Собираем массив существующих уже автобусов
	$result_bus = mysql_query("SELECT `alias` AS `bus_alias` FROM `bus` WHERE `id_town` = " . intval($row['id_town']));
	while ($bus = mysql_fetch_assoc($result_bus)) {
		fputs($f_hand,
			'<url>
				<loc>https://mybuses.ru/' . $town_alias . '/bus/' . html_entity_decode($bus['bus_alias']) . '/</loc>
				<changefreq>weekly</changefreq>
				<lastmod>' . $msc_date . '</lastmod>
		 </url>
		');
	}
	mysql_free_result($result_bus);

	//Собираем массив остановок
	$result_station = mysql_query("SELECT `alias` AS `station_alias` FROM `station` WHERE `id_town`= " . intval($id_town));
	while ($station = mysql_fetch_assoc($result_station)) {
		fputs($f_hand,
			'<url>
				<loc>https://mybuses.ru/' . $town_alias . '/station/' . html_entity_decode($station['station_alias']) . '/</loc>
				<changefreq>weekly</changefreq>
				<lastmod>' . $msc_date . '</lastmod>
		 </url>
		');
	}
	mysql_free_result($result_station);
	fputs( $f_hand, '</urlset>');
	fclose($f_hand);

	file_put_contents($sitemap,
		'<sitemap>
			<loc>https://mybuses.ru/sitemaps/' . $town_alias . '_sitemap.xml</loc>
			<lastmod>'.$lm_date.'</lastmod>
		</sitemap>
		', FILE_APPEND);
}
mysql_free_result($result);

//Список всех городов России с интересной популяцией имеющие вокзалы ( WHERE `reg_stoppoint`.`d_shift`=0 AND `reg_stoppoint`.`a_shift`=0 )
$result  = mysql_query("SELECT `reg_town`.`id_town`, `reg_town`.`town_alias`
							FROM `reg_town`
							JOIN `reg_town_rating` ON `reg_town`.`town_code`=`reg_town_rating`.`town_code`
							JOIN `reg_station` ON `reg_station`.`id_town`=`reg_town`.`id_town`
							JOIN `reg_stoppoint` ON `reg_stoppoint`.`id_station`=`reg_station`.`id_station`
						  WHERE `reg_stoppoint`.`d_shift`=0
							AND `reg_stoppoint`.`a_shift`=0
						  GROUP BY  `reg_town`.`id_town`
						  ORDER BY `reg_town`.`town_name` ASC");
while($row = mysql_fetch_assoc($result)){
	extract($row);
	$town_alias_safe = html_entity_decode($town_alias);
	$f_hand = fopen(MAIN_PATH.'reg_'.$town_alias_safe . '_sitemap.xml', 'w+');


	fputs($f_hand,
		'<?xml version="1.0" encoding="UTF-8"?>
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		<url>
			<loc>https://mybuses.ru/' . $town_alias_safe . '/</loc>
			<lastmod>' . $lm_date . '</lastmod>
			<changefreq>daily</changefreq>
			<priority>0.8</priority>
		</url>
		');

	$result_terminus = mysql_query("SELECT `reg_station`.`station_alias`
				FROM `reg_stoppoint`
				JOIN `reg_station` ON  `reg_stoppoint`.`id_station`=`reg_station`.`id_station`
				JOIN `reg_town` ON `reg_station`.`id_town`=`reg_town`.`id_town`
				LEFT JOIN `reg_station_rating` ON `reg_station`.`station_code`=`reg_station_rating`.`station_code`
			  WHERE `reg_town`.`town_alias` = '".mysql_real_escape_string($town_alias)."'
			  	AND `reg_stoppoint`.`d_shift`=0
			  	AND `reg_stoppoint`.`a_shift`=0
			  GROUP BY `reg_station`.`id_station`
			  ORDER BY `reg_station_rating`.`rating_value` DESC");

	while($terminus = mysql_fetch_assoc($result_terminus)){
		fputs($f_hand,
			'<url>
				<loc>https://mybuses.ru/' . $town_alias_safe . '/terminus/'.html_entity_decode($terminus['station_alias']).'/</loc>
				<changefreq>daily</changefreq>
				<lastmod>' . $lm_date . '</lastmod>
			</url>
		');
	}
	mysql_free_result($result_terminus);

	$way_result = mysql_query("SELECT STRAIGHT_JOIN DISTINCT CONCAT('".mysql_real_escape_string($town_alias)."', '_-_', `town_alias`) AS `way_alias`
                                        FROM(
                                            SELECT DISTINCT `id_thread`
                                            FROM `reg_stoppoint`
                                            LEFT JOIN `reg_station` USING(`id_station`)
                                            WHERE `d_shift` = 0 AND `a_shift` = 0 AND `id_town` = ".intval($id_town)."
                                        ) T
                                        LEFT JOIN `reg_stoppoint` USING(`id_thread`)
                                        LEFT JOIN `reg_station` USING(`id_station`)
                                        LEFT JOIN `reg_town` USING(`id_town`)
                                        WHERE `a_shift` != 0");
	while( $way = mysql_fetch_assoc($way_result) ){
		fputs($f_hand,
			'<url>
				<loc>https://mybuses.ru/' . $town_alias_safe . '/way/'.html_entity_decode($way['way_alias']).'/</loc>
				<changefreq>daily</changefreq>
				<lastmod>' . $lm_date . '</lastmod>
			</url>
		');
	}
	mysql_free_result($way_result);
	fputs( $f_hand, '</urlset>');
	fclose($f_hand);

	file_put_contents($sitemap,
		'<sitemap>
			<loc>https://mybuses.ru/sitemaps/reg_' . $town_alias_safe . '_sitemap.xml</loc>
			<lastmod>'.$lm_date.'</lastmod>
		</sitemap>
		', FILE_APPEND);
}
mysql_free_result($result);
file_put_contents($sitemap, '</sitemapindex>', FILE_APPEND);

//Генерация даты обновления
file_put_contents(DATE_MOD_PATH, '<span>Дата последнего обновления расписания: <b>'.date('j').' '.$monthes_array[date('m')].' '.date('Y').'</b></span>');
exec('chown apache.apache '.MAIN_PATH.'*.xml; chmod 644 '.MAIN_PATH.'*.xml');
exec('chown apache.apache '.$sitemap.'; chmod 644 '.$sitemap);

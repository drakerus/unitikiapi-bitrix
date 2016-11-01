<?
set_time_limit(0);
date_default_timezone_set("Europe/Moscow");
//$_SERVER["DOCUMENT_ROOT"]='/home/bitrix/www/';
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/db_mods/dbconn.php");
require($_SERVER["DOCUMENT_ROOT"]."/mods/bus/lib.php");

define('DB_PREFIX', 'ph382841_mybuses');



/*
//Собираем массив стран
$sql="SELECT * FROM `country`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$country_code_db_array[html_entity_decode($row['country_code'])]=$row['id_country'];
}

//Собираем массив регионов
$sql="SELECT * FROM `region`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$region_name_db_array[html_entity_decode($row['region_name'])]=$row['id_region'];
}


//Собираем массив районов
$sql="SELECT * FROM `district`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$district_name_db_array[html_entity_decode($row['district_name'])]=$row['id_district'];
}

//Собираем массив городов
$sql="SELECT * FROM `reg_town`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$town_code_db_array[html_entity_decode($row['town_code'])]=$row['id_town'];
}

//Собираем массив остановок
$sql="SELECT * FROM `reg_town`;";
$result=mysql_query($sql);
while ($row = mysql_fetch_array($result)){
$town_code_db_array[html_entity_decode($row['town_code'])]=$row['id_town'];
}
*/

$string='<group code="unitiki-1408-08-volgogradavtotrans" title="1408-08 ВолгоградАвтоТранс">
<stations>
<station code="342" country_code="RU" title="площадь Ленина" city_title="Волгоград" city_id="537" country_title="Россия" region_title="Волгоградская область" district_title="" lat="48.707103" lon="44.516939"/>
<station code="346" country_code="RU" title="площадь Ленина, по стороне памятника" city_title="Волгоград" city_id="537" country_title="Россия" region_title="Волгоградская область" district_title="" lat="48.707103" lon="44.516939"/>
<station code="6933" country_code="RU" title="Автовокзал" city_title="поселок городского типа Джубга" city_id="16053" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.322378" lon="38.704390"/>
<station code="6935" country_code="RU" title="автобусная остановка, напротив магазина Евросети" city_title="село Лермонтово" city_id="16091" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.300848" lon="38.755774"/>
<station code="6937" country_code="RU" title="Автовокзал" city_title="поселок городского типа Новомихайловский" city_id="16109" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.261365" lon="38.860131"/>
<station code="6939" country_code="RU" title="остановка на трассе &quot;Автостанция&quot;" city_title="село Ольгинка" city_id="16195" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.201336" lon="38.889165"/>
<station code="6941" country_code="RU" title="трасса, автобусная остановка &quot;Аквапарк Дельфин&quot;" city_title="село Небуг" city_id="16209" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.171097" lon="39.002101"/>
<station code="6943" country_code="RU" title="автобусная остановка &quot;Аэропорт&quot;" city_title="село Агой" city_id="16213" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.147221" lon="39.037890"/>
<station code="6945" country_code="RU" title="автобусная остановка напротив ТЦ &quot;Красная площадь&quot;" city_title="Туапсе" city_id="4706" country_title="Россия" region_title="Краснодарский край" district_title="" lat="44.095232" lon="39.073364"/>
<station code="6947" country_code="RU" title="автобусная остановка напротив базы отдыха «Энергетик» и пансионата «Юбилейный», ул. Школьная,2" city_title="село Шепси" city_id="16221" country_title="Россия" region_title="Краснодарский край" district_title="Туапсинский район" lat="44.036575" lon="39.144816"/>
<station code="6949" country_code="RU" title="Лазаревское, ул. Победы, 2, «Чайка»" city_title="Сочи" city_id="16281" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Сочи" lat="43.581509" lon="39.722882"/>
<station code="6953" country_code="RU" title="Лоо, Автовокзал" city_title="Сочи" city_id="16281" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Сочи" lat="43.581509" lon="39.722882"/>
<station code="6955" country_code="RU" title="Дагомыс, пансионат Дагомыс" city_title="Сочи" city_id="16281" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Сочи" lat="43.581509" lon="39.722882"/>
<station code="6957" country_code="RU" title="г.Адлер, пансионат «Знание» туда/она же санаторий «Известия» обратно" city_title="Сочи" city_id="16281" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Сочи" lat="43.581509" lon="39.722882"/>
<station code="6959" country_code="RU" title="Псоу (граница с Абхазией)" city_title="Сочи" city_id="16281" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Сочи" lat="43.581509" lon="39.722882"/>
<station code="6961" country_code="RU" title="музейный  комплекс «Михайловское укрепление»." city_title="поселок городского типа Архипо-Осиповка" city_id="16423" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Геленджик" lat="44.371311" lon="38.531455"/>
<station code="6963" country_code="RU" title="на кольце до въезда в поселок" city_title="село Дивноморское" city_id="16427" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Геленджик" lat="44.499768" lon="38.137311"/>
<station code="6965" country_code="RU" title="Старый  автовокзал (пересечение ул. Луначарского и ул. Садовая" city_title="Геленджик" city_id="2846" country_title="Россия" region_title="Краснодарский край" district_title="" lat="44.563022" lon="38.079055"/>
<station code="6967" country_code="RU" title="Автовокзал" city_title="село Кабардинка" city_id="16451" country_title="Россия" region_title="Краснодарский край" district_title="городской округ Геленджик" lat="44.650696" lon="37.935917"/>
<station code="6969" country_code="RU" title="Автобусная остановка «Рынок Северный»" city_title="Анапа" city_id="2827" country_title="Россия" region_title="Краснодарский край" district_title="" lat="44.894965" lon="37.316313"/>
</stations>
<vehicles>
<vehicle code="0f0f578ef1fb93d4fb6ec616a3ff1b7b" title="Богдан 18 Мест"/>
<vehicle code="4d959528f5ad57a3cd4b5fb1621e50e0" title="КИА 41 Место"/>
<vehicle code="356092bb3b5429d7829b54a84efd0dc5" title="Богдан 26 Мест"/>
<vehicle code="b9e702a9033a45e16fc4aacfcbc555f1" title="ПАЗ-3205 23 Места"/>
<vehicle code="b07c097c3e38e3442a00d47a47e32b34" title="КАВЗ 27 Мест"/>
<vehicle code="2865b0929a3ef7382ffcf03cd9678d78" title="Мерседес 19 Мест"/>
<vehicle code="b7839a429237f555e0a4c7033eee123a" title="Газель 15 Мест"/>
<vehicle code="5671809fc81b85f29ff58d4aac069279" title="Фиат 14 Мест"/>
<vehicle code="9789131d3aeb28f079a8e9934e909219" title="Хундай 43 Места"/>
<vehicle code="dede18acc218b1b81dc06e72f6a07962" title="Форд 27 Мест"/>
<vehicle code="f21ae9019fca509e2acf56922b735ae5" title="ПАЗ-3205 41 Место"/>
<vehicle code="b934e4d43a64a4ce836b08a025ec9161" title="Форд 18 Мест"/>
<vehicle code="c52a315777d2309fd9c14a8efbd357fa" title="Хундай 42 Места"/>
<vehicle code="ef0c01251815a7af2e6d8696358e122c" title="Газель 13 Мест"/>
<vehicle code="03d5969d880ef0aa7c1a31123a8727fe" title="Луидор 14 Мест"/>
<vehicle code="84101f013845857e72068559f98efc14" title="Лайнер 40 Мест"/>
<vehicle code="4909209752728f14571b19cc40a1750e" title="Газель 12 Мест"/>
<vehicle code="198e196393e7b97e1308841deb0f6375" title="ПАЗ-32053 23 Места"/>
<vehicle code="eae622007ecbd5faeee2ef2d67c61414" title="Волжанин 30 Мест"/>
<vehicle code="c4682de98ab7122cadbebeac89c1deef" title="ПАЗ-672 36 Мест"/>
<vehicle code="a8638ecb8459098e9189bd8783a27c04" title="Хундай 25 Мест"/>
<vehicle code="058503b2cc59115f2915564840355f39" title="Богдан 43 Места"/>
<vehicle code="36e8a97d983505d64946c7fbeb8242f8" title="Ивеко 20 Мест"/>
<vehicle code="9d9014267d6c80a86fff166fde5ec439" title="Неоплан 43 Места"/>
<vehicle code="970ca8ee36d7431ea0ea99fc89df9663" title="БАУ 38 Мест"/>
<vehicle code="5dbcca8efd6f6b9c88fe2c9d9f6dfeba" title="Фиат 18 Мест"/>
<vehicle code="9e16ea3493a0117d5cfa33cccafc9bd0" title="ПАЗ-32053 24 Места"/>
<vehicle code="d738b92f9be6da414364ef1fcee46f13" title="Шу чи 47 Мест"/>
<vehicle code="52bf5fed0483d667edc45ff45232084a" title="Аврора 29 Мест"/>
<vehicle code="729acc70f62419f292d114160c82a9f5" title="ПАЗ-3205 40 Мест"/>
<vehicle code="d2d080980747b7fe842f66dac3806801" title="Неоплан 47 Мест"/>
<vehicle code="a66cb55c1ab5a8f7a6f96b08f33c4339" title="Хундай 18 Мест"/>
<vehicle code="247fefca418bd09f71934572e3c575cd" title="ПАЗ-3205 42 Места"/>
<vehicle code="d1be45c8920084618ceb884afc0096cf" title="Фольксваген 19 Мест"/>
<vehicle code="7c018386eee9c38b25571cf187ccdb92" title="Мерседес 16 Мест"/>
<vehicle code="94af5d18fa2a6810e209ae7c6b8e8662" title="КАВЗ 29 Мест"/>
<vehicle code="89c81dea029f273c04fc8f0c62442515" title="ПАЗ-3205 22 Места"/>
<vehicle code="f4ffdc2aa8d135c65bc30f00041091a8" title="ПАЗ-3205 36 Мест"/>
<vehicle code="4c7ba7c1f16d897db0f214880f5ac9d9" title="Форд 19 Мест"/>
<vehicle code="ac169a045b84c60090f971d821606afc" title="ПАЗ-3204 65 Мест"/>
<vehicle code="503c675f0433690a0ab0293cf40a918a" title="ЛИАЗ-5256 44 Места"/>
<vehicle code="205d7e439e47f147d5e622bbfa55d601" title="ПАЗ-3205 24 Места"/>
<vehicle code="a9072a465932e799eb0cc34c3df79feb" title="Форд 16 Мест"/>
<vehicle code="73f0979c3222526b0895935f9a8ab08b" title="ПАЗ-4234 24 Места"/>
<vehicle code="a3f8a48e1ebb6396e778f6c3b53d99e0" title="Форд 22 Места"/>
<vehicle code="72934370e6eb6722e4861a071a4e3f99" title="Ивеко 26 Мест"/>
<vehicle code="49dfb03a6b961d5a6f4481eaa6206379" title="Ситроен 17 Мест"/>
<vehicle code="405b808ff30ab8f52884ff9d459fbd88" title="КИА грандбир 47 Мест"/>
<vehicle code="2f288d690f52e7cea6269b98932c449b" title="Мерседес 17 Мест"/>
<vehicle code="ab59074704d75eaa93854b3e4e76c00a" title="Ивеко 19 Мест"/>
<vehicle code="75e18e978b55990bff92b927d44ca48e" title="ПАЗ-3205 35 Мест"/>
<vehicle code="37caf1abdd752c37a19597406495deb6" title="ЛИАЗ-5256 104 Места"/>
<vehicle code="1afafc04ae2ba25cc570c1903a610766" title="ЛИАЗ-5256 23 Места"/>
<vehicle code="714bc9d8929c13e03d9dc57dd81f1dc8" title="ПАЗ-32053 22 Места"/>
<vehicle code="2d04df1e794c12240a58b264996af59d" title="КИА грандбир 41 Место"/>
<vehicle code="2457a918704113feaada46b293bbf216" title="Газель-некст 18 Мест"/>
<vehicle code="8b940291c83d8127ca3acffc9d55fa2e" title="Газель 14 Мест"/>
<vehicle code="4c84145ce0b77d427b4fa7c069448d89" title="Мерседес 18 Мест"/>
<vehicle code="e9e9dae34ecc47f58de9052f21c7b211" title="Луидор 12 Мест"/>
<vehicle code="827300fcbfb7148ad088fa91e815c3f4" title="Ивеко 18 Мест"/>
<vehicle code="8af01a3f47bd896e6f8e6ce952164cf2" title="ПАЗ-3204 23 Места"/>
<vehicle code="110f8e88263e3b1a699691720a0e113d" title="Фольксваген 20 Мест"/>
<vehicle code="8c982619a692e3369fad114f7bc269a8" title="Форд 17 Мест"/>
<vehicle code="ca6efb8145fc4c1ebf47ba75b66d9d3a" title="Пежо 18 Мест"/>
<vehicle code="7c1c7c32c585917a4da7ddcbbca5949a" title="КИА 43 Места"/>
<vehicle code="5f0aa25b7b4b134f6392654586f21015" title="Луидор 13 Мест"/>
<vehicle code="0ff54f6bec64210ff176fee13104b93c" title="ПАЗ-4234 28 Мест"/>
<vehicle code="4f8d69f403c39233ac051dfa2dd7f07c" title="ПАЗ-32053 40 Мест"/>
<vehicle code="c52e8207cb4301f789366a60a34aba72" title="Лайнер 41 Место"/>
<vehicle code="2447735446f91eedc1748bb60109ebaa" title="Мерседес 20 Мест"/>
<vehicle code="9ce2710556897b54ae3fa301e0e04510" title="ПАЗ-4234 23 Места"/>
<vehicle code="9c007729d5880f50972df1836601f1c7" title="Хундай 29 Мест"/>
<vehicle code="a862db81f3ac61891f7831607fb832e1" title="Мерседес 21 Место"/>
<vehicle code="05354ab0f24188abddbbee169ea64515" title="ПАЗ-3205 26 Мест"/>
<vehicle code="7247a99d0e0812e94154dc4851b50311" title="ПАЗ-32053 21 Место"/>
<vehicle code="5ae9c82169fb7a2f8233d606908c4684" title="Богдан 27 Мест"/>
<vehicle code="92df75fbf8705b56f440ec716330ff81" title="ЛАЗ-699 41 Место"/>
<vehicle code="d4f30a5d82946c9cc8d12e58b5266563" title="Пежо 17 Мест"/>
<vehicle code="1682b52914cf931a0f54c6a6f5914dd9" title="Хагер 22 Места"/>
<vehicle code="3a2aa035d1da754c19cf4b6ea6f253e7" title="Фиат 13 Мест"/>
<vehicle code="f7a00283acb630541dba3ced479c020a" title="Ситроен 18 Мест"/>
<vehicle code="a5e870a0dbf84f9262a9db2750516b69" title="ПАЗ-672 40 Мест"/>
<vehicle code="e31ca96b3268eb4896e86397f5ac319c" title="Хундай 19 Мест"/>
<vehicle code="6602d57345d94d0933ee1c412f4bf080" title="КАВЗ 52 Места"/>
<vehicle code="547a9abd69cfa1c426c3a86cab22dafe" title="ПАЗ-32053 41 Место"/>
<vehicle code="ca830f582dce1459cab8cc124688013b" title="ПАЗ-4234 30 Мест"/>
<vehicle code="80fe11aae2cd7a7d019fd54d527033af" title="ПАЗ-3204 41 Место"/>
<vehicle code="b18f5d26929af07dfab7642bc072d5b5" title="Сетра 40 Мест"/>
<vehicle code="10c595bcb8978075626a5ad87a4cf26e" title="ПАЗ-3205 27 Мест"/>
<vehicle code="93f29629364daaef2df7178628f8e33c" title="ПАЗ-672 24 Места"/>
<vehicle code="214123c0090aa815fdad485368b8e067" title="ПАЗ-3205 39 Мест"/>
<vehicle code="fe2c7102f866f19276d493631aa10f79" title="Форд 14 Мест"/>
<vehicle code="7794b8fc54fdeea3f6164d8bd8992904" title="Икарус 21 Место"/>
<vehicle code="0366b5542d2a07f7e5f43a250c3fb51d" title="ПАЗ-3205 25 Мест"/>
<vehicle code="81bc0d6361e93f13c7b3bd6c0da53027" title="ПАЗ 21 Место"/>
<vehicle code="a8b24952f90ea00a4033155bc5746706" title="КАВЗ 25 Мест"/>
<vehicle code="738faec81efb5a66b6488ad203542fac" title="ПАЗ-3205 20 Мест"/>
<vehicle code="612d8bd7374f5be469574ab5a7bb7ed2" title="Пежо 16 Мест"/>
<vehicle code="5ea97a0c8e7b9725a790da6d3f8db1d8" title="Икарус-260 80 Мест"/>
<vehicle code="2b0113c0b98ba95bbb6bf8202cf43727" title="ЛАЗ-699 65 Мест"/>
<vehicle code="d0ae7715ff814bc38b32d3dca8d0a7bc" title="КАВЗ 35 Мест"/>
<vehicle code="270e6627ae4870a9298c01debfe54360" title="Кароса 42 Места"/>
<vehicle code="e41482f990deefb04e1f0fd33690d3b3" title="ПАЗ-672 41 Место"/>
<vehicle code="ebca40d73e6bd46e1552d1f8d9fd6504" title="Сетра 49 Мест"/>
<vehicle code="71373b62859ce6d6a197fc7cede22c32" title="КАВЗ 20 Мест"/>
<vehicle code="6e4744296c2fe893961aac776e092e1d" title="Ссанг йонг 45 Мест"/>
<vehicle code="c8b6efd52a1ee5ffaf54fb4d8ec1cee6" title="Хагер 35 Мест"/>
<vehicle code="d004a0d1ff2e365ce2297d5db34506d8" title="ПАЗ-32053 37 Мест"/>
<vehicle code="a0741aeccd3680ad590118f465c49473" title="ПАЗ-672 28 Мест"/>
<vehicle code="14fb2fd0317798377e0e30d452c627dc" title="КИА 18 Мест"/>
<vehicle code="4f78ca3f72f726f14ff0fd258403a90c" title="Газель 18 Мест"/>
<vehicle code="149f0bade9f0dde8813c3cd0caa48032" title="Луидор 18 Мест"/>
<vehicle code="9086128ce192e9d035a5a80834eb8ca8" title="ПАЗ-3204 25 Мест"/>
<vehicle code="2141dbd18aac8054701afce480e1cb31" title="КАВЗ 21 Место"/>
<vehicle code="75f4011daeba6514008b2928ef435e31" title="Вольво 53 Места"/>
</vehicles>
<carriers>
<carrier code="626e6884ab546613e2fe1badfbf2b665" title="ООО &quot;ВолгоградАвтоТранс&quot;"/>
<carrier code="626e6884ab546613e2fe1badfbf2b665" title="ООО &quot;ВолгоградАвтоТранс&quot;"/>
<carrier code="626e6884ab546613e2fe1badfbas5" title="ООО &quot;АзерГорТранс&quot;"/>
</carriers>
<fares>
<fare code="06c832719489ae29bb5873531b3068bb">
<price price="1651" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6933"/>
</price>
<price price="1651" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6935"/>
</price>
<price price="1651" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6937"/>
</price>
<price price="1651" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6939"/>
</price>
<price price="1651" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6941"/>
</price>
<price price="1651" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6943"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6945"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6947"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="342"/>
<stop_to station_code="6949"/>
</price>
</fare>
<fare code="28bd87a87c6003b407335bfa234c71da">
<price price="2200" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6953"/>
</price>
<price price="2200" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6955"/>
</price>
<price price="2200" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6957"/>
</price>
<price price="2200" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6959"/>
</price>
</fare>
<fare code="d65b9c0f1c9e0dc99c99615fe40d5b1f">
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6961"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6963"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6965"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6967"/>
</price>
<price price="1761" currency="RUR" oneway_fare="1">
<stop_from station_code="346"/>
<stop_to station_code="6969"/>
</price>
</fare>
</fares>
<threads>
<thread t_type="bus" title="Волгоград - Анапа" carrier_code="626e6884ab546613e2fe1badfbf2b665" fare_code="d65b9c0f1c9e0dc99c99615fe40d5b1f">
<stoppoints>
<stoppoint station_code="346" distance="0" departure_shift="0"/>
<stoppoint station_code="6961" distance="840" departure_shift="55800" arrival_shift="55800"/>
<stoppoint station_code="6963" distance="882" departure_shift="59400" arrival_shift="59400"/>
<stoppoint station_code="6965" distance="930" departure_shift="61200" arrival_shift="61200"/>
<stoppoint station_code="6967" distance="946" departure_shift="63000" arrival_shift="63000"/>
<stoppoint station_code="6969" distance="1016" arrival_shift="64800"/>
</stoppoints>
<schedules>
<schedule days="25" times="17:00" period_start_date="2015-06-22" period_end_date="2015-08-30"/>
</schedules>
</thread>
<thread t_type="bus" title="Волгоград - Сочи" carrier_code="626e6884ab546613e2fe1badfbf2b665" fare_code="28bd87a87c6003b407335bfa234c71da">
<stoppoints>
<stoppoint station_code="346" distance="0" departure_shift="0"/>
<stoppoint station_code="6953" distance="944" departure_shift="68400" arrival_shift="68400"/>
<stoppoint station_code="6955" distance="954" departure_shift="70200" arrival_shift="70200"/>
<stoppoint station_code="6957" distance="990" departure_shift="73800" arrival_shift="73800"/>
<stoppoint station_code="6959" distance="1010" arrival_shift="77400"/>
</stoppoints>
<schedules>
<schedule days="5" times="16:00" period_start_date="2015-06-22" period_end_date="2015-06-28"/>
<schedule days="25" times="16:00" period_start_date="2015-06-29" period_end_date="2015-08-30"/>
</schedules>
</thread>
<thread t_type="bus" title="Волгоград - Сочи" carrier_code="626e6884ab546613e2fe1badfbf2b665" fare_code="06c832719489ae29bb5873531b3068bb">
<stoppoints>
<stoppoint station_code="342" distance="0" departure_shift="0"/>
<stoppoint station_code="6933" distance="823" departure_shift="51600" arrival_shift="51600"/>
<stoppoint station_code="6935" distance="828" departure_shift="52500" arrival_shift="52500"/>
<stoppoint station_code="6937" distance="839" departure_shift="53400" arrival_shift="53400"/>
<stoppoint station_code="6939" distance="851" departure_shift="54000" arrival_shift="54000"/>
<stoppoint station_code="6941" distance="865" departure_shift="55200" arrival_shift="55200"/>
<stoppoint station_code="6943" distance="871" departure_shift="55800" arrival_shift="55800"/>
<stoppoint station_code="6945" distance="880" departure_shift="57600" arrival_shift="57600"/>
<stoppoint station_code="6947" distance="892" departure_shift="59400" arrival_shift="59400"/>
<stoppoint station_code="6949" distance="920" arrival_shift="63000"/>
</stoppoints>
<schedules>
<schedule days="25" times="18:00" period_start_date="2015-06-22" period_end_date="2015-08-30"/>
</schedules>
</thread>
</threads>
</group>';


$xml = simplexml_load_string($string);
echo '<pre>';
//print_r($xml);
/*
foreach($xml->fares as $fare){
	//print_r($fare);
	echo '@';
	foreach($fare->fare->price as $price){	   
	$currency_array[]=$price["currency"];
	}

}
*/

// Справочник валюты
foreach($xml->fares->fare->price as $price){
//$currency_array[]=$price["currency"]->asXML();
$currency_array[]=$price['currency']->__toString();
//$currency_array[]=$price->attributes("currency")->;
}
$currency_array=array_unique($currency_array);
print_r($currency_array);

//Справочник перевозчиков
foreach($xml->carriers->carrier as $carrier){
//$currency_array[]=$price["currency"]->asXML();
$carrier_array['code'][]=$carrier['code']->__toString();
$carrier_array['name'][]=$carrier['title']->__toString();
//$currency_array[]=$price->attributes("currency")->;
}
$carrier_array['code']=array_unique($carrier_array['code']);
$carrier_array['name']=array_unique($carrier_array['name']);
print_r($carrier_array);

//Справочник ТС
foreach($xml->vehicles->vehicle as $vehicle){
//$currency_array[]=$price["currency"]->asXML();
$vehicle_array['code'][]=$vehicle['code']->__toString();
$vehicle_array['name'][]=$vehicle['title']->__toString();
//$currency_array[]=$price->attributes("currency")->;
}
print_r($vehicle_array);


//Справочник стран
foreach($xml->stations->station as $station){


//страна
$sql='SELECT * FROM `country` WHERE `country_code`="'.$station['country_code']->__toString().'";';
$result=mysql_query($sql);
if($row = mysql_fetch_array($result)){ $id_country=$row['id_country'];}
else{
$sql="INSERT INTO `".DB_PREFIX."`.`country` (`id_country`, `country_code`, `country_name`) VALUES (NULL, '".$station['country_code']->__toString()."', '".$station['country_title']->__toString()."');";
$result=mysql_query($sql);
$id_country=mysql_insert_id();
}

echo 'ID_страны='.$id_country;

//регион
if(!empty($station['region_title']->__toString())){
	
	$sql='SELECT * FROM `region` WHERE `region_name`="'.$station['region_title']->__toString().'";';
	$result=mysql_query($sql);
	if($row = mysql_fetch_array($result)){ $id_region=$row['id_region'];}
	else{
	$sql="INSERT INTO `".DB_PREFIX."`.`region` (`id_region`, `region_code`, `region_name`) VALUES (NULL, '', '".$station['region_title']->__toString()."');";
	$result=mysql_query($sql);
	$id_region=mysql_insert_id();
	}
}
echo 'ID_региона='.$id_region;

die();

//регион

$region_array['country_code'][]=$station['country_code']->__toString();
$region_array['name'][]=$station['region_title']->__toString();


//район
//город
//остановка




}
$country_array=array_unique($country_array);
print_r($country_array);
?>

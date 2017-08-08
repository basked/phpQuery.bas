<?php

error_reporting(E_ALL);
set_time_limit(1000);// время выполнения скрипта
require('/phpQuery/phpQuery.php'); // подключаем phpQuery
require('/libs/helpers.php'); // подключаем файл для вывода отладочной инфы
header("Content-type: text/html; charset = utf-8"); // кодировка utf-8

// константы для работы с прокси
define("PROXY_SERVER", "172.16.15.33");
define("PROXY_PORT", 3128);
define("PROXY_NAME", "gt-asup6");
define("PROXY_PASS", "lastmove");
define("PROXY_USE", FALSE);


echo date("H:i:s") . '<br>';
$p = 1;
// перебор всех ссылок в каталоге
for ($p = 1; $p <= 1; $p++) {

    $url = "https://candylady.by/catalog/" . $p;
    $curl = curl_init($url);
    if (PROXY_USE == true) {
        curl_setopt($curl, CURLOPT_PROXY, PROXY_SERVER);
        curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, PROXY_NAME . ":" . PROXY_PASS);
    }
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // возвращаем ответ в переменную
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // включаем редирект
    curl_setopt($curl, CURLOPT_COOKIE, "loc=1; _ym_uid=1500577932378426131; PHPSESSID=c3b8b66df13d77dccbeabf70e167e76f; _ym_isad=1; jv_enter_ts_puLsZ1Y3oX=1502197829210; jv_visits_count_puLsZ1Y3oX=5; jv_utm_puLsZ1Y3oX=; currency=1; _ga=GA1.2.1596758469.1500577933; _gid=GA1.2.994241966.1502197828; _ym_visorc_23717779=w");
    $page = curl_exec($curl);
    $doc = phpQuery::newDocument($page);
    $data_makers = $doc->find('[data-maker]');
    $i = 0;
    foreach ($data_makers as $data_maker) {
        $data_maker_pq = pq($data_maker);
        $desc = $data_maker_pq->find('div.description');
        $price = $data_maker_pq->find('p.price');
        echo $p . "_" . $i . ")" . $desc->find("p:nth-child(1)")->text() . ';' .
        $desc->find("p:nth-child(2)")->text() . ';' .
        $desc->find("p:nth-child(3)")->text() . ';' .
        $desc->find("p:nth-child(4)")->text() . ';' .
        $price->text() . '<br>';
        $i++;
    }
}
echo date("H:i:s") . '<br>';
curl_close($curl);
xprint($doc);

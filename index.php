<?php

error_reporting(E_ALL);
set_time_limit(1000);
require('/phpQuery/phpQuery.php');
header("Content-type: text/html; charset = utf-8");
// ддя работы с прокси
define("C_PROXY_SERVER", "172.16.15.33");
define("C_PROXY_PORT", 3128);
define("C_PROXY_NAME", "gt-asup6");
define("C_PROXY_PASS", "lastmove");
echo date("H:i:s").'<br>';
$p = 1;
// перебор всех ссылок в каталоге
for ($p = 1; $p <= 1; $p++) {

    $url = "https://candylady.by/catalog/" . $p;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // возвращаем ответ в переменную
    curl_setopt($curl, CURLOPT_PROXY, C_PROXY_SERVER);
    curl_setopt($curl, CURLOPT_PROXYPORT, C_PROXY_PORT);
    curl_setopt($curl, CURLOPT_PROXYUSERPWD, C_PROXY_NAME . ":" . C_PROXY_PASS);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // включаем редирект
    curl_setopt($curl, CURLOPT_COOKIE, "loc=1; _ym_uid=1500577932378426131; PHPSESSID=c3b8b66df13d77dccbeabf70e167e76f; _ym_isad=1; jv_enter_ts_puLsZ1Y3oX=1502197829210; jv_visits_count_puLsZ1Y3oX=5; jv_utm_puLsZ1Y3oX=; currency=1; _ga=GA1.2.1596758469.1500577933; _gid=GA1.2.994241966.1502197828; _ym_visorc_23717779=w");
    $page = curl_exec($curl);
    $doc = phpQuery::newDocument($page);
    $i = 0;
    $descriptions = $doc->find('div.description'); // описание всех товаров на странице
    $prices = $doc->find('p.price'); // цена всех товаров на странице
    foreach ($prices as $price) {
    $pr_pq = pq($price);
    echo $pr_pq->find("span")->text().' '.$pr_pq->find("strong")->text().'<br>';
    };
    /*foreach ($descriptions as $desc) {
        $desc_pq = pq($desc);
        echo $p . "_" . $i . ")" . $desc_pq->find("p:nth-child(1)")->text() . ';' .
        $desc_pq->find("p:nth-child(2)")->text() . ';' .
        $desc_pq->find("p:nth-child(3)")->text() . ';' .
        $desc_pq->find("p:nth-child(4)")->text() . '<br>';
        $i++;
    }*/
}
echo date("H:i:s").'<br>';

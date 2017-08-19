<?php

error_reporting(E_ALL);
set_time_limit(1000); // время выполнения скрипта
require('/phpQuery/phpQuery.php'); // подключаем phpQuery
require('/libs/helpers.php'); // подключаем файл для вывода отладочной инфы
header("Content-type: text/html; charset = utf-8"); // кодировка utf-8
// константы для работы с прокси
define("PROXY_SERVER", "172.16.15.33");
define("PROXY_PORT", 3128);
define("PROXY_NAME", "gt-asup6");
define("PROXY_PASS", "lastmove");
define("HOST_PARSE", "https://candylady.by");
define("KURS_PAGE", "/currency.php"); // страница курса

function request($url, $use_proxy = false, $cookiefile = 'cookie.txt') {
    $ch = curl_init($url);
    if ($use_proxy == true) {
        curl_setopt($ch, CURLOPT_PROXY, PROXY_SERVER);
        curl_setopt($ch, CURLOPT_PROXYPORT, PROXY_PORT);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, PROXY_NAME . ":" . PROXY_PASS);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // возвращает результат в переменную а не в буфер 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //использовать редиректы
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36'); //выставляем настройки браузера 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // работа с https
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // работа с https
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile); // сохраняет куки в файл
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile); // загружает куки из файла
    //curl_setopt($curl, CURLOPT_POST, true);   //включает протокол GET
    //curl_setopt($curl, CURLOPT_HTTPGET,true);  //включает протокол GET
    //curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); //запрос по умолчанию GET
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

function get_kurs() {
    $html_currency = request(HOST_PARSE . KURS_PAGE, true);
    $doc = phpQuery::newDocument($html_currency);
    $kurs = $doc->find('option')->attr('index'); // находим какой то непонятный курс(но так действует скрипт на сайте)
    return $kurs;
}

function get_desc_product($num_product, $url_product = '') {
    if ($url_product == '') {
        echo 'Ошибка при загруке продукта ' . $num_product . '; Пустая ссылка.';
        exit();
    } else {
        $full_desc = request(HOST_PARSE . $url_product, true);
        $full_desc_pq = pq($full_desc);
        $full_desc_pq->find('div.desc')->text();
    }
}

echo get_kurs() . '<br>';
echo date("H:i:s") . '<br>';
$page_max = 5;
// перебор всех ссылок в каталоге
for ($p = 1; $p <= $page_max; $p++) {

    $url = HOST_PARSE . "/catalog/" . $p . '/';
    $html = request($url, true);
    $doc = phpQuery::newDocument($html);
    //phpQuery::$ajaxAllowedHosts[] = 'candylady.by';
    //PhpQuery::$ajaxAllowedHosts[] = 'mail.google.com';
    $data_makers = $doc->find('[data-maker]');
    $i = 0;
    foreach ($data_makers as $data_maker) {
        $data_maker_pq = pq($data_maker);
        $desc = $data_maker_pq->find('div.description'); // блок с описанием товара
        $price = (int) $data_maker_pq->find('p.price')->attr('data-val'); // блок с ценой в валюте
        $link = $data_maker_pq->find('a:nth-child(1)')->attr('href'); // ссылка на полное описание товара

        echo $p . "_" . $i . ")" . $link . ';' . $desc->find("p:nth-child(1)")->text() . ';' .
        $desc->find('p:nth-child(2)')->text() . ';' .
        $desc->find('p:nth-child(3)')->text() . ';' .
        $desc->find('p:nth-child(4)')->text() . ';' .
        ($price * get_kurs()) . '<br>';
        $i++;
    }
}

get_desc_product($num_product)U
echo date("H:i:s") . '<br>';
//xprint($doc->html());

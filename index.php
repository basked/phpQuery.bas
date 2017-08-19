<?php

error_reporting(E_ALL);
set_time_limit(10000); // время выполнения скрипта
require('phpQuery/phpQuery.php'); // подключаем phpQuery
require('libs/helpers.php'); // подключаем файл для вывода отладочной инфы
header("Content-type: text/html; charset = utf-8"); // кодировка utf-8
// для работы с прокси
define("USE_PROXY", FALSE);
// константы для работы с прокси
define("PROXY_SERVER", "172.16.15.33");
define("PROXY_PORT", 3128);
define("PROXY_NAME", "gt-asup6");
define("PROXY_PASS", "lastmove");
define("HOST_PARSE", "https://candylady.by");
define("KURS_PAGE", "/currency.php"); // страница курса

function request($url, $use_proxy = USE_PROXY, $cookiefile = 'cookie.txt') {
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
    $html_currency = request(HOST_PARSE . KURS_PAGE, USE_PROXY);
    $doc = phpQuery::newDocument($html_currency);
    $kurs = $doc->find('option')->attr('index'); // находим какой то непонятный курс(но так действует скрипт на сайте)
    return $kurs;
}

// удаляет, точки тримит
function str_etalon($str) {
    return str_replace('.', '', trim($str));
}

function get_desc_product($page_num,$url_product = '/catalog/model/41146') {
    if ($url_product == '') {
        echo 'Ошибка при загруке продукта со страницы' . $url_product . '; Пустая ссылка.';
        exit();
    } else {
        $html = request(HOST_PARSE . $url_product, USE_PROXY);
        $doc = phpQuery::newDocument($html);
        // $('.card').find('.img>img')->attr('src');
        if ($doc !== null) {
            $desc = array();
            $doc->find('.card-bread')->find('a,i,b,span')->remove(); // удаляем элементы для поиска модели

            $id = str_replace('/catalog/model/', '', $url_product);
            $url = HOST_PARSE . $url_product;
            $name = str_replace('.', '', trim($doc->find('.card-bread')->text()));
            $doc->find('.desc>p>b')->empty(); // удаляем содержимое тегов b (Модель, Категория, Фирма, Размеры...)
            $img = trim($img = $doc->find('a.zoom_img')->attr('href'));
            $cnt_photos = $doc->find('.photos>a')->size();
            $photos_arr = array();
            if ($cnt_photos > 0) {
                for ($k = 1; $k <= $cnt_photos; $k++) {
                    $photos_arr[] = $doc->find(".photos>a:nth-child($k)")->attr('href');
                }
            }     
         // до преобразования в массив действовал такой фунционал
              $desc_html=htmlentities($doc->find('.desc>p')->html());
              $maker = str_replace('.', '', trim($doc->find('.desc>p:nth-child(1)')->text()));
              $category = str_replace('.', '', trim($doc->find('.desc>p:nth-child(2)')->text()));
              $size = str_replace('.', '', trim($doc->find('.desc>p:nth-child(3)')->text()));
              $size_arr = explode(', ', $size);
              $height = str_replace('.', '', trim($doc->find('.desc>p:nth-child(4)')->text()));
              $color = str_replace('.', '', trim($doc->find('.desc>p:nth-child(5)')->text()));
       
            
            // лучше загрузить все элементы описания в массив а потом обрабатывать (но так на 18-20 сек работает медленнее)
          /*$full_desc = array();
            $full_desc = explode('<b></b>', $doc->find('.desc>p')->html());
            for ($i = 0; $i < count($full_desc); $i++) {
                $full_desc[$i] = str_etalon($full_desc[$i]);
            }   
            $maker = $full_desc[1];
            $category = $full_desc[2];
            $size = $full_desc[3];
            $size_arr = explode(', ', $size);
            $height = $full_desc[4];
            $color = $full_desc[5];
           */
            
            
            $other = $doc->find('.desc>.other')->text();
            $other_html = htmlentities( $doc->find('.desc>.other')->html());
            $price = $doc->find('div.price')->attr('data-val') * get_kurs();
            $desc = ['id' => $id,'page_num'=>$page_num, 'url' => $url,'desc_all'=>$desc_html, 'name' => $name, 'img' => $img, 'photos' => $photos_arr, 'maker' => $maker, 'category' => $category, 'size' => $size_arr, 'height' => $height, 'color' => $color, 'price' => $price, 'other' => $other, 'other_html' => $other_html];
        }
    }
    $JSON_str = json_encode($desc, JSON_UNESCAPED_UNICODE); // преобразуем массив в JSON  формат 
    return $JSON_str;
}

;

// все ссылки на продукцию данной страницы
function get_links_from_page($page) {
    $url = HOST_PARSE . "/catalog/" . $page . '/';
    $html = request($url, USE_PROXY);
    $doc = phpQuery::newDocument($html);
    $link_arr = array();
  //  $links = $doc->find('[data-maker]>a:nth-child(1)'); //
    foreach ($doc->find('[data-maker]>a:nth-child(1)') as $links) {
        $link_arr[] = pq($links)->attr('href');
    }
    return $link_arr;
}

// выводит инормацию от 
function get_desc_product_from_catalog($start_page = 1, $end_page) {
    $cnt = 0;
    echo get_kurs() . '<br>';
    echo date("H:i:s") . '<br>';
// перебор всех ссылок в каталоге
    for ($p = $start_page; $p <= $end_page; $p++) {

        $url = HOST_PARSE . "/catalog/" . $p . '/';
        $html = request($url, USE_PROXY);
        $doc = phpQuery::newDocument($html);
        //phpQuery::$ajaxAllowedHosts[] = 'candylady.by';
        //PhpQuery::$ajaxAllowedHosts[] = 'mail.google.com';
        $data_makers = $doc->find('[data-maker]');
        $i = 0;
        foreach ($data_makers as $data_maker) {
            $cnt++;
            $data_maker_pq = pq($data_maker);
            $desc = $data_maker_pq->find('div.description'); // блок с описанием товара
            $price = (int) $data_maker_pq->find('p.price')->attr('data-val'); // блок с ценой в валюте
            $link = $data_maker_pq->find('a:nth-child(1)')->attr('href'); // ссылка на полное описание товара

            echo $p . "_" . $i . ")" . $link . ';
            ' . $desc->find("p:nth-child(1)")->text() . ';
            ' .
            $desc->find('p:nth-child(2)')->text() . ';
            ' .
            $desc->find('p:nth-child(3)')->text() . ';
            ' .
            $desc->find('p:nth-child(4)')->text() . ';
            ' .
            ($price * get_kurs()) . '<br>';
            $i++;
        }
    }

//get_desc_product($num_product);
    echo date("H:i:s") . '<br>';
    return $cnt;
}

//echo get_desc_product_from_catalog(1, 5);
// );
//xprint($doc->html());
//var_dump(get_links_from_page(2)[1]);

echo date("H:i:s") . '<br>';
 $JSON_arr=array();
 $page_cnt=$_GET['page_cnt'];

for ($i = 1; $i <= $page_cnt; $i++) {
    
    $links = get_links_from_page($i);
    for ($j = 0; $j < count($links); $j++) {
        ob_start();
        $desc_product= get_desc_product($i.'_'.$j,$links[$j]);
      //  echo 'Стр.№ '.$i.'<br>'.'Прод.№'.$j.'<br>'.$desc_product. '<br>';
        $JSON_arr[]=$desc_product;
        
       echo $desc_product.'<br><hr><br>';
       ob_end_flush();
    } 
    
}
         echo $page_cnt;
 echo '<br>'.date("H:i:s") . '<br><hr><br>';
// var_dump($JSON_arr);
 //var_dump(json_decode($JSON_arr[11]));
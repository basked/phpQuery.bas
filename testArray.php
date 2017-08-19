<?php

//$arr['pruducts']= json_decode('[{"name":"Платье", "model":"555", "size":[1,2,3]},{"name":"Платье", "model":"555", "size":[1,2,3,5,6,7,8]} ]',true);
//$size['size'] = array(111, 2, 555, 333);

$arr = array(array('name' => 'Платье', 'model' => '555',  'size'=>[1,2,3,4]));
//echo json_encode($arr)       ;


$desc = array('name' => 'Платье', 'model' => '56965');
$size =array('size'=>[111, 2, 555, 3]);
array_push($desc, ['size'=>[111, 2, 555, 3]]);
array_push($arr, $desc);
var_dump(json_encode($arr,true));

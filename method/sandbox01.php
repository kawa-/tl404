<?php

require dirname(__FILE__) . '/../lib/bootstrap4test.php';

//'id:2 is subscriber of id:3';

$sid = 2;
$tid = 3;
$res = httpPostWithDecode('is_my_subscriber', array('sid' => $sid, 'tid' => $tid));

var_dump($res);
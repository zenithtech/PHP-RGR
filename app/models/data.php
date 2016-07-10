<?php

$db = new DB();
$path = App::parseUrl()[1];

$exec = $db->executeQuery('grgjs.'.$path, new MongoDB\Driver\Query([]));
$execArr = iterator_to_array($exec);

$bson = MongoDB\BSON\fromPHP($execArr);
$json = MongoDB\BSON\toJSON($bson);

header('Content-Type: application/json');
echo json_encode(json_decode($json, true));

die();

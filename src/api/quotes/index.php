<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Quote.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'OPTIONS':
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, X-Requested-With');
        exit();
    case 'GET':
        $keys = array_keys($_GET);
        $pars = array_intersect($keys, array('id','author_id','category_id', 'random'));
        if (!empty($pars)) {
            include_once 'read_single.php';
        } else {
            include_once 'read.php';
        }
        exit();
    case 'POST':
        include_once 'create.php';
        exit();
    case 'PUT':
        include_once 'update.php';
        exit();
    case 'DELETE':
        include_once 'delete.php';
        exit();
}

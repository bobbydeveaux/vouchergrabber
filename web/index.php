<?php

use Symfony\Component\HttpFoundation\Request;

$app = require_once __DIR__.'/../app/app.php';

if ('cli' === php_sapi_name() && count($argv) > 0) {
    $path = '/';
    $arr  = array();
    switch(count($argv)) {
        case 2:
            list($_, $method) = $argv;
            break;
        case 3:
            list($_, $method, $path) = $argv;
            break;
        case 4:
            list($_, $method, $path, $parameters) = $argv;
            parse_str($parameters, $arr);
            break;
        default:
        case 1:
            print 'Invalid args. Format index.php METHOD path params';
            exit;
    }

    $request = Request::create($path, $method, $arr);
    $app->run($request);
}
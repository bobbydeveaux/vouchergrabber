<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once __DIR__.'/bootstrap.php';

use Silex\Application;

$app = new Application(array(
    'debug'   => true,
));

$app['brokerfactory'] = $app->share(function(){
    return new DVO\AMQP\Broker\BrokerFactory();
});

$app['workerfactory'] = $app->share(function(){
    return new DVO\AMQP\Worker\WorkerFactory();
});

// setup the icodes download controller
$app['icodes.controller'] = $app->share(function() use ($app) {
    return new DVO\Controller\IcodesController($app['brokerfactory']);
});

// setup the worker controller
$app['import.controller'] = $app->share(function() use ($app) {
    return new DVO\Controller\ImportController($app['workerfactory']);
});


$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->get('/broker/icodes', "icodes.controller:download");
$app->get('/worker', "import.controller:worker");


return $app;
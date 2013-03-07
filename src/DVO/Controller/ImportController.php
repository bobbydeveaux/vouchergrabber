<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DVO\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DVO\AMQP\Worker\WorkerFactory;

class ImportController
{
    protected $workerFactory;

    /**
     * VoucherController constructor.
     *
     * @param WorkerFactory $workerFactory The WorkerFactory.
     */
    public function __construct(WorkerFactory $workerFactory)
    {
        $this->workerFactory = $workerFactory;
    }

    /**
     * Handles the HTTP GET.
     *
     * @param Request     $request The request.
     * @param Application $app     The app.
     *
     * @return JsonResponse
     */
    public function worker(Request $request, Application $app)
    {
        // first param the exchange to connect to
        // second param is what queue to listen to
        $worker = $this->workerFactory->create('voucher', 'vouchers', AMQP_DURABLE);

        // listen & block for x times before ending
        $worker->run(50);

        return 'Ending process after 50 runs';
    }
}

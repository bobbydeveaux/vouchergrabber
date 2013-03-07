<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DVO\AMQP\Worker;

use DVO\AMQP\AMQPWorker;

// DOC TO DO - OK OK I didn't use doc_c :(
class VoucherWorker extends AMQPWorker
{

    protected $exchangeName = 'voucherExchange';
    protected $key          = 'key1';

    /**
     * Function to do the work.
     *
     * @param string     $envelope The data to enter into the queue.
     * @param \AMQPQueue $queue    The name of the queue.
     *
     * @return void
     */
    public function doWork($envelope, \AMQPQueue $queue)
    {
        //sleep(2);
        echo ($envelope->isRedelivery()) ? 'Redelivery' : 'New Message';
        echo PHP_EOL;
        $item = $envelope->getBody();

        $ch = curl_init('http://voucherapi.com/vouchers');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $item);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($item)
            )
        );

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw Exception('error: ' . curl_error($ch));
        }

        echo 'inserted record' . PHP_EOL;

    }
}

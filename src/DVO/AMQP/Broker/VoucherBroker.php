<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DVO\AMQP\Broker;

use DVO\AMQP\AMQPBroker;

// DOC TO DO - OK OK I didn't use doc_c :(
class VoucherBroker extends AMQPBroker
{
    protected $exchangeName = 'voucherExchange';
    protected $queues       = array(
                                  array('name' => 'datafeed', 'flag' => AMQP_DURABLE),
                                  array('name' => 'frontend', 'flag' => AMQP_NOPARAM)
                                );
    protected $key          = 'key1';
}

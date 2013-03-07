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

// DOC TO DO - OK OK I didn't use doc_c :(
class BrokerFactory
{
    /**
     * Create a broker.
     *
     * @param string $name The name of the broker.
     *
     * @return mixed
     */
    public function create($name)
    {
        if ($name === null) {
            return false;
        }

        $typeClass = "DVO\AMQP\Broker\\" . ucfirst($name) . 'Broker';
        if (class_exists($typeClass)) {
            return new $typeClass;
        }
    }
}

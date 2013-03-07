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

// DOC TO DO - OK OK I didn't use doc_c :(
class WorkerFactory
{
    /**
     * Create a worker.
     *
     * @param string $type  The type of worker.
     * @param string $queue The name of the queue.
     * @param string $flag  The flag of the queue.
     *
     * @return mixed
     */
    public function create($type = null, $queue = null, $flag = null)
    {
        if ($type === null) {
            return false;
        }

        $typeClass = "DVO\AMQP\Worker\\" . ucfirst($type) . 'Worker';
        if (class_exists($typeClass)) {
            return new $typeClass($queue, $flag);
        }
    }
}

<?php

/**
 * This file is part of the DVO package.
 *
 * (c) Bobby DeVeaux <me@bobbyjason.co.uk> / t: @bobbyjason
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DVO\AMQP;

class AMQPBroker
{
    protected $exchange     = null;
    protected $exchangeName = 'default';
    protected $queues       = array();
    protected $key          = 'default';

    /**
     * Get the exchange name.
     *
     * @return string
     */
    protected function getExchangeName()
    {
        return $this->exchangeName;
    }

    /**
     * Get the queues.
     *
     * @return array
     */
    protected function getQueues()
    {
        return $this->queues;
    }

    /**
     * Get the key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Construct the broker.
     */
    public function __construct()
    {
        $connection = new \AMQPConnection();
        $connection->connect();
        if (!$connection->isConnected()) {
            die('Not connected :(' . PHP_EOL);
        }
        // Open Channel
        $channel = new \AMQPChannel($connection);

        // Declare exchange
        $this->exchange = new \AMQPExchange($channel);
        $this->exchange->setName($this->getExchangeName());
        $this->exchange->setType(AMQP_EX_TYPE_FANOUT);
        $this->exchange->setFlags(AMQP_DURABLE);
        $this->exchange->declare();

        // Create Queues
        foreach ($this->getQueues() as $queue) {
            $q = new \AMQPQueue($channel);
            $q->setName($queue['name']);
            // flags should be on a per queue basis
            $q->setFlags($queue['flag']);
            $q->declare();
        }

    }

    /**
     * Send a message to the exchange.
     *
     * @param string $message The message.
     * @param string $key     The queue key.
     *
     * @return void
     */
    public function sendMessage($message, $key)
    {
        $message = $this->exchange->publish($message, 'key1');
        if (!$message) {
            echo 'Message not sent', PHP_EOL;
        } else {
            echo 'Message sent!', PHP_EOL;
        }
    }
}

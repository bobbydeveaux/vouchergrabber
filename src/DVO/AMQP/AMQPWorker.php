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

abstract class AMQPWorker
{
    /**
     * @var integer How many messages should this block for?
     */
    protected $processLimit = 10;

    /**
     * @var integer How many have we processed so far?
     */
    protected $processed = 0;

    /**
     * @var AMQPConnection The AMQP Connection
     */
    protected $connection = null;

    /**
     * @var AMQPChannel The AMQP Channel
     */
    protected $channel = null;

    /**
     * @var AMQPQueue The AMQP Queue
     */
    protected $queue = null;

    /**
     * @var string Name of the queue to connect to
     */
    protected $queueName = 'default';

    /**
     * @var string Name of the AMQP exchange
     */
    protected $exchangeName = 'default';

    /**
     * @var string Name of the AMQP exchange
     */
    protected $queueFlag = AMQP_NOPARAM;


    /**
     * @var string the key of the amqp exhange
     */
    protected $key = 'default';

    /**
     * The function to do the work.
     *
     * @param string     $envelope The envelope to carry the message.
     * @param \AMQPQueue $queue    The queue.
     *
     * @return void
     */
    abstract public function doWork($envelope, \AMQPQueue $queue);

    /**
     * Get the process limit.
     *
     * @return integer
     */
    protected function getProcessLimit()
    {
        return $this->processLimit;
    }

    /**
     * Create the AMQP worker.
     *
     * @param string $queueName The name of the queue.
     * @param string $flag      The flag of the queue.
     */
    public function __construct($queueName, $flag = null)
    {
        $this->queueName = $queueName;
        if (false === empty($flag)) {
            $this->queueFlag = $flag;
        }

        $this->connection = new \AMQPConnection();
        $this->connection->connect();
        if (false === $this->connection->isConnected()) {
            throw new Worker_Exception(
                '[ERROR] Could not connect to AMQP (' . get_class() . ' - ' . get_called_class(). ')'
            );
        }

        $this->setChannel();
        $this->setQueue();

    }

    /**
     * Set the channel.
     *
     * @return void
     */
    protected function setChannel()
    {
        // Open channel
        $this->channel = new \AMQPChannel($this->connection);
    }

    /**
     * Set the queue.
     *
     * @return void
     */
    protected function setQueue()
    {
        // Open Queue and bind to exchange
        $this->queue = new \AMQPQueue($this->channel);
        $this->queue->setName($this->queueName);
        $this->queue->setFlags($this->queueFlag);
        $this->queue->declare();
        $this->queue->bind($this->exchangeName, $this->key);
    }

    /**
     * Run the worker.
     *
     * @param integer $processLimit The process limit.
     *
     * @return boolean
     */
    public function run($processLimit)
    {
        $this->processLimit = $processLimit;

        // consume!
        $self = $this;
        /* @codingStandardsIgnoreStart */
        $this->queue->consume(function($envelope, $queue) use ($self) {
            return $self->consume($envelope, $queue);
        });
        /* @codingStandardsIgnoreEnd */
    }

    /**
     * Consume the queue.
     *
     * @param string     $envelope The message itself.
     * @param \AMQPQueue $queue    The message queue.
     *
     * @return boolean
     */
    final public function consume($envelope, \AMQPQueue $queue)
    {
        try {
            $this->doWork($envelope, $queue);
            $queue->ack($envelope->getDeliveryTag());
            $this->processed++;
        } catch (Worker_Exception_Retry $ex) {
            $queue->nack($envelope->getDeliveryTag());
        } catch (Worker_Exception_Fatal $ex) {
            $queue->ack($envelope->getDeliveryTag());
            // log the message so we don't forget about it
            // @TODO
        }

        if ($this->processed >= $this->getProcessLimit()) {
            return false;
        }

        return true;
    }
}

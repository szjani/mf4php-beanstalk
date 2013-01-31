<?php
/*
 * Copyright (c) 2012 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace mf4php\beanstalk;

use lf4php\LoggerFactory;
use mf4php\DelayableMessage;
use mf4php\Message;
use mf4php\MessageException;
use mf4php\PriorityableMessage;
use mf4php\Queue;
use mf4php\RuntimeLimitableMessage;
use mf4php\TransactedMessageDispatcher;
use Pheanstalk;
use Pheanstalk_Exception_ConnectionException;
use Pheanstalk_Job;
use trf4php\ObservableTransactionManager;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BeanstalkMessageDispatcher extends TransactedMessageDispatcher
{
    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    /**
     *
     * @param Pheanstalk $pheanstalk
     * @param ObservableTransactionManager $transactionManager
     */
    public function __construct(Pheanstalk $pheanstalk, ObservableTransactionManager $transactionManager = null)
    {
        if ($transactionManager !== null) {
            parent::__construct($transactionManager);
        }
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * @return Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    /**
     * @param Queue $queue
     * @param Message $message
     */
    protected function immediateSend(Queue $queue, Message $message)
    {
        $delay = Pheanstalk::DEFAULT_DELAY;
        $ttr = Pheanstalk::DEFAULT_TTR;
        $priority = Pheanstalk::DEFAULT_PRIORITY;

        if ($message instanceof PriorityableMessage) {
            $priority = $message->getPriority();
        }
        if ($message instanceof DelayableMessage) {
            $delay = $message->getDelay();
        }
        if ($message instanceof RuntimeLimitableMessage) {
            $ttr = $message->getRuntimeLimit();
        }

        $logger = LoggerFactory::getLogger(__CLASS__);
        try {
            $this->pheanstalk->putInTube($queue->getName(), serialize($message), $priority, $delay, $ttr);
        } catch (Pheanstalk_Exception_ConnectionException $e) {
            $newExp = new MessageException('Message sending error!', null, $e);
            $logger->error($newExp);
            throw $newExp;
        }
        $logger->info("A message has been sent to beanstalk queue '{}'", array($queue->getName()));
    }

    /**
     * Usually it is called from CLI.
     * It passes reserved messages to listeners.
     *
     * @param Queue $queue
     * @param Pheanstalk_Job $job
     */
    public function messageArrived(Queue $queue, Pheanstalk_Job $job)
    {
        $logger = LoggerFactory::getLogger(__CLASS__);
        $logger->info("Message '{}' has arrived from beanstalk queue '{}'", array($job->getId(), $queue->getName()));
        $message = unserialize($job->getData());
        /* @var $listener MessageListener */
        foreach ($this->getListeners($queue) as $listener) {
            $listener->onMessage($message);
        }
        try {
            $this->pheanstalk->delete($job);
        } catch (Pheanstalk_Exception_ConnectionException $e) {
            $newExp = new MessageException('Message deleting error!', null, $e);
            $logger->error($newExp);
            throw $newExp;
        }
        $logger->info(
            "Message '{}' has been deleted from beanstalk queue '{}'", array($job->getId(), $queue->getName())
        );
    }
}

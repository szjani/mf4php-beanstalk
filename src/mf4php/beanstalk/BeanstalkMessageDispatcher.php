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

use mf4php\AbstractMessageDispatcher;
use mf4php\DelayableMessage;
use mf4php\Message;
use mf4php\MessageListener;
use mf4php\PriorityableMessage;
use mf4php\Queue;
use mf4php\RuntimeLimitableMessage;
use Pheanstalk;
use Pheanstalk_Job;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BeanstalkMessageDispatcher extends AbstractMessageDispatcher
{
    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    /**
     * @param Pheanstalk $pheanstalk
     */
    public function __construct(Pheanstalk $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * @param Queue $queue
     * @param Message $message
     */
    public function send(Queue $queue, Message $message)
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

        $this->pheanstalk->putInTube($queue->getName(), serialize($message), $priority, $delay, $ttr);
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
        $message = unserialize($job->getData());
        /* @var $listener MessageListener */
        foreach ($this->getListeners($queue) as $listener) {
            $listener->onMessage($message);
        }
        $this->pheanstalk->delete($job);
    }
}

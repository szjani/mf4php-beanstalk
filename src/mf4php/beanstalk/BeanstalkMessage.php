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

use InvalidArgumentException;
use mf4php\DelayableMessage;
use mf4php\ObjectMessage;
use mf4php\PriorityableMessage;
use mf4php\RuntimeLimitableMessage;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Serializable;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BeanstalkMessage extends ObjectMessage implements PriorityableMessage,
 DelayableMessage, RuntimeLimitableMessage
{
    /**
     * @var int
     */
    private $priority;

    /**
     * @var int seconds
     */
    private $delay;

    /**
     * @var int seconds
     */
    private $runtimeLimit;

    /**
     * @param Serializable $object
     * @param int $priority
     * @param int $delay
     * @param int $runtimeLimit
     */
    public function __construct(
        Serializable $object,
        $priority = Pheanstalk::DEFAULT_PRIORITY,
        $delay = Pheanstalk::DEFAULT_DELAY,
        $runtimeLimit = Pheanstalk::DEFAULT_TTR
    ) {
        parent::__construct($object);
        $this->assertInteger($priority, 'Priority must be an integer!');
        $this->assertInteger($delay, 'Delay must be an integer!');
        $this->assertInteger($runtimeLimit, 'Runtime limit must be an integer!');
        $this->priority = (int) $priority;
        $this->delay = (int) $delay;
        $this->runtimeLimit = (int) $runtimeLimit;
    }

    private function assertInteger($value, $message)
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException($message);
        }
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function getRuntimeLimit()
    {
        return $this->runtimeLimit;
    }

    public function serialize()
    {
        return serialize(
            array(
                'defaultParams' => parent::serialize(),
                'priority' => $this->priority,
                'delay' => $this->delay,
                'runtimeLimit' => $this->runtimeLimit
            )
        );
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->priority = $data['priority'];
        $this->delay = $data['delay'];
        $this->runtimeLimit = $data['runtimeLimit'];
        parent::unserialize($data['defaultParams']);
    }
}

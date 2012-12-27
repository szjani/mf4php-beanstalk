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

use mf4php\DefaultQueue;
use Pheanstalk_Job;
use PHPUnit_Framework_TestCase;

require_once 'SampleObject.php';

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BeanstalMessageDispatcherTest extends PHPUnit_Framework_TestCase
{
    private $pheanstalkMock;

    /**
     * @var BeanstalkMessageDispatcher
     */
    private $dispatcher;

    /**
     * @var \trf4php\ObservableTransactionManager
     */
    private $tm;

    public function setUp()
    {
        $this->pheanstalkMock = $this->getMock('Pheanstalk', array('putInTube', 'delete'), array(), '', false);
        $this->tm = $this->getMock('\trf4php\ObservableTransactionManager');
        $this->dispatcher = new BeanstalkMessageDispatcher($this->pheanstalkMock, $this->tm);
    }

    public function testSend()
    {
        $queue = new DefaultQueue('testQueue');
        $obj = $this->getMock('Serializable');
        $message = new BeanstalkMessage($obj);
        $this->pheanstalkMock
            ->expects(self::once())
            ->method('putInTube')
            ->with($queue->getName(), serialize($message), $message->getPriority(), $message->getDelay(), $message->getRuntimeLimit());
        $this->dispatcher->send($queue, $message);
    }

    public function testMessageArrived()
    {
        $queue = new DefaultQueue('testQueue');
        $obj = new SampleObject('name@host.com');
        $message = new BeanstalkMessage($obj);
        $job = new Pheanstalk_Job(1, serialize($message));
        $messageListener = $this->getMock('mf4php\MessageListener');
        $messageListener
            ->expects(self::once())
            ->method('onMessage')
            ->with($message);
        $this->pheanstalkMock
            ->expects(self::once())
            ->method('delete')
            ->with($job);
        $this->dispatcher->addListener($queue, $messageListener);
        $this->dispatcher->messageArrived($queue, $job);
    }
}

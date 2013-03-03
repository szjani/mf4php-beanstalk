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

namespace mf4php\beanstalk\integration;

use mf4php\beanstalk\BeanstalkMessageDispatcher;
use mf4php\DefaultQueue;
use Pheanstalk_Pheanstalk as Pheanstalk;

require_once 'Listener.php';

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Config
{
    /**
     * @var BeanstalkMessageDispatcher
     */
    private $dispatcher;

    /**
     * @var \mf4php\MessageListener
     */
    private $listener;

    /**
     * @var \mf4php\Queue
     */
    private $queue;

    /**
     *
     * @var \Pheanstalk
     */
    private $pheanstalk;

    public function __construct()
    {
        $this->pheanstalk = new Pheanstalk('127.0.0.1');
        $this->dispatcher = new BeanstalkMessageDispatcher($this->pheanstalk);
        $this->listener = new Listener();
        $this->queue = new DefaultQueue('testQueue');
        $this->dispatcher->addListener($this->queue, $this->listener);
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function getListener()
    {
        return $this->listener;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return \Pheanstalk
     */
    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }
}

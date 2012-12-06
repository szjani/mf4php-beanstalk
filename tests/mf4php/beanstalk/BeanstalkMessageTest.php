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

use Pheanstalk;
use PHPUnit_Framework_TestCase;

require_once 'SampleObject.php';

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class BeanstalkMessageTest extends PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $obj = new SampleObject('test@host.com');
        $message = new BeanstalkMessage($obj);
        self::assertInstanceOf('\DateTime', $message->getDateTime());
        self::assertSame(Pheanstalk::DEFAULT_DELAY, $message->getDelay());
        self::assertSame(Pheanstalk::DEFAULT_PRIORITY, $message->getPriority());
        self::assertSame(Pheanstalk::DEFAULT_TTR, $message->getRuntimeLimit());
    }

    public function testSerialize()
    {
        $obj = new SampleObject('test@host.com');
        $message = new BeanstalkMessage($obj);

        $serialized = serialize($message);
        $deserMsg = unserialize($serialized);

        self::assertEquals($message->getDateTime(), $deserMsg->getDateTime());
        self::assertEquals($message->getDelay(), $deserMsg->getDelay());
        self::assertEquals($message->getRuntimeLimit(), $deserMsg->getRuntimeLimit());
        self::assertEquals($message->getPriority(), $deserMsg->getPriority());
        self::assertEquals($message->getObject()->getEmail(), $deserMsg->getObject()->getEmail());
    }
}

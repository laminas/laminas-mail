<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage;

use Laminas\Mail;
use Laminas\Mail\Storage;
use Laminas\Mail\Storage\Message;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Storage\Mbox<extended>
 */
class MboxInterfaceTest extends \PHPUnit_Framework_TestCase
{
    protected $_mboxFile;

    public function setUp()
    {
        $this->_mboxFile = __DIR__ . '/../_files/test.mbox/INBOX';
    }

    public function testCount()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $count = count($list);
        $this->assertEquals(7, $count);
    }

    public function testIsset()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $this->assertTrue(isset($list[1]));
    }

    public function testNotIsset()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $this->assertFalse(isset($list[10]));
    }

    public function testArrayGet()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $subject = $list[1]->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testArraySetFail()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $this->setExpectedException('Laminas\Mail\Storage\Exception\RuntimeException');
        $list[1] = 'test';
    }

    public function testIterationKey()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $pos = 1;
        foreach ($list as $key => $message) {
            $this->assertEquals($key, $pos, "wrong key in iteration $pos");
            ++$pos;
        }
    }

    public function testIterationIsMessage()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        foreach ($list as $key => $message) {
            $this->assertInstanceOf('Laminas\Mail\Storage\Message\MessageInterface', $message, 'value in iteration is not a mail message');
        }
    }

    public function testIterationRounds()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $count = 0;
        foreach ($list as $key => $message) {
            ++$count;
        }

        $this->assertEquals(7, $count);
    }

    public function testIterationWithSeek()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $count = 0;
        foreach (new \LimitIterator($list, 1, 3) as $key => $message) {
            ++$count;
        }

        $this->assertEquals(3, $count);
    }

    public function testIterationWithSeekCapped()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $count = 0;
        foreach (new \LimitIterator($list, 3, 7) as $key => $message) {
            ++$count;
        }

        $this->assertEquals(5, $count);
    }

    public function testFallback()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $result = $list->noop();
        $this->assertTrue($result);
    }

    public function testWrongVariable()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $this->setExpectedException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $list->thisdoesnotexist;
    }

    public function testGetHeaders()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);
        $headers = $list[1]->getHeaders();
        $this->assertNotEmpty($headers);
    }

    public function testWrongHeader()
    {
        $list = new Storage\Mbox(['filename' => $this->_mboxFile]);

        $this->setExpectedException('Laminas\Mail\Storage\Exception\InvalidArgumentException');
        $list[1]->thisdoesnotexist;
    }
}

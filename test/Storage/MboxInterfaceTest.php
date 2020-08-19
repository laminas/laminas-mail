<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage;

use Laminas\Mail\Storage;
use Laminas\Mail\Storage\Exception;
use PHPUnit\Framework\TestCase;
use Laminas\Mail\Storage\Message\MessageInterface;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Storage\Mbox<extended>
 */
class MboxInterfaceTest extends TestCase
{
    protected $mboxFile;

    public function setUp(): void
    {
        $this->mboxFile = __DIR__ . '/../_files/test.mbox/INBOX';
    }

    public function testCount(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = count($list);
        $this->assertEquals(7, $count);
    }

    public function testIsset(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertTrue(isset($list[1]));
    }

    public function testNotIsset(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertFalse(isset($list[10]));
    }

    public function testArrayGet(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $subject = $list[1]->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testArraySetFail(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->expectException(Exception\RuntimeException::class);
        $list[1] = 'test';
    }

    public function testIterationKey(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $pos = 1;
        foreach ($list as $key => $message) {
            $this->assertEquals($key, $pos, "wrong key in iteration $pos");
            ++$pos;
        }
    }

    public function testIterationIsMessage(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        foreach ($list as $key => $message) {
            $this->assertInstanceOf(
                MessageInterface::class,
                $message,
                'value in iteration is not a mail message'
            );
        }
    }

    public function testIterationRounds(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = 0;
        foreach ($list as $key => $message) {
            ++$count;
        }

        $this->assertEquals(7, $count);
    }

    public function testIterationWithSeek(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = 0;
        foreach (new \LimitIterator($list, 1, 3) as $key => $message) {
            ++$count;
        }

        $this->assertEquals(3, $count);
    }

    public function testIterationWithSeekCapped(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = 0;
        foreach (new \LimitIterator($list, 3, 7) as $key => $message) {
            ++$count;
        }

        $this->assertEquals(5, $count);
    }

    public function testFallback(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $result = $list->noop();
        $this->assertTrue($result);
    }

    public function testWrongVariable(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->expectException(Exception\InvalidArgumentException::class);
        $list->thisdoesnotexist;
    }

    public function testGetHeaders(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);
        $headers = $list[1]->getHeaders();
        $this->assertNotEmpty($headers);
    }

    public function testWrongHeader(): void
    {
        $list = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->expectException(Exception\InvalidArgumentException::class);
        $list[1]->thisdoesnotexist;
    }
}

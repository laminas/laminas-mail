<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Storage;

use PHPUnit\Framework\TestCase;
use Zend\Config;
use Zend\Mail\Storage;

/**
 * @group      Zend_Mail
 */
class MboxTest extends TestCase
{
    protected $mboxOriginalFile;
    protected $mboxFile;
    protected $mboxFileUnix;
    protected $tmpdir;

    public function setUp()
    {
        if ($this->tmpdir == null) {
            if (getenv('TESTS_ZEND_MAIL_TEMPDIR') != null) {
                $this->tmpdir = getenv('TESTS_ZEND_MAIL_TEMPDIR');
            } else {
                $this->tmpdir = __DIR__ . '/../_files/test.tmp/';
            }
            if (! file_exists($this->tmpdir)) {
                mkdir($this->tmpdir);
            }
            $count = 0;
            $dh = opendir($this->tmpdir);
            while (readdir($dh) !== false) {
                ++$count;
            }
            closedir($dh);
            if ($count != 2) {
                $this->markTestSkipped('Are you sure your tmp dir is a valid empty dir?');
                return;
            }
        }

        $this->mboxOriginalFile = __DIR__ . '/../_files/test.mbox/INBOX';
        $this->mboxFile = $this->tmpdir . 'INBOX';

        copy($this->mboxOriginalFile, $this->mboxFile);
    }

    public function tearDown()
    {
        unlink($this->mboxFile);

        if ($this->mboxFileUnix) {
            unlink($this->mboxFileUnix);
        }
    }

    public function testLoadOk()
    {
        new Storage\Mbox(['filename' => $this->mboxFile]);
        $this->addToAssertionCount(1);
    }

    public function testLoadConfig()
    {
        new Storage\Mbox(new Config\Config(['filename' => $this->mboxFile]));
        $this->addToAssertionCount(1);
    }

    public function testNoParams()
    {
        $this->expectException('Zend\Mail\Storage\Exception\InvalidArgumentException');
        new Storage\Mbox([]);
    }

    public function testLoadFailure()
    {
        $this->expectException('Zend\Mail\Storage\Exception\RuntimeException');
        new Storage\Mbox(['filename' => 'ThisFileDoesNotExist']);
    }

    public function testLoadInvalid()
    {
        $this->expectException('Zend\Mail\Storage\Exception\InvalidArgumentException');
        new Storage\Mbox(['filename' => __FILE__]);
    }

    public function testClose()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $mail->close();
        $this->addToAssertionCount(1);
    }

    public function testHasTop()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertTrue($mail->hasTop);
    }

    public function testHasCreate()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertFalse($mail->hasCreate);
    }

    public function testNoop()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertTrue($mail->noop());
    }

    public function testCount()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = $mail->countMessages();
        $this->assertEquals(7, $count);
    }

    public function testSize()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);
        $shouldSizes = [1 => 397, 89, 694, 452, 497, 101, 139];


        $sizes = $mail->getSize();
        $this->assertEquals($shouldSizes, $sizes);
    }

    public function testSingleSize()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $size = $mail->getSize(2);
        $this->assertEquals(89, $size);
    }

    public function testFetchHeader()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

/*
    public function testFetchTopBody()
    {
        $mail = new Storage\Mbox(array('filename' => $this->mboxFile));

        $content = $mail->getHeader(3, 1)->getContent();
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }
*/

    /**
     * @group 6775
     */
    public function testFetchMessageHeaderUnix()
    {
        $mail = new Storage\Mbox(['filename' => $this->getUnixMboxFile(), 'messageEOL' => "\n"]);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageHeader()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageBody()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $content = $mail->getMessage(3)->getContent();
        list($content) = explode("\n", $content, 2);
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }

    /**
     * @group 6775
     */
    public function testFetchMessageBodyUnix()
    {
        $mail = new Storage\Mbox(['filename' => $this->getUnixMboxFile(), 'messageEOL' => "\n"]);

        $content = $mail->getMessage(3)->getContent();
        list($content) = explode("\n", $content, 2);
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }

    public function testFailedRemove()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->expectException('Zend\Mail\Storage\Exception\RuntimeException');
        $mail->removeMessage(1);
    }

    public function testCapa()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);
        $capa = $mail->getCapabilities();
        $this->assertTrue(isset($capa['uniqueid']));
    }

    public function testValid()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertFalse($mail->valid());
        $mail->rewind();
        $this->assertTrue($mail->valid());
    }


    public function testOutOfBounds()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->expectException('Zend\Mail\Storage\Exception\OutOfBoundsException');
        $mail->seek(INF);
    }

    public function testSleepWake()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = $mail->countMessages();
        $content = $mail->getMessage(1)->getContent();

        $serialzed = serialize($mail);
        $mail = null;
        unlink($this->mboxFile);
        // otherwise this test is to fast for a mtime change
        sleep(2);
        copy($this->mboxOriginalFile, $this->mboxFile);
        $mail = unserialize($serialzed);

        $this->assertEquals($mail->countMessages(), $count);
        $this->assertEquals($mail->getMessage(1)->getContent(), $content);
    }

    public function testSleepWakeRemoved()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $count = $mail->countMessages();
        $content = $mail->getMessage(1)->getContent();

        $serialzed = serialize($mail);
        $mail = null;

        $stat = stat($this->mboxFile);
        chmod($this->mboxFile, 0);
        clearstatcache();
        $statcheck = stat($this->mboxFile);
        if ($statcheck['mode'] % (8 * 8 * 8) !== 0) {
            chmod($this->mboxFile, $stat['mode']);
            $this->markTestSkipped(
                'cannot remove read rights, which makes this test useless (maybe you are using Windows?)'
            );
            return;
        }



        $check = false;
        try {
            $mail = unserialize($serialzed);
        } catch (\Exception $e) {
            $check = true;
            // test ok
        }

        chmod($this->mboxFile, $stat['mode']);

        if (! $check) {
            if (function_exists('posix_getuid') && posix_getuid() === 0) {
                $this->markTestSkipped('seems like you are root and we therefore cannot test the error handling');
            } elseif (! function_exists('posix_getuid')) {
                $this->markTestSkipped('Can\t test if you\'re root and we therefore cannot test the error handling');
            }
            $this->fail('no exception while waking with non readable file');
        }
    }

    public function testUniqueId()
    {
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);

        $this->assertFalse($mail->hasUniqueId);
        $this->assertEquals(1, $mail->getNumberByUniqueId($mail->getUniqueId(1)));

        $ids = $mail->getUniqueId();
        foreach ($ids as $num => $id) {
            $this->assertEquals($num, $id);

            if ($mail->getNumberByUniqueId($id) != $num) {
                $this->fail('reverse lookup failed');
            }
        }
    }

    public function testShortMbox()
    {
        $fh = fopen($this->mboxFile, 'w');
        fputs($fh, "From \r\nSubject: test\r\nFrom \r\nSubject: test2\r\n");
        fclose($fh);
        $mail = new Storage\Mbox(['filename' => $this->mboxFile]);
        $this->assertEquals($mail->countMessages(), 2);
        $this->assertEquals($mail->getMessage(1)->subject, 'test');
        $this->assertEquals($mail->getMessage(1)->getContent(), '');
        $this->assertEquals($mail->getMessage(2)->subject, 'test2');
        $this->assertEquals($mail->getMessage(2)->getContent(), '');
    }

    /**
     * @return string
     */
    private function getUnixMboxFile()
    {
        $this->mboxFileUnix = $this->tmpdir . 'INBOX.unix';

        copy(__DIR__ . '/../_files/test.mbox/INBOX.unix', $this->mboxFileUnix);

        return $this->mboxFileUnix;
    }
}

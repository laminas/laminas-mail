<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage;

use PHPUnit\Framework\TestCase;

class MboxMessageOldTest extends TestCase
{
    protected $mboxOriginalFile;
    protected $mboxFile;
    protected $tmpdir;

    public function setUp(): void
    {
        if ($this->tmpdir == null) {
            if (getenv('TESTS_LAMINAS_MAIL_TEMPDIR') != null) {
                $this->tmpdir = getenv('TESTS_LAMINAS_MAIL_TEMPDIR');
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

    public function tearDown(): void
    {
        unlink($this->mboxFile);
    }

    public function testFetchHeader(): void
    {
        $mail = new TestAsset\MboxOldMessage(['filename' => $this->mboxFile]);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

/*
    public function testFetchTopBody()
    {
        $mail = new TestAsset\MboxOldMessage(array('filename' => $this->mboxFile));

        $content = $mail->getHeader(3, 1)->getContent();
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }
*/

    public function testFetchMessageHeader(): void
    {
        $mail = new TestAsset\MboxOldMessage(['filename' => $this->mboxFile]);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageBody(): void
    {
        $mail = new TestAsset\MboxOldMessage(['filename' => $this->mboxFile]);

        $content = $mail->getMessage(3)->getContent();
        list($content) = explode("\n", $content, 2);
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }

    public function testShortMbox(): void
    {
        $fh = fopen($this->mboxFile, 'w');
        fwrite($fh, "From \r\nSubject: test\r\nFrom \r\nSubject: test2\r\n");
        fclose($fh);
        $mail = new TestAsset\MboxOldMessage(['filename' => $this->mboxFile]);
        $this->assertEquals($mail->countMessages(), 2);
        $this->assertEquals($mail->getMessage(1)->subject, 'test');
        $this->assertEquals($mail->getMessage(1)->getContent(), '');
        $this->assertEquals($mail->getMessage(2)->subject, 'test2');
        $this->assertEquals($mail->getMessage(2)->getContent(), '');
    }
}

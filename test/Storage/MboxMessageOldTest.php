<?php

namespace LaminasTest\Mail\Storage;

use PHPUnit\Framework\TestCase;

use function closedir;
use function copy;
use function explode;
use function fclose;
use function file_exists;
use function fopen;
use function fwrite;
use function getenv;
use function mkdir;
use function opendir;
use function readdir;
use function trim;
use function unlink;

class MboxMessageOldTest extends TestCase
{
    /** @var string */
    protected $mboxOriginalFile;
    /** @var string */
    protected $mboxFile;
    /** @var string */
    protected $tmpdir;

    public function setUp(): void
    {
        if (! isset($this->tmpdir)) {
            if (getenv('TESTS_LAMINAS_MAIL_TEMPDIR') != null) {
                $this->tmpdir = getenv('TESTS_LAMINAS_MAIL_TEMPDIR');
            } else {
                $this->tmpdir = __DIR__ . '/../_files/test.tmp/';
            }
            if (! file_exists($this->tmpdir)) {
                mkdir($this->tmpdir);
            }
            $count = 0;
            $dh    = opendir($this->tmpdir);
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
        $this->mboxFile         = $this->tmpdir . 'INBOX';

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

    public function testFetchMessageHeader(): void
    {
        $mail = new TestAsset\MboxOldMessage(['filename' => $this->mboxFile]);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageBody(): void
    {
        $mail = new TestAsset\MboxOldMessage(['filename' => $this->mboxFile]);

        $content   = $mail->getMessage(3)->getContent();
        [$content] = explode("\n", $content, 2);
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

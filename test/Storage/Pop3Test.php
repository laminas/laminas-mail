<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage;

use Laminas\Config;
use Laminas\Mail\Protocol;
use Laminas\Mail\Storage;
use Laminas\Mail\Storage\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Storage\Pop3<extended>
 */
class Pop3Test extends TestCase
{
    protected $params;

    public function setUp(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_POP3_ENABLED')) {
            $this->markTestSkipped('Laminas_Mail POP3 tests are not enabled');
        }

        $this->params = [
            'host'     => getenv('TESTS_LAMINAS_MAIL_POP3_HOST'),
            'user'     => getenv('TESTS_LAMINAS_MAIL_POP3_USER'),
            'password' => getenv('TESTS_LAMINAS_MAIL_POP3_PASSWORD'),
        ];

        if (getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR') && getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR')) {
            if (! file_exists(getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR') . DIRECTORY_SEPARATOR . 'inbox')
                && ! file_exists(getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR') . DIRECTORY_SEPARATOR . 'INBOX')
            ) {
                $this->markTestSkipped(
                    'There is no file name "inbox" or "INBOX" in '
                    . getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR') . '. I won\'t use it for testing. '
                    . 'This is you safety net. If you think it is the right directory just '
                    . 'create an empty file named INBOX or remove/deactived this message.'
                );
            }

            $this->cleanDir(getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR'));
            $this->copyDir(
                __DIR__ . '/../_files/test.' . getenv('TESTS_LAMINAS_MAIL_SERVER_FORMAT'),
                getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR')
            );
        }
    }

    protected function cleanDir($dir): void
    {
        $dh = opendir($dir);
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $fullname = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($fullname)) {
                $this->cleanDir($fullname);
                rmdir($fullname);
            } else {
                unlink($fullname);
            }
        }
        closedir($dh);
    }

    protected function copyDir($dir, $dest): void
    {
        $dh = opendir($dir);
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..' || $entry == '.svn') {
                continue;
            }
            $fullname = $dir  . DIRECTORY_SEPARATOR . $entry;
            $destname = $dest . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($fullname)) {
                mkdir($destname);
                $this->copyDir($fullname, $destname);
            } else {
                copy($fullname, $destname);
            }
        }
        closedir($dh);
    }

    public function testConnectOk(): void
    {
        new Storage\Pop3($this->params);
    }

    public function testConnectConfig(): void
    {
        new Storage\Pop3(new Config\Config($this->params));
    }

    public function testConnectFailure(): void
    {
        $this->params['host'] = 'example.example';

        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Pop3($this->params);
    }

    public function testNoParams(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Pop3([]);
    }

    public function testConnectSSL(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_POP3_SSL')) {
            return;
        }

        $this->params['ssl'] = 'SSL';

        new Storage\Pop3($this->params);
    }

    public function testConnectTLS(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_POP3_TLS')) {
            return;
        }

        $this->params['ssl'] = 'TLS';

        new Storage\Pop3($this->params);
    }

    public function testConnectSelfSignedSSL(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_POP3_SSL')) {
            return;
        }

        $this->params['ssl'] = 'SSL';
        $this->params['novalidatecert'] = true;

        new Storage\Pop3($this->params);
    }

    public function testInvalidService(): void
    {
        $this->params['port'] = getenv('TESTS_LAMINAS_MAIL_POP3_INVALID_PORT');

        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Pop3($this->params);
    }

    public function testWrongService(): void
    {
        $this->params['port'] = getenv('TESTS_LAMINAS_MAIL_POP3_WRONG_PORT');

        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Pop3($this->params);
    }

    public function testClose(): void
    {
        $mail = new Storage\Pop3($this->params);

        $mail->close();
    }

    public function testHasTop(): void
    {
        $mail = new Storage\Pop3($this->params);

        $this->assertTrue($mail->hasTop);
    }

    public function testHasCreate(): void
    {
        $mail = new Storage\Pop3($this->params);

        $this->assertFalse($mail->hasCreate);
    }

    public function testNoop(): void
    {
        $mail = new Storage\Pop3($this->params);

        $mail->noop();
    }

    public function testCount(): void
    {
        $mail = new Storage\Pop3($this->params);

        $count = $mail->countMessages();
        $this->assertEquals(7, $count);
    }

    public function testSize(): void
    {
        $mail = new Storage\Pop3($this->params);
        $shouldSizes = [1 => 397, 89, 694, 452, 497, 101, 139];

        $sizes = $mail->getSize();
        $this->assertEquals($shouldSizes, $sizes);
    }

    public function testSingleSize(): void
    {
        $mail = new Storage\Pop3($this->params);

        $size = $mail->getSize(2);
        $this->assertEquals(89, $size);
    }

    public function testFetchHeader(): void
    {
        $mail = new Storage\Pop3($this->params);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

/*
    public function testFetchTopBody()
    {
        $mail = new Storage\Pop3($this->params);

        $content = $mail->getHeader(3, 1)->getContent();
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }
*/

    public function testFetchMessageHeader(): void
    {
        $mail = new Storage\Pop3($this->params);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageBody(): void
    {
        $mail = new Storage\Pop3($this->params);

        $content = $mail->getMessage(3)->getContent();
        list($content) = explode("\n", $content, 2);
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }

/*
    public function testFailedRemove()
    {
        $mail = new Laminas_Mail_Storage_Pop3($this->params);

        try {
            $mail->removeMessage(1);
        } catch (Exception $e) {
            return; // test ok
        }

        $this->fail('no exception raised while deleting message (mbox is read-only)');
    }
*/

    public function testWithInstanceConstruction(): void
    {
        $protocol = new Protocol\Pop3($this->params['host']);
        $mail = new Storage\Pop3($protocol);

        $this->expectException(Exception\InvalidArgumentException::class);
        // because we did no login this has to throw an exception
        $mail->getMessage(1);
    }

    public function testRequestAfterClose(): void
    {
        $mail = new Storage\Pop3($this->params);
        $mail->close();

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getMessage(1);
    }

    public function testServerCapa(): void
    {
        $mail = new Protocol\Pop3($this->params['host']);
        $this->assertInternalType('array', $mail->capa());
    }

    public function testServerUidl(): void
    {
        $mail = new Protocol\Pop3($this->params['host']);
        $mail->login($this->params['user'], $this->params['password']);

        $uids = $mail->uniqueid();
        $this->assertEquals(count($uids), 7);

        $this->assertEquals($uids[1], $mail->uniqueid(1));
    }

    public function testRawHeader(): void
    {
        $mail = new Storage\Pop3($this->params);

        $this->assertContains("\r\nSubject: Simple Message\r\n", $mail->getRawHeader(1));
    }

    public function testUniqueId(): void
    {
        $mail = new Storage\Pop3($this->params);

        $this->assertTrue($mail->hasUniqueId);
        $this->assertEquals(1, $mail->getNumberByUniqueId($mail->getUniqueId(1)));

        $ids = $mail->getUniqueId();
        foreach ($ids as $num => $id) {
            foreach ($ids as $inner_num => $inner_id) {
                if ($num == $inner_num) {
                    continue;
                }
                if ($id == $inner_id) {
                    $this->fail('not all ids are unique');
                }
            }

            if ($mail->getNumberByUniqueId($id) != $num) {
                $this->fail('reverse lookup failed');
            }
        }
    }

    public function testWrongUniqueId(): void
    {
        $mail = new Storage\Pop3($this->params);

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getNumberByUniqueId('this_is_an_invalid_id');
    }

    public function testReadAfterClose(): void
    {
        $protocol = new Protocol\Pop3($this->params['host']);
        $protocol->logout();

        $this->expectException(Exception\InvalidArgumentException::class);
        $protocol->readResponse();
    }

    public function testRemove(): void
    {
        $mail = new Storage\Pop3($this->params);
        $count = $mail->countMessages();

        $mail->removeMessage(1);
        $this->assertEquals($mail->countMessages(), --$count);

        unset($mail[2]);
        $this->assertEquals($mail->countMessages(), --$count);
    }

    public function testDotMessage(): void
    {
        $mail = new Storage\Pop3($this->params);
        $content = '';
        $content .= "Before the dot\r\n";
        $content .= ".\r\n";
        $content .= "is after the dot\r\n";
        $this->assertEquals($mail->getMessage(7)->getContent(), $content);
    }
}

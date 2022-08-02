<?php

namespace LaminasTest\Mail\Storage;

use ArrayObject;
use Laminas\Mail\Protocol;
use Laminas\Mail\Storage;
use Laminas\Mail\Storage\Exception;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

use function array_combine;
use function closedir;
use function copy;
use function explode;
use function file_exists;
use function getenv;
use function is_dir;
use function mkdir;
use function opendir;
use function range;
use function readdir;
use function rmdir;
use function strlen;
use function trim;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const INF;

/**
 * @covers Laminas\Mail\Storage\Imap<extended>
 */
class ImapTest extends TestCase
{
    /** @var array */
    protected $params;

    public function setUp(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_IMAP_ENABLED')) {
            $this->markTestSkipped('Laminas_Mail IMAP tests are not enabled');
        }
        $this->params = [
            'host'     => getenv('TESTS_LAMINAS_MAIL_IMAP_HOST'),
            'user'     => getenv('TESTS_LAMINAS_MAIL_IMAP_USER'),
            'password' => getenv('TESTS_LAMINAS_MAIL_IMAP_PASSWORD'),
        ];
        if (getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR') && getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR')) {
            if (
                ! file_exists(getenv('TESTS_LAMINAS_MAIL_SERVER_TESTDIR') . DIRECTORY_SEPARATOR . 'inbox')
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

    protected function cleanDir(string $dir): void
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

    protected function copyDir(string $dir, string $dest): void
    {
        $dh = opendir($dir);
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..' || $entry == '.svn') {
                continue;
            }
            $fullname = $dir . DIRECTORY_SEPARATOR . $entry;
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
        new Storage\Imap($this->params);
    }

    public function testConnectConfig(): void
    {
        new Storage\Imap(new ArrayObject($this->params));
    }

    public function testConnectFailure(): void
    {
        $this->params['host'] = 'example.example';
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($this->params);
    }

    public function testNoParams(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap([]);
    }

    public function testConnectSSL(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_IMAP_SSL')) {
            return;
        }

        $this->params['ssl'] = 'SSL';
        new Storage\Imap($this->params);
    }

    public function testConnectTLS(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_IMAP_TLS')) {
            return;
        }

        $this->params['ssl'] = 'TLS';
        new Storage\Imap($this->params);
    }

    public function testConnectSelfSignedSSL(): void
    {
        if (! getenv('TESTS_LAMINAS_MAIL_IMAP_SSL')) {
            return;
        }

        $this->params['ssl']            = 'SSL';
        $this->params['novalidatecert'] = true;
        new Storage\Imap($this->params);
    }

    public function testInvalidService(): void
    {
        $this->params['port'] = getenv('TESTS_LAMINAS_MAIL_IMAP_INVALID_PORT');
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($this->params);
    }

    public function testWrongService(): void
    {
        $this->params['port'] = getenv('TESTS_LAMINAS_MAIL_IMAP_WRONG_PORT');
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($this->params);
    }

    public function testWrongUsername(): void
    {
        // this also triggers ...{chars}<NL>token for coverage
        $this->params['user'] = "there is no\nnobody";
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($this->params);
    }

    public function testWithInstanceConstruction(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        // if $protocol is invalid the constructor fails while selecting INBOX
        new Storage\Imap($protocol);
    }

    public function testWithNotConnectedInstance(): void
    {
        $protocol = new Protocol\Imap();
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($protocol);
    }

    public function testWithNotLoggedInstance(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($protocol);
    }

    public function testWrongFolder(): void
    {
        $this->params['folder'] = 'this folder does not exist on your server';

        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($this->params);
    }

    public function testClose(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->close();
    }

    public function testHasCreate(): void
    {
        $mail = new Storage\Imap($this->params);

        $this->assertFalse($mail->hasCreate);
    }

    public function testNoop(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->noop();
    }

    public function testCount(): void
    {
        $mail = new Storage\Imap($this->params);

        $count = $mail->countMessages();
        $this->assertEquals(7, $count);
    }

    public function testSize(): void
    {
        $mail        = new Storage\Imap($this->params);
        $shouldSizes = [1 => 397, 89, 694, 452, 497, 101, 139];

        $sizes = $mail->getSize();
        $this->assertEquals($shouldSizes, $sizes);
    }

    public function testSingleSize(): void
    {
        $mail = new Storage\Imap($this->params);

        $size = $mail->getSize(2);
        $this->assertEquals(89, $size);
    }

    public function testFetchHeader(): void
    {
        $mail = new Storage\Imap($this->params);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageHeader(): void
    {
        $mail = new Storage\Imap($this->params);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);
    }

    public function testFetchMessageBody(): void
    {
        $mail = new Storage\Imap($this->params);

        $content   = $mail->getMessage(3)->getContent();
        [$content] = explode("\n", $content, 2);
        $this->assertEquals('Fair river! in thy bright, clear flow', trim($content));
    }

    public function testRemove(): void
    {
        $mail = new Storage\Imap($this->params);

        $count = $mail->countMessages();
        $mail->removeMessage(1);
        $this->assertEquals($mail->countMessages(), $count - 1);
    }

    public function testTooLateCount(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->close();
        // after closing we can't count messages

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->countMessages();
    }

    public function testLoadUnkownFolder(): void
    {
        $this->params['folder'] = 'UnknownFolder';
        $this->expectException(Exception\InvalidArgumentException::class);
        new Storage\Imap($this->params);
    }

    public function testChangeFolder(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->selectFolder('subfolder/test');

        $this->assertEquals($mail->getCurrentFolder(), 'subfolder/test');
    }

    public function testUnknownFolder(): void
    {
        $mail = new Storage\Imap($this->params);
        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->selectFolder('/Unknown/Folder/');
    }

    public function testGlobalName(): void
    {
        $mail = new Storage\Imap($this->params);
        $this->assertEquals($mail->getFolders()->subfolder->__toString(), 'subfolder');
    }

    public function testLocalName(): void
    {
        $mail = new Storage\Imap($this->params);
        $this->assertEquals($mail->getFolders()->subfolder->key(), 'test');
    }

    public function testKeyLocalName(): void
    {
        $mail     = new Storage\Imap($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);
        // we search for this folder because we can't assume an order while iterating
        $searchFolders = [
            'subfolder'      => 'subfolder',
            'subfolder/test' => 'test',
            'INBOX'          => 'INBOX',
        ];
        $foundFolders  = [];

        foreach ($iterator as $localName => $folder) {
            if (! isset($searchFolders[$folder->getGlobalName()])) {
                continue;
            }

            // explicit call of __toString() needed for PHP < 5.2
            $foundFolders[$folder->__toString()] = $localName;
        }

        $this->assertEquals($searchFolders, $foundFolders);
    }

    public function testSelectable(): void
    {
        $mail     = new Storage\Imap($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $localName => $folder) {
            $this->assertEquals($localName, $folder->getLocalName());
        }
    }

    public function testCountFolder(): void
    {
        $mail = new Storage\Imap($this->params);

        $mail->selectFolder('subfolder/test');
        $count = $mail->countMessages();
        $this->assertEquals(1, $count);
    }

    public function testSizeFolder(): void
    {
        $mail = new Storage\Imap($this->params);

        $mail->selectFolder('subfolder/test');
        $sizes = $mail->getSize();
        $this->assertEquals([1 => 410], $sizes);
    }

    public function testFetchHeaderFolder(): void
    {
        $mail = new Storage\Imap($this->params);

        $mail->selectFolder('subfolder/test');
        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Message in subfolder', $subject);
    }

    public function testHasFlag(): void
    {
        $mail = new Storage\Imap($this->params);

        $this->assertTrue($mail->getMessage(1)->hasFlag(Storage::FLAG_RECENT));
    }

    public function testGetFlags(): void
    {
        $mail = new Storage\Imap($this->params);

        $flags = $mail->getMessage(1)->getFlags();
        $this->assertTrue(isset($flags[Storage::FLAG_RECENT]));
        $this->assertContains(Storage::FLAG_RECENT, $flags);
    }

    public function testRawHeader(): void
    {
        $mail = new Storage\Imap($this->params);

        $this->assertContains("\r\nSubject: Simple Message\r\n", $mail->getRawHeader(1));
    }

    public function testUniqueId(): void
    {
        $mail = new Storage\Imap($this->params);

        $this->assertTrue($mail->hasUniqueId);
        $this->assertEquals(1, $mail->getNumberByUniqueId($mail->getUniqueId(1)));

        $ids = $mail->getUniqueId();
        foreach ($ids as $num => $id) {
            foreach ($ids as $innerNum => $innerId) {
                if ($num == $innerNum) {
                    continue;
                }
                if ($id == $innerId) {
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
        $mail = new Storage\Imap($this->params);
        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getNumberByUniqueId('this_is_an_invalid_id');
    }

    public function testCreateFolder(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->createFolder('subfolder/test1');
        $mail->createFolder('test2', 'subfolder');
        $mail->createFolder('test3', $mail->getFolders()->subfolder);

        $mail->getFolders()->subfolder->test1;
        $mail->getFolders()->subfolder->test2;
        $mail->getFolders()->subfolder->test3;
    }

    public function testCreateExistingFolder(): void
    {
        $mail = new Storage\Imap($this->params);

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->createFolder('subfolder/test');
    }

    public function testRemoveFolderName(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->removeFolder('subfolder/test');

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getFolders()->subfolder->test;
    }

    public function testRemoveFolderInstance(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->removeFolder($mail->getFolders()->subfolder->test);

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getFolders()->subfolder->test;
    }

    public function testRemoveInvalidFolder(): void
    {
        $mail = new Storage\Imap($this->params);

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->removeFolder('thisFolderDoestNotExist');
    }

    public function testRenameFolder(): void
    {
        $mail = new Storage\Imap($this->params);

        $mail->renameFolder('subfolder/test', 'subfolder/test1');
        $mail->renameFolder($mail->getFolders()->subfolder->test1, 'subfolder/test');

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->renameFolder('subfolder/test', 'INBOX');
    }

    public function testAppend(): void
    {
        $mail  = new Storage\Imap($this->params);
        $count = $mail->countMessages();

        $message  = '';
        $message .= "From: me@example.org\r\n";
        $message .= "To: you@example.org\r\n";
        $message .= "Subject: append test\r\n";
        $message .= "\r\n";
        $message .= "This is a test\r\n";
        $mail->appendMessage($message);

        $this->assertEquals($count + 1, $mail->countMessages());
        $this->assertEquals($mail->getMessage($count + 1)->subject, 'append test');

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->appendMessage('');
    }

    public function testCopy(): void
    {
        $mail = new Storage\Imap($this->params);

        $mail->selectFolder('subfolder/test');
        $count = $mail->countMessages();
        $mail->selectFolder('INBOX');
        $message = $mail->getMessage(1);

        $mail->copyMessage(1, 'subfolder/test');
        $mail->selectFolder('subfolder/test');
        $this->assertEquals($count + 1, $mail->countMessages());
        $this->assertEquals($mail->getMessage($count + 1)->subject, $message->subject);
        $this->assertEquals($mail->getMessage($count + 1)->from, $message->from);
        $this->assertEquals($mail->getMessage($count + 1)->to, $message->to);

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->copyMessage(1, 'justARandomFolder');
    }

    public function testSetFlags(): void
    {
        $mail = new Storage\Imap($this->params);

        $mail->setFlags(1, [Storage::FLAG_SEEN]);
        $message = $mail->getMessage(1);
        $this->assertTrue($message->hasFlag(Storage::FLAG_SEEN));
        $this->assertFalse($message->hasFlag(Storage::FLAG_FLAGGED));

        $mail->setFlags(1, [Storage::FLAG_SEEN, Storage::FLAG_FLAGGED]);
        $message = $mail->getMessage(1);
        $this->assertTrue($message->hasFlag(Storage::FLAG_SEEN));
        $this->assertTrue($message->hasFlag(Storage::FLAG_FLAGGED));

        $mail->setFlags(1, [Storage::FLAG_FLAGGED]);
        $message = $mail->getMessage(1);
        $this->assertFalse($message->hasFlag(Storage::FLAG_SEEN));
        $this->assertTrue($message->hasFlag(Storage::FLAG_FLAGGED));

        $mail->setFlags(1, ['myflag']);
        $message = $mail->getMessage(1);
        $this->assertFalse($message->hasFlag(Storage::FLAG_SEEN));
        $this->assertFalse($message->hasFlag(Storage::FLAG_FLAGGED));
        $this->assertTrue($message->hasFlag('myflag'));

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->setFlags(1, [Storage::FLAG_RECENT]);
    }

    /**
     * @group 7353
     */
    public function testCanMarkMessageUnseen(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->setFlags(1, [Storage::FLAG_UNSEEN]);
        $message = $mail->getMessage(1);
        $this->assertTrue($message->hasFlag(Storage::FLAG_UNSEEN));
    }

    public function testCapability(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $capa = $protocol->capability();
        $this->assertInternalType('array', $capa);
        $this->assertEquals($capa[0], 'CAPABILITY');
    }

    public function testSelect(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $status = $protocol->select('INBOX');
        $this->assertInternalType('array', $status['flags']);
        $this->assertEquals($status['exists'], 7);
    }

    public function testExamine(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $status = $protocol->examine('INBOX');
        $this->assertInternalType('array', $status['flags']);
        $this->assertEquals($status['exists'], 7);
    }

    public function testClosedSocketNewlineToken(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $protocol->logout();

        $this->expectException(Exception\InvalidArgumentException::class);
        $protocol->select("foo\nbar");
    }

    public function testEscaping(): void
    {
        $protocol = new Protocol\Imap();
        $this->assertEquals($protocol->escapeString('foo'), '"foo"');
        $this->assertEquals($protocol->escapeString('f\\oo'), '"f\\\\oo"');
        $this->assertEquals($protocol->escapeString('f"oo'), '"f\\"oo"');
        $this->assertEquals($protocol->escapeString('foo', 'bar'), ['"foo"', '"bar"']);
        $this->assertEquals($protocol->escapeString("f\noo"), ['{4}', "f\noo"]);
        $this->assertEquals($protocol->escapeList(['foo']), '(foo)');
        $this->assertEquals($protocol->escapeList([['foo']]), '((foo))');
        $this->assertEquals($protocol->escapeList(['foo', 'bar']), '(foo bar)');
    }

    public function testFetch(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $protocol->select('INBOX');

        $range = array_combine(range(1, 7), range(1, 7));
        $this->assertEquals($protocol->fetch('UID', 1, INF), $range);
        $this->assertEquals($protocol->fetch('UID', 1, 7), $range);
        $this->assertEquals($protocol->fetch('UID', range(1, 7)), $range);
        $this->assertInternalType('numeric', $protocol->fetch('UID', 1));

        $result = $protocol->fetch(['UID', 'FLAGS'], 1, INF);
        foreach ($result as $k => $v) {
            $this->assertEquals($k, $v['UID']);
            $this->assertInternalType('array', $v['FLAGS']);
        }

        $this->expectException(Exception\InvalidArgumentException::class);
        $protocol->fetch('UID', 99);
    }

    public function testFetchByUid(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $protocol->select('INBOX');

        $result  = $protocol->fetch(['UID', 'FLAGS'], 1);
        $uid     = $result['UID'];
        $message = $protocol->fetch(['UID', 'FLAGS'], $uid, null, true);
        $this->assertEquals($uid, $message['UID']);
    }

    public function testStore(): void
    {
        $protocol = new Protocol\Imap($this->params['host']);
        $protocol->login($this->params['user'], $this->params['password']);
        $protocol->select('INBOX');

        $this->assertTrue($protocol->store(['\Flagged'], 1));
        $this->assertTrue($protocol->store(['\Flagged'], 1, null, '-'));
        $this->assertTrue($protocol->store(['\Flagged'], 1, null, '+'));

        $result = $protocol->store(['\Flagged'], 1, null, '', false);
        $this->assertContains('\Flagged', $result[1]);
        $result = $protocol->store(['\Flagged'], 1, null, '-', false);
        $this->assertStringNotContainsString('\Flagged', $result[1]);
        $result = $protocol->store(['\Flagged'], 1, null, '+', false);
        $this->assertContains('\Flagged', $result[1]);
    }

    public function testMove(): void
    {
        $mail = new Storage\Imap($this->params);
        $mail->selectFolder('subfolder/test');
        $toCount = $mail->countMessages();
        $mail->selectFolder('INBOX');
        $fromCount = $mail->countMessages();
        $mail->moveMessage(1, 'subfolder/test');

        $this->assertEquals($fromCount - 1, $mail->countMessages());
        $mail->selectFolder('subfolder/test');
        $this->assertEquals($toCount + 1, $mail->countMessages());
    }

    public function testCountFlags(): void
    {
        $mail = new Storage\Imap($this->params);
        foreach ($mail as $id => $message) {
            $mail->setFlags($id, []);
        }
        $this->assertEquals($mail->countMessages(Storage::FLAG_SEEN), 0);
        $this->assertEquals($mail->countMessages(Storage::FLAG_ANSWERED), 0);
        $this->assertEquals($mail->countMessages(Storage::FLAG_FLAGGED), 0);

        $mail->setFlags(1, [Storage::FLAG_SEEN, Storage::FLAG_ANSWERED]);
        $mail->setFlags(2, [Storage::FLAG_SEEN]);
        $this->assertEquals($mail->countMessages(Storage::FLAG_SEEN), 2);
        $this->assertEquals($mail->countMessages(Storage::FLAG_ANSWERED), 1);
        $this->assertEquals($mail->countMessages([Storage::FLAG_SEEN, Storage::FLAG_ANSWERED]), 1);
        $this->assertEquals($mail->countMessages([Storage::FLAG_SEEN, Storage::FLAG_FLAGGED]), 0);
        $this->assertEquals($mail->countMessages(Storage::FLAG_FLAGGED), 0);
    }

    public function testDelimiter(): void
    {
        $mail      = new Storage\Imap($this->params);
        $delimiter = $mail->delimiter();
        $this->assertEquals(strlen($delimiter), 1);
    }
}

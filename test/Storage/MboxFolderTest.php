<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage;

use Laminas\Config;
use Laminas\Mail\Storage\Exception;
use Laminas\Mail\Storage\Folder;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

/**
 * @group      Laminas_Mail
 */
class MboxFolderTest extends TestCase
{
    protected $params;
    protected $originalDir;
    protected $tmpdir;
    protected $subdirs = ['.', 'subfolder'];

    public function setUp(): void
    {
        $this->originalDir = __DIR__ . '/../_files/test.mbox/';

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

        $this->params = [];
        $this->params['dirname'] = $this->tmpdir;
        $this->params['folder']  = 'INBOX';

        foreach ($this->subdirs as $dir) {
            if ($dir != '.') {
                mkdir($this->tmpdir . $dir);
            }
            $dh = opendir($this->originalDir . $dir);
            while (($entry = readdir($dh)) !== false) {
                $entry = $dir . '/' . $entry;
                if (! is_file($this->originalDir . $entry)) {
                    continue;
                }
                copy($this->originalDir . $entry, $this->tmpdir . $entry);
            }
            closedir($dh);
        }
    }

    public function tearDown(): void
    {
        foreach (array_reverse($this->subdirs) as $dir) {
            $dh = opendir($this->tmpdir . $dir);
            while (($entry = readdir($dh)) !== false) {
                $entry = $this->tmpdir . $dir . '/' . $entry;
                if (! is_file($entry)) {
                    continue;
                }
                unlink($entry);
            }
            closedir($dh);
            if ($dir != '.') {
                rmdir($this->tmpdir . $dir);
            }
        }
    }

    public function testLoadOk(): void
    {
        new Folder\Mbox($this->params);
        $this->addToAssertionCount(1);
    }

    public function testLoadConfig(): void
    {
        new Folder\Mbox(new Config\Config($this->params));
        $this->addToAssertionCount(1);
    }

    public function testNoParams(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Folder\Mbox([]);
    }

    public function testFilenameParam(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        // filename is not allowed in this subclass
        new Folder\Mbox(['filename' => 'foobar']);
    }

    public function testLoadFailure(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Folder\Mbox(['dirname' => 'This/Folder/Does/Not/Exist']);
    }

    public function testLoadUnknownFolder(): void
    {
        $this->params['folder'] = 'UnknownFolder';

        $this->expectException(Exception\InvalidArgumentException::class);
        new Folder\Mbox($this->params);
    }

    public function testChangeFolder(): void
    {
        $mail = new Folder\Mbox($this->params);

        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test');

        $this->assertEquals(
            $mail->getCurrentFolder(),
            DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test'
        );
    }

    public function testChangeFolderUnselectable(): void
    {
        $mail = new Folder\Mbox($this->params);
        $this->expectException(Exception\RuntimeException::class);
        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder');
    }

    public function testUnknownFolder(): void
    {
        $mail = new Folder\Mbox($this->params);
        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->selectFolder('/Unknown/Folder/');
    }

    public function testGlobalName(): void
    {
        $mail = new Folder\Mbox($this->params);

        $this->assertEquals($mail->getFolders()->subfolder->__toString(), DIRECTORY_SEPARATOR . 'subfolder');
    }

    public function testLocalName(): void
    {
        $mail = new Folder\Mbox($this->params);

        $this->assertEquals($mail->getFolders()->subfolder->key(), 'test');
    }

    public function testIterator(): void
    {
        $mail = new Folder\Mbox($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);

        // we search for this folder because we cannot assume an order while iterating
        $search_folders = [
            DIRECTORY_SEPARATOR . 'subfolder'                                => 'subfolder',
            DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test' => 'test',
            DIRECTORY_SEPARATOR . 'INBOX'                                    => 'INBOX',
        ];
        $found_folders = [];

        foreach ($iterator as $localName => $folder) {
            if (! isset($search_folders[$folder->getGlobalName()])) {
                continue;
            }

            // explicit call of __toString() needed for PHP < 5.2
            $found_folders[$folder->__toString()] = $localName;
        }

        $this->assertEquals($search_folders, $found_folders);
    }

    public function testKeyLocalName(): void
    {
        $mail = new Folder\Mbox($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);
        // we search for this folder because we cannot assume an order while iterating
        $search_folders = [
            DIRECTORY_SEPARATOR . 'subfolder'                                => 'subfolder',
            DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test' => 'test',
            DIRECTORY_SEPARATOR . 'INBOX'                                    => 'INBOX',
        ];
        $found_folders = [];

        foreach ($iterator as $localName => $folder) {
            if (! isset($search_folders[$folder->getGlobalName()])) {
                continue;
            }

            // explicit call of __toString() needed for PHP < 5.2
            $found_folders[$folder->__toString()] = $localName;
        }

        $this->assertEquals($search_folders, $found_folders);
    }

    public function testSelectable(): void
    {
        $mail = new Folder\Mbox($this->params);
        $iterator = new RecursiveIteratorIterator($mail->getFolders(), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $localName => $folder) {
            $this->assertEquals($localName, $folder->getLocalName());
        }
    }

    public function testCount(): void
    {
        $mail = new Folder\Mbox($this->params);

        $count = $mail->countMessages();
        $this->assertEquals(7, $count);

        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test');
        $count = $mail->countMessages();
        $this->assertEquals(1, $count);
    }

    public function testSize(): void
    {
        $mail = new Folder\Mbox($this->params);
        $shouldSizes = [1 => 397, 89, 694, 452, 497, 101, 139];

        $sizes = $mail->getSize();
        $this->assertEquals($shouldSizes, $sizes);

        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test');
        $sizes = $mail->getSize();
        $this->assertEquals([1 => 410], $sizes);
    }

    public function testFetchHeader(): void
    {
        $mail = new Folder\Mbox($this->params);

        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Simple Message', $subject);

        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test');
        $subject = $mail->getMessage(1)->subject;
        $this->assertEquals('Message in subfolder', $subject);
    }

    public function testSleepWake(): void
    {
        $mail = new Folder\Mbox($this->params);

        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test');
        $count = $mail->countMessages();
        $content = $mail->getMessage(1)->getContent();

        $serialzed = serialize($mail);
        $mail = unserialize($serialzed);

        $this->assertEquals($mail->countMessages(), $count);
        $this->assertEquals($mail->getMessage(1)->getContent(), $content);

        $mail->selectFolder(DIRECTORY_SEPARATOR . 'subfolder' . DIRECTORY_SEPARATOR . 'test');
        $this->assertEquals($mail->countMessages(), $count);
        $this->assertEquals($mail->getMessage(1)->getContent(), $content);
    }

    public function testNotMboxFile(): void
    {
        touch($this->params['dirname'] . 'foobar');
        $mail = new Folder\Mbox($this->params);

        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getFolders()->foobar;
    }

    public function testNotReadableFolder(): void
    {
        $stat = stat($this->params['dirname'] . 'subfolder');
        chmod($this->params['dirname'] . 'subfolder', 0);
        clearstatcache();
        $statcheck = stat($this->params['dirname'] . 'subfolder');
        if ($statcheck['mode'] % (8 * 8 * 8) !== 0) {
            chmod($this->params['dirname'] . 'subfolder', $stat['mode']);
            $this->markTestSkipped(
                'cannot remove read rights, which makes this test useless (maybe you are using Windows?)'
            );
            return;
        }

        $check = false;
        try {
            $mail = new Folder\Mbox($this->params);
        } catch (\Exception $e) {
            $check = true;
            // test ok
        }

        chmod($this->params['dirname'] . 'subfolder', $stat['mode']);

        if (! $check) {
            if (function_exists('posix_getuid') && posix_getuid() === 0) {
                $this->markTestSkipped('seems like you are root and we therefore cannot test the error handling');
            } elseif (! function_exists('posix_getuid')) {
                $this->markTestSkipped('Can\t test if you\'re root and we therefore cannot test the error handling');
            }
            $this->fail('no exception while loading invalid dir with subfolder not readable');
        }
    }

    public function testGetInvalidFolder(): void
    {
        $mail = new Folder\Mbox($this->params);
        $root = $mail->getFolders();
        $root->foobar = new Folder('x', 'x');
        $this->expectException(Exception\InvalidArgumentException::class);
        $mail->getFolders('foobar');
    }

    public function testGetVanishedFolder(): void
    {
        $mail = new Folder\Mbox($this->params);
        $root = $mail->getFolders();
        $root->foobar = new Folder('foobar', DIRECTORY_SEPARATOR . 'foobar');

        $this->expectException(Exception\RuntimeException::class);
        $mail->selectFolder('foobar');
    }
}

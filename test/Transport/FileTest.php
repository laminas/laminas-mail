<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\FileOptions;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Transport\File<extended>
 */
class FileTest extends TestCase
{
    /** @var string */
    private $tempDir;

    public function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/mail_file_transport';
        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        } else {
            $this->cleanup($this->tempDir);
        }

        $fileOptions = new FileOptions([
            'path' => $this->tempDir,
        ]);
        $this->transport  = new File($fileOptions);
    }

    public function tearDown(): void
    {
        $this->cleanup($this->tempDir);
        rmdir($this->tempDir);
    }

    protected function cleanup($dir): void
    {
        foreach (glob($dir . '/*.*') as $file) {
            unlink($file);
        }
    }

    public function getMessage(): Message
    {
        $message = new Message();
        $message->addTo('test@example.com', 'Example Test')
                ->addCc('matthew@example.com')
                ->addBcc('list@example.com', 'Example List')
                ->addFrom([
                    'test@example.com',
                    'matthew@example.com' => 'Matthew',
                ])
                ->setSender('ralph@example.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');
        $message->getHeaders()->addHeaders([
            'X-Foo-Bar' => 'Matthew',
        ]);
        return $message;
    }

    public function testReceivesMailArtifacts(): void
    {
        $message = $this->getMessage();
        $this->transport->send($message);

        $this->assertNotNull($this->transport->getLastFile());
        $file = $this->transport->getLastFile();
        $test = file_get_contents($file);

        $this->assertEquals($message->toString(), $test);
    }

    public function testConstructorNoOptions(): void
    {
        $transport = new File();
        $this->assertSame(FileOptions::class, \get_class($transport->getOptions()));
    }
}

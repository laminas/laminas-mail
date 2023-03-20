<?php

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\FileOptions;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function glob;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Transport\File<extended>
 */
class FileTest extends TestCase
{
    private string $tempDir;
    private File $transport;

    public function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/mail_file_transport';
        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        } else {
            $this->cleanup($this->tempDir);
        }

        $fileOptions     = new FileOptions([
            'path' => $this->tempDir,
        ]);
        $this->transport = new File($fileOptions);
    }

    public function tearDown(): void
    {
        $this->cleanup($this->tempDir);
        rmdir($this->tempDir);
    }

    protected function cleanup(string $dir): void
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
        $this->assertSame(FileOptions::class, $transport->getOptions()::class);
    }
}

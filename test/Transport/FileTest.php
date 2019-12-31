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

/**
 * @group      Laminas_Mail
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tempDir = sys_get_temp_dir() . '/mail_file_transport';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        } else {
            $this->cleanup($this->tempDir);
        }

        $fileOptions = new FileOptions([
            'path' => $this->tempDir,
        ]);
        $this->transport  = new File($fileOptions);
    }

    public function tearDown()
    {
        $this->cleanup($this->tempDir);
        rmdir($this->tempDir);
    }

    protected function cleanup($dir)
    {
        foreach (glob($dir . '/*.*') as $file) {
            unlink($file);
        }
    }

    public function getMessage()
    {
        $message = new Message();
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam')
                ->addCc('matthew@zend.com')
                ->addBcc('api-tools-crteam@lists.zend.com', 'CR-Team, Laminas Project')
                ->addFrom([
                    'api-tools-devteam@zend.com',
                    'matthew@zend.com' => 'Matthew',
                ])
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');
        $message->getHeaders()->addHeaders([
            'X-Foo-Bar' => 'Matthew',
        ]);
        return $message;
    }

    public function testReceivesMailArtifacts()
    {
        $message = $this->getMessage();
        $this->transport->send($message);

        $this->assertNotNull($this->transport->getLastFile());
        $file = $this->transport->getLastFile();
        $test = file_get_contents($file);

        $this->assertEquals($message->toString(), $test);
    }
}

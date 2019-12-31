<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;

/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage UnitTests
 * @group      Laminas_Mail
 */
class SendmailTest extends \PHPUnit_Framework_TestCase
{
    public $transport;
    public $to;
    public $subject;
    public $message;
    public $additional_headers;
    public $additional_parameters;

    public function setUp()
    {
        $this->transport = new Sendmail();
        $self = $this;
        $this->transport->setCallable(function ($to, $subject, $message, $additional_headers, $additional_parameters = null) use ($self) {
            $self->to                    = $to;
            $self->subject               = $subject;
            $self->message               = $message;
            $self->additional_headers    = $additional_headers;
            $self->additional_parameters = $additional_parameters;
        });
        $this->operating_system      = strtoupper(substr(PHP_OS, 0, 3));
    }

    public function tearDown()
    {
        $this->to                    = null;
        $this->subject               = null;
        $this->message               = null;
        $this->additional_headers    = null;
        $this->additional_parameters = null;
    }

    public function getMessage()
    {
        $message = new Message();
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam')
                ->addCc('matthew@zend.com')
                ->addBcc('api-tools-crteam@lists.zend.com', 'CR-Team, Laminas Project')
                ->addFrom(array(
                    'api-tools-devteam@zend.com',
                    'matthew@zend.com' => 'Matthew',
                ))
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');
        $message->getHeaders()->addHeaders(array(
            'X-Foo-Bar' => 'Matthew',
        ));
        return $message;
    }

    public function testReceivesMailArtifactsOnUnixSystems()
    {
        if ($this->operating_system == 'WIN') {
            $this->markTestSkipped('This test is *nix-specific');
        }

        $message = $this->getMessage();
        $this->transport->setParameters('-R hdrs');

        $this->transport->send($message);
        $this->assertEquals('Laminas DevTeam <api-tools-devteam@zend.com>', $this->to);
        $this->assertEquals('Testing Laminas\Mail\Transport\Sendmail', $this->subject);
        $this->assertEquals('This is only a test.', trim($this->message));
        $this->assertNotContains("To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n", $this->additional_headers);
        $this->assertContains("Cc: matthew@zend.com\r\n", $this->additional_headers);
        $this->assertContains("Bcc: \"CR-Team, Laminas Project\" <api-tools-crteam@lists.zend.com>\r\n", $this->additional_headers);
        $this->assertContains("From: api-tools-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n", $this->additional_headers);
        $this->assertContains("X-Foo-Bar: Matthew\r\n", $this->additional_headers);
        $this->assertContains("Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n", $this->additional_headers);
        $this->assertEquals('-R hdrs -f ralph.schindler@zend.com', $this->additional_parameters);
    }

    public function testReceivesMailArtifactsOnWindowsSystems()
    {
        if ($this->operating_system != 'WIN') {
            $this->markTestSkipped('This test is Windows-specific');
        }

        $message = $this->getMessage();

        $this->transport->send($message);
        $this->assertEquals('api-tools-devteam@zend.com', $this->to);
        $this->assertEquals('Testing Laminas\Mail\Transport\Sendmail', $this->subject);
        $this->assertEquals('This is only a test.', trim($this->message));
        $this->assertContains("To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n", $this->additional_headers);
        $this->assertContains("Cc: matthew@zend.com\r\n", $this->additional_headers);
        $this->assertContains("Bcc: \"CR-Team, Laminas Project\" <api-tools-crteam@lists.zend.com>\r\n", $this->additional_headers);
        $this->assertContains("From: api-tools-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n", $this->additional_headers);
        $this->assertContains("X-Foo-Bar: Matthew\r\n", $this->additional_headers);
        $this->assertContains("Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n", $this->additional_headers);
        $this->assertNull($this->additional_parameters);
    }

    public function testLinesStartingWithFullStopsArePreparedProperlyForWindows()
    {
        if ($this->operating_system != 'WIN') {
            $this->markTestSkipped('This test is Windows-specific');
        }

        $message = $this->getMessage();
        $message->setBody("This is the first line.\n. This is the second");
        $this->transport->send($message);
        $this->assertContains("line.\n.. This", trim($this->message));
    }

    public function testAssertSubjectEncoded()
    {
        $message = $this->getMessage();
        $message->setEncoding('UTF-8');
        $this->transport->send($message);
        $this->assertEquals('=?UTF-8?Q?Testing=20Laminas\Mail\Transport\Sendmail?=', $this->subject);
    }
}

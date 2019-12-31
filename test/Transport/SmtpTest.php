<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Headers;
use Laminas\Mail\Message;
use Laminas\Mail\Protocol\Smtp as SmtpProtocol;
use Laminas\Mail\Protocol\SmtpPluginManager;
use Laminas\Mail\Transport\Envelope;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\SmtpOptions;
use LaminasTest\Mail\TestAsset\SmtpProtocolSpy;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Transport\Smtp<extended>
 */
class SmtpTest extends TestCase
{
    /** @var Smtp */
    public $transport;
    /** @var SmtpProtocolSpy */
    public $connection;

    public function setUp()
    {
        $this->transport  = new Smtp();
        $this->connection = new SmtpProtocolSpy();
        $this->transport->setConnection($this->connection);
    }

    public function getMessage()
    {
        $message = new Message();
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $message->addCc('matthew@zend.com');
        $message->addBcc('api-tools-crteam@lists.zend.com', 'CR-Team, Laminas Project');
        $message->addFrom([
            'api-tools-devteam@zend.com',
            'matthew@zend.com' => 'Matthew',
        ]);
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setSubject('Testing Laminas\Mail\Transport\Sendmail');
        $message->setBody('This is only a test.');

        $message->getHeaders()->addHeaders([
            'X-Foo-Bar' => 'Matthew',
        ]);

        return $message;
    }

    /**
     *  Per RFC 2822 3.6
     */
    public function testSendMailWithoutMinimalHeaders()
    {
        $this->expectException('Laminas\Mail\Transport\Exception\RuntimeException');
        $this->expectExceptionMessage(
            'transport expects either a Sender or at least one From address in the Message; none provided'
        );
        $message = new Message();
        $this->transport->send($message);
    }

    /**
     *  Per RFC 2821 3.3 (page 18)
     *  - RCPT (recipient) must be called before DATA (headers or body)
     */
    public function testSendMailWithoutRecipient()
    {
        $this->expectException('Laminas\Mail\Transport\Exception\RuntimeException');
        $this->expectExceptionMessage('at least one recipient if the message has at least one header or body');
        $message = new Message();
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $this->transport->send($message);
    }

    public function testSendMailWithEnvelopeFrom()
    {
        $message = $this->getMessage();
        $envelope = new Envelope([
            'from' => 'mailer@lists.zend.com',
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<mailer@lists.zend.com>', $data);
        $this->assertContains('RCPT TO:<matthew@zend.com>', $data);
        $this->assertContains('RCPT TO:<api-tools-crteam@lists.zend.com>', $data);
        $this->assertContains("From: api-tools-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n", $data);
    }

    public function testSendMailWithEnvelopeTo()
    {
        $message = $this->getMessage();
        $envelope = new Envelope([
            'to' => 'users@lists.zend.com',
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<ralph.schindler@zend.com>', $data);
        $this->assertContains('RCPT TO:<users@lists.zend.com>', $data);
        $this->assertContains('To: Laminas DevTeam <api-tools-devteam@zend.com>', $data);
    }

    public function testSendMailWithEnvelope()
    {
        $message = $this->getMessage();
        $to = ['users@lists.zend.com', 'dev@lists.zend.com'];
        $envelope = new Envelope([
            'from' => 'mailer@lists.zend.com',
            'to' => $to,
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $this->assertEquals($to, $this->connection->getRecipients());

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<mailer@lists.zend.com>', $data);
        $this->assertContains('RCPT TO:<users@lists.zend.com>', $data);
        $this->assertContains('RCPT TO:<dev@lists.zend.com>', $data);
    }

    public function testSendMinimalMail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setBody('testSendMailWithoutMinimalHeaders');
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam');

        $expectedMessage = "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n"
            . "\r\n"
            . "testSendMailWithoutMinimalHeaders";

        $this->transport->send($message);

        $this->assertContains($expectedMessage, $this->connection->getLog());
    }

    public function testSendMinimalMailWithoutSender()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setFrom('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setBody('testSendMinimalMailWithoutSender');
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam');

        $expectedMessage = "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "From: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n"
            . "\r\n"
            . "testSendMinimalMailWithoutSender";

        $this->transport->send($message);

        $this->assertContains($expectedMessage, $this->connection->getLog());
    }

    public function testReceivesMailArtifacts()
    {
        $message = $this->getMessage();
        $this->transport->send($message);

        $expectedRecipients = ['api-tools-devteam@zend.com', 'matthew@zend.com', 'api-tools-crteam@lists.zend.com'];
        $this->assertEquals($expectedRecipients, $this->connection->getRecipients());

        $data = $this->connection->getLog();
        $this->assertContains('MAIL FROM:<ralph.schindler@zend.com>', $data);
        $this->assertContains('To: Laminas DevTeam <api-tools-devteam@zend.com>', $data);
        $this->assertContains('Subject: Testing Laminas\Mail\Transport\Sendmail', $data);
        $this->assertContains("Cc: matthew@zend.com\r\n", $data);
        $this->assertNotContains("Bcc: \"CR-Team, Laminas Project\" <api-tools-crteam@lists.zend.com>\r\n", $data);
        $this->assertContains("From: api-tools-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n", $data);
        $this->assertContains("X-Foo-Bar: Matthew\r\n", $data);
        $this->assertContains("Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n", $data);
        $this->assertContains("\r\n\r\nThis is only a test.", $data, $data);
    }

    public function testCanUseAuthenticationExtensionsViaPluginManager()
    {
        $options    = new SmtpOptions([
            'connection_class' => 'login',
        ]);
        $transport  = new Smtp($options);
        $connection = $transport->plugin($options->getConnectionClass(), [
            'username' => 'matthew',
            'password' => 'password',
            'host'     => 'localhost',
        ]);
        $this->assertInstanceOf('Laminas\Mail\Protocol\Smtp\Auth\Login', $connection);
        $this->assertEquals('matthew', $connection->getUsername());
        $this->assertEquals('password', $connection->getPassword());
    }

    public function testSetAutoDisconnect()
    {
        $this->transport->setAutoDisconnect(false);
        $this->assertFalse($this->transport->getAutoDisconnect());
    }

    public function testGetDefaultAutoDisconnectValue()
    {
        $this->assertTrue($this->transport->getAutoDisconnect());
    }

    public function testAutoDisconnectTrue()
    {
        $this->connection->connect();
        unset($this->transport);
        $this->assertFalse($this->connection->hasSession());
    }

    public function testAutoDisconnectFalse()
    {
        $this->connection->connect();
        $this->transport->setAutoDisconnect(false);
        unset($this->transport);
        $this->assertTrue($this->connection->isConnected());
    }

    public function testDisconnect()
    {
        $this->connection->connect();
        $this->assertTrue($this->connection->isConnected());
        $this->transport->disconnect();
        $this->assertFalse($this->connection->isConnected());
    }

    public function testDisconnectSendReconnects()
    {
        $this->assertFalse($this->connection->hasSession());
        $this->transport->send($this->getMessage());
        $this->assertTrue($this->connection->hasSession());
        $this->connection->disconnect();

        $this->assertFalse($this->connection->hasSession());
        $this->transport->send($this->getMessage());
        $this->assertTrue($this->connection->hasSession());
    }

    public function testAutoReconnect()
    {
        $options = new SmtpOptions();
        $options->setConnectionTimeLimit(5 * 3600);

        $this->transport->setOptions($options);

        // Mock the connection
        $connectionMock = $this->getMockBuilder(SmtpProtocol::class)
            ->disableOriginalConstructor()
            ->setMethods(['connect', 'helo', 'hasSession', 'mail', 'rcpt', 'data', 'rset'])
            ->getMock();

        $connectionMock
            ->expects(self::exactly(2))
            ->method('hasSession')
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );

        $connectionMock
            ->expects(self::exactly(2))
            ->method('connect');

        $connectionMock
            ->expects(self::exactly(2))
            ->method('helo');

        $connectionMock
            ->expects(self::exactly(3))
            ->method('mail');

        $connectionMock
            ->expects(self::exactly(9))
            ->method('rcpt');

        $connectionMock
            ->expects(self::exactly(3))
            ->method('data');

        $connectionMock
            ->expects(self::exactly(1))
            ->method('rset');

        $this->transport->setConnection($connectionMock);

        // Mock the plugin manager so that lazyLoadConnection() works
        $pluginManagerMock = $this->getMockBuilder(SmtpPluginManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $pluginManagerMock
            ->expects(self::once())
            ->method('get')
            ->willReturn($connectionMock);

        $this->transport->setPluginManager($pluginManagerMock);


        // Send the first email - first connect()
        $this->transport->send($this->getMessage());

        // Check that the connectedTime was set properly
        $reflClass             = new \ReflectionClass($this->transport);
        $connectedTimeProperty = $reflClass->getProperty('connectedTime');

        self::assertNotNull($connectedTimeProperty);
        $connectedTimeProperty->setAccessible(true);
        $connectedTimeAfterFirstMail = $connectedTimeProperty->getValue($this->transport);
        $this->assertNotNull($connectedTimeAfterFirstMail);


        // Send the second email - no new connect()
        $this->transport->send($this->getMessage());

        // Make sure that there was no new connect() (and no new timestamp was written)
        $this->assertEquals($connectedTimeAfterFirstMail, $connectedTimeProperty->getValue($this->transport));


        // Manipulate the timestamp to trigger the auto-reconnect
        $connectedTimeProperty->setValue($this->transport, time() - 10 * 3600);

        // Send the third email - it should trigger a new connect()
        $this->transport->send($this->getMessage());
    }
}

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
use Laminas\Mail\Protocol\Smtp\Auth\Login;
use Laminas\Mail\Protocol\SmtpPluginManager;
use Laminas\Mail\Transport\Envelope;
use Laminas\Mail\Transport\Exception;
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

    public function setUp(): void
    {
        $this->transport  = new Smtp();
        $this->connection = new SmtpProtocolSpy();
        $this->transport->setConnection($this->connection);
    }

    public function getMessage(): Message
    {
        $message = new Message();
        $message->addTo('test@example.com', 'Example Test');
        $message->addCc('matthew@example.com');
        $message->addBcc('list@example.com', 'Example List');
        $message->addFrom([
            'test@example.com',
            'matthew@example.com' => 'Matthew',
        ]);
        $message->setSender('ralph@example.com', 'Ralph Schindler');
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
    public function testSendMailWithoutMinimalHeaders(): void
    {
        $this->expectException(Exception\RuntimeException::class);
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
    public function testSendMailWithoutRecipient(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('at least one recipient if the message has at least one header or body');
        $message = new Message();
        $message->setSender('ralph@example.com', 'Ralph Schindler');
        $this->transport->send($message);
    }

    public function testSendMailWithEnvelopeFrom(): void
    {
        $message = $this->getMessage();
        $envelope = new Envelope([
            'from' => 'mailer@example.com',
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $data = $this->connection->getLog();
        $this->assertStringContainsString('MAIL FROM:<mailer@example.com>', $data);
        $this->assertStringContainsString('RCPT TO:<matthew@example.com>', $data);
        $this->assertStringContainsString('RCPT TO:<list@example.com>', $data);
        $this->assertStringContainsString("From: test@example.com,\r\n Matthew <matthew@example.com>\r\n", $data);
    }

    public function testSendMailWithEnvelopeTo(): void
    {
        $message = $this->getMessage();
        $envelope = new Envelope([
            'to' => 'users@example.com',
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $data = $this->connection->getLog();
        $this->assertStringContainsString('MAIL FROM:<ralph@example.com>', $data);
        $this->assertStringContainsString('RCPT TO:<users@example.com>', $data);
        $this->assertStringContainsString('To: Example Test <test@example.com>', $data);
    }

    public function testSendMailWithEnvelope(): void
    {
        $message = $this->getMessage();
        $to = ['users@example.com', 'dev@example.com'];
        $envelope = new Envelope([
            'from' => 'mailer@example.com',
            'to' => $to,
        ]);
        $this->transport->setEnvelope($envelope);
        $this->transport->send($message);

        $this->assertEquals($to, $this->connection->getRecipients());

        $data = $this->connection->getLog();
        $this->assertStringContainsString('MAIL FROM:<mailer@example.com>', $data);
        $this->assertStringContainsString('RCPT TO:<users@example.com>', $data);
        $this->assertStringContainsString('RCPT TO:<dev@example.com>', $data);
    }

    public function testSendMinimalMail(): void
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('ralph@example.com', 'Ralph Schindler');
        $message->setBody('testSendMailWithoutMinimalHeaders');
        $message->addTo('test@example.com', 'Example Test');

        $expectedMessage = "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph@example.com>\r\n"
            . "To: Example Test <test@example.com>\r\n"
            . "\r\n"
            . "testSendMailWithoutMinimalHeaders";

        $this->transport->send($message);

        $this->assertStringContainsString($expectedMessage, $this->connection->getLog());
    }

    public function testSendMinimalMailWithoutSender(): void
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setFrom('ralph@example.com', 'Ralph Schindler');
        $message->setBody('testSendMinimalMailWithoutSender');
        $message->addTo('test@example.com', 'Example Test');

        $expectedMessage = "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "From: Ralph Schindler <ralph@example.com>\r\n"
            . "To: Example Test <test@example.com>\r\n"
            . "\r\n"
            . "testSendMinimalMailWithoutSender";

        $this->transport->send($message);

        $this->assertStringContainsString($expectedMessage, $this->connection->getLog());
    }

    public function testReceivesMailArtifacts(): void
    {
        $message = $this->getMessage();
        $this->transport->send($message);

        $expectedRecipients = ['test@example.com', 'matthew@example.com', 'list@example.com'];
        $this->assertEquals($expectedRecipients, $this->connection->getRecipients());

        $data = $this->connection->getLog();
        $this->assertStringContainsString('MAIL FROM:<ralph@example.com>', $data);
        $this->assertStringContainsString('To: Example Test <test@example.com>', $data);
        $this->assertStringContainsString('Subject: Testing Laminas\Mail\Transport\Sendmail', $data);
        $this->assertStringContainsString("Cc: matthew@example.com\r\n", $data);
        $this->assertStringNotContainsString("Bcc: \"Example List\" <list@example.com>\r\n", $data);
        $this->assertStringContainsString("From: test@example.com,\r\n Matthew <matthew@example.com>\r\n", $data);
        $this->assertStringContainsString("X-Foo-Bar: Matthew\r\n", $data);
        $this->assertStringContainsString("Sender: Ralph Schindler <ralph@example.com>\r\n", $data);
        $this->assertStringContainsString("\r\n\r\nThis is only a test.", $data, $data);
    }

    /**
     * Fold long lines during smtp communication in Protocol\Smtp class.
     * Test folding of long lines following RFC 5322 section-2.2.3
     *
     * @see https://github.com/laminas/laminas-mail/pull/140
     */
    public function testLongLinesFoldingRFC5322(): void
    {
        $message = 'The folding logic expects exactly 1 byte after \r\n in folding';
        $this->assertEquals("\r\n ", Headers::FOLDING, $message);

        $message = $this->getMessage();
        // Create buffer of 8192 bytes (PHP_SOCK_CHUNK_SIZE)
        $buffer = str_repeat('0123456789abcdef', 512);

        $maxLen = SmtpProtocol::SMTP_LINE_LIMIT;
        $headerWithLargeValue = $buffer;
        $headerWithExactlyMaxLineLength = substr($buffer, 0, $maxLen - strlen('X-Exact-Length: '));
        $message->getHeaders()->addHeaders([
            'X-Ms-Exchange-Antispam-Messagedata' => $headerWithLargeValue,
            'X-Exact-Length' => $headerWithExactlyMaxLineLength,
        ]);

        $this->transport->send($message);
        $data = $this->connection->getLog();

        $lines = explode("\r\n", $data);
        $this->assertCount(28, $lines);

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual($maxLen, strlen($line), sprintf('Line is too long: ' . $line));
        }

        $this->assertStringNotContainsString(
            $headerWithLargeValue,
            $data,
            "The original header can't be present if it's wrapped"
        );
        $this->assertStringContainsString(
            $headerWithExactlyMaxLineLength,
            $data,
            "Header with exact length is not wrapped"
        );
    }

    public function testCanUseAuthenticationExtensionsViaPluginManager(): void
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
        $this->assertInstanceOf(Login::class, $connection);
        $this->assertEquals('matthew', $connection->getUsername());
        $this->assertEquals('password', $connection->getPassword());
    }

    public function testSetAutoDisconnect(): void
    {
        $this->transport->setAutoDisconnect(false);
        $this->assertFalse($this->transport->getAutoDisconnect());
    }

    public function testGetDefaultAutoDisconnectValue(): void
    {
        $this->assertTrue($this->transport->getAutoDisconnect());
    }

    public function testAutoDisconnectTrue(): void
    {
        $this->connection->connect();
        unset($this->transport);
        $this->assertFalse($this->connection->hasSession());
    }

    public function testAutoDisconnectFalse(): void
    {
        $this->connection->connect();
        $this->transport->setAutoDisconnect(false);
        unset($this->transport);
        $this->assertTrue($this->connection->isConnected());
    }

    public function testDisconnect(): void
    {
        $this->connection->connect();
        $this->assertTrue($this->connection->isConnected());
        $this->transport->disconnect();
        $this->assertFalse($this->connection->isConnected());
    }

    public function testDisconnectSendReconnects(): void
    {
        $this->assertFalse($this->connection->hasSession());
        $this->transport->send($this->getMessage());
        $this->assertTrue($this->connection->hasSession());
        $this->connection->disconnect();

        $this->assertFalse($this->connection->hasSession());
        $this->transport->send($this->getMessage());
        $this->assertTrue($this->connection->hasSession());
    }

    public function testAutoReconnect(): void
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

        $this->assertNotNull($connectedTimeProperty);
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

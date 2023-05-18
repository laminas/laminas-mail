<?php

namespace LaminasTest\Mail\Protocol;

use Laminas\Mail\Headers;
use Laminas\Mail\Message;
use Laminas\Mail\Protocol\Exception;
use Laminas\Mail\Transport\Smtp;
use LaminasTest\Mail\TestAsset\SmtpProtocolSpy;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Protocol\Smtp<extended>
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

    public function testSendMinimalMail(): void
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('sender@example.com', 'Example Sender');
        $message->setBody('testSendMailWithoutMinimalHeaders');
        $message->addTo('recipient@example.com', 'Recipient Name');

        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<sender@example.com>\r\n"
            . "RCPT TO:<recipient@example.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Example Sender <sender@example.com>\r\n"
            . "To: Recipient Name <recipient@example.com>\r\n"
            . "\r\n"
            . "testSendMailWithoutMinimalHeaders\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testSendEscapedEmail(): void
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('sender@example.com', 'Example Sender');
        $message->setBody("This is a test\n.");
        $message->addTo('recipient@example.com', 'Recipient Name');

        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<sender@example.com>\r\n"
            . "RCPT TO:<recipient@example.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Example Sender <sender@example.com>\r\n"
            . "To: Recipient Name <recipient@example.com>\r\n"
            . "\r\n"
            . "This is a test\r\n"
            . "..\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testDisconnectCallsQuit(): void
    {
        $this->connection->disconnect();
        $this->assertTrue($this->connection->calledQuit);
    }

    public function testDisconnectResetsAuthFlag(): void
    {
        $this->connection->connect();
        $this->connection->setSessionStatus(true);
        $this->connection->setAuth(true);
        $this->assertTrue($this->connection->getAuth());
        $this->connection->disconnect();
        $this->assertFalse($this->connection->getAuth());
    }

    public function testConnectHasVerboseErrors(): void
    {
        $smtp = new TestAsset\ErroneousSmtp();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessageMatches('/nonexistentremote/');

        $smtp->connect('nonexistentremote');
    }

    public function testCanAvoidQuitRequest(): void
    {
        $this->assertTrue($this->connection->useCompleteQuit(), 'Default behaviour must be BC');

        $this->connection->resetLog();
        $this->connection->connect();
        $this->connection->helo();
        $this->connection->disconnect();

        $this->assertStringContainsString('QUIT', $this->connection->getLog());

        $this->connection->setUseCompleteQuit(false);
        $this->assertFalse($this->connection->useCompleteQuit());

        $this->connection->resetLog();
        $this->connection->connect();
        $this->connection->helo();
        $this->connection->disconnect();

        $this->assertStringNotContainsString('QUIT', $this->connection->getLog());

        $connection = new SmtpProtocolSpy([
            'use_complete_quit' => false,
        ]);
        $this->assertFalse($connection->useCompleteQuit());
    }

    public function testAuthThrowsWhenAlreadyAuthed(): void
    {
        $this->connection->setAuth(true);
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Already authenticated for this session');
        $this->connection->auth();
    }

    public function testHeloThrowsWhenAlreadySession(): void
    {
        $this->connection->helo('hostname.test');
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cannot issue HELO to existing session');
        $this->connection->helo('hostname.test');
    }

    public function testHeloThrowsWithInvalidHostname(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('The input does not match the expected structure for a DNS hostname');
        $this->connection->helo("invalid\r\nhost name");
    }

    public function testMailThrowsWhenNoSession(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('A valid session has not been started');
        $this->connection->mail('test@example.com');
    }

    public function testRcptThrowsWhenNoMail(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('No sender reverse path has been supplied');
        $this->connection->rcpt('test@example.com');
    }

    public function testDataThrowsWhenNoRcpt(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('No recipient forward path has been supplied');
        $this->connection->data('message');
    }

    public function testRcptThrowsWithCodeWhenErroneousRecipient(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            SmtpProtocolSpy::ERRONEOUS_RECIPIENT_ENHANCED_CODE . ' ' . SmtpProtocolSpy::ERRONEOUS_RECIPIENT_MESSAGE
        );
        $this->expectExceptionCode(SmtpProtocolSpy::ERRONEOUS_RECIPIENT_CODE);

        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('sender@example.com', 'Example Sender');
        $message->setBody("This is a test\n.");
        $message->addTo(SmtpProtocolSpy::ERRONEOUS_RECIPIENT, 'Erroneous Recipient Name');

        $this->transport->send($message);
    }
}

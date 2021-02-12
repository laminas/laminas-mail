<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Address\AddressInterface;
use Laminas\Mail\AddressList;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Exception\RuntimeException;
use Laminas\Mail\Transport\Sendmail;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @covers Laminas\Mail\Transport\Sendmail<extended>
 */
class SendmailTest extends TestCase
{
    public $transport;
    public $to;
    public $subject;
    public $message;
    public $additional_headers;
    public $additional_parameters;
    public $operating_system;

    public function setUp(): void
    {
        $this->transport = new Sendmail();
        $this->transport->setCallable(
            function ($to, $subject, $message, $additional_headers, $additional_parameters = null): void {
                $this->to                    = $to;
                $this->subject               = $subject;
                $this->message               = $message;
                $this->additional_headers    = $additional_headers;
                $this->additional_parameters = $additional_parameters;
            }
        );
        $this->operating_system      = strtoupper(substr(PHP_OS, 0, 3));
    }

    public function tearDown(): void
    {
        $this->to                    = null;
        $this->subject               = null;
        $this->message               = null;
        $this->additional_headers    = null;
        $this->additional_parameters = null;
    }

    public function getMessage(): Message
    {
        $message = new Message();
        $message->addTo('test@example.com', 'Example Test')
                ->addCc('matthew@example.com')
                ->addBcc('list@example.com', 'Example, List')
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

    private function isWindows(): bool
    {
        return $this->operating_system === 'WIN';
    }

    public function testReceivesMailArtifactsOnUnixSystems(): void
    {
        if ($this->isWindows()) {
            $this->markTestSkipped('This test is *nix-specific');
        }

        $message = $this->getMessage();
        $this->transport->setParameters('-R hdrs');

        $this->transport->send($message);
        $this->assertEquals('Example Test <test@example.com>', $this->to);
        $this->assertEquals('Testing Laminas\Mail\Transport\Sendmail', $this->subject);
        $this->assertEquals('This is only a test.', trim($this->message));
        $this->assertStringNotContainsString("To: Example Test <test@example.com>\n", $this->additional_headers);
        $this->assertStringContainsString("Cc: matthew@example.com\n", $this->additional_headers);
        $this->assertStringContainsString("Bcc: \"Example, List\" <list@example.com>\n", $this->additional_headers);
        $this->assertStringContainsString(
            "From: test@example.com,\n Matthew <matthew@example.com>\n",
            $this->additional_headers
        );
        $this->assertStringContainsString("X-Foo-Bar: Matthew\n", $this->additional_headers);
        $this->assertStringContainsString("Sender: Ralph Schindler <ralph@example.com>\n", $this->additional_headers);
        $this->assertEquals('-R hdrs -f\'ralph@example.com\'', $this->additional_parameters);
    }

    public function testReceivesMailArtifactsOnWindowsSystems(): void
    {
        if (! $this->isWindows()) {
            $this->markTestSkipped('This test is Windows-specific');
        }

        $message = $this->getMessage();

        $this->transport->send($message);
        $this->assertEquals('test@example.com', $this->to);
        $this->assertEquals('Testing Laminas\Mail\Transport\Sendmail', $this->subject);
        $this->assertEquals('This is only a test.', trim($this->message));
        $this->assertStringContainsString("To: Example Test <test@example.com>\r\n", $this->additional_headers);
        $this->assertStringContainsString("Cc: matthew@example.com\r\n", $this->additional_headers);
        $this->assertStringContainsString("Bcc: \"Example, List\" <list@example.com>\r\n", $this->additional_headers);
        $this->assertStringContainsString(
            "From: test@example.com,\r\n Matthew <matthew@example.com>\r\n",
            $this->additional_headers
        );
        $this->assertStringContainsString("X-Foo-Bar: Matthew\r\n", $this->additional_headers);
        $this->assertStringContainsString("Sender: Ralph Schindler <ralph@example.com>\r\n", $this->additional_headers);
        $this->assertNull($this->additional_parameters);
    }

    public function testLinesStartingWithFullStopsArePreparedProperlyForWindows(): void
    {
        if (! $this->isWindows()) {
            $this->markTestSkipped('This test is Windows-specific');
        }

        $message = $this->getMessage();
        $message->setBody("This is the first line.\n. This is the second");
        $this->transport->send($message);
        $this->assertStringContainsString("line.\n.. This", trim($this->message));
    }

    public function testAssertSubjectEncoded(): void
    {
        $message = $this->getMessage();
        $message->setEncoding('UTF-8');
        $this->transport->send($message);
        $this->assertEquals('=?UTF-8?Q?Testing=20Laminas\Mail\Transport\Sendmail?=', $this->subject);
    }

    public function testCodeInjectionInFromHeader(): void
    {
        $this->expectException(RuntimeException::class);
        $message = $this->getMessage();
        $message->setBody('This is the text of the email.');
        $message->setFrom('"AAA\" code injection"@domain', 'Sender\'s name');
        $message->addTo('hacker@localhost', 'Name of recipient');
        $message->setSubject('TestSubject');

        $this->transport->send($message);
    }

    public function testValidEmailLocaDomainInFromHeader(): void
    {
        $message = $this->getMessage();
        $message->setBody('This is the text of the email.');
        $message->setFrom('"foo-bar"@domain', 'Foo Bar');
        $message->addTo('hacker@localhost', 'Name of recipient');
        $message->setSubject('TestSubject');

        $this->transport->send($message);
        $this->assertStringContainsString('From: Foo Bar <"foo-bar"@domain>', $this->additional_headers);
    }

    /**
     * @ref CVE-2016-10033 which targeted WordPress
     */
    public function testPrepareParametersEscapesSenderUsingEscapeShellArg(): void
    {
        // @codingStandardsIgnoreStart
        $injectedEmail = 'user@xenial(tmp1 -be ${run{${substr{0}{1}{$spool_directory}}usr${substr{0}{1}{$spool_directory}}bin${substr{0}{1}{$spool_directory}}touch${substr{10}{1}{$tod_log}}${substr{0}{1}{$spool_directory}}tmp${substr{0}{1}{$spool_directory}}test}}  tmp2)';
        // @codingStandardsIgnoreEnd

        $sender = $this->createMock(AddressInterface::class);
        $sender->method('getEmail')->willReturn($injectedEmail);

        $message = $this->createMock(Message::class);
        $message->method('getSender')->willReturn($sender);
        $message->expects($this->never())->method('getFrom');

        $r = new ReflectionMethod($this->transport, 'prepareParameters');
        $r->setAccessible(true);

        $parameters = $r->invoke($this->transport, $message);
        $this->assertEquals(' -f' . escapeshellarg($injectedEmail), $parameters);
    }

    /**
     * @ref CVE-2016-10033 which targeted WordPress
     */
    public function testPrepareParametersEscapesFromAddressUsingEscapeShellArg(): void
    {
        // @codingStandardsIgnoreStart
        $injectedEmail = 'user@xenial(tmp1 -be ${run{${substr{0}{1}{$spool_directory}}usr${substr{0}{1}{$spool_directory}}bin${substr{0}{1}{$spool_directory}}touch${substr{10}{1}{$tod_log}}${substr{0}{1}{$spool_directory}}tmp${substr{0}{1}{$spool_directory}}test}}  tmp2)';
        // @codingStandardsIgnoreEnd

        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->exactly(2))->method('getEmail')->willReturn($injectedEmail);

        $from = new AddressList();
        $from->add($address);

        $message = $this->createMock(Message::class);
        $message->method('getSender')->willReturn(null);
        $message->method('getFrom')->willReturn($from);

        $r = new ReflectionMethod($this->transport, 'prepareParameters');
        $r->setAccessible(true);

        $parameters = $r->invoke($this->transport, $message);
        $this->assertEquals(' -f' . escapeshellarg($injectedEmail), $parameters);
    }

    public function testTrimmedParameters(): void
    {
        $this->transport->setParameters([' -R', 'hdrs ']);

        $r = new ReflectionProperty($this->transport, 'parameters');
        $r->setAccessible(true);

        $this->assertSame('-R hdrs', $r->getValue($this->transport));
    }

    public function testAllowMessageWithEmptyToHeaderButHasCcHeader(): void
    {
        $message = new Message();
        $message->addCc('matthew@example.com')
                ->setSender('ralph@example.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');

        $this->transport->send($message);
        $this->assertStringContainsString('Sender: Ralph Schindler <ralph@example.com>', $this->additional_headers);
    }

    public function testAllowMessageWithEmptyToHeaderButHasBccHeader(): void
    {
        $message = new Message();
        $message->addBcc('list@example.com', 'Example, List')
                ->setSender('ralph@example.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');

        $this->transport->send($message);
        $this->assertStringContainsString('Sender: Ralph Schindler <ralph@example.com>', $this->additional_headers);
    }

    public function testDoNotAllowMessageWithoutToAndCcAndBccHeaders(): void
    {
        $message = new Message();
        $message->setSender('ralph@example.com', 'Ralph Schindler')
                ->setSubject('Testing Laminas\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');

        $this->expectException(RuntimeException::class);
        $this->transport->send($message);
    }

    /**
     * @see https://github.com/laminas/laminas-mail/issues/19
     */
    public function testHeadersToAndSubjectAreNotDuplicated(): void
    {
        $message = new Message();
        $message
            ->addTo('matthew@example.org')
            ->addFrom('ralph@example.org')
            ->setSubject('Greetings and Salutations!')
            ->setBody("Sorry, I'm going to be late today!");

        $this->transport->send($message);

        $this->assertEquals('matthew@example.org', $this->to);
        $this->assertEquals('Greetings and Salutations!', $this->subject);

        $this->assertDoesNotMatchRegularExpression('/^To: matthew\@example\.org$/m', $this->additional_headers);
        $this->assertDoesNotMatchRegularExpression(
            '/^Subject: Greetings and Salutations!$/m',
            $this->additional_headers
        );
    }

    public function additionalParametersContainingFromSwitch(): iterable
    {
        yield 'leading'     => ['-f\'foo@example.com\''];
        yield 'not-leading' => ['-bs -f\'foo@example.com\''];
    }

    /**
     * @dataProvider additionalParametersContainingFromSwitch
     */
    public function testDoesNotInjectFromParameterFromSenderWhenFromOptionPresentInParameters(string $parameters): void
    {
        if ($this->operating_system == 'WIN') {
            $this->markTestSkipped('This test is *nix-specific');
        }

        $message = $this->getMessage();
        $this->transport->setParameters($parameters);

        $this->transport->send($message);
        $this->assertEquals($parameters, $this->additional_parameters);
    }
}

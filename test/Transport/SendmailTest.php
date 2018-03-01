<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Transport;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Zend\Mail\Address\AddressInterface;
use Zend\Mail\AddressList;
use Zend\Mail\Header;
use Zend\Mail\Headers;
use Zend\Mail\Message;
use Zend\Mail\Transport\Exception\RuntimeException;
use Zend\Mail\Transport\Sendmail;

/**
 * @covers Zend\Mail\Transport\Sendmail<extended>
 */
class SendmailTest extends TestCase
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
        $this->transport->setCallable(
            function ($to, $subject, $message, $additional_headers, $additional_parameters = null) {
                $this->to                    = $to;
                $this->subject               = $subject;
                $this->message               = $message;
                $this->additional_headers    = $additional_headers;
                $this->additional_parameters = $additional_parameters;
            }
        );
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
        $message->addTo('zf-devteam@zend.com', 'ZF DevTeam')
                ->addCc('matthew@zend.com')
                ->addBcc('zf-crteam@lists.zend.com', 'CR-Team, ZF Project')
                ->addFrom([
                    'zf-devteam@zend.com',
                    'matthew@zend.com' => 'Matthew',
                ])
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Zend\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');
        $message->getHeaders()->addHeaders([
            'X-Foo-Bar' => 'Matthew',
        ]);
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
        $this->assertEquals('ZF DevTeam <zf-devteam@zend.com>', $this->to);
        $this->assertEquals('Testing Zend\Mail\Transport\Sendmail', $this->subject);
        $this->assertEquals('This is only a test.', trim($this->message));
        $this->assertNotContains("To: ZF DevTeam <zf-devteam@zend.com>\n", $this->additional_headers);
        $this->assertContains("Cc: matthew@zend.com\n", $this->additional_headers);
        $this->assertContains("Bcc: \"CR-Team, ZF Project\" <zf-crteam@lists.zend.com>\n", $this->additional_headers);
        $this->assertContains("From: zf-devteam@zend.com,\n Matthew <matthew@zend.com>\n", $this->additional_headers);
        $this->assertContains("X-Foo-Bar: Matthew\n", $this->additional_headers);
        $this->assertContains("Sender: Ralph Schindler <ralph.schindler@zend.com>\n", $this->additional_headers);
        $this->assertEquals('-R hdrs -f\'ralph.schindler@zend.com\'', $this->additional_parameters);
    }

    public function testReceivesMailArtifactsOnWindowsSystems()
    {
        if ($this->operating_system != 'WIN') {
            $this->markTestSkipped('This test is Windows-specific');
        }

        $message = $this->getMessage();

        $this->transport->send($message);
        $this->assertEquals('zf-devteam@zend.com', $this->to);
        $this->assertEquals('Testing Zend\Mail\Transport\Sendmail', $this->subject);
        $this->assertEquals('This is only a test.', trim($this->message));
        $this->assertContains("To: ZF DevTeam <zf-devteam@zend.com>\r\n", $this->additional_headers);
        $this->assertContains("Cc: matthew@zend.com\r\n", $this->additional_headers);
        $this->assertContains("Bcc: \"CR-Team, ZF Project\" <zf-crteam@lists.zend.com>\r\n", $this->additional_headers);
        $this->assertContains(
            "From: zf-devteam@zend.com,\r\n Matthew <matthew@zend.com>\r\n",
            $this->additional_headers
        );
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
        $this->assertEquals('=?UTF-8?Q?Testing=20Zend\Mail\Transport\Sendmail?=', $this->subject);
    }

    public function testCodeInjectionInFromHeader()
    {
        $this->expectException(RuntimeException::class);
        $message = $this->getMessage();
        $message->setBody('This is the text of the email.');
        $message->setFrom('"AAA\" code injection"@domain', 'Sender\'s name');
        $message->addTo('hacker@localhost', 'Name of recipient');
        $message->setSubject('TestSubject');

        $this->transport->send($message);
    }

    public function testValidEmailLocaDomainInFromHeader()
    {
        $message = $this->getMessage();
        $message->setBody('This is the text of the email.');
        $message->setFrom('"foo-bar"@domain', 'Foo Bar');
        $message->addTo('hacker@localhost', 'Name of recipient');
        $message->setSubject('TestSubject');

        $this->transport->send($message);
        $this->assertContains('From: Foo Bar <"foo-bar"@domain>', $this->additional_headers);
    }

    /**
     * @ref CVE-2016-10033 which targeted WordPress
     */
    public function testPrepareParametersEscapesSenderUsingEscapeShellArg()
    {
        // @codingStandardsIgnoreStart
        $injectedEmail = 'user@xenial(tmp1 -be ${run{${substr{0}{1}{$spool_directory}}usr${substr{0}{1}{$spool_directory}}bin${substr{0}{1}{$spool_directory}}touch${substr{10}{1}{$tod_log}}${substr{0}{1}{$spool_directory}}tmp${substr{0}{1}{$spool_directory}}test}}  tmp2)';
        // @codingStandardsIgnoreEnd

        $sender = $this->prophesize(AddressInterface::class);
        $sender->getEmail()->willReturn($injectedEmail);

        $message = $this->prophesize(Message::class);
        $message->getSender()->will([$sender, 'reveal']);
        $message->getFrom()->shouldNotBeCalled();

        $r = new ReflectionMethod($this->transport, 'prepareParameters');
        $r->setAccessible(true);

        $parameters = $r->invoke($this->transport, $message->reveal());
        $this->assertEquals(' -f' . escapeshellarg($injectedEmail), $parameters);
    }

    /**
     * @ref CVE-2016-10033 which targeted WordPress
     */
    public function testPrepareParametersEscapesFromAddressUsingEscapeShellArg()
    {
        // @codingStandardsIgnoreStart
        $injectedEmail = 'user@xenial(tmp1 -be ${run{${substr{0}{1}{$spool_directory}}usr${substr{0}{1}{$spool_directory}}bin${substr{0}{1}{$spool_directory}}touch${substr{10}{1}{$tod_log}}${substr{0}{1}{$spool_directory}}tmp${substr{0}{1}{$spool_directory}}test}}  tmp2)';
        // @codingStandardsIgnoreEnd

        $address = $this->prophesize(AddressInterface::class);
        $address->getEmail()->willReturn($injectedEmail)->shouldBeCalledTimes(2);

        $from = new AddressList();
        $from->add($address->reveal());

        $message = $this->prophesize(Message::class);
        $message->getSender()->willReturn(null);
        $message->getFrom()->willReturn($from);

        $r = new ReflectionMethod($this->transport, 'prepareParameters');
        $r->setAccessible(true);

        $parameters = $r->invoke($this->transport, $message->reveal());
        $this->assertEquals(' -f' . escapeshellarg($injectedEmail), $parameters);
    }

    public function testTrimmedParameters()
    {
        $this->transport->setParameters([' -R', 'hdrs ']);

        $r = new ReflectionProperty($this->transport, 'parameters');
        $r->setAccessible(true);

        $this->assertSame('-R hdrs', $r->getValue($this->transport));
    }

    public function testAllowMessageWithEmptyToHeaderButHasCcHeader()
    {
        $message = new Message();
        $message->addCc('matthew@zend.com')
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Zend\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');

        $this->transport->send($message);
    }

    public function testAllowMessageWithEmptyToHeaderButHasBccHeader()
    {
        $message = new Message();
        $message->addBcc('zf-crteam@lists.zend.com', 'CR-Team, ZF Project')
                ->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Zend\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');

        $this->transport->send($message);
    }

    public function testDoNotAllowMessageWithoutToAndCcAndBccHeaders()
    {
        $message = new Message();
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler')
                ->setSubject('Testing Zend\Mail\Transport\Sendmail')
                ->setBody('This is only a test.');

        $this->expectException(RuntimeException::class);
        $this->transport->send($message);
    }
}

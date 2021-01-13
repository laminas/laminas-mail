<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;
use Laminas\Mail\Header\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\MessageId<extended>
 */
class MessageIdTest extends TestCase
{
    public function testSettingManually(): void
    {
        $id = "CALTvGe4_oYgf9WsYgauv7qXh2-6=KbPLExmJNG7fCs9B=1nOYg@mail.example.com";
        $messageid = new Header\MessageId();
        $messageid->setId($id);

        $expected = sprintf('<%s>', $id);
        $this->assertEquals($expected, $messageid->getFieldValue());
        $this->assertEquals($expected, $messageid->getId());
        $this->assertEquals("Message-ID: $expected", $messageid->toString());
    }

    public function testAutoGeneration(): void
    {
        $messageid = new Header\MessageId();
        $messageid->setId();

        $this->assertStringContainsString('@', $messageid->getFieldValue());
    }

    public function testAutoGenerationWithServerVars(): void
    {
        $serverBeforeTest = $_SERVER;
        $_SERVER['REMOTE_ADDR'] = '172.16.0.1';
        $_SERVER['SERVER_NAME'] = 'server-name.test';
        $messageid = new Header\MessageId();
        $messageid->setId();

        $this->assertStringContainsString('@server-name.test', $messageid->getFieldValue());
        $_SERVER = $serverBeforeTest;
    }

    public function headerLines(): array
    {
        return [
            'newline'      => ["Message-ID: foo\nbar"],
            'cr-lf'        => ["Message-ID: bar\r\nfoo"],
            'cr-lf-wsp'    => ["Message-ID: bar\r\n\r\n baz"],
            'multiline'    => ["Message-ID: baz\r\nbar\r\nbau"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringPreventsCrlfInjectionOnDetection($header): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $messageid = Header\MessageId::fromString($header);
    }

    public function invalidIdentifiers(): array
    {
        return [
            'newline'      => ["foo\nbar"],
            'cr-lf'        => ["bar\r\nfoo"],
            'cr-lf-wsp'    => ["bar\r\n\r\n baz"],
            'multiline'    => ["baz\r\nbar\r\nbau"],
            'folding'      => ["bar\r\n baz"],
        ];
    }

    /**
     * @dataProvider invalidIdentifiers
     * @group ZF2015-04
     */
    public function testInvalidIdentifierRaisesException($id): void
    {
        $header = new Header\MessageId();
        $this->expectException(Exception\InvalidArgumentException::class);
        $header->setId($id);
    }

    public function testFromStringRaisesExceptionOnInvalidHeader(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for Message-ID string');
        Header\MessageId::fromString('Foo: bar');
    }

    public function testDefaultEncoding(): void
    {
        $header = new Header\MessageId();
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncodingHasNoEffect(): void
    {
        $header = new Header\MessageId();
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }
}

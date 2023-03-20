<?php

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;
use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\HeaderInterface;
use Laminas\Mail\Header\Received;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\Received<extended>
 */
class ReceivedTest extends TestCase
{
    public function testFromStringCreatesValidReceivedHeader(): void
    {
        $receivedHeader = Header\Received::fromString('Received: xxx');
        $this->assertInstanceOf(HeaderInterface::class, $receivedHeader);
        $this->assertInstanceOf(Received::class, $receivedHeader);
    }

    public function testGetFieldNameReturnsHeaderName(): void
    {
        $receivedHeader = new Header\Received();
        $this->assertEquals('Received', $receivedHeader->getFieldName());
    }

    public function testReceivedGetFieldValueReturnsProperValue(): void
    {
        $this->markTestIncomplete('Received needs to be completed');

        $receivedHeader = new Header\Received();
        $this->assertEquals('xxx', $receivedHeader->getFieldValue());
    }

    public function testReceivedToStringReturnsHeaderFormattedString(): void
    {
        $this->markTestIncomplete('Received needs to be completed');

        $receivedHeader = new Header\Received();

        // @todo set some values, then test output
        $this->assertEmpty('Received: xxx', $receivedHeader->toString());
    }

    /** Implementation specific tests here */
    public static function headerLines(): array
    {
        return [
            'newline'    => ["Received: xx\nx"],
            'cr-lf'      => ["Received: xxx\r\n"],
            'cr-lf-fold' => ["Received: xxx\r\n\r\n zzz"],
            'cr-lf-x2'   => ["Received: xx\r\n\r\nx"],
            'multiline'  => ["Received: x\r\nx\r\nx"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testRaisesExceptionViaFromStringOnDetectionOfCrlfInjection(string $header): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $received = Header\Received::fromString($header);
    }

    public static function invalidValues(): array
    {
        return [
            'newline'   => ["xx\nx"],
            'cr-lf'     => ["xxx\r\n"],
            'cr-lf-wsp' => ["xx\r\n\r\nx"],
            'multiline' => ["x\r\nx\r\nx"],
        ];
    }

    /**
     * @dataProvider invalidValues
     * @group ZF2015-04
     */
    public function testConstructorRaisesExceptionOnValueWithCRLFInjectionAttempt(string $value): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Header\Received($value);
    }

    public function testFromStringRaisesExceptionOnInvalidHeader(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for Received string');
        Header\Received::fromString('Foo: bar');
    }

    public function testDefaultEncoding(): void
    {
        $header = Header\Received::fromString('Received: test');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncodingHasNoEffect(): void
    {
        $header = Header\Received::fromString('Received: test');
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testToString(): void
    {
        $header = new Header\Received('test');
        $this->assertEquals('Received: test', $header->toString());
    }

    public function testToStringMultipleHeaders(): void
    {
        $header = new Header\Received('test');
        $this->assertEquals('Received: test', $header->toStringMultipleHeaders([]));

        $header2 = new Header\Received('test2');
        $this->assertEquals(
            "Received: test\r\nReceived: test2",
            $header->toStringMultipleHeaders([$header2])
        );

        $header3 = new Header\Received('test3');
        $this->assertEquals(
            "Received: test\r\nReceived: test2\r\nReceived: test3",
            $header->toStringMultipleHeaders([$header2, $header3])
        );
    }

    public function testToStringMultipleHeadersThrows(): void
    {
        $header = new Header\Received('test');
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('can only accept an array of Received headers');
        $header->toStringMultipleHeaders([null]);
    }
}

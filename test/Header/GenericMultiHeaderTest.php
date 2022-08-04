<?php

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\GenericMultiHeader;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @covers Laminas\Mail\Header\GenericMultiHeader<extended>
 */
class GenericMultiHeaderTest extends TestCase
{
    public function testFromStringSingle(): void
    {
        $multiHeader = GenericMultiHeader::fromString('x-custom: test');
        $this->assertSame(GenericMultiHeader::class, $multiHeader::class);
    }

    public function testFromStringMultiple(): void
    {
        $headers = GenericMultiHeader::fromString('x-custom: foo,bar');
        $this->assertSame(2, count($headers));
        foreach ($headers as $header) {
            $this->assertSame(GenericMultiHeader::class, $header::class);
        }
    }

    public function testToStringSingle(): void
    {
        $multiHeader = new GenericMultiHeader('x-custom', 'test');

        $this->assertSame('X-Custom: test', $multiHeader->toStringMultipleHeaders([]));
    }

    public function testToStringMultiple(): void
    {
        $multiHeader   = new GenericMultiHeader('x-custom', 'test');
        $anotherHeader = new GenericMultiHeader('x-custom', 'two');

        $this->assertSame('X-Custom: test,two', $multiHeader->toStringMultipleHeaders([$anotherHeader]));
    }

    public function testToStringInvalid(): void
    {
        $multiHeader = new GenericMultiHeader('x-custom', 'test');

        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'This method toStringMultipleHeaders was expecting an array of headers of the same type'
        );
        $multiHeader->toStringMultipleHeaders([null]);
    }
}

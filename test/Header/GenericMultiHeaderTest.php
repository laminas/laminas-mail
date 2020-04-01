<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\GenericMultiHeader;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Header\GenericMultiHeader<extended>
 */
class GenericMultiHeaderTest extends TestCase
{
    public function testFromStringSingle()
    {
        $multiHeader = GenericMultiHeader::fromString('x-custom: test');
        $this->assertSame(GenericMultiHeader::class, \get_class($multiHeader));
    }

    public function testFromStringMultiple()
    {
        $headers = GenericMultiHeader::fromString('x-custom: foo,bar');
        $this->assertSame(2, \count($headers));
        foreach ($headers as $header) {
            $this->assertSame(GenericMultiHeader::class, \get_class($header));
        }
    }

    public function testToStringSingle()
    {
        $multiHeader = new GenericMultiHeader('x-custom', 'test');

        $this->assertSame('X-Custom: test', $multiHeader->toStringMultipleHeaders([]));
    }

    public function testToStringMultiple()
    {
        $multiHeader = new GenericMultiHeader('x-custom', 'test');
        $anotherHeader = new GenericMultiHeader('x-custom', 'two');

        $this->assertSame('X-Custom: test,two', $multiHeader->toStringMultipleHeaders([$anotherHeader]));
    }

    public function testToStringInvalid()
    {
        $multiHeader = new GenericMultiHeader('x-custom', 'test');

        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $this->expectExceptionMessage(
            'This method toStringMultipleHeaders was expecting an array of headers of the same type'
        );
        $multiHeader->toStringMultipleHeaders([null]);
    }
}

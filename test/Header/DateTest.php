<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Header\Date<extended>
 */
class DateTest extends TestCase
{
    public function headerLines()
    {
        return [
            'newline'      => ["Date: xxx yyy\n"],
            'cr-lf'        => ["Date: xxx yyy\r\n"],
            'cr-lf-wsp'    => ["Date: xxx yyy\r\n\r\n"],
            'multiline'    => ["Date: xxx\r\ny\r\nyy"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnCrlfInjectionAttempt($header)
    {
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        Header\Date::fromString($header);
    }

    /**
     * @group ZF2015-04
     */
    public function testPreventsCRLFInjectionViaConstructor()
    {
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $address = new Header\Date("This\ris\r\na\nCRLF Attack");
    }

    public function testFromStringRaisesExceptionOnInvalidHeader()
    {
        $this->expectException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        Header\Date::fromString('Foo: bar');
    }

    public function testDefaultEncoding()
    {
        $header = new Header\Date('today');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncodingHasNoEffect()
    {
        $header = new Header\Date('today');
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testToString()
    {
        $header = new Header\Date('today');
        $this->assertEquals('Date: today', $header->toString());
    }
}

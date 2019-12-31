<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\Received<extended>
 */
class ReceivedTest extends \PHPUnit_Framework_TestCase
{
    public function testFromStringCreatesValidReceivedHeader()
    {
        $receivedHeader = Header\Received::fromString('Received: xxx');
        $this->assertInstanceOf('Laminas\Mail\Header\HeaderInterface', $receivedHeader);
        $this->assertInstanceOf('Laminas\Mail\Header\Received', $receivedHeader);
    }

    public function testGetFieldNameReturnsHeaderName()
    {
        $receivedHeader = new Header\Received();
        $this->assertEquals('Received', $receivedHeader->getFieldName());
    }

    public function testReceivedGetFieldValueReturnsProperValue()
    {
        $this->markTestIncomplete('Received needs to be completed');

        $receivedHeader = new Header\Received();
        $this->assertEquals('xxx', $receivedHeader->getFieldValue());
    }

    public function testReceivedToStringReturnsHeaderFormattedString()
    {
        $this->markTestIncomplete('Received needs to be completed');

        $receivedHeader = new Header\Received();

        // @todo set some values, then test output
        $this->assertEmpty('Received: xxx', $receivedHeader->toString());
    }

    /** Implementation specific tests here */

    public function headerLines()
    {
        return [
            'newline'      => ["Received: xx\nx"],
            'cr-lf'        => ["Received: xxx\r\n"],
            'cr-lf-fold'   => ["Received: xxx\r\n\r\n zzz"],
            'cr-lf-x2'     => ["Received: xx\r\n\r\nx"],
            'multiline'    => ["Received: x\r\nx\r\nx"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testRaisesExceptionViaFromStringOnDetectionOfCrlfInjection($header)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $received = Header\Received::fromString($header);
    }

    public function invalidValues()
    {
        return [
            'newline'      => ["xx\nx"],
            'cr-lf'        => ["xxx\r\n"],
            'cr-lf-wsp'    => ["xx\r\n\r\nx"],
            'multiline'    => ["x\r\nx\r\nx"],
        ];
    }

    /**
     * @dataProvider invalidValues
     * @group ZF2015-04
     */
    public function testConstructorRaisesExceptionOnValueWithCRLFInjectionAttempt($value)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        new Header\Received($value);
    }
}

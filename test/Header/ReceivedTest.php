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
        return array(
            'newline'      => array("Received: xx\nx"),
            'cr-lf'        => array("Received: xxx\r\n"),
            'cr-lf-fold'   => array("Received: xxx\r\n\r\n zzz"),
            'cr-lf-x2'     => array("Received: xx\r\n\r\nx"),
            'multiline'    => array("Received: x\r\nx\r\nx"),
        );
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
        return array(
            'newline'      => array("xx\nx"),
            'cr-lf'        => array("xxx\r\n"),
            'cr-lf-wsp'    => array("xx\r\n\r\nx"),
            'multiline'    => array("x\r\nx\r\nx"),
        );
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

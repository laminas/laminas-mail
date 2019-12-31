<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Address;
use Laminas\Mail\Header;

/**
 * @group      Laminas_Mail
 */
class SenderTest extends \PHPUnit_Framework_TestCase
{
    public function testFromStringCreatesValidReceivedHeader()
    {
        $sender = Header\Sender::fromString('Sender: xxx');
        $this->assertInstanceOf('Laminas\Mail\Header\HeaderInterface', $sender);
        $this->assertInstanceOf('Laminas\Mail\Header\Sender', $sender);
    }

    public function testGetFieldNameReturnsHeaderName()
    {
        $sender = new Header\Sender();
        $this->assertEquals('Sender', $sender->getFieldName());
    }

    public function testReceivedGetFieldValueReturnsProperValue()
    {
        $sender = new Header\Sender();
        $sender->setAddress('foo@bar.com');
        $this->assertEquals('<foo@bar.com>', $sender->getFieldValue());
    }

    public function testReceivedToStringReturnsHeaderFormattedString()
    {
        $sender = new Header\Sender();
        $sender->setAddress('foo@bar.com');

        $this->assertEquals('Sender: <foo@bar.com>', $sender->toString());
    }

    /** Implementation specific tests here */

    public function headerLines()
    {
        return array(
            'newline'      => array("Sender: <foo@bar.com>\n"),
            'cr-lf'        => array("Sender: <foo@bar.com>\r\n"),
            'cr-lf-wsp'    => array("Sender: <foo@bar.com>\r\n\r\n"),
            'multiline'    => array("Sender: <foo\r\n@\r\nbar.com>"),
        );
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnCrlfInjectionDetection($header)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        Header\Sender::fromString($header);
    }

    /**
     * @group ZF2015-04
     */
    public function testPreventsCRLFAttackViaAddress()
    {
        $address = new Address("foo\r@\r\nexample\n.com", "This\ris\r\na\nCRLF Attack");
        $header  = new Header\Sender();
        $header->setAddress($address);

        $this->setExpectedException('Laminas\Mail\Header\Exception\RuntimeException');
        $headerLine = $header->toString();
    }
}

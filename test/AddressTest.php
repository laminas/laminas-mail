<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNotRequireNameForInstantiation()
    {
        $address = new Address('api-tools-devteam@zend.com');
        $this->assertEquals('api-tools-devteam@zend.com', $address->getEmail());
        $this->assertNull($address->getName());
    }

    public function testAcceptsNameViaConstructor()
    {
        $address = new Address('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $this->assertEquals('api-tools-devteam@zend.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testToStringCreatesStringRepresentation()
    {
        $address = new Address('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $this->assertEquals('Laminas DevTeam <api-tools-devteam@zend.com>', $address->toString());
    }

    /**
     * @dataProvider invalidSenderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     */
    public function testSetAddressInvalidAddressObject($email, $name)
    {
        $this->setExpectedException('Laminas\Mail\Exception\InvalidArgumentException');
        new Address($email, $name);
    }

    public function invalidSenderDataProvider()
    {
        return array(
            // Description => [sender address, sender name],
            'Empty' => array('', null),
            'any ASCII' => array('azAZ09-_', null),
            'any UTF-8' => array('ázÁZ09-_', null),

            // CRLF @group ZF2015-04 cases
            array("foo@bar\n", null),
            array("foo@bar\r", null),
            array("foo@bar\r\n", null),
            array("foo@bar", "\r"),
            array("foo@bar", "\n"),
            array("foo@bar", "\r\n"),
            array("foo@bar", "foo\r\nevilBody"),
            array("foo@bar", "\r\nevilBody"),
        );
    }

    /**
     * @dataProvider validSenderDataProvider
     * @param string $email
     * @param null|string $name
     */
    public function testSetAddressValidAddressObject($email, $name)
    {
        $address = new Address($email, $name);
        $this->assertInstanceOf('\Laminas\Mail\Address', $address);
    }

    public function validSenderDataProvider()
    {
        return array(
            // Description => [sender address, sender name],
            'german IDN' => array('öäü@ä-umlaut.de', null),
        );
    }
}

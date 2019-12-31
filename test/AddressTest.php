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
        return [
            // Description => [sender address, sender name],
            'Empty' => ['', null],
            'any ASCII' => ['azAZ09-_', null],
            'any UTF-8' => ['ázÁZ09-_', null],

            // CRLF @group ZF2015-04 cases
            ["foo@bar\n", null],
            ["foo@bar\r", null],
            ["foo@bar\r\n", null],
            ["foo@bar", "\r"],
            ["foo@bar", "\n"],
            ["foo@bar", "\r\n"],
            ["foo@bar", "foo\r\nevilBody"],
            ["foo@bar", "\r\nevilBody"],
        ];
    }
}

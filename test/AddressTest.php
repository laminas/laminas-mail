<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Address;

/**
 * @covers Zend\Mail\Address<extended>
 */
class AddressTest extends TestCase
{
    public function testDoesNotRequireNameForInstantiation()
    {
        $address = new Address('zf-devteam@zend.com');
        $this->assertEquals('zf-devteam@zend.com', $address->getEmail());
        $this->assertNull($address->getName());
    }

    public function testAcceptsNameViaConstructor()
    {
        $address = new Address('zf-devteam@zend.com', 'ZF DevTeam');
        $this->assertEquals('zf-devteam@zend.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testToStringCreatesStringRepresentation()
    {
        $address = new Address('zf-devteam@zend.com', 'ZF DevTeam');
        $this->assertEquals('ZF DevTeam <zf-devteam@zend.com>', $address->toString());
    }

    /**
     * @dataProvider invalidSenderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     */
    public function testSetAddressInvalidAddressObject($email, $name)
    {
        $this->expectException('Zend\Mail\Exception\InvalidArgumentException');
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

    /**
     * @dataProvider validSenderDataProvider
     * @param string $email
     * @param null|string $name
     */
    public function testSetAddressValidAddressObject($email, $name)
    {
        $address = new Address($email, $name);
        $this->assertInstanceOf('\Zend\Mail\Address', $address);
    }

    public function validSenderDataProvider()
    {
        return [
            // Description => [sender address, sender name],
            'german IDN' => ['oau@ä-umlaut.de', null],
        ];
    }
}

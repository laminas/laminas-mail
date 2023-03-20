<?php

namespace LaminasTest\Mail;

use Laminas\Mail\Address;
use Laminas\Mail\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Address<extended>
 */
class AddressTest extends TestCase
{
    public function testDoesNotRequireNameForInstantiation(): void
    {
        $address = new Address('test@example.com');
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertNull($address->getName());
    }

    public function testAcceptsNameViaConstructor(): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testToStringCreatesStringRepresentation(): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $this->assertEquals('Example Test <test@example.com>', $address->toString());
    }

    /**
     * @dataProvider invalidSenderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     */
    public function testSetAddressInvalidAddressObject($email, $name): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Address($email, $name);
    }

    public static function invalidSenderDataProvider(): array
    {
        return [
            // Description => [sender address, sender name],
            'Empty'     => ['', null],
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
    public function testSetAddressValidAddressObject($email, $name): void
    {
        $address = new Address($email, $name);
        $this->assertInstanceOf(Address::class, $address);
    }

    public static function validSenderDataProvider(): array
    {
        return [
            // Description => [sender address, sender name],
            'german IDN' => ['oau@ä-umlaut.de', null],
        ];
    }
}

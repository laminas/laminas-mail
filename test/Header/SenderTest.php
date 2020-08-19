<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Address;
use Laminas\Mail\Exception;
use Laminas\Mail\Header;
use Laminas\Mail\Header\HeaderInterface;
use Laminas\Mail\Header\Sender;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\Sender<extended>
 */
class SenderTest extends TestCase
{
    public function testFromStringCreatesValidReceivedHeader(): void
    {
        $sender = Header\Sender::fromString('Sender: <foo@bar>');
        $this->assertInstanceOf(HeaderInterface::class, $sender);
        $this->assertInstanceOf(Sender::class, $sender);
    }

    public function testGetFieldNameReturnsHeaderName(): void
    {
        $sender = new Header\Sender();
        $this->assertEquals('Sender', $sender->getFieldName());
    }

    /**
     * @dataProvider validSenderHeaderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     * @param string $expectedFieldValue,
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testParseValidSenderHeader($expectedFieldValue, $encodedValue, $encoding): void
    {
        $header = Header\Sender::fromString('Sender:' . $encodedValue);

        $this->assertEquals($expectedFieldValue, $header->getFieldValue());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    /**
     * @dataProvider invalidSenderEncodedDataProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $expectedException
     */
    public function testParseInvalidSenderHeaderThrowException(
        $decodedValue,
        $expectedException
    ): void {
        $this->expectException($expectedException);
        Header\Sender::fromString('Sender:' . $decodedValue);
    }

    /**
     * @dataProvider validSenderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     * @param string $encodedValue
     * @param string $expectedFieldValue,
     * @param string $encoding
     */
    public function testSetAddressValidValue($email, $name, $expectedFieldValue, $encodedValue, $encoding): void
    {
        $header = new Header\Sender();
        $header->setAddress($email, $name);

        $this->assertEquals($expectedFieldValue, $header->getFieldValue());
        $this->assertEquals('Sender: ' . $encodedValue, $header->toString());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    /**
     * @dataProvider invalidSenderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     */
    public function testSetAddressInvalidValue($email, $name): void
    {
        $header = new Header\Sender();
        $this->expectException(Exception\InvalidArgumentException::class);
        $header->setAddress($email, $name);
    }

    /**
     * @dataProvider validSenderDataProvider
     * @group ZF2015-04
     * @param string $email
     * @param null|string $name
     * @param string $expectedFieldValue,
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testSetAddressValidAddressObject($email, $name, $expectedFieldValue, $encodedValue, $encoding): void
    {
        $address = new Address($email, $name);

        $header = new Header\Sender();
        $header->setAddress($address);

        $this->assertSame($address, $header->getAddress());
        $this->assertEquals($expectedFieldValue, $header->getFieldValue());
        $this->assertEquals('Sender: ' . $encodedValue, $header->toString());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    public function validSenderDataProvider(): array
    {
        return [
            // Description => [sender address, sender name, getFieldValue, encoded version, encoding],
            'ASCII address' => [
                'foo@bar',
                null,
                '<foo@bar>',
                '<foo@bar>',
                'ASCII',
            ],
            'ASCII name' => [
                'foo@bar',
                'foo',
                'foo <foo@bar>',
                'foo <foo@bar>',
                'ASCII',
            ],
            'UTF-8 name' => [
                'foo@bar',
                'ázÁZ09',
                'ázÁZ09 <foo@bar>',
                '=?UTF-8?Q?=C3=A1z=C3=81Z09?= <foo@bar>',
                'UTF-8',
            ],
        ];
    }

    public function validSenderHeaderDataProvider(): array
    {
        return array_merge(array_map(function ($parameters) {
            return array_slice($parameters, 2);
        }, $this->validSenderDataProvider()), [
            // Per RFC 2822, 3.4 and 3.6.2, "Sender: foo@bar" is valid.
            'Unbracketed email' => [
                '<foo@bar>',
                'foo@bar',
                'ASCII',
            ],
        ]);
    }

    public function invalidSenderDataProvider(): array
    {
        $mailInvalidArgumentException = Exception\InvalidArgumentException::class;

        return [
            // Description => [sender address, sender name, exception class, exception message],
            'Empty' => ['', null, $mailInvalidArgumentException, null],
            'any ASCII' => ['azAZ09-_', null, $mailInvalidArgumentException, null],
            'any UTF-8' => ['ázÁZ09-_', null, $mailInvalidArgumentException, null],
            'non-string' => [null, null, $mailInvalidArgumentException, null],

            // CRLF @group ZF2015-04 cases
            ["foo@bar\n", null, $mailInvalidArgumentException, null],
            ["foo@bar\r", null, $mailInvalidArgumentException, null],
            ["foo@bar\r\n", null, $mailInvalidArgumentException, null],
            ["foo@bar", "\r", $mailInvalidArgumentException, null],
            ["foo@bar", "\n", $mailInvalidArgumentException, null],
            ["foo@bar", "\r\n", $mailInvalidArgumentException, null],
            ["foo@bar", "foo\r\nevilBody", $mailInvalidArgumentException, null],
            ["foo@bar", "\r\nevilBody", $mailInvalidArgumentException, null],
        ];
    }

    public function invalidSenderEncodedDataProvider(): array
    {
        $mailInvalidArgumentException = Exception\InvalidArgumentException::class;
        $headerInvalidArgumentException = Header\Exception\InvalidArgumentException::class;

        return [
            // Description => [decoded format, exception class, exception message],
            'Empty' => ['', $mailInvalidArgumentException],
            'any ASCII' => ['azAZ09-_', $mailInvalidArgumentException],
            'any UTF-8' => ['ázÁZ09-_', $mailInvalidArgumentException],
            ["xxx yyy\n", $mailInvalidArgumentException],
            ["xxx yyy\r\n", $mailInvalidArgumentException],
            ["xxx yyy\r\n\r\n", $mailInvalidArgumentException],
            ["xxx\r\ny\r\nyy", $mailInvalidArgumentException],
            ["foo\r\n@\r\nbar", $mailInvalidArgumentException],

            ["ázÁZ09 <foo@bar>", $headerInvalidArgumentException],
            'newline' => ["<foo@bar>\n", $headerInvalidArgumentException],
            'cr-lf' => ["<foo@bar>\r\n", $headerInvalidArgumentException],
            'cr-lf-wsp' => ["<foo@bar>\r\n\r\n", $headerInvalidArgumentException],
            'multiline' => ["<foo\r\n@\r\nbar>", $headerInvalidArgumentException],
        ];
    }

    /**
     * @param string $headerString
     * @param string $expectedName
     * @param string $expectedEmail
     *
     * @dataProvider validHeaderLinesProvider
     */
    public function testFromStringWithValidInput($headerString, $expectedName, $expectedEmail): void
    {
        $header = Header\Sender::fromString($headerString);

        $this->assertSame($expectedName, $header->getAddress()->getName());
        $this->assertSame($expectedEmail, $header->getAddress()->getEmail());
    }

    public function validHeaderLinesProvider(): array
    {
        // @codingStandardsIgnoreStart
        return [
            // [ header line,                                  expected sender name, expected email address ]
            ['Sender: foo@bar',                                null,                 'foo@bar'],
            ['Sender: <foo@bar>',                              null,                 'foo@bar'],
            ['Sender:    foo@bar',                             null,                 'foo@bar'],
            ['Sender: name <foo@bar>',                         'name',               'foo@bar'],
            ['Sender: <weird name> <foo@bar>',                 '<weird name>',       'foo@bar'],
            ['Sender: moar words <foo@bar>',                   'moar words',         'foo@bar'],
            ['Sender: =?UTF-8?Q?=C3=A1z=C3=81Z09?= <foo@bar>', 'ázÁZ09',             'foo@bar'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @param string $headerString
     * @param string $expectedException
     * @param string $expectedMessagePart
     *
     * @dataProvider invalidHeaderLinesProvider
     */
    public function testFromStringWithInvalidInput($headerString, $expectedException, $expectedMessagePart = ''): void
    {
        $this->expectException($expectedException);
        if ($expectedMessagePart) {
            $this->expectExceptionMessage($expectedMessagePart);
        }

        Header\Sender::fromString($headerString);
    }

    public function invalidHeaderLinesProvider(): array
    {
        $mailInvalidArgumentException = Exception\InvalidArgumentException::class;
        $headerInvalidArgumentException = Header\Exception\InvalidArgumentException::class;

        return [
            ['Sender: foo', $mailInvalidArgumentException],
            ['Sender: foo<foo>', $mailInvalidArgumentException],
            ['Sender: foo foo', $headerInvalidArgumentException],
            ['Sender: <foo> foo', $headerInvalidArgumentException],
        ];
    }

    public function testDefaultEncoding(): void
    {
        $header = new Header\Sender();
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncoding(): void
    {
        $header = new Header\Sender();
        $header->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $header->getEncoding());
    }

    public function testFromStringRaisesExceptionOnInvalidHeader(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header name for Sender string');
        Header\Sender::fromString('Foo: bar');
    }
}

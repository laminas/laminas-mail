<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\GenericHeader;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Header\GenericHeader<extended>
 */
class GenericHeaderTest extends TestCase
{
    public function invalidHeaderLines(): array
    {
        return [
            'append-chr-32' => [
                'Content-Type' . chr(32) . ': text/html',
                'Invalid header name detected',
            ],
            'newline-non-continuation' => [
                'Content-Type: text/html; charset = "iso-8859-1"' . "\nThis is a test",
                'Invalid header value detected',
            ],
            'missing-colon' => [
                'content-type text/html',
                'Header must match with the format "name:value"',
            ],
        ];
    }

    /**
     * @dataProvider invalidHeaderLines
     * @group ZF2015-04
     */
    public function testSplitHeaderLineRaisesExceptionOnInvalidHeader($line, $message): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        GenericHeader::splitHeaderLine($line);
    }

    public function fieldNames(): array
    {
        return [
            'append-chr-13'  => ["Subject" . chr(13)],
            'append-chr-127' => ["Subject" . chr(127)],
            'non-string' => [null],
        ];
    }

    /**
     * @dataProvider fieldNames
     * @group ZF2015-04
     */
    public function testRaisesExceptionOnInvalidFieldName($fieldName): void
    {
        $header = new GenericHeader();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('name');
        $header->setFieldName($fieldName);
    }

    public function fieldValues(): array
    {
        return [
            'empty-lines'             => ["\n\n\r\n\r\n\n"],
            'trailing-newlines'       => ["Value\n\n\r\n\r\n\n"],
            'leading-newlines'        => ["\n\n\r\n\r\n\nValue"],
            'surrounding-newlines'    => ["\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"],
            'split-value'             => ["Some\n\n\r\n\r\n\nValue"],
            'leading-split-value'     => ["\n\n\r\n\r\n\nSome\n\n\r\n\r\n\nValue"],
            'trailing-split-value'    => ["Some\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"],
            'surrounding-split-value' => ["\n\n\r\n\r\n\nSome\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"],
        ];
    }

    /**
     * @dataProvider fieldValues
     * @group ZF2015-04
     * @param string $fieldValue
     */
    public function testCRLFsequencesAreEncodedOnToString($fieldValue): void
    {
        $header = new GenericHeader('Foo');
        $header->setFieldValue($fieldValue);

        $serialized = $header->toString();
        $this->assertStringNotContainsString("\n", $serialized);
        $this->assertStringNotContainsString("\r", $serialized);
    }

    /**
     * @dataProvider validFieldValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testParseValidSubjectHeader($decodedValue, $encodedValue, $encoding): void
    {
        $header = GenericHeader::fromString('Foo:' . $encodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    /**
     * @dataProvider validFieldValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testSetFieldValueValidValue($decodedValue, $encodedValue, $encoding): void
    {
        $header = new GenericHeader('Foo');
        $header->setFieldValue($decodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals('Foo: ' . $encodedValue, $header->toString());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    public function validFieldValuesProvider(): array
    {
        return [
            // Description => [decoded format, encoded format, encoding],
            //'Empty' => array('', '', 'ASCII'),

            // Encoding cases
            'ASCII charset' => ['azAZ09-_', 'azAZ09-_', 'ASCII'],
            'UTF-8 charset' => ['ázÁZ09-_', '=?UTF-8?Q?=C3=A1z=C3=81Z09-=5F?=', 'UTF-8'],

            // CRLF @group ZF2015-04 cases
            'newline' => ["xxx yyy\n", '=?UTF-8?Q?xxx=20yyy=0A?=', 'UTF-8'],
            'cr-lf' => ["xxx yyy\r\n", '=?UTF-8?Q?xxx=20yyy=0D=0A?=', 'UTF-8'],
            'cr-lf-wsp' => ["xxx yyy\r\n\r\n", '=?UTF-8?Q?xxx=20yyy=0D=0A=0D=0A?=', 'UTF-8'],
            'multiline' => ["xxx\r\ny\r\nyy", '=?UTF-8?Q?xxx=0D=0Ay=0D=0Ayy?=', 'UTF-8'],
        ];
    }

    /**
     * @group ZF2015-04
     */
    public function testCastingToStringHandlesContinuationsProperly(): void
    {
        $encoded = '=?UTF-8?Q?foo=0D=0A=20bar?=';
        $raw = "foo\r\n bar";

        $header = new GenericHeader('Foo');
        $header->setFieldValue($raw);

        $this->assertEquals($raw, $header->getFieldValue());
        $this->assertEquals($encoded, $header->getFieldValue(GenericHeader::FORMAT_ENCODED));
        $this->assertEquals('Foo: ' . $encoded, $header->toString());
    }

    public function testAllowZeroInHeaderValueInConstructor(): void
    {
        $header = new GenericHeader('Foo', 0);
        $this->assertEquals(0, $header->getFieldValue());
        $this->assertEquals('Foo: 0', $header->toString());
    }

    public function testDefaultEncoding(): void
    {
        $header = new GenericHeader('Foo');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncoding(): void
    {
        $header = new GenericHeader('Foo');
        $header->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $header->getEncoding());
    }

    public function testToStringThrowsWithoutFieldName(): void
    {
        $header = new GenericHeader();

        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('Header name is not set, use setFieldName()');
        $header->toString();
    }

    public function testChangeEncodingToAsciiNotAllowedWhenHeaderValueContainsUtf8Characters(): void
    {
        $subject = new GenericHeader();
        $subject->setFieldValue('Accents òàùèéì');

        $this->assertSame('UTF-8', $subject->getEncoding());

        $subject->setEncoding('ASCII');
        $this->assertSame('UTF-8', $subject->getEncoding());
    }

    public function testChangeEncodingBackToAscii(): void
    {
        $subject = new GenericHeader('X-Test');
        $subject->setFieldValue('test');

        $this->assertSame('ASCII', $subject->getEncoding());

        $subject->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $subject->getEncoding());

        $subject->setEncoding('ASCII');
        $this->assertSame('ASCII', $subject->getEncoding());
    }

    public function testSetNullEncoding(): void
    {
        $subject = GenericHeader::fromString('X-Test: test');
        $this->assertSame('ASCII', $subject->getEncoding());

        $subject->setEncoding(null);
        $this->assertSame('ASCII', $subject->getEncoding());
    }

    public function testSettingFieldValueCanChangeEncoding(): void
    {
        $subject = GenericHeader::fromString('X-Test: test');
        $this->assertSame('ASCII', $subject->getEncoding());

        $subject->setFieldValue('Accents òàùèéì');
        $this->assertSame('UTF-8', $subject->getEncoding());
    }

    public function testSettingTheSameEncoding(): void
    {
        $subject = GenericHeader::fromString('X-Test: test');
        $this->assertSame('ASCII', $subject->getEncoding());

        $subject->setEncoding('ASCII');
        $this->assertSame('ASCII', $subject->getEncoding());
    }
}

<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;
use Laminas\Mail\Header\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\Subject<extended>
 */
class SubjectTest extends TestCase
{
    public function testHeaderFolding()
    {
        $string  = str_repeat('foobarblahblahblah baz bat', 10);
        $subject = new Header\Subject();
        $subject->setSubject($string);

        $expected = wordwrap($string, 78, "\r\n ");
        $test     = $subject->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals($expected, $test);
    }

    public function testDefaultEncoding()
    {
        $header = Header\Subject::fromString('Subject: test');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncoding()
    {
        $header = Header\Subject::fromString('Subject: test');
        $header->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $header->getEncoding());
    }

    /**
     * @dataProvider validSubjectValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testParseValidSubjectHeader($decodedValue, $encodedValue, $encoding)
    {
        $header = Header\Subject::fromString('Subject:' . $encodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    /**
     * @dataProvider invalidSubjectValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $expectedException
     * @param string|null $expectedExceptionMessage
     */
    public function testParseInvalidSubjectHeaderThrowException(
        $decodedValue,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        Header\Subject::fromString('Subject:' . $decodedValue);
    }

    public function testFromStringRaisesExceptionOnInvalidHeader()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for Subject string');
        Header\Subject::fromString('Foo: bar');
    }

    /**
     * @dataProvider validSubjectValuesProvider
     * @group ZF2015-04
     * @param string $decodedValue
     * @param string $encodedValue
     * @param string $encoding
     */
    public function testSetSubjectValidValue($decodedValue, $encodedValue, $encoding)
    {
        $header = new Header\Subject();
        $header->setSubject($decodedValue);

        $this->assertEquals($decodedValue, $header->getFieldValue());
        $this->assertEquals('Subject: ' . $encodedValue, $header->toString());
        $this->assertEquals($encoding, $header->getEncoding());
    }

    public function validSubjectValuesProvider()
    {
        return [
            // Description => [decoded format, encoded format, encoding],
            'Empty' => ['', '', 'ASCII'],

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

    public function invalidSubjectValuesProvider()
    {
        $invalidArgumentException = Exception\InvalidArgumentException::class;
        $invalidHeaderValueDetected = 'Invalid header value detected';

        return [
            // Description => [decoded format, exception class, exception message],
            'newline' => ["xxx yyy\n", $invalidArgumentException, $invalidHeaderValueDetected],
            'cr-lf' => ["xxx yyy\r\n", $invalidArgumentException, $invalidHeaderValueDetected],
            'cr-lf-wsp' => ["xxx yyy\r\n\r\n", $invalidArgumentException, $invalidHeaderValueDetected],
            'multiline' => ["xxx\r\ny\r\nyy", $invalidArgumentException, $invalidHeaderValueDetected],
        ];
    }

    public function testChangeEncodingToAsciiNotAllowedWhenSubjectContainsUtf8Characters()
    {
        $subject = new Header\Subject();
        $subject->setSubject('Accents òàùèéì');

        self::assertSame('UTF-8', $subject->getEncoding());

        $subject->setEncoding('ASCII');
        self::assertSame('UTF-8', $subject->getEncoding());
    }

    public function testChangeEncodingBackToAscii()
    {
        $subject = new Header\Subject();
        $subject->setSubject('test');

        self::assertSame('ASCII', $subject->getEncoding());

        $subject->setEncoding('UTF-8');
        self::assertSame('UTF-8', $subject->getEncoding());

        $subject->setEncoding('ASCII');
        self::assertSame('ASCII', $subject->getEncoding());
    }

    public function testSetNullEncoding()
    {
        $subject = Header\Subject::fromString('Subject: test');
        self::assertSame('ASCII', $subject->getEncoding());

        $subject->setEncoding(null);
        self::assertSame('ASCII', $subject->getEncoding());
    }

    public function testSettingSubjectCanChangeEncoding()
    {
        $subject = Header\Subject::fromString('Subject: test');
        self::assertSame('ASCII', $subject->getEncoding());

        $subject->setSubject('Accents òàùèéì');
        self::assertSame('UTF-8', $subject->getEncoding());
    }

    public function testSettingTheSameEncoding()
    {
        $subject = Header\Subject::fromString('Subject: test');
        self::assertSame('ASCII', $subject->getEncoding());

        $subject->setEncoding('ASCII');
        self::assertSame('ASCII', $subject->getEncoding());
    }
}

<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\ContentTransferEncoding;
use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\HeaderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\ContentTransferEncoding<extended>
 */
class ContentTransferEncodingTest extends TestCase
{
    public function dataValidEncodings()
    {
        return [
            ['7bit'],
            ['8bit'],
            ['binary'],
            ['quoted-printable'],
        ];
    }

    public function dataInvalidEncodings()
    {
        return [
            ['9bit'],
            ['x-something'],
        ];
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingFromStringCreatesValidContentTransferEncodingHeader($encoding)
    {
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: '.$encoding);
        $this->assertInstanceOf(HeaderInterface::class, $contentTransferEncodingHeader);
        $this->assertInstanceOf(ContentTransferEncoding::class, $contentTransferEncodingHeader);
    }

    /**
     * @dataProvider dataInvalidEncodings
     */
    public function testContentTransferEncodingFromStringRaisesException($encoding)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: '.$encoding);
    }

    public function testContentTransferEncodingGetFieldNameReturnsHeaderName()
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $this->assertEquals('Content-Transfer-Encoding', $contentTransferEncodingHeader->getFieldName());
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingGetFieldValueReturnsProperValue($encoding)
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding($encoding);
        $this->assertEquals($encoding, $contentTransferEncodingHeader->getFieldValue());
        $this->assertEquals($encoding, $contentTransferEncodingHeader->getTransferEncoding());
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingHandlesCaseInsensitivity($encoding)
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding(strtoupper(substr($encoding, 0, 4)).substr($encoding, 4));
        $this->assertEquals(strtolower($encoding), strtolower($header->getFieldValue()));
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingToStringReturnsHeaderFormattedString($encoding)
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding($encoding);
        $this->assertEquals("Content-Transfer-Encoding: ".$encoding, $contentTransferEncodingHeader->toString());
    }

    public function testProvidingParametersIntroducesHeaderFolding()
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding('quoted-printable');
        $string = $header->toString();

        $this->assertContains("Content-Transfer-Encoding: quoted-printable", $string);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnInvalidHeaderName()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        ContentTransferEncoding::fromString('Content-Transfer-Encoding' . chr(32) . ': 8bit');
    }

    public function headerLines()
    {
        return [
            'newline' => ["Content-Transfer-Encoding: 8bit\n7bit"],
            'cr-lf' => ["Content-Transfer-Encoding: 8bit\r\n7bit"],
            'multiline' => ["Content-Transfer-Encoding: 8bit\r\n7bit\r\nUTF-8"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForInvalidMultilineValues($headerLine)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        ContentTransferEncoding::fromString($headerLine);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForContinuations()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects');
        ContentTransferEncoding::fromString("Content-Transfer-Encoding: 8bit\r\n 7bit");
    }

    /**
     * @group ZF2015-04
     */
    public function testSetTransferEncodingRaisesExceptionForInvalidValues()
    {
        $header = new ContentTransferEncoding();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects');
        $header->setTransferEncoding("8bit\r\n 7bit");
    }

    public function testFromStringRaisesExceptionOnInvalidHeader()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for Content-Transfer-Encoding string');
        ContentTransferEncoding::fromString('Foo: bar');
    }

    public function testDefaultEncoding()
    {
        $header = new ContentTransferEncoding();
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testChangeEncodingHasNoEffect()
    {
        $header = new ContentTransferEncoding();
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }
}

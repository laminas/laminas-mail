<?php

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\ContentTransferEncoding;
use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\HeaderInterface;
use PHPUnit\Framework\TestCase;

use function chr;
use function strtolower;
use function strtoupper;
use function substr;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\ContentTransferEncoding<extended>
 */
class ContentTransferEncodingTest extends TestCase
{
    public static function dataValidEncodings(): array
    {
        return [
            ['7bit'],
            ['8bit'],
            ['binary'],
            ['quoted-printable'],
        ];
    }

    public static function dataInvalidEncodings(): array
    {
        return [
            ['9bit'],
            ['x-something'],
        ];
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingFromStringCreatesValidContentTransferEncodingHeader(
        string $encoding
    ): void {
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: ' . $encoding);
        $this->assertInstanceOf(HeaderInterface::class, $contentTransferEncodingHeader);
        $this->assertInstanceOf(ContentTransferEncoding::class, $contentTransferEncodingHeader);
    }

    /**
     * @dataProvider dataInvalidEncodings
     */
    public function testContentTransferEncodingFromStringRaisesException(string $encoding): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: ' . $encoding);
    }

    public function testContentTransferEncodingGetFieldNameReturnsHeaderName(): void
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $this->assertEquals('Content-Transfer-Encoding', $contentTransferEncodingHeader->getFieldName());
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingGetFieldValueReturnsProperValue(string $encoding): void
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding($encoding);
        $this->assertEquals($encoding, $contentTransferEncodingHeader->getFieldValue());
        $this->assertEquals($encoding, $contentTransferEncodingHeader->getTransferEncoding());
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingHandlesCaseInsensitivity(string $encoding): void
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding(strtoupper(substr($encoding, 0, 4)) . substr($encoding, 4));
        $this->assertEquals(strtolower($encoding), strtolower($header->getFieldValue()));
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingToStringReturnsHeaderFormattedString(string $encoding): void
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding($encoding);
        $this->assertEquals("Content-Transfer-Encoding: " . $encoding, $contentTransferEncodingHeader->toString());
    }

    public function testProvidingParametersIntroducesHeaderFolding(): void
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding('quoted-printable');
        $string = $header->toString();

        $this->assertStringContainsString("Content-Transfer-Encoding: quoted-printable", $string);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnInvalidHeaderName(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        ContentTransferEncoding::fromString('Content-Transfer-Encoding' . chr(32) . ': 8bit');
    }

    public static function headerLines(): array
    {
        return [
            'newline'   => ["Content-Transfer-Encoding: 8bit\n7bit"],
            'cr-lf'     => ["Content-Transfer-Encoding: 8bit\r\n7bit"],
            'multiline' => ["Content-Transfer-Encoding: 8bit\r\n7bit\r\nUTF-8"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForInvalidMultilineValues(string $headerLine): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        ContentTransferEncoding::fromString($headerLine);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForContinuations(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects');
        ContentTransferEncoding::fromString("Content-Transfer-Encoding: 8bit\r\n 7bit");
    }

    /**
     * @group ZF2015-04
     */
    public function testSetTransferEncodingRaisesExceptionForInvalidValues(): void
    {
        $header = new ContentTransferEncoding();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects');
        $header->setTransferEncoding("8bit\r\n 7bit");
    }

    public function testFromStringRaisesExceptionOnInvalidHeader(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for Content-Transfer-Encoding string');
        ContentTransferEncoding::fromString('Foo: bar');
    }

    public function testDefaultEncoding(): void
    {
        $header = new ContentTransferEncoding();
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testChangeEncodingHasNoEffect(): void
    {
        $header = new ContentTransferEncoding();
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public static function unconventionalHeaderLinesProvider(): array
    {
        return [
            // Description => [header line, expected value]
            'contenttransferencoding'   => ['ContentTransferEncoding: 7bit', '7bit'],
            'content_transfer_encoding' => ['Content_Transfer_Encoding: 7bit', '7bit'],
        ];
    }

    /**
     * @dataProvider unconventionalHeaderLinesProvider
     */
    public function testFromStringHandlesUnconventionalNames(string $headerLine, string $expected): void
    {
        $header = ContentTransferEncoding::fromString($headerLine);
        $this->assertInstanceOf(ContentTransferEncoding::class, $header);
        $this->assertEquals('Content-Transfer-Encoding', $header->getFieldName());
        $this->assertEquals($expected, $header->getFieldValue());
    }
}

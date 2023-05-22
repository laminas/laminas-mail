<?php

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header;
use Laminas\Mail\Header\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\MimeVersion<extended>
 */
class MimeVersionTest extends TestCase
{
    public function testSettingManually(): void
    {
        $version = "2.0";
        $mime    = new Header\MimeVersion();
        $mime->setVersion($version);
        $this->assertEquals($version, $mime->getFieldValue());
    }

    public function testDefaultVersion(): void
    {
        $mime = new Header\MimeVersion();
        $this->assertEquals('1.0', $mime->getVersion());
        $this->assertEquals('MIME-Version: 1.0', $mime->toString());
    }

    public static function headerLines(): array
    {
        return [
            'newline'   => ["MIME-Version: 5.0\nbar"],
            'cr-lf'     => ["MIME-Version: 2.0\r\n"],
            'cr-lf-wsp' => ["MIME-Version: 3\r\n\r\n.1"],
            'multiline' => ["MIME-Version: baz\r\nbar\r\nbau"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnDetectionOfCrlfInjection(string $header): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $mime = Header\MimeVersion::fromString($header);
    }

    public static function invalidVersions(): array
    {
        return [
            'no-decimal'    => ['1'],
            'multi-decimal' => ['1.0.0'],
            'alpha'         => ['X.Y'],
            'non-alnum'     => ['Version 1.0'],
        ];
    }

    /**
     * @dataProvider invalidVersions
     * @group ZF2015-04
     */
    public function testRaisesExceptionOnInvalidVersionFromSetVersion(string $value): void
    {
        $header = new Header\MimeVersion();
        $this->expectException(Exception\InvalidArgumentException::class);
        $header->setVersion($value);
    }

    public function testFromStringRaisesExceptionOnInvalidHeader(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for MIME-Version string');
        Header\MimeVersion::fromString('Foo: bar');
    }

    public function testDefaultEncoding(): void
    {
        $header = new Header\MimeVersion();
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncodingHasNoEffect(): void
    {
        $header = new Header\MimeVersion();
        $header->setEncoding('UTF-8');
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public static function unconventionalHeaderLinesProvider(): array
    {
        return [
            // Description => [header line, expected value]
            'mimeversion'  => ["MIMEVersion: 1.0", "1.0"],
            'mime_version' => ["MIME_Version: 1.0", "1.0"],
        ];
    }

    /**
     * @dataProvider unconventionalHeaderLinesProvider
     */
    public function testFromStringHandlesUnconventionalNames(string $headerLine, string $expected): void
    {
        $header = Header\MimeVersion::fromString($headerLine);
        $this->assertInstanceOf(Header\MimeVersion::class, $header);
        $this->assertEquals('MIME-Version', $header->getFieldName());
        $this->assertEquals($expected, $header->getFieldValue());
    }
}

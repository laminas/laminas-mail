<?php

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\ContentType;
use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\HeaderInterface;
use Laminas\Mail\Header\UnstructuredInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\ContentType<extended>
 */
class ContentTypeTest extends TestCase
{
    public function testImplementsHeaderInterface(): void
    {
        $header = new ContentType();

        $this->assertInstanceOf(UnstructuredInterface::class, $header);
        $this->assertInstanceOf(HeaderInterface::class, $header);
    }

    /**
     * @group 6491
     */
    public function testTrailingSemiColonFromString(): void
    {
        $contentTypeHeader = ContentType::fromString(
            'Content-Type: multipart/alternative; boundary="Apple-Mail=_1B852F10-F9C6-463D-AADD-CD503A5428DD";'
        );
        $params            = $contentTypeHeader->getParameters();
        $this->assertEquals(['boundary' => 'Apple-Mail=_1B852F10-F9C6-463D-AADD-CD503A5428DD'], $params);
    }

    public function testExtractsExtraInformationWithoutBeingConfusedByTrailingSemicolon(): void
    {
        $header = ContentType::fromString('Content-Type: application/pdf;name="foo.pdf";');
        $this->assertEquals($header->getParameters(), ['name' => 'foo.pdf']);
    }

    public static function getLiteralData(): array
    {
        return [
            [
                ['name' => 'foo; bar.txt'],
                'text/plain; name="foo; bar.txt"',
            ],
            [
                ['name' => 'foo&bar.txt'],
                'text/plain; name="foo&bar.txt"',
            ],
        ];
    }

    /**
     * @dataProvider getLiteralData
     */
    public function testHandlesLiterals(array $expected, string $header): void
    {
        $header = ContentType::fromString('Content-Type: ' . $header);
        $this->assertEquals($expected, $header->getParameters());
    }

    /**
     * @dataProvider setTypeProvider
     */
    public function testFromString(string $type, array $parameters, string $fieldValue, string $expectedToString): void
    {
        $header = ContentType::fromString($expectedToString);

        $this->assertInstanceOf(ContentType::class, $header);
        $this->assertEquals('Content-Type', $header->getFieldName(), 'getFieldName() value not match');
        $this->assertEquals($type, $header->getType(), 'getType() value not match');
        $this->assertEquals($fieldValue, $header->getFieldValue(), 'getFieldValue() value not match');
        $this->assertEquals($parameters, $header->getParameters(), 'getParameters() value not match');
        $this->assertEquals($expectedToString, $header->toString(), 'toString() value not match');
    }

    /**
     * @dataProvider setTypeProvider
     */
    public function testSetType(string $type, array $parameters, string $fieldValue, string $expectedToString): void
    {
        $header = new ContentType();

        $header->setType($type);
        foreach ($parameters as $name => $value) {
            $header->addParameter($name, $value);
        }

        $this->assertEquals('Content-Type', $header->getFieldName(), 'getFieldName() value not match');
        $this->assertEquals($type, $header->getType(), 'getType() value not match');
        $this->assertEquals($fieldValue, $header->getFieldValue(), 'getFieldValue() value not match');
        $this->assertEquals($parameters, $header->getParameters(), 'getParameters() value not match');
        $this->assertEquals($expectedToString, $header->toString(), 'toString() value not match');
    }

    /**
     * @param class-string $expectedException
     * @dataProvider invalidHeaderLinesProvider
     */
    public function testFromStringThrowException(
        string $headerLine,
        string $expectedException,
        string $exceptionMessage
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);
        ContentType::fromString($headerLine);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringHandlesContinuations(): void
    {
        $header = ContentType::fromString("Content-Type: text/html;\r\n level=1");
        $this->assertEquals('text/html', $header->getType());
        $this->assertEquals(['level' => '1'], $header->getParameters());
    }

    /**
     * Should not throw if the optional count is missing
     *
     * @see https://tools.ietf.org/html/rfc2231
     *
     * @dataProvider parameterWrappingProvider
     */
    public function testParameterWrapping(string $input, array $parameters): void
    {
        $header = ContentType::fromString($input);

        $this->assertEquals($parameters, $header->getParameters());
    }

    /**
     * @param class-string $expectedException
     * @dataProvider invalidParametersProvider
     */
    public function testAddParameterThrowException(
        string $paramName,
        string $paramValue,
        string $expectedException,
        string $exceptionMessage
    ): void {
        $header = new ContentType();
        $header->setType('text/html');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);
        $header->addParameter($paramName, $paramValue);
    }

    public static function setTypeProvider(): array
    {
        $foldingHeaderLine = "Content-Type: foo/baz;\r\n charset=\"us-ascii\"";
        $foldingFieldValue = "foo/baz;\r\n charset=\"us-ascii\"";

        $encodedHeaderLine = "Content-Type: foo/baz;\r\n name=\"=?UTF-8?Q?=C3=93?=\"";
        $encodedFieldValue = "foo/baz;\r\n name=\"Ó\"";

        // @codingStandardsIgnoreStart
        return [
            // Description => [$type, $parameters, $fieldValue, toString()]
            // @group #2728
            'foo/a.b-c' => ['foo/a.b-c', [], 'foo/a.b-c', 'Content-Type: foo/a.b-c'],
            'foo/a+b'   => ['foo/a+b'  , [], 'foo/a+b'  , 'Content-Type: foo/a+b'],
            'foo/baz'   => ['foo/baz'  , [], 'foo/baz'  , 'Content-Type: foo/baz'],
            'parameter use header folding' => ['foo/baz'  , ['charset' => 'us-ascii'], $foldingFieldValue, $foldingHeaderLine],
            'encoded characters' => ['foo/baz'  , ['name' => 'Ó'], $encodedFieldValue, $encodedHeaderLine],
        ];
        // @codingStandardsIgnoreEnd
    }

    public static function invalidParametersProvider(): array
    {
        $invalidArgumentException = Exception\InvalidArgumentException::class;

        // @codingStandardsIgnoreStart
        return [
            // Description => [param name, param value, expected exception, exception message contain]

            // @group ZF2015-04
            'invalid name' => ["b\r\na\rr\n", 'baz', $invalidArgumentException, 'parameter name'],
        ];
        // @codingStandardsIgnoreEnd
    }

    public static function invalidHeaderLinesProvider(): array
    {
        $invalidArgumentException = Exception\InvalidArgumentException::class;

        // @codingStandardsIgnoreStart
        return [
            // Description => [header line, expected exception, exception message contain]

            // @group ZF2015-04
            'invalid name' => ['Content-Type' . chr(32) . ': text/html', $invalidArgumentException, 'header name'],
            'newline'   => ["Content-Type: text/html;\nlevel=1", $invalidArgumentException, 'header value'],
            'cr-lf'     => ["Content-Type: text/html\r\n;level=1", $invalidArgumentException, 'header value'],
            'multiline' => ["Content-Type: text/html;\r\nlevel=1\r\nq=0.1", $invalidArgumentException, 'header value'],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function testFromStringRaisesExceptionOnInvalidHeader(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header line for Content-Type string');
        ContentType::fromString('Foo: bar');
    }

    public function testDefaultEncoding(): void
    {
        $header = new ContentType();
        $this->assertSame('ASCII', $header->getEncoding());
    }

    public function testSetEncoding(): void
    {
        $header = new ContentType();
        $header->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $header->getEncoding());
    }

    public function testSetTypeThrowsOnInvalidValue(): void
    {
        $header = new ContentType();
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('setType expects a value in the format "type/subtype"');
        $header->setType('invalid');
    }

    public function testGetParameter(): void
    {
        $header = ContentType::fromString('content-type: text/plain; level=top');
        $this->assertSame('top', $header->getParameter('level'));
    }

    public function testGetParameterWithSpaceTrimmed(): void
    {
        $header = ContentType::fromString('content-type: text/plain; level=top; name="logfile.log";');
        $this->assertSame('logfile.log', $header->getParameter('name'));
    }

    public function testGetParameterNotExists(): void
    {
        $header = ContentType::fromString('content-type: text/plain');
        $this->assertNull($header->getParameter('level'));
    }

    public function testRemoveParameter(): void
    {
        $header = ContentType::fromString('content-type: text/plain; level=top');
        $this->assertTrue($header->removeParameter('level'));
    }

    public function testRemoveParameterNotExists(): void
    {
        $header = ContentType::fromString('content-type: text/plain');
        $this->assertFalse($header->removeParameter('level'));
    }

    public static function parameterWrappingProvider(): iterable
    {
        yield 'Example from RFC2231' => [
            "Content-Type: application/x-stuff; title*=us-ascii'en-us'This%20is%20%2A%2A%2Afun%2A%2A%2A",
            ['title*' => "us-ascii'en-us'This%20is%20%2A%2A%2Afun%2A%2A%2A"],
        ];
    }

    public static function unconventionalHeaderLinesProvider(): array
    {
        return [
            // Description => [header line, expected value]
            'contenttype'  => ['ContentType: text/plain', 'text/plain'],
            'content_type' => ['Content_Type: text/plain', 'text/plain'],
        ];
    }

    /**
     * @dataProvider unconventionalHeaderLinesProvider
     */
    public function testFromStringHandlesUnconventionalNames(string $headerLine, string $expected): void
    {
        $header = ContentType::fromString($headerLine);
        $this->assertInstanceOf(ContentType::class, $header);
        $this->assertEquals('Content-Type', $header->getFieldName());
        $this->assertEquals($expected, $header->getFieldValue());
    }
}

<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header\ContentType;
use Zend\Mail\Header\Exception\InvalidArgumentException;
use Zend\Mail\Header\HeaderInterface;
use Zend\Mail\Header\UnstructuredInterface;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Header\ContentType<extended>
 */
class ContentTypeTest extends TestCase
{
    public function testImplementsHeaderInterface()
    {
        $header = new ContentType();

        $this->assertInstanceOf(UnstructuredInterface::class, $header);
        $this->assertInstanceOf(HeaderInterface::class, $header);
    }

    /**
     * @group 6491
     */
    public function testTrailingSemiColonFromString()
    {
        $contentTypeHeader = ContentType::fromString(
            'Content-Type: multipart/alternative; boundary="Apple-Mail=_1B852F10-F9C6-463D-AADD-CD503A5428DD";'
        );
        $params = $contentTypeHeader->getParameters();
        $this->assertEquals(['boundary' => 'Apple-Mail=_1B852F10-F9C6-463D-AADD-CD503A5428DD'], $params);
    }

    public function testExtractsExtraInformationWithoutBeingConfusedByTrailingSemicolon()
    {
        $header = ContentType::fromString('Content-Type: application/pdf;name="foo.pdf";');
        $this->assertEquals($header->getParameters(), ['name' => 'foo.pdf']);
    }

    /**
     * @dataProvider setTypeProvider
     */
    public function testFromString($type, $parameters, $fieldValue, $expectedToString)
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
    public function testSetType($type, $parameters, $fieldValue, $expectedToString)
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
     * @dataProvider invalidHeaderLinesProvider
     */
    public function testFromStringThrowException($headerLine, $expectedException, $exceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);
        ContentType::fromString($headerLine);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringHandlesContinuations()
    {
        $header = ContentType::fromString("Content-Type: text/html;\r\n level=1");
        $this->assertEquals('text/html', $header->getType());
        $this->assertEquals(['level' => '1'], $header->getParameters());
    }

    /**
     * @dataProvider invalidParametersProvider
     */
    public function testAddParameterThrowException($paramName, $paramValue, $expectedException, $exceptionMessage)
    {
        $header = new ContentType();
        $header->setType('text/html');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($exceptionMessage);
        $header->addParameter($paramName, $paramValue);
    }

    public function setTypeProvider()
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

    public function invalidParametersProvider()
    {
        $invalidArgumentException = InvalidArgumentException::class;

        // @codingStandardsIgnoreStart
        return [
            // Description => [param name, param value, expected exception, exception message contain]

            // @group ZF2015-04
            'invalid name' => ["b\r\na\rr\n", 'baz', $invalidArgumentException, 'parameter name'],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function invalidHeaderLinesProvider()
    {
        $invalidArgumentException = InvalidArgumentException::class;

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
}

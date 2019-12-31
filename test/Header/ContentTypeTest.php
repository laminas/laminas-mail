<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\ContentType;

/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage UnitTests
 * @group      Laminas_Mail
 */
class ContentTypeTest extends \PHPUnit_Framework_TestCase
{

    public function testContentTypeFromStringCreatesValidContentTypeHeader()
    {
        $contentTypeHeader = ContentType::fromString('Content-Type: xxx/yyy');
        $this->assertInstanceOf('Laminas\Mail\Header\HeaderInterface', $contentTypeHeader);
        $this->assertInstanceOf('Laminas\Mail\Header\ContentType', $contentTypeHeader);
    }

    public function testContentTypeGetFieldNameReturnsHeaderName()
    {
        $contentTypeHeader = new ContentType();
        $this->assertEquals('Content-Type', $contentTypeHeader->getFieldName());
    }

    public function testContentTypeGetFieldValueReturnsProperValue()
    {
        $contentTypeHeader = new ContentType();
        $contentTypeHeader->setType('foo/bar');
        $this->assertEquals('foo/bar', $contentTypeHeader->getFieldValue());
    }

    public function testContentTypeToStringReturnsHeaderFormattedString()
    {
        $contentTypeHeader = new ContentType();
        $contentTypeHeader->setType('foo/bar');
        $this->assertEquals("Content-Type: foo/bar", $contentTypeHeader->toString());
    }

    public function testProvidingParametersIntroducesHeaderFolding()
    {
        $header = new ContentType();
        $header->setType('application/x-unit-test');
        $header->addParameter('charset', 'us-ascii');
        $string = $header->toString();

        $this->assertContains("Content-Type: application/x-unit-test;\r\n", $string);
        $this->assertContains(";\r\n charset=\"us-ascii\"", $string);
    }

    public function testExtractsExtraInformationFromContentType()
    {
        $contentTypeHeader = ContentType::fromString(
            'Content-Type: multipart/alternative; boundary="Apple-Mail=_1B852F10-F9C6-463D-AADD-CD503A5428DD"'
        );
        $params = $contentTypeHeader->getParameters();
        $this->assertEquals($params,array('boundary' => 'Apple-Mail=_1B852F10-F9C6-463D-AADD-CD503A5428DD'));
    }

}


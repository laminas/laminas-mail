<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\ContentTransferEncoding;

/**
 * @group      Laminas_Mail
 */
class ContentTransferEncodingTest extends \PHPUnit_Framework_TestCase
{

    public function testContentTransferEncodingFromStringCreatesValidContentTransferEncodingHeader()
    {
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: 7bit');
        $this->assertInstanceOf('Laminas\Mail\Header\HeaderInterface', $contentTransferEncodingHeader);
        $this->assertInstanceOf('Laminas\Mail\Header\ContentTransferEncoding', $contentTransferEncodingHeader);
    }

    public function testContentTransferEncodingFromStringCreateExcaption()
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: 9bit');
    }

    public function testContentTransferEncodingGetFieldNameReturnsHeaderName()
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $this->assertEquals('Content-Transfer-Encoding', $contentTransferEncodingHeader->getFieldName());
    }

    public function testContentTransferEncodingGetFieldValueReturnsProperValue()
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding('7bit');
        $this->assertEquals('7bit', $contentTransferEncodingHeader->getFieldValue());
    }

    public function testContentTransferEncodingHandlesCaseInsensitivity()
    {
        $encoding = new ContentTransferEncoding();
        $encoding->setTransferEncoding('quOtED-printAble');
        $this->assertEquals('quoted-printable', strtolower($encoding->getFieldValue()));
    }

    public function testContentTransferEncodingToStringReturnsHeaderFormattedString()
    {
        $contentTransferEncodingHeader = new ContentTransferEncoding();
        $contentTransferEncodingHeader->setTransferEncoding('8bit');
        $this->assertEquals("Content-Transfer-Encoding: 8bit", $contentTransferEncodingHeader->toString());
    }

    public function testProvidingParametersIntroducesHeaderFolding()
    {
        $header = new ContentTransferEncoding();
        $header->setTransferEncoding('quoted-printable');
        $string = $header->toString();

        $this->assertContains("Content-Transfer-Encoding: quoted-printable", $string);
    }

}

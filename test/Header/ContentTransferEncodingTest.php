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
    public function dataValidEncodings()
    {
        return array(
            array('7bit'),
            array('8bit'),
            array('binary'),
            array('quoted-printable'),
        );
    }

    public function dataInvalidEncodings()
    {
        return array(
            array('9bit'),
            array('x-something'),
        );
    }

    /**
     * @dataProvider dataValidEncodings
     */
    public function testContentTransferEncodingFromStringCreatesValidContentTransferEncodingHeader($encoding)
    {
        $contentTransferEncodingHeader = ContentTransferEncoding::fromString('Content-Transfer-Encoding: '.$encoding);
        $this->assertInstanceOf('Laminas\Mail\Header\HeaderInterface', $contentTransferEncodingHeader);
        $this->assertInstanceOf('Laminas\Mail\Header\ContentTransferEncoding', $contentTransferEncodingHeader);
    }

    /**
     * @dataProvider dataInvalidEncodings
     */
    public function testContentTransferEncodingFromStringRaisesException($encoding)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
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
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        ContentTransferEncoding::fromString('Content-Transfer-Encoding' . chr(32) . ': 8bit');
    }

    public function headerLines()
    {
        return array(
            'newline' => array("Content-Transfer-Encoding: 8bit\n7bit"),
            'cr-lf' => array("Content-Transfer-Encoding: 8bit\r\n7bit"),
            'multiline' => array("Content-Transfer-Encoding: 8bit\r\n7bit\r\nUTF-8"),
        );
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     * @expectedException Laminas\Mail\Header\Exception\InvalidArgumentException
     */
    public function testFromStringRaisesExceptionForInvalidMultilineValues($headerLine)
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        ContentTransferEncoding::fromString($headerLine);
    }

    /**
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionForContinuations()
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException', 'expects');
        ContentTransferEncoding::fromString("Content-Transfer-Encoding: 8bit\r\n 7bit");
    }

    /**
     * @group ZF2015-04
     */
    public function testSetTransferEncodingRaisesExceptionForInvalidValues()
    {
        $header = new ContentTransferEncoding();
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException', 'expects');
        $header->setTransferEncoding("8bit\r\n 7bit");
    }
}

<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\GenericHeader;
use Laminas\Mail\Header\HeaderWrap;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\HeaderWrap<extended>
 */
class HeaderWrapTest extends \PHPUnit_Framework_TestCase
{
    public function testWrapUnstructuredHeaderAscii()
    {
        $string = str_repeat('foobarblahblahblah baz bat', 4);
        $header = $this->getMock('Laminas\Mail\Header\UnstructuredInterface');
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('ASCII'));
        $expected = wordwrap($string, 78, "\r\n ");

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
    }

    /**
     * @group Laminas-258
     */
    public function testWrapUnstructuredHeaderMime()
    {
        $string = str_repeat('foobarblahblahblah baz bat', 3);
        $header = $this->getMock('Laminas\Mail\Header\UnstructuredInterface');
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('UTF-8'));
        $expected = "=?UTF-8?Q?foobarblahblahblah=20baz=20batfoobarblahblahblah=20baz=20?=\r\n"
                    . " =?UTF-8?Q?batfoobarblahblahblah=20baz=20bat?=";

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
        $this->assertEquals($string, iconv_mime_decode($test, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'));
    }

    /**
     * @group Laminas-359
     */
    public function testMimeEncoding()
    {
        $string   = 'Umlauts: ä';
        $expected = '=?UTF-8?Q?Umlauts:=20=C3=A4?=';

        $test = HeaderWrap::mimeEncodeValue($string, 'UTF-8', 78);
        $this->assertEquals($expected, $test);
        $this->assertEquals($string, iconv_mime_decode($test, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'));
    }

    public function testMimeDecoding()
    {
        $expected = str_repeat('foobarblahblahblah baz bat', 3);
        $encoded = "=?UTF-8?Q?foobarblahblahblah=20baz=20batfoobarblahblahblah=20baz=20?=\r\n"
                    . " =?UTF-8?Q?batfoobarblahblahblah=20baz=20bat?=";

        $decoded = HeaderWrap::mimeDecodeValue($encoded);

        $this->assertEquals($expected, $decoded);
    }

    /**
     * Test that fails with HeaderWrap::canBeEncoded at lowest level:
     *   iconv_mime_encode(): Unknown error (7)
     *
     * which can be triggered as:
     *   $header = new GenericHeader($name, $value);
     */
    public function testCanBeEncoded()
    {
        $name = 'Subject';
        $value = "[#77675] New Issue:xxxxxxxxx xxxxxxx xxxxxxxx xxxxxxxxxxxxx xxxxxxxxxx xxxxxxxx, tähtaeg xx.xx, xxxx";
        $encoded = "Subject: =?UTF-8?Q?[#77675]=20New=20Issue:xxxxxxxxx=20xxxxxxx=20xxxxxxxx=20?=\r\n =?UTF-8?Q?xxxxxxxxxxxxx=20xxxxxxxxxx=20xxxxxxxx,=20t=C3=A4htaeg=20xx.xx,=20xxxx?=";
        $res = HeaderWrap::canBeEncoded($value);
        $this->assertTrue($res);

        $header = new GenericHeader($name, $value);
        $res = $header->toString();
        $this->assertEquals($encoded, $res);
    }
}

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
use Zend\Mail\Header\GenericHeader;
use Zend\Mail\Header\HeaderWrap;
use Zend\Mail\Storage;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Header\HeaderWrap<extended>
 */
class HeaderWrapTest extends TestCase
{
    public function testWrapUnstructuredHeaderAscii()
    {
        $string = str_repeat('foobarblahblahblah baz bat', 4);
        $header = $this->createMock('Zend\Mail\Header\UnstructuredInterface');
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('ASCII'));
        $expected = wordwrap($string, 78, "\r\n ");

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
    }

    /**
     * @group ZF2-258
     */
    public function testWrapUnstructuredHeaderMime()
    {
        $string = str_repeat('foobarblahblahblah baz bat', 3);
        $header = $this->createMock('Zend\Mail\Header\UnstructuredInterface');
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
     * @group ZF2-359
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
     * Test that header lazy-loading doesn't break later header access
     * because undocumented behavior in iconv_mime_decode()
     * @see https://github.com/zendframework/zend-mail/pull/187
     */
    public function testMimeDecodeBreakageBug()
    {
        $headerValue = "v=1; a=rsa-sha25; c=relaxed/simple; d=example.org; h=\r\n\tcontent-language:content-type:content-type:in-reply-to";
        $headers = "DKIM-Signature: {$headerValue}";

        $message = new Storage\Message(['headers' => $headers, 'content' => 'irrelevant']);
        $headers = $message->getHeaders();
        // calling toString will lazy load all headers
        // and would break DKIM-Signature header access
        $headers->toString();

        $header = $headers->get('DKIM-Signature');
        $this->assertEquals('v=1; a=rsa-sha25; c=relaxed/simple; d=example.org; h= content-language:content-type:content-type:in-reply-to', $header->getFieldValue());
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
        // @codingStandardsIgnoreStart
        $value   = "[#77675] New Issue:xxxxxxxxx xxxxxxx xxxxxxxx xxxxxxxxxxxxx xxxxxxxxxx xxxxxxxx, tähtaeg xx.xx, xxxx";
        // @codingStandardsIgnoreEnd
        //
        $res = HeaderWrap::canBeEncoded($value);
        $this->assertTrue($res);
    }
}

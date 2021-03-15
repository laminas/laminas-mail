<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\HeaderWrap;
use Laminas\Mail\Header\UnstructuredInterface;
use Laminas\Mail\Storage;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Header\HeaderWrap<extended>
 */
class HeaderWrapTest extends TestCase
{
    public function testWrapUnstructuredHeaderAscii(): void
    {
        $string = str_repeat('foobarblahblahblah baz bat', 4);
        $header = $this->createMock(UnstructuredInterface::class);
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('ASCII'));
        $expected = wordwrap($string, 78, "\r\n ");

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
    }

    /**
     * @see https://zendframework.com/issues/browse/ZF2-258
     */
    public function testWrapUnstructuredHeaderMime(): void
    {
        $string = str_repeat('foobarblahblahblah baz bat', 3);
        $header = $this->createMock(UnstructuredInterface::class);
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('UTF-8'));
        $expected = "=?UTF-8?Q?foobarblahblahblah=20baz=20batfoobarblahblahblah=20baz=20?=\r\n"
                    . " =?UTF-8?Q?batfoobarblahblahblah=20baz=20bat?=";

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
        $this->assertEquals($string, iconv_mime_decode($test, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'));
    }

    public function testWrapUnknownHeaderType(): void
    {
        $header = new \Laminas\Mail\Header\Bcc('test@example.org');
        $value = 'value unmodified by wrap function';
        $this->assertSame($value, HeaderWrap::wrap($value, $header));
    }

    /**
     * @see https://zendframework.com/issues/browse/ZF2-359
     */
    public function testMimeEncoding(): void
    {
        $string   = 'Umlauts: ä';
        $expected = '=?UTF-8?Q?Umlauts:=20=C3=A4?=';

        $test = HeaderWrap::mimeEncodeValue($string, 'UTF-8', 78);
        $this->assertEquals($expected, $test);
        $this->assertEquals($string, iconv_mime_decode($test, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'));
    }

    public function testMimeDecoding(): void
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
    public function testMimeDecodeBreakageBug(): void
    {
        $headerValue = 'v=1; a=rsa-sha25; c=relaxed/simple; d=example.org; h='
            . "\r\n\t" . 'content-language:content-type:content-type:in-reply-to';
        $headers = "DKIM-Signature: {$headerValue}";

        $message = new Storage\Message(['headers' => $headers, 'content' => 'irrelevant']);
        $headers = $message->getHeaders();
        // calling toString will lazy load all headers
        // and would break DKIM-Signature header access
        $headers->toString();

        $header = $headers->get('DKIM-Signature');
        $this->assertEquals(
            'v=1; a=rsa-sha25; c=relaxed/simple; d=example.org;'
            . ' h= content-language:content-type:content-type:in-reply-to',
            $header->getFieldValue()
        );
    }

    /**
     * Test that fails with HeaderWrap::canBeEncoded at lowest level:
     *   iconv_mime_encode(): Unknown error (7)
     *
     * which can be triggered as:
     *   $header = new GenericHeader($name, $value);
     */
    public function testCanBeEncoded(): void
    {
        // @codingStandardsIgnoreStart
        $value   = "[#77675] New Issue:xxxxxxxxx xxxxxxx xxxxxxxx xxxxxxxxxxxxx xxxxxxxxxx xxxxxxxx, tähtaeg xx.xx, xxxx";
        // @codingStandardsIgnoreEnd
        //
        $res = HeaderWrap::canBeEncoded($value);
        $this->assertTrue($res);
    }

    /**
     * @requires extension imap
     */
    public function testMultilineWithMultibyteSplitAcrossCharacter(): void
    {
        $originalValue = 'аф';

        $this->assertEquals(strlen($originalValue), 4);

        $part1 = base64_encode(substr($originalValue, 0, 3));
        $part2 = base64_encode(substr($originalValue, 3));

        $header = '=?utf-8?B?' . $part1 . '?==?utf-8?B?' . $part2 . '?=';

        $this->assertEquals(
            $originalValue,
            HeaderWrap::mimeDecodeValue($header)
        );
    }
}

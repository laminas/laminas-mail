<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Laminas\Mail\Header;

class HeaderLoaderTest extends TestCase
{
    /**
     * @var Header\HeaderLoader
     */
    private $headerLoader;

    public function setUp()
    {
        $this->headerLoader = new Header\HeaderLoader();
    }

    public function provideHeaderNames()
    {
        return [
            'with existing name' => ['to', Header\To::class],
            'with non-existent name' => ['foo', null],
            'with default value' => ['foo', Header\GenericHeader::class, Header\GenericHeader::class],
        ];
    }

    /**
     * @param $name
     * @param $expected
     * @param $default
     * @dataProvider provideHeaderNames
     */
    public function testHeaderIsProperlyLoaded($name, $expected, $default = null)
    {
        $this->assertEquals($expected, $this->headerLoader->get($name, $default));
    }

    public function testHeaderExistenceIsProperlyChecked()
    {
        $this->assertTrue($this->headerLoader->has('to'));
        $this->assertTrue($this->headerLoader->has('To'));
        $this->assertTrue($this->headerLoader->has('Reply_to'));
        $this->assertTrue($this->headerLoader->has('SUBJECT'));
        $this->assertFalse($this->headerLoader->has('foo'));
        $this->assertFalse($this->headerLoader->has('bar'));
    }

    public function testHeaderCanBeAdded()
    {
        $this->assertFalse($this->headerLoader->has('foo'));
        $this->headerLoader->add('foo', Header\GenericHeader::class);
        $this->assertTrue($this->headerLoader->has('foo'));
    }

    public function testHeaderCanBeRemoved()
    {
        $this->assertTrue($this->headerLoader->has('to'));
        $this->headerLoader->remove('to');
        $this->assertFalse($this->headerLoader->has('to'));
    }

    public static function expectedHeaders()
    {
        return [
            ['bcc', Header\Bcc::class],
            ['cc', Header\Cc::class],
            ['contenttype', Header\ContentType::class],
            ['content_type', Header\ContentType::class],
            ['content-type', Header\ContentType::class],
            ['date', Header\Date::class],
            ['from', Header\From::class],
            ['mimeversion', Header\MimeVersion::class],
            ['mime_version', Header\MimeVersion::class],
            ['mime-version', Header\MimeVersion::class],
            ['received', Header\Received::class],
            ['replyto', Header\ReplyTo::class],
            ['reply_to', Header\ReplyTo::class],
            ['reply-to', Header\ReplyTo::class],
            ['sender', Header\Sender::class],
            ['subject', Header\Subject::class],
            ['to', Header\To::class],
        ];
    }

    /**
     * @dataProvider expectedHeaders
     * @param $name
     * @param $class
     */
    public function testDefaultHeadersMapResolvesProperHeader($name, $class)
    {
        $this->assertEquals($class, $this->headerLoader->get($name));
    }
}

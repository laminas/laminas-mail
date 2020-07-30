<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Laminas\Mail\Header;

class HeaderLocatorTest extends TestCase
{
    /**
     * @var Header\HeaderLocator
     */
    private $headerLocator;

    public function setUp()
    {
        $this->headerLocator = new Header\HeaderLocator();
    }

    public function provideHeaderNames()
    {
        return [
            'with existing name'     => ['to', Header\To::class],
            'with non-existent name' => ['foo', null],
            'with default value'     => ['foo', Header\GenericHeader::class, Header\GenericHeader::class],
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
        $this->assertEquals($expected, $this->headerLocator->get($name, $default));
    }

    public function testHeaderExistenceIsProperlyChecked()
    {
        $this->assertTrue($this->headerLocator->has('to'));
        $this->assertTrue($this->headerLocator->has('To'));
        $this->assertTrue($this->headerLocator->has('Reply_to'));
        $this->assertTrue($this->headerLocator->has('SUBJECT'));
        $this->assertFalse($this->headerLocator->has('foo'));
        $this->assertFalse($this->headerLocator->has('bar'));
    }

    public function testHeaderCanBeAdded()
    {
        $this->assertFalse($this->headerLocator->has('foo'));
        $this->headerLocator->add('foo', Header\GenericHeader::class);
        $this->assertTrue($this->headerLocator->has('foo'));
    }

    public function testHeaderCanBeRemoved()
    {
        $this->assertTrue($this->headerLocator->has('to'));
        $this->headerLocator->remove('to');
        $this->assertFalse($this->headerLocator->has('to'));
    }

    public static function expectedHeaders()
    {
        return [
            'bcc'          => ['bcc', Header\Bcc::class],
            'cc'           => ['cc', Header\Cc::class],
            'contenttype'  => ['contenttype', Header\ContentType::class],
            'content_type' => ['content_type', Header\ContentType::class],
            'content-type' => ['content-type', Header\ContentType::class],
            'date'         => ['date', Header\Date::class],
            'from'         => ['from', Header\From::class],
            'mimeversion'  => ['mimeversion', Header\MimeVersion::class],
            'mime_version' => ['mime_version', Header\MimeVersion::class],
            'mime-version' => ['mime-version', Header\MimeVersion::class],
            'received'     => ['received', Header\Received::class],
            'replyto'      => ['replyto', Header\ReplyTo::class],
            'reply_to'     => ['reply_to', Header\ReplyTo::class],
            'reply-to'     => ['reply-to', Header\ReplyTo::class],
            'sender'       => ['sender', Header\Sender::class],
            'subject'      => ['subject', Header\Subject::class],
            'to'           => ['to', Header\To::class],
        ];
    }

    /**
     * @dataProvider expectedHeaders
     * @param string $name
     * @param Header\HeaderInterface $class
     */
    public function testDefaultHeadersMapResolvesProperHeader($name, $class)
    {
        $this->assertEquals($class, $this->headerLocator->get($name));
    }
}

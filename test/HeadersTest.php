<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail;
use Laminas\Mail\Header;

/**
 * @group      Laminas_Mail
 */
class HeadersTest extends \PHPUnit_Framework_TestCase
{
    public function testHeadersImplementsProperClasses()
    {
        $headers = new Mail\Headers();
        $this->assertInstanceOf('Iterator', $headers);
        $this->assertInstanceOf('Countable', $headers);
    }

    public function testHeadersFromStringFactoryCreatesSingleObject()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryHandlesMissingWhitespace()
    {
        $headers = Mail\Headers::fromString("Fake:foo-bar");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryCreatesSingleObjectWithContinuationLine()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar,\r\n      blah-blah");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar,blah-blah', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryCreatesSingleObjectWithHeaderBreakLine()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar\r\n\r\n");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryThrowsExceptionOnMalformedHeaderLine()
    {
        $this->setExpectedException('Laminas\Mail\Exception\RuntimeException', 'does not match');
        Mail\Headers::fromString("Fake = foo-bar\r\n\r\n");
    }

    public function testHeadersFromStringFactoryCreatesMultipleObjects()
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar\r\nAnother-Fake: boo-baz");
        $this->assertEquals(2, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());

        $header = $headers->get('anotherfake');
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
        $this->assertEquals('Another-Fake', $header->getFieldName());
        $this->assertEquals('boo-baz', $header->getFieldValue());
    }

    public function testHeadersFromStringMultiHeaderWillAggregateLazyLoadedHeaders()
    {
        $headers = new Mail\Headers();
        /* @var $pcl \Laminas\Loader\PluginClassLoader */
        $pcl = $headers->getPluginClassLoader();
        $pcl->registerPlugin('foo', 'Laminas\Mail\Header\GenericMultiHeader');
        $headers->addHeaderLine('foo: bar1,bar2,bar3');
        $headers->forceLoading();
        $this->assertEquals(3, $headers->count());
    }

    public function testHeadersHasAndGetWorkProperly()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array($f = new Header\GenericHeader('Foo', 'bar'), new Header\GenericHeader('Baz', 'baz')));
        $this->assertFalse($headers->has('foobar'));
        $this->assertTrue($headers->has('foo'));
        $this->assertTrue($headers->has('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
    }

    public function testHeadersAggregatesHeaderObjects()
    {
        $fakeHeader = new Header\GenericHeader('Fake', 'bar');
        $headers = new Mail\Headers();
        $headers->addHeader($fakeHeader);
        $this->assertEquals(1, $headers->count());
        $this->assertEquals('bar', $headers->get('Fake')->getFieldValue());
    }

    public function testHeadersAggregatesHeaderThroughAddHeader()
    {
        $headers = new Mail\Headers();
        $headers->addHeader(new Header\GenericHeader('Fake', 'bar'));
        $this->assertEquals(1, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Fake'));
    }

    public function testHeadersAggregatesHeaderThroughAddHeaderLine()
    {
        $headers = new Mail\Headers();
        $headers->addHeaderLine('Fake', 'bar');
        $this->assertEquals(1, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Fake'));
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnMissingFieldValue()
    {
        $this->setExpectedException(
            'Laminas\Mail\Header\Exception\InvalidArgumentException',
            'Header must match with the format "name:value"'
        );
        $headers = new Mail\Headers();
        $headers->addHeaderLine('Foo');
    }

    public function testHeadersAggregatesHeadersThroughAddHeaders()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array(new Header\GenericHeader('Foo', 'bar'), new Header\GenericHeader('Baz', 'baz')));
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo: bar', 'Baz: baz'));
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(array(array('Foo' => 'bar'), array('Baz' => 'baz')));
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(array(array('Foo', 'bar'), array('Baz', 'baz')));
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo' => 'bar', 'Baz' => 'baz'));
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());
    }

    public function testHeadersAddHeadersThrowsExceptionOnInvalidArguments()
    {
        $this->setExpectedException('Laminas\Mail\Exception\InvalidArgumentException', 'Expected array or Trav');
        $headers = new Mail\Headers();
        $headers->addHeaders('foo');
    }

    public function testHeadersCanRemoveHeader()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo' => 'bar', 'Baz' => 'baz'));
        $this->assertEquals(2, $headers->count());
        $headers->removeHeader('foo');
        $this->assertEquals(1, $headers->count());
        $this->assertFalse($headers->has('foo'));
        $this->assertTrue($headers->has('baz'));
    }

    public function testRemoveHeaderWithFieldNameWillRemoveAllInstances()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array(array('Foo' => 'foo'), array('Foo' => 'bar'), 'Baz' => 'baz'));
        $this->assertEquals(3, $headers->count());
        $headers->removeHeader('foo');
        $this->assertEquals(1, $headers->count());
        $this->assertFalse($headers->get('foo'));
        $this->assertTrue($headers->has('baz'));
    }

    public function testRemoveHeaderWithInstanceWillRemoveThatInstance()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array(array('Foo' => 'foo'), array('Foo' => 'bar'), 'Baz' => 'baz'));
        $header = $headers->get('foo')->current();
        $this->assertEquals(3, $headers->count());
        $headers->removeHeader($header);
        $this->assertEquals(2, $headers->count());
        $this->assertTrue($headers->has('foo'));
        $this->assertNotSame($header, $headers->get('foo'));
    }

    public function testHeadersCanClearAllHeaders()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo' => 'bar', 'Baz' => 'baz'));
        $this->assertEquals(2, $headers->count());
        $headers->clearHeaders();
        $this->assertEquals(0, $headers->count());
    }

    public function testHeadersCanBeIterated()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo' => 'bar', 'Baz' => 'baz'));
        $iterations = 0;
        foreach ($headers as $index => $header) {
            $iterations++;
            $this->assertInstanceOf('Laminas\Mail\Header\GenericHeader', $header);
            switch ($index) {
                case 0:
                    $this->assertEquals('bar', $header->getFieldValue());
                    break;
                case 1:
                    $this->assertEquals('baz', $header->getFieldValue());
                    break;
                default:
                    $this->fail('Invalid index returned from iterator');
            }
        }
        $this->assertEquals(2, $iterations);
    }

    public function testHeadersCanBeCastToString()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo' => 'bar', 'Baz' => 'baz'));
        $this->assertEquals('Foo: bar' . "\r\n" . 'Baz: baz' . "\r\n", $headers->toString());
    }

    public function testHeadersCanBeCastToArray()
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(array('Foo' => 'bar', 'Baz' => 'baz'));
        $this->assertEquals(array('Foo' => 'bar', 'Baz' => 'baz'), $headers->toArray());
    }

    public function testCastingToArrayReturnsMultiHeadersAsArrays()
    {
        $headers = new Mail\Headers();
        $received1 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\nby framework (Postfix) with ESMTP id BBBBBBBBBBB\r\nfor <laminas@framework>; Mon, 21 Nov 2011 12:50:27 -0600 (CST)");
        $received2 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\nby framework (Postfix) with ESMTP id AAAAAAAAAAA\r\nfor <laminas@framework>; Mon, 21 Nov 2011 12:50:29 -0600 (CST)");
        $headers->addHeader($received1);
        $headers->addHeader($received2);
        $array   = $headers->toArray();
        $expected = array(
            'Received' => array(
                $received1->getFieldValue(),
                $received2->getFieldValue(),
            ),
        );
        $this->assertEquals($expected, $array);
    }

    public function testCastingToStringReturnsAllMultiHeaderValues()
    {
        $headers = new Mail\Headers();
        $received1 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\nby framework (Postfix) with ESMTP id BBBBBBBBBBB\r\nfor <laminas@framework>; Mon, 21 Nov 2011 12:50:27 -0600 (CST)");
        $received2 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\nby framework (Postfix) with ESMTP id AAAAAAAAAAA\r\nfor <laminas@framework>; Mon, 21 Nov 2011 12:50:29 -0600 (CST)");
        $headers->addHeader($received1);
        $headers->addHeader($received2);
        $string  = $headers->toString();
        $expected = array(
            'Received: ' . $received1->getFieldValue(),
            'Received: ' . $received2->getFieldValue(),
        );
        $expected = implode("\r\n", $expected) . "\r\n";
        $this->assertEquals($expected, $string);
    }

    public static function expectedHeaders()
    {
        return array(
            array('bcc', 'Laminas\Mail\Header\Bcc'),
            array('cc', 'Laminas\Mail\Header\Cc'),
            array('contenttype', 'Laminas\Mail\Header\ContentType'),
            array('content_type', 'Laminas\Mail\Header\ContentType'),
            array('content-type', 'Laminas\Mail\Header\ContentType'),
            array('date', 'Laminas\Mail\Header\Date'),
            array('from', 'Laminas\Mail\Header\From'),
            array('mimeversion', 'Laminas\Mail\Header\MimeVersion'),
            array('mime_version', 'Laminas\Mail\Header\MimeVersion'),
            array('mime-version', 'Laminas\Mail\Header\MimeVersion'),
            array('received', 'Laminas\Mail\Header\Received'),
            array('replyto', 'Laminas\Mail\Header\ReplyTo'),
            array('reply_to', 'Laminas\Mail\Header\ReplyTo'),
            array('reply-to', 'Laminas\Mail\Header\ReplyTo'),
            array('sender', 'Laminas\Mail\Header\Sender'),
            array('subject', 'Laminas\Mail\Header\Subject'),
            array('to', 'Laminas\Mail\Header\To'),
        );
    }

    /**
     * @dataProvider expectedHeaders
     */
    public function testDefaultPluginLoaderIsSeededWithHeaders($plugin, $class)
    {
        $headers = new Mail\Headers();
        $loader  = $headers->getPluginClassLoader();
        $test    = $loader->load($plugin);
        $this->assertEquals($class, $test);
    }

    public function testClone()
    {
        $headers = new Mail\Headers();
        $headers->addHeader(new Header\Bcc());
        $headers2 = clone($headers);
        $this->assertEquals($headers, $headers2);
        $headers2->removeHeader('Bcc');
        $this->assertTrue($headers->has('Bcc'));
        $this->assertFalse($headers2->has('Bcc'));
    }
}

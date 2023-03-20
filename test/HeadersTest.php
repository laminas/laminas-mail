<?php

namespace LaminasTest\Mail;

use ArrayIterator;
use Countable;
use ErrorException;
use Iterator;
use Laminas\Loader\PluginClassLocator;
use Laminas\Mail;
use Laminas\Mail\Header;
use Laminas\Mail\Header\Exception;
use Laminas\Mail\Header\GenericHeader;
use Laminas\Mail\Header\GenericMultiHeader;
use PHPUnit\Framework\TestCase;
use stdClass;

use function implode;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

/**
 * @covers \Laminas\Mail\Headers<extended>
 */
class HeadersTest extends TestCase
{
    /** @var null|callable */
    private $originalErrorHandler;

    public function tearDown(): void
    {
        $this->restoreErrorHandler();
    }

    /**
     * Handle deprecation errors and throw them.
     *
     * This is necessary as we are silencing the trigger_error call. This is
     * done so that there is no impact on users, but, if they are logging errors
     * using an error handler, they will see them in their logs. As such, we
     * cannot rely on PHPUnit to catch them, and need to instead handle them
     * ourselves in a similar fashion.
     */
    public function setDeprecationErrorHandler(): void
    {
        $this->originalErrorHandler = set_error_handler(
            static function (int $errno, string $errstr, string $errfile, int $errline): void {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            },
            E_USER_DEPRECATED
        );
    }

    public function restoreErrorHandler(): void
    {
        if (null !== $this->originalErrorHandler) {
            return;
        }

        restore_error_handler();
        $this->originalErrorHandler = null;
    }

    public function testHeadersImplementsProperClasses(): void
    {
        $headers = new Mail\Headers();
        $this->assertInstanceOf(Iterator::class, $headers);
        $this->assertInstanceOf(Countable::class, $headers);
    }

    public function testHeadersFromStringFactoryCreatesSingleObject(): void
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf(GenericHeader::class, $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryHandlesMissingWhitespace(): void
    {
        $headers = Mail\Headers::fromString("Fake:foo-bar");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf(GenericHeader::class, $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    /**
     * @group 6657
     */
    public function testHeadersFromStringFactoryCreatesSingleObjectWithContinuationLine(): void
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar,\r\n      blah-blah");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf(GenericHeader::class, $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar, blah-blah', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryCreatesSingleObjectWithHeaderBreakLine(): void
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar\r\n\r\n");
        $this->assertEquals(1, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf(GenericHeader::class, $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());
    }

    public function testHeadersFromStringFactoryThrowsExceptionOnMalformedHeaderLine(): void
    {
        $this->expectException(Mail\Exception\RuntimeException::class);
        $this->expectExceptionMessage('does not match');
        Mail\Headers::fromString("Fake = foo-bar\r\n\r\n");
    }

    public function testHeadersFromStringFactoryThrowsExceptionOnMalformedHeaderLines(): void
    {
        $this->expectException(Mail\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Malformed header detected');
        Mail\Headers::fromString("Fake: foo-bar\r\n\r\n\r\n\r\nAnother-Fake: boo-baz");
    }

    public function testHeadersFromStringFactoryCreatesMultipleObjects(): void
    {
        $headers = Mail\Headers::fromString("Fake: foo-bar\r\nAnother-Fake: boo-baz");
        $this->assertEquals(2, $headers->count());

        $header = $headers->get('fake');
        $this->assertInstanceOf(GenericHeader::class, $header);
        $this->assertEquals('Fake', $header->getFieldName());
        $this->assertEquals('foo-bar', $header->getFieldValue());

        $header = $headers->get('anotherfake');
        $this->assertInstanceOf(GenericHeader::class, $header);
        $this->assertEquals('Another-Fake', $header->getFieldName());
        $this->assertEquals('boo-baz', $header->getFieldValue());
    }

    public function testHeadersFromStringMultiHeaderWillAggregateLazyLoadedHeaders(): void
    {
        $headers = new Mail\Headers();
        $loader  = $headers->getHeaderLocator();
        $loader->add('foo', GenericMultiHeader::class);
        $headers->addHeaderLine('foo: bar1,bar2,bar3');
        $headers->forceLoading();
        $this->assertEquals(3, $headers->count());
    }

    public function testHeadersHasAndGetWorkProperly(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([
            $f = new Header\GenericHeader('Foo', 'bar'),
            new Header\GenericHeader('Baz', 'baz'),
        ]);
        $this->assertFalse($headers->has('foobar'));
        $this->assertTrue($headers->has('foo'));
        $this->assertTrue($headers->has('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
    }

    public function testHeadersAggregatesHeaderObjects(): void
    {
        $fakeHeader = new Header\GenericHeader('Fake', 'bar');
        $headers    = new Mail\Headers();
        $headers->addHeader($fakeHeader);
        $this->assertEquals(1, $headers->count());
        $this->assertEquals('bar', $headers->get('Fake')->getFieldValue());
    }

    public function testHeadersAggregatesHeaderThroughAddHeader(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeader(new Header\GenericHeader('Fake', 'bar'));
        $this->assertEquals(1, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Fake'));
    }

    public function testHeadersAggregatesHeaderThroughAddHeaderLine(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaderLine('Fake', 'bar');
        $this->assertEquals(1, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Fake'));
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnMissingFieldValue(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header must match with the format "name:value"');
        $headers = new Mail\Headers();
        $headers->addHeaderLine('Foo');
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnInvalidFieldNull(): void
    {
        $headers = new Mail\Headers();

        $this->expectException(Mail\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects its first argument to be a string');
        $headers->addHeaderLine(null);
    }

    public function testHeadersAddHeaderLineThrowsExceptionOnInvalidFieldObject(): void
    {
        $headers = new Mail\Headers();
        $object  = new stdClass();

        $this->expectException(Mail\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects its first argument to be a string');
        $headers->addHeaderLine($object);
    }

    public function testHeadersAggregatesHeadersThroughAddHeaders(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([new Header\GenericHeader('Foo', 'bar'), new Header\GenericHeader('Baz', 'baz')]);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo: bar', 'Baz: baz']);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo' => 'bar'], ['Baz' => 'baz']]);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo', 'bar'], ['Baz', 'baz']]);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());

        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(2, $headers->count());
        $this->assertInstanceOf(GenericHeader::class, $headers->get('Foo'));
        $this->assertEquals('bar', $headers->get('foo')->getFieldValue());
        $this->assertEquals('baz', $headers->get('baz')->getFieldValue());
    }

    public function testHeadersAddHeadersThrowsExceptionOnInvalidArguments(): void
    {
        $this->expectException(Mail\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected array or Traversable');
        $headers = new Mail\Headers();
        $headers->addHeaders('foo');
    }

    public function testHeadersCanRemoveHeader(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(2, $headers->count());
        $headers->removeHeader('foo');
        $this->assertEquals(1, $headers->count());
        $this->assertFalse($headers->has('foo'));
        $this->assertTrue($headers->has('baz'));
    }

    public function testRemoveHeaderWithFieldNameWillRemoveAllInstances(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo' => 'foo'], ['Foo' => 'bar'], 'Baz' => 'baz']);
        $this->assertEquals(3, $headers->count());
        $headers->removeHeader('foo');
        $this->assertEquals(1, $headers->count());
        $this->assertFalse($headers->get('foo'));
        $this->assertTrue($headers->has('baz'));
    }

    public function testRemoveHeaderWithInstanceWillRemoveThatInstance(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders([['Foo' => 'foo'], ['Foo' => 'bar'], 'Baz' => 'baz']);
        $header = $headers->get('foo')->current();
        $this->assertEquals(3, $headers->count());
        $headers->removeHeader($header);
        $this->assertEquals(2, $headers->count());
        $this->assertTrue($headers->has('foo'));
        $this->assertNotSame($header, $headers->get('foo'));
    }

    public function testRemoveHeaderWhenEmpty(): void
    {
        $headers = new Mail\Headers();
        $this->assertFalse($headers->removeHeader(''));
    }

    public function testHeadersCanClearAllHeaders(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(2, $headers->count());
        $headers->clearHeaders();
        $this->assertEquals(0, $headers->count());
    }

    public function testHeadersCanBeIterated(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $iterations = 0;
        foreach ($headers as $index => $header) {
            $iterations++;
            $this->assertInstanceOf(GenericHeader::class, $header);
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

    public function testHeadersCanBeCastToString(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals('Foo: bar' . "\r\n" . 'Baz: baz' . "\r\n", $headers->toString());
    }

    public function testHeadersCanBeCastToArray(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaders(['Foo' => 'bar', 'Baz' => 'baz']);
        $this->assertEquals(['Foo' => 'bar', 'Baz' => 'baz'], $headers->toArray());
    }

    public function testCastingToArrayReturnsMultiHeadersAsArrays(): void
    {
        $headers = new Mail\Headers();

        // @codingStandardsIgnoreStart
        $received1 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id BBBBBBBBBBB\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:27 -0600 (CST)");
        $received2 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id AAAAAAAAAAA\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:29 -0600 (CST)");
        // @codingStandardsIgnoreEnd

        $headers->addHeader($received1);
        $headers->addHeader($received2);
        $array    = $headers->toArray();
        $expected = [
            'Received' => [
                $received1->getFieldValue(),
                $received2->getFieldValue(),
            ],
        ];
        $this->assertEquals($expected, $array);
    }

    public function testCastingToStringReturnsAllMultiHeaderValues(): void
    {
        $headers = new Mail\Headers();

        // @codingStandardsIgnoreStart
        $received1 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id BBBBBBBBBBB\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:27 -0600 (CST)");
        $received2 = Header\Received::fromString("Received: from framework (localhost [127.0.0.1])\r\n by framework (Postfix) with ESMTP id AAAAAAAAAAA\r\n for <laminas@framework>; Mon, 21 Nov 2011 12:50:29 -0600 (CST)");
        // @codingStandardsIgnoreEnd

        $headers->addHeader($received1);
        $headers->addHeader($received2);
        $string   = $headers->toString();
        $expected = [
            'Received: ' . $received1->getFieldValue(),
            'Received: ' . $received2->getFieldValue(),
        ];
        $expected = implode("\r\n", $expected) . "\r\n";
        $this->assertEquals($expected, $string);
    }

    public function testGetReturnsArrayIterator(): void
    {
        $headers  = new Mail\Headers();
        $received = Header\Received::fromString('Received: from framework (localhost [127.0.0.1])');
        $headers->addHeader($received);

        $return = $headers->get('Received');
        $this->assertSame(ArrayIterator::class, $return::class);
    }

    /**
     * Test that toArray can take format parameter
     *
     * @see https://github.com/zendframework/zend-mail/pull/61
     */
    public function testToArrayFormatRaw(): void
    {
        $rawSubject = '=?ISO-8859-2?Q?PD=3A_My=3A_Go=B3?= =?ISO-8859-2?Q?blahblah?=';
        $headers    = new Mail\Headers();
        $subject    = Header\Subject::fromString("Subject: $rawSubject");
        $headers->addHeader($subject);
        // default
        $array    = $headers->toArray(Header\HeaderInterface::FORMAT_RAW);
        $expected = [
            'Subject' => 'PD: My: Gołblahblah',
        ];
        $this->assertEquals($expected, $array);
    }

    /**
     * Test that toArray can take format parameter
     *
     * @see https://github.com/zendframework/zend-mail/pull/61
     */
    public function testToArrayFormatEncoded(): void
    {
        $rawSubject = '=?ISO-8859-2?Q?PD=3A_My=3A_Go=B3?= =?ISO-8859-2?Q?blahblah?=';
        $headers    = new Mail\Headers();
        $subject    = Header\Subject::fromString("Subject: $rawSubject");
        $headers->addHeader($subject);

        // encoded
        $array    = $headers->toArray(Header\HeaderInterface::FORMAT_ENCODED);
        $expected = [
            'Subject' => '=?UTF-8?Q?PD:=20My:=20Go=C5=82blahblah?=',
        ];
        $this->assertEquals($expected, $array);
    }

    public function testClone(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeader(new Header\Bcc());
        $headers2 = clone $headers;
        $this->assertEquals($headers, $headers2);
        $headers2->removeHeader('Bcc');
        $this->assertTrue($headers->has('Bcc'));
        $this->assertFalse($headers2->has('Bcc'));
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackFromString(): void
    {
        $this->expectException(Mail\Exception\RuntimeException::class);
        Mail\Headers::fromString("Fake: foo-bar\r\n\r\nevilContent");
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeaderLineSingle(): void
    {
        $headers = new Mail\Headers();
        $this->expectException(Exception\InvalidArgumentException::class);
        $headers->addHeaderLine("Fake: foo-bar\r\n\r\nevilContent");
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeaderLineWithValue(): void
    {
        $headers = new Mail\Headers();
        $this->expectException(Exception\InvalidArgumentException::class);
        $headers->addHeaderLine('Fake', "foo-bar\r\n\r\nevilContent");
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeaderLineMultiple(): void
    {
        $headers = new Mail\Headers();
        $this->expectException(Exception\InvalidArgumentException::class);
        $headers->addHeaderLine('Fake', ["foo-bar\r\n\r\nevilContent"]);
        $headers->forceLoading();
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeadersSingle(): void
    {
        $headers = new Mail\Headers();
        $this->expectException(Exception\InvalidArgumentException::class);
        $headers->addHeaders(["Fake: foo-bar\r\n\r\nevilContent"]);
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeadersWithValue(): void
    {
        $headers = new Mail\Headers();
        $this->expectException(Exception\InvalidArgumentException::class);
        $headers->addHeaders(['Fake' => "foo-bar\r\n\r\nevilContent"]);
    }

    /**
     * @group ZF2015-04
     */
    public function testHeaderCrLfAttackAddHeadersMultiple(): void
    {
        $headers = new Mail\Headers();
        $this->expectException(Exception\InvalidArgumentException::class);
        $headers->addHeaders(['Fake' => ["foo-bar\r\n\r\nevilContent"]]);
        $headers->forceLoading();
    }

    public function testAddressListGetEncodedFieldValueWithUtf8Domain(): void
    {
        $to = new Header\To();
        $to->setEncoding('UTF-8');
        $to->getAddressList()->add('local-part@ä-umlaut.de');
        $encodedValue = $to->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals('local-part@xn---umlaut-4wa.de', $encodedValue);
    }

    /**
     * Test ">" being part of email "comment".
     *
     * Example Email-header:
     *  "Foo <bar" foo.bar@test.com
     *
     * Description:
     *   The example email-header should be valid
     *   according to https://tools.ietf.org/html/rfc2822#section-3.4
     *   but the function AdressList.php/addFromString matches it incorrect.
     *   The result has the following form:
     *    "bar <foo.bar@test.com"
     *   This is clearly not a valid adress and therefore causes
     *   exceptions in the following code
     *
     * @see https://github.com/zendframework/zend-mail/issues/127
     */
    public function testEmailNameParser(): void
    {
        $to = Header\To::fromString('To: "=?UTF-8?Q?=C3=B5lu?= <bar" <foo.bar@test.com>');

        $address = $to->getAddressList()->get('foo.bar@test.com');
        $this->assertEquals('õlu <bar', $address->getName());
        $this->assertEquals('foo.bar@test.com', $address->getEmail());

        $encodedValue = $to->getFieldValue(Header\HeaderInterface::FORMAT_ENCODED);
        $this->assertEquals('=?UTF-8?Q?=C3=B5lu=20<bar?= <foo.bar@test.com>', $encodedValue);

        $encodedValue = $to->getFieldValue(Header\HeaderInterface::FORMAT_RAW);
        // FIXME: shouldn't the "name" part be in quotes?
        $this->assertEquals('õlu <bar <foo.bar@test.com>', $encodedValue);
    }

    public function testDefaultEncoding(): void
    {
        $headers = new Mail\Headers();
        $this->assertSame('ASCII', $headers->getEncoding());
    }

    public function testSetEncodingNoHeaders(): void
    {
        $headers = new Mail\Headers();
        $headers->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $headers->getEncoding());
    }

    public function testSetEncodingWithHeaders(): void
    {
        $headers = new Mail\Headers();
        $headers->addHeaderLine('To: test@example.com');
        $headers->addHeaderLine('Cc: tester@example.org');

        $headers->setEncoding('UTF-8');
        $this->assertSame('UTF-8', $headers->getEncoding());
    }

    public function testAddHeaderCallsSetEncoding(): void
    {
        $headers = new Mail\Headers();
        $headers->setEncoding('UTF-8');

        $subject = new Header\Subject();
        // default to ASCII
        $this->assertSame('ASCII', $subject->getEncoding());

        $headers->addHeader($subject);
        // now UTF-8 via addHeader() call
        $this->assertSame('UTF-8', $subject->getEncoding());
    }

    /**
     * @todo Remove for 3.0.0
     */
    public function testGetPluginClassLoaderEmitsDeprecationNotice(): void
    {
        $this->setDeprecationErrorHandler();
        $headers = new Mail\Headers();

        $this->expectExceptionMessage('getPluginClassLoader is deprecated');
        $headers->getPluginClassLoader();
    }

    /**
     * @todo Remove for 3.0.0
     */
    public function testSetPluginClassLoaderEmitsDeprecationNotice(): void
    {
        $this->setDeprecationErrorHandler();
        $headers = new Mail\Headers();
        $loader  = $this->createMock(PluginClassLocator::class);

        $this->expectExceptionMessage('deprecated');
        $headers->setPluginClassLoader($loader);
    }

    public function testGetHeaderLocatorReturnsHeaderLocatorInstanceByDefault(): void
    {
        $headers = new Mail\Headers();
        $locator = $headers->getHeaderLocator();
        $this->assertInstanceOf(Mail\Header\HeaderLocator::class, $locator);
    }

    public function testCanInjectAlternateHeaderLocatorInstance(): void
    {
        $headers = new Mail\Headers();
        $locator = $this->createMock(Mail\Header\HeaderLocatorInterface::class);

        $headers->setHeaderLocator($locator);
        $this->assertSame($locator, $headers->getHeaderLocator());
    }

    public function testStrictKeyComparisonInHas(): void
    {
        $headers = Mail\Headers::fromString("000: foo-bar");
        $this->assertFalse($headers->has('0'));
    }

    public function testStrictKeyComparisonInGet(): void
    {
        $headers = Mail\Headers::fromString("000: foo-bar");
        $this->assertFalse($headers->get('0'));
    }

    /** @group issue-175 */
    public function testUndefinedDefineMissingIntlExtensionConstants(): void
    {
        $headers = Mail\Headers::fromString('To: foo@example.com')->setEncoding('UTF-8');

        self::assertSame(['To' => 'foo@example.com'], $headers->toArray());

        self::assertSame("To: foo@example.com" . Mail\Headers::EOL, $headers->toString());
    }
}

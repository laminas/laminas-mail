<?php

namespace LaminasTest\Mail\Storage;

use Exception as GeneralException;
use Laminas\Mail\Address\AddressInterface;
use Laminas\Mail\Exception as MailException;
use Laminas\Mail\Header\HeaderInterface;
use Laminas\Mail\Header\To;
use Laminas\Mail\Headers;
use Laminas\Mail\Storage;
use Laminas\Mail\Storage\Exception;
use Laminas\Mail\Storage\Message;
use Laminas\Mime;
use Laminas\Mime\Exception as MimeException;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

use function file_get_contents;
use function fopen;
use function implode;
use function substr;
use function var_export;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Storage\Message<extended>
 * @covers Laminas\Mail\Headers<extended>
 */
class MessageTest extends TestCase
{
    /** @var string */
    protected $file;
    /** @var string */
    protected $file2;

    public function setUp(): void
    {
        $this->file  = __DIR__ . '/../_files/mail.eml';
        $this->file2 = __DIR__ . '/../_files/mail_multi_to.eml';
    }

    public function testInvalidFile(): void
    {
        $this->expectException(GeneralException::class);
        new Message(['file' => '/this/file/does/not/exists']);
    }

    /**
     * @dataProvider filesProvider
     */
    public function testIsMultipart(array $params): void
    {
        $message = new Message($params);
        $this->assertTrue($message->isMultipart());
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetHeader(array $params): void
    {
        $message = new Message($params);
        $this->assertEquals($message->subject, 'multipart');
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetToHeader(array $params): void
    {
        $message = new Message($params);
        /** @var HeaderInterface $toHeader */
        $toHeader = $message->getHeader('To');
        $this->assertEquals('foo@example.com', $toHeader->getFieldValue());
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetDecodedHeader(array $params): void
    {
        $message = new Message($params);
        $this->assertEquals('Peter Müller <peter-mueller@example.com>', $message->from);
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetHeaderAsArray(array $params): void
    {
        $message = new Message($params);
        $this->assertEquals(['multipart'], $message->getHeader('subject', 'array'), 'getHeader() value not match');
    }

    public function testGetFirstPart(): void
    {
        $message = new Message(['file' => $this->file]);

        $this->assertEquals(substr($message->getPart(1)->getContent(), 0, 14), 'The first part');
    }

    public function testGetFirstPartTwice(): void
    {
        $message = new Message(['file' => $this->file]);

        $message->getPart(1);
        $this->assertEquals(substr($message->getPart(1)->getContent(), 0, 14), 'The first part');
    }

    public function testGetWrongPart(): void
    {
        $this->expectException(GeneralException::class);
        $message = new Message(['file' => $this->file]);
        $message->getPart(-1);
    }

    public function testNoHeaderMessage(): void
    {
        $message = new Message(['file' => __FILE__]);

        $this->assertEquals(substr($message->getContent(), 0, 5), '<?php');

        $raw     = file_get_contents(__FILE__);
        $raw     = "\t" . $raw;
        $message = new Message(['raw' => $raw]);

        $this->assertEquals(substr($message->getContent(), 0, 6), "\t<?php");
    }

    /**
     * after pull/86 messageId gets double braces
     *
     * @see https://github.com/zendframework/zend-mail/pull/86
     * @see https://github.com/zendframework/zend-mail/pull/156
     */
    public function testMessageIdHeader(): void
    {
        $message   = new Message(['file' => $this->file]);
        $messageId = $message->messageId;
        $this->assertEquals('<CALTvGe4_oYgf9WsYgauv7qXh2-6=KbPLExmJNG7fCs9B=1nOYg@mail.example.com>', $messageId);
    }

    public function testMultipleHeader(): void
    {
        $raw     = file_get_contents($this->file);
        $raw     = "sUBject: test\r\nSubJect: test2\r\n" . $raw;
        $message = new Message(['raw' => $raw]);

        $this->assertEquals(
            'test' . Mime\Mime::LINEEND . 'test2' . Mime\Mime::LINEEND . 'multipart',
            $message->getHeader('subject', 'string')
        );

        $this->assertEquals(
            ['test', 'test2', 'multipart'],
            $message->getHeader('subject', 'array')
        );
    }

    public function testAllowWhitespaceInEmptySingleLineHeader(): void
    {
        $src     = "From: user@example.com\n"
            . "To: userpal@example.net\n"
            . "Subject: This is your reminder\n  \n  about the football game tonight\n"
            . "Date: Wed, 20 Oct 2010 20:53:35 -0400\n\n"
            . "Don't forget to meet us for the tailgate party!\n";
        $message = new Message(['raw' => $src]);

        $this->assertEquals(
            'This is your reminder about the football game tonight',
            $message->getHeader('subject', 'string')
        );
    }

    public function testAllowWhitespaceInEmptyMultiLineHeader(): void
    {
        $src     = "From: user@example.com\nTo: userpal@example.net\n"
            . "Subject: This is your reminder\n  \n \n"
            . "  about the football game tonight\n"
            . "Date: Wed, 20 Oct 2010 20:53:35 -0400\n\n"
            . "Don't forget to meet us for the tailgate party!\n";
        $message = new Message(['raw' => $src]);

        $this->assertEquals(
            'This is your reminder about the football game tonight',
            $message->getHeader('subject', 'string')
        );
    }

    public function testContentTypeDecode(): void
    {
        $message = new Message(['file' => $this->file]);

        $this->assertEquals(
            Mime\Decode::splitContentType($message->ContentType),
            ['type' => 'multipart/alternative', 'boundary' => 'crazy-multipart']
        );
    }

    public function testSplitEmptyMessage(): void
    {
        $this->assertEquals(Mime\Decode::splitMessageStruct('', 'xxx'), null);
    }

    public function testSplitInvalidMessage(): void
    {
        $this->expectException(MimeException\ExceptionInterface::class);
        Mime\Decode::splitMessageStruct("--xxx\n", 'xxx');
    }

    public function testInvalidMailHandler(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Message(['handler' => 1]);
    }

    public function testMissingId(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $mail = new Storage\Mbox(['filename' => __DIR__ . '/../_files/test.mbox/INBOX']);
        new Message(['handler' => $mail]);
    }

    public function testIterator(): void
    {
        $message = new Message(['file' => $this->file]);
        foreach (new RecursiveIteratorIterator($message) as $num => $part) {
            if ($num == 1) {
                // explicit call of __toString() needed for PHP < 5.2
                $this->assertEquals(substr($part->__toString(), 0, 14), 'The first part');
            }
        }
        $this->assertEquals($part->contentType, 'text/x-vertical');
    }

    public function testDecodeString(): void
    {
        $is = Mime\Decode::decodeQuotedPrintable('=?UTF-8?Q?"Peter M=C3=BCller"?= <peter-mueller@example.com>');
        $this->assertEquals('"Peter Müller" <peter-mueller@example.com>', $is);
    }

    public function testSplitHeader(): void
    {
        $header = 'foo; x=y; y="x"';
        $this->assertEquals(Mime\Decode::splitHeaderField($header), ['foo', 'x' => 'y', 'y' => 'x']);
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'x'), 'y');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'y'), 'x');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'foo', 'foo'), 'foo');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'foo'), null);
    }

    public function testSplitInvalidHeader(): void
    {
        $this->expectException(MimeException\ExceptionInterface::class);
        $header = '';
        Mime\Decode::splitHeaderField($header);
    }

    public function testSplitMessage(): void
    {
        $header   = 'Test: test';
        $body     = 'body';
        $newlines = ["\r\n", "\n\r", "\n", "\r"];

        $decodedBody    = null; // "Declare" variable before first "read" usage to avoid IDEs warning
        $decodedHeaders = null; // "Declare" variable before first "read" usage to avoid IDEs warning

        foreach ($newlines as $contentEol) {
            foreach ($newlines as $decodeEol) {
                $content = $header . $contentEol . $contentEol . $body;
                Mime\Decode::splitMessage($content, $decodedHeaders, $decodedBody, $decodeEol);
                $this->assertEquals(['Test' => 'test'], $decodedHeaders->toArray());
                $this->assertEquals($body, $decodedBody);
            }
        }
    }

    public function testTopLines(): void
    {
        $message = new Message(['headers' => file_get_contents($this->file)]);
        $this->assertStringStartsWith('multipart message', $message->getToplines());
    }

    public function testNoContent(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $message = new Message(['raw' => 'Subject: test']);
        $message->getContent();
    }

    public function testEmptyHeader(): void
    {
        $message = new Message([]);
        $this->assertEquals([], $message->getHeaders()->toArray());

        $message = new Message([]);

        $this->expectException(MailException\InvalidArgumentException::class);
        $message->subject;
    }

    public function testWrongHeaderType(): void
    {
        // @codingStandardsIgnoreStart
        $badMessage = unserialize(
            "O:28:\"Laminas\Mail\Storage\Message\":9:{s:8:\"\x00*\x00flags\";a:0:{}s:10:\"\x00*\x00headers\";s:16:\"Yellow submarine\";s:10:\"\x00*\x00content\";N;s:11:\"\x00*\x00topLines\";s:0:\"\";s:8:\"\x00*\x00parts\";a:0:{}s:13:\"\x00*\x00countParts\";N;s:15:\"\x00*\x00iterationPos\";i:1;s:7:\"\x00*\x00mail\";N;s:13:\"\x00*\x00messageNum\";i:0;}"
        );
        // @codingStandardsIgnoreEnd

        $this->expectException(MailException\RuntimeException::class);
        $badMessage->getHeaders();
    }

    public function testEmptyBody(): void
    {
        $message = new Message([]);
        $part    = null;
        try {
            $part = $message->getPart(1);
        } catch (Exception\RuntimeException) {
            // ok
        }
        if ($part) {
            $this->fail('no exception raised while getting part from empty message');
        }

        $message = new Message([]);
        $this->assertEquals(0, $message->countParts());
    }

    /**
     * @see https://zendframework.com/issues/browse/ZF-5209
     */
    public function testCheckingHasHeaderFunctionality(): void
    {
        $message = new Message(['headers' => ['subject' => 'foo']]);

        $this->assertTrue($message->getHeaders()->has('subject'));
        $this->assertTrue(isset($message->subject));
        $this->assertTrue($message->getHeaders()->has('SuBject'));
        $this->assertTrue(isset($message->suBjeCt));
        $this->assertFalse($message->getHeaders()->has('From'));
    }

    public function testWrongMultipart(): void
    {
        $this->expectException(Exception\RuntimeException::class);
        $message = new Message(['raw' => "Content-Type: multipart/mixed\r\n\r\ncontent"]);
        $message->getPart(1);
    }

    public function testLateFetch(): void
    {
        $mail = new Storage\Mbox(['filename' => __DIR__ . '/../_files/test.mbox/INBOX']);

        $message = new Message(['handler' => $mail, 'id' => 5]);
        $this->assertEquals($message->countParts(), 2);
        $this->assertEquals($message->countParts(), 2);

        $message = new Message(['handler' => $mail, 'id' => 5]);
        $this->assertEquals($message->subject, 'multipart');

        $message = new Message(['handler' => $mail, 'id' => 5]);
        $this->assertStringStartsWith('multipart message', $message->getContent());
    }

    public function testManualIterator(): void
    {
        $message = new Message(['file' => $this->file]);

        $this->assertTrue($message->valid());
        $this->assertEquals($message->getChildren(), $message->current());
        $this->assertEquals($message->key(), 1);

        $message->next();
        $this->assertTrue($message->valid());
        $this->assertEquals($message->getChildren(), $message->current());
        $this->assertEquals($message->key(), 2);

        $message->next();
        $this->assertFalse($message->valid());

        $message->rewind();
        $this->assertTrue($message->valid());
        $this->assertEquals($message->getChildren(), $message->current());
        $this->assertEquals($message->key(), 1);
    }

    public function testMessageFlagsAreSet(): void
    {
        $origFlags = [
            'foo' => 'bar',
            'baz' => 'bat',
        ];
        $message   = new Message(['flags' => $origFlags]);

        $messageFlags = $message->getFlags();
        $this->assertTrue($message->hasFlag('bar'), var_export($messageFlags, true));
        $this->assertTrue($message->hasFlag('bat'), var_export($messageFlags, true));
        $this->assertEquals(['bar' => 'bar', 'bat' => 'bat'], $messageFlags);
    }

    public function testGetHeaderFieldSingle(): void
    {
        $message = new Message(['file' => $this->file]);
        $this->assertEquals($message->getHeaderField('subject'), 'multipart');
    }

    public function testGetHeaderFieldDefault(): void
    {
        $message = new Message(['file' => $this->file]);
        $this->assertEquals($message->getHeaderField('content-type'), 'multipart/alternative');
    }

    public function testGetHeaderFieldNamed(): void
    {
        $message = new Message(['file' => $this->file]);
        $this->assertEquals($message->getHeaderField('content-type', 'boundary'), 'crazy-multipart');
    }

    public function testGetHeaderFieldMissing(): void
    {
        $message = new Message(['file' => $this->file]);
        $this->assertNull($message->getHeaderField('content-type', 'foo'));
    }

    public function testGetHeaderFieldInvalid(): void
    {
        $this->expectException(MailException\ExceptionInterface::class);
        $message = new Message(['file' => $this->file]);
        $message->getHeaderField('fake-header-name', 'foo');
    }

    public function testCaseInsensitiveMultipart(): void
    {
        $message = new Message(['raw' => "coNTent-TYpe: muLTIpaRT/x-empty\r\n\r\n"]);
        $this->assertTrue($message->isMultipart());
    }

    public function testCaseInsensitiveField(): void
    {
        $header = 'test; fOO="this is a test"';
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'Foo'), 'this is a test');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'bar'), null);
    }

    public function testSpaceInFieldName(): void
    {
        $header = 'test; foo =bar; baz      =42';
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'foo'), 'bar');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'baz'), 42);
    }

    /**
     * splitMessage with Headers as input fails to process AddressList with semicolons
     *
     * @see https://github.com/laminas/laminas-mail/pull/93
     */
    public function testHeadersLosesNameQuoting(): void
    {
        $headerList = [
            'From: "Famous bearings |;" <skf@example.com>',
            'Reply-To: "Famous bearings |:" <skf@example.com>',
        ];

        // create Headers object from array
        Mime\Decode::splitMessage(implode("\r\n", $headerList), $headers1, $body);
        $this->assertInstanceOf(Headers::class, $headers1);
        // create Headers object from Headers object
        Mime\Decode::splitMessage($headers1, $headers2, $body);
        $this->assertInstanceOf(Headers::class, $headers2);

        // test that same problem does not happen with Storage\Message internally
        $message = new Message(['headers' => $headers2, 'content' => (string) $body]);
        $this->assertEquals('"Famous bearings |;" <skf@example.com>', $message->from);
        $this->assertEquals('Famous bearings |: <skf@example.com>', $message->replyTo);
    }

    /**
     * @see https://zendframework.com/issues/browse/ZF2-372
     */
    public function testStrictParseMessage(): void
    {
        $this->expectException(MailException\RuntimeException::class);

        $raw     = file_get_contents($this->file);
        $raw     = "From foo@example.com  Sun Jan 01 00:00:00 2000\n" . $raw;
        $message = new Message(['raw' => $raw, 'strict' => true]);
    }

    public function testMultivaluedToHeader(): void
    {
        $message = new Message(['file' => $this->file2]);
        /** @var To $header */
        $header      = $message->getHeader('to');
        $addressList = $header->getAddressList();
        $this->assertEquals(2, $addressList->count());
        $address = $addressList->get('bar@example.pl');
        self::assertInstanceOf(AddressInterface::class, $address);
        $this->assertEquals('nicpoń', $address->getName());
    }

    public static function filesProvider(): array
    {
        $filePath                    = __DIR__ . '/../_files/mail.eml';
        $fileBlankLineOnTop          = __DIR__ . '/../_files/mail_blank_top_line.eml';
        $fileSurroundingSingleQuotes = __DIR__ . '/../_files/mail_surrounding_single_quotes.eml';

        return [
            // Description => [params]
            'resource'                            => [['file' => fopen($filePath, 'r')]],
            'file path'                           => [['file' => $filePath]],
            'raw'                                 => [['raw' => file_get_contents($filePath)]],
            'file with blank line on top'         => [['file' => $fileBlankLineOnTop]],
            'file with surrounding single quotes' => [['file' => $fileSurroundingSingleQuotes]],
        ];
    }
}

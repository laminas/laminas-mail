<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Storage;

use Exception as GeneralException;
use PHPUnit\Framework\TestCase;
use Zend\Mail\Exception as MailException;
use Zend\Mail\Storage;
use Zend\Mail\Storage\Exception;
use Zend\Mail\Storage\Message;
use Zend\Mime;
use Zend\Mime\Exception as MimeException;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Storage\Message<extended>
 */
class MessageTest extends TestCase
{
    protected $file;
    protected $file2;

    public function setUp()
    {
        $this->file = __DIR__ . '/../_files/mail.txt';
        $this->file2 = __DIR__ . '/../_files/mail_multi_to.txt';
    }

    public function testInvalidFile()
    {
        $this->expectException(GeneralException::class);
        new Message(['file' => '/this/file/does/not/exists']);
    }

    /**
     * @dataProvider filesProvider
     */
    public function testIsMultipart($params)
    {
        $message = new Message($params);
        $this->assertTrue($message->isMultipart());
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetHeader($params)
    {
        $message = new Message($params);
        $this->assertEquals($message->subject, 'multipart');
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetDecodedHeader($params)
    {
        $message = new Message($params);
        $this->assertEquals('Peter Müller <peter-mueller@example.com>', $message->from);
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGetHeaderAsArray($params)
    {
        $message = new Message($params);
        $this->assertEquals(['multipart'], $message->getHeader('subject', 'array'), 'getHeader() value not match');
    }

    public function testGetFirstPart()
    {
        $message = new Message(['file' => $this->file]);

        $this->assertEquals(substr($message->getPart(1)->getContent(), 0, 14), 'The first part');
    }

    public function testGetFirstPartTwice()
    {
        $message = new Message(['file' => $this->file]);

        $message->getPart(1);
        $this->assertEquals(substr($message->getPart(1)->getContent(), 0, 14), 'The first part');
    }


    public function testGetWrongPart()
    {
        $this->expectException(GeneralException::class);
        $message = new Message(['file' => $this->file]);
        $message->getPart(-1);
    }

    public function testNoHeaderMessage()
    {
        $message = new Message(['file' => __FILE__]);

        $this->assertEquals(substr($message->getContent(), 0, 5), '<?php');

        $raw = file_get_contents(__FILE__);
        $raw = "\t" . $raw;
        $message = new Message(['raw' => $raw]);

        $this->assertEquals(substr($message->getContent(), 0, 6), "\t<?php");
    }

    /**
     * after pull/86 messageId gets double braces
     *
     * @see https://github.com/zendframework/zend-mail/pull/86
     * @see https://github.com/zendframework/zend-mail/pull/156
     */
    public function testMessageIdHeader()
    {
        $message = new Message(['file' => $this->file]);
        $messageId = $message->messageId;
        $this->assertEquals('<CALTvGe4_oYgf9WsYgauv7qXh2-6=KbPLExmJNG7fCs9B=1nOYg@mail.example.com>', $messageId);
    }

    public function testMultipleHeader()
    {
        $raw = file_get_contents($this->file);
        $raw = "sUBject: test\r\nSubJect: test2\r\n" . $raw;
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

    public function testContentTypeDecode()
    {
        $message = new Message(['file' => $this->file]);

        $this->assertEquals(
            Mime\Decode::splitContentType($message->ContentType),
            ['type' => 'multipart/alternative', 'boundary' => 'crazy-multipart']
        );
    }

    public function testSplitEmptyMessage()
    {
        $this->assertEquals(Mime\Decode::splitMessageStruct('', 'xxx'), null);
    }

    public function testSplitInvalidMessage()
    {
        $this->expectException(MimeException\ExceptionInterface::class);
        Mime\Decode::splitMessageStruct("--xxx\n", 'xxx');
    }

    public function testInvalidMailHandler()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        new Message(['handler' => 1]);
    }

    public function testMissingId()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $mail = new Storage\Mbox(['filename' => __DIR__ . '/../_files/test.mbox/INBOX']);
        new Message(['handler' => $mail]);
    }

    public function testIterator()
    {
        $message = new Message(['file' => $this->file]);
        foreach (new \RecursiveIteratorIterator($message) as $num => $part) {
            if ($num == 1) {
                // explicit call of __toString() needed for PHP < 5.2
                $this->assertEquals(substr($part->__toString(), 0, 14), 'The first part');
            }
        }
        $this->assertEquals($part->contentType, 'text/x-vertical');
    }

    public function testDecodeString()
    {
        $is = Mime\Decode::decodeQuotedPrintable('=?UTF-8?Q?"Peter M=C3=BCller"?= <peter-mueller@example.com>');
        $this->assertEquals('"Peter Müller" <peter-mueller@example.com>', $is);
    }

    public function testSplitHeader()
    {
        $header = 'foo; x=y; y="x"';
        $this->assertEquals(Mime\Decode::splitHeaderField($header), ['foo', 'x' => 'y', 'y' => 'x']);
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'x'), 'y');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'y'), 'x');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'foo', 'foo'), 'foo');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'foo'), null);
    }

    public function testSplitInvalidHeader()
    {
        $this->expectException(MimeException\ExceptionInterface::class);
        $header = '';
        Mime\Decode::splitHeaderField($header);
    }

    public function testSplitMessage()
    {
        $header = 'Test: test';
        $body   = 'body';
        $newlines = ["\r\n", "\n\r", "\n", "\r"];

        $decoded_body    = null; // "Declare" variable before first "read" usage to avoid IDEs warning
        $decoded_headers = null; // "Declare" variable before first "read" usage to avoid IDEs warning

        foreach ($newlines as $contentEOL) {
            foreach ($newlines as $decodeEOL) {
                $content = $header . $contentEOL . $contentEOL . $body;
                Mime\Decode::splitMessage($content, $decoded_headers, $decoded_body, $decodeEOL);
                $this->assertEquals(['Test' => 'test'], $decoded_headers->toArray());
                $this->assertEquals($body, $decoded_body);
            }
        }
    }

    public function testToplines()
    {
        $message = new Message(['headers' => file_get_contents($this->file)]);
        $this->assertStringStartsWith('multipart message', $message->getToplines());
    }

    public function testNoContent()
    {
        $this->expectException(Exception\RuntimeException::class);
        $message = new Message(['raw' => 'Subject: test']);
        $message->getContent();
    }

    public function testEmptyHeader()
    {
        $message = new Message([]);
        $this->assertEquals([], $message->getHeaders()->toArray());

        $message = new Message([]);
        $subject = null;

        $this->expectException('Zend\\Mail\\Exception\\InvalidArgumentException');
        $message->subject;
    }

    public function testWrongHeaderType()
    {
        // @codingStandardsIgnoreStart
        $badMessage = unserialize(
            "O:25:\"Zend\Mail\Storage\Message\":9:{s:8:\"\x00*\x00flags\";a:0:{}s:10:\"\x00*\x00headers\";s:16:\"Yellow submarine\";s:10:\"\x00*\x00content\";N;s:11:\"\x00*\x00topLines\";s:0:\"\";s:8:\"\x00*\x00parts\";a:0:{}s:13:\"\x00*\x00countParts\";N;s:15:\"\x00*\x00iterationPos\";i:1;s:7:\"\x00*\x00mail\";N;s:13:\"\x00*\x00messageNum\";i:0;}"
        );
        // @codingStandardsIgnoreEnd

        $this->expectException(MailException\RuntimeException::class);
        $badMessage->getHeaders();
    }

    public function testEmptyBody()
    {
        $message = new Message([]);
        $part = null;
        try {
            $part = $message->getPart(1);
        } catch (Exception\RuntimeException $e) {
            // ok
        }
        if ($part) {
            $this->fail('no exception raised while getting part from empty message');
        }

        $message = new Message([]);
        $this->assertEquals(0, $message->countParts());
    }

    /**
     * @group ZF-5209
     */
    public function testCheckingHasHeaderFunctionality()
    {
        $message = new Message(['headers' => ['subject' => 'foo']]);

        $this->assertTrue($message->getHeaders()->has('subject'));
        $this->assertTrue(isset($message->subject));
        $this->assertTrue($message->getHeaders()->has('SuBject'));
        $this->assertTrue(isset($message->suBjeCt));
        $this->assertFalse($message->getHeaders()->has('From'));
    }

    public function testWrongMultipart()
    {
        $this->expectException(Exception\RuntimeException::class);
        $message = new Message(['raw' => "Content-Type: multipart/mixed\r\n\r\ncontent"]);
        $message->getPart(1);
    }

    public function testLateFetch()
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

    public function testManualIterator()
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

    public function testMessageFlagsAreSet()
    {
        $origFlags = [
            'foo' => 'bar',
            'baz' => 'bat'
        ];
        $message = new Message(['flags' => $origFlags]);

        $messageFlags = $message->getFlags();
        $this->assertTrue($message->hasFlag('bar'), var_export($messageFlags, 1));
        $this->assertTrue($message->hasFlag('bat'), var_export($messageFlags, 1));
        $this->assertEquals(['bar' => 'bar', 'bat' => 'bat'], $messageFlags);
    }

    public function testGetHeaderFieldSingle()
    {
        $message = new Message(['file' => $this->file]);
        $this->assertEquals($message->getHeaderField('subject'), 'multipart');
    }

    public function testGetHeaderFieldDefault()
    {
        $message = new Message(['file' => $this->file]);
        $this->assertEquals($message->getHeaderField('content-type'), 'multipart/alternative');
    }

    public function testGetHeaderFieldNamed()
    {
        $message = new Message(['file' => $this->file]);
        $this->assertEquals($message->getHeaderField('content-type', 'boundary'), 'crazy-multipart');
    }

    public function testGetHeaderFieldMissing()
    {
        $message = new Message(['file' => $this->file]);
        $this->assertNull($message->getHeaderField('content-type', 'foo'));
    }

    public function testGetHeaderFieldInvalid()
    {
        $this->expectException(MailException\ExceptionInterface::class);
        $message = new Message(['file' => $this->file]);
        $message->getHeaderField('fake-header-name', 'foo');
    }

    public function testCaseInsensitiveMultipart()
    {
        $message = new Message(['raw' => "coNTent-TYpe: muLTIpaRT/x-empty\r\n\r\n"]);
        $this->assertTrue($message->isMultipart());
    }

    public function testCaseInsensitiveField()
    {
        $header = 'test; fOO="this is a test"';
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'Foo'), 'this is a test');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'bar'), null);
    }

    public function testSpaceInFieldName()
    {
        $header = 'test; foo =bar; baz      =42';
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'foo'), 'bar');
        $this->assertEquals(Mime\Decode::splitHeaderField($header, 'baz'), 42);
    }

    /**
     * @group ZF2-372
     */
    public function testStrictParseMessage()
    {
        $this->expectException('Zend\\Mail\\Exception\\RuntimeException');

        $raw = file_get_contents($this->file);
        $raw = "From foo@example.com  Sun Jan 01 00:00:00 2000\n" . $raw;
        $message = new Message(['raw' => $raw, 'strict' => true]);
    }

    public function testMultivalueToHeader()
    {
        $message = new Message(['file' => $this->file2]);
        /** @var \Zend\Mail\Header\To $header */
        $header = $message->getHeader('to');
        $addressList = $header->getAddressList();
        $this->assertEquals(2, $addressList->count());
        $this->assertEquals('nicpoń', $addressList->get('bar@example.pl')->getName());
    }

    public function filesProvider()
    {
        $filePath = __DIR__ . '/../_files/mail.txt';
        $fileBlankLineOnTop = __DIR__ . '/../_files/mail_blank_top_line.txt';

        return [
            // Description => [params]
            'resource'                    => [['file' => fopen($filePath, 'r')]],
            'file path'                   => [['file' => $filePath]],
            'raw'                         => [['raw'  => file_get_contents($filePath)]],
            'file with blank line on top' => [['file' => $fileBlankLineOnTop]],
        ];
    }
}

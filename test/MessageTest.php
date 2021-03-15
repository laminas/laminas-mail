<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail\Address;
use Laminas\Mail\AddressList;
use Laminas\Mail\Exception;
use Laminas\Mail\Header;
use Laminas\Mail\Header\ContentType;
use Laminas\Mail\Header\GenericHeader;
use Laminas\Mail\Headers;
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Message<extended>
 */
class MessageTest extends TestCase
{
    /** @var Message */
    public $message;

    public function setUp(): void
    {
        $this->message = new Message();
    }

    public function testInvalidByDefault(): void
    {
        $this->assertFalse($this->message->isValid());
    }

    public function testSetsOrigDateHeaderByDefault(): void
    {
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('date'));
        $header  = $headers->get('date');
        $date    = date('r');
        $date    = substr($date, 0, 16);
        $test    = $header->getFieldValue();
        $test    = substr($test, 0, 16);
        $this->assertEquals($date, $test);
    }

    public function testAddingFromAddressMarksAsValid(): void
    {
        $this->message->addFrom('test@example.com');
        $this->assertTrue($this->message->isValid());
    }

    public function testHeadersMethodReturnsHeadersObject(): void
    {
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
    }

    public function testToMethodReturnsAddressListObject(): void
    {
        $this->message->addTo('test@example.com');
        $to = $this->message->getTo();
        $this->assertInstanceOf(AddressList::class, $to);
    }

    public function testToAddressListLivesInHeaders(): void
    {
        $this->message->addTo('test@example.com');
        $to      = $this->message->getTo();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('to'));
        $header  = $headers->get('to');
        $this->assertSame($header->getAddressList(), $to);
    }

    public function testFromMethodReturnsAddressListObject(): void
    {
        $this->message->addFrom('test@example.com');
        $from = $this->message->getFrom();
        $this->assertInstanceOf(AddressList::class, $from);
    }

    public function testFromAddressListLivesInHeaders(): void
    {
        $this->message->addFrom('test@example.com');
        $from    = $this->message->getFrom();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('from'));
        $header  = $headers->get('from');
        $this->assertSame($header->getAddressList(), $from);
    }

    public function testCcMethodReturnsAddressListObject(): void
    {
        $this->message->addCc('test@example.com');
        $cc = $this->message->getCc();
        $this->assertInstanceOf(AddressList::class, $cc);
    }

    public function testCcAddressListLivesInHeaders(): void
    {
        $this->message->addCc('test@example.com');
        $cc      = $this->message->getCc();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('cc'));
        $header  = $headers->get('cc');
        $this->assertSame($header->getAddressList(), $cc);
    }

    public function testBccMethodReturnsAddressListObject(): void
    {
        $this->message->addBcc('test@example.com');
        $bcc = $this->message->getBcc();
        $this->assertInstanceOf(AddressList::class, $bcc);
    }

    public function testBccAddressListLivesInHeaders(): void
    {
        $this->message->addBcc('test@example.com');
        $bcc     = $this->message->getBcc();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('bcc'));
        $header  = $headers->get('bcc');
        $this->assertSame($header->getAddressList(), $bcc);
    }

    public function testReplyToMethodReturnsAddressListObject(): void
    {
        $this->message->addReplyTo('test@example.com');
        $replyTo = $this->message->getReplyTo();
        $this->assertInstanceOf(AddressList::class, $replyTo);
    }

    public function testReplyToAddressListLivesInHeaders(): void
    {
        $this->message->addReplyTo('test@example.com');
        $replyTo = $this->message->getReplyTo();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('reply-to'));
        $header  = $headers->get('reply-to');
        $this->assertSame($header->getAddressList(), $replyTo);
    }

    public function testSenderIsNullByDefault(): void
    {
        $this->assertNull($this->message->getSender());
    }

    public function testNullSenderDoesNotCreateHeader(): void
    {
        $sender = $this->message->getSender();
        $headers = $this->message->getHeaders();
        $this->assertFalse($headers->has('sender'));
    }

    public function testSettingSenderCreatesAddressObject(): void
    {
        $this->message->setSender('test@example.com');
        $sender = $this->message->getSender();
        $this->assertInstanceOf(Address::class, $sender);
    }

    public function testCanSpecifyNameWhenSettingSender(): void
    {
        $this->message->setSender('test@example.com', 'Example Test');
        $sender = $this->message->getSender();
        $this->assertInstanceOf(Address::class, $sender);
        $this->assertEquals('Example Test', $sender->getName());
    }

    public function testCanProvideAddressObjectWhenSettingSender(): void
    {
        $sender = new Address('test@example.com');
        $this->message->setSender($sender);
        $test = $this->message->getSender();
        $this->assertSame($sender, $test);
    }

    public function testSenderAccessorsProxyToSenderHeader(): void
    {
        $header = new Header\Sender();
        $this->message->getHeaders()->addHeader($header);
        $address = new Address('test@example.com', 'Example Test');
        $this->message->setSender($address);
        $this->assertSame($address, $header->getAddress());
    }

    public function testCanAddFromAddressUsingName(): void
    {
        $this->message->addFrom('test@example.com', 'Example Test');
        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testCanAddFromAddressUsingEmailAndNameAsString(): void
    {
        $this->message->addFrom('Example Test <test@example.com>');
        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testCanAddFromAddressUsingAddressObject(): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $this->message->addFrom($address);

        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyFromAddressesUsingArray(): void
    {
        $addresses = [
            'test@example.com',
            'list@example.com' => 'Laminas Contributors List',
            new Address('announce@example.com', 'Laminas Announce List'),
        ];
        $this->message->addFrom($addresses);

        $from = $this->message->getFrom();
        $this->assertEquals(3, count($from));

        $this->assertTrue($from->has('test@example.com'));
        $this->assertTrue($from->has('list@example.com'));
        $this->assertTrue($from->has('announce@example.com'));
    }

    public function testCanAddManyFromAddressesUsingAddressListObject(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addFrom('announce@example.com');
        $this->message->addFrom($list);
        $from = $this->message->getFrom();
        $this->assertEquals(2, count($from));
        $this->assertTrue($from->has('announce@example.com'));
        $this->assertTrue($from->has('test@example.com'));
    }

    public function testCanSetFromListFromAddressList(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addFrom('announce@example.com');
        $this->message->setFrom($list);
        $from = $this->message->getFrom();
        $this->assertEquals(1, count($from));
        $this->assertFalse($from->has('announce@example.com'));
        $this->assertTrue($from->has('test@example.com'));
    }

    public function testCanAddCcAddressUsingName(): void
    {
        $this->message->addCc('test@example.com', 'Example Test');
        $addresses = $this->message->getCc();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testCanAddCcAddressUsingAddressObject(): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $this->message->addCc($address);

        $addresses = $this->message->getCc();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyCcAddressesUsingArray(): void
    {
        $addresses = [
            'test@example.com',
            'list@example.com' => 'Laminas Contributors List',
            new Address('announce@example.com', 'Laminas Announce List'),
        ];
        $this->message->addCc($addresses);

        $cc = $this->message->getCc();
        $this->assertEquals(3, count($cc));

        $this->assertTrue($cc->has('test@example.com'));
        $this->assertTrue($cc->has('list@example.com'));
        $this->assertTrue($cc->has('announce@example.com'));
    }

    public function testCanAddManyCcAddressesUsingAddressListObject(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addCc('announce@example.com');
        $this->message->addCc($list);
        $cc = $this->message->getCc();
        $this->assertEquals(2, count($cc));
        $this->assertTrue($cc->has('announce@example.com'));
        $this->assertTrue($cc->has('test@example.com'));
    }

    public function testCanSetCcListFromAddressList(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addCc('announce@example.com');
        $this->message->setCc($list);
        $cc = $this->message->getCc();
        $this->assertEquals(1, count($cc));
        $this->assertFalse($cc->has('announce@example.com'));
        $this->assertTrue($cc->has('test@example.com'));
    }

    public function testCanAddBccAddressUsingName(): void
    {
        $this->message->addBcc('test@example.com', 'Example Test');
        $addresses = $this->message->getBcc();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testCanAddBccAddressUsingAddressObject(): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $this->message->addBcc($address);

        $addresses = $this->message->getBcc();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyBccAddressesUsingArray(): void
    {
        $addresses = [
            'test@example.com',
            'list@example.com' => 'Laminas Contributors List',
            new Address('announce@example.com', 'Laminas Announce List'),
        ];
        $this->message->addBcc($addresses);

        $bcc = $this->message->getBcc();
        $this->assertEquals(3, count($bcc));

        $this->assertTrue($bcc->has('test@example.com'));
        $this->assertTrue($bcc->has('list@example.com'));
        $this->assertTrue($bcc->has('announce@example.com'));
    }

    public function testCanAddManyBccAddressesUsingAddressListObject(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addBcc('announce@example.com');
        $this->message->addBcc($list);
        $bcc = $this->message->getBcc();
        $this->assertEquals(2, count($bcc));
        $this->assertTrue($bcc->has('announce@example.com'));
        $this->assertTrue($bcc->has('test@example.com'));
    }

    public function testCanSetBccListFromAddressList(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addBcc('announce@example.com');
        $this->message->setBcc($list);
        $bcc = $this->message->getBcc();
        $this->assertEquals(1, count($bcc));
        $this->assertFalse($bcc->has('announce@example.com'));
        $this->assertTrue($bcc->has('test@example.com'));
    }

    public function testCanAddReplyToAddressUsingName(): void
    {
        $this->message->addReplyTo('test@example.com', 'Example Test');
        $addresses = $this->message->getReplyTo();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testCanAddReplyToAddressUsingAddressObject(): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $this->message->addReplyTo($address);

        $addresses = $this->message->getReplyTo();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyReplyToAddressesUsingArray(): void
    {
        $addresses = [
            'test@example.com',
            'list@example.com' => 'Laminas Contributors List',
            new Address('announce@example.com', 'Laminas Announce List'),
        ];
        $this->message->addReplyTo($addresses);

        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(3, count($replyTo));

        $this->assertTrue($replyTo->has('test@example.com'));
        $this->assertTrue($replyTo->has('list@example.com'));
        $this->assertTrue($replyTo->has('announce@example.com'));
    }

    public function testCanAddManyReplyToAddressesUsingAddressListObject(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addReplyTo('announce@example.com');
        $this->message->addReplyTo($list);
        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(2, count($replyTo));
        $this->assertTrue($replyTo->has('announce@example.com'));
        $this->assertTrue($replyTo->has('test@example.com'));
    }

    public function testCanSetReplyToListFromAddressList(): void
    {
        $list = new AddressList();
        $list->add('test@example.com');

        $this->message->addReplyTo('announce@example.com');
        $this->message->setReplyTo($list);
        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(1, count($replyTo));
        $this->assertFalse($replyTo->has('announce@example.com'));
        $this->assertTrue($replyTo->has('test@example.com'));
    }

    public function testSubjectIsEmptyByDefault(): void
    {
        $this->assertNull($this->message->getSubject());
    }

    public function testSubjectIsMutable(): void
    {
        $this->message->setSubject('test subject');
        $subject = $this->message->getSubject();
        $this->assertEquals('test subject', $subject);
    }

    public function testSubjectIsMutableReplaceExisting(): void
    {
        $this->message->setSubject('test subject');
        $this->message->setSubject('new subject');
        $this->assertSame('new subject', $this->message->getSubject());
    }

    public function testSettingSubjectProxiesToHeader(): void
    {
        $this->message->setSubject('test subject');
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertTrue($headers->has('subject'));
        $header = $headers->get('subject');
        $this->assertEquals('test subject', $header->getFieldValue());
    }

    public function testBodyIsEmptyByDefault(): void
    {
        $this->assertNull($this->message->getBody());
    }

    public function testMaySetBodyFromString(): void
    {
        $this->message->setBody('body');
        $this->assertEquals('body', $this->message->getBody());
    }

    public function testMaySetBodyFromStringSerializableObject(): void
    {
        $object = new TestAsset\StringSerializableObject('body');
        $this->message->setBody($object);
        $this->assertSame($object, $this->message->getBody());
        $this->assertEquals('body', $this->message->getBodyText());
    }

    public function testMaySetBodyFromMimeMessage(): void
    {
        $body = new MimeMessage();
        $this->message->setBody($body);
        $this->assertSame($body, $this->message->getBody());
    }

    public function testMaySetNullBody(): void
    {
        $this->message->setBody(null);
        $this->assertNull($this->message->getBody());
    }

    public static function invalidBodyValues(): array
    {
        return [
            [['foo']],
            [true],
            [false],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider invalidBodyValues
     */
    public function testSettingNonScalarNonMimeNonStringSerializableValueForBodyRaisesException($body): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->message->setBody($body);
    }

    public function testSettingBodyFromSinglePartMimeMessageSetsAppropriateHeaders(): void
    {
        $mime = new Mime('foo-bar');
        $part = new MimePart('<b>foo</b>');
        $part->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($part);

        $this->message->setBody($body);
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);

        $this->assertTrue($headers->has('mime-version'));
        $header = $headers->get('mime-version');
        $this->assertEquals('1.0', $header->getFieldValue());

        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertEquals('text/html', $header->getFieldValue());
    }

    public function testSettingUtf8MailBodyFromSinglePartMimeUtf8MessageSetsAppropriateHeaders(): void
    {
        $mime = new Mime('foo-bar');
        $part = new MimePart('UTF-8 TestString: AaÜüÄäÖöß');
        $part->type = Mime::TYPE_TEXT;
        $part->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $part->charset = 'utf-8';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($part);

        $this->message->setEncoding('UTF-8');
        $this->message->setBody($body);

        $this->assertStringContainsString(
            'Content-Type: text/plain;' . Headers::FOLDING . 'charset="utf-8"' . Headers::EOL
            . 'Content-Transfer-Encoding: quoted-printable' . Headers::EOL,
            $this->message->getHeaders()->toString()
        );
    }

    public function testSettingBodyFromMultiPartMimeMessageSetsAppropriateHeaders(): void
    {
        $mime = new Mime('foo-bar');
        $text = new MimePart('foo');
        $text->type = 'text/plain';
        $html = new MimePart('<b>foo</b>');
        $html->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($text);
        $body->addPart($html);

        $this->message->setBody($body);
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf(Headers::class, $headers);

        $this->assertTrue($headers->has('mime-version'));
        $header = $headers->get('mime-version');
        $this->assertEquals('1.0', $header->getFieldValue());

        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertEquals("multipart/mixed;\r\n boundary=\"foo-bar\"", $header->getFieldValue());
    }

    public function testRetrievingBodyTextFromMessageWithMultiPartMimeBodyReturnsMimeSerialization(): void
    {
        $mime = new Mime('foo-bar');
        $text = new MimePart('foo');
        $text->type = 'text/plain';
        $html = new MimePart('<b>foo</b>');
        $html->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($text);
        $body->addPart($html);

        $this->message->setBody($body);

        $text = $this->message->getBodyText();
        $this->assertEquals($body->generateMessage(Headers::EOL), $text);
        $this->assertStringContainsString('--foo-bar', $text);
        $this->assertStringContainsString('--foo-bar--', $text);
        $this->assertStringContainsString('Content-Type: text/plain', $text);
        $this->assertStringContainsString('Content-Type: text/html', $text);
    }

    public function testEncodingIsAsciiByDefault(): void
    {
        $this->assertEquals('ASCII', $this->message->getEncoding());
    }

    public function testEncodingIsMutable(): void
    {
        $this->message->setEncoding('UTF-8');
        $this->assertEquals('UTF-8', $this->message->getEncoding());
    }

    public function testMessageReturnsNonEncodedSubject(): void
    {
        $this->message->setSubject('This is a subject');
        $this->message->setEncoding('UTF-8');
        $this->assertEquals('This is a subject', $this->message->getSubject());
    }

    public function testSettingNonAsciiEncodingForcesMimeEncodingOfSomeHeaders(): void
    {
        $this->message->addTo('test@example.com', 'Laminas DevTeam');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('list@example.com', 'Laminas Contributors List');
        $this->message->addBcc('devs@example.com', 'Laminas CR Team');
        $this->message->setSubject('This is a subject');
        $this->message->setEncoding('UTF-8');

        $test = $this->message->getHeaders()->toString();

        $expected = '=?UTF-8?Q?Laminas=20DevTeam?=';
        $this->assertStringContainsString($expected, $test);
        $this->assertStringContainsString('<test@example.com>', $test);

        $expected = "=?UTF-8?Q?Matthew=20Weier=20O'Phinney?=";
        $this->assertStringContainsString($expected, $test, $test);
        $this->assertStringContainsString('<matthew@example.com>', $test);

        $expected = '=?UTF-8?Q?Laminas=20Contributors=20List?=';
        $this->assertStringContainsString($expected, $test);
        $this->assertStringContainsString('<list@example.com>', $test);

        $expected = '=?UTF-8?Q?Laminas=20CR=20Team?=';
        $this->assertStringContainsString($expected, $test);
        $this->assertStringContainsString('<devs@example.com>', $test);

        $expected = 'Subject: =?UTF-8?Q?This=20is=20a=20subject?=';
        $this->assertStringContainsString($expected, $test);
    }

    /**
     * @see https://zendframework.com/issues/browse/ZF2-507
     */
    public function testDefaultDateHeaderEncodingIsAlwaysAscii(): void
    {
        $this->message->setEncoding('utf-8');
        $headers = $this->message->getHeaders();
        $header  = $headers->get('date');
        $date    = date('r');
        $date    = substr($date, 0, 16);
        $test    = $header->getFieldValue();
        $test    = substr($test, 0, 16);
        $this->assertEquals($date, $test);
    }

    public function testRestoreFromSerializedString(): void
    {
        $this->message->addTo('test@example.com', 'Example Test');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('list@example.com', 'Laminas Contributors List');
        $this->message->setSubject('This is a subject');
        $this->message->setBody('foo');
        $serialized      = $this->message->toString();
        $restoredMessage = Message::fromString($serialized);
        $this->assertEquals($serialized, $restoredMessage->toString());
    }

    /**
     * @group 45
     */
    public function testCanRestoreFromSerializedStringWhenBodyContainsMultipleNewlines(): void
    {
        $this->message->addTo('test@example.com', 'Example Test');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('list@example.com', 'Laminas Contributors List');
        $this->message->setSubject('This is a subject');
        $this->message->setBody("foo\n\ntest");
        $serialized      = $this->message->toString();
        $restoredMessage = Message::fromString($serialized);
        $this->assertEquals($serialized, $restoredMessage->toString());
    }

    /**
     * @see https://zendframework.com/issues/browse/ZF-5962
     */
    public function testPassEmptyArrayIntoSetPartsOfMimeMessageShouldReturnEmptyBodyString(): void
    {
        $mimeMessage = new MimeMessage();
        $mimeMessage->setParts([]);

        $this->message->setBody($mimeMessage);
        $this->assertEquals('', $this->message->getBodyText());
    }

    public function messageRecipients(): array
    {
        return [
            'setFrom' => ['setFrom'],
            'addFrom' => ['addFrom'],
            'setTo' => ['setTo'],
            'addTo' => ['addTo'],
            'setCc' => ['setCc'],
            'addCc' => ['addCc'],
            'setBcc' => ['setBcc'],
            'addBcc' => ['addBcc'],
            'setReplyTo' => ['setReplyTo'],
            'setSender' => ['setSender'],
        ];
    }

    /**
     * @group ZF2015-04
     * @dataProvider messageRecipients
     */
    public function testExceptionWhenAttemptingToSerializeMessageWithCRLFInjectionViaHeader($recipientMethod): void
    {
        $subject = [
            'test1',
            'Content-Type: text/html; charset = "iso-8859-1"',
            '',
            '<html><body><iframe src="http://example.com/"></iframe></body></html> <!--',
        ];
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->message->{$recipientMethod}(implode(Headers::EOL, $subject));
    }

    /**
     * @group ZF2015-04
     */
    public function testDetectsCRLFInjectionViaSubject(): void
    {
        $subject = [
            'test1',
            'Content-Type: text/html; charset = "iso-8859-1"',
            '',
            '<html><body><iframe src="http://example.com/"></iframe></body></html> <!--',
        ];
        $this->message->setSubject(implode(Headers::EOL, $subject));

        $serializedHeaders = $this->message->getHeaders()->toString();
        $this->assertStringContainsString('example', $serializedHeaders);
        $this->assertStringNotContainsString("\r\n<html>", $serializedHeaders);
    }

    public function testHeaderUnfoldingWorksAsExpectedForMultipartMessages(): void
    {
        $text = new MimePart('Test content');
        $text->type = Mime::TYPE_TEXT;
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $text->disposition = Mime::DISPOSITION_INLINE;
        $text->charset = 'UTF-8';

        $html = new MimePart('<b>Test content</b>');
        $html->type = Mime::TYPE_HTML;
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $html->disposition = Mime::DISPOSITION_INLINE;
        $html->charset = 'UTF-8';

        $multipartContent = new MimeMessage();
        $multipartContent->addPart($text);
        $multipartContent->addPart($html);

        $multipartPart = new MimePart($multipartContent->generateMessage());
        $multipartPart->charset = 'UTF-8';
        $multipartPart->type = 'multipart/alternative';
        $multipartPart->boundary = $multipartContent->getMime()->boundary();

        $message = new MimeMessage();
        $message->addPart($multipartPart);

        $this->message->getHeaders()->addHeaderLine('Content-Transfer-Encoding', Mime::ENCODING_QUOTEDPRINTABLE);
        $this->message->setBody($message);

        $contentType = $this->message->getHeaders()->get('Content-Type');
        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertStringContainsString('multipart/alternative', $contentType->getFieldValue());
        $this->assertStringContainsString($multipartContent->getMime()->boundary(), $contentType->getFieldValue());
    }

    /**
     * @group 19
     */
    public function testCanParseMultipartReport(): void
    {
        $raw = file_get_contents(__DIR__ . '/_files/laminas-mail-19.eml');
        $message = Message::fromString($raw);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertIsString($message->getBody());

        $headers = $message->getHeaders();
        $this->assertCount(8, $headers);
        $this->assertTrue($headers->has('Date'));
        $this->assertTrue($headers->has('From'));
        $this->assertTrue($headers->has('Message-Id'));
        $this->assertTrue($headers->has('To'));
        $this->assertTrue($headers->has('MIME-Version'));
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertTrue($headers->has('Subject'));
        $this->assertTrue($headers->has('Auto-Submitted'));

        $contentType = $headers->get('Content-Type');
        $this->assertEquals('multipart/report', $contentType->getType());
    }

    public function testMailHeaderContainsZeroValue(): void
    {
        $message =
            "From: someone@example.com\r\n"
            ."To: someone@example.com\r\n"
            ."Subject: plain text email example\r\n"
            ."X-Spam-Score: 0\r\n"
            ."X-Some-Value: 1\r\n"
            ."\r\n"
            ."I am a test message\r\n";

        $msg = Message::fromString($message);
        $this->assertStringContainsString('X-Spam-Score: 0', $msg->toString());
    }

    /**
     * @ref CVE-2016-10033 which targeted WordPress
     */
    public function testSecondCodeInjectionInFromHeader(): void
    {
        $message = new Message();
        $this->expectException(Exception\InvalidArgumentException::class);
        // @codingStandardsIgnoreStart
        $message->setFrom('user@xenial(tmp1 -be ${run{${substr{0}{1}{$spool_directory}}usr${substr{0}{1}{$spool_directory}}bin${substr{0}{1}{$spool_directory}}touch${substr{10}{1}{$tod_log}}${substr{0}{1}{$spool_directory}}tmp${substr{0}{1}{$spool_directory}}test}}  tmp2)', 'Sender\'s name');
        // @codingStandardsIgnoreEnd
    }

    public function testMessageSubjectFromString(): void
    {
        $rawMessage = 'Subject: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=';
        $mail = Message::fromString($rawMessage);

        $this->assertStringContainsString(
            'Subject: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=' . "\r\n",
            $mail->toString()
        );
    }

    public function testMessageSubjectSetSubject(): void
    {
        $mail = new Message();
        $mail->setSubject('Non “ascii” characters like accented vowels òàùèéì');

        $this->assertStringContainsString(
            'Subject: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=' . "\r\n",
            $mail->toString()
        );
    }

    public function testCorrectHeaderEncodingAddHeader(): void
    {
        $mail = new Message();
        $header = new GenericHeader('X-Test', 'Non “ascii” characters like accented vowels òàùèéì');
        $mail->getHeaders()->addHeader($header);

        $this->assertStringContainsString(
            'X-Test: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=' . "\r\n",
            $mail->toString()
        );
    }

    public function testCorrectHeaderEncodingSetHeaders(): void
    {
        $mail = new Message();
        $header = new GenericHeader('X-Test', 'Non “ascii” characters like accented vowels òàùèéì');
        $headers = new Headers();
        $headers->addHeader($header);
        $mail->setHeaders($headers);

        $this->assertStringContainsString(
            'X-Test: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=' . "\r\n",
            $mail->toString()
        );
    }

    public function testCorrectHeaderEncodingFromString(): void
    {
        $mail = new Message();
        $str = 'X-Test: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=';
        $header = GenericHeader::fromString($str);
        $mail->getHeaders()->addHeader($header);

        $this->assertStringContainsString(
            'X-Test: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=' . "\r\n",
            $mail->toString()
        );
    }

    public function testCorrectHeaderEncodingFromStringAndSetHeaders(): void
    {
        $mail = new Message();
        $str = 'X-Test: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=';

        $header = GenericHeader::fromString($str);
        $headers = new Headers();
        $headers->addHeader($header);
        $mail->setHeaders($headers);

        $this->assertStringContainsString(
            'X-Test: =?UTF-8?Q?Non=20=E2=80=9Cascii=E2=80=9D=20characters=20like=20accented=20?=' . "\r\n"
            . ' =?UTF-8?Q?vowels=20=C3=B2=C3=A0=C3=B9=C3=A8=C3=A9=C3=AC?=' . "\r\n",
            $mail->toString()
        );
    }

    public function testMessageSubjectEncodingWhenEncodingSetAfterTheSubject(): void
    {
        $mail = new Message();
        $mail->setSubject('hello world');
        $mail->setEncoding('UTF-8');

        $this->assertSame('UTF-8', $mail->getHeaders()->get('subject')->getEncoding());
        $this->assertSame(
            'Subject: =?UTF-8?Q?hello=20world?=',
            $mail->getHeaders()->get('subject')->toString()
        );
    }

    public function testMessageSubjectEncodingWhenEcodingSetBeforeTheSubject(): void
    {
        $mail = new Message();
        $mail->setEncoding('UTF-8');
        $mail->setSubject('hello world');

        $this->assertSame('UTF-8', $mail->getHeaders()->get('subject')->getEncoding());
        $this->assertSame(
            'Subject: =?UTF-8?Q?hello=20world?=',
            $mail->getHeaders()->get('subject')->toString()
        );
    }
}

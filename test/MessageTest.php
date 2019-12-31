<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail\Address;
use Laminas\Mail\AddressList;
use Laminas\Mail\Header;
use Laminas\Mail\Headers;
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use stdClass;

/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage UnitTests
 * @group      Laminas_Mail
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message */
    public $message;

    public function setUp()
    {
        $this->message = new Message();
    }

    public function testInvalidByDefault()
    {
        $this->assertFalse($this->message->isValid());
    }

    public function testSetsOrigDateHeaderByDefault()
    {
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('date'));
        $header  = $headers->get('date');
        $date    = date('r');
        $date    = substr($date, 0, 16);
        $test    = $header->getFieldValue();
        $test    = substr($test, 0, 16);
        $this->assertEquals($date, $test);
    }

    public function testAddingFromAddressMarksAsValid()
    {
        $this->message->addFrom('api-tools-devteam@example.com');
        $this->assertTrue($this->message->isValid());
    }

    public function testHeadersMethodReturnsHeadersObject()
    {
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
    }

    public function testToMethodReturnsAddressListObject()
    {
        $this->message->addTo('api-tools-devteam@example.com');
        $to = $this->message->getTo();
        $this->assertInstanceOf('Laminas\Mail\AddressList', $to);
    }

    public function testToAddressListLivesInHeaders()
    {
        $this->message->addTo('api-tools-devteam@example.com');
        $to      = $this->message->getTo();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('to'));
        $header  = $headers->get('to');
        $this->assertSame($header->getAddressList(), $to);
    }

    public function testFromMethodReturnsAddressListObject()
    {
        $this->message->addFrom('api-tools-devteam@example.com');
        $from = $this->message->getFrom();
        $this->assertInstanceOf('Laminas\Mail\AddressList', $from);
    }

    public function testFromAddressListLivesInHeaders()
    {
        $this->message->addFrom('api-tools-devteam@example.com');
        $from    = $this->message->getFrom();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('from'));
        $header  = $headers->get('from');
        $this->assertSame($header->getAddressList(), $from);
    }

    public function testCcMethodReturnsAddressListObject()
    {
        $this->message->addCc('api-tools-devteam@example.com');
        $cc = $this->message->getCc();
        $this->assertInstanceOf('Laminas\Mail\AddressList', $cc);
    }

    public function testCcAddressListLivesInHeaders()
    {
        $this->message->addCc('api-tools-devteam@example.com');
        $cc      = $this->message->getCc();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('cc'));
        $header  = $headers->get('cc');
        $this->assertSame($header->getAddressList(), $cc);
    }

    public function testBccMethodReturnsAddressListObject()
    {
        $this->message->addBcc('api-tools-devteam@example.com');
        $bcc = $this->message->getBcc();
        $this->assertInstanceOf('Laminas\Mail\AddressList', $bcc);
    }

    public function testBccAddressListLivesInHeaders()
    {
        $this->message->addBcc('api-tools-devteam@example.com');
        $bcc     = $this->message->getBcc();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('bcc'));
        $header  = $headers->get('bcc');
        $this->assertSame($header->getAddressList(), $bcc);
    }

    public function testReplyToMethodReturnsAddressListObject()
    {
        $this->message->addReplyTo('api-tools-devteam@example.com');
        $replyTo = $this->message->getReplyTo();
        $this->assertInstanceOf('Laminas\Mail\AddressList', $replyTo);
    }

    public function testReplyToAddressListLivesInHeaders()
    {
        $this->message->addReplyTo('api-tools-devteam@example.com');
        $replyTo = $this->message->getReplyTo();
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('reply-to'));
        $header  = $headers->get('reply-to');
        $this->assertSame($header->getAddressList(), $replyTo);
    }

    public function testSenderIsNullByDefault()
    {
        $this->assertNull($this->message->getSender());
    }

    public function testSettingSenderCreatesAddressObject()
    {
        $this->message->setSender('api-tools-devteam@example.com');
        $sender = $this->message->getSender();
        $this->assertInstanceOf('Laminas\Mail\Address', $sender);
    }

    public function testCanSpecifyNameWhenSettingSender()
    {
        $this->message->setSender('api-tools-devteam@example.com', 'Laminas DevTeam');
        $sender = $this->message->getSender();
        $this->assertInstanceOf('Laminas\Mail\Address', $sender);
        $this->assertEquals('Laminas DevTeam', $sender->getName());
    }

    public function testCanProvideAddressObjectWhenSettingSender()
    {
        $sender = new Address('api-tools-devteam@example.com');
        $this->message->setSender($sender);
        $test = $this->message->getSender();
        $this->assertSame($sender, $test);
    }

    public function testSenderAccessorsProxyToSenderHeader()
    {
        $header = new Header\Sender();
        $this->message->getHeaders()->addHeader($header);
        $address = new Address('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->setSender($address);
        $this->assertSame($address, $header->getAddress());
    }

    public function testCanAddFromAddressUsingName()
    {
        $this->message->addFrom('api-tools-devteam@example.com', 'Laminas DevTeam');
        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('api-tools-devteam@example.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testCanAddFromAddressUsingAddressObject()
    {
        $address = new Address('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->addFrom($address);

        $addresses = $this->message->getFrom();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyFromAddressesUsingArray()
    {
        $addresses = array(
            'api-tools-devteam@example.com',
            'api-tools-contributors@example.com' => 'Laminas Contributors List',
            new Address('fw-announce@example.com', 'Laminas Announce List'),
        );
        $this->message->addFrom($addresses);

        $from = $this->message->getFrom();
        $this->assertEquals(3, count($from));

        $this->assertTrue($from->has('api-tools-devteam@example.com'));
        $this->assertTrue($from->has('api-tools-contributors@example.com'));
        $this->assertTrue($from->has('fw-announce@example.com'));
    }

    public function testCanAddManyFromAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addFrom('fw-announce@example.com');
        $this->message->addFrom($list);
        $from = $this->message->getFrom();
        $this->assertEquals(2, count($from));
        $this->assertTrue($from->has('fw-announce@example.com'));
        $this->assertTrue($from->has('api-tools-devteam@example.com'));
    }

    public function testCanSetFromListFromAddressList()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addFrom('fw-announce@example.com');
        $this->message->setFrom($list);
        $from = $this->message->getFrom();
        $this->assertEquals(1, count($from));
        $this->assertFalse($from->has('fw-announce@example.com'));
        $this->assertTrue($from->has('api-tools-devteam@example.com'));
    }

    public function testCanAddCcAddressUsingName()
    {
        $this->message->addCc('api-tools-devteam@example.com', 'Laminas DevTeam');
        $addresses = $this->message->getCc();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('api-tools-devteam@example.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testCanAddCcAddressUsingAddressObject()
    {
        $address = new Address('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->addCc($address);

        $addresses = $this->message->getCc();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyCcAddressesUsingArray()
    {
        $addresses = array(
            'api-tools-devteam@example.com',
            'api-tools-contributors@example.com' => 'Laminas Contributors List',
            new Address('fw-announce@example.com', 'Laminas Announce List'),
        );
        $this->message->addCc($addresses);

        $cc = $this->message->getCc();
        $this->assertEquals(3, count($cc));

        $this->assertTrue($cc->has('api-tools-devteam@example.com'));
        $this->assertTrue($cc->has('api-tools-contributors@example.com'));
        $this->assertTrue($cc->has('fw-announce@example.com'));
    }

    public function testCanAddManyCcAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addCc('fw-announce@example.com');
        $this->message->addCc($list);
        $cc = $this->message->getCc();
        $this->assertEquals(2, count($cc));
        $this->assertTrue($cc->has('fw-announce@example.com'));
        $this->assertTrue($cc->has('api-tools-devteam@example.com'));
    }

    public function testCanSetCcListFromAddressList()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addCc('fw-announce@example.com');
        $this->message->setCc($list);
        $cc = $this->message->getCc();
        $this->assertEquals(1, count($cc));
        $this->assertFalse($cc->has('fw-announce@example.com'));
        $this->assertTrue($cc->has('api-tools-devteam@example.com'));
    }

    public function testCanAddBccAddressUsingName()
    {
        $this->message->addBcc('api-tools-devteam@example.com', 'Laminas DevTeam');
        $addresses = $this->message->getBcc();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('api-tools-devteam@example.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testCanAddBccAddressUsingAddressObject()
    {
        $address = new Address('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->addBcc($address);

        $addresses = $this->message->getBcc();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyBccAddressesUsingArray()
    {
        $addresses = array(
            'api-tools-devteam@example.com',
            'api-tools-contributors@example.com' => 'Laminas Contributors List',
            new Address('fw-announce@example.com', 'Laminas Announce List'),
        );
        $this->message->addBcc($addresses);

        $bcc = $this->message->getBcc();
        $this->assertEquals(3, count($bcc));

        $this->assertTrue($bcc->has('api-tools-devteam@example.com'));
        $this->assertTrue($bcc->has('api-tools-contributors@example.com'));
        $this->assertTrue($bcc->has('fw-announce@example.com'));
    }

    public function testCanAddManyBccAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addBcc('fw-announce@example.com');
        $this->message->addBcc($list);
        $bcc = $this->message->getBcc();
        $this->assertEquals(2, count($bcc));
        $this->assertTrue($bcc->has('fw-announce@example.com'));
        $this->assertTrue($bcc->has('api-tools-devteam@example.com'));
    }

    public function testCanSetBccListFromAddressList()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addBcc('fw-announce@example.com');
        $this->message->setBcc($list);
        $bcc = $this->message->getBcc();
        $this->assertEquals(1, count($bcc));
        $this->assertFalse($bcc->has('fw-announce@example.com'));
        $this->assertTrue($bcc->has('api-tools-devteam@example.com'));
    }

    public function testCanAddReplyToAddressUsingName()
    {
        $this->message->addReplyTo('api-tools-devteam@example.com', 'Laminas DevTeam');
        $addresses = $this->message->getReplyTo();
        $this->assertEquals(1, count($addresses));
        $address = $addresses->current();
        $this->assertEquals('api-tools-devteam@example.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testCanAddReplyToAddressUsingAddressObject()
    {
        $address = new Address('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->addReplyTo($address);

        $addresses = $this->message->getReplyTo();
        $this->assertEquals(1, count($addresses));
        $test = $addresses->current();
        $this->assertSame($address, $test);
    }

    public function testCanAddManyReplyToAddressesUsingArray()
    {
        $addresses = array(
            'api-tools-devteam@example.com',
            'api-tools-contributors@example.com' => 'Laminas Contributors List',
            new Address('fw-announce@example.com', 'Laminas Announce List'),
        );
        $this->message->addReplyTo($addresses);

        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(3, count($replyTo));

        $this->assertTrue($replyTo->has('api-tools-devteam@example.com'));
        $this->assertTrue($replyTo->has('api-tools-contributors@example.com'));
        $this->assertTrue($replyTo->has('fw-announce@example.com'));
    }

    public function testCanAddManyReplyToAddressesUsingAddressListObject()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addReplyTo('fw-announce@example.com');
        $this->message->addReplyTo($list);
        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(2, count($replyTo));
        $this->assertTrue($replyTo->has('fw-announce@example.com'));
        $this->assertTrue($replyTo->has('api-tools-devteam@example.com'));
    }

    public function testCanSetReplyToListFromAddressList()
    {
        $list = new AddressList();
        $list->add('api-tools-devteam@example.com');

        $this->message->addReplyTo('fw-announce@example.com');
        $this->message->setReplyTo($list);
        $replyTo = $this->message->getReplyTo();
        $this->assertEquals(1, count($replyTo));
        $this->assertFalse($replyTo->has('fw-announce@example.com'));
        $this->assertTrue($replyTo->has('api-tools-devteam@example.com'));
    }

    public function testSubjectIsEmptyByDefault()
    {
        $this->assertNull($this->message->getSubject());
    }

    public function testSubjectIsMutable()
    {
        $this->message->setSubject('test subject');
        $subject = $this->message->getSubject();
        $this->assertEquals('test subject', $subject);
    }

    public function testSettingSubjectProxiesToHeader()
    {
        $this->message->setSubject('test subject');
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);
        $this->assertTrue($headers->has('subject'));
        $header = $headers->get('subject');
        $this->assertEquals('test subject', $header->getFieldValue());
    }

    public function testBodyIsEmptyByDefault()
    {
        $this->assertNull($this->message->getBody());
    }

    public function testMaySetBodyFromString()
    {
        $this->message->setBody('body');
        $this->assertEquals('body', $this->message->getBody());
    }

    public function testMaySetBodyFromStringSerializableObject()
    {
        $object = new TestAsset\StringSerializableObject('body');
        $this->message->setBody($object);
        $this->assertSame($object, $this->message->getBody());
        $this->assertEquals('body', $this->message->getBodyText());
    }

    public function testMaySetBodyFromMimeMessage()
    {
        $body = new MimeMessage();
        $this->message->setBody($body);
        $this->assertSame($body, $this->message->getBody());
    }

    public function testMaySetNullBody()
    {
        $this->message->setBody(null);
        $this->assertNull($this->message->getBody());
    }

    public static function invalidBodyValues()
    {
        return array(
            array(array('foo')),
            array(true),
            array(false),
            array(new stdClass),
        );
    }

    /**
     * @dataProvider invalidBodyValues
     */
    public function testSettingNonScalarNonMimeNonStringSerializableValueForBodyRaisesException($body)
    {
        $this->setExpectedException('Laminas\Mail\Exception\InvalidArgumentException');
        $this->message->setBody($body);
    }

    public function testSettingBodyFromSinglePartMimeMessageSetsAppropriateHeaders()
    {
        $mime = new Mime('foo-bar');
        $part = new MimePart('<b>foo</b>');
        $part->type = 'text/html';
        $body = new MimeMessage();
        $body->setMime($mime);
        $body->addPart($part);

        $this->message->setBody($body);
        $headers = $this->message->getHeaders();
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);

        $this->assertTrue($headers->has('mime-version'));
        $header = $headers->get('mime-version');
        $this->assertEquals('1.0', $header->getFieldValue());

        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertEquals('text/html', $header->getFieldValue());
    }

    public function testSettingBodyFromMultiPartMimeMessageSetsAppropriateHeaders()
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
        $this->assertInstanceOf('Laminas\Mail\Headers', $headers);

        $this->assertTrue($headers->has('mime-version'));
        $header = $headers->get('mime-version');
        $this->assertEquals('1.0', $header->getFieldValue());

        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertEquals("multipart/mixed;\r\n boundary=\"foo-bar\"", $header->getFieldValue());
    }

    public function testRetrievingBodyTextFromMessageWithMultiPartMimeBodyReturnsMimeSerialization()
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
        $this->assertContains('--foo-bar', $text);
        $this->assertContains('--foo-bar--', $text);
        $this->assertContains('Content-Type: text/plain', $text);
        $this->assertContains('Content-Type: text/html', $text);
    }

    public function testEncodingIsAsciiByDefault()
    {
        $this->assertEquals('ASCII', $this->message->getEncoding());
    }

    public function testEncodingIsMutable()
    {
        $this->message->setEncoding('UTF-8');
        $this->assertEquals('UTF-8', $this->message->getEncoding());
    }

    public function testMessageReturnsNonEncodedSubject()
    {
        $this->message->setSubject('This is a subject');
        $this->message->setEncoding('UTF-8');
        $this->assertEquals('This is a subject', $this->message->getSubject());
    }

    public function testSettingNonAsciiEncodingForcesMimeEncodingOfSomeHeaders()
    {
        $this->message->addTo('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('api-tools-contributors@example.com', 'Laminas Contributors List');
        $this->message->addBcc('api-tools-crteam@example.com', 'Laminas CR Team');
        $this->message->setSubject('This is a subject');
        $this->message->setEncoding('UTF-8');

        $test = $this->message->getHeaders()->toString();

        $expected = '=?UTF-8?Q?Laminas=20DevTeam?=';
        $this->assertContains($expected, $test);
        $this->assertContains('<api-tools-devteam@example.com>', $test);

        $expected = "=?UTF-8?Q?Matthew=20Weier=20O'Phinney?=";
        $this->assertContains($expected, $test, $test);
        $this->assertContains('<matthew@example.com>', $test);

        $expected = '=?UTF-8?Q?Laminas=20Contributors=20List?=';
        $this->assertContains($expected, $test);
        $this->assertContains('<api-tools-contributors@example.com>', $test);

        $expected = '=?UTF-8?Q?Laminas=20CR=20Team?=';
        $this->assertContains($expected, $test);
        $this->assertContains('<api-tools-crteam@example.com>', $test);

        $expected = 'Subject: =?UTF-8?Q?This=20is=20a=20subject?=';
        $this->assertContains($expected, $test);
    }

    /**
     * @group Laminas-507
     */
    public function testDefaultDateHeaderEncodingIsAlwaysAscii()
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

    public function testRestoreFromSerializedString()
    {
        $this->message->addTo('api-tools-devteam@example.com', 'Laminas DevTeam');
        $this->message->addFrom('matthew@example.com', "Matthew Weier O'Phinney");
        $this->message->addCc('api-tools-contributors@example.com', 'Laminas Contributors List');
        $this->message->setSubject('This is a subject');
        $this->message->setBody('foo');
        $serialized      = $this->message->toString();
        $restoredMessage = Message::fromString($serialized);
        $this->assertEquals($serialized, $restoredMessage->toString());
    }
}

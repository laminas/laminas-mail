<?php

namespace LaminasTest\Mail;

use Laminas\Mail\Address;
use Laminas\Mail\AddressList;
use Laminas\Mail\Exception;
use Laminas\Mail\Message;
use Laminas\Mail\MessageFactory;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\MessageFactory<extended>
 */
class MessageFactoryTest extends TestCase
{
    public function testConstructMessageWithOptions(): void
    {
        $options = [
            'encoding' => 'UTF-8',
            'from'     => 'matthew@example.com',
            'to'       => 'test@example.com',
            'cc'       => 'list@example.com',
            'bcc'      => 'test@example.com',
            'reply-to' => 'matthew@example.com',
            'sender'   => 'matthew@example.com',
            'subject'  => 'subject',
            'body'     => 'body',
        ];

        $message = MessageFactory::getInstance($options);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('UTF-8', $message->getEncoding());
        $this->assertEquals('subject', $message->getSubject());
        $this->assertEquals('body', $message->getBody());
        $this->assertInstanceOf(Address::class, $message->getSender());
        $this->assertEquals($options['sender'], $message->getSender()->getEmail());

        $getMethods = [
            'from'     => 'getFrom',
            'to'       => 'getTo',
            'cc'       => 'getCc',
            'bcc'      => 'getBcc',
            'reply-to' => 'getReplyTo',
        ];

        foreach ($getMethods as $key => $method) {
            $value = $message->{$method}();
            $this->assertInstanceOf(AddressList::class, $value);
            $this->assertEquals(1, count($value));
            $this->assertTrue($value->has($options[$key]));
        }
    }

    public function testCanCreateMessageWithMultipleRecipientsViaArrayValue(): void
    {
        $options = [
            'from' => ['matthew@example.com' => 'Matthew'],
            'to'   => [
                'test@example.com',
                'list@example.com',
            ],
        ];

        $message = MessageFactory::getInstance($options);

        $from = $message->getFrom();
        $this->assertInstanceOf(AddressList::class, $from);
        $this->assertEquals(1, count($from));
        $this->assertTrue($from->has('matthew@example.com'));
        $this->assertEquals('Matthew', $from->get('matthew@example.com')->getName());

        $to = $message->getTo();
        $this->assertInstanceOf(AddressList::class, $to);
        $this->assertEquals(2, count($to));
        $this->assertTrue($to->has('test@example.com'));
        $this->assertTrue($to->has('list@example.com'));
    }

    public function testIgnoresUnreconizedOptions(): void
    {
        $options = [
            'foo' => 'bar',
        ];
        $mail    = MessageFactory::getInstance($options);
        $this->assertInstanceOf(Message::class, $mail);
    }

    public function testEmptyOption(): void
    {
        $mail = MessageFactory::getInstance();
        $this->assertInstanceOf(Message::class, $mail);
    }

    public static function invalidMessageOptions(): array
    {
        return [
            'null'         => [null],
            'bool'         => [true],
            'int'          => [1],
            'float'        => [1.1],
            'string'       => ['not-an-array'],
            'plain-object' => [
                (object) [
                    'from' => 'matthew@example.com',
                    'to'   => 'foo@example.com',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidMessageOptions
     */
    public function testExceptionForOptionsNotArrayOrTraversable(mixed $options): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        MessageFactory::getInstance($options);
    }
}

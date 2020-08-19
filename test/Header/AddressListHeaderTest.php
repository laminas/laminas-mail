<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Address;
use Laminas\Mail\AddressList;
use Laminas\Mail\Header\AbstractAddressList;
use Laminas\Mail\Header\Bcc;
use Laminas\Mail\Header\Cc;
use Laminas\Mail\Header\From;
use Laminas\Mail\Header\ReplyTo;
use Laminas\Mail\Header\To;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 */
class AddressListHeaderTest extends TestCase
{
    public static function getHeaderInstances(): array
    {
        return [
            [new Bcc(), 'Bcc'],
            [new Cc(), 'Cc'],
            [new From(), 'From'],
            [new ReplyTo(), 'Reply-To'],
            [new To(), 'To'],
        ];
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeadersExtendAbstractAddressListHeader($header): void
    {
        $this->assertInstanceOf(AbstractAddressList::class, $header);
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeaderFieldNamesAreDiscrete($header, $type): void
    {
        $this->assertEquals($type, $header->getFieldName());
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeadersComposeAddressLists($header): void
    {
        $list = $header->getAddressList();
        $this->assertInstanceOf(AddressList::class, $list);
    }

    public function testFieldValueIsEmptyByDefault(): void
    {
        $header = new To();
        $this->assertEquals('', $header->getFieldValue());
    }

    public function testFieldValueIsCreatedFromAddressList(): void
    {
        $header = new To();
        $list   = $header->getAddressList();
        $this->populateAddressList($list);
        $expected = $this->getExpectedFieldValue();
        $this->assertEquals($expected, $header->getFieldValue());
    }

    public function populateAddressList(AddressList $list): void
    {
        $address = new Address('test@example.com', 'Example Test');
        $list->add($address);
        $list->add('list@example.com');
        $list->add('announce@example.com', 'Example Announce List');
        $list->add('first@last.example.com', 'Last, First');
    }

    public function getExpectedFieldValue(): string
    {
        // @codingStandardsIgnoreStart
        return "Example Test <test@example.com>,\r\n list@example.com,\r\n Example Announce List <announce@example.com>,\r\n \"Last, First\" <first@last.example.com>";
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testStringRepresentationIncludesHeaderAndFieldValue($header, $type): void
    {
        $this->populateAddressList($header->getAddressList());
        $expected = sprintf('%s: %s', $type, $this->getExpectedFieldValue());
        $this->assertEquals($expected, $header->toString());
    }

    public function getStringHeaders(): array
    {
        $value = $this->getExpectedFieldValue();
        return [
            'cc'       => ['Cc: ' . $value, Cc::class],
            'bcc'      => ['Bcc: ' . $value, Bcc::class],
            'from'     => ['From: ' . $value, From::class],
            'reply-to' => ['Reply-To: ' . $value, ReplyTo::class],
            'to'       => ['To: ' . $value, To::class],
        ];
    }

    /**
     * @dataProvider getStringHeaders
     */
    public function testDeserializationFromString($headerLine, $class): void
    {
        $callback = sprintf('%s::fromString', $class);
        $header   = $callback($headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(4, count($list));
        $this->assertTrue($list->has('test@example.com'));
        $this->assertTrue($list->has('list@example.com'));
        $this->assertTrue($list->has('announce@example.com'));
        $this->assertTrue($list->has('first@last.example.com'));
        $address = $list->get('test@example.com');
        $this->assertEquals('Example Test', $address->getName());
        $address = $list->get('list@example.com');
        $this->assertNull($address->getName());
        $address = $list->get('announce@example.com');
        $this->assertEquals('Example Announce List', $address->getName());
        $address = $list->get('first@last.example.com');
        $this->assertEquals('Last, First', $address->getName());
    }

    public function getStringHeadersWithNoWhitespaceSeparator(): array
    {
        $value = $this->getExpectedFieldValue();
        return [
            'cc'       => ['Cc:' . $value, Cc::class],
            'bcc'      => ['Bcc:' . $value, Bcc::class],
            'from'     => ['From:' . $value, From::class],
            'reply-to' => ['Reply-To:' . $value, ReplyTo::class],
            'to'       => ['To:' . $value, To::class],
        ];
    }

    /**
     * @dataProvider getHeadersWithComments
     */
    public function testDeserializationFromStringWithComments($value): void
    {
        $header = From::fromString($value);
        $list = $header->getAddressList();
        $this->assertEquals(1, count($list));
        $this->assertTrue($list->has('user@example.com'));
    }

    public function getHeadersWithComments(): array
    {
        return [
            ['From: user@example.com (Comment)'],
            ['From: user@example.com (Comm\\)ent)'],
            ['From: (Comment\\\\)user@example.com(Another)'],
        ];
    }

    /**
     * @group 3789
     * @dataProvider getStringHeadersWithNoWhitespaceSeparator
     */
    public function testAllowsNoWhitespaceBetweenHeaderAndValue($headerLine, $class): void
    {
        $callback = sprintf('%s::fromString', $class);
        $header   = $callback($headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(4, count($list));
        $this->assertTrue($list->has('test@example.com'));
        $this->assertTrue($list->has('list@example.com'));
        $this->assertTrue($list->has('announce@example.com'));
        $this->assertTrue($list->has('first@last.example.com'));
        $address = $list->get('test@example.com');
        $this->assertEquals('Example Test', $address->getName());
        $address = $list->get('list@example.com');
        $this->assertNull($address->getName());
        $address = $list->get('announce@example.com');
        $this->assertEquals('Example Announce List', $address->getName());
        $address = $list->get('first@last.example.com');
        $this->assertEquals('Last, First', $address->getName());
    }

    /**
     * @dataProvider getAddressListsWithGroup
     */
    public function testAddressListWithGroup($input, $count, $sample): void
    {
        $header = To::fromString($input);
        $list = $header->getAddressList();
        $this->assertEquals($count, count($list));
        if ($count > 0) {
            $this->assertTrue($list->has($sample));
        }
    }

    public function getAddressListsWithGroup(): array
    {
        return [
            ['To: undisclosed-recipients:;', 0, null],
            ['To: friends: john@example.com; enemies: john@example.net, bart@example.net;', 3, 'john@example.net'],
        ];
    }

    public function specialCharHeaderProvider(): array
    {
        return [
            [
                "To: =?UTF-8?B?dGVzdCxsYWJlbA==?= <john@example.com>, john2@example.com",
                ['john@example.com' => 'test,label', 'john2@example.com' => null],
                'UTF-8',
            ],
            [
                'To: "TEST\",QUOTE" <john@example.com>, john2@example.com',
                ['john@example.com' => 'TEST",QUOTE', 'john2@example.com' => null],
                'ASCII',
            ],
        ];
    }

    /**
     * @dataProvider specialCharHeaderProvider
     */
    public function testDeserializationFromSpecialCharString($headerLine, $expected, $encoding): void
    {
        $header = To::fromString($headerLine);

        $expectedTo = new To();
        $addressList = $expectedTo->getAddressList();
        $addressList->addMany($expected);
        $expectedTo->setEncoding($encoding);
        $this->assertEquals($expectedTo, $header);
        foreach ($expected as $k => $v) {
            $this->assertTrue($addressList->has($k));
            $this->assertEquals($addressList->get($k)->getName(), $v);
        }
    }
}

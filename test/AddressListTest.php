<?php

namespace LaminasTest\Mail;

use Countable;
use Laminas\Mail\Address;
use Laminas\Mail\AddressList;
use Laminas\Mail\Exception\InvalidArgumentException;
use Laminas\Mail\Header;
use PHPUnit\Framework\TestCase;
use Traversable;

use function count;

/**
 * @group      Laminas_Mail
 * @covers \Laminas\Mail\AddressList<extended>
 */
class AddressListTest extends TestCase
{
    private AddressList $list;

    public function setUp(): void
    {
        $this->list = new AddressList();
    }

    public function testImplementsCountable(): void
    {
        $this->assertInstanceOf(Countable::class, $this->list);
    }

    public function testIsEmptyByDefault(): void
    {
        $this->assertEquals(0, count($this->list));
    }

    public function testAddingEmailsIncreasesCount(): void
    {
        $this->list->add('test@example.com');
        $this->assertEquals(1, count($this->list));
    }

    public function testAddingEmailFromStringIncreasesCount(): void
    {
        $this->list->addFromString('test@example.com');
        $this->assertEquals(1, count($this->list));
    }

    public function testImplementsTraversable(): void
    {
        $this->assertInstanceOf(Traversable::class, $this->list);
    }

    public function testHasReturnsFalseWhenAddressNotInList(): void
    {
        $this->assertFalse($this->list->has('foo@example.com'));
    }

    public function testHasReturnsTrueWhenAddressInList(): void
    {
        $this->list->add('test@example.com');
        $this->assertTrue($this->list->has('test@example.com'));
    }

    public function testGetReturnsFalseWhenEmailNotFound(): void
    {
        $this->assertFalse($this->list->get('foo@example.com'));
    }

    public function testThrowExceptionOnInvalidInputAdd(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('add expects an email address or Laminas\Mail\Address object');
        $this->list->add(null);
    }

    public function testThrowExceptionOnInvalidInputAddMany(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('add expects an email address or Laminas\Mail\Address object');
        $this->list->addMany([null]);
    }

    public function testGetReturnsAddressObjectWhenEmailFound(): void
    {
        $this->list->add('test@example.com');
        $address = $this->list->get('test@example.com');
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('test@example.com', $address->getEmail());
    }

    public function testCanAddAddressWithName(): void
    {
        $this->list->add('test@example.com', 'Example Test');
        $address = $this->list->get('test@example.com');
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('test@example.com', $address->getEmail());
        $this->assertEquals('Example Test', $address->getName());
    }

    public function testCanAddManyAddressesAtOnce(): void
    {
        $addresses = [
            'test@example.com',
            'list@example.com' => 'Example List',
            new Address('announce@example.com', 'Announce List'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(3, count($this->list));
        $this->assertTrue($this->list->has('test@example.com'));
        $this->assertTrue($this->list->has('list@example.com'));
        $this->assertTrue($this->list->has('announce@example.com'));
    }

    public function testCanAddFromStringFluently(): void
    {
        $this->list->addFromString('test_fromstring_fluency1@example.com')
            ->addFromString('test_fromstring_fluency2@example.com');

        $this->assertTrue($this->list->has('test_fromstring_fluency1@example.com'));
        $this->assertTrue($this->list->has('test_fromstring_fluency2@example.com'));
    }

    public function testLosesParensInName(): void
    {
        $header = '"Supports (E-mail)" <support@example.org>';

        $to          = Header\To::fromString('To:' . $header);
        $addressList = $to->getAddressList();
        $address     = $addressList->get('support@example.org');
        $this->assertEquals('Supports', $address->getName());
        $this->assertEquals('E-mail', $address->getComment());
        $this->assertEquals('support@example.org', $address->getEmail());
    }

    public function testDoesNotStoreDuplicatesAndFirstWins(): void
    {
        $addresses = [
            'test@example.com',
            new Address('test@example.com', 'Example Test'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(1, count($this->list));
        $this->assertTrue($this->list->has('test@example.com'));
        $address = $this->list->get('test@example.com');
        $this->assertNull($address->getName());
    }

    /**
     * Microsoft Outlook sends emails with semicolon separated To addresses.
     *
     * @see https://blogs.msdn.microsoft.com/oldnewthing/20150119-00/?p=44883
     */
    public function testSemicolonSeparator(): void
    {
        $header = 'Some User <some.user@example.com>; uzer2.surname@example.org;'
            . ' asda.fasd@example.net, root@example.org';

        // In previous versions, this throws: 'The input exceeds the allowed
        // length'; hence the try/catch block, to allow finding the root cause.
        try {
            $to = Header\To::fromString('To:' . $header);
        } catch (InvalidArgumentException) {
            $this->fail('Header\To::fromString should not throw');
        }
        $addressList = $to->getAddressList();

        $this->assertEquals('Some User', $addressList->get('some.user@example.com')->getName());
        $this->assertTrue($addressList->has('uzer2.surname@example.org'));
        $this->assertTrue($addressList->has('asda.fasd@example.net'));
        $this->assertTrue($addressList->has('root@example.org'));
    }

    public function testMergeTwoLists(): void
    {
        $otherList = new AddressList();
        $this->list->add('one@example.net');
        $otherList->add('two@example.org');
        $this->list->merge($otherList);
        $this->assertEquals(2, count($this->list));
    }

    public function testDeleteSuccess(): void
    {
        $this->list->add('test@example.com');
        $this->assertTrue($this->list->delete('test@example.com'));
        $this->assertEquals(0, count($this->list));
    }

    public function testDeleteNotExist(): void
    {
        $this->assertFalse($this->list->delete('test@example.com'));
    }

    public function testKey(): void
    {
        $this->assertNull($this->list->key());
        $this->list->add('test@example.com');
        $this->list->add('test@example.net');
        $this->list->add('test@example.org');
        $this->list->rewind();
        $this->assertSame('test@example.com', $this->list->key());
        $this->list->next();
        $this->assertSame('test@example.net', $this->list->key());
        $this->list->next();
        $this->assertSame('test@example.org', $this->list->key());
    }

    /**
     * If name-field is quoted with "", then ' inside it should not treated as terminator, but as value.
     */
    public function testMixedQuotesInName(): void
    {
        $header = '"Bob O\'Reilly" <bob@example.com>,blah@example.com';

        // In previous versions, this throws:
        // 'Bob O'Reilly <bob@example.com>,blah' can not be matched against dot-atom format
        // hence the try/catch block, to allow finding the root cause.
        try {
            $to = Header\To::fromString('To:' . $header);
        } catch (InvalidArgumentException) {
            $this->fail('Header\To::fromString should not throw');
        }

        $addressList = $to->getAddressList();
        $this->assertTrue($addressList->has('bob@example.com'));
        $this->assertTrue($addressList->has('blah@example.com'));
        $this->assertEquals("Bob O'Reilly", $addressList->get('bob@example.com')->getName());
    }
}

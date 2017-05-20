<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Address;
use Zend\Mail\AddressList;
use Zend\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Header;

/**
 * @group      Zend_Mail
 * @covers \Zend\Mail\AddressList<extended>
 */
class AddressListTest extends TestCase
{
    /** @var AddressList */
    private $list;

    public function setUp()
    {
        $this->list = new AddressList();
    }

    public function testImplementsCountable()
    {
        $this->assertInstanceOf('Countable', $this->list);
    }

    public function testIsEmptyByDefault()
    {
        $this->assertEquals(0, count($this->list));
    }

    public function testAddingEmailsIncreasesCount()
    {
        $this->list->add('zf-devteam@zend.com');
        $this->assertEquals(1, count($this->list));
    }

    public function testImplementsTraversable()
    {
        $this->assertInstanceOf('Traversable', $this->list);
    }

    public function testHasReturnsFalseWhenAddressNotInList()
    {
        $this->assertFalse($this->list->has('foo@example.com'));
    }

    public function testHasReturnsTrueWhenAddressInList()
    {
        $this->list->add('zf-devteam@zend.com');
        $this->assertTrue($this->list->has('zf-devteam@zend.com'));
    }

    public function testGetReturnsFalseWhenEmailNotFound()
    {
        $this->assertFalse($this->list->get('foo@example.com'));
    }

    public function testGetReturnsAddressObjectWhenEmailFound()
    {
        $this->list->add('zf-devteam@zend.com');
        $address = $this->list->get('zf-devteam@zend.com');
        $this->assertInstanceOf('Zend\Mail\Address', $address);
        $this->assertEquals('zf-devteam@zend.com', $address->getEmail());
    }

    public function testCanAddAddressWithName()
    {
        $this->list->add('zf-devteam@zend.com', 'ZF DevTeam');
        $address = $this->list->get('zf-devteam@zend.com');
        $this->assertInstanceOf('Zend\Mail\Address', $address);
        $this->assertEquals('zf-devteam@zend.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testCanAddManyAddressesAtOnce()
    {
        $addresses = [
            'zf-devteam@zend.com',
            'zf-contributors@lists.zend.com' => 'ZF Contributors List',
            new Address('fw-announce@lists.zend.com', 'ZF Announce List'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(3, count($this->list));
        $this->assertTrue($this->list->has('zf-devteam@zend.com'));
        $this->assertTrue($this->list->has('zf-contributors@lists.zend.com'));
        $this->assertTrue($this->list->has('fw-announce@lists.zend.com'));
    }

    public function testLosesParensInName()
    {
        $header = '"Supports (E-mail)" <support@example.org>';

        $to = Header\To::fromString('To:' . $header);
        $addressList = $to->getAddressList();
        $address = $addressList->get('support@example.org');
        $this->assertEquals('Supports (E-mail)', $address->getName());
        $this->assertEquals('support@example.org', $address->getEmail());
    }

    public function testDoesNotStoreDuplicatesAndFirstWins()
    {
        $addresses = [
            'zf-devteam@zend.com',
            new Address('zf-devteam@zend.com', 'ZF DevTeam'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(1, count($this->list));
        $this->assertTrue($this->list->has('zf-devteam@zend.com'));
        $address = $this->list->get('zf-devteam@zend.com');
        $this->assertNull($address->getName());
    }

    /**
     * Microsoft Outlook sends emails with semicolon separated To addresses.
     *
     * @see https://blogs.msdn.microsoft.com/oldnewthing/20150119-00/?p=44883
     */
    public function testSemicolonSeparator()
    {
        $header = 'Some User <some.user@example.com>; uzer2.surname@example.org;'
            . ' asda.fasd@example.net, root@example.org';

        // In previous versions, this throws: 'The input exceeds the allowed
        // length'; hence the try/catch block, to allow finding the root cause.
        try {
            $to = Header\To::fromString('To:' . $header);
        } catch (InvalidArgumentException $e) {
            $this->fail('Header\To::fromString should not throw');
        }
        $addressList = $to->getAddressList();

        $this->assertEquals('Some User', $addressList->get('some.user@example.com')->getName());
        $this->assertTrue($addressList->has('uzer2.surname@example.org'));
        $this->assertTrue($addressList->has('asda.fasd@example.net'));
        $this->assertTrue($addressList->has('root@example.org'));
    }
}

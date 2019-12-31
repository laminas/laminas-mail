<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail\Address;
use Laminas\Mail\AddressList;

/**
 * @group      Laminas_Mail
 */
class AddressListTest extends \PHPUnit_Framework_TestCase
{
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
        $this->list->add('api-tools-devteam@zend.com');
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
        $this->list->add('api-tools-devteam@zend.com');
        $this->assertTrue($this->list->has('api-tools-devteam@zend.com'));
    }

    public function testGetReturnsFalseWhenEmailNotFound()
    {
        $this->assertFalse($this->list->get('foo@example.com'));
    }

    public function testGetReturnsAddressObjectWhenEmailFound()
    {
        $this->list->add('api-tools-devteam@zend.com');
        $address = $this->list->get('api-tools-devteam@zend.com');
        $this->assertInstanceOf('Laminas\Mail\Address', $address);
        $this->assertEquals('api-tools-devteam@zend.com', $address->getEmail());
    }

    public function testCanAddAddressWithName()
    {
        $this->list->add('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $address = $this->list->get('api-tools-devteam@zend.com');
        $this->assertInstanceOf('Laminas\Mail\Address', $address);
        $this->assertEquals('api-tools-devteam@zend.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testCanAddManyAddressesAtOnce()
    {
        $addresses = [
            'api-tools-devteam@zend.com',
            'api-tools-contributors@lists.zend.com' => 'Laminas Contributors List',
            new Address('fw-announce@lists.zend.com', 'Laminas Announce List'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(3, count($this->list));
        $this->assertTrue($this->list->has('api-tools-devteam@zend.com'));
        $this->assertTrue($this->list->has('api-tools-contributors@lists.zend.com'));
        $this->assertTrue($this->list->has('fw-announce@lists.zend.com'));
    }

    public function testDoesNotStoreDuplicatesAndFirstWins()
    {
        $addresses = [
            'api-tools-devteam@zend.com',
            new Address('api-tools-devteam@zend.com', 'Laminas DevTeam'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(1, count($this->list));
        $this->assertTrue($this->list->has('api-tools-devteam@zend.com'));
        $address = $this->list->get('api-tools-devteam@zend.com');
        $this->assertNull($address->getName());
    }
}

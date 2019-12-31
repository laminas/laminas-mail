<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Address;
use Laminas\Mail\AddressList;
use Laminas\Mail\Header\Bcc;
use Laminas\Mail\Header\Cc;
use Laminas\Mail\Header\From;
use Laminas\Mail\Header\ReplyTo;
use Laminas\Mail\Header\To;

/**
 * @group      Laminas_Mail
 */
class AddressListHeaderTest extends \PHPUnit_Framework_TestCase
{
    public static function getHeaderInstances()
    {
        return array(
            array(new Bcc(), 'Bcc'),
            array(new Cc(), 'Cc'),
            array(new From(), 'From'),
            array(new ReplyTo(), 'Reply-To'),
            array(new To(), 'To'),
        );
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeadersExtendAbstractAddressListHeader($header)
    {
        $this->assertInstanceOf('Laminas\Mail\Header\AbstractAddressList', $header);
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeaderFieldNamesAreDiscrete($header, $type)
    {
        $this->assertEquals($type, $header->getFieldName());
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testConcreteHeadersComposeAddressLists($header)
    {
        $list = $header->getAddressList();
        $this->assertInstanceOf('Laminas\Mail\AddressList', $list);
    }

    public function testFieldValueIsEmptyByDefault()
    {
        $header = new To();
        $this->assertEquals('', $header->getFieldValue());
    }

    public function testFieldValueIsCreatedFromAddressList()
    {
        $header = new To();
        $list   = $header->getAddressList();
        $this->populateAddressList($list);
        $expected = $this->getExpectedFieldValue();
        $this->assertEquals($expected, $header->getFieldValue());
    }

    public function populateAddressList(AddressList $list)
    {
        $address = new Address('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $list->add($address);
        $list->add('api-tools-contributors@lists.zend.com');
        $list->add('fw-announce@lists.zend.com', 'Laminas Announce List');
        $list->add('first@last.zend.com', 'Last, First');
    }

    public function getExpectedFieldValue()
    {
        return "Laminas DevTeam <api-tools-devteam@zend.com>,\r\n api-tools-contributors@lists.zend.com,\r\n Laminas Announce List <fw-announce@lists.zend.com>,\r\n \"Last, First\" <first@last.zend.com>";
    }

    /**
     * @dataProvider getHeaderInstances
     */
    public function testStringRepresentationIncludesHeaderAndFieldValue($header, $type)
    {
        $this->populateAddressList($header->getAddressList());
        $expected = sprintf('%s: %s', $type, $this->getExpectedFieldValue());
        $this->assertEquals($expected, $header->toString());
    }

    public function getStringHeaders()
    {
        $value = $this->getExpectedFieldValue();
        return array(
            'cc'       => array('Cc: ' . $value, 'Laminas\Mail\Header\Cc'),
            'bcc'      => array('Bcc: ' . $value, 'Laminas\Mail\Header\Bcc'),
            'from'     => array('From: ' . $value, 'Laminas\Mail\Header\From'),
            'reply-to' => array('Reply-To: ' . $value, 'Laminas\Mail\Header\ReplyTo'),
            'to'       => array('To: ' . $value, 'Laminas\Mail\Header\To'),
        );
    }

    /**
     * @dataProvider getStringHeaders
     */
    public function testDeserializationFromString($headerLine, $class)
    {
        $callback = sprintf('%s::fromString', $class);
        $header   = call_user_func($callback, $headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(4, count($list));
        $this->assertTrue($list->has('api-tools-devteam@zend.com'));
        $this->assertTrue($list->has('api-tools-contributors@lists.zend.com'));
        $this->assertTrue($list->has('fw-announce@lists.zend.com'));
        $this->assertTrue($list->has('first@last.zend.com'));
        $address = $list->get('api-tools-devteam@zend.com');
        $this->assertEquals('Laminas DevTeam', $address->getName());
        $address = $list->get('api-tools-contributors@lists.zend.com');
        $this->assertNull($address->getName());
        $address = $list->get('fw-announce@lists.zend.com');
        $this->assertEquals('Laminas Announce List', $address->getName());
        $address = $list->get('first@last.zend.com');
        $this->assertEquals('Last, First', $address->getName());
    }

    public function getStringHeadersWithNoWhitespaceSeparator()
    {
        $value = $this->getExpectedFieldValue();
        return array(
            'cc'       => array('Cc:' . $value, 'Laminas\Mail\Header\Cc'),
            'bcc'      => array('Bcc:' . $value, 'Laminas\Mail\Header\Bcc'),
            'from'     => array('From:' . $value, 'Laminas\Mail\Header\From'),
            'reply-to' => array('Reply-To:' . $value, 'Laminas\Mail\Header\ReplyTo'),
            'to'       => array('To:' . $value, 'Laminas\Mail\Header\To'),
        );
    }

    /**
     * @group 3789
     * @dataProvider getStringHeadersWithNoWhitespaceSeparator
     */
    public function testAllowsNoWhitespaceBetweenHeaderAndValue($headerLine, $class)
    {
        $callback = sprintf('%s::fromString', $class);
        $header   = call_user_func($callback, $headerLine);
        $this->assertInstanceOf($class, $header);
        $list = $header->getAddressList();
        $this->assertEquals(4, count($list));
        $this->assertTrue($list->has('api-tools-devteam@zend.com'));
        $this->assertTrue($list->has('api-tools-contributors@lists.zend.com'));
        $this->assertTrue($list->has('fw-announce@lists.zend.com'));
        $this->assertTrue($list->has('first@last.zend.com'));
        $address = $list->get('api-tools-devteam@zend.com');
        $this->assertEquals('Laminas DevTeam', $address->getName());
        $address = $list->get('api-tools-contributors@lists.zend.com');
        $this->assertNull($address->getName());
        $address = $list->get('fw-announce@lists.zend.com');
        $this->assertEquals('Laminas Announce List', $address->getName());
        $address = $list->get('first@last.zend.com');
        $this->assertEquals('Last, First', $address->getName());
    }
}

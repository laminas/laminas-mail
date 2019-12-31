<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail\Address;

/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage UnitTests
 * @group      Laminas_Mail
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNotRequireNameForInstantiation()
    {
        $address = new Address('api-tools-devteam@zend.com');
        $this->assertEquals('api-tools-devteam@zend.com', $address->getEmail());
        $this->assertNull($address->getName());
    }

    public function testAcceptsNameViaConstructor()
    {
        $address = new Address('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $this->assertEquals('api-tools-devteam@zend.com', $address->getEmail());
        $this->assertEquals('Laminas DevTeam', $address->getName());
    }

    public function testToStringCreatesStringRepresentation()
    {
        $address = new Address('api-tools-devteam@zend.com', 'Laminas DevTeam');
        $this->assertEquals('Laminas DevTeam <api-tools-devteam@zend.com>', $address->toString());
    }
}

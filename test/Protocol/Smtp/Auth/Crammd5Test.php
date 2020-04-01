<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Protocol\Smtp\Auth;

use Laminas\Mail\Protocol\Smtp\Auth\Crammd5;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Protocol\Smtp\Auth\Crammd5<extended>
 */
class Crammd5Test extends TestCase
{
    /**
     * @var Crammd5
     */
    protected $auth;

    public function setUp()
    {
        $this->auth = new Crammd5();
    }

    public function testHmacMd5ReturnsExpectedHash()
    {
        $class = new ReflectionClass('Laminas\Mail\Protocol\Smtp\Auth\Crammd5');
        $method = $class->getMethod('hmacMd5');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            $this->auth,
            ['frodo', 'speakfriendandenter']
        );

        $this->assertEquals('be56fa81a5671e0c62e00134180aae2c', $result);
    }

    public function testUsernameAccessors()
    {
        $this->auth->setUsername('test');
        $this->assertEquals('test', $this->auth->getUsername());
    }

    public function testPasswordAccessors()
    {
        $this->auth->setPassword('test');
        $this->assertEquals('test', $this->auth->getPassword());
    }
}

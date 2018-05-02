<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Protocol\Smtp\Auth;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zend\Mail\Protocol\Smtp\Auth\Crammd5;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Protocol\Smtp\Auth\Crammd5<extended>
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
        $class = new ReflectionClass('Zend\Mail\Protocol\Smtp\Auth\Crammd5');
        $method = $class->getMethod('hmacMd5');
        $method->setAccessible(true);

        $result = $method->invokeArgs(
            $this->auth,
            ['frodo', 'speakfriendandenter']
        );

        $this->assertEquals('be56fa81a5671e0c62e00134180aae2c', $result);
    }
}

<?php

namespace LaminasTest\Mail\Protocol\Smtp\Auth;

use Laminas\Mail\Exception\InvalidArgumentException;
use Laminas\Mail\Protocol\Smtp\Auth\Crammd5;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Protocol\Smtp\Auth\Crammd5<extended>
 */
class Crammd5Test extends TestCase
{
    /** @var Crammd5 */
    protected $auth;

    public function setUp(): void
    {
        $this->auth = new Crammd5();
    }

    public function testHmacMd5ReturnsExpectedHash(): void
    {
        $class  = new ReflectionClass(Crammd5::class);
        $method = $class->getMethod('hmacMd5');

        $result = $method->invokeArgs(
            $this->auth,
            ['frodo', 'speakfriendandenter']
        );

        $this->assertEquals('be56fa81a5671e0c62e00134180aae2c', $result);
    }

    public function testAnExceptionIsThrownForEmptyPassword(): void
    {
        $class  = new ReflectionClass(Crammd5::class);
        $method = $class->getMethod('hmacMd5');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CramMD5 authentication requires a non-empty password');
        $method->invokeArgs(
            $this->auth,
            ['', 'data']
        );
    }

    public function testAnExceptionIsThrownForEmptyChallenge(): void
    {
        $class  = new ReflectionClass(Crammd5::class);
        $method = $class->getMethod('hmacMd5');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CramMD5 authentication requires a non-empty challenge');
        $method->invokeArgs(
            $this->auth,
            ['foo', '']
        );
    }

    public function testUsernameAccessors(): void
    {
        $this->auth->setUsername('test');
        $this->assertEquals('test', $this->auth->getUsername());
    }

    public function testPasswordAccessors(): void
    {
        $this->auth->setPassword('test');
        $this->assertEquals('test', $this->auth->getPassword());
    }
}

<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Transport\Factory;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Stdlib\ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Transport\Factory<extended>
 */
class FactoryTest extends TestCase
{
    /**
     * @dataProvider invalidSpecTypeProvider
     * @expectedException \Laminas\Mail\Transport\Exception\InvalidArgumentException
     * @param $spec
     */
    public function testInvalidSpecThrowsInvalidArgumentException($spec)
    {
        Factory::create($spec);
    }

    public function invalidSpecTypeProvider()
    {
        return [
            ['spec'],
            [new \stdClass()],
        ];
    }

    /**
     *
     */
    public function testDefaultTypeIsSendmail()
    {
        $transport = Factory::create();

        $this->assertInstanceOf(Sendmail::class, $transport);
    }

    /**
     * @dataProvider typeProvider
     * @param $type
     */
    public function testCanCreateClassUsingTypeKey($type)
    {
        set_error_handler(function ($code, $message) {
            // skip deprecation notices
            return;
        }, E_USER_DEPRECATED);
        $transport = Factory::create([
            'type' => $type,
        ]);
        restore_error_handler();

        $this->assertInstanceOf($type, $transport);
    }

    public function typeProvider()
    {
        $types = [
            ['Laminas\Mail\Transport\File'],
            ['Laminas\Mail\Transport\InMemory'],
            ['Laminas\Mail\Transport\Sendmail'],
            ['Laminas\Mail\Transport\Smtp'],
        ];

        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $types[] = ['Laminas\Mail\Transport\Null'];
        }

        return $types;
    }

    /**
     * @dataProvider typeAliasProvider
     * @param $type
     * @param $expectedClass
     */
    public function testCanCreateClassFromTypeAlias($type, $expectedClass)
    {
        $transport = Factory::create([
            'type' => $type,
        ]);

        $this->assertInstanceOf($expectedClass, $transport);
    }

    public function typeAliasProvider()
    {
        return [
            ['file', 'Laminas\Mail\Transport\File'],
            ['null', 'Laminas\Mail\Transport\InMemory'],
            ['memory', 'Laminas\Mail\Transport\InMemory'],
            ['inmemory', 'Laminas\Mail\Transport\InMemory'],
            ['InMemory', 'Laminas\Mail\Transport\InMemory'],
            ['sendmail', 'Laminas\Mail\Transport\Sendmail'],
            ['smtp', 'Laminas\Mail\Transport\Smtp'],
            ['File', 'Laminas\Mail\Transport\File'],
            ['Null', 'Laminas\Mail\Transport\InMemory'],
            ['NULL', 'Laminas\Mail\Transport\InMemory'],
            ['Sendmail', 'Laminas\Mail\Transport\Sendmail'],
            ['SendMail', 'Laminas\Mail\Transport\Sendmail'],
            ['Smtp', 'Laminas\Mail\Transport\Smtp'],
            ['SMTP', 'Laminas\Mail\Transport\Smtp'],
        ];
    }

    /**
     *
     */
    public function testCanUseTraversableAsSpec()
    {
        $spec = new ArrayObject([
            'type' => 'null'
        ]);

        $transport = Factory::create($spec);

        $this->assertInstanceOf(InMemory::class, $transport);
    }

    /**
     * @dataProvider invalidClassProvider
     * @expectedException \Laminas\Mail\Transport\Exception\DomainException
     * @param $class
     */
    public function testInvalidClassThrowsDomainException($class)
    {
        Factory::create([
            'type' => $class
        ]);
    }

    public function invalidClassProvider()
    {
        return [
            ['stdClass'],
            ['non-existent-class'],
        ];
    }

    /**
     *
     */
    public function testCanCreateSmtpTransportWithOptions()
    {
        $transport = Factory::create([
            'type' => 'smtp',
            'options' => [
                'host' => 'somehost',
            ]
        ]);

        $this->assertEquals($transport->getOptions()->getHost(), 'somehost');
    }

    /**
     *
     */
    public function testCanCreateFileTransportWithOptions()
    {
        $transport = Factory::create([
            'type' => 'file',
            'options' => [
                'path' => __DIR__,
            ]
        ]);

        $this->assertEquals($transport->getOptions()->getPath(), __DIR__);
    }
}

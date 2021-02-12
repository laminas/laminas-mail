<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Composer\InstalledVersions;
use Laminas\Mail\Transport\Factory;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Stdlib\ArrayObject;
use PHPUnit\Framework\TestCase;
use Laminas\Mail\Transport\Exception;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\Smtp;

/**
 * @covers Laminas\Mail\Transport\Factory<extended>
 */
class FactoryTest extends TestCase
{
    /**
     * @dataProvider invalidSpecTypeProvider
     * @param $spec
     */
    public function testInvalidSpecThrowsInvalidArgumentException($spec): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        Factory::create($spec);
    }

    public function invalidSpecTypeProvider(): array
    {
        return [
            ['spec'],
            [new \stdClass()],
        ];
    }

    /**
     *
     */
    public function testDefaultTypeIsSendmail(): void
    {
        $transport = Factory::create();

        $this->assertInstanceOf(Sendmail::class, $transport);
    }

    /**
     * @dataProvider typeProvider
     * @param $type
     */
    public function testCanCreateClassUsingTypeKey($type): void
    {
        set_error_handler(function ($code, $message): void {
            // skip deprecation notices
        }, E_USER_DEPRECATED);
        $transport = Factory::create([
            'type' => $type,
        ]);
        restore_error_handler();

        $this->assertInstanceOf($type, $transport);
    }

    public function typeProvider(): array
    {
        $types = [
            [File::class],
            [InMemory::class],
            [Sendmail::class],
            [Smtp::class],
        ];

        return $types;
    }

    /**
     * @dataProvider typeAliasProvider
     * @param $type
     * @param $expectedClass
     */
    public function testCanCreateClassFromTypeAlias($type, $expectedClass): void
    {
        $transport = Factory::create([
            'type' => $type,
        ]);

        $this->assertInstanceOf($expectedClass, $transport);
    }

    public function typeAliasProvider(): array
    {
        return [
            ['file', File::class],
            ['memory', InMemory::class],
            ['inmemory', InMemory::class],
            ['InMemory', InMemory::class],
            ['sendmail', Sendmail::class],
            ['smtp', Smtp::class],
            ['File', File::class],
            ['null', InMemory::class],
            ['Null', InMemory::class],
            ['NULL', InMemory::class],
            ['Sendmail', Sendmail::class],
            ['SendMail', Sendmail::class],
            ['Smtp', Smtp::class],
            ['SMTP', Smtp::class],
        ];
    }

    /**
     *
     */
    public function testCanUseTraversableAsSpec(): void
    {
        if (class_exists(InstalledVersions::class)
            && version_compare(InstalledVersions::getVersion('laminas/laminas-stdlib'), '3.3.0') < 0
        ) {
            $this->markTestSkipped(
                'continue statement inside of switch causes errors when testing against stdlib < 3.3.0 versions'
            );
        }

        $spec = new ArrayObject([
            'type' => 'inMemory',
        ]);

        $transport = Factory::create($spec);

        $this->assertInstanceOf(InMemory::class, $transport);
    }

    /**
     * @dataProvider invalidClassProvider
     * @param $class
     */
    public function testInvalidClassThrowsDomainException($class): void
    {
        $this->expectException(Exception\DomainException::class);
        Factory::create([
            'type' => $class,
        ]);
    }

    public function invalidClassProvider(): array
    {
        return [
            ['stdClass'],
            ['non-existent-class'],
        ];
    }

    /**
     *
     */
    public function testCanCreateSmtpTransportWithOptions(): void
    {
        $transport = Factory::create([
            'type' => 'smtp',
            'options' => [
                'host' => 'somehost',
            ],
        ]);

        $this->assertEquals($transport->getOptions()->getHost(), 'somehost');
    }

    /**
     *
     */
    public function testCanCreateFileTransportWithOptions(): void
    {
        $transport = Factory::create([
            'type' => 'file',
            'options' => [
                'path' => __DIR__,
            ],
        ]);

        $this->assertEquals($transport->getOptions()->getPath(), __DIR__);
    }
}

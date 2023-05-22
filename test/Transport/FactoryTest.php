<?php

namespace LaminasTest\Mail\Transport;

use Composer\InstalledVersions;
use Laminas\Mail\Transport\Exception;
use Laminas\Mail\Transport\Factory;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\InMemory;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\Smtp;
use Laminas\Stdlib\ArrayObject;
use PHPUnit\Framework\TestCase;
use stdClass;

use function class_exists;
use function restore_error_handler;
use function set_error_handler;
use function version_compare;

use const E_USER_DEPRECATED;

/**
 * @covers Laminas\Mail\Transport\Factory<extended>
 */
class FactoryTest extends TestCase
{
    /**
     * @dataProvider invalidSpecTypeProvider
     */
    public function testInvalidSpecThrowsInvalidArgumentException(mixed $spec): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        Factory::create($spec);
    }

    public static function invalidSpecTypeProvider(): array
    {
        return [
            ['spec'],
            [new stdClass()],
        ];
    }

    public function testDefaultTypeIsSendmail(): void
    {
        $transport = Factory::create();

        $this->assertInstanceOf(Sendmail::class, $transport);
    }

    /**
     * @dataProvider typeProvider
     * @param class-string $type
     */
    public function testCanCreateClassUsingTypeKey(string $type): void
    {
        set_error_handler(static function ($code, $message): void {
            // skip deprecation notices
        }, E_USER_DEPRECATED);
        $transport = Factory::create([
            'type' => $type,
        ]);
        restore_error_handler();

        $this->assertInstanceOf($type, $transport);
    }

    public static function typeProvider(): array
    {
        return [
            [File::class],
            [InMemory::class],
            [Sendmail::class],
            [Smtp::class],
        ];
    }

    /**
     * @dataProvider typeAliasProvider
     * @param class-string $expectedClass
     */
    public function testCanCreateClassFromTypeAlias(string $type, string $expectedClass): void
    {
        $transport = Factory::create([
            'type' => $type,
        ]);

        $this->assertInstanceOf($expectedClass, $transport);
    }

    public static function typeAliasProvider(): array
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

    public function testCanUseTraversableAsSpec(): void
    {
        if (
            class_exists(InstalledVersions::class)
            && version_compare((string) InstalledVersions::getVersion('laminas/laminas-stdlib'), '3.3.0') < 0
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
     */
    public function testInvalidClassThrowsDomainException(string $class): void
    {
        $this->expectException(Exception\DomainException::class);
        Factory::create([
            'type' => $class,
        ]);
    }

    public static function invalidClassProvider(): array
    {
        return [
            ['stdClass'],
            ['non-existent-class'],
        ];
    }

    public function testCanCreateSmtpTransportWithOptions(): void
    {
        $transport = Factory::create([
            'type'    => 'smtp',
            'options' => [
                'host' => 'somehost',
            ],
        ]);

        $this->assertEquals($transport->getOptions()->getHost(), 'somehost');
    }

    public function testCanCreateFileTransportWithOptions(): void
    {
        $transport = Factory::create([
            'type'    => 'file',
            'options' => [
                'path' => __DIR__,
            ],
        ]);

        $this->assertEquals($transport->getOptions()->getPath(), __DIR__);
    }
}

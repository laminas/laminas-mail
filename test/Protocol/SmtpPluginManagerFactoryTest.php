<?php

namespace LaminasTest\Mail\Protocol;

// phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid
use Interop\Container\ContainerInterface;
use Laminas\Mail\Protocol\Smtp;
use Laminas\Mail\Protocol\SmtpPluginManager;
use Laminas\Mail\Protocol\SmtpPluginManagerFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function method_exists;

class SmtpPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new SmtpPluginManagerFactory();

        $plugins = $factory($container, SmtpPluginManager::class);
        $this->assertInstanceOf(SmtpPluginManager::class, $plugins);

        if (method_exists($plugins, 'configure')) {
            $reflectionClass         = new ReflectionClass($plugins);
            $creationContextProperty = $reflectionClass->getProperty('creationContext');

            // laminas-servicemanager v3
            $this->assertEquals($container, $creationContextProperty->getValue($plugins));
        } else {
            // laminas-servicemanager v2
            $this->assertSame($container, $plugins->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $smtp      = $this->createMock(Smtp::class);

        $factory = new SmtpPluginManagerFactory();
        $plugins = $factory($container, SmtpPluginManager::class, [
            'services' => [
                'test' => $smtp,
            ],
        ]);
        $this->assertSame($smtp, $plugins->get('test'));
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2(): void
    {
        $container = $this->createMock(ServiceLocatorInterface::class);

        $smtp = $this->createMock(Smtp::class);

        $factory = new SmtpPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $smtp,
            ],
        ]);

        $plugins = $factory->createService($container);
        $this->assertSame($smtp, $plugins->get('test'));
    }
}

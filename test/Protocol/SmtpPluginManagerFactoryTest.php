<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Protocol;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mail\Protocol\Smtp;
use Zend\Mail\Protocol\SmtpPluginManager;
use Zend\Mail\Protocol\SmtpPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class SmtpPluginManagerFactoryTest extends TestCase
{
    public function testFactoryReturnsPluginManager()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $factory = new SmtpPluginManagerFactory();

        $plugins = $factory($container, SmtpPluginManager::class);
        $this->assertInstanceOf(SmtpPluginManager::class, $plugins);

        if (method_exists($plugins, 'configure')) {
            // zend-servicemanager v3
            $this->assertAttributeSame($container, 'creationContext', $plugins);
        } else {
            // zend-servicemanager v2
            $this->assertSame($container, $plugins->getServiceLocator());
        }
    }

    /**
     * @depends testFactoryReturnsPluginManager
     */
    public function testFactoryConfiguresPluginManagerUnderContainerInterop()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $smtp = $this->prophesize(Smtp::class)->reveal();

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
    public function testFactoryConfiguresPluginManagerUnderServiceManagerV2()
    {
        $container = $this->prophesize(ServiceLocatorInterface::class);
        $container->willImplement(ContainerInterface::class);

        $smtp = $this->prophesize(Smtp::class)->reveal();

        $factory = new SmtpPluginManagerFactory();
        $factory->setCreationOptions([
            'services' => [
                'test' => $smtp,
            ],
        ]);

        $plugins = $factory->createService($container->reveal());
        $this->assertSame($smtp, $plugins->get('test'));
    }
}

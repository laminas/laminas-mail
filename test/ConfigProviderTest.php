<?php

namespace LaminasTest\Mail;

use Laminas\Mail\ConfigProvider;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * @group      Laminas_Mail
 * @covers \Laminas\Mail\ConfigProvider<extended>
 */
class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $config         = $configProvider();
        $this->assertEquals(['dependencies'], array_keys($config));
    }
}

<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail;

use Laminas\Mail\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers \Laminas\Mail\ConfigProvider<extended>
 */
class ConfigProviderTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $config = $configProvider();
        $this->assertEquals(['dependencies'], \array_keys($config));
    }
}

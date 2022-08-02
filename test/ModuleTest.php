<?php

namespace LaminasTest\Mail;

use Laminas\Mail\Module;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * @group      Laminas_Mail
 * @covers \Laminas\Mail\Module<extended>
 */
class ModuleTest extends TestCase
{
    public function testInvoke(): void
    {
        $module = new Module();
        $config = $module->getConfig();
        $this->assertEquals(['service_manager'], array_keys($config));
    }
}

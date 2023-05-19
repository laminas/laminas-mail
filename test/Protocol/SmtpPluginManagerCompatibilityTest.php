<?php

namespace LaminasTest\Mail\Protocol;

use Laminas\Mail\Protocol\Exception\InvalidArgumentException;
use Laminas\Mail\Protocol\Smtp;
use Laminas\Mail\Protocol\SmtpPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use PHPUnit\Framework\TestCase;

class SmtpPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected static function getPluginManager(): SmtpPluginManager
    {
        return new SmtpPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException(): string
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf(): string
    {
        return Smtp::class;
    }
}

<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Protocol;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Protocol\Smtp;
use Zend\Mail\Protocol\SmtpPluginManager;
use Zend\Mail\Protocol\Exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class SmtpPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new SmtpPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return Smtp::class;
    }
}

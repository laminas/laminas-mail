<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Transport\Null as NullTransport;

/**
 * @group      Laminas_Mail
 */
class NullTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $this->markTestSkipped('Cannot test Null transport under PHP 7; reserved keyword');
        }
    }

    public function testRaisesNoticeOnInstantiation()
    {
        $this->setExpectedException('PHPUnit_Framework_Error_Deprecated');
        new NullTransport();
    }
}

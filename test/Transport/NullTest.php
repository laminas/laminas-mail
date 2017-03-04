<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Transport;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Transport\Null as NullTransport;

/**
 * @group      Zend_Mail
 */
class NullTest extends TestCase
{
    public function setUp()
    {
        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $this->markTestSkipped('Cannot test Null transport under PHP 7; reserved keyword');
        }
    }

    public function testRaisesNoticeOnInstantiation()
    {
        $this->expectException('PHPUnit_Framework_Error_Deprecated');
        new NullTransport();
    }
}

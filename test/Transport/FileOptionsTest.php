<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Transport\FileOptions;

/**
 * @category   Laminas
 * @package    Laminas_Mail
 * @subpackage UnitTests
 * @group      Laminas_Mail
 */
class FileOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->options = new FileOptions();
    }

    public function testPathIsSysTempDirByDefault()
    {
        $this->assertEquals(sys_get_temp_dir(), $this->options->getPath());
    }

    public function testDefaultCallbackIsSetByDefault()
    {
        $callback = $this->options->getCallback();
        $this->assertTrue(is_callable($callback));
        $test     = call_user_func($callback, '');
        $this->assertRegExp('#^LaminasMail_\d+_\d+\.tmp$#', $test);
    }

    public function testPathIsMutable()
    {
        $original = $this->options->getPath();
        $this->options->setPath(__DIR__);
        $test     = $this->options->getPath();
        $this->assertNotEquals($original, $test);
        $this->assertEquals(__DIR__, $test);
    }

    public function testCallbackIsMutable()
    {
        $original = $this->options->getCallback();
        $new      = function($transport) {};
        $this->options->setCallback($new);
        $test     = $this->options->getCallback();
        $this->assertNotSame($original, $test);
        $this->assertSame($new, $test);
    }
}

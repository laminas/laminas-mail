<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Transport;

use Laminas\Mail\Exception;
use Laminas\Mail\Transport\FileOptions;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Transport\FileOptions<extended>
 */
class FileOptionsTest extends TestCase
{
    /** @var FileOptions  */
    private $options;

    public function setUp(): void
    {
        $this->options = new FileOptions();
    }

    public function testPathIsSysTempDirByDefault(): void
    {
        $this->assertEquals(sys_get_temp_dir(), $this->options->getPath());
    }

    public function testDefaultCallbackIsSetByDefault(): void
    {
        $callback = $this->options->getCallback();
        $this->assertIsCallable($callback);
        $test     = $callback('');
        $this->assertMatchesRegularExpression('#^LaminasMail_\d+_\d+\.eml$#', $test);
    }

    public function testPathIsMutable(): void
    {
        $original = $this->options->getPath();
        $this->options->setPath(__DIR__);
        $test     = $this->options->getPath();
        $this->assertNotEquals($original, $test);
        $this->assertEquals(__DIR__, $test);
    }

    public function testCallbackIsMutable(): void
    {
        $original = $this->options->getCallback();
        $new      = function ($transport): void {
        };

        $this->options->setCallback($new);
        $test = $this->options->getCallback();
        $this->assertNotSame($original, $test);
        $this->assertSame($new, $test);
    }

    public function testSetCallbackThrowsWhenNotCallable(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a valid callback');
        $this->options->setCallback(null);
    }

    public function testSetPathThrowsWhenPathNotWritable(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a valid path in which to write mail files');
        $this->options->setPath('/');
    }
}

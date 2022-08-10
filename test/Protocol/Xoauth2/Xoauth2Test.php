<?php

declare(strict_types=1);

namespace LaminasTest\Mail\Protocol\Xoauth2;

use Laminas\Mail\Protocol\Xoauth2\Xoauth2;
use PHPUnit\Framework\TestCase;

/**
 * @covers  Laminas\Mail\Protocol\Xoauth2\Xoauth2
 */
class Xoauth2Test extends TestCase
{
    /** @psalm-suppress InternalClass */
    public function testEncodeXoauth2Sasl(): void
    {
        /** @psalm-suppress InternalMethod */
        $this->assertEquals(
            'dXNlcj10ZXN0QGNvbnRvc28ub25taWNyb3NvZnQuY29tAWF1dGg9QmVhcmVyIEV3QkFBbDNCQUFVRkZwVUFvN0ozVmUwYmpMQldaV0NjbFJDM0VvQUEBAQ==',
            Xoauth2::encodeXoauth2Sasl(
                'test@contoso.onmicrosoft.com',
                'EwBAAl3BAAUFFpUAo7J3Ve0bjLBWZWCclRC3EoAA'
            )
        );
    }
}

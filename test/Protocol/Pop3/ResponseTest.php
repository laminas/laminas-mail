<?php

declare(strict_types=1);

namespace LaminasTest\Mail\Protocol\Pop3;

use Laminas\Mail\Protocol\Pop3\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Protocol\Pop3\Response
 */
class ResponseTest extends TestCase
{
     /** @psalm-suppress InternalClass */
    public function testIntegration(): void
    {
        /** @psalm-suppress InternalMethod */
        $response = new Response('+OK', 'Auth');

        /** @psalm-suppress InternalMethod */
        $this->assertEquals('+OK', $response->status());

        /** @psalm-suppress InternalMethod */
        $this->assertEquals('Auth', $response->message());
    }
}

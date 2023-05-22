<?php

namespace LaminasTest\Mail\Protocol;

use Laminas\Mail\Protocol\ProtocolTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers  Laminas\Mail\Protocol\ProtocolTrait
 */
class ProtocolTraitTest extends TestCase
{
    public function testTls12Version(): void
    {
        $mock = new class {
            use ProtocolTrait;
        };

        $this->assertNotEmpty(
            STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT & $mock->getCryptoMethod(),
            'TLSv1.2 must be present in crypto method list'
        );
    }
}

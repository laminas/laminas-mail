<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

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
        $mock = $this->getMockForTrait(ProtocolTrait::class);

        $this->assertNotEmpty(
            STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT & $mock->getCryptoMethod(),
            'TLSv1.2 must be present in crypto method list'
        );
    }
}

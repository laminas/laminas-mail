<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Protocol;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Protocol\ProtocolTrait;

/**
 * @covers  Zend\Mail\Protocol\ProtocolTrait
 */
class ProtocolTraitTest extends TestCase
{
    /**
     * @requires PHP 5.6.7
     */
    public function testTls12Version()
    {
        $mock = $this->getMockForTrait(ProtocolTrait::class);

        $this->assertNotEmpty(
            STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT & $mock->getCryptoMethod(),
            'TLSv1.2 must be present in crypto method list'
        );
    }
}

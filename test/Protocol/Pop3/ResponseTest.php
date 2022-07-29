<?php
declare(strict_types=1);

namespace LaminasTest\Mail\Protocol\Pop3;

use Laminas\Mail\Protocol\Pop3\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Protocol\Pop3\Response
 */
class ResponseTest  extends TestCase
{
    public function testIntegration(){
        $response = new Response('+OK', 'Auth');
        $this->assertEquals('+OK', $response->status());
        $this->assertEquals('Auth', $response->message());
    }
}

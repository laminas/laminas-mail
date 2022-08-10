<?php
declare(strict_types=1);

namespace LaminasTest\Mail\Protocol\Pop3\Xoauth2;

use Laminas\Mail\Protocol\Pop3\Response;
use Laminas\Mail\Protocol\Pop3\Xoauth2\Microsoft;
use Laminas\Mail\Protocol\Xoauth2\Xoauth2;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Mail\Protocol\Pop3\Xoauth2\Microsoft
 */
class MicrosoftTest extends TestCase
{
    public function testIntegration():void
    {
        $protocol = new class() extends Microsoft {

            /** @var string $step */
            private $step = '';

            public function __construct()
            {
                parent::__construct();
            }

            /** @psalm-suppress InternalClass */
            public function readRemoteResponse():Response
            {
                if ($this->step === self::AUTH_INITIALIZE_REQUEST) {
                    /** @psalm-suppress InternalMethod */
                    return new Response(self::AUTH_RESPONSE_INITIALIZED_OK, 'Auth initialized');
                }

                /** @psalm-suppress InternalMethod */
                return new Response('+OK', 'Authenticated');
            }

            public function sendRequest($request):void
            {
                $this->step = $request;
            }

            public function connect($host, $port = null, $ssl = false):void
            {
                $this->socket = fopen("php://memory", 'rw+');
            }
        };

        $protocol->connect('localhost', 0, false);

        $protocol->authenticate('test@example.com', '123');

        $this->assertInstanceOf(Microsoft::class, $protocol);
    }
}

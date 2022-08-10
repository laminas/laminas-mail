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
    public function testIntegration(): void
    {
        $protocol = new class() extends Microsoft {
            private string $step;

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
                parent::sendRequest($request);
            }

            public function connect($host, $port = null, $ssl = false):void
            {
                $this->socket = fopen("php://memory", 'rw+');
            }

            /**
             * @return null|resource
             */
            public function getSocket(){
                return $this->socket;
            }
        };

        $protocol->connect('localhost', 0, false);

        $protocol->login('test@example.com', '123');

        $this->assertInstanceOf(Microsoft::class, $protocol);

        $streamContents = '';
        if(($socket = $protocol->getSocket())){
            rewind($socket);
            $streamContents = stream_get_contents($socket);
            $streamContents = str_replace("\r\n", "\n", $streamContents);
        }

        $this->assertEquals(
            'AUTH XOAUTH2'."\n".Xoauth2::encodeXoauth2Sasl('test@example.com', '123')."\n",
            $streamContents
        );
    }
}

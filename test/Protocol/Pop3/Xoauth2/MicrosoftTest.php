<?php

declare(strict_types=1);

namespace LaminasTest\Mail\Protocol\Pop3\Xoauth2;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Protocol\Pop3\Response;
use Laminas\Mail\Protocol\Pop3\Xoauth2\Microsoft;
use Laminas\Mail\Protocol\Xoauth2\Xoauth2;
use PHPUnit\Framework\TestCase;

use function fopen;
use function rewind;
use function str_replace;
use function stream_get_contents;

/**
 * @covers Laminas\Mail\Protocol\Pop3\Xoauth2\Microsoft
 */
class MicrosoftTest extends TestCase
{
    /** @psalm-suppress InternalClass */
    public function testIntegration(): void
    {
        /**
         * @psalm-suppress PropertyNotSetInConstructor
         * @psalm-suppress InvalidExtendClass
         */
        $protocol = new class () extends Microsoft {
            private string $step;

            /** @psalm-suppress InternalClass */
            public function readRemoteResponse(): Response
            {
                if ($this->step === self::AUTH_INITIALIZE_REQUEST) {
                    /** @psalm-suppress InternalMethod */
                    return new Response(self::AUTH_RESPONSE_INITIALIZED_OK, 'Auth initialized');
                }

                /** @psalm-suppress InternalMethod */
                return new Response('+OK', 'Authenticated');
            }

            /**
             * Send a request
             *
             * @param string $request your request without newline
             * @throws RuntimeException
             */
            public function sendRequest($request): void
            {
                $this->step = $request;
                parent::sendRequest($request);
            }

            /**
             * Open connection to POP3 server
             *
             * @param  string      $host  hostname or IP address of POP3 server
             * @param  int|null    $port  of POP3 server, default is 110 (995 for ssl)
             * @param  string|bool $ssl   use 'SSL', 'TLS' or false
             * @throws RuntimeException
             * @return string welcome message
             */
            public function connect($host, $port = null, $ssl = false)
            {
                $this->socket = fopen("php://memory", 'rw+');
                return '';
            }

            /**
             * @return null|resource
             */
            public function getSocket()
            {
                return $this->socket;
            }
        };

        $protocol->connect('localhost', 0, false);

        $protocol->login('test@example.com', '123');

        $this->assertInstanceOf(Microsoft::class, $protocol);

        $streamContents = '';
        if ($socket = $protocol->getSocket()) {
            rewind($socket);
            $streamContents = stream_get_contents($socket);
            $streamContents = str_replace("\r\n", "\n", $streamContents);
        }

        /** @psalm-suppress InternalMethod */
        $xoauth2Sasl = Xoauth2::encodeXoauth2Sasl('test@example.com', '123');

        $this->assertEquals(
            'AUTH XOAUTH2' . "\n" . $xoauth2Sasl . "\n",
            $streamContents
        );
    }
}

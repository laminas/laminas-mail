<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 */

namespace LaminasTest\Mail\Protocol;

use Laminas\Mail\Protocol\AbstractProtocol;
use Laminas\Mail\Protocol\Exception\RuntimeException;
use Laminas\Mail\Protocol\ProtocolTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

use function strpos;

use const PHP_BINARY;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Protocol\AbstractProtocol<extended>
 */
final class AbstractProtocolTest extends TestCase
{
    /** @var Process */
    private $process;

    protected function setUp(): void
    {
        $this->process = new Process([
            PHP_BINARY,
            '-S',
            '127.0.0.1:8080',
            '-t',
            __DIR__ . '/HttpStatusService',
        ]);
        $this->process->start();
        $this->process->waitUntil(static function (string $type, string $output): bool {
            return false !== strpos($output, 'started');
        });
    }

    protected function tearDown(): void
    {
        $this->process->stop();
    }

    /**
     * @requires PHP >= 7.4
     */
    public function testExceptionShouldBeRaisedWhenConnectionHasTimedOut(): void
    {
        $protocol = new class ('127.0.0.1', 8080) extends AbstractProtocol {
            use ProtocolTrait;

            public function connect(): void
            {
                $this->_disconnect();
                $this->socket = $this->setupSocket('tcp', $this->host, $this->port, 2);
            }

            public function send(string $path, ?int $readTimeout): string
            {
                $this->_send('GET ' . $path . ' HTTP/1.1');
                $this->_send('Host: ' . $this->host);
                $this->_send('');

                return $this->_receive($readTimeout);
            }
        };

        $protocol->connect();
        self::assertSame('HTTP/1.1 200 OK' . AbstractProtocol::EOL, $protocol->send('/', null));

        $protocol->connect();
        $this->expectExceptionObject(new RuntimeException('127.0.0.1 has timed out'));
        $protocol->send('/?sleep=3', 1);
    }
}

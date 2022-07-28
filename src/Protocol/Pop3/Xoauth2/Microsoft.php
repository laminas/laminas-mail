<?php

namespace Laminas\Mail\Protocol\Pop3\Xoauth2;

use Laminas\Mail\Protocol\Exception\RuntimeException;
use Laminas\Mail\Storage\ParamsNormalizer;

class Microsoft extends \Laminas\Mail\Protocol\Pop3
{
    public function authenticate(string $targetMailbox, string $accessToken): void
    {
        $this->sendRequest('AUTH XOAUTH2');

        $response = $this->readRemoteResponse();

        if ($response->status() != '+') {
            throw new RuntimeException($response->message());
        }

        $this->request($this->buildXOauth2String(
            $targetMailbox,
            $accessToken
        ));
    }

    private function buildXOauth2String(string $targetMailbox, string $accessToken): string
    {
        return base64_encode(
            sprintf(
                "user=%s%sauth=Bearer %s%s%s",
                $targetMailbox,
                chr(0x01),
                $accessToken,
                chr(0x01),
                chr(0x01)
            )
        );
    }

    /**
     * create instance from parameters
     * Supported parameters are
     *   - host hostname or ip address of POP3 server
     *   - targetMailbox mail address of the mailbox to access
     *   - accessToken from client credentials OAUTH2 flow
     *   - port port for POP3 server [optional, default = 995]
     *   - ssl 'SSL' or 'TLS' for secure sockets
     *
     * @param  array|object $params mail reader specific parameters
     * @throws \Laminas\Mail\Protocol\Exception\RuntimeException
     */
    public static function fromParams($params): self
    {
        $params = ParamsNormalizer::normalizeParams($params);

        $host = $params['host'] ?? 'localhost';
        $targetMailbox = $params['targetMailbox'] ?? '';
        $accessToken = $params['accessToken'] ?? '';
        $port = $params['port'] ?? 995;
        $ssl = $params['ssl'] ?? 'SSL';

        if (null !== $port) {
            $port = (int) $port;
        }

        if (! is_string($ssl)) {
            $ssl = (bool) $ssl;
        }

        $protocol  = new self();

        $protocol->connect(
            (string) $host,
            $port,
            $ssl
        );

        $protocol->authenticate((string) $targetMailbox, (string) $accessToken);

        return $protocol;
    }
}

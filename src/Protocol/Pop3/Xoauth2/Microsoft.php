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
     * @param array{host: non-empty-string, targetMailbox: non-empty-string, accessToken: non-empty-string, port: int, ssl: string} $params
     * @throws \Laminas\Mail\Protocol\Exception\RuntimeException
     */
    public static function fromParams(array $params): self
    {
        $params = ParamsNormalizer::normalizeParams($params);

        $host = isset($params['host']) && is_string($params['host']) ? $params['host'] : 'localhost';
        $targetMailbox = isset($params['targetMailbox']) && is_string($params['targetMailbox']) ? $params['targetMailbox'] : '';
        $accessToken = isset($params['accessToken']) && is_string($params['accessToken']) ? $params['accessToken'] : '';
        $port = isset($params['port']) && is_int($params['port']) ? $params['port'] : 995;
        $ssl = isset($params['ssl']) && is_string($params['ssl']) ? $params['ssl'] : 'SSL';

        $protocol  = new self();

        $protocol->connect(
            $host,
            $port,
            $ssl
        );

        $protocol->authenticate($targetMailbox, $accessToken);

        return $protocol;
    }
}

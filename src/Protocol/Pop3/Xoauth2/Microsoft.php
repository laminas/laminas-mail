<?php

namespace Laminas\Mail\Protocol\Pop3\Xoauth2;

use Laminas\Mail\Protocol\Exception\RuntimeException;
use Laminas\Mail\Storage\ParamsNormalizer;

class Microsoft extends \Laminas\Mail\Protocol\Pop3
{
    protected const AUTH_INITIALIZE_REQUEST = 'AUTH XOAUTH2';
    protected const AUTH_RESPONSE_INITIALIZED_OK = '+';

    /**
     * xoauth2Sasl - XOUATH2 encoded client response
     * @param string $xoauth2Sasl
     * @return void
     */

    public function authenticate(string $xoauth2Sasl): void
    {
        $this->sendRequest(self::AUTH_INITIALIZE_REQUEST);

        $response = $this->readRemoteResponse();

        if ($response->status() != self::AUTH_RESPONSE_INITIALIZED_OK) {
            throw new RuntimeException($response->message());
        }

        $this->request($xoauth2Sasl);
    }
}

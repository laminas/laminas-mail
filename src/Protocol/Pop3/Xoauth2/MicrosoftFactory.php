<?php
declare(strict_types=1);

namespace Laminas\Mail\Protocol\Pop3\Xoauth2;

use Laminas\Mail\Protocol\Xoauth2\Xoauth2;

class MicrosoftFactory
{
    /**
     * create Microsoft POP3 XOUATH2 instance from parameters
     * supported parameters are
     *   - host hostname or ip address of POP3 server
     *   - targetMailbox mail address of the mailbox to access
     *   - accessToken from client credentials OAUTH2 flow
     *   - port port for POP3 server [optional, default = 995]
     *   - ssl 'SSL' or 'TLS' for secure sockets [optional, default = 'SSL']
     * @throws \Laminas\Mail\Protocol\Exception\RuntimeException
     */
    public static function getInstance(
        string $host,
        string $targetMailbox,
        string $accessToken,
        int $port = 995,
        string $ssl = 'SSL'
    ):Microsoft {
        $protocol  = new Microsoft();

        $protocol->connect(
            $host,
            $port,
            $ssl
        );

        $protocol->authenticate(Xoauth2::encodeXoauth2Sasl(
            $targetMailbox,
            $accessToken
        ));

        return $protocol;
    }
}

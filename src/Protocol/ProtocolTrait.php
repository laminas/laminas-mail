<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Protocol;

/**
 * https://bugs.php.net/bug.php?id=69195
 */
trait ProtocolTrait
{
    /**
     * If set to true, do not validate the SSL certificate
     * @var null|bool
     */
    protected $novalidatecert;

    public function getCryptoMethod()
    {
        // Allow the best TLS version(s) we can
        $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;

        // PHP 5.6.7 dropped inclusion of TLS 1.1 and 1.2 in STREAM_CRYPTO_METHOD_TLS_CLIENT
        // so add them back in manually if we can
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }

        return $cryptoMethod;
    }

    /**
     * Do not validate SSL certificate
     *
     * @param bool $novalidatecert Set to true to disable certificate validation
     *
     * @return Imap|Pop3
     */
    public function setNoValidateCert(bool $novalidatecert)
    {
        $this->novalidatecert = $novalidatecert;
        return $this;
    }

    /**
     * Should we validate SSL certificate?
     *
     * @return bool
     */
    public function validateCert()
    {
        return ! $this->novalidatecert;
    }

    /**
     * Prepare socket options
     *
     * @return array
     */
    protected function prepareSocketOptions()
    {
        return $this->novalidatecert
            ? [
                'ssl' => [
                    'verify_peer_name' => false,
                    'verify_peer'      => false,
                ]
            ] : [];
    }

    /**
     * Setup connection socket
     *
     * @param  string   $host hostname or IP address of IMAP server
     * @param  int|null $port of IMAP server, default is 143 (993 for ssl)
     *
     * @return void
     */
    protected function setSocket($host, $port)
    {
        $socketOptions = [];

        ErrorHandler::start();
        $this->socket = stream_socket_client(
            $host . ":" . $port,
            $errno,
            $errstr,
            self::TIMEOUT_CONNECTION,
            STREAM_CLIENT_CONNECT,
            stream_context_create($this->prepareSocketOptions())
        );

        $error = ErrorHandler::stop();
        if (! $this->socket) {
            throw new Exception\RuntimeException(sprintf(
                'cannot connect to host %s',
                ($error ? sprintf('; error = %s (errno = %d )', $error->getMessage(), $error->getCode()) : '')
            ), 0, $error);
        }
    }
}

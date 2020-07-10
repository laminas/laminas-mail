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
     * @return Imap
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
        return !$this->novalidatecert;
    }
}

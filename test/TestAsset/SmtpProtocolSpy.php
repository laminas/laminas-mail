<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\TestAsset;

use Laminas\Mail\Protocol\Smtp;

/**
 * Test spy to use when testing SMTP protocol
 */
class SmtpProtocolSpy extends Smtp
{
    public $calledQuit = false;
    protected $connect = false;
    protected $mail;
    protected $rcptTest = [];

    public function connect()
    {
        $this->connect = true;

        return true;
    }

    public function disconnect()
    {
        $this->connect = false;
        parent::disconnect();
    }

    public function quit()
    {
        $this->calledQuit = true;
        parent::quit();
    }

    public function rset()
    {
        parent::rset();
        $this->rcptTest = [];
    }

    public function rcpt($to)
    {
        parent::rcpt($to);
        $this->rcpt = true;
        $this->rcptTest[] = $to;
    }

    // @codingStandardsIgnoreStart
    protected function _send($request)
    {
        // Save request to internal log
        $this->_addLog($request . self::EOL);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    protected function _expect($code, $timeout = null)
    {
        return '';
    }
    // @codingStandardsIgnoreEnd

    /**
     * Are we connected?
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->connect;
    }

    /**
     * Get recipients
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->rcptTest;
    }

    /**
     * Get Auth Status
     *
     * @return bool
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Set Auth Status
     *
     * @param  bool $status
     * @return self
     */
    public function setAuth($status)
    {
        $this->auth = (bool) $status;

        return $this;
    }

    /**
     * Set Session Status
     *
     * @param  bool $status
     * @return self
     */
    public function setSessionStatus($status)
    {
        $this->sess = (bool) $status;

        return $this;
    }
}

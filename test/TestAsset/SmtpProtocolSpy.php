<?php

namespace LaminasTest\Mail\TestAsset;

use Laminas\Mail\Protocol\Smtp;

/**
 * Test spy to use when testing SMTP protocol
 */
class SmtpProtocolSpy extends Smtp
{
    /** @var bool */
    public $calledQuit = false;
    /** @var bool */
    protected $connect = false;
    /** @var string[] */
    protected $rcptTest = [];

    public function connect(): bool
    {
        $this->connect = true;

        return true;
    }

    public function disconnect(): void
    {
        $this->connect = false;
        parent::disconnect();
    }

    public function quit(): void
    {
        $this->calledQuit = true;
        parent::quit();
    }

    public function rset(): void
    {
        parent::rset();
        $this->rcptTest = [];
    }

    /**
     * {@inheritDoc}
     */
    public function rcpt($to): void
    {
        parent::rcpt($to);
        $this->rcpt       = true;
        $this->rcptTest[] = $to;
    }

    // @codingStandardsIgnoreStart
    protected function _send($request): void
    {
        // Save request to internal log
        $this->_addLog($request . self::EOL);
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    protected function _expect($code, $timeout = null): string
    {
        return '';
    }
    // @codingStandardsIgnoreEnd

    /**
     * Are we connected?
     */
    public function isConnected(): bool
    {
        return $this->connect;
    }

    /**
     * Get recipients
     *
     * @return array
     */
    public function getRecipients(): array
    {
        return $this->rcptTest;
    }

    /**
     * Get Auth Status
     */
    public function getAuth(): bool
    {
        return $this->auth;
    }

    /**
     * Set Auth Status
     *
     * @param  bool $status
     */
    public function setAuth($status): self
    {
        $this->auth = (bool) $status;

        return $this;
    }

    /**
     * Set Session Status
     *
     * @param  bool $status
     */
    public function setSessionStatus($status): self
    {
        $this->sess = (bool) $status;

        return $this;
    }
}

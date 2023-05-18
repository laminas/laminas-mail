<?php

namespace LaminasTest\Mail\TestAsset;

use Laminas\Mail\Protocol\Smtp;

/**
 * Test spy to use when testing SMTP protocol
 */
class SmtpProtocolSpy extends Smtp
{
    public const ERRONEOUS_RECIPIENT               = 'nosuchuser@example.com';
    public const ERRONEOUS_RECIPIENT_CODE          = '550';
    public const ERRONEOUS_RECIPIENT_ENHANCED_CODE = '5.1.1';
    public const ERRONEOUS_RECIPIENT_MESSAGE       = 'Mailbox "nosuchuser" does not exist';
    public const ERRONEOUS_RECIPIENT_RESPONSE      = self::ERRONEOUS_RECIPIENT_CODE . ' '
                                                . self::ERRONEOUS_RECIPIENT_ENHANCED_CODE . ' '
                                                . self::ERRONEOUS_RECIPIENT_MESSAGE;

    /** @var bool */
    public $calledQuit = false;
    /** @var bool */
    protected $connect = false;
    /** @var string[] */
    protected $rcptTest            = [];
    protected bool $useReceive     = false;
    protected string $fakeResponse = '';

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
        if ($to === self::ERRONEOUS_RECIPIENT) {
            $this->setFakeResponse(self::ERRONEOUS_RECIPIENT_RESPONSE);
            try {
                parent::rcpt($to);
            } finally {
                $this->resetFakeResponse();
            }
        } else {
            parent::rcpt($to);
        }

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
        if ($this->useReceive) {
            return parent::_expect($code, $timeout);
        }
        return '';
    }
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    protected function _receive($timeout = null): string
    {
        return $this->fakeResponse;
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

    /**
     * @return $this
     */
    protected function setFakeResponse(string $response): self
    {
        $this->useReceive   = true;
        $this->fakeResponse = $response;
        return $this;
    }

    /**
     * @return $this
     */
    protected function resetFakeResponse(): self
    {
        $this->useReceive   = false;
        $this->fakeResponse = '';
        return $this;
    }
}

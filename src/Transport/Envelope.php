<?php

namespace Laminas\Mail\Transport;

use Laminas\Stdlib\AbstractOptions;

class Envelope extends AbstractOptions
{
    /** @var string */
    protected $from = '';

    /** @var string */
    protected $to = '';

    /**
     * Get MAIL FROM
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set MAIL FROM
     *
     * @param  string $from
     */
    public function setFrom($from)
    {
        $this->from = (string) $from;
    }

    /**
     * Get RCPT TO
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set RCPT TO
     *
     * @param  string $to
     */
    public function setTo($to)
    {
        $this->to = (string)$to;
    }
}

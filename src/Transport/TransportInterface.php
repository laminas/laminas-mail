<?php

declare(strict_types=1);

namespace Laminas\Mail\Transport;

use Laminas\Mail;

/**
 * Interface for mail transports
 */
interface TransportInterface
{
    /**
     * Send a mail message
     *
     * @return
     */
    public function send(Mail\Message $message);
}

<?php

declare(strict_types=1);

namespace LaminasTest\Mail\Storage\TestAsset;

use Laminas\Mail\Storage\Maildir;
use Laminas\Mail\Storage\Message;

/**
 * Maildir class, which uses old message class
 */
class MaildirOldMessage extends Maildir
{
    /**
     * used message class
     *
     * @var string
     */
    protected $messageClass = Message::class;
}

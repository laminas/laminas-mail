<?php

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
     * @var class-string<Message>
     */
    protected $messageClass = Message::class;
}

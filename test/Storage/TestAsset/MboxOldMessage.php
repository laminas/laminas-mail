<?php

namespace LaminasTest\Mail\Storage\TestAsset;

use Laminas\Mail\Storage\Mbox;
use Laminas\Mail\Storage\Message;

/**
 * Maildir class, which uses old message class
 */
class MboxOldMessage extends Mbox
{
    /**
     * used message class
     *
     * @var class-string<Message\MessageInterface>
     */
    protected $messageClass = Message::class;
}

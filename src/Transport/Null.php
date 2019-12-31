<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Transport;

use Laminas\Mail\Message;

/**
 * File transport
 *
 * The null transport will just store the message in memory.  It is helpful
 * when unit testing.
 */
class Null implements TransportInterface
{
    /**
     * @var Message
     */
    protected $lastMessage;

    /**
     * Takes the last message and Saves it for testing
     *
     * @param Message $message
     */
    public function send(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * Get the last message sent
     *
     * @return Message
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}

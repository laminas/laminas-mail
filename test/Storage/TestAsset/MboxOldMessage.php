<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Storage\TestAsset;

use Laminas\Mail\Storage\Mbox;
use Laminas\Mail\Storage\Message;

/**
 * Maildir class, which uses old message class
 */
class MboxOldMessage extends Mbox
{
    // @codingStandardsIgnoreStart
    /**
     * used message class
     * @var string
     */
    protected $_messageClass = Message::class;
    // @codingStandardsIgnoreEnd
}

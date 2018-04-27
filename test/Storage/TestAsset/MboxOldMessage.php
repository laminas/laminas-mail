<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Storage\TestAsset;

use Zend\Mail\Storage\Message;
use Zend\Mail\Storage\Mbox;

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

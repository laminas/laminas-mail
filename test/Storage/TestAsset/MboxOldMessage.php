<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Storage\TestAsset;

use Zend\Mail\Storage\Mbox;
use Zend\Mail\Storage\Message;

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

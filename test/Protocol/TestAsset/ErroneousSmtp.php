<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Protocol\TestAsset;

use Zend\Mail\Protocol\AbstractProtocol;

/**
 * Expose AbstractProtocol behaviour
 */
final class ErroneousSmtp extends AbstractProtocol
{
    public function connect($customRemote = null)
    {
        return $this->_connect($customRemote);
    }
}

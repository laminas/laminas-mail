<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Protocol\TestAsset;

use Laminas\Mail\Protocol\AbstractProtocol;

/**
 * Expose AbstractProtocol behaviour
 */
final class ErroneousSmtp extends AbstractProtocol
{
    public function connect($customRemote = null): bool
    {
        return $this->_connect($customRemote);
    }
}

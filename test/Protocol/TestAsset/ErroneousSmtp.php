<?php

declare(strict_types=1);

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

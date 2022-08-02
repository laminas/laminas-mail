<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

class Bcc extends AbstractAddressList
{
    /** @var string */
    protected $fieldName = 'Bcc';

    /** @var string */
    protected static $type = 'bcc';
}

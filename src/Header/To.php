<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

class To extends AbstractAddressList
{
    protected $fieldName   = 'To';
    protected static $type = 'to';
}

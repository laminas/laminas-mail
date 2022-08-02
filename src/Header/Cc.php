<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

class Cc extends AbstractAddressList
{
    protected $fieldName   = 'Cc';
    protected static $type = 'cc';
}

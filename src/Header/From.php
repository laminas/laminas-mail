<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

class From extends AbstractAddressList
{
    protected $fieldName   = 'From';
    protected static $type = 'from';
}

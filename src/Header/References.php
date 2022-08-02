<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

class References extends IdentificationField
{
    protected $fieldName   = 'References';
    protected static $type = 'references';
}

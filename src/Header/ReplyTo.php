<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

class ReplyTo extends AbstractAddressList
{
    protected $fieldName   = 'Reply-To';
    protected static $type = 'reply-to';
}

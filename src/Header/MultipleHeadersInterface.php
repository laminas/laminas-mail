<?php

declare(strict_types=1);

namespace Laminas\Mail\Header;

interface MultipleHeadersInterface extends HeaderInterface
{
    public function toStringMultipleHeaders(array $headers);
}

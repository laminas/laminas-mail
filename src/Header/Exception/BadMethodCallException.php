<?php

declare(strict_types=1);

namespace Laminas\Mail\Header\Exception;

use Laminas\Mail\Exception;

class BadMethodCallException extends Exception\BadMethodCallException implements ExceptionInterface
{
}

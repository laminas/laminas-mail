<?php

declare(strict_types=1);

namespace Laminas\Mail\Protocol\Exception;

use Laminas\Mail\Exception;

/**
 * Exception for Laminas\Mail component.
 */
class InvalidArgumentException extends Exception\InvalidArgumentException implements ExceptionInterface
{
}

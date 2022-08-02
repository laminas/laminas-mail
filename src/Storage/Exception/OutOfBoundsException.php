<?php

declare(strict_types=1);

namespace Laminas\Mail\Storage\Exception;

use Laminas\Mail\Exception;

/**
 * Exception for Laminas\Mail component.
 */
class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
}

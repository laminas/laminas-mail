<?php

declare(strict_types=1);

namespace Laminas\Mail\Storage\Exception;

use Laminas\Mail\Exception;

/**
 * Exception for Laminas\Mail component.
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}

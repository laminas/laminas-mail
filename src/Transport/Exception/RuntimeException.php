<?php

declare(strict_types=1);

namespace Laminas\Mail\Transport\Exception;

use Laminas\Mail\Exception;

/**
 * Exception for Laminas\Mail component.
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}

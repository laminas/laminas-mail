<?php

declare(strict_types=1);

namespace Laminas\Mail\Storage\Part\Exception;

use Laminas\Mail\Storage\Exception;

/**
 * Exception for Laminas\Mail component.
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}

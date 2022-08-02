<?php

declare(strict_types=1);

namespace Laminas\Mail\Transport\Exception;

use Laminas\Mail\Exception;

/**
 * Exception for Laminas\Mail\Transport component.
 */
class DomainException extends Exception\DomainException implements ExceptionInterface
{
}

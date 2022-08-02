<?php

declare(strict_types=1);

namespace Laminas\Mail\Exception;

/**
 * Exception for Laminas\Mail component.
 */
class BadMethodCallException extends \BadMethodCallException implements
    ExceptionInterface
{
}

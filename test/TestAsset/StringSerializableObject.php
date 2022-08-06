<?php

namespace LaminasTest\Mail\TestAsset;

use Stringable;

class StringSerializableObject implements Stringable
{
    public function __construct(private string $message)
    {
    }

    public function __toString(): string
    {
        return $this->message;
    }
}

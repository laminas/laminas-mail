<?php

namespace LaminasTest\Mail\TestAsset;

class StringSerializableObject
{
    /** @var string */
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }
}

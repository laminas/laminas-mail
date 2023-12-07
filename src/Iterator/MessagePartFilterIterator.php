<?php

declare(strict_types=1);

namespace Laminas\Mail\Iterator;

use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use RecursiveFilterIterator;
use RecursiveIterator;

use function str_starts_with;

class MessagePartFilterIterator extends RecursiveFilterIterator
{
    private string $partType;

    public function __construct(RecursiveIterator $iterator, string $partType)
    {
        parent::__construct($iterator);

        $this->partType = $partType;
    }

    public function accept(): bool
    {
        if ($this->hasChildren()) {
            return true;
        }

        /** @var Part $part */
        $part = $this->current();
        return str_starts_with($part->getType(), $this->partType);
    }

    public function hasChildren(): bool
    {
        return $this->getInnerIterator()->hasChildren();
    }

    public function getChildren(): MessagePartFilterIterator
    {
        return new self(
            $this->getInnerIterator()->getChildren(),
            $this->partType
        );
    }
}

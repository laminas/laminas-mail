<?php

declare(strict_types=1);

namespace Laminas\Mail\Iterator;

use Laminas\Mime\Part;
use RecursiveFilterIterator;

use function str_starts_with;

class AttachmentPartFilterIterator extends RecursiveFilterIterator
{
    public function accept(): bool
    {
        if ($this->hasChildren()) {
            return true;
        }

        /** @var Part $part */
        $part = $this->current();

        return str_starts_with((string)$part->getDisposition(), "attachment");
    }

    public function hasChildren(): bool
    {
        return $this->getInnerIterator()->hasChildren();
    }

    public function getChildren(): AttachmentPartFilterIterator
    {
        return new self($this->getInnerIterator()->getChildren());
    }
}

<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Limit extends Part
{
    public function __construct(protected int $count, protected int $offset = 0)
    {
        
    }

    public function toLimit(): string
    {
        $parts = [];

        if ($this->count) {
            $parts[] = sprintf('LIMIT %d', $this->count);
        }

        if ($this->offset) {
            $parts[] = sprintf('OFFSET %d', $this->offset);
        }

        return implode(' ', $parts);
    }

}
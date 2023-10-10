<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Group extends Part
{
    public function __construct(protected string $expression)
    {
        
    }

    public function toGroup(): string
    {
        return $this->expression;
    }
}
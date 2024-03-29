<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Order extends Part
{
    public function __construct(protected string $expression)
    {
        
    }

    public function toOrder(): string
    {
        return $this->expression;
    }

}
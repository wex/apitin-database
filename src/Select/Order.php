<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Order extends Part
{
    public function __construct(protected string $expression)
    {
        
    }

    public function toOrder(): string
    {
        if (is_null($this->value)) {

            return $this->expression;

        } else {

            return str_replace(
                '?',
                $this->quoteValue($this->value),
                $this->expression
            );

        }
    }

}
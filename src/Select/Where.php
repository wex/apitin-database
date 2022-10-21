<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Where extends Part
{
    public function __construct(protected string $expression, protected $value = null)
    {
        
    }

    public function toWhere(): string
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
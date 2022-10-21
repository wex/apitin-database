<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Expression extends Part
{
    public function __construct(protected string $field, protected ?string $alias = null, protected bool $raw = false)
    {
        
    }

    public function toExpression(): string
    {
        if ($this->raw) {
            return $this->field;
        }

        if (!is_null($this->alias)) {
            return sprintf("%s AS %s",
                $this->quoteIdentifier($this->field),
                $this->quoteIdentifier($this->alias)
            );
        }

        return $this->quoteIdentifier($this->field);
    }

}
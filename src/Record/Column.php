<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;
use BadMethodCallException;
use DateTimeImmutable;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Column extends ColumnAttribute
{
    public function from($value)
    {
        return match($this->type) {
            static::TYPE_STRING     => (string) $value,
            static::TYPE_TEXT       => $value,
            static::TYPE_INTEGER    => intval($value),
            static::TYPE_DECIMAL    => floatval($value),
            static::TYPE_BOOLEAN    => (bool) $value,
            static::TYPE_DATETIME   => new DateTimeImmutable($value),
            static::TYPE_DATE       => new DateTimeImmutable($value),
            static::TYPE_FOREIGNKEY => $value ? intval($value) : null,
            static::TYPE_VIRTUAL    => $value,
            default => throw new BadMethodCallException(sprintf(
                "Type '%s' is not supported",
                $this->type
            ))
        };
    }

    public function to($value)
    {
        return match($this->type) {
            static::TYPE_STRING     => (string) $value,
            static::TYPE_TEXT       => $value,
            static::TYPE_INTEGER    => intval($value),
            static::TYPE_DECIMAL    => floatval($value),
            static::TYPE_BOOLEAN    => (bool) $value,
            static::TYPE_DATETIME   => $value->format('Y-m-d H:i:s'),
            static::TYPE_DATE       => $value->format('Y-m-d'),
            static::TYPE_FOREIGNKEY => $value ? intval($value) : null,
            static::TYPE_VIRTUAL    => $value,
            default => throw new BadMethodCallException(sprintf(
                "Type '%s' is not supported",
                $this->type
            ))
        };
    }
}

<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;
use BadMethodCallException;
use DateTimeImmutable;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column extends ColumnAttribute
{
    public function from($value)
    {
        return match($this->type) {
            static::TYPE_STRING     => is_null($value) ? null : $value,
            static::TYPE_TEXT       => is_null($value) ? null : $value,
            static::TYPE_INTEGER    => is_null($value) ? null : intval($value),
            static::TYPE_DECIMAL    => is_null($value) ? null : floatval($value),
            static::TYPE_BOOLEAN    => is_null($value) ? null : (bool) $value,
            static::TYPE_DATETIME   => is_null($value) ? null : new DateTimeImmutable($value),
            static::TYPE_DATE       => is_null($value) ? null : new DateTimeImmutable($value),
            static::TYPE_FOREIGNKEY => is_null($value) ? null : intval($value),
            static::TYPE_VIRTUAL    => $value,
            static::TYPE_HASMANY    => $value,
            default => throw new BadMethodCallException(sprintf(
                "Type '%s' is not supported",
                $this->type
            ))
        };
    }

    public function to($value)
    {
        return match($this->type) {
            static::TYPE_STRING     => is_null($value) ? null : $value,
            static::TYPE_TEXT       => is_null($value) ? null : $value,
            static::TYPE_INTEGER    => is_null($value) ? null : intval($value),
            static::TYPE_DECIMAL    => is_null($value) ? null : floatval($value),
            static::TYPE_BOOLEAN    => is_null($value) ? null : (int) $value,
            static::TYPE_DATETIME   => is_null($value) ? null : $value->format('Y-m-d H:i:s'),
            static::TYPE_DATE       => is_null($value) ? null : $value->format('Y-m-d'),
            static::TYPE_FOREIGNKEY => is_null($value) ? null : intval($value),
            static::TYPE_VIRTUAL    => $value,
            static::TYPE_HASMANY    => $value,
            default => throw new BadMethodCallException(sprintf(
                "Type '%s' is not supported",
                $this->type
            ))
        };
    }
}

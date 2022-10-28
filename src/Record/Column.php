<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;
use BadMethodCallException;
use DateTimeImmutable;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Column
{
    const   TYPE_STRING     = 'string';
    const   TYPE_INTEGER    = 'int';
    const   TYPE_DECIMAL    = 'decimal';
    const   TYPE_BOOLEAN    = 'bool';
    const   TYPE_DATETIME   = 'datetime';
    const   TYPE_DATE       = 'date';

    public function __construct(
        public string $name,
        public string $type = self::TYPE_STRING,
        public bool $required = false,
        public mixed $default = null
    )
    {
        
    }

    public function from($value)
    {
        return match($this->type) {
            static::TYPE_STRING     => (string) $value,
            static::TYPE_INTEGER    => intval($value),
            static::TYPE_DECIMAL    => floatval($value),
            static::TYPE_BOOLEAN    => (bool) $value,
            static::TYPE_DATETIME   => new DateTimeImmutable($value),
            static::TYPE_DATE       => new DateTimeImmutable($value),
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
            static::TYPE_INTEGER    => intval($value),
            static::TYPE_DECIMAL    => floatval($value),
            static::TYPE_BOOLEAN    => (bool) $value,
            static::TYPE_DATETIME   => $value->format('Y-m-d H:i:s'),
            static::TYPE_DATE       => $value->format('Y-m-d'),
            default => throw new BadMethodCallException(sprintf(
                "Type '%s' is not supported",
                $this->type
            ))
        };
    }
}

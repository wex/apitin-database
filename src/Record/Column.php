<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;
use BadMethodCallException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Column
{
    const   TYPE_STRING     = 'string';
    const   TYPE_INTEGER    = 'int';
    const   TYPE_DECIMAL    = 'decimal';
    const   TYPE_BOOLEAN    = 'bool';

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
        switch ($this->type) {
            case static::TYPE_STRING: 
                return (string) $value;

            case static::TYPE_INTEGER:
                return intval($value);

            case static::TYPE_DECIMAL:
                return floatval($value);

            case static::TYPE_BOOLEAN:
                return !!$value;
        }

        throw new BadMethodCallException(sprintf(
            "Type '%s' is not supported",
            $this->type
        ));
    }

    public function to($value)
    {
        switch ($this->type) {
            case static::TYPE_STRING: 
                return "{$value}";

            case static::TYPE_INTEGER:
                return intval($value);

            case static::TYPE_DECIMAL:
                return floatval($value);

            case static::TYPE_BOOLEAN:
                return $value ? 1 : 0;
        }

        throw new BadMethodCallException(sprintf(
            "Type '%s' is not supported",
            $this->type
        ));
    }
}

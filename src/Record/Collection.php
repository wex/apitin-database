<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;
use BadMethodCallException;
use LogicException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Collection extends ColumnAttribute
{
    const   TYPE_JSON       = 'json';

    public function __construct(
        public string $instance,
        public string $type = self::TYPE_JSON
    )
    {
        
    }

    public function bind(ColumnAttribute $column, string $property, string $instance)
    {
        $instance::onGet($property, function($instance) use ($property) {
            $instance->$property = json_decode($instance->$property ?? '[]');
        });

        $instance::onSet($property, function($instance, $value) use ($property) {
            if (is_array($value)) {
                $instance->$property = json_encode($value);
            } else if (is_null($value)) {
                $instance->$property = json_encode([]);
            } else {
                throw new BadMethodCallException(sprintf(
                    "Expected array - got '%s'",
                    gettype($value)
                ));
            }
        });

        $instance::onLoad(function($instance) use ($property) {
            $instance->store[$property] = json_decode($instance->$property ?? '[]');
        });
    }
}
<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\Record;
use Attribute;
use BadMethodCallException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Relation extends ColumnAttribute
{
    public function __construct(
        public string $name,
        public string $record
    )
    {
        
    }


    public function bind(ColumnAttribute $column, string $property, string $instance)
    {
        $relatedClass   = $column->record;
        $foreignColumn  = $column->name;
        $virtualColumn  = $property;

        $instance::onLoad(function($instance) use ($foreignColumn, $virtualColumn, $relatedClass) {
            if ($instance->$foreignColumn) {
                $instance->store[ $virtualColumn ] = $relatedClass::load($instance->$foreignColumn);
            } else {
                $instance->store[ $virtualColumn ] = null;
            }
        });

        $instance::onSet($property, function($instance, $value) use ($relatedClass, $foreignColumn) {
            if ($value instanceof Record) {
                if (!($value instanceof $relatedClass)) throw new BadMethodCallException(sprintf(
                    "Expected type '%s' - got '%s'",
                    $relatedClass,
                    get_class($value)
                ));
                $relatedPK = $relatedClass::getPrimaryKey();

                $instance->$foreignColumn = $value->$relatedPK;
            } else if (is_null($value)) {
                $instance->$foreignColumn = $value;
            } else {
                throw new BadMethodCallException(sprintf(
                    "Expected type '%s' - got '%s'",
                    $relatedClass,
                    gettype($value)
                ));
            }
        });

    }
}
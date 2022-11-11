<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\JsonCollection;
use Apitin\Database\JsonStore;
use Attribute;
use BadMethodCallException;
use LogicException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Store extends ColumnAttribute
{
    public function __construct(
        public string $instance
    )
    {
        
    }

    public function bind(ColumnAttribute $column, string $property, string $instance)
    {
        $collects = $column->instance;

        $instance::onLoad(function($instance) use ($collects, $property) {

            $instance->store[$property] = new JsonStore($instance, $property, $collects);
            
        });

        $instance::onSave(function($instance) use ($property) {

            $instance->store[$property] = $instance->store[$property]->save();

        });
    }
}
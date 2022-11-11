<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\FixedCollection;
use Attribute;
use BadMethodCallException;
use LogicException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Collection extends ColumnAttribute
{
    const   TYPE_FIXED      = 'fixed';
    const   TYPE_LAZY       = 'lazy';
    const   TYPE_JSON       = 'json';

    public function __construct(
        public string $name,
        public string $instance,
        public string $type = self::TYPE_FIXED
    )
    {
        
    }

    public function bind(ColumnAttribute $column, string $property, string $instance)
    {
        $relatedClass   = $this->instance;
        $relatedTable   = $relatedClass::getTable();
        $relatedKey     = $this->name;
        $localPK        = $instance::getPrimaryKey();

        $instance::onLoad(function($instance) use ($property, $relatedClass, $relatedTable, $relatedKey, $localPK) {
            $select = $relatedClass::select();
            $select->where("{$relatedTable}.{$relatedKey} = ?", $instance->$localPK);
            
            $instance->store[$property] = new FixedCollection($instance, $select);
        });
    }
}
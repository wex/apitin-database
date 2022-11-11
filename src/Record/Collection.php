<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\FixedCollection;
use Apitin\Database\JsonCollection;
use Apitin\Database\LazyCollection;
use Attribute;
use BadMethodCallException;
use LogicException;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Collection extends ColumnAttribute
{
    const   TYPE_FIXED      = 'fixed';
    const   TYPE_LAZY       = 'lazy';

    public function __construct(
        public string $name,
        public string $instance,
        public string $type = self::TYPE_FIXED
    )
    {
        
    }

    public function bind(ColumnAttribute $column, string $property, string $instance)
    {
        $collectionType = $this->type;
        $relatedClass   = $this->instance;
        $relatedTable   = $relatedClass::getTable();
        $relatedKey     = $this->name;
        $localPK        = $instance::getPrimaryKey();

        $instance::onLoad(function($instance) use ($property, $relatedClass, $relatedTable, $relatedKey, $localPK, $collectionType) {
            $select = $relatedClass::select();
            $select->where("{$relatedTable}.{$relatedKey} = ?", $instance->$localPK);

            switch ($collectionType) {
                case Collection::TYPE_FIXED:
                    $instance->store[$property] = new FixedCollection($instance, $property, $select);
                    break;

                case Collection::TYPE_LAZY:
                    $instance->store[$property] = new LazyCollection($instance, $property, $select);
                    break;

                default:
                    throw new BadMethodCallException(sprintf(
                        "Unknown collection type: '%s'",
                        $collectionType
                    ));
            }
            
        });
    }
}
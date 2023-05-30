<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
abstract class ColumnAttribute
{
    const   TYPE_STRING     = 'string';
    const   TYPE_INTEGER    = 'int';
    const   TYPE_DECIMAL    = 'decimal';
    const   TYPE_BOOLEAN    = 'bool';
    const   TYPE_DATETIME   = 'datetime';
    const   TYPE_DATE       = 'date';
    const   TYPE_TEXT       = 'mediumtext';
    const   TYPE_FOREIGNKEY = 'foreign_key';
    const   TYPE_VIRTUAL    = 'virtual';
    
    public function __construct(
        public string $type = self::TYPE_STRING,
        public bool $required = false,
        public mixed $default = null,
        public ?string $alias = null
    )
    {
        
    }

    public function bind(ColumnAttribute $column, string $property, string $instance)
    {

    }
}
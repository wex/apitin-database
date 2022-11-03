<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
abstract class ColumnAttribute
{
    public function bind(ColumnAttribute $column, string $property, string $instance)
    {

    }
}
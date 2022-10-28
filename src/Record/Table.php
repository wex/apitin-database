<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        public string $name,
        public string $primaryKey = 'id',
        public bool $timeStamps = false,
        public bool $softDelete = false
    )
    {
        
    }
}

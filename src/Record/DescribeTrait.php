<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use ReflectionClass;

trait DescribeTrait
{
    use CacheTrait;

    public static function getTable()
    {
        return static::cached('table', function() {
            $ref = new ReflectionClass(static::class);
            return $ref->getAttributes(Table::class)[0]->newInstance()->name;
        });
    }

    public static function getPrimaryKey()
    {
        return static::cached('primaryKey', function() {
            $ref = new ReflectionClass(static::class);
            return $ref->getAttributes(Table::class)[0]->newInstance()->primaryKey;
        });
    }

    public static function describe()
    {
        return static::cached('columns', function() {
            $ref = new ReflectionClass(static::class);
            $columns = [];
            $primaryKey = static::getPrimaryKey();
            $columns[$primaryKey] = [
                'type'      => Column::TYPE_INTEGER,
                'default'   => null,
                'required'  => false,
                'column'    => new Column($primaryKey, Column::TYPE_INTEGER, false, null),
            ];
            foreach ($ref->getAttributes(Column::class) as $attr) {
                $t = $attr->newInstance();
                $columns[$t->name] = [
                    'type'      => $t->type,
                    'default'   => $t->default,
                    'required'  => $t->required,
                    'column'    => $t,
                ];
            }
            return $columns;
        });
    }
}

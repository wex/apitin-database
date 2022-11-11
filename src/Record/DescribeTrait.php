<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\Record;
use BadMethodCallException;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

trait DescribeTrait
{
    use CacheTrait;

    /**
     * @return string
     */
    public static function getTable()
    {
        return static::cached('table', function() {
            $ref = new ReflectionClass(static::class);
            return $ref->getAttributes(Table::class)[0]->newInstance()->name;
        });
    }

    /**
     * @return string
     */
    public static function getPrimaryKey()
    {
        return static::cached('primaryKey', function() {
            $ref = new ReflectionClass(static::class);
            return $ref->getAttributes(Table::class)[0]->newInstance()->primaryKey;
        });
    }

    /**
     * @return bool
     */
    public static function hasTimestamps()
    {
        return static::cached('timeStamps', function() {
            $ref = new ReflectionClass(static::class);
            return $ref->getAttributes(Table::class)[0]->newInstance()->timeStamps;
        });
    }

    /**
     * @return string
     */
    public static function useSoftDelete()
    {
        return static::cached('softDelete', function() {
            $ref = new ReflectionClass(static::class);
            return $ref->getAttributes(Table::class)[0]->newInstance()->softDelete;
        });
    }

    /**
     * @return Column[]
     */
    public static function describe()
    {
        return static::cached('columns', function() {
            $refClass   = new ReflectionClass(static::class);
            $columns    = [];
            $primaryKey = static::getPrimaryKey();

            /**
             * Primary Key
             */
            $columns[$primaryKey] = new Column($primaryKey, Column::TYPE_INTEGER, false, null);

            /**
             * Other fields
             */
            foreach ($refClass->getProperties(ReflectionProperty::IS_PROTECTED) as $refProp) {
                foreach ($refProp->getAttributes(ColumnAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $propAttr) {
                    $attr = $propAttr->newInstance();
                    
                    switch ($attr::class) {

                        case Column::class:
                            $columns[ $attr->name ] = $attr;
                            $attr->bind($attr, $refProp->getName(), static::class);
                            break;

                        case Relation::class:
                            $columns[ $attr->name ] = new Column($attr->name, Column::TYPE_FOREIGNKEY);
                            $columns[ $refProp->getName() ] = new Column($attr->name, Column::TYPE_VIRTUAL);
                            $attr->bind($attr, $refProp->getName(), static::class);
                            break;

                        case Collection::class:
                            $columns[ $refProp->getName() ] = new Column($refProp->getName(), Column::TYPE_VIRTUAL); 
                            $attr->bind($attr, $refProp->getName(), static::class);
                            break;

                        default:
                            throw new BadMethodCallException(sprintf(
                                "Unknown column attribute: '%s'",
                                $attr::class
                            ));
                    }


                }
            }

            return $columns;
        });
    }
}

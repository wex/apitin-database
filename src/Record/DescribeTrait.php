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
            $columns[$primaryKey] = new Column(Column::TYPE_INTEGER, false, null);

            /**
             * Timestamps
             */
            if (static::hasTimestamps()) {
                $columns['created_at'] = new Column(Column::TYPE_DATETIME, false, null);
                $columns['updated_at'] = new Column(Column::TYPE_DATETIME, false, null);
            }

            /**
             * Soft-delete
             */
            if (static::useSoftDelete()) {
                $columns['deleted_at'] = new Column(Column::TYPE_DATETIME, false, null);
            }

            /**
             * Other fields
             */
            foreach ($refClass->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC) as $refProp) {
                foreach ($refProp->getAttributes(ColumnAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $propAttr) {
                    $attr = $propAttr->newInstance();

                    $fieldName = $refProp->getName();
                    
                    switch ($attr::class) {

                        case Column::class:
                            $columns[ $fieldName ] = $attr;
                            $attr->bind($attr, $fieldName, static::class);
                            break;

                        case HasMany::class:
                            $columns[ $fieldName ] = new Column(Column::TYPE_HASMANY);
                            $attr->bind($attr, $fieldName, static::class);
                            break;

                        case Relation::class:
                            $columns[ $fieldName ] = new Column(Column::TYPE_FOREIGNKEY);
                            $columns[ $fieldName ] = new Column(Column::TYPE_VIRTUAL);
                            $attr->bind($attr, $fieldName, static::class);
                            break;

                        case Collection::class:
                            $columns[ $fieldName ] = new Column(Column::TYPE_VIRTUAL); 
                            $attr->bind($attr, $fieldName, static::class);
                            break;

                        case Store::class:
                            $columns[ $fieldName ] = new Column(Column::TYPE_TEXT);
                            $attr->bind($attr, $fieldName, static::class);
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

<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

use Apitin\Database\Database;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class HasMany extends ColumnAttribute
{
    public function __construct(
        public string $foreignKey,
        public string $record
    )
    {
        
    }


    public function bind(ColumnAttribute $column, string $property, string $instance)
    {
        $instance::onLoad(function($instance) use ($property, $column) {
            $pkName = $instance::getPrimaryKey();
            $fkName = $column->foreignKey;
            $class  = $column->record;

            $instance->$property = [];

            if ($instance->$pkName) {

                $select = $class::select();
                $select->where(
                    sprintf('%s = ?', str_replace('`', '``', "{$fkName}")),
                    $instance->$pkName
                );

                foreach ($select->all() as $t) {
                    $instance->$property[] = $t;
                }
                
            }            
        });

        $instance::onValidate($property, function($instance) use ($property, $column) {
            $wantedType = $column->record;
            $result = [];
            
            if (!is_array($instance->$property)) $result[] = Validator::ERR_INVALID_TYPE;
            foreach ($instance->$property as $t) {
                if (!($t instanceof $wantedType)) { $result[] = Validator::ERR_INVALID_TYPE; break; }
            }

            return array_unique($result);
        });

        $instance::afterSave(function($instance) use ($property, $column) {
            $class  = $column->record;
            $db     = Database::factory();
            $pkName = $instance::getPrimaryKey();
            $fkName = $column->foreignKey;
            $fKey   = $class::getPrimaryKey();
            
            if (!is_array($instance->$property)) return;
            if (!$instance->$pkName) return;

            $existingKeys = [0];

            foreach ($instance->$property as $t) {
                $key = $t::getPrimaryKey();
                $existingKeys[] = $t->$key;
            }

            $db->exec(sprintf('DELETE FROM `%s` WHERE `%s` = %s AND `%s` NOT IN (%s)',
                str_replace('`', '``', "{$class::getTable()}"),
                str_replace('`', '``', "{$fkName}"),
                $db->quote("{$instance->$pkName}"),
                str_replace('`', '``', "{$fKey}"),
                implode(',', array_map(function($v) use ($db) { return $db->quote("{$v}"); }, $existingKeys))
            ));

            foreach ($instance->$property as $t) {
                $t->$fkName = $instance->$pkName;
                $t->save();
            }

        });
    }
}
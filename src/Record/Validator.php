<?php

namespace Apitin\Database\Record;;

use Apitin\Database\Database;
use Apitin\Database\Record;
use Apitin\Database\Record\Column;
use Apitin\Database\Select\Quote;
use Closure;
use DateTimeImmutable;
use Exception;

class Validator
{
    const   ERR_INVALID_TYPE    = 'error.invalid_type';
    const   ERR_REQUIRED        = 'error.required';
    const   ERR_UNIQUE          = 'error.not_unique';

    const   ERR_TOO_SHORT       = 'error.too_short';
    const   ERR_TOO_LONG        = 'error.too_long';
    const   ERR_TOO_SMALL       = 'error.too_small';
    const   ERR_TOO_BIG         = 'error.too_big';

    protected Record $class;
    protected string $record;
    protected array $meta;
    protected array $data;

    public function __construct(Record &$record)
    {
        $recordClass = $record::class;

        $this->class    = $record;
        $this->meta     = $recordClass::describe();
        $this->record   = $recordClass;

        foreach ($this->meta as $fieldName => $fieldMeta) {
            $this->data[ $fieldName ] = $fieldMeta->from($record->$fieldName);
        }
    }

    public function validate(array $skip = [])
    {
        $recordClass    = $this->record;
        $result         = [];

        foreach ($this->meta as $fieldName => $fieldMeta) {
            if (in_array($fieldName, $skip)) continue;

            foreach ($recordClass::onValidate($fieldName) as $callback) {
                $fieldResult = Closure::fromCallable($callback)->call($this->class, $this->class, $this->class->$fieldName);
                if (is_array($fieldResult) && count($fieldResult)) {
                    $result[ $fieldName ] = $fieldResult;
                }
            }

            if (in_array($fieldMeta->type, [Column::TYPE_HASMANY])) continue;
            if ($fieldMeta->type === Column::TYPE_VIRTUAL) continue;

            $fieldResult    = [];
            $fieldValue     = $this->data[ $fieldName ];
            $fieldEmpty     = is_null($fieldValue) || !strlen("{$fieldValue}");
            $fieldLength    = $fieldEmpty ? 0 : strlen("{$fieldValue}");

            $fieldMinLen = $fieldMaxLen = $fieldMinValue = $fieldMaxValue = null;

            $fieldRequired  = $fieldMeta->required ? true : false;
            $fieldUnique    = $fieldMeta->unique ? true : false;

            switch ($fieldMeta->type) {
                case Column::TYPE_STRING:
                case Column::TYPE_TEXT:
                    $fieldMinLen    = is_null($fieldMeta->min) ? 0 : $fieldMeta->min;
                    $fieldMaxLen    = is_null($fieldMeta->max) ? 255 : $fieldMeta->max;
                    break;

                case Column::TYPE_DECIMAL:
                    $fieldMinValue  = is_null($fieldMeta->min) ? PHP_FLOAT_MIN : $fieldMeta->min;
                    $fieldMaxValue  = is_null($fieldMeta->max) ? PHP_FLOAT_MAX : $fieldMeta->max;
                    break;

                case Column::TYPE_INTEGER:
                    $fieldMinValue  = is_null($fieldMeta->min) ? PHP_INT_MIN : $fieldMeta->min;
                    $fieldMaxValue  = is_null($fieldMeta->max) ? PHP_INT_MAX : $fieldMeta->max;
                    break;

                case Column::TYPE_BOOLEAN:
                    $fieldMinValue  = 0;
                    $fieldMaxValue  = 1;
                    break;

                case Column::TYPE_DATE:
                    $fieldMinValue  = is_null($fieldMeta->min) ? null : (new DateTimeImmutable($fieldMeta->min))->setTime(0, 0, 0);
                    $fieldMaxValue  = is_null($fieldMeta->max) ? null : (new DateTimeImmutable($fieldMeta->max))->setTime(0, 0, 0);
                    break;

                case Column::TYPE_DATETIME:
                    $fieldMinValue  = is_null($fieldMeta->min) ? null : new DateTimeImmutable($fieldMeta->min);
                    $fieldMaxValue  = is_null($fieldMeta->max) ? null : new DateTimeImmutable($fieldMeta->max);
                    break;

                default:
                    $fieldResult[] = static::ERR_INVALID_TYPE;
                    break;
            }

            if ($fieldRequired && $fieldEmpty) $fieldResult[] = static::ERR_REQUIRED;

            if ($fieldMinLen && !$fieldEmpty && $fieldLength < $fieldMinLen) $fieldResult[] = static::ERR_TOO_SHORT;
            if ($fieldMaxLen && !$fieldEmpty && $fieldLength > $fieldMaxLen) $fieldResult[] = static::ERR_TOO_LONG; 

            if ($fieldMinValue && !$fieldEmpty && $fieldValue < $fieldMinValue) $fieldResult[] = static::ERR_TOO_SMALL;
            if ($fieldMaxValue && !$fieldEmpty && $fieldValue > $fieldMaxValue) $fieldResult[] = static::ERR_TOO_BIG;

            if ($fieldUnique && !$fieldEmpty) {
                $quoter     = new Quote;
                $primaryKey = $recordClass::getPrimaryKey();

                try {
                    $existing   = $recordClass::select()
                                            ->where(sprintf('%s = ?', $quoter->quoteIdentifier($fieldName)), $fieldValue)
                                            ->where(sprintf('%s != ?', $quoter->quoteIdentifier($primaryKey)), $this->data[$primaryKey] ?? 0)
                                            ->first();

                    if ($existing->id) $fieldResult[] = static::ERR_UNIQUE;

                } catch (Exception $e) {}
            }

            if (count($fieldResult)) $result[ $fieldName ] = $fieldResult;
        }

        if (!count($result)) return true;

        return $result;
    }

}
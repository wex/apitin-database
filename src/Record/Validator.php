<?php

namespace Apitin\Database\Record;;

use Apitin\Database\Record;
use Apitin\Database\Record\Column;

class Validator
{
    const   ERR_INVALID_TYPE    = 'error.invalid_type';
    const   ERR_REQUIRED        = 'error.required';

    const   ERR_TOO_SHORT       = 'error.too_short';
    const   ERR_TOO_LONG        = 'error.too_long';
    const   ERR_TOO_SMALL       = 'error.too_small';
    const   ERR_TOO_BIG         = 'error.too_big';

    protected array $meta;
    protected array $data;

    public function __construct(Record &$record)
    {
        $recordClass = $record::class;

        $this->meta = $recordClass::describe();

        foreach ($this->meta as $fieldName => $fieldMeta) {
            $this->data[ $fieldName ] = $fieldMeta->from($record->$fieldName);
        }
    }

    public function validate(array $skip = [])
    {
        $result = [];

        foreach ($this->meta as $fieldName => $fieldMeta) {
            if (in_array($fieldName, $skip)) continue;

            $fieldResult    = [];
            $fieldValue     = $this->data[ $fieldName ];
            $fieldEmpty     = is_null($fieldValue) || !strlen($fieldValue);
            $fieldLength    = $fieldEmpty ? 0 : strlen($fieldValue);

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

                default:
                    $fieldResult[] = static::ERR_INVALID_TYPE;
                    break;
            }

            if ($fieldRequired && $fieldEmpty) $fieldResult[] = static::ERR_REQUIRED;

            if ($fieldMinLen && !$fieldEmpty && $fieldLength < $fieldMinLen) $fieldResult[] = static::ERR_TOO_SHORT;
            if ($fieldMaxLen && !$fieldEmpty && $fieldLength > $fieldMaxLen) $fieldResult[] = static::ERR_TOO_LONG; 

            if ($fieldMinValue && !$fieldEmpty && $fieldValue < $fieldMinValue) $fieldResult[] = static::ERR_TOO_SMALL;
            if ($fieldMaxValue && !$fieldEmpty && $fieldValue > $fieldMaxValue) $fieldResult[] = static::ERR_TOO_BIG;

            if ($fieldResult) $result[ $fieldName ] = $fieldResult;
        }

        if (!$result) return true;

        return $result;
    }

}
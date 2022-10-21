<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

use Apitin\Database\Database;

abstract class Part implements IPart
{
    public function quoteValue($value): string
    {
        /**
         * @todo FIX THIS - this is ugly way to do it.
         */
        return sprintf("'%s'", str_replace(
            [
                "\x00", 
                "'"
            ], 
            [
                '', 
                "\'"
            ], 
            $value
        ));
    }

    public function quoteIdentifier(...$values): string
    {
        return implode('.', array_map(
            function($t) {
                return sprintf('`%s`', str_replace('`', '``', $t));
            },
            $values
        ));
    }

    public function toExpression(): string
    {
        return '';
    }

    public function toTable(): string
    {
        return '';
    }

    public function toWhere(): string
    {
        return '';
    }

    public function toJoin(): string
    {
        return '';
    }

    public function toGroup(): string
    {
        return '';
    }

    public function toHaving(): string
    {
        return '';
    }

    public function toOrder(): string
    {
        return '';
    }

    public function toLimit(): string
    {
        return '';
    }

}
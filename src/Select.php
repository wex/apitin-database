<?php declare(strict_types = 1);

namespace Apitin\Database;

use Apitin\Database\Select\Expression;
use Apitin\Database\Select\From;
use Apitin\Database\Select\Having;
use Apitin\Database\Select\Join;
use Apitin\Database\Select\Part;
use Apitin\Database\Select\Where;
use Closure;

class Select
{
    const PART_EXPRESSION   = 'expressions';
    const PART_TABLE        = 'tables';
    const PART_WHERE        = 'wheres';
    const PART_JOIN         = 'joins';
    const PART_GROUP        = 'groups';
    const PART_HAVING       = 'havings';
    const PART_ORDER        = 'orders';
    const PART_LIMIT        = 'limits';

    /**
     * @var Part[]
     */
    protected array $parts = [];

    /**
     * @param string|array $table Table name as string or [tableName => alias]
     * @param array $columns Array of selected columns
     */
    public function __construct(string|array $table, array $columns = ['*'])
    {
        if (is_array($table)) {

            $this->parts[] = new From(key($table), $columns, current($table));

        } else {

            $this->parts[] = new From($table, $columns);

        }
    }

    /**
     * @param string|array $table Table name as string or [tableName => alias]
     * @param array $columns Array of selected columns
     */
    public function from(string|array $table, array $columns = ['*']): static
    {
        if (is_array($table)) {

            $this->parts[] = new From(key($table), $columns, current($table));

        } else {

            $this->parts[] = new From($table, $columns);

        }

        return $this;
    }

    /**
     * @param array $columns Array of selected columns
     */
    public function columns(array $columns): static
    {
        foreach ($columns as $k => $t) {

            if (is_callable($t)) {

                $this->parts[] = new Expression(Closure::fromCallable($t)->call($this), null, true);

            } else {

                $this->parts[] = new Expression($t, is_numeric($k) ? null : $k);

            }            
        }

        return $this;
    }

    /**
     * @param string|array $table Table name as string or [tableName => alias]
     * @param array $on Join rules as string or in array as where -format ('foo = ?', 'bar')
     * @param array $columns Array of selected columns
     */
    public function join(string|array $table, string|array $on, array $columns = ['*']): static
    {
        if (is_array($table)) {

            $this->parts[] = new Join(key($table), $on, $columns, current($table), 'INNER');

        } else {

            $this->parts[] = new Join($table, $on, $columns, null, 'INNER');

        }

        return $this;
    }

    /**
     * @param string|array $table Table name as string or [tableName => alias]
     * @param array $on Join rules as string or in array as where -format ('foo = ?', 'bar')
     * @param array $columns Array of selected columns
     */
    public function joinLeft(string|array $table, string|array $on, array $columns = ['*']): static
    {
        if (is_array($table)) {

            $this->parts[] = new Join(key($table), $on, $columns, current($table), 'LEFT');

        } else {

            $this->parts[] = new Join($table, $on, $columns, null, 'LEFT');

        }

        return $this;
    }

    /**
     * @param string $expression Expression - if ? given, value will be quoted and replaced there
     * @param mixed $value Optional value for expression
     */
    public function where(string $expression, $value = null): static
    {
        $this->parts[] = new Where($expression, $value);

        return $this;
    }

    /**
     * @param string $expression Expression - if ? given, value will be quoted and replaced there
     * @param mixed $value Optional value for expression
     */
    public function having(string $expression, $value = null): static
    {
        $this->parts[] = new Having($expression, $value);

        return $this;
    }

    /**
     * @return string SQL string
     */
    public function toSql(): string
    {
        $partMap = [
            static::PART_EXPRESSION => 'toExpression',
            static::PART_TABLE      => 'toTable',
            static::PART_WHERE      => 'toWhere',
            static::PART_JOIN       => 'toJoin',
            static::PART_GROUP      => 'toGroup',
            static::PART_HAVING     => 'toHaving',
            static::PART_ORDER      => 'toOrder',
            static::PART_LIMIT      => 'toLimit',
        ];

        $parts = [];

        foreach ($partMap as $k => $v) {
            $parts[$k] = [];
        }

        foreach ($this->parts as $t) {
            foreach ($partMap as $partKey => $partCallback) {
                $parts[$partKey][] = $t->$partCallback();
            }
        }

        foreach ($parts as &$t) {
            $t = array_values(array_filter($t));
        }

        $sql = sprintf("SELECT\n\t%s\nFROM\n\t%s %s%s %s%s",
            implode(', ', $parts[static::PART_EXPRESSION]),
            implode(', ', $parts[static::PART_TABLE]),

            count($parts[static::PART_JOIN]) ? "\n" : '',
            implode("\n", $parts[static::PART_JOIN]),

            count($parts[static::PART_WHERE]) ? "\nWHERE\n\t" : '',
            implode(" AND\n\t", $parts[static::PART_WHERE]),

            count($parts[static::PART_GROUP]) ? "\nGROUP BY\n\t" : '',
            implode(', ', $parts[static::PART_GROUP]),

            count($parts[static::PART_HAVING]) ? "\nHAVING\n\t" : '',
            implode(" AND\n\t", $parts[static::PART_HAVING]),

            count($parts[static::PART_ORDER]) ? "\nORDER BY\n\t" : '',
            implode(", ", $parts[static::PART_ORDER]),

            count($parts[static::PART_LIMIT]) ? "\nLIMIT\n\t" : '',
            implode("", $parts[static::PART_LIMIT]),

        );
        
        return $sql;
    }

    /**
     * @return string 
     */
    public function __toString()
    {
        return $this->toSql();
    }

    /**
     * @return array First record found by select
     */
    public function first()
    {

    }

    /**
     * @return array[array] All records found by select
     */
    public function all()
    {

    }

    /**
     * @return mixed First record's first column found by select
     */
    public function column()
    {

    }

}
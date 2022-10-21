<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class Join extends Part
{
    public function __construct(protected string $table, protected array|string $on, protected array $columns = ['*'], protected ?string $alias = null, protected string $type = 'INNER')
    {
        $this->name = $alias ?? $table;
    }

    public function toJoin(): string
    {
        $joinTable = $joinExpression = '';

        if (!is_null($this->alias)) {

            $joinTable = sprintf("%s AS %s", 
                $this->quoteIdentifier($this->table), 
                $this->quoteIdentifier($this->alias)
            );

        } else {

            $joinTable = $this->quoteIdentifier($this->table);

        }

        if (is_array($this->on)) {

            $joinExpression = implode(' AND ', array_map(
                function($onValue, $onExpression) {
                    if (is_numeric($onExpression)) {
                        return $onValue;
                    } else {
                        return str_replace(
                            '?',
                            $this->quoteValue($onValue),
                            $onExpression
                        );
                    }
                },
                $this->on,
                array_keys($this->on)
            ));

        } else {

            $joinExpression = $this->on;

        }

        return sprintf("%s JOIN\n\t%s ON %s",
            $this->type,
            $joinTable,
            $joinExpression
        );
    }

    public function toExpression(): string
    {
        return implode(', ', array_map(
            function($t, $k) { 

                if (is_callable($t)) {
                    return $t();
                }

                if ($t === '*') {

                    return sprintf("%s.%s", 
                        $this->quoteIdentifier($this->name),
                        $t
                    );

                }
                if (is_numeric($k)) {

                    return $this->quoteIdentifier($this->name, $t); 

                } else {

                    return sprintf('%s.%s AS %s',
                        $this->quoteIdentifier($this->name),
                        $this->quoteIdentifier($k),
                        $this->quoteIdentifier($t)
                    );

                }

            },
            $this->columns,
            array_keys($this->columns)
        ));
    }
}
<?php declare(strict_types = 1);

namespace Apitin\Database\Select;

class From extends Part
{
    protected string $name;

    public function __construct(protected string $table, protected array $columns = ['*'], protected ?string $alias = null)
    {
        $this->name = $alias ?? $table;
    }

    public function toTable(): string
    {
        if (!is_null($this->alias)) {

            return sprintf("%s AS %s", 
                $this->quoteIdentifier($this->table), 
                $this->quoteIdentifier($this->alias)
            );

        }

        return $this->quoteIdentifier($this->table);
    }

    public function toExpression(): string
    {
        return implode(', ', array_map(
            function($t, $k) { 

                if ($t === '*') {

                    return sprintf("%s.%s", 
                        $this->quoteIdentifier($this->name),
                        $t
                    );

                }

                if (is_callable($t)) {
                    return $t;
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
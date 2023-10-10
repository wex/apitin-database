<?php declare(strict_types = 1);

namespace Apitin\Database;

use Exception;

class FixedCollection extends Collection
{
    protected string $className;
    protected Record $parent;
    protected string $column;
    protected Select $source;
    protected array  $store     = [];
    protected int    $iterator  = -1;

    public function __construct(Record &$parent, string $column, Select $source, string $className)
    {
        $this->parent       = $parent;
        $this->source       = $source;
        $this->column       = $column;
        $this->className    = $className;

        $this->refresh();
    }

    public function refresh()
    {
        $this->store = $this->source->all();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->store);
    }

    public function &offsetGet(mixed $offset): mixed
    {
        return $this->store[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->store[is_null($offset) ? count($this->store) : $offset] = $value;
        $this->parent->setDirty($this->column);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('destroy?');
        unset( $this->store[$offset] );
    }

    public function &current(): mixed
    {
        return $this->store[$this->iterator];
    }

    public function key(): mixed
    {
        return $this->iterator;
    }

    public function next(): void
    {
        $this->iterator++;
    }

    public function rewind(): void
    {
        $this->iterator = 0;
    }

    public function valid(): bool
    {
        return array_key_exists($this->iterator, $this->store);
    }

    public function count(): int
    {
        return count($this->store);
    }

    public function save(array $foreignKeys)
    {
        foreach ($this->store as &$item) {
            foreach ($foreignKeys as $foreignKey => $foreignValue) {
                $item->$foreignKey = $foreignValue;
            }
            $item->save();
        }

        $this->refresh();
    }
}
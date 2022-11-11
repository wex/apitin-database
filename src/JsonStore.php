<?php declare(strict_types = 1);

namespace Apitin\Database;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use Iterator;

class JsonStore implements ArrayAccess, Iterator, Countable
{
    protected Record $parent;
    protected string $column;
    protected string $collects;
    protected array  $store     = [];
    protected int    $iterator  = -1;

    public function __construct(Record &$parent, string $column,  string $collects)
    {
        $this->parent   = $parent;
        $this->column   = $column;
        $this->collects = $collects;

        $this->refresh();
    }

    public function refresh()
    {
        $column = $this->column;
        $this->store = $this->parent->$column ?
            json_decode($this->parent->$column, true) :
            [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->store);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $object = $this->collects;

        if (!$this->offsetExists($offset)) {
            throw new BadMethodCallException(sprintf(
                "Invalid offset: %s",
                $offset
            ));
        }

        return $this->store[$offset] ?
            new $object($this->store[$offset]) :
            null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!($value instanceof $this->collects)) {
            throw new BadMethodCallException(sprintf(
                "Invalid record for storage: '%s'",
                get_class($value)
            ));
        }

        $this->store[is_null($offset) ? count($this->store) : $offset] = $value->toArray();
        $this->parent->setDirty($this->column);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset( $this->store[$offset] );
    }

    public function current(): mixed
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

    public function save()
    {
        return json_encode($this->store);
    }
}
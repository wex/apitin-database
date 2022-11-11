<?php declare(strict_types = 1);

namespace Apitin\Database;

use LengthException;
use LogicException;

class LazyCollection extends Collection
{
    protected Record $parent;
    protected Select $source;
    protected array  $cache     = [];
    protected int    $offset    = -1;

    protected string $lastQuery = '';
    protected ?Record $lastRecord;

    public function __construct(Record &$parent, Select $source)
    {
        $this->parent   = $parent;
        $this->source   = $source;

        $this->refresh();
    }

    public function refresh()
    {
        // nop
    }

    protected function _load(mixed $offset)
    {
        $select = clone $this->source;
        $select->limit(1, intval($offset));

        if ("{$select}" !== $this->lastQuery) {
            $this->lastQuery    = "{$select}";
            try {
                $this->lastRecord       = $select->first();
                $this->cache[$offset]   = $this->lastRecord;
            } catch (LengthException $e) {
                $this->lastRecord       = null;
            }
        }

        return $this->lastRecord;
    }

    public function offsetExists(mixed $offset): bool
    {
        return !!$this->_load($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->_load($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('NOT IMPLEMENTED YET');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('NOT IMPLEMENTED YET');
    }

    public function current(): mixed
    {
        return $this->_load($this->offset);
    }

    public function key(): mixed
    {
        return $this->offset;
    }

    public function next(): void
    {
        $this->offset++;
    }

    public function rewind(): void
    {
        $this->offset = 0;
    }

    public function valid(): bool
    {
        return !!$this->_load($this->offset);
    }

    public function count(): int
    {
        throw new LogicException('NOT IMPLEMENTED YET');
        return count($this->store);
    }

    public function save()
    {
        throw new LogicException('NOT IMPLEMENTED YET');
        die('todo');
    }
}
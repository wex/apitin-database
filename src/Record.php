<?php declare(strict_types = 1);

namespace Apitin\Database;

use Apitin\Database\Record\DescribeTrait;
use Apitin\Database\Record\Select;

abstract class Record
{
    /**
     * @var mixed[]
     */
    protected array $store = [];

    /**
     * @var mixed[]
     */
    protected array $dirty = [];

    /**
     * Get attribute describers
     */ 
    use DescribeTrait;
    
    public function __construct(array $kvp = [])
    {
        foreach (static::describe() as $k => $meta) {
            $this->store[$k] = $meta['default'];
        }

        foreach ($kvp as $k => $v) {
            $this->store[$k] = $v;
        }
    }

    public function __set(string $key, $value): void
    {
        $this->dirty[$key] = $this->store[$key] ?? null;
        $this->store[$key] = $value;
    }

    public function __get(string $key): mixed
    {
        return $this->store[$key] ?? null;
    }

    public function hasChanged(): bool
    {
        return !!count($this->dirty);
    }

    public function toArray(?array $keys = null): array
    {
        return is_null($keys) ?
            $this->store :
            array_intersect_key($this->store, $keys);
    }

    public static function create(array $data = []): static
    {
        $instance = new static;
        
        foreach ($data as $k => $v) {
            $instance->$k = $v;
        }

        return $instance;
    }

    public function set(array $kvp): void
    {
        foreach ($kvp as $k => $v) {
            $this->$k = $v;
        }
    }

    public static function select()
    {
        $select = new Select(static::getTable());
        $select->setClass(static::class);

        return $select;
    }
}
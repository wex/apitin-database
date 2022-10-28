<?php declare(strict_types = 1);

namespace Apitin\Database;

use Apitin\Database\Record\DescribeTrait;
use Apitin\Database\Record\Select;
use Apitin\Database\Select as DatabaseSelect;
use stdClass;
use PDOException;
use LengthException;
use LogicException;

abstract class Record
{
    /**
     * @var Database
     */
    protected static Database $db;

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
    
    /**
     * @param Database $db 
     */
    public static function setDatabase(Database $db)
    {
        static::$db = $db;
        DatabaseSelect::setDatabase($db);
    }

    public function __construct(array $kvp = [])
    {
        foreach (static::describe() as $k => $meta) {
            $this->store[$k] = $meta->default;
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

    public function set(array $kvp): static
    {
        foreach ($kvp as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    public static function select(): Select
    {
        $select = new Select(static::getTable());
        $select->setClass(static::class);

        return $select;
    }

    /**
     * @return static
     */
    public static function load(int $primaryKeyValue)
    {
        $select = static::select();
        $select->where(
            sprintf('%s.%s = ?',
                static::getTable(),
                static::getPrimaryKey()
            ),
            $primaryKeyValue
        );

        return $select->first();
    }

    /**
     * @return static
     */
    public function reload()
    {
        $primaryKey = static::getPrimaryKey();

        return static::load(intval($this->$primaryKey));
    }

    /**
     * @return static
     */
    public function save()
    {
        if (!$this->hasChanged()) return $this;

        $primaryKey = static::getPrimaryKey();

        if ($this->$primaryKey) {

            $data = [];
            foreach (static::describe() as $field) {
                if (!isset($this->dirty[$field->name])) continue;
                $data[$field->name] = $this->store[$field->name] ?? $field->default;
            }

            static::$db->update(
                $this->getTable(),
                $data,
                [$this->getPrimaryKey() => $this->$primaryKey]
            );

        } else {

            $data = [];
            foreach (static::describe() as $field) {
                $data[$field->name] = $this->store[$field->name] ?? $field->default;
            }

            $this->$primaryKey = static::$db->insert(
                $this->getTable(),
                $data
            );

        }

        return $this->reload();
    }
}
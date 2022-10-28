<?php declare(strict_types = 1);

namespace Apitin\Database;

use Apitin\Database\Record\DescribeTrait;
use Apitin\Database\Record\Select;
use Apitin\Database\Select as DatabaseSelect;

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
        $fields = static::describe();

        $this->dirty[$key] = $this->store[$key] ?? null;
        $this->store[$key] = isset($fields[$key]) ?
            $fields[$key]->to($value) :
            $value;
    }

    public function __get(string $key): mixed
    {
        $fields = static::describe();
        $value = $this->store[$key] ?? null;

        return isset($fields[$key]) ?
            $fields[$key]->from($value) :
            $value;
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

        if (static::useSoftDelete()) {
            $select->where('deleted_at IS NULL OR deleted_at >= NOW()');
        }

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
        $instance = static::load(intval($this->$primaryKey));

        $this->store = $instance->store;

        return $this;
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

            if (!$data) return $this;

            if (static::hasTimestamps()) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            static::$db->update(
                $this->getTable(),
                $data,
                [$primaryKey => $this->$primaryKey]
            );

        } else {

            $data = [];
            foreach (static::describe() as $field) {
                $data[$field->name] = $this->store[$field->name] ?? $field->default;
            }

            if (!$data) return $this;

            if (static::hasTimestamps()) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            $this->$primaryKey = static::$db->insert(
                $this->getTable(),
                $data
            );

        }

        return $this->reload();
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        $primaryKey = static::getPrimaryKey();

        if (static::useSoftDelete()) {

            return !!static::$db->update(
                static::getTable(),
                ['deleted_at' => date('Y-m-d H:i:s')],
                [$primaryKey => $this->$primaryKey]
            );

        } else {

            return !!static::$db->delete(
                static::getTable(),
                [$primaryKey => $this->$primaryKey]
            );

        }
    }
}
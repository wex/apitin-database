<?php declare(strict_types = 1);

namespace Apitin\Database;

use Apitin\Database\Record\Column;
use Apitin\Database\Record\DescribeTrait;
use Apitin\Database\Record\EventTrait;
use Apitin\Database\Record\Select;
use Apitin\Database\Record\Validator;
use Apitin\Database\Select as DatabaseSelect;
use Closure;

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
     * Record events
     */
    use EventTrait;
    
    /**
     * @param Database $db 
     */
    public static function setDatabase(Database $db)
    {
        static::$db = $db;
        DatabaseSelect::setDatabase($db);
    }

    public static function boot()
    {

    }

    public function __construct(array $kvp = [])
    {
        $aliasMap = [];

        foreach (static::describe() as $fieldName => $fieldMeta) {
            if ($fieldMeta->alias) $aliasMap[ $fieldMeta->alias ] = $fieldName;
            $this->store[$fieldName] = $fieldMeta->default;
            unset( $this->{$fieldName} );
        }

        foreach ($kvp as $k => $v) {
            $this->store[$aliasMap[$k] ?? $k] = $v;
        }

        if (static::onBoot()) {
            static::boot();
        }

        foreach (static::onLoad() as $callback) {
            Closure::fromCallable($callback)->call($this, $this);
        }
    }

    public function __set(string $key, $value): void
    {
        $fields     = static::describe();

        if (isset($fields[$key]) && $fields[$key]->readonly) return;

        foreach (static::onSet($key) as $callback) {
            $value = Closure::fromCallable($callback)->call($this, $this, $value);
        }

        $this->dirty[$key] = $this->store[$key] ?? null;
        $this->store[$key] = isset($fields[$key]) ?
            $fields[$key]->to($value) :
            $value;

        foreach (static::onChange() as $callback) {
            Closure::fromCallable($callback)->call($this, $this, $key, $value);
        }
    }

    public function __get(string $key): mixed
    {
        $fields     = static::describe();
        $value      = $this->store[$key] ?? null;

        foreach (static::onGet($key) as $callback) {
            $value = Closure::fromCallable($callback)->call($this, $this);
        }

        return isset($fields[$key]) ?
            $fields[$key]->from($value) :
            $value;
    }

    public function __call($name, $arguments): mixed
    {
        $fields     = static::describe();

        foreach ($fields as $t) {
            var_dump( $t );
        }

        return false;
    }

    public function setDirty(string $column, mixed $previous = true)
    {
        $this->dirty[$column] = $previous;
    }

    public function hasChanged(): bool
    {
        if (count($this->dirty)) return true;
        
        // Iterate thru relations, if any changed -> true

        return false;
    }

    public function toArray(?array $keys = null): array
    {
        $fields = static::describe();
        $values = is_null($keys) ?
            $this->store :
            array_intersect_key($this->store, $keys);
        
        array_walk($values, function(&$v, $k) use ($fields) {
            if (isset($fields[$k])) {
                $v = $fields[$k]->from($v);
            }
        });

        return $values;
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
        $this->dirty = [];

        return $this;
    }

    /**
     * @return static
     * 
     * @todo FIXME : Collections are not saved at all (aka huutista)
     * @todo FIXME : hasChanged() is a mess
     */
    public function save()
    {
        //if (!$this->hasChanged()) return $this;

        foreach (static::onSave() as $callback) {
            Closure::fromCallable($callback)->call($this, $this);
        }

        $primaryKey = static::getPrimaryKey();

        if ($this->$primaryKey) {

            $data = [];
            foreach (static::describe() as $name => $field) {
                if (in_array($field->type, [Column::TYPE_VIRTUAL])) continue;
                if (!array_key_exists($name, $this->dirty)) continue;
                $data[$field->alias ?? $name] = $this->store[$name] ?: $field->to($field->default);
            }

            if (static::hasTimestamps()) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            if (count($data)) {
                static::$db->update(
                    $this->getTable(),
                    $data,
                    [$primaryKey => $this->$primaryKey]
                );
            }

        } else {

            $data = [];
            foreach (static::describe() as $name => $field) {
                if (in_array($field->type, [Column::TYPE_VIRTUAL])) continue;
                $data[$field->alias ?? $name] = $this->store[$name] ?: $field->to($field->default);
            }

            if (!$data) return $this;

            if (static::hasTimestamps()) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            if (count($data)) {
                $this->$primaryKey = static::$db->insert(
                    $this->getTable(),
                    $data
                );
            }

        }

        foreach (static::afterSave() as $callback) {
            Closure::fromCallable($callback)->call($this, $this);
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

    /**
     * @return true|Validator 
     */
    public function validate(array $skip = []): array|bool
    {
        $validator = new Validator($this);

        return $validator->validate($skip);
    }
}
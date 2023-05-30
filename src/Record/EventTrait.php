<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

trait EventTrait
{
    static $events = [];

    public static function onBoot()
    {
        $cacheKey = sprintf('%s.boot', static::class);

        if (!isset(static::$events[$cacheKey])) {
            return static::$events[$cacheKey] = true;
        }

        return false;
    }

    public static function onGet(string $key, callable $callback = null)
    {
        $cacheKey = sprintf("%s.get.%s", static::class, $key);

        if (is_null($callback)) {
            return static::$events[$cacheKey] ?? [];
        } else {
            static::$events[$cacheKey][] = $callback;
        }
    }

    public static function onSet(string $key, callable $callback = null)
    {
        $cacheKey = sprintf("%s.set.%s", static::class, $key);

        if (is_null($callback)) {
            return static::$events[$cacheKey] ?? [];
        } else {
            static::$events[$cacheKey][] = $callback;
        }
    }

    public static function onLoad(callable $callback = null)
    {
        $cacheKey = sprintf("%s.load", static::class);

        if (is_null($callback)) {
            return static::$events[$cacheKey] ?? [];
        } else {
            static::$events[$cacheKey][] = $callback;
        }
    }

    public static function onSave(callable $callback = null)
    {
        $cacheKey = sprintf("%s.save", static::class);

        if (is_null($callback)) {
            return static::$events[$cacheKey] ?? [];
        } else {
            static::$events[$cacheKey][] = $callback;
        }
    }


}

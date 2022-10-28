<?php declare(strict_types = 1);

namespace Apitin\Database\Record;

trait CacheTrait
{
    static $cache = [];

    public static function cached(string $key, $setterWhenNotFound = null)
    {
        $cacheKey = sprintf("%s.%s", static::class, $key);

        if (!isset(static::$cache[$cacheKey])) {
            static::$cache[$cacheKey] = is_callable($setterWhenNotFound) ?
                $setterWhenNotFound() :
                $setterWhenNotFound;
        }

        return static::$cache[$cacheKey];        
    }
}

<?php

namespace Pianissimo\Component\Cache;

use DateInterval;
use DateTime;
use Pianissimo\Component\Cache\Exception\InvalidCacheKeyException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;

class CacheService implements CacheInterface
{
    private const DEFAULT_TTL = 86400;

    private const CACHE_DIR = __DIR__ . '/../../../var/cache/';

    /**
     * Validates the given cache key.
     * Invalid characters: {}()/\@:
     */
    public function validateKey($key): bool
    {
        return preg_match('/({|}|\(|\)|\\|\/|\@|\:)/', $key) === 0;
    }

    /**
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     */
    private function getExpireDateTime($ttl): DateTime
    {
        $now = new DateTime();

        if ($ttl instanceof DateInterval) {
            $dateInterval = $ttl;
        } else {
            if (is_numeric($ttl)) {
                $seconds = (int) $ttl;
            } else {
                $seconds = self::DEFAULT_TTL;
            }

            $dateInterval = DateInterval::createFromDateString($seconds . ' seconds');
        }

        return $now->add($dateInterval);
    }

    /**
     * Returns the path to the cache file of the given key
     */
    private function getPath($key): string
    {
        return self::CACHE_DIR . md5($key);
    }

    /**
     * Creates the given directory if not exists.
     */
    private function mkdirIfNotExists(string $dir): void
    {
        if (file_exists($dir) === false) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf("Directory '%s' was not created", $dir));
            }
        }
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        if ($this->validateKey($key) === false) {
            throw new InvalidCacheKeyException(sprintf("Given cache key '%s' not valid", $key));
        }

        $path = $this->getPath($key);

        if (file_exists($path) === false) {
            return $default;
        }

        $contents = file_get_contents($path);
        return $contents !== false ?: $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store.
     * @param mixed $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null): bool
    {
        $expireDateTime = $this->getExpireDateTime($ttl);

        if ($this->validateKey($key) === false) {
            throw new InvalidCacheKeyException(sprintf("Given cache key '%s' not valid", $key));
        }

        $this->mkdirIfNotExists(self::CACHE_DIR);
        return file_put_contents($this->getPath($key), $value);
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key): bool
    {
        if ($this->validateKey($key) === false) {
            throw new InvalidCacheKeyException(sprintf("Given cache key '%s' not valid", $key));
        }

        $path = $this->getPath($key);

        if (file_exists($path) === false) {
            return false;
        }

        return unlink($path);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool
    {
        $files = glob(self::CACHE_DIR . '*');

        foreach ($files as $file) {
            if (unlink($file) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[] = $this->get($key);
        }

        return $data;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key): bool
    {
        if ($this->validateKey($key) === false) {
            throw new InvalidCacheKeyException(sprintf("Given cache key '%s' not valid", $key));
        }

        return file_exists($this->getPath($key));
    }
}
# Caching
Caspar supports caching using the file system or apc. By default, caspar caches routing definitions, settings and
translated strings in the cache.

You can define which logging instance to be used by setting the cache strategy. The default caching strategy generated
by the `create_app` command-line task is file-based caching. 

## File-based caching
File-based caching can be enabled by passing an instance of the `\caspar\core\Cache` class with type 
`\caspar\core\Cache::TYPE_FILE` to the `\caspar\core\Caspar::setCacheStrategy()` method.

Here is an example from the default `index.php` created by the `create_app` command-line task:
```php
$cache_object = new \caspar\core\Cache(\caspar\core\Cache::TYPE_FILE, ['path' => CASPAR_CACHE_PATH]);
\caspar\core\Caspar::setCacheStrategy($cache_object);
```

The `CASPAR_CACHE_PATH` constant is defined automatically in the auto-generated `index.php` and by default points to
the `application/cache` folder of your application.

## In-memory caching (using apc)
Enable apc-based caching can by passing an instance of the `\caspar\core\Cache` class with type 
`\caspar\core\Cache::TYPE_APC` to the `\caspar\core\Caspar::setCacheStrategy()` method:
```php
$cache_object = new \caspar\core\Cache(\caspar\core\Cache::TYPE_APC);
\caspar\core\Caspar::setCacheStrategy($cache_object);
```

## Temporarily disabling / enable caching
You can temporarily disable the cache by calling the `disable()` method on the cache object:

```php
$cache_object = new \caspar\core\Cache(\caspar\core\Cache::TYPE_APC);
\caspar\core\Caspar::setCacheStrategy($cache_object);

# Disable the cache
$cache_object->disable();
# or
\caspar\core\Caspar::getCache()->disable();
```

You can re-enable the cache by calling the `enable()` method on the cache object.

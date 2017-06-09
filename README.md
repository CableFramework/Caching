# Caching
Cache library for Cable Framework


## memcache


```php 

$container = Factory::create();
$container->addProvider(CachingProvider::class);
$container->addProvider(MemcacheDriverProvider::class);

$cache = Caching::create($container);

$memcache = $cache->driver('memcache');


```

## Redis


```php 

$container = Factory::create();
$container->addProvider(CachingProvider::class);
$container->addProvider(RedisDriverProvider::class);

$cache = Caching::create($container);

$memcache = $cache->driver('redis');


```

## array


```php 

$container = Factory::create();
$container->addProvider(CachingProvider::class);
$cache = Caching::create($container);

$memcache = $cache->driver('array');


```
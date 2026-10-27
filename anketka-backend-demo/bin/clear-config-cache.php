<?php

declare(strict_types=1);

chdir(__DIR__ . '/../');

require 'vendor/autoload.php';

$config = include 'config/config.php';

use Laminas\ServiceManager\ServiceManager;
use Psr\Cache\CacheItemPoolInterface;

if (! isset($config['config_cache_path'])) {
    echo "No configuration cache path found" . PHP_EOL;
    exit(0);
}

if (! file_exists($config['config_cache_path'])) {
    printf(
        "Configured config cache file '%s' not found%s",
        $config['config_cache_path'],
        PHP_EOL
    );
    exit(0);
}

if (false === unlink($config['config_cache_path'])) {
    printf(
        "Error removing config cache file '%s'%s",
        $config['config_cache_path'],
        PHP_EOL
    );
    exit(1);
}

printf(
    "Removed configured config cache file '%s'%s",
    $config['config_cache_path'],
    PHP_EOL
);

$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;
$container = new ServiceManager($dependencies);
try {
    $cache = $container->get(CacheItemPoolInterface::class);
    $cacheKey = 'router_routes_' . md5('src/App/Http/Handler');
    $cacheItem = $cache->getItem($cacheKey);
    if ($cacheItem->isHit()) {
        $cache->deleteItem($cacheKey);
        echo "Removed router cache" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Warning: Could not clear router cache: " . $e->getMessage() . PHP_EOL;
}

$proxyCacheDir = __DIR__ . '/../data/doctrine-cache';
$proxyFiles = glob($proxyCacheDir . '/*.php') ?: [];
foreach ($proxyFiles as $file) {
    unlink($file);
}
if ($proxyFiles) {
    printf("Removed %d doctrine proxy file(s)%s", count($proxyFiles), PHP_EOL);
}

exit(0);

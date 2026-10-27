<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;

class CacheFactory
{
    public function __invoke(ContainerInterface $container): CacheItemPoolInterface
    {
        $config = $container->get('config');
        $debug = $config['debug'] ?? false;
        // В debug режиме не кэшируем
        return $debug
            ? new NullAdapter()
            : new FilesystemAdapter('global', 0, 'data/global-cache');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Router;

use Mezzio\Application;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class RouterInitializerFactory
{
    public function __invoke(ContainerInterface $container): RouterInitializer
    {
        $config = $container->get('config');

        return new RouterInitializer(
            $container->get(Application::class),
            $container->get(CacheItemPoolInterface::class),
            $container->get(LoggerInterface::class),
            $config['app']['environment'] ?? 'prod'
        );
    }
}
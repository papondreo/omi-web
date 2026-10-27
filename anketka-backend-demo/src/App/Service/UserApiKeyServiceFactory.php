<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class UserApiKeyServiceFactory
{
    public function __invoke(ContainerInterface $container): UserApiKeyService
    {
        $config = $container->get('config');
        $ttlDays = (int) ($config['app']['api_key_ttl_days'] ?? 0);

        return new UserApiKeyService(
            $container->get(EntityManager::class),
            $container->get(IpAddressService::class),
            $ttlDays,
        );
    }
}
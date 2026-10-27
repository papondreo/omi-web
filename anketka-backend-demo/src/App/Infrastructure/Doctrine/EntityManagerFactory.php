<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;

class EntityManagerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EntityManager
    {
        $config = $container->get('config');

        /** @var CacheItemPoolInterface $cache */
        $cache = $container->get(CacheItemPoolInterface::class);

        $entityManagerConfig = ORMSetup::createAttributeMetadataConfig(
            [__DIR__ . '/../../Entity'], // путь к вашим Entity
            $config['debug'],
            cache: $cache
        );

        $entityManagerConfig->setProxyNamespace('AnketkaDoctrineProxies');
        $entityManagerConfig->setProxyDir(__DIR__ . '/../../../../data/doctrine-cache');

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => $config['db']['host'],
            'port' => $config['db']['port'] ?? 3306,
            'dbname' => $config['db']['dbname'],
            'user' => $config['db']['user'],
            'password' => $config['db']['password'],
            'charset' => 'utf8mb4',
            'driverOptions' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
            ],
        ], $entityManagerConfig);

        return new EntityManager($connection, $entityManagerConfig);
    }
}

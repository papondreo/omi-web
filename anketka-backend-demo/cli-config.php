<?php

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

require_once __DIR__ . '/vendor/autoload.php';

// Получаем контейнер Mezzio/Laminas
$container = require __DIR__ . '/config/container.php';

// Получаем EntityManager из контейнера
$entityManager = $container->get(\Doctrine\ORM\EntityManager::class);

// Путь до вашего конфига миграций (можно migrations.php или migrations-db.php)
$config = new PhpFile(__DIR__ . '/migrations.php'); // или migrations-db.php

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));

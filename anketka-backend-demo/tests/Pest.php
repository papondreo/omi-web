<?php

declare(strict_types=1);

use Tests\TestCase;
use Tests\IntegrationTestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Unit');
uses(IntegrationTestCase::class)->in('Integration');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Свежий контейнер на каждый тест: сервисы в нём можно подменять моками
 * через $this->container->setService() / setFactory()
 */
function getTestContainer(array $configOverrides = []): \Laminas\ServiceManager\ServiceManager
{
    $config = require __DIR__ . '/../config/config.php';

    // Применяем переопределения конфигурации
    foreach ($configOverrides as $key => $value) {
        $config[$key] = $value;
    }

    $container = new \Laminas\ServiceManager\ServiceManager();
    $container->setService('config', $config);

    $dependencies = $config['dependencies'] ?? [];
    $factories = $dependencies['factories'] ?? [];
    $aliases = $dependencies['aliases'] ?? [];
    $invokables = $dependencies['invokables'] ?? [];
    $abstractFactories = $dependencies['abstract_factories'] ?? [];

    foreach ($factories as $name => $factory) {
        $container->setFactory($name, $factory);
    }

    foreach ($aliases as $alias => $target) {
        $container->setAlias($alias, $target);
    }

    foreach ($invokables as $name => $invokable) {
        $container->setInvokableClass($name, $invokable);
    }

    foreach ($abstractFactories as $abstractFactory) {
        $container->addAbstractFactory($abstractFactory);
    }

    return $container;
}

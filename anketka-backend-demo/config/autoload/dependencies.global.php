<?php

declare(strict_types=1);

use App\Http\Handler\GetSurveys\GetSurveysHandler;
use App\Http\Handler\GetSurveys\GetSurveysHandlerFactory;
use App\Http\Router\RouterInitializer;
use App\Http\Router\RouterInitializerFactory;
use App\Infrastructure\Cache\CacheFactory;
use App\Infrastructure\Doctrine\EntityManagerFactory;
use App\Infrastructure\Panel\PanelApiFactory;
use App\Infrastructure\Panel\PanelApiInterface;
use App\Service\UserApiKeyService;
use App\Service\UserApiKeyServiceFactory;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Psr\Cache\CacheItemPoolInterface;

return [
    'dependencies' => [
        'factories' => [
            // Infrastructure services
            EntityManager::class => EntityManagerFactory::class,
            CacheItemPoolInterface::class => CacheFactory::class,
            PanelApiInterface::class => PanelApiFactory::class,
            RouterInitializer::class => RouterInitializerFactory::class,

            // Application services
            UserApiKeyService::class => UserApiKeyServiceFactory::class,

            // Handlers: явная фабрика нужна, только когда в конструктор идут скаляры из конфига.
            // Всё остальное собирает ReflectionBasedAbstractFactory по типам аргументов.
            GetSurveysHandler::class => GetSurveysHandlerFactory::class,
        ],
        'abstract_factories' => [
            ReflectionBasedAbstractFactory::class,
        ],
    ],
];

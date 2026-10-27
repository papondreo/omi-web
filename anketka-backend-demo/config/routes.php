<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/api/docs', \App\Http\Handler\Doc\DocHandler::class);
    /** @var \App\Http\Router\RouterInitializer $routerInitializer */
    $routerInitializer = $container->get(\App\Http\Router\RouterInitializer::class);

    $routerInitializer->registerRoutes();
};

<?php

declare(strict_types=1);

use App\Http\JsonNotFoundHandler;
use App\Http\Middleware\JsonErrorHandler;
use App\Http\Middleware\RouteParamsMiddleware;
use App\Http\RequestDtoFactory\RequestDtoValidationHandler;
use Mezzio\Application;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Psr\Container\ContainerInterface;

/**
 * Setup middleware pipeline:
 */

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->pipe(JsonErrorHandler::class);
    $app->pipe(RequestDtoValidationHandler::class);
    $app->pipe(ServerUrlMiddleware::class);
    $app->pipe(BodyParamsMiddleware::class);
    $app->pipe(RouteMiddleware::class);
    $app->pipe(RouteParamsMiddleware::class);
    $app->pipe(ImplicitHeadMiddleware::class);
    $app->pipe(ImplicitOptionsMiddleware::class);
    $app->pipe(MethodNotAllowedMiddleware::class);
    $app->pipe(UrlHelperMiddleware::class);
    $app->pipe(DispatchMiddleware::class);
    $app->pipe(JsonNotFoundHandler::class);
};

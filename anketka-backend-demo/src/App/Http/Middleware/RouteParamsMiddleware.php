<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Handler\Common\ErrorResponseDto;
use App\Http\RouteParamsFactory\RouteParamsValidationException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteParamsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RouteParamsValidationException $e) {
            $properties = $e->getProperties();
            $firstError = reset($properties) ?: 'Некорректные параметры маршрута';
            
            return new JsonResponse(
                new ErrorResponseDto($firstError),
                400
            );
        }
    }
}
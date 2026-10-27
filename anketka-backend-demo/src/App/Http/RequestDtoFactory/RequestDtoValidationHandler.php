<?php

declare(strict_types=1);

namespace App\Http\RequestDtoFactory;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Превращает RequestDtoValidationException, брошенный из любого хендлера,
 * в ответ 400 вида {"properties": {"<поле>": "<сообщение на русском>"}}
 */
class RequestDtoValidationHandler implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (RequestDtoValidationException $e) {
            return new JsonResponse([
                'properties' => $e->getProperties()
            ], 400);
        }
    }
}

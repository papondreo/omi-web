<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Handler\Common\ErrorResponseDto;
use App\Service\UserApiKeyService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserApiKeyService $apiKeyService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $apiKey = $request->getHeaderLine('X-API-Key');

        if (empty($apiKey)) {
            return new JsonResponse(
                new ErrorResponseDto('API key is required'),
                401
            );
        }

        $userApiKey = $this->apiKeyService->findActiveByToken($apiKey);

        if ($userApiKey === null) {
            return new JsonResponse(
                new ErrorResponseDto('Invalid API key'),
                401
            );
        }

        $user = $userApiKey->getUser();

        if (!$user->isActivated()) {
            return new JsonResponse(
                new ErrorResponseDto('User not activated'),
                403
            );
        }

        $this->apiKeyService->touchLastUsed($userApiKey);

        // Добавляем пользователя и его токен в атрибуты запроса для использования в handlers
        $request = $request
            ->withAttribute('user', $user)
            ->withAttribute('userApiKey', $userApiKey);

        return $handler->handle($request);
    }
}
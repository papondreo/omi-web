<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Service\UserApiKeyService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class OptionalAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserApiKeyService $apiKeyService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $apiKey = $request->getHeaderLine('X-API-Key');

        // Если API key предоставлен, проверяем его
        if (!empty($apiKey)) {
            $userApiKey = $this->apiKeyService->findActiveByToken($apiKey);

            if ($userApiKey !== null) {
                $user = $userApiKey->getUser();

                if ($user->isActivated()) {
                    $this->apiKeyService->touchLastUsed($userApiKey);
                    $request = $request
                        ->withAttribute('user', $user)
                        ->withAttribute('userApiKey', $userApiKey);
                }
            }
        }

        // Продолжаем выполнение независимо от наличия авторизации
        return $handler->handle($request);
    }
}
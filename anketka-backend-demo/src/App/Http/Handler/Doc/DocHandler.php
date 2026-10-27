<?php

declare(strict_types=1);

namespace App\Http\Handler\Doc;

use Laminas\Diactoros\Response;
use OpenApi\Attributes\Info;
use OpenApi\Attributes\SecurityScheme;
use OpenApi\Attributes\Server;
use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Отдаёт OpenAPI-спецификацию, собранную из атрибутов над хендлерами и DTO.
 * Тот же набор атрибутов RouterInitializer использует для регистрации роутов.
 */
#[Info(
    version: '1.0',
    description: 'Демо-версия API Анкетки',
    title: 'Anketka API (demo)',
)]
#[Server(
    url: 'http://localhost:8350/',
    description: 'localhost'
)]
#[SecurityScheme(
    securityScheme: 'ApiKeyAuth',
    type: 'apiKey',
    description: 'API key authorization',
    name: 'X-API-Key',
    in: 'header'
)]
class DocHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $openapi = (new Generator)->generate(['./src/App']);

        if (!$openapi) {
            return new Response\TextResponse('{}', headers: ['Content-Type' => 'application/json']);
        }

        // Получаем JSON и преобразуем пути
        $json = $openapi->toJson();
        $data = json_decode($json, true);

        if (isset($data['paths'])) {
            $newPaths = [];
            foreach ($data['paths'] as $path => $pathItem) {
                // Скрываем из документации операции, помеченные x: ['internal' => true]
                $pathItem = array_filter(
                    $pathItem,
                    fn($operation) => !is_array($operation) || empty($operation['x-internal'])
                );
                if ($pathItem === []) {
                    continue;
                }

                // Заменяем :param на {param} в пути
                $newPath = preg_replace('/:(\w+)/', '{$1}', $path);
                $newPaths[$newPath] = $pathItem;
            }
            $data['paths'] = $newPaths;
        }

        return new Response\TextResponse(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            headers: ['Content-Type' => 'application/json']
        );
    }
}

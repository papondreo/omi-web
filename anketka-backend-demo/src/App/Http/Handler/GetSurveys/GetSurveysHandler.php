<?php

declare(strict_types=1);

namespace App\Http\Handler\GetSurveys;

use App\Entity\User;
use App\Http\Handler\Common\ApiKeySecurity;
use App\Http\Handler\Common\ErrorResponseDto;
use App\Infrastructure\Panel\PanelApiInterface;
use App\Infrastructure\Panel\PanelSurvey;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

#[Get(
    path: '/api/surveys',
    operationId: 'surveys',
    security: ApiKeySecurity::VALUE,
    tags: ['Survey'],
    responses: [
        new Response(
            response: 200,
            description: 'Список опросов',
            content: new JsonContent(ref: GetSurveysResponseDto::class)
        ),
        new Response(response: 401, description: 'Не авторизован'),
    ]
)]
readonly class GetSurveysHandler implements RequestHandlerInterface
{
    public function __construct(
        private PanelApiInterface      $panelApi,
        private CacheItemPoolInterface $cache,
        private int                    $cacheTtl,
        private LoggerInterface        $logger,
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Пользователя кладёт в атрибуты AuthenticationMiddleware:
        // он подключается автоматически, потому что у роута задан security
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            return new JsonResponse(
                new ErrorResponseDto('Не авторизован'),
                401
            );
        }

        $panelistId = $user->getPanelistId();

        try {
            // Панель — внешний сервис, поэтому ответ кэшируем на короткое время
            $cacheItem = $this->cacheTtl > 0 ? $this->cache->getItem('surveys_' . $panelistId) : null;

            if ($cacheItem !== null && $cacheItem->isHit()) {
                return new JsonResponse($this->buildResponse($cacheItem->get()));
            }

            $surveys = $this->panelApi->getSurveys($panelistId);

            if ($cacheItem !== null) {
                $cacheItem->set($surveys)->expiresAfter($this->cacheTtl);
                $this->cache->save($cacheItem);
            }

            return new JsonResponse($this->buildResponse($surveys));

        } catch (\Exception $e) {
            $this->logger->error('Failed to load surveys', [
                'panelist_id' => $panelistId,
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse(
                new ErrorResponseDto('Ошибка получения опросов'),
                500
            );
        }
    }

    /**
     * @param PanelSurvey[] $surveys
     */
    private function buildResponse(array $surveys): GetSurveysResponseDto
    {
        $response = new GetSurveysResponseDto();
        $response->surveys = array_map(fn(PanelSurvey $survey) => SurveyDto::fromPanelSurvey($survey), $surveys);

        return $response;
    }
}

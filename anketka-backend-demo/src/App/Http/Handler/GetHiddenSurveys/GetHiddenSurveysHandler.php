<?php

declare(strict_types=1);

namespace App\Http\Handler\GetHiddenSurveys;

use App\Entity\User;
use App\Http\Handler\Common\ApiKeySecurity;
use App\Http\Handler\Common\ErrorResponseDto;
use App\Http\Handler\GetSurveys\GetSurveysResponseDto;
use App\Http\Handler\GetSurveys\SurveyDto;
use App\Infrastructure\Panel\PanelApiInterface;
use App\Infrastructure\Panel\PanelSurvey;
use App\Service\HiddenSurveyService;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

// Путь не /api/surveys/hidden: он совпал бы с шаблоном /api/surveys/:id
#[Get(
    path: '/api/hidden-surveys',
    operationId: 'hiddenSurveys',
    security: ApiKeySecurity::VALUE,
    tags: ['Survey'],
    responses: [
        new Response(
            response: 200,
            description: 'Скрытые пользователем опросы',
            content: new JsonContent(ref: GetSurveysResponseDto::class)
        ),
        new Response(response: 401, description: 'Не авторизован'),
    ]
)]
readonly class GetHiddenSurveysHandler implements RequestHandlerInterface
{
    public function __construct(
        private PanelApiInterface   $panelApi,
        private HiddenSurveyService $hiddenSurveyService,
        private LoggerInterface     $logger,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof User) {
            return new JsonResponse(
                new ErrorResponseDto('Не авторизован'),
                401
            );
        }

        $response = new GetSurveysResponseDto();
        $response->surveys = [];

        $hiddenIds = $this->hiddenSurveyService->getHiddenIds($user);
        if ($hiddenIds === []) {
            return new JsonResponse($response);
        }

        try {
            $surveys = $this->panelApi->getSurveys($user->getPanelistId());
        } catch (\Exception $e) {
            $this->logger->error('Failed to load hidden surveys', [
                'panelist_id' => $user->getPanelistId(),
                'error' => $e->getMessage(),
            ]);
            return new JsonResponse(
                new ErrorResponseDto('Ошибка получения опросов'),
                500
            );
        }

        // Опросы, которые уже пропали из панели, не показываем: вернуть их некуда
        $hidden = array_flip($hiddenIds);
        $response->surveys = array_values(array_map(
            fn(PanelSurvey $survey) => SurveyDto::fromPanelSurvey($survey),
            array_filter($surveys, fn(PanelSurvey $survey) => isset($hidden[$survey->id]))
        ));

        return new JsonResponse($response);
    }
}

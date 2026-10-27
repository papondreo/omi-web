<?php

declare(strict_types=1);

namespace App\Http\Handler\HideSurvey;

use App\Entity\User;
use App\Http\Handler\Common\ApiKeySecurity;
use App\Http\Handler\Common\ErrorResponseDto;
use App\Http\Handler\GetSurvey\GetSurveyRouteParams;
use App\Http\RouteParamsFactory\RouteParamsFactory;
use App\Infrastructure\Panel\PanelApiInterface;
use App\Service\HiddenSurveyService;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Post(
    path: '/api/surveys/:id/hide',
    operationId: 'hideSurvey',
    security: ApiKeySecurity::VALUE,
    tags: ['Survey'],
    parameters: [
        new Parameter(
            name: 'id',
            description: 'ID опроса',
            in: 'path',
            required: true,
            schema: new Schema(type: 'integer')
        ),
    ],
    responses: [
        new Response(response: 204, description: 'Опрос скрыт (повторное скрытие — тоже 204)'),
        new Response(response: 400, description: 'Некорректный ID'),
        new Response(response: 401, description: 'Не авторизован'),
        new Response(response: 404, description: 'Опрос не найден'),
    ]
)]
readonly class HideSurveyHandler implements RequestHandlerInterface
{
    public function __construct(
        private PanelApiInterface   $panelApi,
        private HiddenSurveyService $hiddenSurveyService,
        private RouteParamsFactory  $routeParamsFactory,
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

        $params = $this->routeParamsFactory->fromRouteResult($request, GetSurveyRouteParams::class);

        // Скрыть можно только опрос, который есть у пользователя в панели,
        // иначе в БД копились бы отметки о несуществующих опросах
        if ($this->panelApi->getSurvey($user->getPanelistId(), $params->id) === null) {
            return new JsonResponse(
                new ErrorResponseDto('Опрос не найден'),
                404
            );
        }

        $this->hiddenSurveyService->hide($user, $params->id);

        return new EmptyResponse(204);
    }
}

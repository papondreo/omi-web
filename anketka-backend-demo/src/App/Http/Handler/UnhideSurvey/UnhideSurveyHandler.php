<?php

declare(strict_types=1);

namespace App\Http\Handler\UnhideSurvey;

use App\Entity\User;
use App\Http\Handler\Common\ApiKeySecurity;
use App\Http\Handler\Common\ErrorResponseDto;
use App\Http\Handler\GetSurvey\GetSurveyRouteParams;
use App\Http\RouteParamsFactory\RouteParamsFactory;
use App\Service\HiddenSurveyService;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Delete(
    path: '/api/surveys/:id/hide',
    operationId: 'unhideSurvey',
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
        new Response(response: 204, description: 'Опрос возвращён в список (если не был скрыт — тоже 204)'),
        new Response(response: 400, description: 'Некорректный ID'),
        new Response(response: 401, description: 'Не авторизован'),
    ]
)]
readonly class UnhideSurveyHandler implements RequestHandlerInterface
{
    public function __construct(
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

        // В панель не ходим: вернуть можно и опрос, который из неё уже пропал
        $this->hiddenSurveyService->unhide($user, $params->id);

        return new EmptyResponse(204);
    }
}

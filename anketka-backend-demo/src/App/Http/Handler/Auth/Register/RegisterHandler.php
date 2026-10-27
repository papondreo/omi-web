<?php

declare(strict_types=1);

namespace App\Http\Handler\Auth\Register;

use App\Http\Handler\Common\ErrorResponseDto;
use App\Http\RequestDtoFactory\RequestDtoFactory;
use App\Service\EmailAlreadyRegisteredException;
use App\Service\RegisterService;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Post(
    path: '/api/register',
    operationId: 'register',
    requestBody: new RequestBody(content: new JsonContent(ref: RegisterRequestDto::class)),
    tags: ['Auth'],
    responses: [
        new Response(response: 200, description: 'Пользователь зарегистрирован', content: new JsonContent(ref: RegisterResponseDto::class)),
        new Response(response: 400, description: 'Ошибка валидации'),
        new Response(response: 409, description: 'Email уже зарегистрирован', content: new JsonContent(ref: ErrorResponseDto::class)),
    ]
)]
readonly class RegisterHandler implements RequestHandlerInterface
{
    public function __construct(
        private RegisterService   $registerService,
        private RequestDtoFactory $dtoFactory,
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // При ошибке валидации бросается RequestDtoValidationException,
        // её перехватывает RequestDtoValidationHandler и отдаёт 400
        $jsonRequest = $this->dtoFactory->fromParsedBody($request, RegisterRequestDto::class);

        try {
            $apiKey = $this->registerService->register($jsonRequest->email, $jsonRequest->password, $request);
        } catch (EmailAlreadyRegisteredException) {
            return new JsonResponse(
                new ErrorResponseDto('Email уже используется'),
                409
            );
        }

        $response = new RegisterResponseDto();
        $response->apiKey = $apiKey->getApiKey();

        return new JsonResponse($response);
    }
}

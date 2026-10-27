<?php

declare(strict_types=1);

namespace App\Http\Handler\Auth\Login;

use App\Http\Handler\Common\ErrorResponseDto;
use App\Http\RequestDtoFactory\RequestDtoFactory;
use App\Service\InvalidCredentialsException;
use App\Service\LoginService;
use App\Service\UserNotActivatedException;
use Laminas\Diactoros\Response\JsonResponse;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Post(
    path: '/api/login',
    operationId: 'login',
    requestBody: new RequestBody(content: new JsonContent(ref: LoginRequestDto::class)),
    tags: ['Auth'],
    responses: [
        new Response(response: 200, description: 'Вход выполнен', content: new JsonContent(ref: LoginResponseDto::class)),
        new Response(response: 400, description: 'Ошибка валидации'),
        new Response(response: 401, description: 'Неверный email или пароль', content: new JsonContent(ref: ErrorResponseDto::class)),
        new Response(response: 403, description: 'Аккаунт не активирован', content: new JsonContent(ref: ErrorResponseDto::class)),
    ]
)]
readonly class LoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private LoginService      $loginService,
        private RequestDtoFactory $dtoFactory,
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $jsonRequest = $this->dtoFactory->fromParsedBody($request, LoginRequestDto::class);

        try {
            $apiKey = $this->loginService->login($jsonRequest->email, $jsonRequest->password, $request);
        } catch (InvalidCredentialsException) {
            // Одинаковый ответ для неизвестного email и неверного пароля:
            // по нему нельзя узнать, зарегистрирован ли адрес
            return new JsonResponse(
                new ErrorResponseDto('Неверный email или пароль'),
                401
            );
        } catch (UserNotActivatedException) {
            return new JsonResponse(
                new ErrorResponseDto('Аккаунт не активирован'),
                403
            );
        }

        $response = new LoginResponseDto();
        $response->apiKey = $apiKey->getApiKey();

        return new JsonResponse($response);
    }
}

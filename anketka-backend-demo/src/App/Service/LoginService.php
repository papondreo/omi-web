<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserApiKey;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Вход по email и паролю: проверка пароля + новый API-ключ на каждый вход.
 *
 * В боевом проекте пароль проверяет панель, а сверх этого есть капча,
 * rate limit по email, антифрод, реактивация после блокировки за неактивность
 * и повторная отправка письма активации. В демо пароль сверяется с хешем в БД.
 */
readonly class LoginService
{
    public function __construct(
        private EntityManager     $em,
        private UserApiKeyService $apiKeyService,
    ) {
    }

    /**
     * @throws InvalidCredentialsException
     * @throws UserNotActivatedException
     */
    public function login(string $email, string $password, ServerRequestInterface $request): UserApiKey
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user === null || !password_verify($password, $user->getPassword())) {
            throw new InvalidCredentialsException();
        }

        if (!$user->isActivated()) {
            throw new UserNotActivatedException($email);
        }

        return $this->apiKeyService->createKey($user, $request);
    }
}

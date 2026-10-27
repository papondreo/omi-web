<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserApiKey;
use App\Infrastructure\Panel\PanelApiInterface;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Регистрация: участник в панели + локальный пользователь + API-ключ.
 *
 * В боевом проекте здесь ещё капча, проверка email, антифрод, партнёрская
 * атрибуция и письмо активации. В демо пользователь активируется сразу.
 */
readonly class RegisterService
{
    public function __construct(
        private EntityManager     $em,
        private PanelApiInterface $panelApi,
        private UserApiKeyService $apiKeyService,
    ) {
    }

    /**
     * @throws EmailAlreadyRegisteredException
     */
    public function register(string $email, string $password, ServerRequestInterface $request): UserApiKey
    {
        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing !== null) {
            throw new EmailAlreadyRegisteredException($email);
        }

        $panelistId = $this->panelApi->registerMember($email);

        $user = User::register($email, $password, $panelistId)
            ->setActivated(true);

        $this->em->persist($user);
        $this->em->flush();

        return $this->apiKeyService->createKey($user, $request);
    }
}

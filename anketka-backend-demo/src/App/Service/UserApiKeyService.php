<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserApiKey;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface;

readonly class UserApiKeyService
{
    private const string KEY_PREFIX = 'ank_';
    private const int KEY_BYTES = 24;
    private const int TOUCH_INTERVAL_SECONDS = 3600;

    public function __construct(
        private EntityManager    $em,
        private IpAddressService $ipAddressService,
        private int              $ttlDays = 0,
    ) {
    }

    public function createKey(User $user, ServerRequestInterface $request): UserApiKey
    {
        $now = new \DateTimeImmutable();
        $expiresAt = $this->ttlDays > 0
            ? $now->modify("+{$this->ttlDays} days")
            : null;

        $userAgent = $request->getHeaderLine('User-Agent');
        $userAgent = $userAgent !== '' ? substr($userAgent, 0, 512) : null;

        $key = new UserApiKey();
        $key->setUser($user)
            ->setApiKey(self::generateToken())
            ->setUserAgent($userAgent)
            ->setIp($this->ipAddressService->getClientIp($request))
            ->setCreatedAt($now)
            ->setLastUsedAt($now)
            ->setExpiresAt($expiresAt);

        $this->em->persist($key);
        $this->em->flush();

        return $key;
    }

    public function findActiveByToken(string $token): ?UserApiKey
    {
        $key = $this->em->getRepository(UserApiKey::class)
            ->findOneBy(['apiKey' => $token]);

        if ($key === null) {
            return null;
        }

        return $key->isActive(new \DateTimeImmutable()) ? $key : null;
    }

    public function findLatestActiveForUser(User $user): ?UserApiKey
    {
        $now = new \DateTimeImmutable();

        $qb = $this->em->getRepository(UserApiKey::class)
            ->createQueryBuilder('k')
            ->where('k.user = :user')
            ->andWhere('k.revokedAt IS NULL')
            ->andWhere('(k.expiresAt IS NULL OR k.expiresAt > :now)')
            ->orderBy('k.lastUsedAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('now', $now);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function touchLastUsed(UserApiKey $key): void
    {
        $now = new \DateTimeImmutable();

        if (($now->getTimestamp() - $key->getLastUsedAt()->getTimestamp()) < self::TOUCH_INTERVAL_SECONDS) {
            return;
        }

        $key->setLastUsedAt($now);
        $this->em->flush();
    }

    public function revokeAllForUser(User $user): int
    {
        return (int) $this->em->createQuery(
            'UPDATE App\Entity\UserApiKey k
             SET k.revokedAt = :now
             WHERE k.user = :user AND k.revokedAt IS NULL'
        )->execute([
            'now'  => new \DateTimeImmutable(),
            'user' => $user,
        ]);
    }

    public function revokeAllForUserExcept(User $user, string $exceptToken): int
    {
        return (int) $this->em->createQuery(
            'UPDATE App\Entity\UserApiKey k
             SET k.revokedAt = :now
             WHERE k.user = :user AND k.revokedAt IS NULL AND k.apiKey != :except'
        )->execute([
            'now'    => new \DateTimeImmutable(),
            'user'   => $user,
            'except' => $exceptToken,
        ]);
    }

    public function revokeByToken(string $token): bool
    {
        $affected = (int) $this->em->createQuery(
            'UPDATE App\Entity\UserApiKey k
             SET k.revokedAt = :now
             WHERE k.apiKey = :token AND k.revokedAt IS NULL'
        )->execute([
            'now'   => new \DateTimeImmutable(),
            'token' => $token,
        ]);

        return $affected > 0;
    }

    private static function generateToken(): string
    {
        return self::KEY_PREFIX . bin2hex(random_bytes(self::KEY_BYTES));
    }
}
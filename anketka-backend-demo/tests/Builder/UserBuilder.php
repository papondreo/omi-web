<?php

declare(strict_types=1);

namespace Tests\Builder;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;

/**
 * Тестовые данные создаются только через билдеры: у каждого поля есть
 * разумное значение по умолчанию, тест переопределяет только то, что проверяет
 */
class UserBuilder
{
    private string $email = 'test@example.com';
    private string $password = 'password123';
    private bool $activated = true;
    private int $panelistId = 123456;
    private ?string $id = null;
    private ?EntityManager $entityManager = null;

    public function __construct(?EntityManager $entityManager = null)
    {
        $this->entityManager = $entityManager;
    }

    public static function create(?EntityManager $entityManager = null): self
    {
        return new self($entityManager);
    }

    public function withEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function withActivated(bool $activated): self
    {
        $this->activated = $activated;
        return $this;
    }

    public function withPanelistId(int $panelistId): self
    {
        $this->panelistId = $panelistId;
        return $this;
    }

    public function withId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Создает и возвращает User entity
     */
    public function build(): User
    {
        return (new User())
            ->setId($this->id ?? Uuid::uuid4()->toString())
            ->setEmail($this->email)
            ->setPassword(password_hash($this->password, PASSWORD_DEFAULT))
            ->setActivated($this->activated)
            ->setPanelistId($this->panelistId)
            ->setCreatedAt(new \DateTimeImmutable());
    }

    /**
     * Создает User, сохраняет в БД и выполняет flush
     */
    public function persistAndFlush(): User
    {
        if ($this->entityManager === null) {
            throw new \RuntimeException('EntityManager is required for persistAndFlush operation');
        }

        $user = $this->build();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
}

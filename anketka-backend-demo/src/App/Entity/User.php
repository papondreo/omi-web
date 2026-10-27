<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;

/**
 * Пользователь сайта. ID — UUID v4, потому что уходит на фронт.
 *
 * panelistId — ID участника во внешней панельной платформе: баланс, опросы и
 * анкетные данные (datapoints) живут там, локально храним только связку.
 */
#[Entity]
#[Table(name: 'users')]
class User
{
    #[Id]
    #[Column(type: 'guid')]
    private string $id;

    #[Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[Column(type: 'string', length: 255)]
    private string $password;

    #[Column(type: 'boolean')]
    private bool $activated = false;

    #[Column(type: 'integer', unique: true)]
    private int $panelistId;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public static function register(string $email, string $plainPassword, int $panelistId): self
    {
        return (new self())
            ->setId(Uuid::uuid4()->toString())
            ->setEmail($email)
            ->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT))
            ->setPanelistId($panelistId)
            ->setCreatedAt(new \DateTimeImmutable());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $passwordHash): self
    {
        $this->password = $passwordHash;
        return $this;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;
        return $this;
    }

    public function getPanelistId(): int
    {
        return $this->panelistId;
    }

    public function setPanelistId(int $panelistId): self
    {
        $this->panelistId = $panelistId;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Опрос, который пользователь скрыл из своего списка.
 *
 * Сами опросы живут во внешней панели, поэтому здесь хранится только отметка:
 * surveyId — ID опроса в панели, а не связь с нашей таблицей
 */
#[Entity]
#[Table(name: 'hidden_surveys')]
#[UniqueConstraint(name: 'uniq_hidden_surveys_user_survey', columns: ['user_id', 'survey_id'])]
class HiddenSurvey
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    private ?int $id = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[Column(name: 'survey_id', type: 'integer')]
    private int $surveyId;

    #[Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    public function setSurveyId(int $surveyId): self
    {
        $this->surveyId = $surveyId;
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

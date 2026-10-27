<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\HiddenSurvey;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;

/**
 * Опросы, которые пользователь скрыл из своего списка.
 * hide и unhide идемпотентны: повторный вызов не меняет результат и не считается ошибкой
 */
readonly class HiddenSurveyService
{
    public function __construct(
        private EntityManager $em,
    ) {
    }

    public function hide(User $user, int $surveyId): void
    {
        $existing = $this->em->getRepository(HiddenSurvey::class)
            ->findOneBy(['user' => $user, 'surveyId' => $surveyId]);

        if ($existing !== null) {
            return;
        }

        $hidden = (new HiddenSurvey())
            ->setUser($user)
            ->setSurveyId($surveyId)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($hidden);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // Параллельный запрос (двойной клик) успел записать отметку раньше — результат тот же.
            // После исключения EntityManager закрыт, поэтому дальше в этом запросе с БД не работаем
        }
    }

    public function unhide(User $user, int $surveyId): void
    {
        $this->em->createQuery(
            'DELETE FROM App\Entity\HiddenSurvey h
             WHERE h.user = :user AND h.surveyId = :surveyId'
        )->execute([
            'user'     => $user,
            'surveyId' => $surveyId,
        ]);
    }

    /**
     * @return int[]
     */
    public function getHiddenIds(User $user): array
    {
        $rows = $this->em->createQuery(
            'SELECT h.surveyId FROM App\Entity\HiddenSurvey h
             WHERE h.user = :user'
        )->execute([
            'user' => $user,
        ]);

        return array_map('intval', array_column($rows, 'surveyId'));
    }
}

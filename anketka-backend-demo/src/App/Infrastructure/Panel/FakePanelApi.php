<?php

declare(strict_types=1);

namespace App\Infrastructure\Panel;

/**
 * Заглушка панели на данных в памяти — чтобы демо запускалось без внешних сервисов
 */
class FakePanelApi implements PanelApiInterface
{
    private const array SURVEYS = [
        [101, 'Ваш любимый кофе', 'Пара вопросов о том, где и как вы пьёте кофе', 30, 5],
        [102, 'Онлайн-покупки', 'Как вы выбираете интернет-магазин', 80, 12],
        [103, 'Путешествия', 'Куда вы ездили в этом году и куда собираетесь', 150, 20],
    ];

    public function registerMember(string $email): int
    {
        return random_int(1_000_000, 9_999_999);
    }

    public function getSurveys(int $panelistId): array
    {
        return array_map(
            fn(array $row) => $this->makeSurvey($panelistId, ...$row),
            self::SURVEYS
        );
    }

    public function getSurvey(int $panelistId, int $surveyId): ?PanelSurvey
    {
        foreach ($this->getSurveys($panelistId) as $survey) {
            if ($survey->id === $surveyId) {
                return $survey;
            }
        }

        return null;
    }

    private function makeSurvey(int $panelistId, int $id, string $title, string $description, int $reward, int $duration): PanelSurvey
    {
        return new PanelSurvey(
            $id,
            $title,
            $description,
            $reward,
            $duration,
            sprintf('https://example.com/survey/%d?member=%d', $id, $panelistId),
        );
    }
}

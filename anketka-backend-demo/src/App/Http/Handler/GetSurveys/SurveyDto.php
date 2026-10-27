<?php

declare(strict_types=1);

namespace App\Http\Handler\GetSurveys;

use App\Infrastructure\Panel\PanelSurvey;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(type: 'object')]
class SurveyDto
{
    #[Property(description: 'ID опроса')]
    public int $id;

    #[Property(description: 'Заголовок опроса')]
    public string $title;

    #[Property(description: 'Описание опроса')]
    public string $description;

    #[Property(description: 'Вознаграждение за прохождение в баллах')]
    public int $reward;

    #[Property(description: 'Длительность опроса в минутах')]
    public int $duration;

    #[Property(description: 'URL для прохождения опроса')]
    public string $url;

    public static function fromPanelSurvey(PanelSurvey $survey): self
    {
        $dto = new self();
        $dto->id = $survey->id;
        $dto->title = $survey->title;
        $dto->description = $survey->description;
        $dto->reward = $survey->reward;
        $dto->duration = $survey->durationMinutes;
        $dto->url = $survey->url;

        return $dto;
    }
}

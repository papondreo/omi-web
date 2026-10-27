<?php

declare(strict_types=1);

namespace App\Http\Handler\GetSurveys;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(type: 'object')]
class GetSurveysResponseDto
{
    /**
     * @var SurveyDto[]
     */
    #[Property(
        description: 'Список опросов',
        type: 'array',
        items: new Items(ref: SurveyDto::class)
    )]
    public array $surveys = [];
}

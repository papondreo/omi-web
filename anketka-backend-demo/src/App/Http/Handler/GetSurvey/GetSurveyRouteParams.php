<?php

declare(strict_types=1);

namespace App\Http\Handler\GetSurvey;

use App\Http\RouteParamsFactory\RouteParamsInterface;
use Symfony\Component\Validator\Constraints\Positive;

class GetSurveyRouteParams implements RouteParamsInterface
{
    #[Positive(message: 'ID опроса должен быть положительным числом')]
    public int $id;
}

<?php

declare(strict_types=1);

namespace App\Http\Handler\Auth\Register;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(type: 'object')]
class RegisterResponseDto
{
    #[Property(description: 'API-ключ для заголовка X-API-Key', example: 'ank_3f9c...')]
    public string $apiKey;
}

<?php

declare(strict_types=1);

namespace App\Http\Handler\Common;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(type: 'object')]
class ErrorResponseDto
{
    #[Property(type: 'string', example: 'Invalid request')]
    public string $error;
    
    #[Property(type: 'string', example: 'Additional details about the error', nullable: true)]
    public ?string $details = null;
    
    public function __construct(string $error, ?string $details = null)
    {
        $this->error = $error;
        $this->details = $details;
    }
}
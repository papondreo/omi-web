<?php

declare(strict_types=1);

namespace App\Http\RouteParamsFactory;

use RuntimeException;

class RouteParamsValidationException extends RuntimeException
{
    /** @var array<string, string> */
    private array $properties = [];

    /**
     * @param array<string, string> $properties
     */
    public function __construct(string $message = '', array $properties = [])
    {
        parent::__construct($message);
        $this->properties = $properties;
    }

    /**
     * @return array<string, string>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
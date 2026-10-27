<?php

declare(strict_types=1);

namespace App\Http\RequestDtoFactory;

class RequestDtoValidationException extends \Exception
{

    /**
     * @param string $message
     * @param array<string,string> $properties
     */
    public function __construct(string $message, private readonly array $properties)
    {
        parent::__construct($message);
    }


    /**
     * @return array<string,string>
     */
    function getProperties(): array
    {
        return $this->properties;
    }
}

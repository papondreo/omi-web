<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PasswordsMatch extends Constraint
{
    public string $message = 'Пароли не совпадают.';
    
    public function __construct(
        mixed $options = null,
        array $groups = null,
        mixed $payload = null,
        ?string $message = null
    ) {
        parent::__construct($options, $groups, $payload);
        
        $this->message = $message ?? $this->message;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
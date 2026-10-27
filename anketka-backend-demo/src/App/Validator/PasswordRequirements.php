<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PasswordRequirements extends Constraint
{
    public string $message = 'Пароль должен содержать минимум 8 символов, минимум одну латинскую букву и минимум одну цифру.';
    
    public function __construct(
        mixed $options = null,
        array $groups = null,
        mixed $payload = null,
        ?string $message = null
    ) {
        parent::__construct($options, $groups, $payload);
        
        $this->message = $message ?? $this->message;
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Handler\Auth\Login;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Schema(type: 'object')]
class LoginRequestDto
{
    #[Property(description: 'Email пользователя', example: 'user@example.com')]
    #[NotBlank]
    #[Email]
    public string $email;

    #[Property(description: 'Пароль', example: 'Password123')]
    #[NotBlank]
    #[Length(max: 255)]
    public string $password;
}

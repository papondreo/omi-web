<?php

declare(strict_types=1);

namespace App\Http\Handler\Auth\Register;

use App\Validator\PasswordRequirements;
use App\Validator\PasswordsMatch;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Schema(type: 'object')]
#[PasswordsMatch]
class RegisterRequestDto
{
    #[Property(description: 'Email пользователя', example: 'user@example.com')]
    #[NotBlank]
    #[Email]
    public string $email;

    #[Property(description: 'Пароль (минимум 8 символов, минимум одна латинская буква и одна цифра)', example: 'Password123')]
    #[NotBlank]
    #[PasswordRequirements]
    public string $password;

    #[Property(description: 'Подтверждение пароля (должно совпадать с паролем)', example: 'Password123')]
    #[NotBlank(message: 'Подтверждение пароля обязательно')]
    public string $passwordRepeat;
}

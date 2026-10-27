<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PasswordsMatchValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordsMatch) {
            throw new UnexpectedTypeException($constraint, PasswordsMatch::class);
        }

        // Валидатор работает на уровне класса, поэтому $value - это объект DTO
        if (!is_object($value)) {
            return;
        }

        // Проверяем что у объекта есть свойства password и passwordRepeat
        if (!property_exists($value, 'password') || !property_exists($value, 'passwordRepeat')) {
            return;
        }

        $password = $value->password ?? null;
        $passwordRepeat = $value->passwordRepeat ?? null;

        // Если один из паролей пустой, не проверяем (это обработает NotBlank)
        if (empty($password) || empty($passwordRepeat)) {
            return;
        }

        // Проверяем совпадение паролей
        if ($password !== $passwordRepeat) {
            $this->context->buildViolation($constraint->message)
                ->atPath('passwordRepeat')
                ->addViolation();
        }
    }
}
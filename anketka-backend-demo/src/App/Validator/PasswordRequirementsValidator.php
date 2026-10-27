<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordRequirementsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordRequirements) {
            throw new UnexpectedTypeException($constraint, PasswordRequirements::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Проверяем минимальную длину
        if (strlen($value) < 8) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            return;
        }

        // Проверяем наличие латинской буквы
        if (!preg_match('/[a-zA-Z]/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            return;
        }

        // Проверяем наличие цифры
        if (!preg_match('/[0-9]/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
            return;
        }
    }
}
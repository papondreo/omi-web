<?php

declare(strict_types=1);

use App\Http\Handler\Auth\Register\RegisterRequestDto;
use App\Validator\PasswordRequirements;
use Symfony\Component\Validator\Validation;

describe('PasswordRequirements', function () {
    it('accepts a valid password', function () {
        $violations = Validation::createValidator()->validate('Password123', new PasswordRequirements());

        expect($violations)->toHaveCount(0);
    });

    it('rejects a weak password', function (string $password) {
        $violations = Validation::createValidator()->validate($password, new PasswordRequirements());

        expect($violations)->toHaveCount(1);
    })->with([
        'too short' => ['Pass1'],
        'no digits' => ['PasswordOnly'],
        'no latin letters' => ['12345678'],
    ]);
});

describe('PasswordsMatch', function () {
    it('reports mismatch on passwordRepeat field', function () {
        $dto = new RegisterRequestDto();
        $dto->email = 'user@example.com';
        $dto->password = 'Password123';
        $dto->passwordRepeat = 'Password124';

        $violations = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
            ->validate($dto);

        expect($violations)->toHaveCount(1)
            ->and($violations[0]->getPropertyPath())->toBe('passwordRepeat');
    });
});

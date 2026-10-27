<?php

declare(strict_types=1);

namespace App\Service;

class EmailAlreadyRegisteredException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Email %s уже зарегистрирован', $email));
    }
}

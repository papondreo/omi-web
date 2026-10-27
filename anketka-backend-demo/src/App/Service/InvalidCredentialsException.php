<?php

declare(strict_types=1);

namespace App\Service;

class InvalidCredentialsException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Неверный email или пароль');
    }
}

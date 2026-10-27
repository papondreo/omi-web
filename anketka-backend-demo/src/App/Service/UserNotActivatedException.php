<?php

declare(strict_types=1);

namespace App\Service;

class UserNotActivatedException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Пользователь %s не активирован', $email));
    }
}

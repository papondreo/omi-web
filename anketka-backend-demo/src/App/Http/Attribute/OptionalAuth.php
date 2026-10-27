<?php

declare(strict_types=1);

namespace App\Http\Attribute;

use Attribute;

/**
 * Указывает, что endpoint не требует обязательной авторизации,
 * но может использовать её если она предоставлена
 */
#[Attribute(Attribute::TARGET_CLASS)]
class OptionalAuth
{
}
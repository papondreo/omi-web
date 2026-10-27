<?php

declare(strict_types=1);

use App\Service\MonologFactory;
use Monolog\Level;
use Psr\Log\LoggerInterface;

return [
    'dependencies' => [
        'factories' => [
            LoggerInterface::class => MonologFactory::class,
            // Для отдельного лог-файла под конкретный канал см. App\Logger\ChannelLoggerFactory
            // (рецепт регистрации — в докблоке класса).
        ],
    ],
    
    'logger' => [
        'name' => 'app',
        'path' => 'data/log',
        'level' => Level::Info,
        'max_files' => 30,
        'debug' => false,
    ],
];

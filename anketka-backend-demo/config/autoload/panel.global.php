<?php

declare(strict_types=1);

return [
    // Внешняя панельная платформа: участники, кошельки, опросы, datapoints.
    // В демо доступен только драйвер 'fake' (данные в памяти), см. App\Infrastructure\Panel.
    'panel' => [
        'driver' => 'fake',
    ],
];

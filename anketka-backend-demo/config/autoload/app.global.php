<?php

declare(strict_types=1);

return [
    'app' => [
        'environment' => 'prod',
        'surveys_cache_ttl' => 120,
        // 0 = ключ живёт вечно, иначе TTL в днях с момента создания
        'api_key_ttl_days' => 0,
    ],
];

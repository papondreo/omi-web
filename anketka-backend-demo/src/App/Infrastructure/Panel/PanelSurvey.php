<?php

declare(strict_types=1);

namespace App\Infrastructure\Panel;

/**
 * Опрос в том виде, в каком его отдаёт панель
 */
final readonly class PanelSurvey
{
    public function __construct(
        public int    $id,
        public string $title,
        public string $description,
        public int    $reward,
        public int    $durationMinutes,
        public string $url,
    ) {
    }
}

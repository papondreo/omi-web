<?php

declare(strict_types=1);

namespace App\Infrastructure\Panel;

use Psr\Container\ContainerInterface;

class PanelApiFactory
{
    public function __invoke(ContainerInterface $container): PanelApiInterface
    {
        $config = $container->get('config');
        $driver = $config['panel']['driver'] ?? 'fake';

        return match ($driver) {
            'fake' => new FakePanelApi(),
            default => throw new \RuntimeException(sprintf('Драйвер панели "%s" недоступен в демо', $driver)),
        };
    }
}

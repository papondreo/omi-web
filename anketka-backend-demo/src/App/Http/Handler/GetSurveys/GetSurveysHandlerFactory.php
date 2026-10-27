<?php

declare(strict_types=1);

namespace App\Http\Handler\GetSurveys;

use App\Infrastructure\Panel\PanelApiInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class GetSurveysHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): GetSurveysHandler
    {
        $config = $container->get('config');
        $cacheTtl = (int)($config['app']['surveys_cache_ttl'] ?? 30);

        return new GetSurveysHandler(
            $container->get(PanelApiInterface::class),
            $container->get(CacheItemPoolInterface::class),
            $cacheTtl,
            $container->get(LoggerInterface::class),
        );
    }
}

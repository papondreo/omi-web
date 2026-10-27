<?php

declare(strict_types=1);

namespace App\Http\Router;

use App\Http\Attribute\OptionalAuth;
use App\Http\Middleware\AuthenticationMiddleware;
use App\Http\Middleware\OptionalAuthenticationMiddleware;
use Mezzio\Application;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;

readonly class RouterInitializer
{
    /**
     * Роуты с этим префиксом регистрируются только в dev-окружении
     */
    public const string TEST_ROUTE_PREFIX = '/api/test/';

    public function __construct(
        private Application            $app,
        private CacheItemPoolInterface $cache,
        private LoggerInterface        $logger,
        private string                $environment = 'prod',
        private string                 $directory = 'src/App/Http/Handler'
    ) {
    }

    /**
     * Регистрирует роуты на основе атрибутов #[Get], #[Post] в указанных классах директории
     */
    public function registerRoutes(): void
    {
        $startTime = microtime(true);

        $cacheKey = 'router_routes_' . md5($this->directory);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $routes = $cacheItem->get();
            $this->logger->debug('RouterInitializer: Using cached routes', ['count' => count($routes)]);
            foreach ($routes as $route) {
                $this->registerRoute($route['method'], $route['path'], $route['class'], $route['auth']);
            }
            $endTime = microtime(true);
            $this->logger->debug('RouterInitializer: Routes initialized from cache', [
                'time_ms' => round(($endTime - $startTime) * 1000, 2),
                'count' => count($routes)
            ]);
            return;
        }

        $routes = $this->scanForRoutes();
        $this->logger->debug('RouterInitializer: Scanning filesystem for routes', ['count' => count($routes)]);

        foreach ($routes as $route) {
            $this->registerRoute($route['method'], $route['path'], $route['class'], $route['auth']);
        }

        $cacheItem->set($routes);
        $this->cache->save($cacheItem);

        $endTime = microtime(true);
        $this->logger->debug('RouterInitializer: Routes scanned and cached', [
            'time_ms' => round(($endTime - $startTime) * 1000, 2),
            'count' => count($routes)
        ]);
    }

    /**
     * Сканирует файловую систему в поисках роутов
     */
    private function scanForRoutes(): array
    {
        $routes = [];
        $directory = $this->directory;
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($rii as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;
            $class = $this->getClassFromFile($file->getPathname());
            if (!$class || !class_exists($class)) continue;

            $ref = new ReflectionClass($class);
            $attrs = $ref->getAttributes();

            foreach ($attrs as $attr) {
                $name = $attr->getName();
                $args = $attr->getArguments();
                $path = $args['path'] ?? $args[0] ?? '';

                $authType = $this->determineAuthType($ref, $args);

                $method = match (true) {
                    $name === 'OpenApi\\Attributes\\Post' || $name === 'Post' => 'post',
                    $name === 'OpenApi\\Attributes\\Get' || $name === 'Get' => 'get',
                    $name === 'OpenApi\\Attributes\\Delete' || $name === 'Delete' => 'delete',
                    $name === 'OpenApi\\Attributes\\Patch' || $name === 'Patch' => 'patch',
                    $name === 'OpenApi\\Attributes\\Put' || $name === 'Put' => 'put',
                    default => null,
                };

                if ($method !== null) {
                    $routes[] = ['method' => $method, 'path' => $path, 'class' => $class, 'auth' => $authType];
                }
            }
        }

        return $routes;
    }

    /**
     * Определяет тип авторизации для роута
     */
    private function determineAuthType(ReflectionClass $ref, array $routeArgs): string
    {
        if (isset($routeArgs['security'])) {
            return 'auth';
        }

        $optionalAuthAttributes = $ref->getAttributes(OptionalAuth::class);
        if (!empty($optionalAuthAttributes)) {
            return 'optional';
        }

        return 'none';
    }

    /**
     * Регистрирует маршрут с учетом security middleware
     */
    private function registerRoute(string $method, string $path, string $handlerClass, string $authType): void
    {
        // Фильтр здесь, а не в scanForRoutes: кеш роутов остаётся одинаковым для всех окружений
        if ($this->environment !== 'dev' && str_starts_with($path, self::TEST_ROUTE_PREFIX)) {
            return;
        }

        if ($authType === 'auth') {
            $this->app->$method($path, [AuthenticationMiddleware::class, $handlerClass]);
        } elseif ($authType === 'optional') {
            $this->app->$method($path, [OptionalAuthenticationMiddleware::class, $handlerClass]);
        } else {
            $this->app->$method($path, $handlerClass);
        }
    }

    /**
     * Получает FQCN из файла PHP (простая реализация)
     */
    private function getClassFromFile(string $file): ?string
    {
        $src = file_get_contents($file);
        if (!preg_match('#namespace ([^;]+);#', $src, $ns)) return null;
        if (!preg_match('#class (\w+)#', $src, $cls)) return null;
        return $ns[1] . '\\' . $cls[1];
    }
}

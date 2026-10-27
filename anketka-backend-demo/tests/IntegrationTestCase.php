<?php

declare(strict_types=1);

namespace Tests;

use App\Entity\User;
use App\Entity\UserApiKey;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Stream;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Application;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Builder\UserBuilder;

/**
 * Базовый класс интеграционных тестов.
 *
 * Тесты гоняют приложение целиком (pipeline + роуты + хендлеры) против настоящей
 * MySQL из docker-compose. Отдельная БД app_test создаётся автоматически, схема
 * строится из сущностей через SchemaTool один раз на процесс, а перед каждым
 * тестом все таблицы очищаются.
 */
abstract class IntegrationTestCase extends BaseTestCase
{
    // Кеши на уровне процесса
    protected static ?array $cachedConfig = null;
    protected static ?array $tableNames = null;
    protected static bool $schemaCreated = false;

    // Экземпляры для тестов
    protected ServiceManager $container;
    protected EntityManager $em;
    protected Application $app;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Кешируем конфигурацию на уровне процесса
        if (self::$cachedConfig === null) {
            $config = require __DIR__ . '/../config/config.php';
            $config['db'] = [
                'host' => 'mysql', // сервис из docker-compose.yml
                'port' => 3306,
                'dbname' => 'app_test',
                'user' => 'root',
                'password' => 'root',
            ];
            // Тесты рассчитаны на dev-окружение, не зависим от локальных *.local.php
            $config['app']['environment'] = 'dev';

            self::$cachedConfig = $config;
        }

        // Создаем схему только один раз
        if (!self::$schemaCreated) {
            $em = getTestContainer(self::$cachedConfig)->get(EntityManager::class);
            self::createTestDatabase($em);
            self::createSchema($em);
            self::$schemaCreated = true;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Новый контейнер на каждый тест, чтобы можно было подменять сервисы
        $this->container = getTestContainer(self::$cachedConfig);
        // Кэш в памяти: закэшированный ответ одного теста не должен попасть в другой
        $this->container->setService(CacheItemPoolInterface::class, new ArrayAdapter());
        $this->em = $this->container->get(EntityManager::class);

        // Очищаем только данные
        $this->resetDatabase();
        $this->em->clear();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->clear();
    }

    private static function createTestDatabase(EntityManager $em): void
    {
        $params = $em->getConnection()->getParams();
        $dbName = $params['dbname'];

        // Временно подключаемся без указания базы данных
        unset($params['dbname']);
        $tmpConnection = \Doctrine\DBAL\DriverManager::getConnection($params);
        $tmpConnection->executeStatement("CREATE DATABASE IF NOT EXISTS `$dbName`");
        $tmpConnection->close();
    }

    private static function createSchema(EntityManager $em): void
    {
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);

        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);
    }

    protected function resetDatabase(): void
    {
        $connection = $this->em->getConnection();

        if (self::$tableNames === null) {
            self::$tableNames = $connection->createSchemaManager()->listTableNames();
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::$tableNames as $table) {
            if ($table !== 'doctrine_migration_versions') {
                $connection->executeStatement('TRUNCATE TABLE ' . $table);
            }
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Собирает приложение так же, как public/index.php. Вызывать ПОСЛЕ подмены
     * сервисов моками — иначе хендлеры получат настоящие зависимости.
     */
    protected function initializeApp(): void
    {
        $this->app = $this->container->get(Application::class);
        $factory = $this->container->get(\Mezzio\MiddlewareFactory::class);

        (require __DIR__ . '/../config/pipeline.php')($this->app, $factory, $this->container);
        (require __DIR__ . '/../config/routes.php')($this->app, $factory, $this->container);
    }

    protected function createJsonRequest(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write(json_encode($data));
        $stream->rewind();

        $uriObj = new \Laminas\Diactoros\Uri($uri);
        $queryParams = [];
        if ($uriObj->getQuery()) {
            parse_str($uriObj->getQuery(), $queryParams);
        }

        return ServerRequestFactory::fromGlobals()
            ->withMethod($method)
            ->withUri($uriObj)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream)
            ->withParsedBody($data)
            ->withQueryParams($queryParams);
    }

    protected function createJsonRequestWithAuth(string $method, string $uri, array $data, string $apiKey): ServerRequestInterface
    {
        return $this->createJsonRequest($method, $uri, $data)
            ->withHeader('X-API-Key', $apiKey);
    }

    protected function createUserBuilder(): UserBuilder
    {
        return UserBuilder::create($this->em);
    }

    /**
     * Создаёт активный API-ключ для пользователя и возвращает его токен
     */
    protected function createApiKeyForUser(User $user): string
    {
        $now = new \DateTimeImmutable();
        $token = 'ank_' . bin2hex(random_bytes(24));

        $key = new UserApiKey();
        $key->setUser($user)
            ->setApiKey($token)
            ->setCreatedAt($now)
            ->setLastUsedAt($now);

        $this->em->persist($key);
        $this->em->flush();

        return $token;
    }

    /**
     * Создает мок сервиса и устанавливает его в контейнер
     */
    protected function mockService(string $serviceClass): Mockery\MockInterface
    {
        $mock = Mockery::mock($serviceClass);
        $this->container->setService($serviceClass, $mock);
        return $mock;
    }

    protected function decodeJson(\Psr\Http\Message\ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }
}

<?php

declare(strict_types=1);

use App\Infrastructure\Panel\PanelApiInterface;
use App\Infrastructure\Panel\PanelSurvey;

beforeEach(function () {
    $this->panelApiMock = $this->mockService(PanelApiInterface::class);
    $this->initializeApp();
});

afterEach(function () {
    Mockery::close();
});

describe('GET /api/surveys', function () {
    it('returns 401 without api key', function () {
        $response = $this->app->handle($this->createJsonRequest('GET', '/api/surveys'));

        expect($response->getStatusCode())->toBe(401);
    });

    it('returns 401 with unknown api key', function () {
        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys', [], 'ank_unknown')
        );

        expect($response->getStatusCode())->toBe(401);
    });

    it('returns 403 for not activated user', function () {
        $user = $this->createUserBuilder()->withActivated(false)->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys', [], $apiKey)
        );

        expect($response->getStatusCode())->toBe(403);
    });

    it('returns surveys of the current panelist', function () {
        $user = $this->createUserBuilder()->withPanelistId(777)->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        $this->panelApiMock->shouldReceive('getSurveys')
            ->once()
            ->with(777)
            ->andReturn([
                new PanelSurvey(1, 'Опрос', 'Описание', 50, 10, 'https://example.com/s/1'),
            ]);

        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys', [], $apiKey)
        );

        expect($response->getStatusCode())->toBe(200)
            ->and($this->decodeJson($response))->toBe([
                'surveys' => [[
                    'id' => 1,
                    'title' => 'Опрос',
                    'description' => 'Описание',
                    'reward' => 50,
                    'duration' => 10,
                    'url' => 'https://example.com/s/1',
                ]],
            ]);
    });

    it('caches panel response between requests', function () {
        $user = $this->createUserBuilder()->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        // Второй запрос должен прийти из кэша, а не из панели
        $this->panelApiMock->shouldReceive('getSurveys')->once()->andReturn([]);

        foreach ([1, 2] as $_) {
            $response = $this->app->handle(
                $this->createJsonRequestWithAuth('GET', '/api/surveys', [], $apiKey)
            );
            expect($response->getStatusCode())->toBe(200);
        }
    });

    it('returns 500 when panel is unavailable', function () {
        $user = $this->createUserBuilder()->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        $this->panelApiMock->shouldReceive('getSurveys')
            ->andThrow(new RuntimeException('connection timeout'));

        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys', [], $apiKey)
        );

        expect($response->getStatusCode())->toBe(500)
            ->and($this->decodeJson($response)['error'])->toBe('Ошибка получения опросов');
    });
});

describe('GET /api/surveys/:id', function () {
    it('returns a single survey', function () {
        $user = $this->createUserBuilder()->withPanelistId(777)->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        $this->panelApiMock->shouldReceive('getSurvey')
            ->once()
            ->with(777, 42)
            ->andReturn(new PanelSurvey(42, 'Опрос', 'Описание', 50, 10, 'https://example.com/s/42'));

        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys/42', [], $apiKey)
        );

        expect($response->getStatusCode())->toBe(200)
            ->and($this->decodeJson($response)['id'])->toBe(42);
    });

    it('returns 404 for unknown survey', function () {
        $user = $this->createUserBuilder()->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        $this->panelApiMock->shouldReceive('getSurvey')->andReturnNull();

        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys/42', [], $apiKey)
        );

        expect($response->getStatusCode())->toBe(404);
    });

    it('returns 400 for invalid id', function () {
        $user = $this->createUserBuilder()->persistAndFlush();
        $apiKey = $this->createApiKeyForUser($user);

        $this->panelApiMock->shouldNotReceive('getSurvey');

        $response = $this->app->handle(
            $this->createJsonRequestWithAuth('GET', '/api/surveys/abc', [], $apiKey)
        );

        expect($response->getStatusCode())->toBe(400)
            ->and($this->decodeJson($response)['error'])->toBe('ID опроса должен быть положительным числом');
    });
});

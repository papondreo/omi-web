<?php

declare(strict_types=1);

use App\Entity\HiddenSurvey;
use App\Infrastructure\Panel\PanelApiInterface;
use App\Infrastructure\Panel\PanelSurvey;

beforeEach(function () {
    $this->panelApiMock = $this->mockService(PanelApiInterface::class);
    $this->initializeApp();

    // Панель с тремя опросами: в тестах скрываем средний, чтобы поймать дыры в ключах массива
    $this->panelSurveys = [
        new PanelSurvey(101, 'Первый', 'Описание', 50, 10, 'https://example.com/s/101'),
        new PanelSurvey(102, 'Второй', 'Описание', 150, 20, 'https://example.com/s/102'),
        new PanelSurvey(103, 'Третий', 'Описание', 30, 5, 'https://example.com/s/103'),
    ];
    $this->panelApiMock->shouldReceive('getSurveys')->andReturn($this->panelSurveys)->byDefault();
    $this->panelApiMock->shouldReceive('getSurvey')
        ->andReturnUsing(function (int $panelistId, int $surveyId) {
            foreach ($this->panelSurveys as $survey) {
                if ($survey->id === $surveyId) {
                    return $survey;
                }
            }
            return null;
        })
        ->byDefault();

    $this->request = function (string $method, string $uri, string $apiKey) {
        return $this->app->handle($this->createJsonRequestWithAuth($method, $uri, [], $apiKey));
    };
    $this->surveyIds = function (string $uri, string $apiKey): array {
        $response = ($this->request)('GET', $uri, $apiKey);
        expect($response->getStatusCode())->toBe(200);
        return array_column($this->decodeJson($response)['surveys'], 'id');
    };
});

afterEach(function () {
    Mockery::close();
});

describe('POST /api/surveys/:id/hide', function () {
    it('returns 401 without api key', function () {
        $response = $this->app->handle($this->createJsonRequest('POST', '/api/surveys/102/hide'));

        expect($response->getStatusCode())->toBe(401);
    });

    it('returns 400 for invalid id', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());

        $response = ($this->request)('POST', '/api/surveys/abc/hide', $apiKey);

        expect($response->getStatusCode())->toBe(400)
            ->and($this->decodeJson($response)['error'])->toBe('ID опроса должен быть положительным числом');
    });

    it('returns 404 for unknown survey and stores nothing', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());

        $response = ($this->request)('POST', '/api/surveys/999/hide', $apiKey);

        expect($response->getStatusCode())->toBe(404)
            ->and($this->decodeJson($response)['error'])->toBe('Опрос не найден')
            ->and($this->em->getRepository(HiddenSurvey::class)->count([]))->toBe(0);
    });

    it('hides survey from the list', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());

        $response = ($this->request)('POST', '/api/surveys/102/hide', $apiKey);

        expect($response->getStatusCode())->toBe(204)
            ->and((string) $response->getBody())->toBe('')
            ->and(($this->surveyIds)('/api/surveys', $apiKey))->toBe([101, 103]);
    });

    it('is idempotent: hiding twice stores a single record', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());

        foreach ([1, 2] as $_) {
            expect(($this->request)('POST', '/api/surveys/102/hide', $apiKey)->getStatusCode())->toBe(204);
        }

        expect($this->em->getRepository(HiddenSurvey::class)->count([]))->toBe(1);
    });

    it('does not affect other users', function () {
        $alice = $this->createApiKeyForUser(
            $this->createUserBuilder()->withEmail('alice@example.com')->withPanelistId(1)->persistAndFlush()
        );
        $bob = $this->createApiKeyForUser(
            $this->createUserBuilder()->withEmail('bob@example.com')->withPanelistId(2)->persistAndFlush()
        );

        ($this->request)('POST', '/api/surveys/102/hide', $alice);

        expect(($this->surveyIds)('/api/surveys', $alice))->toBe([101, 103])
            ->and(($this->surveyIds)('/api/surveys', $bob))->toBe([101, 102, 103]);
    });

    it('applies immediately even when the survey list is cached', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());
        // Панель вызывается один раз: второй список приходит из кэша, но уже без скрытого опроса
        $this->panelApiMock->shouldReceive('getSurveys')->once()->andReturn($this->panelSurveys);

        expect(($this->surveyIds)('/api/surveys', $apiKey))->toBe([101, 102, 103]);

        ($this->request)('POST', '/api/surveys/102/hide', $apiKey);

        expect(($this->surveyIds)('/api/surveys', $apiKey))->toBe([101, 103]);
    });

    it('keeps surveys a JSON array when a survey from the middle is hidden', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());
        ($this->request)('POST', '/api/surveys/102/hide', $apiKey);

        $body = (string) ($this->request)('GET', '/api/surveys', $apiKey)->getBody();

        expect(json_decode($body)->surveys)->toBeArray();
    });
});

describe('DELETE /api/surveys/:id/hide', function () {
    it('returns 401 without api key', function () {
        $response = $this->app->handle($this->createJsonRequest('DELETE', '/api/surveys/102/hide'));

        expect($response->getStatusCode())->toBe(401);
    });

    it('returns survey back to the list', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());
        ($this->request)('POST', '/api/surveys/102/hide', $apiKey);

        $response = ($this->request)('DELETE', '/api/surveys/102/hide', $apiKey);

        expect($response->getStatusCode())->toBe(204)
            ->and(($this->surveyIds)('/api/surveys', $apiKey))->toBe([101, 102, 103]);
    });

    it('is idempotent: unhiding a visible survey returns 204', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());

        $response = ($this->request)('DELETE', '/api/surveys/102/hide', $apiKey);

        expect($response->getStatusCode())->toBe(204);
    });

    it('unhides only for the current user', function () {
        $alice = $this->createApiKeyForUser(
            $this->createUserBuilder()->withEmail('alice@example.com')->withPanelistId(1)->persistAndFlush()
        );
        $bob = $this->createApiKeyForUser(
            $this->createUserBuilder()->withEmail('bob@example.com')->withPanelistId(2)->persistAndFlush()
        );
        ($this->request)('POST', '/api/surveys/102/hide', $alice);

        ($this->request)('DELETE', '/api/surveys/102/hide', $bob);

        expect(($this->surveyIds)('/api/surveys', $alice))->toBe([101, 103]);
    });
});

describe('GET /api/hidden-surveys', function () {
    it('returns 401 without api key', function () {
        $response = $this->app->handle($this->createJsonRequest('GET', '/api/hidden-surveys'));

        expect($response->getStatusCode())->toBe(401);
    });

    it('returns empty list without calling the panel when nothing is hidden', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());
        $this->panelApiMock->shouldNotReceive('getSurveys');

        expect(($this->surveyIds)('/api/hidden-surveys', $apiKey))->toBe([]);
    });

    it('returns only hidden surveys', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());
        ($this->request)('POST', '/api/surveys/101/hide', $apiKey);
        ($this->request)('POST', '/api/surveys/103/hide', $apiKey);

        expect(($this->surveyIds)('/api/hidden-surveys', $apiKey))->toBe([101, 103]);
    });

    it('skips hidden surveys that are no longer in the panel', function () {
        $apiKey = $this->createApiKeyForUser($this->createUserBuilder()->persistAndFlush());
        ($this->request)('POST', '/api/surveys/102/hide', $apiKey);
        $this->panelApiMock->shouldReceive('getSurveys')->andReturn([$this->panelSurveys[0]]);

        expect(($this->surveyIds)('/api/hidden-surveys', $apiKey))->toBe([]);
    });
});

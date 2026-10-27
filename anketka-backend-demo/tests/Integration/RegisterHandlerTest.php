<?php

declare(strict_types=1);

use App\Entity\User;
use App\Entity\UserApiKey;
use App\Infrastructure\Panel\PanelApiInterface;

beforeEach(function () {
    // Мокаем ТОЛЬКО внешнее: шлюз в панель. EntityManager, сервисы и валидаторы — настоящие
    $this->panelApiMock = $this->mockService(PanelApiInterface::class);
    $this->initializeApp();
});

afterEach(function () {
    Mockery::close();
});

describe('POST /api/register', function () {
    it('registers user in panel and locally, returns api key', function () {
        $this->panelApiMock->shouldReceive('registerMember')
            ->once()
            ->with('new@example.com')
            ->andReturn(555001);

        $response = $this->app->handle($this->createJsonRequest('POST', '/api/register', [
            'email' => 'new@example.com',
            'password' => 'Password123',
            'passwordRepeat' => 'Password123',
        ]));

        expect($response->getStatusCode())->toBe(200);
        $data = $this->decodeJson($response);
        expect($data['apiKey'])->toStartWith('ank_');

        // Проверяем состояние БД, а не только ответ
        $this->em->clear();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'new@example.com']);
        expect($user)->not->toBeNull()
            ->and($user->getPanelistId())->toBe(555001)
            ->and($user->isActivated())->toBeTrue()
            ->and(password_verify('Password123', $user->getPassword()))->toBeTrue();

        $apiKey = $this->em->getRepository(UserApiKey::class)->findOneBy(['apiKey' => $data['apiKey']]);
        expect($apiKey?->getUser()->getId())->toBe($user->getId());
    });

    it('returns 400 with russian messages for invalid body', function () {
        $this->panelApiMock->shouldNotReceive('registerMember');

        $response = $this->app->handle($this->createJsonRequest('POST', '/api/register', [
            'email' => 'not-an-email',
            'password' => 'short',
            'passwordRepeat' => 'other',
        ]));

        expect($response->getStatusCode())->toBe(400);
        $properties = $this->decodeJson($response)['properties'];
        expect($properties)->toHaveKeys(['email', 'password', 'passwordRepeat'])
            ->and($properties['passwordRepeat'])->toBe('Пароли не совпадают.');
    });

    it('returns 409 when email is already registered', function () {
        $this->createUserBuilder()->withEmail('taken@example.com')->persistAndFlush();
        $this->panelApiMock->shouldNotReceive('registerMember');

        $response = $this->app->handle($this->createJsonRequest('POST', '/api/register', [
            'email' => 'taken@example.com',
            'password' => 'Password123',
            'passwordRepeat' => 'Password123',
        ]));

        expect($response->getStatusCode())->toBe(409)
            ->and($this->decodeJson($response)['error'])->toBe('Email уже используется');
    });
});

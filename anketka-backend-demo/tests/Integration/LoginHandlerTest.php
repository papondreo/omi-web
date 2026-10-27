<?php

declare(strict_types=1);

use App\Entity\UserApiKey;

beforeEach(function () {
    $this->initializeApp();
});

afterEach(function () {
    Mockery::close();
});

describe('POST /api/login', function () {
    it('returns a new api key for valid credentials', function () {
        $user = $this->createUserBuilder()
            ->withEmail('user@example.com')
            ->withPassword('Password123')
            ->persistAndFlush();

        $response = $this->app->handle($this->createJsonRequest('POST', '/api/login', [
            'email' => 'user@example.com',
            'password' => 'Password123',
        ]));

        expect($response->getStatusCode())->toBe(200);
        $data = $this->decodeJson($response);
        expect($data['apiKey'])->toStartWith('ank_');

        $this->em->clear();
        $apiKey = $this->em->getRepository(UserApiKey::class)->findOneBy(['apiKey' => $data['apiKey']]);
        expect($apiKey?->getUser()->getId())->toBe($user->getId());
    });

    it('issues a separate key for every login', function () {
        $this->createUserBuilder()
            ->withEmail('user@example.com')
            ->withPassword('Password123')
            ->persistAndFlush();

        $keys = [];
        foreach ([1, 2] as $_) {
            $response = $this->app->handle($this->createJsonRequest('POST', '/api/login', [
                'email' => 'user@example.com',
                'password' => 'Password123',
            ]));
            $keys[] = $this->decodeJson($response)['apiKey'];
        }

        expect($keys[0])->not->toBe($keys[1])
            ->and($this->em->getRepository(UserApiKey::class)->count([]))->toBe(2);
    });

    it('returns 401 for wrong password', function () {
        $this->createUserBuilder()
            ->withEmail('user@example.com')
            ->withPassword('Password123')
            ->persistAndFlush();

        $response = $this->app->handle($this->createJsonRequest('POST', '/api/login', [
            'email' => 'user@example.com',
            'password' => 'WrongPassword1',
        ]));

        expect($response->getStatusCode())->toBe(401)
            ->and($this->decodeJson($response)['error'])->toBe('Неверный email или пароль')
            ->and($this->em->getRepository(UserApiKey::class)->count([]))->toBe(0);
    });

    it('returns the same 401 for unknown email', function () {
        $response = $this->app->handle($this->createJsonRequest('POST', '/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123',
        ]));

        expect($response->getStatusCode())->toBe(401)
            ->and($this->decodeJson($response)['error'])->toBe('Неверный email или пароль');
    });

    it('returns 403 for not activated user', function () {
        $this->createUserBuilder()
            ->withEmail('user@example.com')
            ->withPassword('Password123')
            ->withActivated(false)
            ->persistAndFlush();

        $response = $this->app->handle($this->createJsonRequest('POST', '/api/login', [
            'email' => 'user@example.com',
            'password' => 'Password123',
        ]));

        expect($response->getStatusCode())->toBe(403)
            ->and($this->decodeJson($response)['error'])->toBe('Аккаунт не активирован')
            ->and($this->em->getRepository(UserApiKey::class)->count([]))->toBe(0);
    });

    it('returns 400 with russian messages for invalid body', function () {
        $response = $this->app->handle($this->createJsonRequest('POST', '/api/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]));

        expect($response->getStatusCode())->toBe(400)
            ->and($this->decodeJson($response)['properties'])->toHaveKeys(['email', 'password']);
    });
});

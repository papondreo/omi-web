<?php

declare(strict_types=1);

namespace App\Infrastructure\Panel;

/**
 * Шлюз во внешнюю панельную платформу.
 *
 * В боевом проекте за этим местом стоит обёртка над HTTP-клиентами внутренней
 * платформы компании: участники, кошельки и транзакции, роутинг опросов,
 * анкетные данные (datapoints). Клиенты ставятся из приватного
 * composer-репозитория, поэтому в демо их нет — только FakePanelApi.
 *
 * Правило, которое важно и в бою: хендлеры и сервисы зависят от шлюза,
 * а в интеграционных тестах шлюз всегда мокается.
 */
interface PanelApiInterface
{
    /**
     * Регистрирует участника в панели и возвращает его ID (panelistId)
     */
    public function registerMember(string $email): int;

    /**
     * Опросы, доступные участнику прямо сейчас
     *
     * @return PanelSurvey[]
     */
    public function getSurveys(int $panelistId): array;

    public function getSurvey(int $panelistId, int $surveyId): ?PanelSurvey;
}

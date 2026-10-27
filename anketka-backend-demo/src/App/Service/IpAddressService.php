<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Http\Message\ServerRequestInterface;

class IpAddressService
{
    /**
     * Получает IP адрес клиента из запроса
     * 
     * @param ServerRequestInterface $request
     * @return string
     */
    public function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        // Проверяем заголовок X-Forwarded-For для прокси
        if (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
            // Берем первый IP из списка (реальный IP клиента)
            $ips = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        
        // Проверяем другие возможные заголовки прокси
        if (!empty($serverParams['HTTP_X_REAL_IP'])) {
            return $serverParams['HTTP_X_REAL_IP'];
        }
        
        if (!empty($serverParams['HTTP_CLIENT_IP'])) {
            return $serverParams['HTTP_CLIENT_IP'];
        }
        
        // Возвращаем REMOTE_ADDR или дефолтное значение
        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * IP реального TCP-соединения, без учёта прокси-заголовков.
     *
     * Перед nginx нет доверенного прокси, поэтому X-Forwarded-For и подобные
     * заголовки клиент может подделать. Для решений о блокировке (rate limit,
     * fail2ban) использовать ТОЛЬКО этот метод, не getClientIp().
     */
    public function getRemoteAddr(ServerRequestInterface $request): string
    {
        return $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}